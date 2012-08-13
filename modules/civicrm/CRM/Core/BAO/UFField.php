<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id$
 *
 */

/**
 * This class contains function for UFField
 *
 */
class CRM_Core_BAO_UFField extends CRM_Core_DAO_UFField {

  /**
   * Takes a bunch of params that are needed to match certain criteria and
   * retrieves the relevant objects. Typically the valid params are only
   * contact_id. We'll tweak this function to be more full featured over a period
   * of time. This is the inverse function of create. It also stores all the retrieved
   * values in the default array
   *
   * @param array $params   (reference ) an assoc array of name/value pairs
   * @param array $defaults (reference ) an assoc array to hold the flattened values
   *
   * @return object CRM_Core_BAO_UFField object
   * @access public
   * @static
   */
  static function retrieve(&$params, &$defaults) {
    return CRM_Core_DAO::commonRetrieve('CRM_Core_DAO_UFField', $params, $defaults);
  }

  /**
   * Get the form title.
   *
   * @param int $id id of uf_form
   *
   * @return string title
   *
   * @access public
   * @static
   *
   */
  public static function getTitle($id) {
    return CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFField', $groupId, 'title');
  }

  /**
   * update the is_active flag in the db
   *
   * @param int      $id         id of the database record
   * @param boolean  $is_active  value we want to set the is_active field
   *
   * @return Object              DAO object on sucess, null otherwise
   * @access public
   * @static
   */
  static function setIsActive($id, $is_active) {
    //check if custom data profile field is disabled
    if ($is_active) {
      if (CRM_Core_BAO_UFField::checkUFStatus($id)) {
        return CRM_Core_DAO::setFieldValue('CRM_Core_DAO_UFField', $id, 'is_active', $is_active);
      }
      else {
        CRM_Core_Session::setStatus(ts('Cannot enable this UF field since the used custom field is disabled.'));
      }
    }
    else {
      return CRM_Core_DAO::setFieldValue('CRM_Core_DAO_UFField', $id, 'is_active', $is_active);
    }
  }

  /**
   * Delete the profile Field.
   *
   * @param int  $id    Field Id
   *
   * @return boolean
   *
   * @access public
   * @static
   *
   */
  public static function del($id) {
    //delete  field field
    $field = new CRM_Core_DAO_UFField();
    $field->id = $id;
    $field->delete();
    return TRUE;
  }

  /**
   * Function to check duplicate for duplicate field in a group
   *
   * @param array $params an associative array with field and values
   * @ids   array $ids    array that containd ids
   *
   *@access public
   *@static
   */
  public static function duplicateField($params, $ids) {
    $ufField = new CRM_Core_DAO_UFField();
    $ufField->uf_group_id = CRM_Utils_Array::value('uf_group', $ids);
    $ufField->field_type = $params['field_name'][0];
    $ufField->field_name = $params['field_name'][1];
    $ufField->location_type_id = (CRM_Utils_Array::value(2, $params['field_name'])) ? $params['field_name'][2] : 'NULL';
    $ufField->phone_type_id = CRM_Utils_Array::value(3, $params['field_name']);

    if (CRM_Utils_Array::value('uf_field', $ids)) {
      $ufField->whereAdd("id <> " . CRM_Utils_Array::value('uf_field', $ids));
    }

    return $ufField->find(TRUE);
  }

  /**
   * function to add the UF Field
   *
   * @param array $params (reference) array containing the values submitted by the form
   * @param array $ids    (reference) array containing the id
   *
   * @return object CRM_Core_BAO_UFField object
   *
   * @access public
   * @static
   *
   */
  static function add(&$params, &$ids) {
    // set values for uf field properties and save
    $ufField             = new CRM_Core_DAO_UFField();
    $ufField->field_type = $params['field_name'][0];
    $ufField->field_name = $params['field_name'][1];

    //should not set location type id for Primary
    $locationTypeId = CRM_Utils_Array::value(2, $params['field_name']);
    if ($locationTypeId) {
      $ufField->location_type_id = $locationTypeId;
    }
    else {
      $ufField->location_type_id = 'null';
    }

    $ufField->phone_type_id = CRM_Utils_Array::value(3, $params['field_name'], 'NULL');
    $ufField->listings_title = CRM_Utils_Array::value('listings_title', $params);
    $ufField->visibility = CRM_Utils_Array::value('visibility', $params);
    $ufField->help_pre = CRM_Utils_Array::value('help_pre', $params);
    $ufField->help_post = CRM_Utils_Array::value('help_post', $params);
    $ufField->label = CRM_Utils_Array::value('label', $params);
    $ufField->is_required = CRM_Utils_Array::value('is_required', $params, FALSE);
    $ufField->is_active = CRM_Utils_Array::value('is_active', $params, FALSE);
    $ufField->in_selector = CRM_Utils_Array::value('in_selector', $params, FALSE);
    $ufField->is_view = CRM_Utils_Array::value('is_view', $params, FALSE);
    $ufField->is_registration = CRM_Utils_Array::value('is_registration', $params, FALSE);
    $ufField->is_match = CRM_Utils_Array::value('is_match', $params, FALSE);
    $ufField->is_searchable = CRM_Utils_Array::value('is_searchable', $params, FALSE);

    // fix for CRM-316
    $oldWeight = NULL;

    if (CRM_Utils_Array::value('field_id', $params)) {
      $oldWeight = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFField', $params['field_id'], 'weight', 'id');
    }
    $fieldValues = array('uf_group_id' => $params['group_id']);
    $ufField->weight = CRM_Utils_Weight::updateOtherWeights('CRM_Core_DAO_UFField', $oldWeight, $params['weight'], $fieldValues);

    // need the FKEY - uf group id
    $ufField->uf_group_id = CRM_Utils_Array::value('uf_group', $ids, FALSE);
    $ufField->id = CRM_Utils_Array::value('uf_field', $ids, FALSE);

    return $ufField->save();
  }

  /**
   * Function to enable/disable profile field given a custom field id
   *
   * @param int      $customFieldId     custom field id
   * @param boolean  $is_active         set the is_active field
   *
   * @return void
   * @static
   * @access public
   */
  static function setUFField($customFieldId, $is_active) {
    //find the profile id given custom field
    $ufField = new CRM_Core_DAO_UFField();
    $ufField->field_name = "custom_" . $customFieldId;

    $ufField->find();
    while ($ufField->fetch()) {
      //enable/ disable profile
      CRM_Core_BAO_UFField::setIsActive($ufField->id, $is_active);
    }
  }

  /**
   * Function to copy exisiting profile fields to
   * new profile from the already built profile
   *
   * @param int      $old_id  from which we need to copy
   * @param boolean  $new_id  in which to copy
   *
   * @return void
   * @static
   * @access public
   */
  static function copy($old_id, $new_id) {
    $ufField = new CRM_Core_DAO_UFField();
    $ufField->uf_group_id = $old_id;
    $ufField->find();
    while ($ufField->fetch()) {
      //copy the field records as it is on new ufgroup id
      $ufField->uf_group_id = $new_id;
      $ufField->id = NULL;
      $ufField->save();
    }
  }

  /**
   * Function to delete profile field given a custom field
   *
   * @param int   $customFieldId      ID of the custom field to be deleted
   *
   * @return void
   *
   * @static
   * @access public
   */
  function delUFField($customFieldId) {
    //find the profile id given custom field id
    $ufField = new CRM_Core_DAO_UFField();
    $ufField->field_name = "custom_" . $customFieldId;

    $ufField->find();
    while ($ufField->fetch()) {
      //enable/ disable profile
      CRM_Core_BAO_UFField::del($ufField->id);
    }
  }

  /**
   * Function to enable/disable profile field given a custom group id
   *
   * @param int      $customGroupId custom group id
   * @param boolean  $is_active value we want to set the is_active field
   *
   * @return void
   * @static
   * @access public
   */
  function setUFFieldStatus($customGroupId, $is_active) {
    //find the profile id given custom group id
    $queryString = "SELECT civicrm_custom_field.id as custom_field_id
                        FROM   civicrm_custom_field, civicrm_custom_group
                        WHERE  civicrm_custom_field.custom_group_id = civicrm_custom_group.id
                          AND  civicrm_custom_group.id = %1";
    $p = array(1 => array($customGroupId, 'Integer'));
    $dao = CRM_Core_DAO::executeQuery($queryString, $p);

    while ($dao->fetch()) {
      //enable/ disable profile
      CRM_Core_BAO_UFField::setUFField($dao->custom_field_id, $is_active);
    }
  }

  /**
   * Function to check the status of custom field used in uf fields
   *
   * @params  int $UFFieldId     uf field id
   *
   * @return boolean   false if custom field are disabled else true
   * @static
   * @access public
   */
  static function checkUFStatus($UFFieldId) {
    $fieldName = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFField', $UFFieldId, 'field_name');
    // return if field is not a custom field
    if (!$customFieldId = CRM_Core_BAO_CustomField::getKeyID($fieldName)) {
      return TRUE;
    }

    $customField = new CRM_Core_DAO_CustomField();
    $customField->id = $customFieldId;
    // if uf field is custom field
    if ($customField->find(TRUE)) {
      if (!$customField->is_active) {
        return FALSE;
      }
      else {
        return TRUE;
      }
    }
  }

  /*
   * Function to find out whether given profile group using Activity
   * Profile fields with contact fields
   */
  static function checkContactActivityProfileType($ufGroupId) {
    $ufGroup = new CRM_Core_DAO_UFGroup();
    $ufGroup->id = $ufGroupId;
    $ufGroup->find(TRUE);

    $profileTypes = array();
    if ($ufGroup->group_type) {
      $typeParts = explode(CRM_Core_DAO::VALUE_SEPARATOR, $ufGroup->group_type);
      $profileTypes = explode(',', $typeParts[0]);
    }

    if (empty($profileTypes)) {
      return FALSE;
    }
    $components = array('Contribution', 'Participant', 'Membership');
    if (!in_array('Activity', $profileTypes)) {
      return FALSE;
    }
    elseif (count($profileTypes) == 1) {
      return FALSE;
    }

    if ($index = array_search('Contact', $profileTypes)) {
      unset($profileTypes[$index]);
      if (count($profileTypes) == 1) {
        return TRUE;
      }
    }

    $contactTypes = array('Individual', 'Household', 'Organization');
    $subTypes = CRM_Contact_BAO_ContactType::subTypes();

    $profileTypeComponent = array_intersect($components, $profileTypes);
    if (!empty($profileTypeComponent) ||
      count(array_intersect($contactTypes, $profileTypes)) > 1 ||
      count(array_intersect($subTypes, $profileTypes)) > 1
    ) {
      return FALSE;
    }

    return TRUE;
  }

  /* Function to find out whether given profile group uses $required 
   * and/or $optionalprofile types
   *  
   * @param integer $ufGroupId  profile id
   * @param array   $required   array of types those are required
   * @param array   $optional   array of types those are optional
   *
   * @return boolean $valid  
   * @static
   */
  static function checkValidProfileType($ufGroupId, $required, $optional = NULL) {
    if (!is_array($required) || empty($required)) {
      return;
    }

    $ufGroup = new CRM_Core_DAO_UFGroup();
    $ufGroup->id = $ufGroupId;
    $ufGroup->find(TRUE);

    $profileTypes = array();
    if ($ufGroup->group_type) {
      $typeParts = explode(CRM_Core_DAO::VALUE_SEPARATOR, $ufGroup->group_type);
      $profileTypes = explode(',', $typeParts[0]);
    }

    if (empty($profileTypes)) {
      return FALSE;
    }

    $valid = TRUE;
    foreach ($required as $key => $val) {
      if (!in_array($val, $profileTypes)) {
        $valid = FALSE;
        break;
      }
    }

    if ($valid && is_array($optional)) {
      foreach ($optional as $key => $val) {
        if (in_array($val, $profileTypes)) {
          $valid = TRUE;
          break;
        }
      }
    }

    return $valid;
  }

  /**
   * function to check for mix profile fields (eg: individual + other contact types)
   *
   * @params int     $ufGroupId  uf group id
   * @params boolean $check      this is to check mix profile (if true it will check if profile is
   *                             pure ie. it contains only one contact type)
   *
   * @return  true for mix profile else false
   * @acess public
   * @static
   */
  static function checkProfileType($ufGroupId) {
    $ufGroup = new CRM_Core_DAO_UFGroup();
    $ufGroup->id = $ufGroupId;
    $ufGroup->find(TRUE);

    $profileTypes = array();
    if ($ufGroup->group_type) {
      $typeParts = explode(CRM_Core_DAO::VALUE_SEPARATOR, $ufGroup->group_type);
      $profileTypes = explode(',', $typeParts[0]);
    }

    //early return if new profile.
    if (empty($profileTypes)) {
      return FALSE;
    }

    //we need to unset Contact
    if (count($profileTypes) > 1) {
      $index = array_search('Contact', $profileTypes);
      if ($index !== FALSE) {
        unset($profileTypes[$index]);
      }
    }

    // suppress any subtypes if present
    CRM_Contact_BAO_ContactType::suppressSubTypes($profileTypes);

    $contactTypes = array('Contact', 'Individual', 'Household', 'Organization');
    $components   = array('Contribution', 'Participant', 'Membership', 'Activity');
    $fields       = array();

    // check for mix profile condition
    if (count($profileTypes) > 1) {
      //check the there are any components include in profile
      foreach ($components as $value) {
        if (in_array($value, $profileTypes)) {
          return TRUE;
        }
      }
      //check if there are more than one contact types included in profile
      if (count($profileTypes) > 1) {
        return TRUE;
      }
    }
    elseif (count($profileTypes) == 1) {
      // note for subtype case count would be zero
      $profileTypes = array_values($profileTypes);
      if (!in_array($profileTypes[0], $contactTypes)) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * function to get the profile type (eg: individual/organization/household)
   *
   * @param int      $ufGroupId     uf group id
   * @param boolean  $returnMixType this is true, then field type of  mix profile field is returned
   * @param boolean  $onlyPure      true if only pure profiles are required
   *
   * @return  profile group_type
   * @acess public
   * @static
   */
  static function getProfileType($ufGroupId, $returnMixType = TRUE, $onlyPure = FALSE, $skipComponentType = FALSE) {
    // profile types
    $contactTypes = array('Contact', 'Individual', 'Household', 'Organization');
    $subTypes = CRM_Contact_BAO_ContactType::subTypes();

    $components = array('Contribution', 'Participant', 'Membership', 'Activity');

    $ufGroup            = new CRM_Core_DAO_UFGroup();
    $ufGroup->id        = $ufGroupId;
    $ufGroup->is_active = 1;

    $ufGroup->find(TRUE);

    $profileTypes = array();
    if ($ufGroup->group_type) {
      $typeParts = explode(CRM_Core_DAO::VALUE_SEPARATOR, $ufGroup->group_type);
      $profileTypes = explode(',', $typeParts[0]);
    }

    if ($onlyPure) {
      if (count($profileTypes) == 1) {
        return $profileTypes[0];
      }
      else {
        return NULL;
      }
    }

    //we need to unset Contact
    if (count($profileTypes) > 1) {
      $index = array_search('Contact', $profileTypes);
      if ($index !== FALSE) {
        unset($profileTypes[$index]);
      }
    }

    $profileType = $mixProfileType = NULL;

    // this case handles pure profile
    if (count($profileTypes) == 1) {
      $profileType = array_pop($profileTypes);
    }
    else {
      //check the there are any components include in profile
      $componentCount = array();
      foreach ($components as $value) {
        if (in_array($value, $profileTypes)) {
          $componentCount[] = $value;
        }
      }

      //check contact type included in profile
      $contactTypeCount = array();
      foreach ($contactTypes as $value) {
        if (in_array($value, $profileTypes)) {
          $contactTypeCount[] = $value;
        }
      }
      // subtype counter
      $subTypeCount = array();
      foreach ($subTypes as $value) {
        if (in_array($value, $profileTypes)) {
          $subTypeCount[] = $value;
        }
      }
      if (!$skipComponentType && count($componentCount) == 1) {
        $profileType = $componentCount[0];
      }
      elseif (count($componentCount) > 1) {
        $mixProfileType = $componentCount[1];
      }
      elseif (count($subTypeCount) == 1) {
        $profileType = $subTypeCount[0];
      }
      elseif (count($contactTypeCount) == 1) {
        $profileType = $contactTypeCount[0];
      }
      elseif (count($subTypeCount) > 1) {
        // this is mix subtype profiles
        $mixProfileType = $subTypeCount[1];
      }
      elseif (count($contactTypeCount) > 1) {
        // this is mix contact profiles
        $mixProfileType = $contactTypeCount[1];
      }
    }

    if ($mixProfileType) {
      if ($returnMixType) {
        return $mixProfileType;
      }
      else {
        return 'Mixed';
      }
    }
    else {
      return $profileType;
    }
  }

  /**
   * function to check for mix profiles groups (eg: individual + other contact types)
   *
   * @return  true for mix profile group else false
   * @acess public
   * @static
   */
  static function checkProfileGroupType($ctype) {
    $ufGroup = new CRM_Core_DAO_UFGroup();

    $query = "
SELECT ufg.id as id
  FROM civicrm_uf_group as ufg, civicrm_uf_join as ufj
 WHERE ufg.id = ufj.uf_group_id
   AND ufj.module = 'User Registration'
   AND ufg.is_active = 1 ";

    $ufGroup = CRM_Core_DAO::executeQuery($query);

    $fields = array();
    $validProfiles = array('Individual', 'Organization', 'Household', 'Contribution');
    while ($ufGroup->fetch()) {
      $profileType = self::getProfileType($ufGroup->id);
      if (in_array($profileType, $validProfiles)) {
        continue;
      }
      elseif ($profileType) {
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * check for searchable or in selector field for given profile.
   *
   *@params int     $profileID profile id.
   *
   *@return boolean $result    true/false.
   */
  function checkSearchableORInSelector($profileID) {
    $result = FALSE;
    if (!$profileID) {
      return $result;
    }

    $query = "
SELECT  id 
  From  civicrm_uf_field 
 WHERE  (in_selector = 1 OR is_searchable = 1)
   AND  uf_group_id = {$profileID}";

    $ufFields = CRM_Core_DAO::executeQuery($query);
    while ($ufFields->fetch()) {
      $result = TRUE;
      break;
    }

    return $result;
  }

  /**
   *Reset In selector and is seachable values for given $profileID.
   *
   *@params int $profileID profile id.
   *
   *@return void.
   */
  function resetInSelectorANDSearchable($profileID) {
    if (!$profileID) {
      return;
    }
    $query = "UPDATE civicrm_uf_field SET in_selector = 0, is_searchable = 0 WHERE  uf_group_id = {$profileID}";
    CRM_Core_DAO::executeQuery($query);
  }

  /**
   * Add fields to $profileAddressFields as appropriate.
   * profileAddressFields is assigned to the template to tell it
   * what fields are in the profile address
   * that potentially should be copied to the Billing fields
   * we want to give precedence to 
   *   1) Billing & 
   *   2) then Primary designated as 'Primary
   *   3) location_type is primary
   *   4) if none of these apply then it just uses the first one
   *   
   *   as this will be used to
   * transfer profile address data to billing fields
   * http://issues.civicrm.org/jira/browse/CRM-5869
   * @param string $key Field key - e.g. street_address-Primary, first_name
   * @params array $profileAddressFields array of profile fields that relate to address fields
   */
  static function assignAddressField($key, &$profileAddressFields) {
    $billing_id = CRM_Core_BAO_LocationType::getBilling();
    list($prefixName, $index) = CRM_Utils_System::explode('-', $key, 2);
    
    //check for valid fields ( fields that are present in billing block )
    $validBillingFields = array(
      'first_name','middle_name','last_name','street_address',
      'supplemental_address_1','city','state_province',
      'postal_code','country'
    );

    if ( !in_array($prefixName, $validBillingFields) ) {
      return;
    }
    
    if (!empty($index) && (
        // it's empty so we set it OR
        !CRM_Utils_array::value($prefixName, $profileAddressFields)
        //we are dealing with billing id (precedence)
        || $index == $billing_id
        // we are dealing with primary & billing not set
        || ($index == 'Primary' && $profileAddressFields[$prefixName] != $billing_id)
        || ($index == CRM_Core_BAO_LocationType::getDefault()->id
          && $profileAddressFields[$prefixName] != $billing_id
          && $profileAddressFields[$prefixName] != 'Primary'
        )
      )) {
      $profileAddressFields[$prefixName] = $index;
    }
  }
}

