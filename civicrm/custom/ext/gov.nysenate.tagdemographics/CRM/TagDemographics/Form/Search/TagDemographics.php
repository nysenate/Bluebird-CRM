<?php

/**
 * A custom contact search
 */
class CRM_TagDemographics_Form_Search_TagDemographics extends CRM_Contact_Form_Search_Custom_Base implements CRM_Contact_Form_Search_Interface {
  public $_demographicOptions;
  public $_activeDemographic;

  function __construct(&$formValues) {
    parent::__construct($formValues);

    $this->_demographicOptions = array(
      'gender' => 'Gender',
      'age' => 'Age',
      'postal_code' => 'Postal Code',
      'city' => 'City',
      'town' => 'Town',
      'county' => 'County',
      'sd' => 'Senate District',
      'ad' => 'Assembly District',
      'school_district' => 'School District',
    );

    $this->_activeDemographic = NULL;
  }

  /**
   * Prepare a set of search fields
   *
   * @param CRM_Core_Form $form modifiable
   * @return void
   */
  function buildForm(&$form) {
    CRM_Utils_System::setTitle(ts('Tag Demographics Search'));

    $tags = array('' => ts('- any tag -')) + CRM_Core_PseudoConstant::get('CRM_Core_DAO_EntityTag', 'tag_id', array('onlyActive' => FALSE));
    $form->add('select', 'tag', ts('Tag'), $tags, TRUE, array('class' => "crm-select2 huge"));

    $form->add('select', 'demographic', ts('Demographic'),
      $this->_demographicOptions, TRUE, array(
        'class' => 'crm-select2 huge',
        'multiple' => TRUE,
        'placeholder' => ts('- select -'),
      ));

    // Optionally define default search values
    $form->setDefaults(array());

    /**
     * if you are using the standard template, this array tells the template what elements
     * are part of the search criteria
     */
    $form->assign('elements', array('tag', 'demographic'));

    //export option
    $formValues = $form->get('formValues');
    if (!empty($formValues)) {
      $quickExportUrl = CRM_Utils_System::url('civicrm/search/custom/tagdemographics/quickexport',
        http_build_query(array('formValues' => $formValues)));
      $form->assign('quickExportUrl', $quickExportUrl);
    }
  }

  /**
   * Get a list of summary data points
   *
   * @return mixed; NULL or array with keys:
   *  - summary: string
   *  - total: numeric
   */
  function summary() {
    return NULL;
  }

  /**
   * Get a list of displayable columns
   *
   * @return array, keys are printable column headers and values are SQL column names
   */
  function &columns() {
    /*Civi::log()->debug('columns', array(
      'vals' => $this->_formValues,
      'demographics' => $this->_demographicOptions,
    ));*/

    // return by reference
    $columns = array(
      'Demographic' => 'demo',
      'Values' => 'label',
      'Count' => 'demo_count',
    );
    return $columns;
  }

  /**
   * Construct a full SQL query which returns one page worth of results
   *
   * @param int $offset
   * @param int $rowcount
   * @param null $sort
   * @param bool $includeContactIDs
   * @param bool $justIDs
   * @return string, sql
   */
  function all($offset = 0, $rowcount = 0, $sort = NULL,
    $includeContactIDs = FALSE, $justIDs = FALSE
  ) {
    //Civi::log()->debug('all', array('this' => $this));

    $sql = array();
    foreach ($this->_formValues['demographic'] as $demo) {
      //set cycle demo value so we can use in sql parts
      $this->_activeDemographic = $demo;

      $select = $this->select();
      $from = $this->from();
      $where = $this->where();
      $groupBy = $this->groupBy();

      $sql[] = "(
        SELECT '{$demo}' demo, $select
        $from
        WHERE $where
        $groupBy
        ORDER BY label asc
      )";
    }

    $sqlCombined = implode(' UNION ', $sql);
    //Civi::log()->debug('all', array('$sqlCombined' => $sqlCombined));

    return $sqlCombined;
  }

  /**
   * Construct a SQL SELECT clause
   *
   * @return string, sql fragment with SELECT arguments
   */
  function select() {
    //Civi::log()->debug('select', array('vals' => $this->_formValues));

    $meta = $this->getDemographicMeta($this->_activeDemographic);

    return "{$meta['select']} label, COUNT(c.id) demo_count";
  }

  /**
   * Construct a SQL FROM clause
   *
   * @return string, sql fragment with FROM and JOIN clauses
   */
  function from() {
    $meta = $this->getDemographicMeta($this->_activeDemographic);
    $tag = CRM_Utils_Array::value('tag', $this->_formValues);

    return "
      FROM civicrm_contact c
      JOIN civicrm_entity_tag et
        ON c.id = et.entity_id
        AND et.entity_table = 'civicrm_contact'
        AND et.tag_id = {$tag}
      {$meta['from']}
    ";
  }

  /**
   * Construct a SQL WHERE clause
   *
   * @param bool $includeContactIDs
   * @return string, sql fragment with conditional expressions
   */
  function where($includeContactIDs = FALSE) {
    $meta = $this->getDemographicMeta($this->_activeDemographic);

    $where = 'c.is_deceased != 1'.$meta['where'];

    return $where;
  }

  function groupBy() {
    return "
      GROUP BY label
    ";
  }

  public function count() {
    $sql = $this->all();

    $dao = CRM_Core_DAO::executeQuery($sql,
      CRM_Core_DAO::$_nullArray
    );
    return $dao->N;
  }

  public function contactIDs($offset = 0, $rowcount = 0, $sort = NULL, $returnSQL = TRUE) {
    return $this->all($offset, $rowcount, $sort);
  }

  /**
   * Determine the Smarty template for the search screen
   *
   * @return string, template path (findable through Smarty template path)
   */
  function templateFile() {
    return 'CRM/TagDemographics/Form/Search/TagDemographics.tpl';
  }

  /**
   * Modify the content of each row
   *
   * @param array $row modifiable SQL result row
   * @return void
   */
  function alterRow(&$row) {
    //Civi::log()->debug('alterRow', array('row' => $row));

    $meta = $this->getDemographicMeta($row['demo']);
    $row['demo'] = $meta['title'];
    $row['label'] = (empty($row['label']) && $row['label'] !== '0') ? '(none)' : $row['label'];
  }

  public function setTitle($title) {
    CRM_Utils_System::setTitle(ts('Tag Demographic Search'));
  }

  function getDemographicMeta($demo) {
    $meta = array();
    switch ($demo) {
      case 'gender':
        $meta = array(
          'title' => $this->_demographicOptions[$demo],
          'select' => 'demo.label',
          'from' => "
            LEFT JOIN civicrm_option_value demo
              ON c.gender_id = demo.value
              AND demo.option_group_id = 3
          ",
          'where' => '',
        );
        break;

      case 'age':
        $meta = array(
          'title' => $this->_demographicOptions[$demo],
          'select' => 'YEAR(CURRENT_DATE) - YEAR(c.birth_date) - (RIGHT(CURRENT_DATE, 5) < RIGHT(c.birth_date, 5))',
          'from' => "",
          'where' => ' AND c.birth_date IS NOT NULL'
        );
        break;

      case 'postal_code':
        $meta = array(
          'title' => $this->_demographicOptions[$demo],
          'select' => 'demo.postal_code',
          'from' => "
            LEFT JOIN civicrm_address demo
              ON c.id = demo.contact_id
          ",
          'where' => '',
        );
        break;

      case 'city':
        $meta = array(
          'title' => $this->_demographicOptions[$demo],
          'select' => 'demo.city',
          'from' => "
            LEFT JOIN civicrm_address demo
              ON c.id = demo.contact_id
          ",
          'where' => '',
        );
        break;

      case 'town':
        $meta = array(
          'title' => $this->_demographicOptions[$demo],
          'select' => 'di.town_52',
          'from' => "
            LEFT JOIN civicrm_address demo
              ON c.id = demo.contact_id
            LEFT JOIN civicrm_value_district_information_7 di
              ON demo.id = di.entity_id
          ",
          'where' => '',
        );
        break;

      case 'county':
        $meta = array(
          'title' => $this->_demographicOptions[$demo],
          'select' => 'di.county_50',
          'from' => "
            LEFT JOIN civicrm_address demo
              ON c.id = demo.contact_id
            LEFT JOIN civicrm_value_district_information_7 di
              ON demo.id = di.entity_id
          ",
          'where' => '',
        );
        break;

      case 'sd':
        $meta = array(
          'title' => $this->_demographicOptions[$demo],
          'select' => 'di.ny_senate_district_47',
          'from' => "
            LEFT JOIN civicrm_address demo
              ON c.id = demo.contact_id
            LEFT JOIN civicrm_value_district_information_7 di
              ON demo.id = di.entity_id
          ",
          'where' => '',
        );
        break;

      case 'ad':
        $meta = array(
          'title' => $this->_demographicOptions[$demo],
          'select' => 'di.ny_assembly_district_48',
          'from' => "
            LEFT JOIN civicrm_address demo
              ON c.id = demo.contact_id
            LEFT JOIN civicrm_value_district_information_7 di
              ON demo.id = di.entity_id
          ",
          'where' => '',
        );
        break;

      case 'school_district':
        $meta = array(
          'title' => $this->_demographicOptions[$demo],
          'select' => 'di.school_district_54',
          'from' => "
            LEFT JOIN civicrm_address demo
              ON c.id = demo.contact_id
            LEFT JOIN civicrm_value_district_information_7 di
              ON demo.id = di.entity_id
          ",
          'where' => '',
        );
        break;

      default:
    }

    return $meta;
  }

  /**
   * export csv
   */
  static function quickExport() {
    //CRM_Core_Error::debug_var('$_REQUEST', $_REQUEST);

    if (!empty($_REQUEST['formValues'])) {
      $_REQUEST['is_quick_export'] = true;
      $formValues = $_REQUEST['formValues'];

      CRM_Export_BAO_Export::exportCustom($formValues['customSearchClass'],
        $formValues,
        'sort_name'
      );
    }
  }//quickExport
}
