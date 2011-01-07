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
 | Version 3, 19 November 2007.                                       |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * @copyright DharmaTech  (c) 2009
 * $Id$
 *
 */

require_once 'Engage/Report/Form/List.php';

/**
 *  Generate a walk list
 */
class Engage_Report_Form_WalkList extends Engage_Report_Form_List {
 
    function __construct( ) {

        parent::__construct();

        //  Walk list columns
       $this->_columns =  
           array(
                 $this->_demoTable =>
                 array( 'dao' => 'CRM_Contact_DAO_Contact',
                        'fields' =>
                        array( $this->_demoLangCol =>
                               array( 'type' => CRM_Report_Form::OP_STRING,
                                      'required' => true,
                                      'title'      => ts( 'Language' ) ),
                               ),
                        'filters' =>             
                        array( $this->_demoLangCol =>
                               array( 
                                     'title'      => ts( 'Language' ),
                                     'operatorType' => CRM_Report_Form::OP_SELECT,
                                     'type' => CRM_Report_Form::OP_STRING,
                                     'options' =>$this->_languages
                                      ),
                               ),
                        'grouping'=> 'contact-fields'
                        ),
                 $this->_coreInfoTable =>
                 array( 'dao' => 'CRM_Contact_DAO_Contact',
                        'fields' =>
                        array( 
                               $this->_coreTypeCol =>
                               array( 'type' => CRM_Report_Form::OP_STRING,
                                      'required' => true,
                                      'title'      => ts( 'Constituent Type' ) ),
                               $this->_coreOtherCol =>
                               array( 'type' => CRM_Report_Form::OP_STRING,
                                      'required' => true,
                                      'title'      => ts( 'Other Name' ) ) 
                               ),
                        'filters' =>             
                        array( 
                               $this->_coreTypeCol =>
                               array( 
                                     'title'      => ts( 'Constituent Type' ),
                                     'operatorType' => CRM_Report_Form::OP_SELECT,
                                     'type' => CRM_Report_Form::OP_STRING,
                                     'options' =>$this->_contactType
                                      ), ),
                        'grouping'=> 'contact-fields'
                        ),
                 'civicrm_contact'      =>
                 array( 'dao'     => 'CRM_Contact_DAO_Contact',
                        'fields'  =>
                        array( 'gender_id'           => 
                               array( 'title' => ts( 'Sex' ),
                                      'required'    => true),  
                               'birth_date' => 
                               array( 'title' => ts( 'Age' ),
                                      'required'  => true,
                                      'type'  => CRM_Report_FORM::OP_INT ),
                               'id'           => 
                               array( 'title' => ts( 'Contact ID' ),
                                      'required'    => true),  
                               'display_name' => 
                               array( 'title' => ts( 'Contact Name' ),
                                      'required'  => true,
                                      'no_repeat' => true ),
                               ),
                        'filters' =>             
                        array('gender_id'           => 
                              array( 'title' => ts( 'Sex' ),
                                     'operatorType' => CRM_Report_Form::OP_SELECT,
                                     'type' => CRM_Report_Form::OP_STRING,
                                     'options' => array( '' => '' ) + CRM_Core_PseudoConstant::gender( ) ),  
                              'sort_name'    => 
                              array( 'title'      => ts( 'Contact Name' ),
                                     'operator'   => 'like' ),
                              ),
                        'grouping'=> 'contact-fields',
                        'order_bys'=>             
                        array( 'sort_name' => array( 'title' => ts( 'Contact Name' ),
                                                     'required'  => true ) ),
                        
                        ),
                 'civicrm_address' =>
                 array( 'dao' => 'CRM_Core_DAO_Address',
                        'fields' =>
                        array(
                              'street_number'    =>
                              array( 'required' => true,
                                     'title' => ts('Street#') ),
                              'street_name'  => 
                              array( 'title' => ts('Street Name'), 
                                     'nodisplay' => true,
                                     'required'  => true ),    
                              'street_address'    =>
                              array( 'required' => true,
                                     'title' => ts('Street Address') ),
                              'street_unit'     =>
                              array( 'required' => true,
                                     'title'    => ts( 'Apt.' ) ),
                              'city'              =>
                              array( 'required' => true ),
                              'postal_code'       => 
                              array( 'title' => 'Zip',
                                     'required' => true ),
                              'state_province_id' => 
                              array( 'title'   => ts( 'State/Province' ),
                                     'required' => true ),
                              'country_id'        => 
                              array( 'title' => ts( 'Country' ), ), ),
                        'filters' =>             
                        array('street_address' => null,
                              'city'           => null,
                              'postal_code'    => array( 'title' => 'Zip'),
                              ),
                        'grouping'=> 'location-fields',
                        ),
                 'civicrm_phone' => 
                 array( 'dao' => 'CRM_Core_DAO_Phone',
                        'fields' =>
                        array( 'phone' => array( 'default' => true,
                                                 'required' => true ) ),
                        'grouping'=> 'location-fields',
                        ),
                 'civicrm_email' => 
                 array( 'dao' => 'CRM_Core_DAO_Email',
                        'fields' =>
                        array( 'email' => null ),
                        'grouping'=> 'location-fields',
                        ),
                 $this->_voterInfoTable =>
                 array( 'dao' => 'CRM_Contact_DAO_Contact',
                        'fields' =>
                        array( $this->_partyCol =>
                               array( 'type' => CRM_Report_Form::OP_STRING,
                                      'required' => true,
                                      'title'      => ts( 'Party Reg' ) ),
                               $this->_vhCol =>
                               array( 'type' => CRM_Report_Form::OP_STRING,
                                      'required' => true,
                                      'title'      => ts( 'VH' ) )
                               ),
                        'filters' => array(),
                        'grouping'=> 'contact-fields'
                        ),
                 'civicrm_group' => 
                 array( 'dao'    => 'CRM_Contact_DAO_GroupContact',
                        'alias'  => 'cgroup',
                        'filters' =>             
                        array( 'gid' => 
                               array( 'name'          => 'group_id',
                                      'title'         => ts( 'Group' ),
                                      'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                      'group'         => true,
                                      'options'       => CRM_Core_PseudoConstant::group( ) ), ), ),
                 );
    }
    
    /**
     *  Generate WHERE clauses for SQL SELECT
     *  FIXME: deal with age filter
     */
    function where( ) {
        $clauses = array( "{$this->_aliases['civicrm_address']}.id IS NOT NULL" );

        foreach ( $this->_columns as $tableName => $table ) {
            //echo "where: table name $tableName<br>";

            //  Treatment of normal filters
            if ( array_key_exists('filters', $table) ) {
               foreach ( $table['filters'] as $fieldName => $field ) {
                    //echo "&nbsp;&nbsp;&nbsp;field name $fieldName<br>";
                    $clause = null;

                    if ( $field['type'] & CRM_Utils_Type::T_DATE ) {
                        $relative = CRM_Utils_Array::value( "{$fieldName}_relative", $this->_params );
                        $from     = CRM_Utils_Array::value( "{$fieldName}_from"    , $this->_params );
                        $to       = CRM_Utils_Array::value( "{$fieldName}_to"      , $this->_params );
                        
                        $clause = $this->dateClause( $field['name'], $relative, $from, $to );
                    } elseif ( $fieldName == $this->_demoLangCol ) {
                        if ( !empty( $this->_params[ $this->_demoLangCol . '_value' ] ) ) {
                            $clause = "{$field['dbAlias']}='"
                                . $this->_params[ $this->_demoLangCol . '_value' ]
                                . "'";
                        }                        
                    } else {
                        $op = CRM_Utils_Array::value( "{$fieldName}_op", $this->_params );
                        if ( $op == 'mand' ) $clause = true;
                        else if ( $op ) {
                            $clause = 
                                $this->whereClause( $field,
                                                    $op,
                                                    CRM_Utils_Array::value( "{$fieldName}_value", $this->_params ),
                                                    CRM_Utils_Array::value( "{$fieldName}_min", $this->_params ),
                                                    CRM_Utils_Array::value( "{$fieldName}_max", $this->_params ) );
                        }
                    }
                    //var_dump($clause);
                    if ( ! empty( $clause ) ) {
                        if ( CRM_Utils_Array::value( 'group', $field ) ) {
                            $clauses[] = $this->whereGroupClause( $clause );
                        } else {
                            $clauses[] = $clause;
                        }
                    }
                }
            }
        }

        if ( empty( $clauses ) ) {
            $this->_where = "WHERE ( 1 ) ";
        } else {
            $this->_where = "WHERE " . implode( ' AND ', $clauses );
        }
    }
    
    /**
     *  Process submitted form
     */
    function postProcess( ) {
        parent::postProcess();
    }   

    function alterDisplay( &$rows ) {

        if ($this->_outputMode == 'print' || $this->_outputMode == 'pdf') {
            $this->executePrintmode($rows);
            return;
        }
        // custom code to alter rows
        //var_dump($rows);
        $genderList = CRM_Core_PseudoConstant::gender( );
        $entryFound = false;
        foreach ( $rows as $rowNum => $row ) { 
            // handle state province
            if ( array_key_exists('civicrm_address_state_province_id', $row) ) {
                if ( $value = $row['civicrm_address_state_province_id'] ) {
                    $rows[$rowNum]['civicrm_address_state_province_id'] = 
                        CRM_Core_PseudoConstant::stateProvince( $value );
                }
                $entryFound = true;
            }

            // handle country
            if ( array_key_exists('civicrm_address_country_id', $row) ) {
                if ( $value = $row['civicrm_address_country_id'] ) {
                    $rows[$rowNum]['civicrm_address_country_id'] = 
                        CRM_Core_PseudoConstant::country( $value );
                }
                $entryFound = true;
            }

            // Handle contactType
            if ( !empty( $row[ $this->_coreInfoTable . '_' .$this->_coreTypeCol ] ) ) {
                $rows[$rowNum][ $this->_coreInfoTable . '_' . $this->_coreTypeCol ] =
                    $this->hexOne2str( $rows[$rowNum][$this->_coreInfoTable . '_' . $this->_coreTypeCol] );
                $entryFound = true;
            }

            // date of birth to age
            if ( !empty( $row['civicrm_contact_birth_date'] ) ) {
                $rows[$rowNum][ 'civicrm_contact_birth_date' ] =
                    $this->dob2age( $row['civicrm_contact_birth_date'] . " 00:00:00" );
                $entryFound = true;
            }

            // gender label
            if ( !empty( $row['civicrm_contact_gender_id'] ) ) {
                $rows[$rowNum][ 'civicrm_contact_gender_id' ] = $genderList[$row['civicrm_contact_gender_id']];
                $entryFound = true;
            }

            //  Abbreviate party registration to first letter
            if ( !empty( $row[ "{$this->_voterInfoTable}_{$this->_partyCol}" ] ) ) {
                $rows[$rowNum][ "{$this->_voterInfoTable}_{$this->_partyCol}" ] =
                    substr( $row[ "{$this->_voterInfoTable}_{$this->_partyCol}" ], 0, 1 );
                $entryFound = true;
            }

            // skip looking further in rows, if first row itself doesn't 
            // have the column we need
            if ( !$entryFound ) {
                break;
            }
        }

        // make sure column order is same as in print mode
        $columnOrder = array(
                             'civicrm_address_street_number',
                             'civicrm_address_street_unit',
                             'civicrm_contact_display_name',
                             'civicrm_phone_phone',
                             'civicrm_contact_birth_date',
                             'civicrm_contact_gender_id',
                             $this->_demoTable . '_' . $this->_demoLangCol,
                             $this->_voterInfoTable . '_' . $this->_partyCol,
                             $this->_voterInfoTable . '_' . $this->_vhCol,
                             $this->_coreInfoTable . '_' . $this->_coreTypeCol,
                             'civicrm_contact_id',
                             );
        $tempHeaders = $this->_columnHeaders;
        $this->_columnHeaders = array( );
        foreach ( $columnOrder as $col ) {
            if ( array_key_exists($col, $tempHeaders) ) {
                $this->_columnHeaders[$col] = $tempHeaders[$col];
                unset($tempHeaders[$col]);
            }
        }
        $this->_columnHeaders = $this->_columnHeaders + $tempHeaders;
    }

   function executePrintmode($rows) {
      
       //  Separate out fields and build a temporary table
       $tempTable = "WalkList_" . uniqid();
       $sql = "CREATE TEMPORARY TABLE {$tempTable}"
           . " ( id              INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                  street_name     VARCHAR(255),
                  s_street_number VARCHAR(255),
                  i_street_number INT,
                  odd             TINYINT,
                  apt_number      VARCHAR(255),
                  city            VARCHAR(255),
                  state           VARCHAR(255),
                  zip             VARCHAR(255),
                  name            VARCHAR(255),
                  phone           VARCHAR(255),
                  age             INT,
                  sex             VARCHAR(255),
                  lang            CHAR(2),
                  party           CHAR(1),
                  vh              CHAR(1),
                  contact_type    VARCHAR(255),
                  other_name      VARCHAR(255),
                  contact_id      INT )
                 ENGINE=HEAP
                 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci"; 
        CRM_Core_DAO::executeQuery($sql);

        $gender = CRM_Core_PseudoConstant::gender();
    
        foreach( $rows as $key => $value ) {

            $dob = $value['civicrm_contact_birth_date'];
            $age = empty( $dob ) ? 'null' : $this->dob2age( $dob );
            $sex = $gender[ CRM_Utils_Array::value('civicrm_contact_gender_id',$value) ];
            $lang = strtoupper( substr( $value[ $this->_demoTable
                                      . '_' . $this->_demoLangCol], 0, 2 ) );
            $party = substr( $value[ "{$this->_voterInfoTable}_{$this->_partyCol}" ], 0, 1 );
            $vh = substr( $value[ "{$this->_voterInfoTable}_{$this->_vhCol}" ], 0, 1 );
            $contactType = $value[ $this->_coreInfoTable
                                   . '_' . $this->_coreTypeCol];
            $on = $value[ $this->_coreInfoTable
                                   . '_' . $this->_coreOtherCol];
            $otherName = empty( $on ) ? 'null' : "'{$on}'";
            $type = null;
            if ( !empty( $contactType ) ) {
                $type = $this->hexOne2str($contactType);
            }
            $contact_id = (int)$value['civicrm_contact_id'];

            $state = null;
            if (!empty( $value['civicrm_address_state_province_id'] ) ) {
                $state = CRM_Core_PseudoConstant::stateProvince(
                            $value['civicrm_address_state_province_id'] );
            }

            $sStreetNumber = $value['civicrm_address_street_number'];
            $iStreetNumber = $value['civicrm_address_street_number']? (int)$value['civicrm_address_street_number']: 'null';
            $odd           = $value['civicrm_address_street_number']? ((int)$value['civicrm_address_street_number']%2):'null';
            $query = "INSERT INTO {$tempTable} SET
                       street_name     = \"{$value['civicrm_address_street_name']}\",
                       s_street_number = '{$sStreetNumber}',
                       i_street_number = {$iStreetNumber},
                       odd             = {$odd},
                       apt_number      = '{$value['civicrm_address_street_unit']}',
                       city            = '{$value['civicrm_address_city']}',
                       state           = '{$state}',
                       zip             = '{$value['civicrm_address_postal_code']}',
                       name            = \"{$value['civicrm_contact_display_name']}\",
                       phone           = '{$value['civicrm_phone_phone']}',
                       age             = {$age},
                       sex             = '{$sex}',
                       lang            = '{$lang}',
                       party           = '{$party}',
                       vh              = '{$vh}',
                       contact_type    = '{$type}',
                       other_name      = {$otherName},
                       contact_id      = {$contact_id}";

            CRM_Core_DAO::executeQuery($query);
        } 

        //  With the data normalized and in a table, we can
        //  retrieve it in the order we need to present it
        $query = "SELECT * FROM {$tempTable} ORDER BY state, city, zip,
                  street_name, odd, i_street_number, apt_number";
        $dao   = CRM_Core_DAO::executeQuery( $query );

        //  Initialize output state
        $first       = true;
        $state       = '';
        $city        = '';
        $zip         = '';
        $street_name = '';
        $odd         = '';
        $pageRow     = 0;
        $reportDate  = date('F j, Y' );

        $pdfRows   = array( );
        $groupRows = array( );
        $groupCounts = 0;
        
        $pdfHeaders = array( 's_street_number' => array( 'title' => 'STREET#' ),
                             'apt_number'      => array( 'title' => 'APT'     ),
                             'name'            => array( 'title' => 'Name'    ),
                             'phone'           => array( 'title' => 'PHONE'   ),
                             'age'             => array( 'title' => 'AGE'     ),
                             'sex'             => array( 'title' => 'SEX'     ),
                             'lang'            => array( 'title' => 'Lang'    ),
                             'party'           => array( 'title' => 'Party'   ),
                             'vh'              => array( 'title' => 'VH'      ),
                             'contact_type'    => array( 'title' => 'Constituent Type' ),
                             'note'            => array( 'title' => 'NOTES'   ),
                             'rcode'           => array( 'title' => 'RESPONSE CODES'   ),
                             'status'          => array( 'title' => 'STATUS'  ),
                             'contact_id'      => array( 'title' => 'ID',
                                                         'class' => 'width=7%') );
        $groupInfo = array( 'date'  => $reportDate,
                            'descr' => empty( $this->_groupDescr )? '': "<br>Group {$this->_groupDescr}"  );

        
        while( $dao->fetch( ) ) {

            if ( strtolower( $state ) != strtolower( $dao->state )
                  || strtolower( $city )  != strtolower( $dao->city )
                  || strtolower( $zip )   != strtolower( $dao->zip )
                  || strtolower( $street_name )
                                 != strtolower( $dao->street_name )
                  || $odd != $dao->odd
                 || $pageRow > 6
                ) {

                $state       = $dao->state;
                $city        = $dao->city;
                $zip         = $dao->zip;
                $street_name = $dao->street_name;
                $odd         = $dao->odd;
                $pageRow     = 0;

                $groupRow['org']         = $this->_orgName;
                $groupRow['street_name'] = $street_name;
                $groupRow['city_zip']    = $city.', '.$state .' '.$zip;
                $groupRow['odd']         = $odd ? 'Odd' : 'Even';

                $groupCounts++;
                $groupRows[$groupCounts] = $groupRow;

            } 

            $pdfRow = array();
            foreach( $pdfHeaders as $k => $v ){
                if ( property_exists($dao , $k ) ){
                    if( $k == 'name' && $dao->other_name ) {
                        $pdfRow[$k] = $dao->$k. "<br />" . $dao->other_name;
                        continue;
                    }
                    $pdfRow[$k] = $dao->$k;  
                } else {
                    $pdfRow[$k] = "";
                }
            }
            
            $pdfRows[$groupCounts][] =  $pdfRow;

            $pageRow++;
        }
        $this->assign( 'pageTotal' , $groupCounts );
        $this->assign( 'pdfHeaders', $pdfHeaders );
        $this->assign( 'groupInfo', $groupInfo );
        $this->assign( 'pdfRows', $pdfRows );
        $this->assign( 'groupRows', $groupRows );
  
   }

}
