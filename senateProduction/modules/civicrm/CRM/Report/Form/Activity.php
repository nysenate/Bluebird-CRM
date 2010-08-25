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

require_once 'CRM/Report/Form.php';

class CRM_Report_Form_Activity extends CRM_Report_Form {

    protected $_emailField         = false;
    protected $_customGroupExtends = array( 'Activity' );

    function __construct( ) {
        $this->_columns = array(  
                                'civicrm_contact'      =>
                                array( 'dao'     => 'CRM_Contact_DAO_Contact',
                                       'fields'  =>
                                       array(
                                             'source_contact_id' =>
                                             array( 'name'       => 'id',
                                                    'alias'      => 'contact_civireport',
                                                    'no_display' => true, 
                                                    'required'   => true, 
                                                    ),
                                             'contact_source'    =>
                                              array( 'name'      => 'display_name' ,
                                                     'title'     => ts( 'Source Contact Name' ),
                                                     'alias'     => 'contact_civireport',
                                                     'required'  => true,
                                                     'no_repeat' => true ),
                                              'contact_assignee' =>
                                              array( 'name'      => 'display_name' ,
                                                     'title'     => ts( 'Assignee Contact Name' ),
                                                     'alias'     => 'civicrm_contact_assignee',
                                                     'default'   => true ),
                                              'contact_target'   =>
                                              array( 'name'      => 'display_name' ,
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
                                             'contact_target'    => 
                                              array( 'name'      => 'sort_name' ,
                                                     'alias'     => 'civicrm_contact_target',
                                                     'title'     => ts( 'Target Contact Name' ),
                                                     'operator'  => 'like',
                                                     'type'      => CRM_Report_Form::OP_STRING  ) ),
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
                                                      'default'    => true ,
                                                      'type'       =>  CRM_Utils_Type::T_STRING 
                                                      ),
                                               'subject'           => 
                                               array( 'title'      => ts('Subject'),
                                                      'default'    => true ),
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
                                              'subject'             =>
                                              array( 'title'        => ts( 'Activity Subject' ),
                                                     'operator'     => 'like' ),
                                              'activity_type_id'    => 
                                              array( 'title'        => ts( 'Activity Type' ),
                                                     'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                                     'options'      => CRM_Core_PseudoConstant::activityType(), ), 
                                              'status_id'           => 
                                              array( 'title'        => ts( 'Activity Status' ),
                                                     'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                                     'options'      => CRM_Core_PseudoConstant::activityStatus(), ),
                                              ),
                                       'group_bys' =>             
                                       array( 'source_contact_id'  =>
                                              array('title'    => ts( 'Source Contact' ),
                                                    'default'  => true ),
                                              'activity_date_time' => 
                                              array( 'title'   => ts( 'Activity Date' ) ),
                                              'activity_type_id'   =>
                                              array( 'title'   => ts( 'Activity Type' ) ),
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
 
                                  );
        
        parent::__construct( );
    }

    function select( ) {
        $select = array( );
        $this->_columnHeaders = array( );
        foreach ( $this->_columns as $tableName => $table ) {
            if ( array_key_exists('fields', $table) ) {
                foreach ( $table['fields'] as $fieldName => $field ) {
                    if ( CRM_Utils_Array::value( 'required', $field ) ||
                         CRM_Utils_Array::value( $fieldName, $this->_params['fields'] ) ) {
                        if ( $tableName == 'civicrm_email' ) {
                            $this->_emailField = true;
                        } 

                        $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
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
        
        if ( $this->_emailField ) {
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
                    if ( $field['type'] & CRM_Utils_Type::T_DATE ) {
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
        $this->_groupBy   = array();
        if ( ! empty($this->_params['group_bys']) ) {
            foreach ( $this->_columns as $tableName => $table ) {
                if ( ! empty($table['group_bys']) ) {
                    foreach ( $table['group_bys'] as $fieldName => $field ) {
                        if ( CRM_Utils_Array::value( $fieldName, $this->_params['group_bys'] ) ) {
                            $this->_groupBy[] = $field['dbAlias'];
                        }
                    }
                }
            }
        }
        $this->_groupBy[] = "{$this->_aliases['civicrm_activity']}.id";
        $this->_groupBy   = "GROUP BY " . implode( ', ', $this->_groupBy ) . " ";
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
        $activityType   = CRM_Core_PseudoConstant::activityType( true, true );
        $activityStatus = CRM_Core_PseudoConstant::activityStatus();
        $viewLinks      = false;

        require_once 'CRM/Core/Permission.php';
        if ( CRM_Core_Permission::check( 'access CiviCRM' ) ) {
            $viewLinks  = true;
            $onHover    = ts('View Contact Summary for this Contact');
            $onHoverAct = ts('View Activity Record');
        }
        foreach ( $rows as $rowNum => $row ) {
            
            if ( array_key_exists('civicrm_contact_contact_source', $row ) ) {
                if ( $value = $row['civicrm_contact_source_contact_id'] ) {
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
                if ( $value = $row['civicrm_activity_assignment_assignee_contact_id'] ) {
                    if ( $viewLinks ) {
                        $url = CRM_Utils_System::url( "civicrm/contact/view"  , 
                                                      'reset=1&cid=' . $value ,
                                                      $this->_absoluteUrl );
                        $rows[$rowNum]['civicrm_contact_contact_assignee_link' ] = $url; 
                        $rows[$rowNum]['civicrm_contact_contact_assignee_hover'] = $onHover;
                    }
                    $entryFound = true; 
                }
            }
            
            if ( array_key_exists('civicrm_contact_contact_target', $row ) ) {
                if ( $value = $row['civicrm_activity_target_target_contact_id'] ) {
                    if ( $viewLinks ) {
                        $url = CRM_Utils_System::url( "civicrm/contact/view"  , 
                                                      'reset=1&cid=' . $value ,
                                                      $this->_absoluteUrl );
                        $rows[$rowNum]['civicrm_contact_contact_target_link' ] = $url; 
                        $rows[$rowNum]['civicrm_contact_contact_target_hover'] = $onHover;
                    }
                    $entryFound = true; 
                }
            }
            
            if ( array_key_exists('civicrm_activity_activity_type_id', $row ) ) {
                if ( $value = $row['civicrm_activity_activity_type_id'] ) {
                    $rows[$rowNum]['civicrm_activity_activity_type_id'] = $activityType[$value];
                    if ( $viewLinks ) {
                        // case activities get a special view link
                        if ( $rows[$rowNum]['civicrm_case_activity_case_id'] ) {
                            $url = CRM_Utils_System::url( "civicrm/case/activity/view"  , 
                                                          'reset=1&cid=' . $rows[$rowNum]['civicrm_contact_source_contact_id'] .
                                                          '&aid=' . $rows[$rowNum]['civicrm_activity_id'] . '&caseID=' . $rows[$rowNum]['civicrm_case_activity_case_id'],
                                                          $this->_absoluteUrl );
                        } else {
                            $url = CRM_Utils_System::url( "civicrm/contact/view/activity"  , 
                                                          'action=view&reset=1&cid=' . $rows[$rowNum]['civicrm_contact_source_contact_id'] .
                                                          '&id=' . $rows[$rowNum]['civicrm_activity_id'] . '&atype=' . $value ,
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
            
            if ( !$entryFound ) {
                break;
            }
        }
    }
}
