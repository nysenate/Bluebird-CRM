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

class CRM_Activity_BAO_Query 
{


    /** 
     * build select for Case 
     * 
     * @return void  
     * @access public  
     */
    static function select( &$query ) {
        
        if ( CRM_Utils_Array::value( 'activity_id', $query->_returnProperties ) ) {
            $query->_select['activity_id']  = "civicrm_activity.id as activity_id";
            $query->_element['activity_id'] = 1;
            $query->_tables['civicrm_activity'] = $query->_whereTables['civicrm_activity'] = 1;
        }
        
        if ( CRM_Utils_Array::value( 'activity_type_id', $query->_returnProperties ) ) {
            $query->_select['activity_type_id']  = "activity_type.label as activity_type_id";
            $query->_element['activity_type_id'] = 1;
            $query->_tables['civicrm_activity'] = 1;
            $query->_tables['activity_type'] = 1;
            $query->_whereTables['civicrm_activity'] = 1;
            $query->_whereTables['activity_type'] = 1;
        }
        
        if ( CRM_Utils_Array::value( 'activity_subject', $query->_returnProperties ) ) {
            $query->_select['activity_subject']  = "civicrm_activity.subject as activity_subject";
            $query->_element['activity_subject'] = 1;
			$query->_tables['civicrm_activity'] = $query->_whereTables['civicrm_activity'] = 1;
        }
        
        if ( CRM_Utils_Array::value( 'activity_date_time', $query->_returnProperties ) ) {
            $query->_select['activity_date_time']  = "civicrm_activity.activity_date_time as activity_date_time";
            $query->_element['activity_date_time'] = 1;
			$query->_tables['civicrm_activity'] = $query->_whereTables['civicrm_activity'] = 1;
        }
        
        if ( CRM_Utils_Array::value( 'activity_status_id', $query->_returnProperties ) ) {
            $query->_select['activity_status_id']  = "activity_status.label as activity_status_id";
            $query->_element['activity_status_id'] = 1;
            $query->_tables['civicrm_activity'] = 1;
            $query->_tables['activity_status'] = 1;
            $query->_whereTables['civicrm_activity'] = 1;
            $query->_whereTables['activity_status'] = 1;
        }  

        if ( CRM_Utils_Array::value( 'activity_duration', $query->_returnProperties ) ) {
            $query->_select['activity_duration']  = "civicrm_activity.duration as activity_duration";
            $query->_element['activity_duration'] = 1;
			$query->_tables['civicrm_activity'] = $query->_whereTables['civicrm_activity'] = 1;
        }
        
        if ( CRM_Utils_Array::value( 'activity_location', $query->_returnProperties ) ) {
            $query->_select['activity_location']  = "civicrm_activity.location as activity_location";
            $query->_element['activity_location'] = 1;
			$query->_tables['civicrm_activity'] = $query->_whereTables['civicrm_activity'] = 1;
        }
        
        if ( CRM_Utils_Array::value( 'activity_details', $query->_returnProperties ) ) {
            $query->_select['activity_details']  = "civicrm_activity.details as activity_details";
            $query->_element['activity_details'] = 1;
			$query->_tables['civicrm_activity'] = $query->_whereTables['civicrm_activity'] = 1;
        }

        if ( CRM_Utils_Array::value( 'source_record_id', $query->_returnProperties ) ) {
            $query->_select['source_record_id']  = "civicrm_activity.source_record_id as source_record_id";
            $query->_element['source_record_id'] = 1;
			$query->_tables['civicrm_activity'] = $query->_whereTables['civicrm_activity'] = 1;
        }

        if ( CRM_Utils_Array::value( 'activity_is_test', $query->_returnProperties ) ) {
            $query->_select['activity_is_test']  = "civicrm_activity.is_test as activity_is_test";
            $query->_element['activity_is_test'] = 1;
            $query->_tables['civicrm_activity'] = $query->_whereTables['civicrm_activity'] = 1;
        }
    }

     /** 
     * Given a list of conditions in query generate the required
     * where clause
     * 
     * @return void 
     * @access public 
     */ 
    static function where( &$query ) 
    {
        $isTest = false;
        $grouping = null;
        foreach ( array_keys( $query->_params ) as $id ) {
            if ( substr( $query->_params[$id][0], 0, 9) == 'activity_' ) {
            	if ( $query->_mode == CRM_Contact_BAO_QUERY::MODE_CONTACTS ) {
                	$query->_useDistinct = true;
                }
                $grouping = $query->_params[$id][3];
                self::whereClauseSingle( $query->_params[$id], $query );
                if ( $query->_params[$id][0] == 'activity_test' ) {
                    $isTest = true;
                }
            }
        }
        
        if ( $grouping !== null &&
             !$isTest ) {
            $values = array( 'activity_test', '=', 0, $grouping, 0 );
            self::whereClauseSingle( $values, $query );
        }
    }
    
    /** 
     * where clause for a single field
     * 
     * @return void 
     * @access public 
     */ 
    static function whereClauseSingle( &$values, &$query ) 
    {
        list( $name, $op, $value, $grouping, $wildcard ) = $values;
        
        $strtolower = function_exists('mb_strtolower') ? 'mb_strtolower' : 'strtolower';
        $query->_tables['civicrm_activity'] = $query->_whereTables['civicrm_activity'] = 1;
        
        // Always exclude activities where is_deleted is true, and where is_current_revision is false
        $query->_where[$grouping][] = ' civicrm_activity.is_deleted = 0 AND civicrm_activity.is_current_revision = 1';

        switch ( $name ) {
        
        case 'activity_type_id':
            $types  = CRM_Core_PseudoConstant::activityType( true, true );
            $clause = array( );
            if ( is_array( $value ) ) {
                foreach ( $value as $id => $dontCare ) {
                    if ( array_key_exists( $id, $types ) && $dontCare ) {
                        $clause[] = "'" . CRM_Utils_Type::escape( $types[$id], 'String' ) . "'";
                    }
                } 
                $activityTypes = implode( ',', array_keys( $value ) );
            } else {
                $clause[] = "'" . CRM_Utils_Type::escape( $value, 'String' ) . "'";
                $activityTypes = $value;
            }
            $query->_where[$grouping][] = ' civicrm_activity.activity_type_id IN (' . $activityTypes . ')';
            $query->_qill [$grouping][]  = ts('Activity Type') . ' ' . implode( ' ' . ts('or') . ' ', $clause );
            
            break;
            
        case 'activity_role':
            CRM_Contact_BAO_Query::$_activityRole = $values[2];
             
            //for activity target name
            $activityTargetName = $query->getWhereValues( 'activity_target_name', $grouping );
            if ( ! $activityTargetName[2] ) {
                $name = null;
            } else {
                $name = trim( $activityTargetName[2] );
                $name = strtolower( CRM_Core_DAO::escapeString( $name ) );
            }
            
            $query->_where[$grouping][] = " contact_a.sort_name LIKE '%{$name}%'";
           
            if ( $values[2] == 1 ) {
                $query->_where[$grouping][] = " civicrm_activity_target.activity_id = civicrm_activity.id AND civicrm_activity_target.target_contact_id = contact_a.id";
                $query->_tables['civicrm_activity_target']   = $query->_whereTables['civicrm_activity_target'] = 1;
                $query->_qill[$grouping][]  = ts( 'Activity with').  " '$name'";
                
            } else if ( $values[2] == 2 ) {
                $query->_where[$grouping][] = " civicrm_activity.source_contact_id = contact_a.id";
                $query->_qill[$grouping][]  = ts( 'Activity created by').  " '$name'";
                
            } else if ( $values[2] == 3 ) {
                $query->_where[$grouping][] = " civicrm_activity_assignment.activity_id = civicrm_activity.id AND civicrm_activity_assignment.assignee_contact_id = contact_a.id";
                $query->_tables['civicrm_activity_assignment']   = $query->_whereTables['civicrm_activity_assignment'] = 1;
                $query->_qill[$grouping][]  = ts( 'Activity assigned to').  " '$name'";
            }
            
            break;
       
        case 'activity_status':
            $status = CRM_Core_PseudoConstant::activityStatus( );
            $clause = array( );
            if ( is_array( $value ) ) {
                foreach ( $value as $k => $v) { 
                    if ($k) { 
                        $clause[] = "'" . CRM_Utils_Type::escape( $status[$k], 'String' ) . "'";
                    }
                }
            } else {
                $clause[] = "'" . CRM_Utils_Type::escape( $value, 'String' ) . "'";
            }
            $query->_where[$grouping][] = ' civicrm_activity.status_id IN (' .
                implode( ',', array_keys( $value ) ) .
                ')';
            $query->_qill [$grouping][]  = ts('Activity Status') . ' - ' . implode( ' ' . ts('or') . ' ',  $clause);
            
            break;
            
        case 'activity_subject':
            $n = trim( $value );
            $value = strtolower(CRM_Core_DAO::escapeString($n));
            if ( $wildcard ) {
                if ( strpos( $value, '%' ) !== false ) {
                    // only add wild card if not there
                    $value = "'$value'";
                } else {
                    $value = "'%$value%'";
                }
                $op    = 'LIKE';
            } else {
                $value = "'$value'";
            }
            $wc = ( $op != 'LIKE' ) ? "LOWER(civicrm_activity.subject)" : "civicrm_activity.subject";
            $query->_where[$grouping][] = " $wc $op $value";
            $query->_qill[$grouping][]  = ts( 'Subject' ) . " $op - '$n'";
            
            break;
                   
        case 'activity_test':
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( "civicrm_activity.is_test", $op, $value, "Integer" ) ;
           if ( $value ) {
               $query->_qill[$grouping][]  = ts( 'Find Test Activities' );
           }
            
            break;
                
        case 'activity_date':
        case 'activity_date_low':
        case 'activity_date_high':
            $query->dateQueryBuilder( $values,
                                     'civicrm_activity', 'activity_date', 'activity_date_time', ts('Activity Date') );

            break;
        case 'activity_tags':
            require_once'CRM/Core/BAO/Tag.php';
            $value = array_keys( $value );
            $activityTags = CRM_Core_BAO_Tag::getTagsUsedFor('civicrm_activity');
            
            $names = array( );
            $val   = array( );
            if ( is_array( $value ) ) {
                foreach ($value as $k => $v) {
                    $names[] = $activityTags[$v];
                }
            } 
            $query->_where[$grouping][] = "civicrm_activity_tag.tag_id IN (". implode( ",",$value) .")";
            $query->_qill[$grouping ][] = ts( 'Activity Tag %1', array( 1 => $op))  . ' ' . implode( ' ' . ts('OR') . ' ', $names);
            $query->_tables['civicrm_activity_tag'] = $query->_whereTables['civicrm_activity_tag'] = 1;
            
            break;
            
        }
        
    }
    
    static function from( $name, $mode, $side ) {
        $from = null;
        switch ( $name ) {
            
        case 'civicrm_activity': 
            if ( $mode & CRM_Contact_BAO_Query::MODE_ACTIVITY ) {
                $side = ' INNER ';
                
            }
		    
            $activityRole = CRM_Contact_BAO_Query::$_activityRole;
            if ( $activityRole == 1 ) { 
                $from .= " $side JOIN civicrm_activity_target ON civicrm_activity_target.target_contact_id = contact_a.id ";
                $from .= " $side JOIN civicrm_activity ON civicrm_activity.id = civicrm_activity_target.activity_id ";
            } else if ( in_array( $activityRole, array( 0, 2 ) ) ) {
                $from .= " $side JOIN civicrm_activity ON civicrm_activity.source_contact_id = contact_a.id ";
            } else if ( $activityRole == 3 ) {
                $from .= " $side JOIN civicrm_activity_assignment ON civicrm_activity_assignment.assignee_contact_id = contact_a.id ";
                $from .= " $side JOIN civicrm_activity ON civicrm_activity.id = civicrm_activity_assignment.activity_id ";
            }
		    
            break;
			
        case 'activity_status':
            $from = " $side JOIN civicrm_option_group option_group_activity_status ON (option_group_activity_status.name = 'activity_status')";
            $from .= " $side JOIN civicrm_option_value activity_status ON (civicrm_activity.status_id = activity_status.value 
                               AND option_group_activity_status.id = activity_status.option_group_id ) ";
            
            break;
			
        case 'activity_type':
            $from = " $side JOIN civicrm_option_group option_group_activity_type ON (option_group_activity_type.name = 'activity_type')";
            $from .= " $side JOIN civicrm_option_value activity_type ON (civicrm_activity.activity_type_id = activity_type.value 
                               AND option_group_activity_type.id = activity_type.option_group_id ) ";
			
            break;
            
        case 'civicrm_activity_tag':            
            $from .= " $side JOIN civicrm_entity_tag as civicrm_activity_tag ON ( civicrm_activity_tag.entity_table = 'civicrm_activity' AND civicrm_activity_tag.entity_id = civicrm_activity.id ) ";
			break;
        }
        
        return $from;
    }
    
    
    /**
     * getter for the qill object
     *
     * @return string
     * @access public
     */
    function qill( ) {
        return (isset($this->_qill)) ? $this->_qill : "";
    }
    
    
    /**
     * add all the elements shared between case activity search  and advanaced search
     *
     * @access public 
     * @return void
     * @static
     */  
    static function buildSearchForm( &$form ) 
    {
        $activityOptions = CRM_Core_PseudoConstant::activityType( true, true );
        asort( $activityOptions );
        foreach ( $activityOptions as $activityID => $activity ) {
            $form->_activityElement =& $form->addElement( 'checkbox', "activity_type_id[$activityID]", null, $activity,array('onClick' => 'showCustomData( this.id );'));
        }
        $form->addDate( 'activity_date_low', ts( 'Activity Dates - From' ), false, array( 'formatType' => 'searchDate') );
        $form->addDate( 'activity_date_high', ts( 'To' ), false, array( 'formatType' => 'searchDate') );
        
        $activityRoles  = array( 1 => ts( 'With' ), 2 => ts( 'Created by' ), 3 => ts( 'Assigned to' ) );
        $form->addRadio( 'activity_role', ts( 'Contact Role and Name' ), $activityRoles, null, '<br />');
        $form->setDefaults( array( 'activity_role' => 1 ) );
        
        $form->addElement( 'text', 'activity_target_name', ts( 'Contact Name' ), CRM_Core_DAO::getAttribute( 'CRM_Contact_DAO_Contact', 'sort_name' ) );
        
        $activityStatus = CRM_Core_PseudoConstant::activityStatus( );
        foreach ( $activityStatus as $activityStatusID => $activityStatusName ) {
            $activity_status[] = HTML_QuickForm::createElement( 'checkbox', $activityStatusID, null, $activityStatusName );
        }
        $form->addGroup( $activity_status, 'activity_status', ts( 'Activity Status' ) );
        $form->setDefaults( array( 'activity_status[1]' => 1, 'activity_status[2]' => 1 ) );
        $form->addElement( 'text', 'activity_subject', ts( 'Subject' ), CRM_Core_DAO::getAttribute( 'CRM_Contact_DAO_Contact', 'sort_name') );
        $form->addElement( 'checkbox', 'activity_test', ts( 'Find Test Activities?' ) );
        require_once 'CRM/Core/BAO/Tag.php';
        $activity_tags = CRM_Core_BAO_Tag::getTags( 'civicrm_activity' );
        if ( $activity_tags ) {
            foreach ($activity_tags as $tagID => $tagName) {
                $form->_tagElement =& $form->addElement('checkbox', "activity_tags[$tagID]", 
                                                        null, $tagName);         
            }
        }
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $extends = array( 'Activity' );
        $groupDetails = CRM_Core_BAO_CustomGroup::getGroupDetail( null, true, $extends );
        if ( $groupDetails ) {
            require_once 'CRM/Core/BAO/CustomField.php';
            $form->assign( 'activityGroupTree', $groupDetails );
            foreach ( $groupDetails as $group ) {
                foreach ( $group['fields'] as $field ) {
                    $fieldId = $field['id'];               
                    $elementName = 'custom_' . $fieldId;
                    CRM_Core_BAO_CustomField::addQuickFormElement( $form, $elementName, $fieldId, false, false, true );
                }
            }
        }
    }

    static function addShowHide( &$showHide ) 
    {
        $showHide->addHide( 'caseActivityForm' );
        $showHide->addShow( 'caseActivityForm_show' );
    }
    
    static function defaultReturnProperties( $mode ) 
    {
        $properties = null;
        if ( $mode & CRM_Contact_BAO_Query::MODE_ACTIVITY ) {
            $properties = array(  
                                'activity_id'         => 1, 
                                'contact_type'        => 1, 
                                'contact_sub_type'    => 1, 
                                'sort_name'           => 1, 
                                'display_name'        => 1,
                                'activity_type_id'	  => 1,
                                'activity_subject'	  => 1,
                                'activity_date_time'  => 1,
                                'activity_duration'	  => 1,
                                'activity_location'   => 1,
                                'activity_details'    => 1,
                                'activity_status_id'  => 1,
                                'source_contact_id'   => 1,
                                'source_record_id'    => 1,
                                'activity_is_test'    => 1
                                );

            // also get all the custom activity properties
            require_once "CRM/Core/BAO/CustomField.php";
            $fields = CRM_Core_BAO_CustomField::getFieldsForImport('Activity');
            if ( ! empty( $fields ) ) {
                foreach ( $fields as $name => $dontCare ) {
                    $properties[$name] = 1;
                }
            }
        }
        return $properties;
    }

}