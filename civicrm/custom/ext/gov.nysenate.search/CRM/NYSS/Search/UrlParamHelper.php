<?php

/**
 * @note This is really just an exercise in reducing use of core override files.
 * This code was moved here from overrides of CRM_Contact_Form_Search_Criteria
 * and CRM_Contact_Form_Search.
 *
 * The entry point can be found in
 * CRM_Core_Form_Search::loadSearchParamsFromUrl()
 *
 * This makes use of CiviCRM's service container
 * and should therefore be registered and loaded through the service
 * container architecture.
 */

  class CRM_NYSS_Search_UrlParamHelper {

  public static string $SERVICE_NAME = 'nyss_search_param_helper';

  //NYSS 12133
  /**
   * Responsible to define URL params that can be used as search form params.
   * @note this code has been moved here from
   * CRM_Contact_Form_Search::setSearchParamFromUrl(&$form)
   */
  public function contactParams() {

    return array_merge(
      CRM_Utils_Array::collect('data_type', CRM_Contact_Form_Search_Criteria::getBasicSearchFields()),
      CRM_Utils_Array::collect('data_type', self::getDemographicsSearchFields()),
      CRM_Utils_Array::collect('data_type', self::getLocationSearchFields()),
      CRM_Utils_Array::collect('data_type', self::getChangeLogSearchFields()),
      CRM_Utils_Array::collect('data_type', self::getCustomSearchFields())
    );

  }

  //NYSS 12133
  /**
   * Return list of location fields that can be displayed for the Address search section.
   *
   * @param array
   */
  public static function getLocationSearchFields() {
    return array(
      'street_address' => ['title' => ts('Street Address'), 'data_type' => 'String'],
      'supplemental_address_1' => ['title' => ts('Supplemental Address 1'), 'data_type' => 'String'],
      'supplemental_address_2' => ['title' => ts('Supplemental Address 2'), 'data_type' => 'String'],
      'supplemental_address_3' => ['title' => ts('Supplemental Address 3'), 'data_type' => 'String'],
      'city' => ['title' => ts('City'), 'data_type' => 'String'],
      'postal_code' => ['title' => ts('Postal Code'), 'data_type' => 'String'],
      'country' => ['title' => ts('Country'), 'data_type' => 'Integer', 'select' => 'country'],
      'state_province' => ['title' => ts('State/Province'), 'data_type' => 'Integer', 'select' => 'stateProvince'],
      'county' => ['title' => ts('County'), 'data_type' => 'Integer', 'select' => 'county'],
      'address_name' => ['title' => ts('Address Name'), 'data_type' => 'String'],
      'street_number' => ['title' => ts('Street Number'), 'data_type' => 'String'],
      'street_name' => ['title' => ts('Street Name'), 'data_type' => 'String'],
      'street_unit' => ['title' => ts('Apt/Unit/Suite'), 'data_type' => 'String'],
    );
  }

  //NYSS 12133
  /**
   * Return list of demographic fields that can be displayed for the Demographics search section.
   *
   * @param array
   */
  public static function getDemographicsSearchFields() {
    return [
      'gender_id' => ['title' => ts('Gender'), 'data_type' => 'String'],
      'age_low' => ['title' => ts('Min Age'), 'data_type' => 'String'],
      'age_high' => ['title' => ts('Max Age'), 'data_type' => 'String'],
      'birth_date' => ['data_type' => 'String'],
      'deceased_date' => ['data_type' => 'String'],
      'is_deceased' => ['title' => ts('Deceased'), 'data_type' => 'Positive'],
    ];
  }

  //NYSS 12133
  public static function getChangeLogSearchFields() {
    return [
      'changed_by' => ['title' => ts('Modified By'), 'data_type' => 'String'],
      'log_date' => ['title' => NULL, 'data_type' => 'Positive'],
      'log_date_relative' => ['data_type' => 'String'],
    ];
  }

  //NYSS 12133
  /**
   * Return list of custom fields that can be displayed for the Custom Fields search section.
   *
   * @param array
   */
  public static function getCustomSearchFields() {
    $extends = array_merge(array('Contact', 'Individual', 'Household', 'Organization', 'Address'),
      CRM_Contact_BAO_ContactType::subTypes()
    );
    $groupDetails = CRM_Core_BAO_CustomGroup::getGroupDetail(NULL, TRUE,
      $extends
    );

    $customFields = [];
    foreach ($groupDetails as $key => $group) {
      foreach ($group['fields'] as $field) {
        $customFields['custom_' . $field['id']] = ['data_type' => $field['data_type']];
      }
    }

    return $customFields;
  }

}