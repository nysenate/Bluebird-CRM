<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2018                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */

/**
 *
 * This search now functions as a subset of advanced search since at some point it
 * was added to advanced search.
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2018
 */
class CRM_Contact_Form_Search_Custom_Proximity extends CRM_Contact_Form_Search_Custom_Base implements CRM_Contact_Form_Search_Interface {

  protected $_latitude = NULL;
  protected $_longitude = NULL;
  protected $_distance = NULL;
  protected $_aclFrom = NULL;
  protected $_aclWhere = NULL;

  /**
   * Class constructor.
   *
   * @param array $formValues
   *
   * @throws Exception
   */
  public function __construct(&$formValues) {
    parent::__construct($formValues);

    // unset search profile and other search params if set
    unset($this->_formValues['uf_group_id']);
    unset($this->_formValues['component_mode']);
    unset($this->_formValues['operator']);

    if (!empty($this->_formValues)) {
      // add the country and state
      if (!empty($this->_formValues['country_id'])) {
        $this->_formValues['country'] = CRM_Core_PseudoConstant::country($this->_formValues['country_id']);
      }

      if (!empty($this->_formValues['state_province_id'])) {
        $this->_formValues['state_province'] = CRM_Core_PseudoConstant::stateProvince($this->_formValues['state_province_id']);
      }

      // use the address to get the latitude and longitude
      //CRM_Utils_Geocode_Google::format($this->_formValues);
      CRM_Utils_SAGE::geocode($this->_formValues);//NYSS 11478

      if (!is_numeric(CRM_Utils_Array::value('geo_code_1', $this->_formValues)) ||
        !is_numeric(CRM_Utils_Array::value('geo_code_2', $this->_formValues)) ||
        !isset($this->_formValues['distance'])
      ) {
        CRM_Core_Error::fatal(ts('Could not geocode input'));
      }

      $this->_latitude = $this->_formValues['geo_code_1'];
      $this->_longitude = $this->_formValues['geo_code_2'];

      if ($this->_formValues['prox_distance_unit'] == "miles") {
        $conversionFactor = 1609.344;
      }
      else {
        $conversionFactor = 1000;
      }
      $this->_distance = $this->_formValues['distance'] * $conversionFactor;
    }
    $this->_group = CRM_Utils_Array::value('group', $this->_formValues);

    $this->_tag = CRM_Utils_Array::value('tag', $this->_formValues);

    $this->_columns = array(
      ts('&nbsp;') => 'contact_type', //NYSS 4899
      ts('Name') => 'sort_name',
      ts('Street Address') => 'street_address',
      ts('City') => 'city',
      ts('Postal Code') => 'postal_code',
      ts('State') => 'state_province',
      //ts('Country') => 'country',//NYSS
    );
  }

  /**
   * Get the query object for this selector.
   *
   * @return CRM_Contact_BAO_Query
   */
  public function getQueryObj() {
    return $this->_query;
  }

  /**
   * @param CRM_Core_Form $form
   */
  public function buildForm(&$form) {

    $config = CRM_Core_Config::singleton();
    $countryDefault = $config->defaultContactCountry;

    $form->add('text', 'distance', ts('Distance'), NULL, TRUE);

    $proxUnits = array('km' => ts('km'), 'miles' => ts('miles'));
    $form->add('select', 'prox_distance_unit', ts('Units'), $proxUnits, TRUE);

    $form->add('text',
      'street_address',
      ts('Street Address')
    );

    $form->add('text',
      'city',
      ts('City')
    );

    $form->add('text',
      'postal_code',
      ts('Postal Code')
    );

    $defaults = array();
    if ($countryDefault) {
      $defaults['country_id'] = $countryDefault;
    }
    $form->addChainSelect('state_province_id');

    $country = array('' => ts('- select -')) + CRM_Core_PseudoConstant::country();
    $form->add('select', 'country_id', ts('Country'), $country, TRUE, array('class' => 'crm-select2'));

    $form->add('text', 'geo_code_1', ts('Latitude'));
    $form->add('text', 'geo_code_2', ts('Longitude'));

    $group = array('' => ts('- any group -')) + CRM_Core_PseudoConstant::nestedGroup();
    $form->addElement('select', 'group', ts('Group'), $group, array('class' => 'crm-select2 huge'));

    $tag = array('' => ts('- any tag -')) + CRM_Core_PseudoConstant::get('CRM_Core_DAO_EntityTag', 'tag_id', array('onlyActive' => FALSE));
    $form->addElement('select', 'tag', ts('Tag'), $tag, array('class' => 'crm-select2 huge'));

    /**
     * You can define a custom title for the search form
     */
    $this->setTitle('Proximity Search');

    /**
     * if you are using the standard template, this array tells the template what elements
     * are part of the search criteria
     */
    $form->assign('elements', array(
      'distance',
      'prox_distance_unit',
      'street_address',
      'city',
      'postal_code',
      'country_id',
      'state_province_id',
      'group',
      'tag',
    ));
  }

  /**
   * @param int $offset
   * @param int $rowcount
   * @param null $sort
   * @param bool $includeContactIDs
   * @param bool $justIDs
   *
   * @return string
   */
  public function all(
    $offset = 0, $rowcount = 0, $sort = NULL,
    $includeContactIDs = FALSE, $justIDs = FALSE
  ) {
    if ($justIDs) {
      $selectClause = "contact_a.id as contact_id";
    }
    else {
      //NYSS add contact type
      $selectClause = "
contact_a.id           as contact_id    ,
contact_a.sort_name    as sort_name     ,
contact_a.contact_type as contact_type  ,
address.street_address as street_address,
address.city           as city          ,
address.postal_code    as postal_code   ,
state_province.name    as state_province,
country.name           as country
";
    }

    return $this->sql($selectClause,
      $offset, $rowcount, $sort,
      $includeContactIDs, NULL
    );
  }

  /**
   * Override sql() function to use the Query object rather than generating on the form.
   *
   * @param string $selectClause
   * @param int $offset
   * @param int $rowcount
   * @param null $sort
   * @param bool $includeContactIDs
   * @param null $groupBy
   *
   * @return string
   */
  public function sql(
    $selectClause,
    $offset = 0,
    $rowcount = 0,
    $sort = NULL,
    $includeContactIDs = FALSE,
    $groupBy = NULL
  ) {

    $isCountOnly = FALSE;
    if ($selectClause === 'count(distinct contact_a.id) as total') {
      $isCountOnly = TRUE;
    }

    $searchParams = [
      ['prox_distance_unit', '=', $this->_formValues['prox_distance_unit'], 0, 0],
      ['prox_distance', '=', $this->_formValues['distance'], 0, 0],
      ['prox_geo_code_1', '=', $this->_formValues['geo_code_1'], 0, 0],
      ['prox_geo_code_2', '=', $this->_formValues['geo_code_2'], 0, 0],
    ];
    if (!empty($this->_formValues['group'])) {
      $searchParams[] = ['group', '=', ['IN', (array) $this->_formValues['group']][1], 0, 0];
    }
    if (!empty($this->_formValues['tag'])) {
      $searchParams[] = ['contact_tags', '=', ['IN', (array) $this->_formValues['tag']][1], 0, 0];
    }

    $display = array_fill_keys(['city', 'state_province', 'country', 'postal_code', 'street_address', 'display_name', 'sort_name'], 1);
    if ($selectClause === 'contact_a.id as contact_id') {
      // Not sure when this would happen but calling all with 'justIDs' gets us here.
      $display = ['contact_id' => 1];
    }

    $this->_query = new CRM_Contact_BAO_Query($searchParams, $display);
    return $this->_query->searchQuery(
      $offset,
      $rowcount,
      $sort,
      $isCountOnly,
      $includeContactIDs,
      FALSE,
      $isCountOnly,
      $returnQuery = TRUE
    );

  }

  /**
   * @return string
   */
  public function from() {
    //unused
    return '';
  }
  /**
   * @param bool $includeContactIDs
   *
   * @return string
   */
  public function where($includeContactIDs = FALSE) {
    $params = array();
    $clause = array();

    $where = CRM_Contact_BAO_ProximityQuery::where($this->_latitude,
      $this->_longitude,
      $this->_distance,
      'address'
    );

    if ($this->_tag) {
      $where .= "
AND t.tag_id = {$this->_tag}
";
    }
    if ($this->_group) {
      $where .= "
AND cgc.group_id = {$this->_group}
 ";
    }

    $where .= " AND contact_a.is_deleted != 1 ";

    if ($this->_aclWhere) {
      $where .= " AND {$this->_aclWhere} ";
    }

    //NYSS
    if (!empty($this->_formValues['contact_type']) ) {
      $where .= " AND contact_a.contact_type LIKE '%{$this->_formValues['contact_type']}%'";
    }

    //NYSS standard clauses
    $where .= " AND is_deleted = 0 AND is_deceased = 0 ";

    return $this->whereClause($where, $params);
  }

  /**
   * @return string
   */
  public function templateFile() {
    return 'CRM/Contact/Form/Search/Custom/Proximity.tpl';
  }

  /**
   * @return array|null
   */
  public function setDefaultValues() {
    if (!empty($this->_formValues)) {
      return $this->_formValues;
    }
    $config = CRM_Core_Config::singleton();
    $countryDefault = $config->defaultContactCountry;
    $stateprovinceDefault = $config->defaultContactStateProvince;
    $defaults = array();

    if ($countryDefault) {
      if ($countryDefault == '1228' || $countryDefault == '1226') {
        $defaults['prox_distance_unit'] = 'miles';
      }
      else {
        $defaults['prox_distance_unit'] = 'km';
      }
      $defaults['country_id'] = $countryDefault;
      if ($stateprovinceDefault) {
        $defaults['state_province_id'] = $stateprovinceDefault;
      }
      return $defaults;
    }
    return NULL;
  }

  //NYSS 4899
  /**
   * @param $row
   */
  public function alterRow(&$row) {
    $row['contact_type' ] = 
      CRM_Contact_BAO_Contact_Utils::getImage( $row['contact_type'],
        false,
        $row['contact_id']
    );
  }

  /**
   * @param $title
   */
  public function setTitle($title) {
    if ($title) {
      CRM_Utils_System::setTitle($title);
    }
    else {
      CRM_Utils_System::setTitle(ts('Search'));
    }
  }

  /**
   * Validate form input.
   *
   * @param array $fields
   * @param array $files
   * @param CRM_Core_Form $self
   *
   * @return array
   *   Input errors from the form.
   */
  public function formRule($fields, $files, $self) {
    $this->addGeocodingData($fields);

    if (!is_numeric(CRM_Utils_Array::value('geo_code_1', $fields)) ||
      !is_numeric(CRM_Utils_Array::value('geo_code_2', $fields)) ||
      !isset($fields['distance'])
    ) {
      $errorMessage = ts('Could not determine co-ordinates for provided data');
      return array_fill_keys(['street_address', 'city', 'postal_code', 'country_id', 'state_province_id'], $errorMessage);
    }
    return [];
  }

  /**
   * Add the geocoding data to the fields supplied.
   *
   * @param array $fields
   */
  protected function addGeocodingData(&$fields) {
    if (!empty($fields['country_id'])) {
      $fields['country'] = CRM_Core_PseudoConstant::country($fields['country_id']);
    }

    if (!empty($fields['state_province_id'])) {
      $fields['state_province'] = CRM_Core_PseudoConstant::stateProvince($fields['state_province_id']);
    }
    CRM_Core_BAO_Address::addGeocoderData($fields);
  }

}
