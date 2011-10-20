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

require_once 'CRM/Report/Form.php';

class CRM_Report_Form_Activity extends CRM_Report_Form {
  
    protected $_customGroupExtends = array( 'Activity' );

    function __construct( ) {
        $config = CRM_Core_Config::singleton( );
        $campaignEnabled = in_array( "CiviCampaign", $config->enableComponents );
        if ( $campaignEnabled ){
            require_once 'CRM/Campaign/BAO/Campaign.php';
            require_once 'CRM/Campaign/PseudoConstant.php';
            $getCampaigns = CRM_Campaign_BAO_Campaign::getPermissionedCampaigns( null, null, true, false, true );
            $this->activeCampaigns = $getCampaigns['campaigns'];
            asort( $this->activeCampaigns );
            $this->engagementLevels = CRM_Campaign_PseudoConstant::engagementLevel();
        }
        $this->activityTypes = CRM_Core_PseudoConstant::activityType( true, false, false, 'label', true );        
        asort( $this->activityTypes );
        
        $this->_columns = array(  
                                'civicrm_contact'      =>
                                array( 'dao'     => 'CRM_Contact_DAO_Contact',
                                       'fields'  =>
                                       array(
                                             'source_contact_id' =>
                                             array( 'name'       => 'id',
                                                    'alias'      => 'contact_civireport',
                                                    'no_display' => true, 
                                                    ),
                                             'contact_source'    =>
                                              array( 'name'      => 'sort_name' ,
                                                     'title'     => ts( 'Source Contact Name' ),
                                                     'alias'     => 'contact_civireport',
                                                     'no_repeat' => true ),
                                             'contact_assignee' =>
                                             array( 'name'      => 'sort_name' ,
                                                     'title'     => ts( 'Assignee Contact Name' ),
                                                     'alias'     => 'civicrm_contact_assignee',
                                                     'default'   => true ),
                                              'contact_target'   =>
                                              array( 'name'      => 'sort_name' ,
                                                     'title'     => ts( 'Target Contact Name' ),
                                                     'alias'     => 'civicrm_contact_target',
                                                     'default'   => true ),
                                              ),
                                       
                                       'filters' =>             
                                       array( 'contact_source'   =>
                                              array('name'       => 'sort_name' ,
                                                    'alias'      => 'contact_civireport',
                                                    'title'      => ts( 'Source Contact Name' ),
                                                    'operator'   => 'like',
                                                    'type'       => CRM_Report_Form::OP_STRING ),
                                              'contact_assignee' => 
                                              array( 'name'      => 'sort_name' ,
                                                     'alias'     => 'civicrm_contact_assignee',
                                                     'title'     => ts( 'Assignee Contact Name' ),
                                                     'operator'  => 'like',
                                                     'type'      => CRM_Report_Form::OP_STRING ),
                                              'contact_target'   => 
                                              array( 'name'      => 'sort_name' ,
                                                     'alias'     => 'civicrm_contact_target',
                                                     'title'     => ts( 'Target Contact Name' ),
                                                     'operator'  => 'like',
                                                     'type'      => CRM_Report_Form::OP_STRING  ),
                                              'current_user'     => 
                                              array( 'name'      => 'current_user',
                                                     'title'     => ts('Limit To Current User'),
                                                     'type'      => CRM_Utils_Type::T_INT,
                                                     'operatorType' => CRM_Report_Form::OP_SELECT,
                                                     'options'   => array('0'=>ts('No'), '1'=>ts('Yes') ) ) ),
                                       'grouping' => 'contact-fields',
                                       ),
                                
                                'civicrm_email'         =>
                                array( 'dao'     => 'CRM_Core_DAO_Email',
                                       'fields'  =>
                                       array( 'contact_source_email'   =>
                                              array( 'name'      => 'email' ,
                                                     'title'     => ts( 'Source Contact Email' ),
                                                     'alias'     => 'civicrm_email_source', ),
                                              'contact_assignee_email' =>
                                              array( 'name'      => 'email' ,
                                                     'title'     => ts( 'Assignee Contact Email' ),
                                                     'alias'     => 'civicrm_email_assignee', ),
                                              'contact_target_email'   =>
                                              array( 'name'      => 'email' ,
                                                     'title'     => ts( 'Target Contact Email' ),
                                                     'alias'     => 'civicrm_email_target', ),
                                              ),
                                       'order_bys' =>             
                                       array( 'source_contact_email'  =>
                                              array('name'  => 'email',
                                                    'title' => ts( 'Source Contact Email'),
                                                    'alias' => 'civicrm_email_source' ) ),
                                       ),
                                
                                'civicrm_activity'      =>
                                array( 'dao'     => 'CRM_Activity_DAO_Activity',
                                       'fields'  =>
                                       array(  'id'                => 
                                               array( 'no_display' => true,
                                                      'required'   => true   
                                                      ),
                                               'activity_type_id'  => 
                                               array( 'title'      => ts( 'Activity Type' ),
                                                      'default'    => true,
                                                      'type'       =>  CRM_Utils_Type::T_STRING 
                                                      ),
                                               'activity_subject'  => 
                                               array( 'title'      => ts('Subject'),
                                                      'default'    => true,
                                                      ),
                                               'source_contact_id' => 
                                               array( 'no_display' => true ,
                                                      'required'   => true , ),
                                               'activity_date_time'=> 
                                               array( 'title'      => ts( 'Activity Date'),
                                                      'default'    => true ),
                                               'status_id'         => 
                                               array( 'title'      => ts( 'Activity Status' ),
                                                      'default'    => true ,
                                                      'type'       =>  CRM_Utils_Type::T_STRING ), ),
                                       'filters' =>   
                                       array( 'activity_date_time'  => 
                                              array( 'default'      => 'this.month',
                                                     'operatorType' => CRM_Report_Form::OP_DATE),
                                              'activity_subject'    =>
                                              array( 'title'        => ts( 'Activity Subject' ) ),
                                              'activity_type_id'    => 
                                              array( 'title'        => ts( 'Activity Type' ),
                                                     'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                                     'options'      => $this->activityTypes, ), 
                                              'status_id'           => 
                                              array( 'title'        => ts( 'Activity Status' ),
                                                     'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                                     'options'      => CRM_Core_PseudoConstant::activityStatus(), ),
                                              ),
                                       'order_bys' =>             
                                       array( 'source_contact_id'  =>
                                              array('title'    => ts( 'Source Contact' ), 'default_weight' => '0' ),
                                              'activity_date_time' => 
                                              array( 'title'   => ts( 'Activity Date' ), 'default_weight' => '1' ),
                                              'activity_type_id'   =>
                                              array( 'title'   => ts( 'Activity Type' ), 'default_weight' => '2' ),
                                              ),
                                       'grouping' => 'activity-fields',
                                       'alias'    => 'activity'
                                       
                                       ),
                                
                                'civicrm_activity_assignment'      =>
                                array( 'dao'     => 'CRM_Activity_DAO_ActivityAssignment',
                                       'fields'  =>
                                       array(
                                             'assignee_contact_id' => 
                                             array( 'no_display' => true,
                                                    'required'   => true ), ), 
                                       'alias'   => 'activity_assignment'
                                       ),
                                'civicrm_activity_target'        =>
                                array( 'dao'     => 'CRM_Activity_DAO_ActivityTarget',
                                       'fields'  =>
                                       array(
                                             'target_contact_id' => 
                                             array( 'no_display' => true,
                                                    'required'   => true ), ),
                                       'alias'   => 'activity_target'
                                       ),
                                'civicrm_case_activity'        =>
                                array( 'dao'     => 'CRM_Case_DAO_CaseActivity',
                                       'fields'  =>
                                       array(
                                             'case_id' =>
                                             array( 'name'       => 'case_id',
                                                    'no_display' => true,
                                                    'required'   => true,
                                                    ),),
                                       'alias'   => 'case_activity'
                                       ),
 
                                  ) + $this->addAddressFields(false, true);
        
        if ( $campaignEnabled ) {
            // Add display column and filter for Survey Results, Campaign and Engagement Index if CiviCampaign is enabled
            
            $this->_columns['civicrm_activity']['fields']['result']   = array('title' => 'Survey Result',
                                                                              'default' => 'false');
            $this->_columns['civicrm_activity']['filters']['result']  = array( 'title'        => ts( 'Survey Result' ),
                                                                               'operator'     => 'like',
                                                                               'type'       =>  CRM_Utils_Type::T_STRING  );
            if ( !empty( $this->activeCampaigns ) ){
                $this->_columns['civicrm_activity']['fields']['campaign_id']  = array( 'title' => 'Campaign',
                                                                                       'default' => 'false' );
                $this->_columns['civicrm_activity']['filters']['campaign_id'] = array( 'title'        => ts( 'Campaign' ),
                                                                                       'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                                                                       'options'     => $this->activeCampaigns  );
            }
            if ( !empty( $this->engagementLevels ) ) {
                $this->_columns['civicrm_activity']['fields']['engagement_level']  = array( 'title' => 'Engagement Index',
                                                                                            'default' => 'false' );
                $this->_columns['civicrm_activity']['filters']['engagement_level'] = array( 'title'        => ts( 'Engagement Index' ),
                                                                                            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                                                                            'options'     => $this->engagementLevels  );                
            }
        }
        $this->_groupFilter = true; 
        $this->_tagFilter = true;
        parent::__construct( );
    }

    function select( ) {
        $select    = array( );
        $seperator =  CRM_CORE_DAO::VALUE_SEPARATOR;
        $this->_columnHeaders = array( );
        foreach ( $this->_columns as $tableName => $table ) {
            if ( array_key_exists('fields', $table) ) {
                foreach ( $table['fields'] as $fieldName => $field ) {
                    if ( CRM_Utils_Array::value( 'required', $field ) ||
                         CRM_Utils_Array::value( $fieldName, $this->_params['fields'] ) ) {

                        if ( !CRM_Utils_Array::value( 'activity_type_id', $this->_params['group_bys'] ) &&
                             ( in_array( $fieldName, array('contact_assignee', 'assignee_contact_id' ) ) || 
                               in_array( $fieldName, array( 'contact_target', 'target_contact_id' ) ) ) ) {
                            $orderByRef = "activity_assignment_civireport.assignee_contact_id";
                            if ( in_array( $fieldName, array( 'contact_target', 'target_contact_id' ) ) ) {
                                $orderByRef = "activity_target_civireport.target_contact_id";
                            }
                            $select[] = "GROUP_CONCAT(DISTINCT {$field['dbAlias']}  ORDER BY {$orderByRef} SEPARATOR '{$seperator}') as {$tableName}_{$fieldName}";
                        } else {
                            $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
                        }
                        $this->_columnHeaders["{$tableName}_{$fieldName}"]['type']  = CRM_Utils_Array::value( 'type', $field );
                        $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = CRM_Utils_Array::value( 'title', $field );
                        $this->_columnHeaders["{$tableName}_{$fieldName}"]['no_display'] = CRM_Utils_Array::value( 'no_display', $field );
                    }
                }
            }
        }

        $this->_select = "SELECT " . implode( ', ', $select ) . " ";

    }

    function from( ) {

        $this->_from = "
        FROM civicrm_activity {$this->_aliases['civicrm_activity']}
        
             LEFT JOIN civicrm_activity_target  {$this->_aliases['civicrm_activity_target']} 
                    ON {$this->_aliases['civicrm_activity']}.id = {$this->_aliases['civicrm_activity_target']}.activity_id 
             LEFT JOIN civicrm_activity_assignment {$this->_aliases['civicrm_activity_assignment']}
                    ON {$this->_aliases['civicrm_activity']}.id = {$this->_aliases['civicrm_activity_assignment']}.activity_id 
             LEFT JOIN civicrm_contact contact_civireport
                    ON {$this->_aliases['civicrm_activity']}.source_contact_id = contact_civireport.id 
             LEFT JOIN civicrm_contact civicrm_contact_target 
                    ON {$this->_aliases['civicrm_activity_target']}.target_contact_id = civicrm_contact_target.id
             LEFT JOIN civicrm_contact civicrm_contact_assignee 
                    ON {$this->_aliases['civicrm_activity_assignment']}.assignee_contact_id = civicrm_contact_assignee.id
            
             {$this->_aclFrom}
             LEFT JOIN civicrm_option_value 
                    ON ( {$this->_aliases['civicrm_activity']}.activity_type_id = civicrm_option_value.value )
             LEFT JOIN civicrm_option_group 
                    ON civicrm_option_group.id = civicrm_option_value.option_group_id
             LEFT JOIN civicrm_case_activity case_activity_civireport 
                    ON case_activity_civireport.activity_id = {$this->_aliases['civicrm_activity']}.id
             LEFT JOIN civicrm_case 
                    ON case_activity_civireport.case_id = civicrm_case.id
             LEFT JOIN civicrm_case_contact 
                    ON civicrm_case_contact.case_id = civicrm_case.id ";
        
        if ( $this->isTableSelected('civicrm_email') ) {
            $this->_from .= "
            LEFT JOIN civicrm_email civicrm_email_source 
                   ON {$this->_aliases['civicrm_activity']}.source_contact_id = civicrm_email_source.contact_id AND
                      civicrm_email_source.is_primary = 1 

            LEFT JOIN civicrm_email civicrm_email_target 
                   ON {$this->_aliases['civicrm_activity_target']}.target_contact_id = civicrm_email_target.contact_id AND 
                      civicrm_email_target.is_primary = 1

            LEFT JOIN civicrm_email civicrm_email_assignee 
                   ON {$this->_aliases['civicrm_activity_assignment']}.assignee_contact_id = civicrm_email_assignee.contact_id AND 
                      civicrm_email_assignee.is_primary = 1 ";
        }
        $this->addAddressFromClause();
    }

    function where( ) {
        $this->_where = " WHERE civicrm_option_group.name = 'activity_type' AND 
                                {$this->_aliases['civicrm_activity']}.is_test = 0 AND
                                {$this->_aliases['civicrm_activity']}.is_deleted = 0 AND
                                {$this->_aliases['civicrm_activity']}.is_current_revision = 1";
        
        $clauses = array( );
        foreach ( $this->_columns as $tableName => $table ) {
            if ( array_key_exists('filters', $table) ) {

                foreach ( $table['filters'] as $fieldName => $field ) {
                    $clause = null;
                    if ( CRM_Utils_Array::value( 'type', $field ) & CRM_Utils_Type::T_DATE ) {
                        $relative = CRM_Utils_Array::value( "{$fieldName}_relative", $this->_params );
                        $from     = CRM_Utils_Array::value( "{$fieldName}_from"    , $this->_params );
                        $to       = CRM_Utils_Array::value( "{$fieldName}_to"      , $this->_params );
                        
                        $clause = $this->dateClause( $field['name'], $relative, $from, $to, $field['type'] );
                    } else {
                        $op = CRM_Utils_Array::value( "{$fieldName}_op", $this->_params );
                        if ( $op ) {
                            $clause = 
                                $this->whereClause( $field,
                                                    $op,
                                                    CRM_Utils_Array::value( "{$fieldName}_value", $this->_params ),
                                                    CRM_Utils_Array::value( "{$fieldName}_min", $this->_params ),
                                                    CRM_Utils_Array::value( "{$fieldName}_max", $this->_params ) );
                        }
                    }
                    
                    if ( $field['name'] == 'current_user' ) {
                        if ( CRM_Utils_Array::value( "{$fieldName}_value", $this->_params ) == 1 ) {
                            // get current user
                            $session = CRM_Core_Session::singleton( );
                            if ( $contactID = $session->get( 'userID' ) ) {
                                $clause = "( contact_civireport.id = "   . $contactID . 
                                    " OR civicrm_contact_assignee.id = " . $contactID . 
                                    " OR civicrm_contact_target.id = "   . $contactID . " )";
                            } else {
                                $clause = NULL;
                            }
                        } else { 
                            $clause = NULL;
                        }
                    }
                    if ( ! empty( $clause ) ) {
                        $clauses[] = $clause;
                    }
                }
            }
        }

        if ( empty( $clauses ) ) {
            $this->_where .= " ";
        } else {
            $this->_where .= " AND " . implode( ' AND ', $clauses );
        }
         
        if ( $this->_aclWhere ) {
            $this->_where .= " AND {$this->_aclWhere} ";
        } 
    }

    function groupBy( ) {
        $this->_groupBy   = "GROUP BY {$this->_aliases['civicrm_activity']}.id";
    }

    function buildACLClause( $tableAlias ) {
        //override for ACL( Since Cotact may be source
        //contact/assignee or target also it may be null )
        
        require_once 'CRM/Core/Permission.php';
        require_once 'CRM/Contact/BAO/Contact/Permission.php';
        if ( CRM_Core_Permission::check( 'view all contacts' ) ) {
            $this->_aclFrom = $this->_aclWhere = null;
            return;
        }
        
        $session = CRM_Core_Session::singleton( );
        $contactID =  $session->get( 'userID' );
        if ( ! $contactID ) {
            $contactID = 0;
        }
        $contactID = CRM_Utils_Type::escape( $contactID, 'Integer' );
        
        CRM_Contact_BAO_Contact_Permission::cache( $contactID );
        $clauses = array();
        foreach( $tableAlias as $k => $alias ) {
            $clauses[] = " INNER JOIN civicrm_acl_contact_cache aclContactCache_{$k} ON ( {$alias}.id = aclContactCache_{$k}.contact_id OR {$alias}.id IS NULL ) AND aclContactCache_{$k}.user_id = $contactID ";  
        }
        
        $this->_aclFrom  = implode(" ", $clauses );
        $this->_aclWhere = null;
    }
    function postProcess( ) {
        
        $this->buildACLClause( array( 'contact_civireport' , 'civicrm_contact_target', 'civicrm_contact_assignee' ) );
        parent::postProcess();
    }

    function alterDisplay( &$rows ) {
        // custom code to alter rows
        
        $entryFound     = false;
        $activityType   = CRM_Core_PseudoConstant::activityType( true, true, false, 'label', true );
        $activityStatus = CRM_Core_PseudoConstant::activityStatus();
        $viewLinks      = false;
        $seperator      = CRM_CORE_DAO::VALUE_SEPARATOR;
        $context        = CRM_Utils_Request::retrieve( 'context', 'String', $this, false, 'report' );
 
        require_once 'CRM/Core/Permission.php';
        if ( CRM_Core_Permission::check( 'access CiviCRM' ) ) {
            $viewLinks  = true;
            $onHover    = ts('View Contact Summary for this Contact');
            $onHoverAct = ts('View Activity Record');
        }
        foreach ( $rows as $rowNum => $row ) {
            
            if ( array_key_exists('civicrm_contact_contact_source', $row ) ) {
                if ( $value = $row['civicrm_activity_source_contact_id'] ) {
                    if ( $viewLinks ) {
                        $url = CRM_Utils_System::url( "civicrm/contact/view"  , 
                                                      'reset=1&cid=' . $value ,
                                                      $this->_absoluteUrl );
                        $rows[$rowNum]['civicrm_contact_contact_source_link' ] = $url;
                        $rows[$rowNum]['civicrm_contact_contact_source_hover'] = $onHover;
                    }
                    $entryFound = true; 
                }
            }
            
            if ( array_key_exists('civicrm_contact_contact_assignee', $row ) ) {
                $assigneeNames = explode( $seperator, $row['civicrm_contact_contact_assignee'] );
                if ( $value = $row['civicrm_activity_assignment_assignee_contact_id'] ) {
                    $assigneeContactIds = explode( $seperator, $value );
                    $link = array( );
                    if ( $viewLinks ) {
                        foreach ( $assigneeContactIds as $id => $value ) {
                            $url = CRM_Utils_System::url( "civicrm/contact/view", 
                                                            'reset=1&cid=' . $value );
                            $link[] = "<a title='".$onHover."' href='" . $url . "'>{$assigneeNames[$id]}</a>";
                        }
                        $rows[$rowNum]['civicrm_contact_contact_assignee'] = implode( '; ',$link );
                    }
                    $entryFound = true; 
                }
            }
            
            if ( array_key_exists('civicrm_contact_contact_target', $row ) ) {
                $targetNames = explode( $seperator, $row['civicrm_contact_contact_target'] );
                if ( $value = $row['civicrm_activity_target_target_contact_id'] ) {
                    $targetContactIds = explode( $seperator, $value );
                    $link = array( );
                    if ( $viewLinks ) {
                        foreach ( $targetContactIds as $id => $value ) {
                            $url = CRM_Utils_System::url( "civicrm/contact/view", 
                                                          'reset=1&cid=' . $value );
                            $link[] = "<a title='".$onHover."' href='" . $url . "'>{$targetNames[$id]}</a>";
                        }
                        $rows[$rowNum]['civicrm_contact_contact_target'] = implode( '; ',$link );
                    }
                    $entryFound = true; 
                }
            }
            
            if ( array_key_exists('civicrm_activity_activity_type_id', $row ) ) {
                if ( $value = $row['civicrm_activity_activity_type_id'] ) {
                    $rows[$rowNum]['civicrm_activity_activity_type_id'] = $activityType[$value];
                    if ( $viewLinks ) {
                        // Check for target contact id(s) and use the first contact id in that list for view activity link if found,
                        // else use source contact id
                        if ( !empty( $rows[$rowNum]['civicrm_activity_target_target_contact_id'] ) ) {
                            $targets = explode( $seperator, $rows[$rowNum]['civicrm_activity_target_target_contact_id']);
                            $cid = $targets[0];
                        } else {
                            $cid = $rows[$rowNum]['civicrm_activity_source_contact_id'];
                        }

                        // case activities get a special view link
                        if ( $rows[$rowNum]['civicrm_case_activity_case_id'] ) {
                            $url = CRM_Utils_System::url( "civicrm/case/activity/view"  , 
                                                          'reset=1&cid=' . $cid .
                                                          '&aid=' . $rows[$rowNum]['civicrm_activity_id'] . '&caseID=' . $rows[$rowNum]['civicrm_case_activity_case_id'] . '&context=' . $context,
                                                          $this->_absoluteUrl );
                        } else {
                            $url = CRM_Utils_System::url( "civicrm/contact/view/activity"  , 
                                                          'action=view&reset=1&cid=' . $cid .
                                                          '&id=' . $rows[$rowNum]['civicrm_activity_id'] . '&atype=' . $value . '&context=' . $context ,
                                                          $this->_absoluteUrl );
                        }
                        $rows[$rowNum]['civicrm_activity_activity_type_id_link'] = $url;
                        $rows[$rowNum]['civicrm_activity_activity_type_id_hover'] = $onHoverAct;
                    }
                    $entryFound = true;
                }
            }
            
            if ( array_key_exists('civicrm_activity_status_id', $row ) ) {
                if ( $value = $row['civicrm_activity_status_id'] ) {
                    $rows[$rowNum]['civicrm_activity_status_id'] = $activityStatus[$value];
                    $entryFound = true;
                }
            }
            
            if ( array_key_exists('civicrm_activity_campaign_id', $row ) ) {
                if ( $value = $row['civicrm_activity_campaign_id'] ) {
                    $rows[$rowNum]['civicrm_activity_campaign_id'] = $this->activeCampaigns[$value];
                    $entryFound = true;
                }
            }

            if ( array_key_exists('civicrm_activity_engagement_level', $row ) ) {
                if ( $value = $row['civicrm_activity_engagement_level'] ) {
                    $rows[$rowNum]['civicrm_activity_engagement_level'] = $this->engagementLevels[$value];
                    $entryFound = true;
                }
            }

            $entryFound =  $this->alterDisplayAddressFields($row,$rows,$rowNum,'activity','List all activities for this ')?true:$entryFound;
 
            if ( !$entryFound ) {
                break;
            }
        }
    }
}
