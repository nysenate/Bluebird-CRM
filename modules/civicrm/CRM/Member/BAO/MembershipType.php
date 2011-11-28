<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

require_once 'CRM/Member/DAO/MembershipType.php';

class CRM_Member_BAO_MembershipType extends CRM_Member_DAO_MembershipType 
{

    /**
     * static holder for the default LT
     */
    static $_defaultMembershipType = null;
    

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
     * @return object CRM_Member_BAO_MembershipType object
     * @access public
     * @static
     */
    static function retrieve( &$params, &$defaults ) 
    {
        $membershipType = new CRM_Member_DAO_MembershipType( );
        $membershipType->copyValues( $params );
        if ( $membershipType->find( true ) ) {
            CRM_Core_DAO::storeValues( $membershipType, $defaults );
            return $membershipType;
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
        return CRM_Core_DAO::setFieldValue( 'CRM_Member_DAO_MembershipType', $id, 'is_active', $is_active );
    }

    /**
     * function to add the membership types
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
        $params['is_active'] =  CRM_Utils_Array::value( 'is_active', $params, false );
        
        // action is taken depending upon the mode
        $membershipType               = new CRM_Member_DAO_MembershipType( );
        
        $membershipType->copyValues( $params );
        
        $membershipType->domain_id = CRM_Core_Config::domainID( );

        $membershipType->id = CRM_Utils_Array::value( 'membershipType', $ids );
        $membershipType->member_of_contact_id = CRM_Utils_Array::value( 'memberOfContact', $ids );

        $membershipType->save( );

        return $membershipType;
    }

    /**
     * Function to delete membership Types 
     * 
     * @param int $membershipTypeId
     * @static
     */
    
    static function del($membershipTypeId ,  $skipRedirect = false) 
    {
        //check dependencies
        $check  = false;
        $status = array( );
        $dependancy = array(
                            'Membership'      => 'membership_type_id', 
                            'MembershipBlock' => 'membership_type_default'
                            );
        
        foreach ($dependancy as $name => $field) {
            require_once (str_replace('_', DIRECTORY_SEPARATOR, "CRM_Member_DAO_" . $name) . ".php");
            eval('$dao = new CRM_Member_DAO_' . $name . '();');
            $dao->$field = $membershipTypeId;
            if ($dao->find(true)) {
                $check = true;
                $status[] = $name;
            }
        }
        if ($check) {


            $cnt = 1;
            $message = ts('This membership type cannot be deleted due to following reason(s):' ); 
            if ( in_array( 'Membership', $status) ) {
                $deleteURL = CRM_Utils_System::url('civicrm/member/search', 'reset=1');
                $message .= '<br/>' . ts('%2. There are some contacts who have this membership type assigned to them. Search for contacts with this membership type on the <a href=\'%1\'>CiviMember >> Find Members</a> page. If you delete all memberships of this type, you will then be able to delete the membership type on this page. To delete the membership type, all memberships of this type should be deleted.', array(1 => $deleteURL, 2 => $cnt));
                $cnt++;
            }
            
            if ( in_array( 'MembershipBlock', $status) ) {
                $deleteURL = CRM_Utils_System::url('civicrm/admin/contribute', 'reset=1');
                $message .= '<br/>' . ts('%2. This Membership Type is being link to <a href=\'%1\'>Online Contribution page</a>. Please change/delete it in order to delete this Membership Type.', array(1 => $deleteURL, 2 => $cnt));
            }
            if ( ! $skipRedirect  ) {
              $session = CRM_Core_Session::singleton();
              CRM_Core_Session::setStatus($message);
              return CRM_Utils_System::redirect( CRM_Utils_System::url('civicrm/admin/member/membershipType', 'reset=1&action=browse'));
            }else{
             $error = array( );
             $error['is_error'] = 1;
             //don't translate as api error message are not translated
             $error['error_message'] = $message ;
             return $error;
            }
        }
        
        //delete from membership Type table
        require_once 'CRM/Member/DAO/MembershipType.php';
        $membershipType = new CRM_Member_DAO_MembershipType( );
        $membershipType->id = $membershipTypeId;
        
        //fix for membership type delete api
        $result = false;
        if ( $membershipType->find (true ) ) { 
            $membershipType->delete( );
            $result =  true;
        }
        
        return $result;
    }
    
    /**
     * Function to convert membership Type's 'start day' & 'rollover day' to human readable formats.
     * 
     * @param array $membershipType an array of membershipType-details.
     * @static
     */
    
    static function convertDayFormat( &$membershipType ) 
    {
        $periodDays = array(
                            'fixed_period_start_day',
                            'fixed_period_rollover_day'
                            );
        foreach ( $membershipType as $id => $details ) {
            foreach ( $periodDays as $pDay) {
                if ( CRM_Utils_Array::value($pDay, $details) ) {
                    $month = substr( $details[$pDay], 0, strlen($details[$pDay])-2);
                    $day   = substr( $details[$pDay],-2);    
                    $monthMap = array(
                                      '1'  => 'Jan',
                                      '2'  => 'Feb',
                                      '3'  => 'Mar',
                                      '4'  => 'Apr',
                                      '5'  => 'May',
                                      '6'  => 'Jun',
                                      '7'  => 'Jul',
                                      '8'  => 'Aug',
                                      '9'  => 'Sep',
                                      '10' => 'Oct',
                                      '11' => 'Nov',
                                      '12' => 'Dec'
                                      );
                    $membershipType[$id][$pDay] = $monthMap[$month].' '.$day; 
                }
            }
        }
    }
    
    /**
     * Function to get membership Types 
     * 
     * @param int $membershipTypeId
     * @static
     */
    static function getMembershipTypes( $public = true )
    {
        require_once 'CRM/Member/DAO/Membership.php';
        $membershipTypes = array();
        $membershipType = new CRM_Member_DAO_MembershipType( );
        $membershipType->is_active = 1;
        if (  $public ){
            $membershipType->visibility = 'Public';
        }
        $membershipType->orderBy(' weight');
        $membershipType->find();
        while ( $membershipType->fetch() ) {
            $membershipTypes[$membershipType->id] = $membershipType->name; 
        }
        $membershipType->free( );
        return $membershipTypes;
     }
    
    /**
     * Function to get membership Type Details 
     * 
     * @param int $membershipTypeId
     * @static
     */
    function getMembershipTypeDetails( $membershipTypeId ) 
    {
        require_once 'CRM/Member/DAO/Membership.php';
        $membershipTypeDetails = array();
        
        $membershipType = new CRM_Member_DAO_MembershipType( );
        $membershipType->is_active = 1;
        $membershipType->id = $membershipTypeId;
        if ( $membershipType->find(true) ) {
            CRM_Core_DAO::storeValues($membershipType, $membershipTypeDetails );
            $membershipType->free( );
            return   $membershipTypeDetails;
        } else {
            return null;
        }
    }

    /**
     * Function to calculate start date and end date for new membership 
     * 
     * @param int  $membershipTypeId membership type id
     * @param date $joinDate member since ( in mysql date format ) 
     * @param date $startDate start date ( in mysql date format ) 
     *
     * @return array associated array with  start date, end date and join date for the membership
     * @static
     */
    function getDatesForMembershipType( $membershipTypeId, $joinDate = null, $startDate = null, $endDate = null ) 
    {
        $membershipTypeDetails = self::getMembershipTypeDetails( $membershipTypeId );
        
        // convert all dates to 'Y-m-d' format.
        foreach ( array( 'joinDate', 'startDate', 'endDate' ) as $dateParam ) {
            if ( !empty( $$dateParam ) ) { 
                $$dateParam = CRM_Utils_Date::processDate( $$dateParam, null, false, 'Y-m-d' );
            }
        }
        if ( !$joinDate ) {
            $joinDate = date( 'Y-m-d' );
        }
        $actualStartDate = $joinDate;
        if ( $startDate ) {
            $actualStartDate = $startDate; 
        }
        
        $fixed_period_rollover = false;
        if ( CRM_Utils_Array::value( 'period_type', $membershipTypeDetails)  == 'rolling' ) {
            if ( !$startDate ) {
                $startDate = $joinDate;
            }
            $actualStartDate = $startDate;
        } else if ( CRM_Utils_Array::value( 'period_type', $membershipTypeDetails ) == 'fixed' ) {
            //calculate start date

            // today is always join date, in case of Online join date
            // is equal to current system date
            $toDay  = explode('-', $joinDate );

            // get year from join date
            $year  = $toDay[0];
            $month = $toDay[1];
            
            if ( $membershipTypeDetails['duration_unit'] == 'year' ) {

                //get start fixed day
                $startMonth     = substr( $membershipTypeDetails['fixed_period_start_day'], 0, 
                                          strlen($membershipTypeDetails['fixed_period_start_day'])-2);
                $startDay       = substr( $membershipTypeDetails['fixed_period_start_day'], -2 );

                $fixedStartDate = date('Y-m-d', mktime( 0, 0, 0, $startMonth, $startDay, $year ) );
                
                //get start rollover day
                $rolloverMonth     = substr( $membershipTypeDetails['fixed_period_rollover_day'], 0,
                                             strlen($membershipTypeDetails['fixed_period_rollover_day']) - 2 );
                $rolloverDay       = substr( $membershipTypeDetails['fixed_period_rollover_day'],-2);
                
                $fixedRolloverDate = date('Y-m-d', mktime( 0, 0, 0, $rolloverMonth, $rolloverDay, $year ) );
                
                //CRM-7825 -membership date rules are :
                //1. Membership should not be start in future.
                //2. rollover window should be subset of membership window.
                
                //store original fixed start date as per current year.
                $actualStartDate = $fixedStartDate;
                
                //store original fixed rollover date as per current year.
                $actualRolloverDate = $fixedRolloverDate;
                
                //make sure membership should not start in future.
                if ( $joinDate < $actualStartDate ) {
                    $actualStartDate = date('Y-m-d', mktime( 0, 0, 0, $startMonth, $startDay, $year - 1 ) );
                }
                
                //get the fixed end date here.
                $dateParts    = explode( '-', $actualStartDate );
                $fixedEndDate = date('Y-m-d',mktime( 0, 0, 0, 
                                                     $dateParts[1], 
                                                     $dateParts[2] - 1, 
                                                     $dateParts[0] + $membershipTypeDetails['duration_interval'] ) );
                
                //make sure rollover window should be 
                //subset of membership period window.
                if ( $fixedEndDate < $actualRolloverDate ) {
                    $actualRolloverDate = date('Y-m-d', mktime( 0, 0, 0, $rolloverMonth, $rolloverDay, $year - 1 ) );
                }
                if ( $actualRolloverDate < $actualStartDate ) {
                    $actualRolloverDate = date('Y-m-d', mktime( 0, 0, 0, $rolloverMonth, $rolloverDay, $year + 1 ) );
                }
                
                //do check signup is in rollover window.
                if ( $actualRolloverDate <= $joinDate ) {
                    $fixed_period_rollover = true;
                }
                
                if ( !$startDate ) {
                    $startDate = $actualStartDate;
                }
            } else if ( $membershipTypeDetails['duration_unit'] == 'month' ) {
                //here start date is always from start of the joining
                //month irrespective when you join during the month,
                //so if you join on 1 Jan or 15 Jan your start
                //date will always be 1 Jan
                if ( !$startDate ) {
                    $actualStartDate = $startDate = $year.'-'.$month.'-01';
                }
            }
        }

        //calculate end date if it is not passed by user
        if ( !$endDate ) {
            //end date calculation
            $date  = explode('-', $actualStartDate );
            $year  = $date[0];
            $month = $date[1];
            $day   = $date[2];
            
            switch ( $membershipTypeDetails['duration_unit'] ) {
                
            case 'year' :
                $year  = $year + $membershipTypeDetails['duration_interval'];
                //extend membership date by duration interval.
                if ( $fixed_period_rollover ) {
                    $year += 1;
                }
                
                break;
            case 'month':
                $month = $month + $membershipTypeDetails['duration_interval'];
                
                if ( $fixed_period_rollover ) {
                    //Fix Me: Currently we don't allow rollover if
                    //duration interval is month
                }
                
                break;
            case 'day':
                $day   = $day + $membershipTypeDetails['duration_interval'];
                
                if ( $fixed_period_rollover ) {
                    //Fix Me: Currently we don't allow rollover if
                    //duration interval is day
                }
                
                break;
            }

            if ( $membershipTypeDetails['duration_unit'] =='lifetime' ) {
                $endDate = null;
            } else {
                $endDate = date('Y-m-d',mktime( 0, 0, 0, $month, $day-1, $year));
            }
        }

        $reminderDate    = null;
        $membershipDates = array( );

        if ( isset( $membershipTypeDetails["renewal_reminder_day"] ) &&
             $membershipTypeDetails["renewal_reminder_day"]          &&
             $endDate ) {
            $date  = explode('-', $endDate );
            $year  = $date[0];
            $month = $date[1];
            $day   = $date[2];
            $day   = $day - $membershipTypeDetails["renewal_reminder_day"];
            $reminderDate = date( 'Y-m-d', mktime( 0, 0, 0, $month, $day-1, $year) );
        }

        $dates = array(  'start_date'    => 'startDate',
                         'end_date'      => 'endDate',
                         'join_date'     => 'joinDate',
                         'reminder_date' => 'reminderDate' );
        foreach ( $dates as $varName => $valName )  {
            $membershipDates[$varName] = CRM_Utils_Date::customFormat( $$valName,'%Y%m%d');
        } 

        if ( !$endDate ) {
            $membershipDates['reminder_date'] = null;
        }

        return $membershipDates;
    }

    /**
     * Function to calculate start date and end date for renewal membership 
     * 
     * @param int $membershipId 
     * @param $changeToday 
     * @param int $membershipTypeID - if provided, overrides the membership type of the $membershipID membership
     *
     * CRM-7297 Membership Upsell - Added $membershipTypeID param to facilitate calculations of dates when membership type changes
     * @return Array array fo the start date, end date and join date of the membership
     * @static
     */
    function getRenewalDatesForMembershipType( $membershipId, $changeToday = null, $membershipTypeID = null ) 
    {
        require_once 'CRM/Member/BAO/Membership.php';
        require_once 'CRM/Member/BAO/MembershipStatus.php';
        $params = array('id' => $membershipId);
        $membershipDetails = CRM_Member_BAO_Membership::getValues( $params, $values );
        $statusID          = $membershipDetails[$membershipId]->status_id;
        // CRM-7297 Membership Upsell
        if ( is_null( $membershipTypeID ) ) {
	        $membershipTypeDetails = self::getMembershipTypeDetails( $membershipDetails[$membershipId]->membership_type_id );
        } else {
        	$membershipTypeDetails = self::getMembershipTypeDetails( $membershipTypeID );
        }
        $statusDetails  = CRM_Member_BAO_MembershipStatus::getMembershipStatus($statusID);
        
        if ( $statusDetails['is_current_member'] == 1 ) {
            $startDate    = $membershipDetails[$membershipId]->start_date;
            // CRM=7297 Membership Upsell: we need to handle null end_date in case we are switching 
            // from a lifetime to a different membership type
            if ( is_null( $membershipDetails[$membershipId]->end_date ) ) {
            	$date = date('Y-m-d');
            } else {
            	$date = $membershipDetails[$membershipId]->end_date;
            }
            $date = explode('-', $date );
            $logStartDate = date('Y-m-d', mktime( 0, 0, 0,
                                                  (double) $date[1],
                                                  (double) ($date[2] + 1),
                                                  (double) $date[0] ) );
            $date         = explode('-', $logStartDate );
            
            $year  = $date[0];
            $month = $date[1];
            $day   = $date[2];
            
            switch ( $membershipTypeDetails['duration_unit'] ) {
            case 'year' :
                $year  = $year   + $membershipTypeDetails['duration_interval'];
                break;
            case 'month':
                $month = $month  + $membershipTypeDetails['duration_interval'];
                break;
            case 'day':
                $day   = $day    + $membershipTypeDetails['duration_interval'];
                break;
            }
            if ( $membershipTypeDetails['duration_unit'] =='lifetime') {
                $endDate = null;
            } else {
                $endDate = date('Y-m-d',mktime(0, 0, 0,
                                               $month,
                                               $day - 1,
                                               $year));
            }
            $today = date( 'Y-m-d' );
            $membershipDates = array();
            $membershipDates['today']      = CRM_Utils_Date::customFormat($today    ,'%Y%m%d' );
            $membershipDates['start_date'] = CRM_Utils_Date::customFormat($startDate,'%Y%m%d' );
            $membershipDates['end_date'  ] = CRM_Utils_Date::customFormat($endDate  ,'%Y%m%d' );
            if ( $endDate && CRM_Utils_Array::value( "renewal_reminder_day", $membershipTypeDetails ) ) {
                $date = explode('-', $endDate );
                $year  = $date[0];
                $month = $date[1];
                $day   = $date[2];
                $day = $day - $membershipTypeDetails["renewal_reminder_day"];
                $reminderDate = date('Y-m-d',mktime( 0, 0, 0,
                                                     $month,
                                                     $day - 1,
                                                     $year ) );
                $membershipDates['reminder_date'] = CRM_Utils_Date::customFormat($reminderDate,'%Y%m%d');
            } 
            $membershipDates['log_start_date' ] = CRM_Utils_Date::customFormat($logStartDate,'%Y%m%d');
        
        } else {
            $today = date( 'Y-m-d' );
            if ( $changeToday ) { 
                $today = CRM_Utils_Date::processDate( $changeToday, null, false, 'Y-m-d' ); 
            } 
            // Calculate new start/end/reminder dates when join date is today
            $renewalDates = self::getDatesForMembershipType( $membershipTypeDetails['id'],
                                                             $today );
            $membershipDates = array();
            $membershipDates['today']      = CRM_Utils_Date::customFormat($today,'%Y%m%d' );
            $membershipDates['start_date'] = $renewalDates['start_date'];
            $membershipDates['end_date']   = $renewalDates['end_date'];
            if ( $renewalDates['reminder_date'] ) {
                $membershipDates['reminder_date'] = $renewalDates['reminder_date'];
            } 
            $membershipDates['log_start_date'] = $renewalDates['start_date'];
        }
        
        return $membershipDates;
    }

    /**
     * Function to retrieve all Membership Types associated
     * with an Organization
     * 
     * @param int $orgID  Id of Organization 
     *
     * @return Array array of the details of membership types
     * @static
     */
    static function getMembershipTypesByOrg( $orgID )
    {
        $membershipTypes = array();
        $dao = new CRM_Member_DAO_MembershipType();
        $dao->member_of_contact_id = $orgID;
        $dao->find();
        while($dao->fetch()) {
            $membershipTypes[$dao->id] = array();
            CRM_Core_DAO::storeValues( $dao, $membershipTypes[$dao->id] ); 
        } 
        return $membershipTypes;
    }

    /**
     * Function to retrieve all Membership Types with Member of Contact id
     * 
     * @param array membership types
     *
     * @return Array array of the details of membership types with Member of Contact id
     * @static
     */    
    static function getMemberOfContactByMemTypes( $membershipTypes ) {
        $memTypeOrgs = array( );
        if ( empty($membershipTypes) ) {
            return $memTypeOrgs;
        }

        $result = CRM_Core_DAO::executeQuery("SELECT id, member_of_contact_id FROM civicrm_membership_type WHERE id IN (". implode(',', $membershipTypes) .")");
        while( $result->fetch( ) ) {
            $memTypeOrgs[$result->id] = $result->member_of_contact_id;
        }
        
        return $memTypeOrgs;
    }
}


