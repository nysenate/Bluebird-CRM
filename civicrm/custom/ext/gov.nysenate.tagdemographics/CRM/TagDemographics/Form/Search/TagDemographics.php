<?php

/**
 * A custom contact search
 */
class CRM_TagDemographics_Form_Search_TagDemographics extends CRM_Contact_Form_Search_Custom_Base implements CRM_Contact_Form_Search_Interface {
  public $_demographicOptions;

  function __construct(&$formValues) {
    parent::__construct($formValues);

    $this->_demographicOptions = array(
      'gender' => 'Gender',
      'age' => 'Age',
      //'postal_code' => 'Postal Code',
    );
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
    $form->add('select', 'tag', ts('Tagged'), $tags, TRUE, array('class' => "crm-select2 huge"));

    $form->add('select', 'demographic', ts('Demographic'), $this->_demographicOptions, TRUE, array('class' => "crm-select2"));

    // Optionally define default search values
    $form->setDefaults(array());

    /**
     * if you are using the standard template, this array tells the template what elements
     * are part of the search criteria
     */
    $form->assign('elements', array('tag', 'demographic'));
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
      "Demographic Breakdown: {$this->_demographicOptions[$this->_formValues['demographic']]}" => 'label',
      ts('Count') => 'demo_count',
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
    $select = $this->select();
    $from = $this->from();
    $where = $this->where();
    $groupBy = $this->groupBy();

    $sql = "
      SELECT $select
      $from
      WHERE $where
      $groupBy
    ";

    // Define ORDER BY for query in $sort, with default value
    if (!empty($sort)) {
      if (is_string($sort)) {
        $sql .= " ORDER BY $sort ";
      }
      else {
        $sql .= " ORDER BY " . trim($sort->orderBy());
      }
    }
    else {
      $sql .= "ORDER BY label asc";
    }

    if ($rowcount > 0 && $offset >= 0) {
      $offset = CRM_Utils_Type::escape($offset, 'Int');
      $rowcount = CRM_Utils_Type::escape($rowcount, 'Int');
      $sql .= " LIMIT $offset, $rowcount ";
    }

    Civi::log()->debug('all', array('$sql' => $sql));

    return $sql;
  }

  /**
   * Construct a SQL SELECT clause
   *
   * @return string, sql fragment with SELECT arguments
   */
  function select() {
    //Civi::log()->debug('select', array('vals' => $this->_formValues));

    $meta = $this->getDemographicMeta($this->_formValues);

    return "
      {$meta['select']} as label,
      COUNT(c.id) as demo_count
    ";
  }

  /**
   * Construct a SQL FROM clause
   *
   * @return string, sql fragment with FROM and JOIN clauses
   */
  function from() {
    $meta = $this->getDemographicMeta($this->_formValues);
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
    $meta = $this->getDemographicMeta($this->_formValues);

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
  }

  public function setTitle($title) {
    if ($title) {
      CRM_Utils_System::setTitle($title);
    }
    else {
      CRM_Utils_System::setTitle(ts('Tag Demographic Search'));
    }
  }

  function getDemographicMeta($params) {
    $meta = array();
    switch ($params['demographic']) {
      case 'gender':
        $meta = array(
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
          'select' => 'YEAR(CURRENT_DATE) - YEAR(c.birth_date) - (RIGHT(CURRENT_DATE, 5) < RIGHT(c.birth_date, 5))',
          //'select' => 'TIMESTAMPDIFF(YEAR, c.birth_date, CURDATE())',
          //'select' => "DATE_FORMAT(FROM_DAYS(DATEDIFF(c.birth_date, CURRENT_DATE)), '%Y')+0",
          //'select' => 'DATEDIFF(CURRENT_DATE, c.birth_date) / 365.25',
          'from' => "",
          'where' => ' AND c.birth_date IS NOT NULL'
        );
        break;

      case 'postal_code':
        $meta = array(
          'select' => 'demo.label',
          'from' => "
            LEFT JOIN civicrm_option_value demo
              ON c.gender_id = demo.value
              AND demo.option_group_id = 3
          ",
          'where' => '',
        );
        break;

      default:
    }

    return $meta;
  }
}
