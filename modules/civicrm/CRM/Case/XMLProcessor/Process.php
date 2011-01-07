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

require_once 'CRM/Case/XMLProcessor.php';
require_once 'CRM/Utils/Date.php';

class CRM_Case_XMLProcessor_Process extends CRM_Case_XMLProcessor {

    function run( $caseType,
                  &$params ) {
        $xml = $this->retrieve( $caseType );

        if ( $xml === false ) {
            require_once 'CRM/Utils/System.php';
            $docLink = CRM_Utils_System::docURL2( "CiviCase Configuration" );
            CRM_Core_Error::fatal( ts("Configuration file could not be retrieved for case type = '%1' %2.",
                                      array( 1 => $caseType, 2 => $docLink) ) );
            return false;
        }

        require_once 'CRM/Case/XMLProcessor/Process.php';
        $xmlProcessorProcess = new CRM_Case_XMLProcessor_Process( );
        $this->_isMultiClient = $xmlProcessorProcess->getAllowMultipleCaseClients( );

        $this->process( $xml, $params );
    }

    function get( $caseType,
                  $fieldSet, $isLabel = false, $maskAction = false ) {
        $xml = $this->retrieve( $caseType );
        if ( $xml === false ) {
            require_once 'CRM/Utils/System.php';
            $docLink = CRM_Utils_System::docURL2( "CiviCase Configuration" );
            CRM_Core_Error::fatal( ts("Unable to load configuration file for the referenced case type: '%1' %2.", 
                                      array( 1 => $caseType, 2 => $docLink ) ) );
            return false;
        }

        switch ( $fieldSet ) {
        case 'CaseRoles':
            return $this->caseRoles( $xml->CaseRoles );
        case 'ActivitySets':
            return $this->activitySets( $xml->ActivitySets );
        case 'ActivityTypes':
            return $this->activityTypes( $xml->ActivityTypes, false, $isLabel, $maskAction );
        }
    }

    function process( $xml,
                      &$params ) {
                      
        $standardTimeline = CRM_Utils_Array::value( 'standardTimeline', $params );
        $activitySetName  = CRM_Utils_Array::value( 'activitySetName' , $params );
        $activityTypeName = CRM_Utils_Array::value( 'activityTypeName', $params );
        
        if ( 'Open Case' ==
             CRM_Utils_Array::value( 'activityTypeName', $params ) ) {
            // create relationships for the ones that are required
            foreach ( $xml->CaseRoles as $caseRoleXML ) {
                foreach ( $caseRoleXML->RelationshipType as $relationshipTypeXML ) {
                    if ( (int ) $relationshipTypeXML->creator == 1 ) {
                        if (! $this->createRelationships( (string ) $relationshipTypeXML->name,
                                                          $params ) ) {
                            CRM_Core_Error::fatal( );
                            return false;
                        }
                    }
                }
            }
        }
        
        if ( 'Change Case Start Date' ==
             CRM_Utils_Array::value( 'activityTypeName', $params ) ) {
            // delete all existing activities which are non-empty
            $this->deleteEmptyActivity( $params );
        }

        foreach ( $xml->ActivitySets as $activitySetsXML ) {
            foreach ( $activitySetsXML->ActivitySet as $activitySetXML ) {
                if ( $standardTimeline ) {
                    if ( (boolean ) $activitySetXML->timeline ) {
                        return $this->processStandardTimeline( $activitySetXML,
                                                               $params );
                    }
                } else if ( $activitySetName ) {
                    $name = (string ) $activitySetXML->name;
                    if ( $name == $activitySetName ) {
                        return $this->processActivitySet( $activitySetXML,
                                                          $params ); 
                    }
                } 
            }
        }

    }

    function processStandardTimeline( $activitySetXML,
                                      &$params ) {
        if ( 'Change Case Type' ==
             CRM_Utils_Array::value( 'activityTypeName', $params ) ) {
            // delete all existing activities which are non-empty
            $this->deleteEmptyActivity( $params );
        }

        foreach ( $activitySetXML->ActivityTypes as $activityTypesXML ) {
            foreach ( $activityTypesXML as $activityTypeXML ) {
                $this->createActivity( $activityTypeXML, $params );
            }
        }
    }

    function processActivitySet( $activitySetXML, &$params ) {
        foreach ( $activitySetXML->ActivityTypes as $activityTypesXML ) {
            foreach ( $activityTypesXML as $activityTypeXML ) {
                $this->createActivity( $activityTypeXML, $params );
            }
        }
    }

    function &caseRoles( $caseRolesXML, $isCaseManager = false ) {
        $relationshipTypes =& $this->allRelationshipTypes( );

        $result = array( );
        foreach ( $caseRolesXML as $caseRoleXML ) {
            foreach ( $caseRoleXML->RelationshipType as $relationshipTypeXML ) {
                $relationshipTypeName = (string ) $relationshipTypeXML->name;
                $relationshipTypeID   = array_search( $relationshipTypeName,
                                                      $relationshipTypes );
                if ( $relationshipTypeID === false ) {
                    continue;
                }
                
                if ( !$isCaseManager ) {    
                    $result[$relationshipTypeID] = $relationshipTypeName;
                } else if ( $relationshipTypeXML->manager ) {
                    return $relationshipTypeID;
                }
            }
        }
        return $result;
    }

    function createRelationships( $relationshipTypeName,
                                  &$params ) {
        $relationshipTypes =& $this->allRelationshipTypes( );

        // get the relationship id
        $relationshipTypeID = array_search( $relationshipTypeName,
                                            $relationshipTypes );
        if ( $relationshipTypeID === false ) {
            CRM_Core_Error::fatal( );
            return false;
        }
        
        $client = $params['clientID'];
        if ( !is_array( $client ) ) $client = array( $client );
        
        foreach( $client as $key => $clientId ) {
            $relationshipParams = array( 'relationship_type_id' => $relationshipTypeID,
                                         'contact_id_a'         => $clientId,
                                         'contact_id_b'         => $params['creatorID'],
                                         'is_active'            => 1,
                                         'case_id'              => $params['caseID'],
                                         'start_date'           => date("Ymd") );
        
            if ( ! $this->createRelationship( $relationshipParams ) ) {
                CRM_Core_Error::fatal( );
                return false;
            }
        }
        return true;
    }

    function createRelationship( &$params ) {
        require_once 'CRM/Contact/DAO/Relationship.php';

        $dao = new CRM_Contact_DAO_Relationship( );
        $dao->copyValues( $params );
        // only create a relationship if it does not exist
        if ( ! $dao->find( true ) ) {
            $dao->save( );
        }
        return true;
    }

    function activityTypes( $activityTypesXML, $maxInst = false, $isLabel = false, $maskAction = false ) {
        $activityTypes =& $this->allActivityTypes( true, true );
        $result = array( );
        foreach ( $activityTypesXML as $activityTypeXML ) {
            foreach ( $activityTypeXML as $recordXML ) {
                $activityTypeName = (string ) $recordXML->name;
                $maxInstances     = (string ) $recordXML->max_instances;
                $activityTypeInfo = CRM_Utils_Array::value( $activityTypeName, $activityTypes );
                
                if ( $activityTypeInfo['id'] ) {
                    if ( $maskAction ) {
                        if ( $maskAction == 'edit' && 
                             '0' ===  (string ) $recordXML->editable ) {
                            $result[$maskAction][] = $activityTypeInfo['id'];
                        }
                    } else{
                        if ( !$maxInst ) {
                            //if we want,labels of activities should be returned.
                            if ( $isLabel ) {
                                $result[$activityTypeInfo['id']] = $activityTypeInfo['label'];
                            } else {
                                $result[$activityTypeInfo['id']] = $activityTypeName;
                            }
                        } else {
                            if ( $maxInstances ) {
                                $result[$activityTypeName] = $maxInstances;
                            }
                        }
                    }
                }
            }
        }

        // call option value hook
        require_once 'CRM/Utils/Hook.php';
        CRM_Utils_Hook::optionValues( $result, 'case_activity_type' );

        return $result;
    }

    function deleteEmptyActivity( &$params ) {
        $query = "
DELETE a
FROM   civicrm_activity a
INNER JOIN civicrm_activity_target t ON t.activity_id = a.id
WHERE  t.target_contact_id = %1
AND    a.is_auto = 1
AND    a.is_current_revision = 1
";
        $sqlParams = array( 1 => array( $params['clientID'], 'Integer' ) );
        CRM_Core_DAO::executeQuery( $query, $sqlParams );
    }

    function isActivityPresent( &$params ) {
        $query = "
SELECT     count(a.id)
FROM       civicrm_activity a
INNER JOIN civicrm_case_activity ca on ca.activity_id = a.id
WHERE      a.activity_type_id  = %1
AND        ca.case_id = %2
AND        a.is_deleted = 0
";

        $sqlParams   = array( 1 => array( $params['activityTypeID'], 'Integer' ),
                              2 => array( $params['caseID']        , 'Integer' ) );
        $count       = CRM_Core_DAO::singleValueQuery( $query, $sqlParams );
        
        // check for max instance
        $caseType    = CRM_Case_BAO_Case::getCaseType( $params['caseID'] );
        $maxInstance = self::getMaxInstance( $caseType, $params['activityTypeName'] );

        return $maxInstance ? ($count < $maxInstance ? false : true) : false;  
    }

    function createActivity( $activityTypeXML,
                             &$params ) {

        $activityTypeName =  (string) $activityTypeXML->name;
        $activityTypes    =& $this->allActivityTypes( true, true );
        $activityTypeInfo = CRM_Utils_Array::value( $activityTypeName, $activityTypes );

        if ( ! $activityTypeInfo ) {
            require_once 'CRM/Utils/System.php';
            $docLink = CRM_Utils_System::docURL2( "CiviCase Configuration" );
            CRM_Core_Error::fatal(ts('Activity type %1, found in case configuration file, is not present in the database %2',
                                  array(1 => $activityTypeName, 2 => $docLink)));
            return false;
        }
        $activityTypeID = $activityTypeInfo['id'];

        if ( isset( $activityTypeXML->status ) ) {
            $statusName = (string) $activityTypeXML->status;
        } else {
            $statusName = 'Scheduled';
        }

        if( $this->_isMultiClient ) {
            $client = $params['clientID'];
        } else {
            $client = array( 1 => $params['clientID'] );
        }

        require_once 'CRM/Core/OptionGroup.php';
        if ( $activityTypeName == 'Open Case' ) {
            $activityParams = array( 'activity_type_id'    => $activityTypeID,
                                     'source_contact_id'   => $params['creatorID'],
                                     'is_auto'             => false,
                                     'is_current_revision' => 1,
                                     'subject'             => CRM_Utils_Array::value('subject', $params) ? $params['subject'] : $activityTypeName,
                                     'status_id'           => CRM_Core_OptionGroup::getValue( 'activity_status',
                                                                                              $statusName,
                                                                                              'name' ),
                                     'target_contact_id'   => $client,
                                     'medium_id'           => CRM_Utils_Array::value('medium_id', $params),
                                     'location'            => CRM_Utils_Array::value('location',  $params),
                                     'details'             => CRM_Utils_Array::value('details',   $params),
                                     'duration'            => CRM_Utils_Array::value('duration',  $params),
                                     );
        } else {
            $activityParams = array( 'activity_type_id'    => $activityTypeID,
                                     'source_contact_id'   => $params['creatorID'],
                                     'is_auto'             => true,
                                     'is_current_revision' => 1,
                                     'status_id'           => CRM_Core_OptionGroup::getValue( 'activity_status',
                                                                                            $statusName,
                                                                                            'name' ),
                                     'target_contact_id'   => $client
                                    );
        }
        
        //parsing date to default preference format
        $params['activity_date_time'] = CRM_Utils_Date::processDate( $params['activity_date_time'] );
        
        if ( $activityTypeName == 'Open Case' ) {
            // we don't set activity_date_time for auto generated
            // activities, but we want it to be set for open case.
            $activityParams['activity_date_time'] = $params['activity_date_time'];
            if ( array_key_exists('custom', $params) && is_array($params['custom']) ) {
                $activityParams['custom'] = $params['custom'];
            }
        } else {
            $activityDate = null;
            //get date of reference activity if set.
            if ( $referenceActivityName = (string) $activityTypeXML->reference_activity  ) {

                //we skip open case as reference activity.CRM-4374.
                if ( CRM_Utils_Array::value('resetTimeline', $params) && $referenceActivityName == 'Open Case' ) {
                    $activityDate = $params['activity_date_time']; 
                } else {
                    $referenceActivityInfo = CRM_Utils_Array::value( $referenceActivityName, $activityTypes );
                    if ( $referenceActivityInfo['id'] ) {
                        $caseActivityParams = array( 'activity_type_id' => $referenceActivityInfo['id'] );
                        
                        //if reference_select is set take according activity.
                        if ( $referenceSelect = (string) $activityTypeXML->reference_select ) {
                            $caseActivityParams[$referenceSelect] = 1;
                        }
                        
                        require_once 'CRM/Case/BAO/Case.php';
                        $referenceActivity = 
                            CRM_Case_BAO_Case::getCaseActivityDates( $params['caseID'], $caseActivityParams, true );
                        
                        if ( is_array($referenceActivity) ) {
                            foreach( $referenceActivity as $aId => $details ) {
                                $activityDate = CRM_Utils_Array::value('activity_date', $details );
                                break;
                            }
                        }
                    }
                }
            }
            if ( !$activityDate ) {
                $activityDate = $params['activity_date_time'];
            }
            list( $activity_date, $activity_time ) = CRM_Utils_Date::setDateDefaults( $activityDate );
            $activityDateTime = CRM_Utils_Date::processDate( $activity_date, $activity_time );
            //add reference offset to date.
            if ( (int) $activityTypeXML->reference_offset ) {
                $activityDateTime = CRM_Utils_Date::intervalAdd( 'day', (int) $activityTypeXML->reference_offset, 
                                                                 $activityDateTime );
            }
            
            $activityParams['activity_date_time'] = CRM_Utils_Date::format( $activityDateTime );
        }

        // if same activity is already there, skip and dont touch
        $params['activityTypeID']   = $activityTypeID;
        $params['activityTypeName'] = $activityTypeName;
        if ( $this->isActivityPresent( $params ) ) {
            return true;
        }
        $activityParams['case_id'] = $params['caseID'];
        if ( CRM_Utils_Array::value('is_auto', $activityParams) ) {
            $activityParams['skipRecentView'] = true;
        }
        
        require_once 'CRM/Activity/BAO/Activity.php';
        $activity = CRM_Activity_BAO_Activity::create( $activityParams );
        
        if ( ! $activity ) {
            CRM_Core_Error::fatal( );
            return false;
        }

        // create case activity record
        $caseParams = array( 'activity_id' => $activity->id,
                             'case_id'     => $params['caseID'] );
        require_once 'CRM/Case/BAO/Case.php';
        CRM_Case_BAO_Case::processCaseActivity( $caseParams );
        return true;
    }

    function activitySets( $activitySetsXML ) {
        $result = array( );
        foreach ( $activitySetsXML as $activitySetXML ) {
            foreach ( $activitySetXML as $recordXML ) {
                $activitySetName  = (string ) $recordXML->name;
                $activitySetLabel = (string ) $recordXML->label;
                $result[$activitySetName] = $activitySetLabel;
            }
        }
        
        return $result;
    }
    
    function getMaxInstance( $caseType, $activityTypeName = null ) {
        $xml = $this->retrieve( $caseType );
        
        if ( $xml === false ) {
            CRM_Core_Error::fatal( );
            return false;
        }

        $activityInstances = $this->activityTypes( $xml->ActivityTypes, true );
        return $activityTypeName ? $activityInstances[$activityTypeName] : $activityInstances;
    }

    function getCaseManagerRoleId( $caseType ) {
        $xml = $this->retrieve( $caseType );
        return $this->caseRoles( $xml->CaseRoles, true );
    }

    function getRedactActivityEmail(  ) {
        $xml = $this->retrieve( "Settings" );
        return ( string ) $xml->RedactActivityEmail ? 1 : 0;
    }

    /**
     * Retrieves AllowMultipleCaseClients setting
     * 
     * @return string 1 if allowed, 0 if not
     */      
    function getAllowMultipleCaseClients(  ) {
        $xml = $this->retrieve( "Settings" );
        return ( string ) $xml->AllowMultipleCaseClients ? 1 : 0;
    }    
}
