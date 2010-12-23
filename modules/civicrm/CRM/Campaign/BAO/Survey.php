<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
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

require_once 'CRM/Campaign/DAO/Survey.php';

Class CRM_Campaign_BAO_Survey extends CRM_Campaign_DAO_Survey
{
    /**
     * Takes a bunch of params that are needed to match certain criteria and
     * retrieves the relevant objects. Typically the valid params are only
     * campaign_id. 
     *
     * @param array  $params   (reference ) an assoc array of name/value pairs
     * @param array  $defaults (reference ) an assoc array to hold the flattened values
     *
     * @access public
     */
    
    /**
     * The action links that we need to display for the browse screen
     *
     * @var array
     */
    
    static function retrieve( &$params, &$defaults ) 
    {
        $dao = new CRM_Campaign_DAO_Survey( );
        
        $dao->copyValues($params);
        
        if( $dao->find( true ) ) {
            CRM_Core_DAO::storeValues( $dao, $defaults );
            return $dao;
        }
        return null;  
    }

    /**
     * takes an associative array and creates a Survey object
     *
     * the function extract all the params it needs to initialize the create a
     * survey object.
     *
     * 
     * @return object CRM_Survey_DAO_Survey object
     * @access public
     * @static
     */
    static function create( &$params ) 
    {
        if ( empty( $params ) ) {
            return;
        }
        
        if ($params['is_default']) {
            $query = "UPDATE civicrm_survey SET is_default = 0";
            CRM_Core_DAO::executeQuery($query, CRM_Core_DAO::$_nullArray);
        }
        
        if ( !(CRM_Utils_Array::value('id', $params)) )  {

            if ( !(CRM_Utils_Array::value('created_id', $params)) ) {
                $session = CRM_Core_Session::singleton( );
                $params['created_id'] = $session->get( 'userID' );
            }
            if ( !(CRM_Utils_Array::value('created_date', $params)) ) {
                $params['created_date'] = date('YmdHis');
            }
            
        }
        
        $dao = new CRM_Campaign_DAO_Survey();
        $dao->copyValues( $params );
        $dao->save();

        return $dao;
    }

     /**
     * Function to get Petition Details 
     * 
     * @param boolean $all
     * @param int $id
     * @static
     */
    static function getSurvey( $all = false, 
                               $id = null, 
                               $defaultOnly = false ) {
        
		require_once 'CRM/Core/OptionGroup.php';
        $petitionTypeID = CRM_Core_OptionGroup::getValue( 'activity_type', 'petition',  'name' );

        $survey = array( );
        $dao = new CRM_Campaign_DAO_Survey( );

        if ( !$all ) {
            $dao->is_active = 1;
        } 
        if ( $id ) {
            $dao->id = $id;  
        }
        if ( $defaultOnly ) {
            $dao->is_default = 1;   
        }
        
        $dao->whereAdd ("activity_type_id != $petitionTypeID");   
        $dao->find( );
        while ( $dao->fetch() ) {
            CRM_Core_DAO::storeValues($dao, $survey[$dao->id]);
        }
        
        return $survey;
    }

    /**
     * Function to get Surveys
     * 
     * @param boolean $all
     * @param int $id
     * @static
     */
    static function getSurveyList( $all = false ) {
        require_once 'CRM/Campaign/BAO/Campaign.php';

        $survey = array( );
        $dao = new CRM_Campaign_DAO_Survey( );
        
        if ( !$all ) {
            $dao->is_active = 1;
        }   
        
        $dao->find( );
        while ( $dao->fetch() ) {
            $survey[$dao->id] = $dao->title;
        }
        
        return $survey;
    }
    
    /**
     * Function to get Surveys activity types
     *
     *
     * @static
     */
    static function getSurveyActivityType( ) {
        require_once 'CRM/Core/OptionGroup.php';
        $activityTypes = array( );

        $campaignCompId = CRM_Core_Component::getComponentID('CiviCampaign');
        if ( $campaignCompId ) {
            $activityTypes = CRM_Core_OptionGroup::values( 'activity_type', false, false, false, " AND v.component_id={$campaignCompId}" , 'name' );
        }
        return $activityTypes;
    }
    
    /**
     * Function to get Surveys custom groups
     * @param  $surveyTypes an array of survey type id.
     *
     * @static
     */
    static function getSurveyCustomGroups( $surveyTypes = array( ) ) 
    {
        $customGroups  = array( );
        if( !is_array($surveyTypes) ) {
            $surveyTypes = array( $surveyTypes );
        }
        
        if ( !empty($surveyTypes) ) {
            $activityTypes = array_flip($surveyTypes);
        } else {
            $activityTypes = self::getSurveyActivityType( );
        }
        
        if ( !empty($activityTypes) ) {
            $extendSubType = implode( '[[:>:]]|[[:<:]]', array_keys($activityTypes) );
            
            $query = "SELECT cg.id, cg.name, cg.title, cg.extends_entity_column_value
                      FROM civicrm_custom_group cg
                      WHERE cg.is_active = 1 AND cg.extends_entity_column_value REGEXP '[[:<:]]{$extendSubType}[[:>:]]'";
            
            $dao =  CRM_Core_DAO::executeQuery( $query );
            while( $dao->fetch( ) ) {
                $customGroups[$dao->id]['id']      = $dao->id;
                $customGroups[$dao->id]['name']    = $dao->name;
                $customGroups[$dao->id]['title']   = $dao->title;
                $customGroups[$dao->id]['extends'] = $dao->extends_entity_column_value;
            }
        }
        
        return $customGroups;
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
        return CRM_Core_DAO::setFieldValue( 'CRM_Campaign_DAO_Survey', $id, 'is_active', $is_active );
    }

    /**
     * Function to delete the survey
     *
     * @param int $id survey id
     *
     * @access public
     * @static
     *
     */
    static function del( $id )
    { 
        if ( !$id ) {
            return null;
        }

        $dao     = new CRM_Campaign_DAO_Survey( );
        $dao->id = $id;
        return $dao->delete( );
    }
    
    
    /**
     * This function retrieve contact information.
     *
     * @param array  $voter            an array of contact Ids.
     * @param array  $returnProperties an array of return elements.
     *
     * @return $voterDetails array of contact info.
     * @static
     */
    static function voterDetails( $voterIds, $returnProperties = array( ) ) 
    {
        $voterDetails = array( );
        if ( !is_array( $voterIds ) || empty( $voterIds ) ) {
            return $voterDetails;
        }
        
        if ( empty( $returnProperties ) ) {
            require_once 'CRM/Core/BAO/Preferences.php';
            $autocompleteContactSearch = CRM_Core_BAO_Preferences::valueOptions( 'contact_autocomplete_options' );
            $returnProperties = array_fill_keys( array_merge( array( 'contact_type',
                                                                     'contact_sub_type',
                                                                     'sort_name'), 
                                                              array_keys( $autocompleteContactSearch ) ), 1 );
        }
        
        $select = $from = array( );
        foreach ( $returnProperties as $property => $ignore ) {
            $value = ( in_array( $property, array( 'city', 'street_address' ) ) ) ? 'address' : $property;
            switch ( $property ) {
            case 'sort_name' :
            case 'contact_type' :
            case 'contact_sub_type' :
                $select[] = "$property as $property";
                $from['contact'] = 'civicrm_contact contact';
                break;
                
            case 'email' :
            case 'phone' :
            case 'city' :
            case 'street_address' :
                $select[] = "$property as $property";
                $from[$value] = "LEFT JOIN civicrm_{$value} {$value} ON ( contact.id = {$value}.contact_id AND {$value}.is_primary = 1 ) ";
                break;
                
            case 'country':
            case 'state_province':
                $select[] = "{$property}.name as $property";
                if ( !in_array( 'address', $from ) ) {
                    $from['address'] = 'LEFT JOIN civicrm_address address ON ( contact.id = address.contact_id AND address.is_primary = 1) ';
                }
                $from[$value] = " LEFT JOIN civicrm_{$value} {$value} ON ( address.{$value}_id = {$value}.id  ) ";
                break;
            }
        }
                
        //finally retrieve contact details.
        if ( !empty( $select ) && !empty( $from ) ) {
            $fromClause   = implode( ' ' , $from   );
            $selectClause = implode( ', ', $select );
            $whereClause  = "contact.id IN (" . implode( ',',  $voterIds ) . ')';  
            
            $query = "
  SELECT  contact.id as contactId, $selectClause 
    FROM  $fromClause
   WHERE  $whereClause
Group By  contact.id";
            
            $contact = CRM_Core_DAO::executeQuery( $query );
            require_once 'CRM/Contact/BAO/Contact/Utils.php';
            while ( $contact->fetch( ) ) {
                $voterDetails[$contact->contactId]['contact_id'] = $contact->contactId;
                foreach ( $returnProperties as $property => $ignore ) {
                    $voterDetails[$contact->contactId][$property] = $contact->$property;
                }
                $image = CRM_Contact_BAO_Contact_Utils::getImage( $contact->contact_sub_type ? 
                                                                  $contact->contact_sub_type : $contact->contact_type,
                                                                  false,
                                                                  $contact->contactId );
                $voterDetails[$contact->contactId]['contact_type'] = $image;
            }
            $contact->free( );
        }
        
        return $voterDetails; 
    }
    
    
    /**
     * This function retrieve survey related activities w/ for give voter ids.
     *
     * @param int   $surveyId  survey id.
     * @param array $voterIds  voterIds.
     *
     * @return $activityDetails array of survey activity.
     * @static
     */
    static function voterActivityDetails( $surveyId, $voterIds, $interviewerId = null, $statusIds = array( ) ) 
    {
        $activityDetails = array( );
        if ( !$surveyId || 
             !is_array( $voterIds ) || empty( $voterIds ) ) {
            return $activityDetails;
        }
        
        $whereClause = null;
        if ( is_array( $statusIds ) && !empty( $statusIds ) ) {
            $whereClause = ' AND ( activity.status_id IN ( '. implode( ',', array_values( $statusIds ) ) . ' ) )';
        }
        
        if ( !$interviewerId ) {
            $session = CRM_Core_Session::singleton( );
            $interviewerId = $session->get('userID');
        }
        
        $targetContactIds = ' ( ' . implode( ',', $voterIds ) . ' ) ';
        
        $query = " 
    SELECT  activity.id, activity.status_id, 
            activityTarget.target_contact_id as voter_id,
            activityAssignment.assignee_contact_id as interviewer_id
      FROM  civicrm_activity activity
INNER JOIN  civicrm_activity_target activityTarget ON ( activityTarget.activity_id = activity.id )
INNER JOIN  civicrm_activity_assignment activityAssignment ON ( activityAssignment.activity_id = activity.id )
     WHERE  activity.source_record_id = %1
       AND  ( activity.is_deleted IS NULL OR activity.is_deleted = 0 )
       AND  activityAssignment.assignee_contact_id = %2
       AND  activityTarget.target_contact_id IN {$targetContactIds} 
            $whereClause";
        
        $activity = CRM_Core_DAO::executeQuery( $query, array( 1 => array( $surveyId, 'Integer' ),
                                                               2 => array( $interviewerId, 'Integer' ) ) );
        while ( $activity->fetch( ) ) {
            $activityDetails[$activity->voter_id] = array( 'voter_id'       => $activity->voter_id,
                                                           'status_id'      => $activity->status_id,
                                                           'activity_id'    => $activity->id,
                                                           'interviewer_id' => $activity->interviewer_id );
        }
        
        return $activityDetails;
    }
    
    /**
     * This function retrieve survey related activities.
     *
     * @param int    $surveyId  survey id.
     *
     * @return $activities an array of survey activity.
     * @static
     */
    static function getSurveyActivities( $surveyId, $interviewerId = null, $statusIds = array( ) ) 
    {
        $activities = array( );
        if ( !$surveyId ) return $activities; 
        
        $where = array( );
        if ( is_array( $statusIds ) && !empty( $statusIds ) ) {
            $where[] = '( activity.status_id IN ( '. implode( ',', array_values( $statusIds ) ) . ' ) )';
        }
        if ( $interviewerId ) {
            $where[] = "( activityAssignment.assignee_contact_id =  $interviewerId )";
        }
        $whereClause = null;
        if ( !empty( $where ) ) {
            $whereClause = ' AND ( '. implode( ' AND ', $where ) . ' )';
        }
        
        $actTypeId = CRM_Core_DAO::getFieldValue( 'CRM_Campaign_DAO_Survey', $surveyId, 'activity_type_id' ); 
        if ( !$actTypeId ) return $activities;
        
        $query = "
    SELECT  activity.id, activity.status_id, 
            activityTarget.target_contact_id as voter_id,
            activityAssignment.assignee_contact_id as interviewer_id,
            activity.result as result,
            activity.activity_date_time as activity_date_time
      FROM  civicrm_activity activity
INNER JOIN  civicrm_activity_target activityTarget ON ( activityTarget.activity_id = activity.id )
INNER JOIN  civicrm_activity_assignment activityAssignment ON ( activityAssignment.activity_id = activity.id )
     WHERE  activity.source_record_id = %1
       AND  activity.activity_type_id = %2
       AND  ( activity.is_deleted IS NULL OR activity.is_deleted = 0 )
            $whereClause";
        
        $activity = CRM_Core_DAO::executeQuery( $query, array( 1 => array( $surveyId,  'Integer'),
                                                               2 => array( $actTypeId, 'Integer' ) ) );
        
        while ( $activity->fetch( ) ) {
            $activities[$activity->id] = array( 'id'             => $activity->id,
                                                'voter_id'       => $activity->voter_id,
                                                'status_id'      => $activity->status_id,
                                                'interviewer_id' => $activity->interviewer_id,
                                                'result'         => $activity->result,
                                                'activity_date_time' => $activity->activity_date_time );
        }
        
        return $activities;
    }
    
    /**
     * This function retrieve survey voter information.
     *
     * @param int    $surveyId       survey id.
     * @param int    $interviewerId  interviewer id.
     * @param array  $statusIds      survey status ids.
     * @return survey related contact ids. 
     * @static
     */
    static function getSurveyVoterInfo( $surveyId, $interviewerId = null, $statusIds = array( ) ) 
    {
        $voterIds = array( );
        if ( !$surveyId ) return $voterIds;
        
        $cacheKey = $surveyId;
        if ( $interviewerId ) $cacheKey .= "_{$interviewerId}";
        if ( is_array( $statusIds ) && !empty( $statusIds ) ) {
            $cacheKey = "{$cacheKey}_" . implode( '_', $statusIds );
        }
        
        static $contactIds = array( );
        if ( !isset( $contactIds[$cacheKey] ) ) {
            $activities = self::getSurveyActivities( $surveyId, $interviewerId, $statusIds );
            foreach ( $activities as $values ) {
                $voterIds[$values['voter_id']] = $values;
            }
            $contactIds[$cacheKey] = $voterIds;
        }
        
        return $contactIds[$cacheKey];
    }
    
    /*
     * This function retrieve all option groups which are created as a result set 
     *
     * @return $resultSets an array of option groups.
     * @static
     */
    static function getResultSets(  ) {
        $resultSets = array( );
        $query = "SELECT id, label FROM civicrm_option_group WHERE name LIKE 'civicrm_survey_%' AND is_active=1";
        $dao   = CRM_Core_DAO::executeQuery( $query );
        while ( $dao->fetch( ) ) {
            $resultSets[$dao->id] = $dao->label;
        }
        
        return $resultSets;
    }
    
    /*
     * This function is to check survey activity.  
     *
     * @param int $activityId activity id.
     * @param int $activityTypeId activity type id.
     * @return true/false boolean.
     * @static
     */
    static function isSurveyActivity( $activityId ) 
    {
        $isSurveyActivity = false;
        if ( !$activityId ) return $isSurveyActivity;
        
        require_once 'CRM/Activity/DAO/Activity.php';
        $activity     = new CRM_Activity_DAO_Activity( );
        $activity->id = $activityId; 
        $activity->selectAdd( 'source_record_id, activity_type_id' );
        if ( $activity->find( true ) && 
             $activity->source_record_id ) {
            $surveyActTypes = self::getSurveyActivityType( ); 
            if ( array_key_exists( $activity->activity_type_id, $surveyActTypes ) ) {
                $isSurveyActivity = true;
            }
        }
        
        return $isSurveyActivity;
    }
    
    /*
     * This function retrive all response options of survey
     *
     * @param int $surveyId survey id.
     * @return $responseOptions an array of option values
     * @static
     */
    static function getResponsesOptions( $surveyId ) 
    {
        $responseOptions = array( );
        if ( !$surveyId ) return $responseOptions;  
        
        $resultId = CRM_Core_DAO::getFieldValue( 'CRM_Campaign_DAO_Survey', $surveyId, 'result_id' );
        if ( $resultId ) { 
            require_once 'CRM/Core/OptionGroup.php';
            $responseOptions = CRM_Core_OptionGroup::valuesByID( $resultId );
        }
        
        return $responseOptions;
    }
    
    /*
     * This function return all voter links with respecting permissions
     *
     * @return $url array of permissioned links
     * @static
     */
    static function buildPermissionLinks( $surveyId ) 
    {
        $menuLinks = array( );
        if ( !$surveyId ) return $menuLinks;  
        
        static $voterLinks = array( );
        if ( empty( $voterLinks ) ) {
            require_once 'CRM/Core/Permission.php';
            $permissioned = false;
            if ( CRM_Core_Permission::check( 'manage campaign' ) ||
                 CRM_Core_Permission::check( 'administer CiviCampaign' ) ) {
                $permissioned = true; 
            }
            
            if ( $permissioned || CRM_Core_Permission::check( "reserve campaign contacts" ) ) {
                $voterLinks['reserve'] = array( 'name'  => 'reserve',
                                                'url'   => 'civicrm/survey/search',
                                                'qs'    => 'sid=%%id%%&reset=1&op=reserve&force=1',
                                                'title' => ts('Reserve Respondents') );
            }
            if ( $permissioned || CRM_Core_Permission::check( "interview campaign contacts" ) ) {
                $voterLinks['release'] = array( 'name'  => 'interview',
                                                'url'   => 'civicrm/survey/search',
                                                'qs'    => 'sid=%%id%%&reset=1&op=interview&force=1',
                                                'title' => ts('Interview Respondents') );
            }
            if ( $permissioned || CRM_Core_Permission::check( "release campaign contacts" ) ) {
                $voterLinks['interview'] = array( 'name'  => 'release',
                                                  'url'   => 'civicrm/survey/search',
                                                  'qs'    => 'sid=%%id%%&reset=1&op=release&force=1',
                                                  'title' => ts('Release Respondents') );
            }
        }
        
        require_once 'CRM/Core/Action.php';
        $ids = array( 'id' => $surveyId );
        foreach ( $voterLinks as $link ) {
            if ( CRM_Utils_Array::value( 'qs', $link ) && 
                 !CRM_Utils_System::isNull( $link['qs'] ) ) {
                $urlPath = CRM_Utils_System::url( CRM_Core_Action::replace( $link['url'], $ids ),
                                                  CRM_Core_Action::replace( $link['qs'], $ids ) );
                $menuLinks[] = sprintf( '<a href="%s" class="action-item" title="%s">%s</a>',
                                        $urlPath,
                                        CRM_Utils_Array::value( 'title', $link ),
                                        $link['title'] );
            }
        }
        
        return $menuLinks;
    }
    
}
