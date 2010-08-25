<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'CRM/Core/DAO/OptionValue.php';
require_once 'CRM/Core/DAO/OptionGroup.php';

class CRM_Core_BAO_OptionValue extends CRM_Core_DAO_OptionValue 
{
    /**
     * class constructor
     */
    function __construct( ) 
    {
        parent::__construct( );
    }
    
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
     * @return object CRM_Core_BAO_OptionValue object
     * @access public
     * @static
     */
    static function retrieve( &$params, &$defaults ) 
    {
        $optionValue = new CRM_Core_DAO_OptionValue( );
        $optionValue->copyValues( $params );
        if ( $optionValue->find( true ) ) {
            CRM_Core_DAO::storeValues( $optionValue, $defaults );
            return $optionValue;
        }
        return null;
    }

    /**
     * update the is_active flag in the db
     *
     * @param int      $id        id of the database record
     * @param boolean  $is_active value we want to set the is_active field
     *
     * @return Object             DAO object on sucess, null otherwise
     * @static
     */
    static function setIsActive( $id, $is_active ) 
    {
        return CRM_Core_DAO::setFieldValue( 'CRM_Core_DAO_OptionValue', $id, 'is_active', $is_active );
    }

    /**
     * Function to add an Option Value
     *
     * @param array $params reference array contains the values submitted by the form
     * @param array $ids    reference array contains the id
     * 
     * @access public
     * @static 
     * @return object
     */
    static function add(&$params, &$ids) 
    {
        $params['is_active'  ] =  CRM_Utils_Array::value( 'is_active', $params, false );
        $params['is_default' ] =  CRM_Utils_Array::value( 'is_default', $params, false );
        $params['is_optgroup'] =  CRM_Utils_Array::value( 'is_optgroup', $params, false );
        $params['filter'     ] =  CRM_Utils_Array::value( 'filter', $params, false );

        // action is taken depending upon the mode
        $optionValue               = new CRM_Core_DAO_OptionValue( );
        $optionValue->copyValues( $params );;
        
        if ( CRM_Utils_Array::value( 'is_default', $params ) ) {
            $query = 'UPDATE civicrm_option_value SET is_default = 0 WHERE  option_group_id = %1';
            
            // tweak default reset, and allow multiple default within group. 
            if ( $resetDefaultFor = CRM_Utils_Array::value( 'reset_default_for',  $params ) ) {
                if ( is_array( $resetDefaultFor ) ) {
                    $colName = key( $resetDefaultFor );
                    $colVal  = $resetDefaultFor[$colName];
                    $query  .= " AND ( $colName IN (  $colVal ) )";
                }
            }
            
            $p = array( 1 => array( $params['option_group_id'], 'Integer' ) );
            CRM_Core_DAO::executeQuery( $query, $p );
        }
        
        $groupName = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionGroup', 
                                                  $params['option_group_id'], 'name', 'id' );
        require_once 'CRM/Core/OptionGroup.php';
        if ( in_array($groupName, CRM_Core_OptionGroup::$_domainIDGroups) ) {
            $optionValue->domain_id = CRM_Core_Config::domainID( );
        }

        $optionValue->id = CRM_Utils_Array::value( 'optionValue', $ids );
        $optionValue->save( );
        return $optionValue;
    }
    
    /**
     * Function to delete Option Value 
     * 
     * @param  int  $optionGroupId     Id of the Option Group to be deleted.
     * 
     * @return void
     * 
     * @access public
     * @static
     */
    static function del($optionValueId) 
    {
        $optionValue = new CRM_Core_DAO_OptionValue( );
        $optionValue->id = $optionValueId;
        require_once 'CRM/Core/Action.php';
        if ( self::updateRecords($optionValueId, CRM_Core_Action::DELETE) ){
            return $optionValue->delete();        
        }
        return false;
    }

    /**
     * Function to retrieve activity type label and decription
     *
     * @param int     $activityTypeId  activity type id
     * 
     * @return array     lable and decription
     * @static
     * @access public
     */
    static function getActivityTypeDetails(  $activityTypeId ) 
    {
        $query =
  "SELECT civicrm_option_value.label, civicrm_option_value.description
   FROM civicrm_option_value
        LEFT JOIN civicrm_option_group ON ( civicrm_option_value.option_group_id = civicrm_option_group.id )
   WHERE civicrm_option_group.name = 'activity_type'
         AND civicrm_option_value.value =  {$activityTypeId} ";

        $dao   =& CRM_Core_DAO::executeQuery( $query );
        
        $dao->fetch( );

        return array( $dao->label, $dao->description );
    }

    /**
     * Get the Option Value title.
     *
     * @param int $id id of Option Value
     * @return string title 
     *
     * @access public
     * @static
     *
     */

    public static function getTitle( $id )
     {
         return CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionValue', $id, 'label' );
     }

    
    /**
     * updates contacts affected by the option value passed.
     *
     * @param Integer $optionValueId     the option value id.
     * @param int     $action            the action describing whether prefix/suffix was UPDATED or DELETED
     *
     * @return void
     */
    static function updateRecords(&$optionValueId, $action) {
        //finding group name
        $optionValue = new CRM_Core_DAO_OptionValue( );
        $optionValue->id = $optionValueId;
        $optionValue->find(true);
        
        $optionGroup = new CRM_Core_DAO_OptionGroup( );
        $optionGroup->id = $optionValue->option_group_id;
        $optionGroup->find(true);
        
        $gName = $optionGroup->name; //group name
        $value = $optionValue->value; //value
        
        // get the proper group name & affected field name
        $individuals      = array('gender'              => 'gender_id', 
                                  'individual_prefix'   => 'prefix_id', 
                                  'individual_suffix'   => 'suffix_id');
        $contributions    = array('payment_instrument'  => 'payment_instrument_id');
        $activities       = array('activity_type'       => 'activity_type_id');
        $participant      = array('participant_role'    => 'role_id');
        $eventType        = array('event_type'          => 'event_type_id');
        $aclRole          = array('acl_role'            => 'acl_role_id');

        $all = array_merge($individuals, $contributions, $activities, $participant, $eventType, $aclRole);
        $fieldName = '';
        
        foreach($all as $name => $id) {
            if ($gName == $name) {
                $fieldName = $id;
            }
        }
        if ($fieldName == '') return true;
        
        if (array_key_exists($gName, $individuals)) {
            require_once 'CRM/Contact/BAO/Contact.php';
            $contactDAO = new CRM_Contact_DAO_Contact();
            
            $contactDAO->$fieldName = $value;
            $contactDAO->find();
            
            while ($contactDAO->fetch()) {
                if ($action == CRM_Core_Action::DELETE) {
                    $contact = new CRM_Contact_DAO_Contact();
                    $contact->id = $contactDAO->id;
                    $contact->find(true);
                    
                    // make sure dates doesn't get reset
                    $contact->birth_date    = CRM_Utils_Date::isoToMysql($contact->birth_date); 
                    $contact->deceased_date = CRM_Utils_Date::isoToMysql($contact->deceased_date); 
                    $contact->$fieldName = 'NULL';
                    $contact->save();
                }
            }

            return true;
        }
        
        if (array_key_exists($gName, $contributions)) {
            require_once 'CRM/Contribute/DAO/Contribution.php';
            $contribution = new CRM_Contribute_DAO_Contribution();
            $contribution->$fieldName = $value;
            $contribution->find();
            while ($contribution->fetch()) {
                if ($action == CRM_Core_Action::DELETE) {
                    $contribution->$fieldName = 'NULL';
                    $contribution->save();
                }
            }
            return true;
        }
        
        if (array_key_exists($gName, $activities)) {
            require_once 'CRM/Activity/DAO/Activity.php';
            $activity = new CRM_Activity_DAO_Activity( );
            $activity->$fieldName = $value;
            $activity->find();
            while ($activity->fetch()) {
                $activity->delete();
            }
            return true;
        }
        
        //delete participant role, type and event type option value
        if (array_key_exists($gName, $participant)) {
            require_once 'CRM/Event/DAO/Participant.php';
            $participantValue = new CRM_Event_DAO_Participant( );
            $participantValue->$fieldName = $value;
            if ( $participantValue->find(true)) {
                return false;
            }
            return true;
        }

        //delete event type option value
        if (array_key_exists($gName, $eventType)) {
            require_once 'CRM/Event/DAO/Event.php';
            $event = new CRM_Event_DAO_Event( );
            $event->$fieldName = $value;
            if ( $event->find(true) ) {
                return false;
            }
            return true;
        }

        //delete acl_role option value
        if (array_key_exists( $gName, $aclRole )) {
            require_once 'CRM/ACL/DAO/EntityRole.php';
            require_once 'CRM/ACL/DAO/ACL.php';
            $entityRole = new CRM_ACL_DAO_EntityRole( );
            $entityRole->$fieldName = $value;

            $aclDAO = new CRM_ACL_DAO_ACL( );
            $aclDAO->entity_id = $value;
            if ( $entityRole->find(true) || $aclDAO->find(true)) {
                return false;
            }
            return true;
        }
    }
}


