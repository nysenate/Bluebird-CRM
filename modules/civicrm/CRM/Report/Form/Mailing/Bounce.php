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

require_once 'CRM/Report/Form.php';

class CRM_Report_Form_Mailing_Bounce extends CRM_Report_Form {

    protected $_summary      = null;

    protected $_emailField   = false;
    
    protected $_phoneField   = false;
    
	# just a toggle we use to build the from
	protected $_mailingidField = false;
	
    protected $_customGroupExtends = array( 'Contact', 'Individual', 'Household', 'Organization' );
    
    protected $_charts  = array( ''         => 'Tabular',
                                 'barChart' => 'Bar Chart',
                                 'pieChart' => 'Pie Chart'
                                 );

    function __construct( ) {
        $this->_columns = array(); 
		
		$this->_columns['civicrm_contact'] = array(
			'dao' => 'CRM_Contact_DAO_Contact',
			'fields' => array(
				'id' => array( 
					'title' => ts('Contact ID'),
					'required'  => true, 
				), 						
				'first_name' => array(
					'title' => ts('First Name'),
					'required' => true,
					'no_repeat' => true,	
				),
				'last_name' => array(
					'title' => ts('Last Name'),
					'required' => true,
					'no_repeat' => true,	
				),
			),
			'filters' => array( 
				'sort_name' => array( 
					'title' => ts( 'Contact Name' )
				),
				'source'  => array( 
					'title'=> ts( 'Contact Source' ),
					'type'=> CRM_Utils_Type::T_STRING ),
					'id'=> array( 
						'title'=> ts( 'Contact ID' ),
						'no_display' => true ,
				), 
			),
			'grouping'  => 'contact-fields',		
		);
		
		$this->_columns['civicrm_mailing'] = array(
			'dao' => 'CRM_Mailing_DAO_Mailing',
			'fields' => array(
				'name' => array(
					'title' => ts('Mailing Name'),
					'no_display' => true,
					'required' => true,
				),
			),
			'filters' => array(
				'mailing_name' => array(
					'name' => 'name',
					'title' => ts('Mailing'),
					'operatorType' => CRM_Report_Form::OP_MULTISELECT,
					'type'=> CRM_Utils_Type::T_STRING,
					'options' => self::mailing_select( ),
					'operator' => 'like',
				),					
			),		
		);
		
		$this->_columns['civicrm_mailing_event_bounce'] = array(
			'dao' => 'CRM_Mailing_DAO_Mailing',
			'fields' => array(
				'bounce_reason' => array(
					'title' => ts('Bounce Reason'),
				),
			),
		);
		
		$this->_columns['civicrm_mailing_bounce_type'] = array(
			'dao' => 'CRM_Mailing_DAO_BounceType',
			'fields' => array(
				'bounce_name' => array(
					'name' => 'name',
					'title' => ts('Bounce Type'),
				),
			),
			'filters' => array(
				'bounce_type_name' => array(
					'name' => 'name',
					'title' => ts('Bounce Type'),
					'operatorType' => CRM_Report_Form::OP_SELECT,
					'type'=> CRM_Utils_Type::T_STRING,
					'options' => self::bounce_type(),
					'operator' => 'like',							
				),
			),
		);
							  
		$this->_columns['civicrm_email']  = array( 
			'dao'=> 'CRM_Core_DAO_Email',
			'fields'=> array( 
				'email' => array( 
					 'title' => ts( 'Email' ),
					 'no_repeat'  => true,
					 'required' => true,
				),
			),
			'grouping'  => 'contact-fields', 
		);
		
		// $this->_columns['civicrm_address'] = array( 
			// 'dao' => 'CRM_Core_DAO_Address',
			// 'grouping'  => 'contact-fields',
			// 'fields' => array( 
				// 'street_address'  => array( 'default' => true ),
				// 'city' => array( 'default' => true ),
				// 'postal_code' => null,
				// 'state_province_id' => array( 'title'   => ts( 'State/Province' ), ),
			// ),
		// );
		
	   // $this->_columns['civicrm_phone'] = array( 
			// 'dao' => 'CRM_Core_DAO_Phone',
			// 'fields' => array( 'phone' => null),
			// 'grouping'  => 'contact-fields',
		// );

		$this->_columns['civicrm_group'] = array( 
			'dao'    => 'CRM_Contact_DAO_Group',
			'alias'  => 'cgroup',
			'filters' => array( 
				'gid' => array( 
					'name'    => 'group_id',
					'title'   => ts( 'Group' ),
					'operatorType' => CRM_Report_Form::OP_MULTISELECT,
					'group'   => true,
					'options' => CRM_Core_PseudoConstant::group( ), 
				), 
			), 
		);

        $this->_tagFilter = true;
        parent::__construct( );
    }
    
    function preProcess( ) {
        $this->assign( 'chartSupported', true );
        parent::preProcess( );
    }
    
    function select( ) {
        $select = array( );
        $this->_columnHeaders = array();

        foreach ( $this->_columns as $tableName => $table ) {
            if ( array_key_exists('fields', $table) ) {
                foreach ( $table['fields'] as $fieldName => $field ) {
                    if ( CRM_Utils_Array::value( 'required', $field ) ||
                         CRM_Utils_Array::value( $fieldName, $this->_params['fields'] ) ) {
                        if ( $tableName == 'civicrm_email' ) {
                            $this->_emailField = true;
                        } else if ( $tableName == 'civicrm_phone' ) {
                            $this->_phoneField = true;
                        }
						# Toggle the mailing name filter flag
						else if ( $tableName == 'civicrm_mailing') {
							$this->_mailingidField = true;
						}

                        $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
                        $this->_columnHeaders["{$tableName}_{$fieldName}"]['type']  = CRM_Utils_Array::value( 'type', $field );
                        $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = $field['title'];
                    }
                }
            }
        }

        
        if ( CRM_Utils_Array::value('charts', $this->_params) ) {
            $select[] = "COUNT({$this->_aliases['civicrm_mailing_event_bounce']}.id) as civicrm_mailing_bounce_count";
            $this->_columnHeaders["civicrm_mailing_bounce_count"]['title'] = ts('Bounce Count'); 
        }

        $this->_select = "SELECT " . implode( ', ', $select ) . " ";
		//print_r($this->_select);
    }

    static function formRule( $fields, $files, $self ) {  
        $errors = $grouping = array( );
        return $errors;
    }

    function from( ) {
        $this->_from = "
        FROM civicrm_contact {$this->_aliases['civicrm_contact']} {$this->_aclFrom}";
            // LEFT JOIN civicrm_address {$this->_aliases['civicrm_address']} 
                   // ON ({$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_address']}.contact_id AND 
                      // {$this->_aliases['civicrm_address']}.is_primary = 1 ) ";
        
		# Grab contacts in a mailing
		if ( $this->_mailingidField) {
			$this->_from .= "
				INNER JOIN civicrm_mailing_event_queue
					ON civicrm_mailing_event_queue.contact_id = {$this->_aliases['civicrm_contact']}.id
				INNER JOIN civicrm_email {$this->_aliases['civicrm_email']}
					ON civicrm_mailing_event_queue.email_id = {$this->_aliases['civicrm_email']}.id
				INNER JOIN civicrm_mailing_event_bounce {$this->_aliases['civicrm_mailing_event_bounce']}
					ON {$this->_aliases['civicrm_mailing_event_bounce']}.event_queue_id = civicrm_mailing_event_queue.id
				LEFT JOIN civicrm_mailing_bounce_type {$this->_aliases['civicrm_mailing_bounce_type']}
					ON {$this->_aliases['civicrm_mailing_event_bounce']}.bounce_type_id = {$this->_aliases['civicrm_mailing_bounce_type']}.id
				INNER JOIN civicrm_mailing_job
					ON civicrm_mailing_event_queue.job_id = civicrm_mailing_job.id
				INNER JOIN civicrm_mailing {$this->_aliases['civicrm_mailing']}
					ON civicrm_mailing_job.mailing_id = {$this->_aliases['civicrm_mailing']}.id
			";
		}
		
        // if ( $this->_emailField ) {
            // $this->_from .= "
            // LEFT JOIN  civicrm_email {$this->_aliases['civicrm_email']} 
                   // ON ({$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_email']}.contact_id AND
                      // {$this->_aliases['civicrm_email']}.is_primary = 1) ";
        // }
		

		

        // if ( $this->_phoneField ) {
            // $this->_from .= "
            // LEFT JOIN civicrm_phone {$this->_aliases['civicrm_phone']} 
                   // ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_phone']}.contact_id AND 
                      // {$this->_aliases['civicrm_phone']}.is_primary = 1 ";
        // }
		


		//print_r($this->_from);
    }
	
    function where( ) {
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
                            if( $fieldName == 'relationship_type_id' ) {
                                $clause =  "{$this->_aliases['civicrm_relationship']}.relationship_type_id=".$this->relationshipId;
                            } else {
                            $clause = 
                                $this->whereClause( $field,
                                                    $op,
                                                    CRM_Utils_Array::value( "{$fieldName}_value", $this->_params ),
                                                    CRM_Utils_Array::value( "{$fieldName}_min", $this->_params ),
                                                    CRM_Utils_Array::value( "{$fieldName}_max", $this->_params ) );
                            }
                        }
                    }
                    
                    if ( ! empty( $clause ) ) {
                        $clauses[] = $clause;
                    }
                }
            }
        }

        if ( empty( $clauses ) ) {
            $this->_where = "WHERE ( 1 )";
        } else {
            $this->_where = "WHERE "  . implode( ' AND ', $clauses );
        }

        if ( $this->_aclWhere ) {
            $this->_where .= " AND {$this->_aclWhere} ";
        } 
    }

    function groupBy( ) {

        if ( CRM_Utils_Array::value('charts', $this->_params) ) {
            $this->_groupBy = " GROUP BY {$this->_aliases['civicrm_mailing']}.id";
        }
    }

    function postProcess( ) {

        $this->beginPostProcess( );

        // get the acl clauses built before we assemble the query
        $this->buildACLClause( $this->_aliases['civicrm_contact'] );

        $sql  = $this->buildQuery( true );
		
		// print_r($sql);
             
        $rows = $graphRows = array();
        $this->buildRows ( $sql, $rows );
        
        $this->formatDisplay( $rows );
        $this->doTemplateAssignment( $rows );
        $this->endPostProcess( $rows );	
    }

    function buildChart( &$rows ) {
        if ( empty($rows) ) {
            return;
        }

        $chartInfo  = array( 'legend'      => ts('Mail Bounce Report'),
                             'xname'       => ts('Mailing'),
                             'yname'       => ts('Bounce'),
                             'xLabelAngle' => 20,
                             'tip'         => ts('Mail Bounce: %1', array(1 => '#val#')),
                             );
        foreach( $rows as $row ) {
            $chartInfo['values'][$row['civicrm_mailing_name']] = $row['civicrm_mailing_bounce_count']; 
        }
        
        // build the chart.
        require_once 'CRM/Utils/OpenFlashChart.php';
        CRM_Utils_OpenFlashChart::buildChart( $chartInfo, $this->_params['charts'] );
        $this->assign( 'chartType', $this->_params['charts'] ); 
    }

    function alterDisplay( &$rows ) {
        // custom code to alter rows
        $entryFound = false;
        foreach ( $rows as $rowNum => $row ) {
            // make count columns point to detail report
            // convert display name to links
            if ( array_key_exists('civicrm_contact_display_name', $row) && 
                 array_key_exists('civicrm_contact_id', $row) ) {
                $url = CRM_Report_Utils_Report::getNextUrl( 'contact/detail', 
                                              'reset=1&force=1&id_op=eq&id_value=' . $row['civicrm_contact_id'],
                                              $this->_absoluteUrl, $this->_id );
                $rows[$rowNum]['civicrm_contact_display_name_link' ] = $url;
                $rows[$rowNum]['civicrm_contact_display_name_hover'] = ts("View Contact details for this contact.");
                $entryFound = true;
            }

            // handle country
            if ( array_key_exists('civicrm_address_country_id', $row) ) {
                if ( $value = $row['civicrm_address_country_id'] ) {
                    $rows[$rowNum]['civicrm_address_country_id'] = CRM_Core_PseudoConstant::country( $value, false );
                }
                $entryFound = true;
            }
            if ( array_key_exists('civicrm_address_state_province_id', $row) ) {
                if ( $value = $row['civicrm_address_state_province_id'] ) {
                    $rows[$rowNum]['civicrm_address_state_province_id'] = CRM_Core_PseudoConstant::stateProvince( $value, false );
                }
                $entryFound = true;
            }


            // skip looking further in rows, if first row itself doesn't 
            // have the column we need
            if ( !$entryFound ) {
                break;
            }
        }
    }

	function mailing_select() {
		require_once('CRM/Mailing/BAO/Mailing.php');
		
		$data = array( );
		
		$mailing = new CRM_Mailing_BAO_Mailing();
		$query = "SELECT name FROM civicrm_mailing ";
		$mailing->query($query);
		
		while($mailing->fetch()) {
			$data[$mailing->name] = $mailing->name;
		}

		return $data;
	}
	
	function bounce_type() {
		require_once('CRM/Mailing/DAO/BounceType.php');
		
		$data = array('' => '--Please Select--');
		
		$bounce_type = new CRM_Mailing_DAO_BounceType();
		$query = "SELECT name FROM civicrm_mailing_bounce_type";
		$bounce_type->query($query);
		
		while($bounce_type->fetch()) {
			$data[$bounce_type->name] = $bounce_type->name;
		}
		
		return $data;
	}
}
