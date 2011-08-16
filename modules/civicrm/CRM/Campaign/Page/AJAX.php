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
 *
 */

require_once 'CRM/Utils/Type.php';

/**
 * This class contains all campaign related functions that are called using AJAX (jQuery)
 */
class CRM_Campaign_Page_AJAX
{
    
    static function registerInterview( )
    {
        $fields = array( 'result',
                         'voter_id',
                         'survey_id',
                         'activity_id',
                         'surveyTitle',
                         'interviewer_id',
                         'activity_type_id' );
        
        $params = array( );
        foreach ( $fields as $fld ) {
            $params[$fld] =  CRM_Utils_Array::value( $fld, $_POST );
        }
        $params['details'] = CRM_Utils_Array::value( 'note', $_POST );
        $voterId    = $params['voter_id'];
        $activityId = $params['activity_id'];
        
        $customKey = "field_{$voterId}_custom";
        foreach ( $_POST as $key => $value ) {
            if ( strpos( $key, $customKey ) !== false ) {
                $customFieldKey = str_replace( str_replace( substr( $customKey, -6 ), '', $customKey ), '', $key );
                $params[$customFieldKey] = $value;
            }
        }
        
        if ( isset( $_POST['field'] ) &&
             CRM_Utils_Array::value( $voterId, $_POST['field'] ) && 
             is_array( $_POST['field'][$voterId] ) ) {
            foreach( $_POST['field'][$voterId] as $fieldKey => $value ) {
                $params[$fieldKey] = $value;
            }
        }
        
        //lets pickup contat related fields.
        foreach ( $_POST as $key => $value ) {
            if ( strpos( $key, "field_{$voterId}_" ) !== false &&
                 strpos( $key, "field_{$voterId}_custom" ) === false ) {
                $key = substr( $key, strlen( "field_{$voterId}_" ) );
                $params[$key] = $value;
            }
        }
        
        require_once "CRM/Utils/JSON.php";
        $result = array( 'status'       => 'fail',
                         'voter_id'     => $voterId,
                         'activity_id'  => $params['interviewer_id'] );
        
        //time to validate custom data.
        require_once 'CRM/Core/BAO/CustomField.php';
        $errors = CRM_Core_BAO_CustomField::validateCustomData( $params );
        if ( is_array( $errors ) && !empty( $errors )  ) {
            $result['errors'] = $errors;
            echo json_encode( $result );
            CRM_Utils_System::civiExit( );
        }
        
        //process the response/interview data.
        require_once 'CRM/Campaign/Form/Task/Interview.php';
        $activityId = CRM_Campaign_Form_Task_Interview::registerInterview( $params );
        if ( $activityId ) $result['status'] = 'success'; 
        
        require_once "CRM/Utils/JSON.php";
        echo json_encode( $result );
        
        CRM_Utils_System::civiExit( );
    }
    
    static function loadOptionGroupDetails( ) {
        
        $id       = CRM_Utils_Array::value( 'option_group_id', $_POST );
        $status   = 'fail';
        $opValues = array( );
        
        if ( $id ) {
            require_once 'CRM/Core/OptionValue.php';
            $groupParams['id'] = $id;
            CRM_Core_OptionValue::getValues( $groupParams, $opValues );
        }

        $surveyId = CRM_Utils_Array::value( 'survey_id', $_POST );
        if ( $surveyId ) {
            require_once 'CRM/Campaign/DAO/Survey.php';
            $survey = new CRM_Campaign_DAO_Survey( );
            $survey->id        = $surveyId;
            $survey->result_id = $id;
            if ( $survey->find( true ) ) {
                if ( $survey->recontact_interval ) {
                    $recontactInterval = unserialize( $survey->recontact_interval );
                    foreach( $opValues as $opValId => $opVal ) {
                        if ( CRM_Utils_Array::value( $opVal['label'], $recontactInterval) ) {
                            $opValues[$opValId]['interval'] = $recontactInterval[$opVal['label']];
                        }
                    }
                }
            }
        }

        if ( !empty($opValues) ) {
            $status = 'success';
        }

        $result = array( 'status' => $status,
                         'result' => $opValues);
        
        echo json_encode( $result );
        CRM_Utils_System::civiExit( );
    }
    
    function voterList( ) 
    {
        //get the search criteria params.
        $searchParams = explode( ',', CRM_Utils_Array::value( 'searchCriteria', $_POST ) );
        
        $params = $searchRows = array( );
        foreach ( $searchParams as $param ) {
            if ( CRM_Utils_Array::value( $param, $_POST ) ) {
                $params[$param] = $_POST[$param];
            }
        }
        
        //format multi-select group and contact types.
        foreach ( array( 'group', 'contact_type' ) as $param ) {
            $paramValue = CRM_Utils_Array::value( $param, $params );
            if ( $paramValue ) {
                unset( $params[$param] );
                $paramValue = explode( ',', $paramValue );
                foreach ( $paramValue as $key => $value ) {
                    $params[$param][$value] = 1;
                }
            }
        }
        
        $voterClauseParams = array( );
        foreach ( array( 'campaign_survey_id', 'survey_interviewer_id', 'campaign_search_voter_for' ) as $fld ) {
            $voterClauseParams[$fld] = CRM_Utils_Array::value( $fld, $params );
        }
        
        $interviewerId  = $surveyTypeId = $surveyId = null;
        $searchVoterFor = $params['campaign_search_voter_for']; 
        if ( $searchVoterFor == 'reserve' ) {
            if ( CRM_Utils_Array::value( 'campaign_survey_id', $params ) ) {
                require_once 'CRM/Campaign/DAO/Survey.php';
                $survey = new CRM_Campaign_DAO_Survey( );
                $survey->id = $surveyId = $params['campaign_survey_id'];
                $survey->selectAdd( 'campaign_id, activity_type_id' );
                $survey->find( true );
                $campaignId   = $survey->campaign_id;
                $surveyTypeId = $survey->activity_type_id;
                
                //allow voter search in sub-part of given constituents,
                //but make sure in case user does not select any group.
                //get all associated campaign groups in where filter, CRM-7406
                $groups = CRM_Utils_Array::value( 'group', $params );
                if ( $campaignId && CRM_Utils_System::isNull( $groups ) ) {
                    require_once 'CRM/Campaign/BAO/Campaign.php';
                    $campaignGroups = CRM_Campaign_BAO_Campaign::getCampaignGroups($campaignId);
                    foreach( $campaignGroups as $id => $group ) $params['group'][$id] = 1;
                }
                
                //apply filter of survey contact type for search.
                require_once 'CRM/Campaign/BAO/Survey.php';
                $contactType = CRM_Campaign_BAO_Survey::getSurveyContactType( $surveyId );
                if ( $contactType ) {
                    $params['contact_type'][$contactType] = 1 ;
                }
                
                unset( $params['campaign_survey_id'] );
            }
            unset( $params['survey_interviewer_id'] );
        } else {
            //get the survey status in where clause.
            require_once 'CRM/Core/PseudoConstant.php';
            $scheduledStatusId = array_search( 'Scheduled', CRM_Core_PseudoConstant::activityStatus( 'name' ) );
            if ( $scheduledStatusId ) $params['survey_status_id'] = $scheduledStatusId;
            //BAO/Query knows reserve/release/interview processes.
            if ( $params['campaign_search_voter_for'] == 'gotv' ) {
                $params['campaign_search_voter_for'] = 'release';
            }
        }
        
        $selectorCols = array( 'sort_name', 
                               'street_address', 
                               'street_name', 
                               'street_number', 
                               'street_unit' );
        
        // get the data table params.
        $dataTableParams = array( 'sEcho'     => array( 'name'    => 'sEcho',
                                                        'type'    => 'Integer',
                                                        'default' => 0                ), 
                                  'offset'    => array( 'name'    => 'iDisplayStart',
                                                        'type'    => 'Integer',
                                                        'default' => 0                ),
                                  'rowCount'  => array( 'name'    => 'iDisplayLength',
                                                        'type'    => 'Integer',
                                                        'default' => 25               ),
                                  'sort'      => array( 'name'    => 'iSortCol_0',
                                                        'type'    => 'Integer',
                                                        'default' => 'sort_name'      ),
                                  'sortOrder' => array( 'name'    => 'sSortDir_0',
                                                        'type'    => 'String', 
                                                        'default' => 'asc'            ) );
        foreach ( $dataTableParams as $pName => $pValues ) {
            $$pName = $pValues['default'];
            if ( CRM_Utils_Array::value( $pValues['name'], $_POST ) ) {
                $$pName = CRM_Utils_Type::escape( $_POST[$pValues['name']], $pValues['type'] );
                if ( $pName == 'sort' ) $$pName = $selectorCols[$$pName];   
            }
        }
        
        require_once 'CRM/Contact/BAO/Query.php';
        $queryParams = CRM_Contact_BAO_Query::convertFormValues( $params );
        $query       = new CRM_Contact_BAO_Query( $queryParams,
                                                  null, null, false, false, 
                                                  CRM_Contact_BAO_Query::MODE_CAMPAIGN,
                                                  true );
        
        //get the voter clause to restrict and validate search.
        require_once 'CRM/Campaign/BAO/Query.php';
        $voterClause = CRM_Campaign_BAO_Query::voterClause( $voterClauseParams );
        
        $searchCount = $query->searchQuery( 0, 0, null,
                                            true, false, 
                                            false, false, 
                                            false, 
                                            CRM_Utils_Array::value( 'whereClause', $voterClause ),
                                            null,
                                            CRM_Utils_Array::value( 'fromClause', $voterClause ) );
        
        $iTotal      = $searchCount;
        
        $selectorCols = array( 'contact_type', 'sort_name', 'street_address', 
                               'street_name', 'street_number', 'street_unit' );
        
        $extraVoterColName = 'is_interview_conducted';
        if ( $params['campaign_search_voter_for'] = 'reserve' ) {
            $extraVoterColName = 'reserve_voter';
        }
        
        if ( $searchCount > 0 ) {
            if ( $searchCount < $offset ) $offset = 0;
            
            require_once( 'CRM/Contact/BAO/Contact/Utils.php' );
            $config = CRM_Core_Config::singleton( );
            
            // get the result of the search
            $result = $query->searchQuery( $offset, $rowCount, $sort, 
                                           false, false,
                                           false, false, 
                                           false, 
                                           CRM_Utils_Array::value( 'whereClause', $voterClause ), 
                                           $sortOrder,
                                           CRM_Utils_Array::value( 'fromClause', $voterClause ) );
            while( $result->fetch() ) {
                $contactID  = $result->contact_id;
                $typeImage  = CRM_Contact_BAO_Contact_Utils::getImage( $result->contact_sub_type ? 
                                                                       $result->contact_sub_type : $result->contact_type,
                                                                       false,
                                                                       $result->contact_id );
                
                $searchRows[$contactID] = array( 'id' => $contactID );
                foreach ( $selectorCols as $col ) {
                    $val = $result->$col;
                    if ( $col == 'contact_type' ) $val = $typeImage;  
                    $searchRows[$contactID][$col] = $val;
                }
                if ( $searchVoterFor == 'reserve' ) {
                    $voterExtraColHtml = '<input type="checkbox" id="survey_activity['. $contactID .']" name="survey_activity['. $contactID .']" value='. $contactID .' onClick="processVoterData( this, \'reserve\' );" />';
                    $msg = ts( 'Respondent Reserved.' );
                    $voterExtraColHtml .= "&nbsp;<span id='success_msg_{$contactID}' class='ok' style='display:none;'>$msg</span>";
                } else if ( $searchVoterFor == 'gotv' ) {
                    $surveyActId  = $result->survey_activity_id; 
                    $voterExtraColHtml = '<input type="checkbox" id="survey_activity['. $surveyActId .']" name="survey_activity['. $surveyActId .']" value='. $surveyActId .' onClick="processVoterData( this, \'gotv\' );" />';
                    $msg = ts( 'Vote Recorded' );
                    $voterExtraColHtml .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span id='success_msg_{$surveyActId}' class='ok' style='display:none;'>$msg</span>";
                } else {
                    $surveyActId  = $result->survey_activity_id; 
                    $voterExtraColHtml = '<input type="checkbox" id="survey_activity['. $surveyActId .']" name="survey_activity['. $surveyActId .']" value='. $surveyActId .' onClick="processVoterData( this, \'release\' );" />';
                    $msg = ts( 'Vote Recorded' );
                    $voterExtraColHtml .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span id='success_msg_{$surveyActId}' class='ok' style='display:none;'>$msg</span>";
                }
                $searchRows[$contactID][$extraVoterColName] = $voterExtraColHtml;
            }
        }
        
        require_once "CRM/Utils/JSON.php";
        $selectorElements = array_merge( $selectorCols, array( $extraVoterColName ) );
        
        $iFilteredTotal = $iTotal;
        
        echo CRM_Utils_JSON::encodeDataTableSelector( $searchRows, $sEcho, $iTotal, $iFilteredTotal, $selectorElements );
        CRM_Utils_System::civiExit( );
    }
    
    function processVoterData( ) 
    {
        $status    = null; 
        $operation = CRM_Utils_Type::escape( $_POST['operation'], 'String' );
        if ( $operation == 'release' ) {
            require_once 'CRM/Utils/String.php';
            $activityId = CRM_Utils_Type::escape($_POST['activity_id'],  'Integer' );
            $isDelete   = CRM_Utils_String::strtoboolstr( CRM_Utils_Type::escape($_POST['isDelete'], 'String' ) );
            if ( $activityId &&
                 CRM_Core_DAO::setFieldValue( 'CRM_Activity_DAO_Activity', 
                                              $activityId, 
                                              'is_deleted', 
                                              $isDelete ) ) {
                $status = 'success';
            }
        } else if ( $operation == 'reserve' ) {
            $activityId     = null;
            $createActivity = true;
            if ( CRM_Utils_Array::value( 'activity_id', $_POST ) ) {
                $activityId = CRM_Utils_Type::escape($_POST['activity_id'],  'Integer' );
                if ( $activityId ) {
                    $createActivity = false;
                    $activityUpdated = CRM_Core_DAO::setFieldValue( 'CRM_Activity_DAO_Activity', 
                                                                    $activityId, 
                                                                    'is_deleted', 
                                                                    0 );
                    if ( $activityUpdated ) $status = 'success';
                }
            }
            if ( $createActivity ) {
                $ids = array( 'source_record_id', 
                              'source_contact_id', 
                              'target_contact_id', 
                              'assignee_contact_id' );
                $activityParams = array( );
                foreach ( $ids as $id ) {
                    $val = CRM_Utils_Array::value( $id, $_POST );
                    if ( !$val ) {
                        $createActivity = false;
                        break;
                    }
                    $activityParams[$id] = CRM_Utils_Type::escape( $val, 'Integer' );
                }
            }
            if ( $createActivity  ) {
                $isReserved = CRM_Utils_String::strtoboolstr( CRM_Utils_Type::escape($_POST['isReserved'], 'String' ) );
                require_once 'CRM/Core/PseudoConstant.php';
                $activityStatus    = CRM_Core_PseudoConstant::activityStatus( 'name' );
                $scheduledStatusId = array_search( 'Scheduled', $activityStatus ); 
                if ( $isReserved ) {
                    $surveyValues = array( );
                    $surveyParams = array( 'id' => $activityParams['source_record_id'] );
                    CRM_Core_DAO::commonRetrieve( 'CRM_Campaign_DAO_Survey',
                                                  $surveyParams, 
                                                  $surveyValues,
                                                  array( 'title', 'activity_type_id', 'campaign_id' ) );
                    
                    $activityTypeId =  $surveyValues['activity_type_id'];
                    
                    $surveytitle = CRM_Utils_Array::value( 'surveyTitle', $_POST );
                    if ( !$surveytitle ) $surveytitle = $surveyValues['title'];
                    
                    $subject =  ts( '%1', array( 1 => $surveytitle ) ). ' - ' . ts( 'Respondent Reservation' );
                    $activityParams['subject']            = $subject;
                    $activityParams['status_id']          = $scheduledStatusId;
                    $activityParams['skipRecentView']     = 1;
                    $activityParams['activity_date_time'] = date('YmdHis');
                    $activityParams['activity_type_id']   = $activityTypeId;
                    $activityParams['campaign_id']        = $surveyValues['campaign_id'];
                    
                    require_once 'CRM/Activity/BAO/Activity.php';
                    $activity = CRM_Activity_BAO_Activity::create( $activityParams );
                    if ( $activity->id ) $status = 'success';
                } else {
                    //delete reserved activity for given voter.
                    require_once 'CRM/Campaign/BAO/Survey.php';
                    $voterIds   = array( $activityParams['target_contact_id'] ); 
                    $activities = CRM_Campaign_BAO_Survey::voterActivityDetails( $activityParams['source_record_id'], 
                                                                                 $voterIds,
                                                                                 $activityParams['source_contact_id'],
                                                                                 array( $scheduledStatusId ) );
                    foreach ( $activities as $voterId => $values ) {
                        $activityId = CRM_Utils_Array::value( 'activity_id', $values );
                        if ( $activityId && ( $values['status_id'] == $scheduledStatusId ) ) {
                            CRM_Core_DAO::setFieldValue( 'CRM_Activity_DAO_Activity', 
                                                         $activityId,
                                                         'is_deleted',
                                                         true );
                            $status = 'success';
                            break;
                        }
                    }
                }
            }
        } else if ( $operation == 'gotv' ) {
            require_once 'CRM/Utils/String.php';
            $activityId = CRM_Utils_Type::escape($_POST['activity_id'],  'Integer' );
            $hasVoted    = CRM_Utils_String::strtoboolstr( CRM_Utils_Type::escape($_POST['hasVoted'], 'String' ) );
            if ( $activityId ) {
                if ( $hasVoted ) {
                    $statusValue = 2;
                } else {
                    $statusValue = 1;
                }
                CRM_Core_DAO::setFieldValue( 'CRM_Activity_DAO_Activity', 
                                             $activityId, 
                                             'status_id', 
                                             $statusValue );
                $status = 'success';
           }
        }

        echo json_encode( array( 'status' => $status ) );
        CRM_Utils_System::civiExit( );
    }
    
    function allActiveCampaigns( ) 
    {
        require_once 'CRM/Utils/JSON.php';
        require_once 'CRM/Campaign/BAO/Campaign.php';
        $currentCampaigns = CRM_Campaign_BAO_Campaign::getCampaigns( );
        $campaigns = CRM_Campaign_BAO_Campaign::getCampaigns( null, null, true, false, true );
        $options   = array( array( 'value' => '',
                                   'title'  => ts('- select -') ) );
        foreach ( $campaigns as $value => $title ) {
            $class = null;
            if ( !array_key_exists( $value, $currentCampaigns ) ) {
                $class = 'status-past';
            }
            $options[] = array( 'value' => $value,
                                'title' => $title,
                                'class' => $class );
        }
        $status = 'fail';
        if ( count( $options ) > 1 ) $status = 'success';  
        
        $results = array( 'status'    => $status,
                          'campaigns' => $options );
        
        echo json_encode( $results );
        
        CRM_Utils_System::civiExit( );
    }

    function campaignGroups( ) 
    {
        require_once 'CRM/Utils/JSON.php';
        require_once 'CRM/Campaign/BAO/Campaign.php';
        $surveyId = CRM_Utils_Request::retrieve( 'survey_id', 'Positive', 
                                                 CRM_Core_DAO::$_nullObject, false, null, 'POST' );
        $campGroups = array( );
        if ( $surveyId ) {
            $campaignId = CRM_Core_DAO::getFieldValue( 'CRM_Campaign_DAO_Survey', $surveyId, 'campaign_id' );
            if ( $campaignId ) {
                require_once 'CRM/Campaign/BAO/Campaign.php';
                $campGroups = CRM_Campaign_BAO_Campaign::getCampaignGroups( $campaignId );
            }
        }
        
        //CRM-7406 --If there is no campaign or no group associated with
        //campaign of given survey, lets allow to search across all groups.
        if ( empty( $campGroups ) ) {
            require_once 'CRM/Core/PseudoConstant.php';
            $campGroups = CRM_Core_PseudoConstant::group( ); 
        }
        $groups = array( array( 'value' => '',
                                'title'  => ts('- select -') ) );
        foreach ( $campGroups as $grpId => $title ) {
            $groups[] = array( 'value' => $grpId,
                               'title' => $title );
        }
        $results = array( 'status' => 'success',
                          'groups' => $groups );
        
        echo json_encode( $results );
        
        CRM_Utils_System::civiExit( );
    }
    
    /**
     * Retrieve campaigns as for campaign dashboard.
     *
     **/
    function campaignList( ) 
    {
        //get the search criteria params.
        $searchParams = explode( ',', CRM_Utils_Array::value( 'searchCriteria', $_POST ) );
        
        $params = $searchRows = array( );
        foreach ( $searchParams as $param ) {
            if ( CRM_Utils_Array::value( $param, $_POST ) ) {
                $params[$param] = $_POST[$param];
            }
        }
        
        //this is sequence columns on datatable.
        $selectorCols = array( 'id', 
                               'name', 
                               'title', 
                               'description', 
                               'start_date', 
                               'end_date', 
                               'campaign_type_id', 
                               'campaign_type', 
                               'status_id', 
                               'status', 
                               'is_active',
                               'isActive',
                               'action' );
        
        // get the data table params.
        $dataTableParams = array( 'sEcho'     => array( 'name'    => 'sEcho',
                                                        'type'    => 'Integer',
                                                        'default' => 0                ), 
                                  'offset'    => array( 'name'    => 'iDisplayStart',
                                                        'type'    => 'Integer',
                                                        'default' => 0                ),
                                  'rowCount'  => array( 'name'    => 'iDisplayLength',
                                                        'type'    => 'Integer',
                                                        'default' => 25               ),
                                  'sort'      => array( 'name'    => 'iSortCol_0',
                                                        'type'    => 'Integer',
                                                        'default' => 'start_date'      ),
                                  'sortOrder' => array( 'name'    => 'sSortDir_0',
                                                        'type'    => 'String', 
                                                        'default' => 'desc'            ) );
        foreach ( $dataTableParams as $pName => $pValues ) {
            $$pName = $pValues['default'];
            if ( CRM_Utils_Array::value( $pValues['name'], $_POST ) ) {
                $$pName = CRM_Utils_Type::escape( $_POST[$pValues['name']], $pValues['type'] );
                if ( $pName == 'sort' ) {
                    $$pName = $selectorCols[$$pName];
                }
            }
        }
        foreach ( array( 'sort', 'offset', 'rowCount', 'sortOrder' ) as $sortParam ) {
            $params[$sortParam] = $$sortParam;
        }
        
        require_once 'CRM/Campaign/BAO/Campaign.php';
        require_once 'CRM/Campaign/Page/DashBoard.php';
        $searchCount = CRM_Campaign_BAO_Campaign::getCampaignSummary( $params, true );
        $campaigns   = CRM_Campaign_Page_DashBoard::getCampaignSummary( $params );
        $iTotal      = $searchCount;
        
        if ( $searchCount > 0 ) {
            if ( $searchCount < $offset ) $offset = 0;
            foreach ( $campaigns as $campaignID => $values ) {
                foreach ( $selectorCols as $col ) {
                    $searchRows[$campaignID][$col] = ($col == 'description') ? str_replace(array("\r\n", "\n", "\r"), '<br />', CRM_Utils_Array::value($col, $values, '')) : CRM_Utils_Array::value($col, $values);
                }
            }
        }
        
        require_once "CRM/Utils/JSON.php";
        $selectorElements = $selectorCols;
        
        $iFilteredTotal = $iTotal;
        
        echo CRM_Utils_JSON::encodeDataTableSelector( $searchRows, $sEcho, $iTotal, $iFilteredTotal, $selectorElements );
        CRM_Utils_System::civiExit( );
    }
    
    /**
     * Retrieve survey for survey dashboard.
     *
     **/
    function surveyList( ) 
    {
        //get the search criteria params.
        $searchParams = explode( ',', CRM_Utils_Array::value( 'searchCriteria', $_POST ) );
        
        $params = $searchRows = array( );
        foreach ( $searchParams as $param ) {
            if ( CRM_Utils_Array::value( $param, $_POST ) ) {
                $params[$param] = $_POST[$param];
            }
        }
        
        //this is sequence columns on datatable.
        $selectorCols = array( 'id',
                               'title', 
                               'campaign_id',
                               'campaign',
                               'activity_type_id',
                               'activity_type',
                               'release_frequency',
                               'default_number_of_contacts',
                               'max_number_of_contacts',
                               'is_default',
                               'is_active',
                               'isActive',
                               'result_id',
                               'action',
                               'voterLinks' );
        
        // get the data table params.
        $dataTableParams = array( 'sEcho'     => array( 'name'    => 'sEcho',
                                                        'type'    => 'Integer',
                                                        'default' => 0                ), 
                                  'offset'    => array( 'name'    => 'iDisplayStart',
                                                        'type'    => 'Integer',
                                                        'default' => 0                ),
                                  'rowCount'  => array( 'name'    => 'iDisplayLength',
                                                        'type'    => 'Integer',
                                                        'default' => 25               ),
                                  'sort'      => array( 'name'    => 'iSortCol_0',
                                                        'type'    => 'Integer',
                                                        'default' => 'created_date'   ),
                                  'sortOrder' => array( 'name'    => 'sSortDir_0',
                                                        'type'    => 'String', 
                                                        'default' => 'desc'           ) );
        foreach ( $dataTableParams as $pName => $pValues ) {
            $$pName = $pValues['default'];
            if ( CRM_Utils_Array::value( $pValues['name'], $_POST ) ) {
                $$pName = CRM_Utils_Type::escape( $_POST[$pValues['name']], $pValues['type'] );
                if ( $pName == 'sort' ) {
                    $$pName = $selectorCols[$$pName];
                }
            }
        }
        foreach ( array( 'sort', 'offset', 'rowCount', 'sortOrder' ) as $sortParam ) {
            $params[$sortParam] = $$sortParam;
        }
        
        require_once 'CRM/Campaign/BAO/Survey.php';
        require_once 'CRM/Campaign/Page/DashBoard.php';
        $surveys     = CRM_Campaign_Page_DashBoard::getSurveySummary( $params );
        $searchCount = CRM_Campaign_BAO_Survey::getSurveySummary( $params, true );
        $iTotal      = $searchCount;
        
        if ( $searchCount > 0 ) {
            if ( $searchCount < $offset ) $offset = 0;
            foreach ( $surveys as $surveyID => $values ) {
                foreach ( $selectorCols as $col ) {
                    $searchRows[$surveyID][$col] = CRM_Utils_Array::value( $col, $values );
                }
            }
        }
        
        require_once "CRM/Utils/JSON.php";
        $selectorElements = $selectorCols;
        
        $iFilteredTotal = $iTotal;
        
        echo CRM_Utils_JSON::encodeDataTableSelector( $searchRows, $sEcho, $iTotal, $iFilteredTotal, $selectorElements );
        CRM_Utils_System::civiExit( );
    }
    
    /**
     * Retrieve petitions for petition dashboard.
     *
     **/
    function petitionList( ) 
    {
        //get the search criteria params.
        $searchParams = explode( ',', CRM_Utils_Array::value( 'searchCriteria', $_POST ) );
        
        $params = $searchRows = array( );
        foreach ( $searchParams as $param ) {
            if ( CRM_Utils_Array::value( $param, $_POST ) ) {
                $params[$param] = $_POST[$param];
            }
        }
        
        //this is sequence columns on datatable.
        $selectorCols = array( 'id',
                               'title', 
                               'campaign_id',
                               'campaign',
                               'activity_type_id',
                               'activity_type',
                               'is_default',
                               'is_active',
                               'isActive',
                               'action' );
        
        // get the data table params.
        $dataTableParams = array( 'sEcho'     => array( 'name'    => 'sEcho',
                                                        'type'    => 'Integer',
                                                        'default' => 0                ), 
                                  'offset'    => array( 'name'    => 'iDisplayStart',
                                                        'type'    => 'Integer',
                                                        'default' => 0                ),
                                  'rowCount'  => array( 'name'    => 'iDisplayLength',
                                                        'type'    => 'Integer',
                                                        'default' => 25               ),
                                  'sort'      => array( 'name'    => 'iSortCol_0',
                                                        'type'    => 'Integer',
                                                        'default' => 'created_date'   ),
                                  'sortOrder' => array( 'name'    => 'sSortDir_0',
                                                        'type'    => 'String', 
                                                        'default' => 'desc'           ) );
        foreach ( $dataTableParams as $pName => $pValues ) {
            $$pName = $pValues['default'];
            if ( CRM_Utils_Array::value( $pValues['name'], $_POST ) ) {
                $$pName = CRM_Utils_Type::escape( $_POST[$pValues['name']], $pValues['type'] );
                if ( $pName == 'sort' ) {
                    $$pName = $selectorCols[$$pName];
                }
            }
        }
        foreach ( array( 'sort', 'offset', 'rowCount', 'sortOrder' ) as $sortParam ) {
            $params[$sortParam] = $$sortParam;
        }
        
        require_once 'CRM/Campaign/BAO/Petition.php';
        require_once 'CRM/Campaign/Page/DashBoard.php';
        $petitions   = CRM_Campaign_Page_DashBoard::getPetitionSummary( $params );
        $searchCount = CRM_Campaign_BAO_Petition::getPetitionSummary( $params, true );
        $iTotal      = $searchCount;
        
        if ( $searchCount > 0 ) {
            if ( $searchCount < $offset ) $offset = 0;
            foreach ( $petitions as $petitionID => $values ) {
                foreach ( $selectorCols as $col ) {
                    $searchRows[$petitionID][$col] = CRM_Utils_Array::value( $col, $values );
                }
            }
        }
        
        require_once "CRM/Utils/JSON.php";
        $selectorElements = $selectorCols;
        
        $iFilteredTotal = $iTotal;
        
        echo CRM_Utils_JSON::encodeDataTableSelector( $searchRows, $sEcho, $iTotal, $iFilteredTotal, $selectorElements );
        CRM_Utils_System::civiExit( );
    }
    
}