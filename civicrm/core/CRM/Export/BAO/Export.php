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

/**
 * This class contains the funtions for Component export
 *
 */
class CRM_Export_BAO_Export
{
    const EXPORT_ROW_COUNT = 100;

    /**
     * Function to get the list the export fields
     *
     * @param int    $selectAll user preference while export
     * @param array  $ids  contact ids
     * @param array  $params associated array of fields
     * @param string $order order by clause
     * @param array  $fields associated array of fields
     * @param array  $moreReturnProperties additional return fields
     * @param int    $exportMode export mode
     * @param string $componentClause component clause
     * @param string $componentTable component table 
     * @param bool   $mergeSameAddress merge records if they have same address 
     * @param bool   $mergeSameHousehold merge records if they belong to the same household
     *
     * @static
     * @access public
     */
    static function exportComponents( $selectAll,
                                      $ids,
                                      $params,
                                      $order = null, 
                                      $fields = null,
                                      $moreReturnProperties = null, 
                                      $exportMode = CRM_Export_Form_Select::CONTACT_EXPORT,
                                      $componentClause = null,
                                      $componentTable  = null,
                                      $mergeSameAddress = false,
                                      $mergeSameHousehold = false )
    {
        $headerRows = $returnProperties = array();
        $primary    = $paymentFields    = false;
        $origFields = $fields;
        $queryMode  = null; 

        $phoneTypes  = CRM_Core_PseudoConstant::phoneType();
        $imProviders = CRM_Core_PseudoConstant::IMProvider();
        $contactRelationshipTypes = CRM_Contact_BAO_Relationship::getContactRelationshipType( 
                                                                                             null, 
                                                                                             null, 
                                                                                             null, 
                                                                                             null, 
                                                                                             true, 
                                                                                             'label', 
                                                                                             false );
        $queryMode = CRM_Contact_BAO_Query::MODE_CONTACTS;
        
        switch ( $exportMode )  {
        case CRM_Export_Form_Select::CONTRIBUTE_EXPORT :
            $queryMode = CRM_Contact_BAO_Query::MODE_CONTRIBUTE;
            break;
        case CRM_Export_Form_Select::EVENT_EXPORT :
            $queryMode = CRM_Contact_BAO_Query::MODE_EVENT;
            break;
        case CRM_Export_Form_Select::MEMBER_EXPORT :
            $queryMode = CRM_Contact_BAO_Query::MODE_MEMBER;
            break;
        case CRM_Export_Form_Select::PLEDGE_EXPORT :
            $queryMode = CRM_Contact_BAO_Query::MODE_PLEDGE;
            break;
        case CRM_Export_Form_Select::CASE_EXPORT :
            $queryMode = CRM_Contact_BAO_Query::MODE_CASE;
            break;
        case CRM_Export_Form_Select::GRANT_EXPORT :
            $queryMode = CRM_Contact_BAO_Query::MODE_GRANT;
            break;
        case CRM_Export_Form_Select::ACTIVITY_EXPORT :
            $queryMode = CRM_Contact_BAO_Query::MODE_ACTIVITY;
            break;
        }
        require_once 'CRM/Core/BAO/CustomField.php';
        if ( $fields ) {
            //construct return properties 
            $locationTypes = CRM_Core_PseudoConstant::locationType();
            $locationTypeFields = array ( 'street_address',
                                          'supplemental_address_1',
                                          'supplemental_address_2',
                                          'city',
                                          'postal_code',
                                          'postal_code_suffix',
                                          'geo_code_1',
                                          'geo_code_2',
                                          'state_province',
                                          'country',
                                          'phone',
                                          'email',
                                          'im' );

            foreach ( $fields as $key => $value ) {
                $phoneTypeId = $imProviderId = null;
                $relationshipTypes = $fieldName = CRM_Utils_Array::value( 1, $value );
                if ( ! $fieldName ) {
                    continue;
                }
                // get phoneType id and IM service provider id seperately
                if ( $fieldName == 'phone' ) { 
                    $phoneTypeId = CRM_Utils_Array::value( 3, $value );
                } else if ( $fieldName == 'im' ) { 
                    $imProviderId = CRM_Utils_Array::value( 3, $value );
                }
                
                if ( array_key_exists ( $relationshipTypes, $contactRelationshipTypes ) ) {
                    if ( CRM_Utils_Array::value( 2, $value ) ) {
                        $relationField = CRM_Utils_Array::value( 2, $value );
                        if ( trim ( CRM_Utils_Array::value( 3, $value ) ) ) {
                            $relLocTypeId = CRM_Utils_Array::value( 3, $value );
                        } else {
                            $relLocTypeId = 1;
                        }

                        if ( $relationField == 'phone' ) { 
                            $relPhoneTypeId  = CRM_Utils_Array::value( 4, $value );                            
                        } else if ( $relationField == 'im' ) {
                            $relIMProviderId = CRM_Utils_Array::value( 4, $value );
                        }
                    } else if ( CRM_Utils_Array::value( 4, $value ) ) {
                        $relationField  = CRM_Utils_Array::value( 4, $value );
                        $relLocTypeId   = CRM_Utils_Array::value( 5, $value );
                        if ( $relationField == 'phone' ) { 
                            $relPhoneTypeId  = CRM_Utils_Array::value( 6, $value );                            
                        } else if ( $relationField == 'im' ) {
                            $relIMProviderId = CRM_Utils_Array::value( 6, $value );
                        }
                    }                    
                }

                $contactType = CRM_Utils_Array::value( 0, $value );
                $locTypeId   = CRM_Utils_Array::value( 2, $value );
                $phoneTypeId = CRM_Utils_Array::value( 3, $value );
                
                if ( $relationField ) {
                    if ( in_array ( $relationField, $locationTypeFields ) ) {
                        if ( $relPhoneTypeId ) {                            
                            $returnProperties[$relationshipTypes]['location'][$locationTypes[$relLocTypeId]]['phone-' .$relPhoneTypeId] = 1;
                        } else if ( $relIMProviderId ) {                            
                            $returnProperties[$relationshipTypes]['location'][$locationTypes[$relLocTypeId]]['im-' .$relIMProviderId] = 1;
                        } else {
                            $returnProperties[$relationshipTypes]['location'][$locationTypes[$relLocTypeId]][$relationField] = 1;
                        } 
                        $relPhoneTypeId = $relIMProviderId = null;                       
                    } else {
                        $returnProperties[$relationshipTypes][$relationField]  = 1;
                    }                    
                } else if ( is_numeric( $locTypeId ) ) {
                    if ( $phoneTypeId ) {
                        $returnProperties['location'][$locationTypes[$locTypeId]]['phone-' .$phoneTypeId] = 1;
                    } else if ( isset( $imProviderId ) ) { 
                        //build returnProperties for IM service provider
                        $returnProperties['location'][$locationTypes[$locTypeId]]['im-' .$imProviderId] = 1;
                    } else {
                        $returnProperties['location'][$locationTypes[$locTypeId]][$fieldName] = 1;
                    }
                } else {
                    //hack to fix component fields
                    if ( $fieldName == 'event_id' ) {
                        $returnProperties['event_title'] = 1;
                    } else {
                        $returnProperties[$fieldName] = 1;
                    }
                }
            }

            // hack to add default returnproperty based on export mode
            if ( $exportMode == CRM_Export_Form_Select::CONTRIBUTE_EXPORT ) {
                $returnProperties['contribution_id'] = 1;
            } else if ( $exportMode == CRM_Export_Form_Select::EVENT_EXPORT ) {
                $returnProperties['participant_id'] = 1;
            } else if ( $exportMode == CRM_Export_Form_Select::MEMBER_EXPORT ) {
                $returnProperties['membership_id'] = 1;
            } else if ( $exportMode == CRM_Export_Form_Select::PLEDGE_EXPORT ) {
                $returnProperties['pledge_id'] = 1;
            } else if ( $exportMode == CRM_Export_Form_Select::CASE_EXPORT ) {
                $returnProperties['case_id'] = 1;
            } else if ( $exportMode == CRM_Export_Form_Select::GRANT_EXPORT ) {
                $returnProperties['grant_id'] = 1;
            } else if ( $exportMode == CRM_Export_Form_Select::ACTIVITY_EXPORT ) {
                $returnProperties['activity_id'] = 1;
            }            
         } else {
            $primary = true;
            $fields = CRM_Contact_BAO_Contact::exportableFields( 'All', true, true );
            foreach ( $fields as $key => $var ) { 
                if ( $key && ( substr( $key, 0, 6 ) !=  'custom' ) ) {
                    //for CRM=952
                    $returnProperties[$key] = 1;
                }
            }
            
            if ( $primary ) {
                $returnProperties['location_type'   ] = 1;
                $returnProperties['im_provider'     ] = 1;
                $returnProperties['phone_type_id'   ] = 1;
                $returnProperties['provider_id'     ] = 1;
                $returnProperties['current_employer'] = 1;
            }
            
            $extraReturnProperties = array( );
            $paymentFields = false;
            
            switch ( $queryMode )  {
            case CRM_Contact_BAO_Query::MODE_EVENT :
                $paymentFields  = true;
                $paymentTableId = "participant_id";
                break;
            case CRM_Contact_BAO_Query::MODE_MEMBER :
                $paymentFields  = true;
                $paymentTableId = "membership_id";
                break;
            case CRM_Contact_BAO_Query::MODE_PLEDGE :
                require_once 'CRM/Pledge/BAO/Query.php';
                $extraReturnProperties = CRM_Pledge_BAO_Query::extraReturnProperties( $queryMode );
                $paymentFields  = true;
                $paymentTableId = "pledge_payment_id";
                break;
            case CRM_Contact_BAO_Query::MODE_CASE :
                require_once 'CRM/Case/BAO/Query.php';
                $extraReturnProperties = CRM_Case_BAO_Query::extraReturnProperties( $queryMode );
                break;
            }
            
            if ( $queryMode != CRM_Contact_BAO_Query::MODE_CONTACTS ) {
                $componentReturnProperties = CRM_Contact_BAO_Query::defaultReturnProperties( $queryMode );
                $returnProperties          = array_merge( $returnProperties, $componentReturnProperties );
        
                if ( !empty( $extraReturnProperties ) ) {
                    $returnProperties = array_merge( $returnProperties, $extraReturnProperties );
                }
        
                // unset groups, tags, notes for components
                foreach ( array( 'groups', 'tags', 'notes' ) as $value ) {
                    unset( $returnProperties[$value] );
                }
            }
        }
        
        if ( $mergeSameAddress ) {
            $drop = false;
            
            //make sure the addressee fields are selected
            //while using merge same address feature
            $returnProperties['addressee'     ] = 1;
            $returnProperties['street_name'   ] = 1;
            if ( !CRM_Utils_Array::value( 'last_name', $returnProperties ) ) {
                $returnProperties['last_name' ] = 1;
                $drop = 'last_name';
            }
            $returnProperties['household_name'] = 1;
            $returnProperties['street_address'] = 1;
        }
        
        if ( $moreReturnProperties ) {
            $returnProperties = array_merge( $returnProperties, $moreReturnProperties );
        }

        $query = new CRM_Contact_BAO_Query( 0, $returnProperties, null, false, false, $queryMode );
        list( $select, $from, $where ) = $query->query( );
        
        if ( $mergeSameHousehold == 1 ) {
            if ( !$returnProperties['id'] ) {
                $returnProperties['id'] = 1;
                $setId = true;
            } else {
                $setId = false;
            }
          
            $relationKey = CRM_Utils_Array::key( 'Household Member of', $contactRelationshipTypes );
            foreach ( $returnProperties as $key => $value ) {
                if ( !array_key_exists( $key, $contactRelationshipTypes ) ) {
                    $returnProperties[$relationKey][$key] = $value;
                }
            }
            
            unset( $returnProperties[$relationKey]['location_type'] );
            unset( $returnProperties[$relationKey]['im_provider'] );
        }
        
        $allRelContactArray = $relationQuery = array();
        
        foreach ( $contactRelationshipTypes as $rel => $dnt ) {
            if ( $relationReturnProperties = CRM_Utils_Array::value( $rel, $returnProperties ) ) {
                $allRelContactArray[$rel] = array();
                // build Query for each relationship
                $relationQuery[$rel] = new CRM_Contact_BAO_Query( 0, $relationReturnProperties,
                                                                  null, false, false, $queryMode );
                list( $relationSelect, $relationFrom, $relationWhere ) = $relationQuery[$rel]->query( );
                
                list( $id, $direction ) = explode( '_', $rel, 2 );
                // identify the relationship direction
                $contactA = 'contact_id_a';
                $contactB = 'contact_id_b'; 
                if ( $direction == 'b_a' ) {
                    $contactA = 'contact_id_b';
                    $contactB = 'contact_id_a';
                }
                if ( $exportMode == CRM_Export_Form_Select::CONTACT_EXPORT ) {
                    $relIDs = $ids;
                } else if( $exportMode == CRM_Export_Form_Select::ACTIVITY_EXPORT )  {
                    $query = "SELECT source_contact_id FROM civicrm_activity
                              WHERE id IN ( ".implode(',', $ids).")";
                    $dao = CRM_Core_DAO::executeQuery( $query );
                    while ( $dao->fetch( ) ) {
                        $relIDs[] = $dao->source_contact_id;
                    } 
                } else {
                    switch ( $exportMode )  {
                    case CRM_Export_Form_Select::CONTRIBUTE_EXPORT :
                        $component ='civicrm_contribution';
                        break;
                    case CRM_Export_Form_Select::EVENT_EXPORT :
                        $component ='civicrm_participant';
                        break;
                    case CRM_Export_Form_Select::MEMBER_EXPORT :
                        $component ='civicrm_membership';
                        break;
                    case CRM_Export_Form_Select::PLEDGE_EXPORT :
                        $component ='civicrm_pledge';
                        break;
                    case CRM_Export_Form_Select::CASE_EXPORT :
                        $component ='civicrm_case';
                        break;
                    case CRM_Export_Form_Select::GRANT_EXPORT :
                        $component ='civicrm_grant';
                        break;
                    }
                    $relIDs = CRM_Core_DAO::getContactIDsFromComponent( $ids,$component );
                }                
                
                $relationshipJoin = $relationshipClause = '';
                if ( $componentTable ) {
                    $relationshipJoin   = " INNER JOIN $componentTable ctTable ON ctTable.contact_id = contact_a.id ";
                } else {
                    $relID  = implode( ',', $relIDs );
                    $relationshipClause = " AND crel.{$contactA} IN ( {$relID} )";
                }

                $relationFrom = " {$relationFrom}
                INNER JOIN civicrm_relationship crel ON crel.{$contactB} = contact_a.id AND crel.relationship_type_id = {$id} 
                {$relationshipJoin} ";
                
                $relationWhere       = " WHERE contact_a.is_deleted = 0 {$relationshipClause}";
                $relationGroupBy     = " GROUP BY crel.{$contactA}";
                $relationSelect      = "{$relationSelect}, {$contactA} as refContact ";
                $relationQueryString = "$relationSelect $relationFrom $relationWhere $relationGroupBy";                

                $allRelContactDAO    = CRM_Core_DAO::executeQuery( $relationQueryString );
                while ( $allRelContactDAO->fetch() ) {
                    //FIX Me: Migrate this to table rather than array
                    // build the array of all related contacts
                    $allRelContactArray[$rel][$allRelContactDAO->refContact] = clone( $allRelContactDAO );
                }              
                $allRelContactDAO->free( );
            }
        }

        // make sure the groups stuff is included only if specifically specified
        // by the fields param (CRM-1969), else we limit the contacts outputted to only
        // ones that are part of a group
        if ( CRM_Utils_Array::value( 'groups', $returnProperties ) ) {
            $oldClause = "contact_a.id = civicrm_group_contact.contact_id";
            $newClause = " ( $oldClause AND civicrm_group_contact.status = 'Added' OR civicrm_group_contact.status IS NULL ) ";
            // total hack for export, CRM-3618
            $from = str_replace( $oldClause,
                                 $newClause,
                                 $from );
        }

        if ( $componentTable ) {
            $from .= " INNER JOIN $componentTable ctTable ON ctTable.contact_id = contact_a.id ";
        } else if ( $componentClause ) {
            if ( empty( $where ) ) {
                $where = "WHERE $componentClause";
            } else {
                $where .= " AND $componentClause";
            }
        }

        $queryString = "$select $from $where";

        $groupBy = "";
        if ( CRM_Utils_Array::value( 'tags'  , $returnProperties ) || 
             CRM_Utils_Array::value( 'groups', $returnProperties ) ||
             CRM_Utils_Array::value( 'notes' , $returnProperties ) ||
             $query->_useGroupBy ) { 
            $groupBy = " GROUP BY contact_a.id";
        }
        if ( $queryMode & CRM_Contact_BAO_Query::MODE_ACTIVITY ) {
            $groupBy = " GROUP BY civicrm_activity.id ";  
        }
        $queryString .= $groupBy;
        if ( $order ) {
            list( $field, $dir ) = explode( ' ', $order, 2 );
            $field = trim( $field );
            if ( CRM_Utils_Array::value( $field, $returnProperties ) ) {
                // $queryString .= " ORDER BY $order";
            }
        }

        //hack for student data
        require_once 'CRM/Core/OptionGroup.php';
        $multipleSelectFields = array( 'preferred_communication_method' => 1 );
        
        if ( CRM_Core_Permission::access( 'Quest' ) ) { 
            require_once 'CRM/Quest/BAO/Student.php';
            $studentFields = array( );
            $studentFields = CRM_Quest_BAO_Student::$multipleSelectFields;
            $multipleSelectFields = array_merge( $multipleSelectFields, $studentFields );
        }
        
        $header = $addPaymentHeader = false;
        
        if ( $paymentFields ) {
            //special return properties for event and members
            $paymentHeaders = array( 'total_amount'        => ts('Total Amount'), 
                                     'contribution_status' => ts('Contribution Status'), 
                                     'received_date'       => ts('Received Date'),
                                     'payment_instrument'  => ts('Payment Instrument'), 
                                     'transaction_id'      => ts('Transaction ID') 
                                     );
            
            // get payment related in for event and members
            require_once 'CRM/Contribute/BAO/Contribution.php';
            $paymentDetails = CRM_Contribute_BAO_Contribution::getContributionDetails( $exportMode, $ids );
            if( !empty( $paymentDetails ) ) $addPaymentHeader = true;
            $nullContributionDetails = array_fill_keys($paymentHeaders,null);    
        }

        $componentDetails = $headerRows = $sqlColumns = array( );
        $setHeader = true;

        $rowCount = self::EXPORT_ROW_COUNT;
        $offset   = 0;

        $count = -1;
        while ( 1 ) {
            $limitQuery = "{$queryString} LIMIT {$offset}, {$rowCount}";
            $dao = CRM_Core_DAO::executeQuery( $limitQuery );
           
            if ( $dao->N <= 0 ) {
                break;
            }
            
            while ( $dao->fetch( ) ) {
                $count++;
                $row = array( );

                //first loop through returnproperties so that we return what is required, and in same order.
                $relationshipField = 0;
                foreach( $returnProperties as $field => $value ) {
                    //we should set header only once
                    if ( $setHeader ) {
                        $sqlDone = false;
                        if ( isset( $query->_fields[$field]['title'] ) ) {
                            $headerRows[] = $query->_fields[$field]['title'];
                        } else if ( $field == 'phone_type_id' ) {
                            $headerRows[] = 'Phone Type';
                        } else if ( $field == 'provider_id' ) { 
                            $headerRows[] = 'Im Service Provider'; 
                        } else if ( is_array( $value ) && $field == 'location' ) {
                            // fix header for location type case
                            foreach ( $value as $ltype => $val ) {
                                foreach ( array_keys( $val ) as $fld ) {
                                    $type = explode( '-', $fld );
                                    $hdr = "{$ltype}-" . $query->_fields[$type[0]]['title'];
                                
                                    if ( CRM_Utils_Array::value( 1, $type ) ) {
                                        if ( CRM_Utils_Array::value( 0, $type ) == 'phone' ) {
                                            $hdr .= "-" . CRM_Utils_Array::value( $type[1], $phoneTypes );
                                        } else if ( CRM_Utils_Array::value( 0, $type ) == 'im' ) {
                                            $hdr .= "-" . CRM_Utils_Array::value( $type[1], $imProviders );
                                        }
                                    }
                                    $headerRows[] = $hdr;
                                    self::sqlColumnDefn( $query, $sqlColumns, $hdr );
                                }
                                $sqlDone = true;
                            }
                        } else if ( substr( $field, 0, 5 ) == 'case_' ) {
                            if ( $query->_fields['case'][$field]['title'] ) {
                                $headerRows[] = $query->_fields['case'][$field]['title'];
                            } else if ( $query->_fields['activity'][$field]['title'] ){
                                $headerRows[] = $query->_fields['activity'][$field]['title'];
                            }
                        } else if ( array_key_exists( $field, $contactRelationshipTypes ) ) {
                            $relName = $field;
                            foreach ( $value as $relationField => $relationValue ) {
                                // below block is same as primary block (duplicate)
                                if ( isset( $relationQuery[$field]->_fields[$relationField]['title'] ) ) {
                                    $headerName   = $field .'-' . $relationQuery[$field]->_fields[$relationField]['title'];
                                    $headerRows[] = $headerName;
                                    self::sqlColumnDefn( $query, $sqlColumns, $headerName );
                                } else if ( $relationField == 'phone_type_id' ) {
                                    $headerName   = $field .'-' . 'Phone Type';
                                    $headerRows[] = $headerName;
                                    self::sqlColumnDefn( $query, $sqlColumns, $headerName );
                                } else if ( $relationField == 'provider_id' ) { 
                                    $headerName   = $field .'-' . 'Im Service Provider';
                                    $headerRows[] = $headerName;
                                    self::sqlColumnDefn( $query, $sqlColumns, $headerName );
                                } else if ( is_array( $relationValue ) && $relationField == 'location' ) {
                                    // fix header for location type case
                                    foreach ( $relationValue as $ltype => $val ) {
                                        foreach ( array_keys( $val ) as $fld ) {
                                            $type = explode( '-', $fld );
                                            $hdr = "{$ltype}-" . $relationQuery[$field]->_fields[$type[0]]['title'];

                                            if ( CRM_Utils_Array::value( 1, $type ) ) {
                                                if ( CRM_Utils_Array::value( 0, $type ) == 'phone' ) {
                                                    $hdr .= "-" . CRM_Utils_Array::value( $type[1], $phoneTypes );
                                                } else if ( CRM_Utils_Array::value( 0, $type ) == 'im' ) {
                                                    $hdr .= "-" . CRM_Utils_Array::value( $type[1], $imProviders );
                                                }
                                            }
                                            $headerName   = $field .'-' . $hdr;
                                            $headerRows[] = $headerName;
                                            self::sqlColumnDefn( $query, $sqlColumns, $headerName );
                                        }
                                    }
                                }
                            }
                        } else {
                            $headerRows[] = $field;
                        }

                        if ( ! $sqlDone ) {
                            self::sqlColumnDefn( $query, $sqlColumns, $field );
                        }
                    }

                    //build row values (data)
                    if ( property_exists( $dao, $field ) ) {
                        $fieldValue = $dao->$field;
                        // to get phone type from phone type id
                        if ( $field == 'phone_type_id' ) {
                            $fieldValue = $phoneTypes[$fieldValue];
                        } else if ( $field == 'provider_id' ) {
                            $fieldValue = CRM_Utils_Array::value( $fieldValue, $imProviders );  
                        }
                    } else {
                        $fieldValue = '';
                    }
                
                    if ( $field == 'id' ) {
                        $row[$field] = $dao->contact_id;
                    } else if ( $field == 'pledge_balance_amount' ) { //special case for calculated field
                        $row[$field] = $dao->pledge_amount - $dao->pledge_total_paid;
                    } else if ( $field == 'pledge_next_pay_amount' ) { //special case for calculated field
                        $row[$field] = $dao->pledge_next_pay_amount + $dao->pledge_outstanding_amount;
                    } else if ( is_array( $value ) && $field == 'location' ) {
                        // fix header for location type case
                        foreach ( $value as $ltype => $val ) {
                            foreach ( array_keys( $val ) as $fld ) {
                                $type = explode( '-', $fld );
                                $fldValue = "{$ltype}-" . $type[0];
                            
                                if ( CRM_Utils_Array::value( 1, $type ) ) {
                                    $fldValue .= "-" . $type[1];
                                }
                            
                                $row[$fldValue] = $dao->$fldValue;
                            }
                        }
                    } else if ( array_key_exists( $field, $contactRelationshipTypes ) ) {
                        $relDAO = $allRelContactArray[$field][$dao->contact_id];

                        foreach ( $value as $relationField => $relationValue ) {
                            if ( is_object( $relDAO ) && property_exists( $relDAO, $relationField ) ) {
                                $fieldValue = $relDAO->$relationField;
                                if ( $relationField == 'phone_type_id' ) {
                                    $fieldValue = $phoneTypes[$relationValue];
                                } else if ( $relationField == 'provider_id' ) {
                                    $fieldValue = CRM_Utils_Array::value( $relationValue, $imProviders );  
                                }
                            } else {
                                $fieldValue = '';
                            }
                            if ( $relationField == 'id' ) {
                                $row[$field . $relationField] = $relDAO->contact_id;
                            } else  if ( is_array( $relationValue ) && $relationField == 'location' ) {
                                foreach ( $relationValue as $ltype => $val ) {
                                    foreach ( array_keys( $val ) as $fld ) {
                                        $type     = explode( '-', $fld );
                                        $fldValue = "{$ltype}-" . $type[0];
                                        if ( CRM_Utils_Array::value( 1, $type ) ) {
                                            $fldValue .= "-" . $type[1];
                                        }
                                        $row[$field . $fldValue] = $relDAO->$fldValue;
                                    }
                                }
                            } else if ( isset( $fieldValue ) && $fieldValue != '' ) {
                                //check for custom data
                                if ( $cfID = CRM_Core_BAO_CustomField::getKeyID( $relationField ) ) {
                                    $row[$field . $relationField] = 
                                        CRM_Core_BAO_CustomField::getDisplayValue( $fieldValue, $cfID, 
                                                                                   $relationQuery[$field]->_options );
                                } else if ( in_array( $relationField, array( 'email_greeting', 'postal_greeting', 'addressee' ) ) ) {
                                    //special case for greeting replacement
                                    $fldValue    = "{$relationField}_display";
                                    $row[$field . $relationField] = $relDAO->$fldValue;
                                } else {
                                    //normal relationship fields
                                    $row[$field . $relationField] = $fieldValue;
                                }
                            } else {
                                // if relation field is empty or null
                                $row[$field . $relationField] = '';             
                            }
                        }
                    } else if ( isset( $fieldValue ) && $fieldValue != '' ) {
                        //check for custom data
                        if ( $cfID = CRM_Core_BAO_CustomField::getKeyID( $field ) ) {
                            $row[$field] = CRM_Core_BAO_CustomField::getDisplayValue( $fieldValue, $cfID, $query->_options );
                        } else if ( array_key_exists( $field, $multipleSelectFields ) ) {
                            //option group fixes
                            $paramsNew = array( $field => $fieldValue );
                            if ( $field == 'test_tutoring') {
                                $name = array( $field => array( 'newName' => $field, 'groupName' => 'test' ) );
                            } else if ( substr( $field, 0, 4) == 'cmr_') { //for  readers group
                                $name = array( $field => array( 'newName' => $field, 'groupName' => substr( $field, 0, -3 ) ) );
                            } else {
                                $name = array( $field => array( 'newName' => $field, 'groupName' => $field ) );
                            }
                            CRM_Core_OptionGroup::lookupValues( $paramsNew, $name, false );
                            $row[$field] = $paramsNew[$field];
                        } else if ( in_array( $field, array( 'email_greeting', 'postal_greeting', 'addressee' ) ) ) {
                            //special case for greeting replacement
                            $fldValue    = "{$field}_display";
                            $row[$field] = $dao->$fldValue;
                        } else {
                            //normal fields
                            $row[$field] = $fieldValue;
                        }
                    } else {
                        // if field is empty or null
                        $row[$field] = '';             
                    }
                }

                // add payment headers if required
                if ( $addPaymentHeader && $paymentFields ) {
                    $headerRows = array_merge( $headerRows, $paymentHeaders );
                    foreach ( $paymentHeaders as $paymentHdr ) {
                        self::sqlColumnDefn( $query, $sqlColumns, $paymentHdr );
                    }
                    $addPaymentHeader = false;
                }

                if ( $setHeader ) {
                    $exportTempTable = self::createTempTable( $sqlColumns );
                }

                //build header only once
                $setHeader = false;
        
                // add payment related information
                if ( $paymentFields && isset( $paymentDetails[ $row[$paymentTableId] ] ) ) {
                    $row = array_merge( $row, $paymentDetails[ $row[$paymentTableId] ] );
                } else if ( $paymentDetails ) {
                    $row = array_merge( $row, $nullContributionDetails );  
                }

                //remove organization name for individuals if it is set for current employer
                if ( CRM_Utils_Array::value('contact_type', $row ) && $row['contact_type'] == 'Individual' && array_key_exists('organization_name', $row ) ) {
                    $row['organization_name'] = '';
                }

                // CRM-3157: localise the output
                // FIXME: we should move this to multilingual stack some day
                require_once 'CRM/Core/I18n.php';
                $i18n =& CRM_Core_I18n::singleton();
                $translatable = array('preferred_communication_method', 
                                      'preferred_mail_format',
                                      'gender',
                                      'state_province',
                                      'country',
                                      'world_region');
                foreach ( $translatable as $column ) {
                    if ( isset( $row[$column] ) and $row[$column] ) {
                        $row[$column] = $i18n->translate( $row[$column] );
                    }
                }

                // add component info
                // write the row to a file
                $componentDetails[] = $row;

                // output every $rowCount rows
                if ( $count % $rowCount == 0 ) {
                    self::writeDetailsToTable( $exportTempTable, $componentDetails, $sqlColumns );
                    $componentDetails = array( );
                }

            }
            $dao->free( );
            $offset += $rowCount;
        }

        self::writeDetailsToTable( $exportTempTable, $componentDetails, $sqlColumns );

        // do merge same address and merge same household processing
        if ( $mergeSameAddress ) {
            self::mergeSameAddress( $exportTempTable, $headerRows, $sqlColumns, $drop );
        }
        
        // merge the records if they have corresponding households
        if ( $mergeSameHousehold ) {
            self::mergeSameHousehold( $exportTempTable, $headerRows, $sqlColumns, $relationKey );
        }

        // fix the headers for rows with relationship type
        if ( $relName ) {
            self::manipulateHeaderRows( $headerRows, $contactRelationshipTypes );
        }

        // now write the CSV file
        self::writeCSVFromTable( $exportTempTable, $headerRows, $sqlColumns, $exportMode );

        CRM_Utils_System::civiExit( );
    }

    /**
     * name of the export file based on mode
     *
     * @param string  $output type of output
     * @param int     $mode export mode
     * @return string name of the file
     */
    function getExportFileName( $output = 'csv', $mode = CRM_Export_Form_Select::CONTACT_EXPORT ) 
    {
        switch ( $mode ) {
        case CRM_Export_Form_Select::CONTACT_EXPORT : 
            return ts('CiviCRM Contact Search');
            
        case CRM_Export_Form_Select::CONTRIBUTE_EXPORT : 
            return ts('CiviCRM Contribution Search');
            
        case CRM_Export_Form_Select::MEMBER_EXPORT : 
            return ts('CiviCRM Member Search');
            
        case CRM_Export_Form_Select::EVENT_EXPORT : 
            return ts('CiviCRM Participant Search');

        case CRM_Export_Form_Select::PLEDGE_EXPORT : 
            return ts('CiviCRM Pledge Search');
            
        case CRM_Export_Form_Select::CASE_EXPORT : 
            return ts('CiviCRM Case Search');
            
        case CRM_Export_Form_Select::GRANT_EXPORT : 
            return ts('CiviCRM Grant Search');

        case CRM_Export_Form_Select::ACTIVITY_EXPORT : 
            return ts('CiviCRM Activity Search');
        }
    }
    
    /**
     * Function to handle import error file creation.
     *
     **/
    function invoke( ) 
    {
        $type       = CRM_Utils_Request::retrieve( 'type',   'Positive', CRM_Core_DAO::$_nullObject );
        $parserName = CRM_Utils_Request::retrieve( 'parser', 'String',   CRM_Core_DAO::$_nullObject );
        if ( empty( $parserName ) || empty( $type ) ) return;
        
        require_once(str_replace('_', DIRECTORY_SEPARATOR, $parserName ) . ".php");
        eval( '$errorFileName =' . $parserName . '::errorFileName( $type );' );
        eval( '$saveFileName =' . $parserName . '::saveFileName( $type );' );
        if ( empty( $errorFileName ) || empty( $saveFileName ) ) return; 
        
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-Type: text/csv');
        header('Content-Length: ' . filesize( $errorFileName ) );
        header('Content-Disposition: attachment; filename=' . $saveFileName );
        
        readfile( $errorFileName );
        
        CRM_Utils_System::civiExit( );
    }
    
    function exportCustom( $customSearchClass, $formValues, $order ) 
    {
        require_once( str_replace( '_', DIRECTORY_SEPARATOR, $customSearchClass ) . '.php' );
        eval( '$search = new ' . $customSearchClass . '( $formValues );' );
      
        $includeContactIDs = false;
        if ( $formValues['radio_ts'] == 'ts_sel' ) {
            $includeContactIDs = true;
        }

        $sql    = $search->all( 0, 0, $order, $includeContactIDs );

        $columns = $search->columns( );

        $header = array_keys  ( $columns );
        $fields = array_values( $columns );

        $rows = array( );
        $dao = CRM_Core_DAO::executeQuery( $sql );
        $alterRow = false;
        if ( method_exists( $search, 'alterRow' ) ) {
            $alterRow = true;
        }
        while ( $dao->fetch( ) ) {
            $row = array( );

            foreach ( $fields as $field ) {
                $row[$field] = $dao->$field;
            }
            if ( $alterRow ) {
                $search->alterRow( $row );
            }
            $rows[] = $row;
        }

        require_once 'CRM/Core/Report/Excel.php';
        CRM_Core_Report_Excel::writeCSVFile( self::getExportFileName( ), $header, $rows );
        CRM_Utils_System::civiExit( );
    }

    static function sqlColumnDefn( &$query, &$sqlColumns, $field )
    {
        if ( substr( $field, -4 ) == '_a_b' ||
             substr( $field, -4 ) == '_b_a' ) {
            return;
        }
        
        $fieldName = CRM_Utils_String::munge( strtolower( $field ), '_', 64 );
        if ( $fieldName == 'id' ) {
            $fieldName = 'civicrm_primary_id';
        }
        
        // set the sql columns
        if ( isset( $query->_fields[$field]['type'] ) ) {
            switch ( $query->_fields[$field]['type'] ) {
            
            case CRM_Utils_Type::T_INT:
            case CRM_Utils_Type::T_BOOL:
            case CRM_Utils_Type::T_BOOLEAN:
                $sqlColumns[$fieldName] = "$fieldName varchar(16)";
                break;

            case CRM_Utils_Type::T_STRING:
                if ( isset( $query->_fields[$field]['maxlength'] ) ) {
                    $sqlColumns[$fieldName] = "$fieldName varchar({$query->_fields[$field]['maxlength']})";
                } else {
                    $sqlColumns[$fieldName] = "$fieldName varchar(64)";
                }
                break;

            case CRM_Utils_Type::T_TEXT:
            case CRM_Utils_Type::T_LONGTEXT:
            case CRM_Utils_Type::T_BLOB:
            case CRM_Utils_Type::T_MEDIUMBLOB:
                $sqlColumns[$fieldName] = "$fieldName longtext";
                break;

            case CRM_Utils_Type::T_FLOAT:
            case CRM_Utils_Type::T_ENUM:
            case CRM_Utils_Type::T_DATE:
            case CRM_Utils_Type::T_TIME:
            case CRM_Utils_Type::T_TIMESTAMP:
            case CRM_Utils_Type::T_MONEY:
            case CRM_Utils_Type::T_EMAIL:
            case CRM_Utils_Type::T_URL:
            case CRM_Utils_Type::T_CCNUM:
            default:
                $sqlColumns[$fieldName] = "$fieldName varchar(32)";
                break;
            }
        } else {
            if ( substr( $fieldName, -3, 3 ) == '_id' ) {
                $sqlColumns[$fieldName] = "$fieldName varchar(16)";
            } else {
                $changeFields = array( 'groups', 'tags', 'notes' );
                if ( in_array( $fieldName, $changeFields) ) {
                    $sqlColumns[$fieldName] = "$fieldName text";
                } else {
                    $sqlColumns[$fieldName] = "$fieldName varchar(64)";
                }
            }
        }
    }

    static function writeDetailsToTable( $tableName, &$details, &$sqlColumns )
    {
        if ( empty( $details ) ) {
            return;
        }
        
        $sql = "
SELECT max(id)
FROM   $tableName
";
        
        $id = CRM_Core_DAO::singleValueQuery( $sql );
        if ( ! $id ) {
            $id = 0;
        }

        $sqlClause = array( );

        foreach ( $details as $dontCare => $row ) {
            $id++;
            $valueString = array( $id );
            foreach ( $row as $dontCare => $value ) {
                if ( empty( $value ) ) {
                    $valueString[] = "''";
                } else {
                    $valueString[] = "'" . CRM_Core_DAO::escapeString( $value ) . "'";
                }
            }
            $sqlClause[] = '(' . implode( ',', $valueString ) . ')';
        }

        $sqlColumnString = '(id, ' . implode( ',', array_keys( $sqlColumns ) ) . ')';

        $sqlValueString  = implode( ",\n", $sqlClause );
        
        $sql = "
INSERT INTO $tableName $sqlColumnString
VALUES $sqlValueString
";
        
        CRM_Core_DAO::executeQuery( $sql );
    }

    static function createTempTable( &$sqlColumns )
    {
        //creating a temporary table for the search result that need be exported
        $exportTempTable = CRM_Core_DAO::createTempTableName( 'civicrm_export', false );

        // also create the sql table
        $sql = "DROP TABLE IF EXISTS {$exportTempTable}";
        CRM_Core_DAO::executeQuery( $sql );
                    
        $sql = "
CREATE TABLE {$exportTempTable} ( 
     id int unsigned NOT NULL AUTO_INCREMENT,
";
        $sql .= implode( ",\n", array_values( $sqlColumns ) );

        $sql .= ",
  PRIMARY KEY ( id )
";
        // add indexes for street_address and household_name if present
        $addIndices = array( 'street_address', 'household_name', 'civicrm_primary_id' );
        foreach ( $addIndices as $index ) {
            if ( isset( $sqlColumns[$index] ) ) {
                $sql .= ",
  INDEX index_{$index}( $index )
";
            }
        }

        $sql .= "
) ENGINE=MyISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci
";

        CRM_Core_DAO::executeQuery( $sql );
        return $exportTempTable;
    }

    static function mergeSameAddress( $tableName, &$headerRows, &$sqlColumns, $drop = false)
    {
        // find all the records that have the same street address BUT not in a household
        $sql = "
SELECT    r1.id as master_id,
          r1.last_name as last_name,
          r1.addressee as master_addressee,
          r2.id as copy_id,
          r2.last_name as copy_last_name,
          r2.addressee as copy_addressee
FROM      $tableName r1
LEFT JOIN $tableName r2 ON r1.street_address = r2.street_address
WHERE     ( r1.household_name IS NULL OR r1.household_name = '' )
AND       ( r2.household_name IS NULL OR r2.household_name = '' )
AND       ( r1.street_address != '' )
AND       r2.id > r1.id
ORDER BY  r1.id
";

        $dao = CRM_Core_DAO::executeQuery( $sql );
        $mergeLastName = true;
        $merge = $parents = $masterAddressee = array( );
        while ( $dao->fetch( ) ) {
            $masterID = $dao->master_id;
            $copyID   = $dao->copy_id;
            $lastName = $dao->last_name;
            $copyLastName = $dao->copy_last_name;

            // merge last names only when same
            if ( $lastName != $copyLastName ) {
                $mergeLastName = false;
            }

            if ( ! isset( $merge[$masterID] ) ) {
                // check if this is an intermediate child
                // this happens if there are 3 or more matches a,b, c
                // the above query will return a, b / a, c / b, c
                // we might be doing a bit more work, but for now its ok, unless someone
                // knows how to fix the query above
                if ( isset( $parents[$masterID] ) ) {
                    $masterID = $parents[$masterID];
                } else {
                    $merge[$masterID] = array( 'addressee' => $dao->master_addressee,
                                               'copy'      => array( ) );
                }
            }
            $parents[$copyID] = $masterID;
            $merge[$masterID]['copy'][$copyID] = $dao->copy_addressee;
        }

        $processed = array( );
        foreach ( $merge as $masterID => $values ) {
            if ( isset( $processed[$masterID] ) ) {
                CRM_Core_Error::fatal( );
            }
            $processed[$masterID] = 1;
            if ( $values['addressee'] ) {
                $masterAddressee = array( trim ( $values['addressee'] ) );
            }
            $deleteIDs = array( );
            foreach ( $values['copy'] as $copyID => $copyAddressee ) {
                if ( isset( $processed[$copyID] ) ) {
                    CRM_Core_Error::fatal( );
                }
                $processed[$copyID] = 1;
                if ( $copyAddressee ) {
                    $masterAddressee[] = trim ( $copyAddressee );
                }
                $deleteIDs[] = $copyID;
            }
            
            $addresseeString = implode( ', ', $masterAddressee );
            if ( $mergeLastName ) {
                $addresseeString = str_replace( " ".$lastName.",", ",", $addresseeString );
            }
            
            $sql = "
UPDATE $tableName
SET    addressee = %1
WHERE  id = %2
";
            $params = array( 1 => array( $addresseeString, 'String'  ),
                             2 => array( $masterID       , 'Integer' ) );
            CRM_Core_DAO::executeQuery( $sql, $params );
            
            // delete all copies
            $deleteIDString = implode( ',', $deleteIDs );
            $sql = "
DELETE FROM $tableName
WHERE  id IN ( $deleteIDString )
";
            CRM_Core_DAO::executeQuery( $sql );
        }

        // drop the table columns for last name
        // if added for addressee calculation
        if ( $drop ) {
            $dropQuery = "
ALTER TABLE $tableName
DROP  $drop";
            
            CRM_Core_DAO::executeQuery( $dropQuery );
            $allKeys = array_keys( $sqlColumns );

            if ( $key = CRM_Utils_Array::key( $drop, $allKeys ) ) {
                unset( $headerRows[$key] );
            }
            unset( $sqlColumns[$drop] );
        }
    }
    
    /**
     * Function to merge household record into the individual record
     * if exists
     *
     * @param string $exportTempTable temporary temp table that stores the records
     * @param array  $headerRows array of headers for the export file
     * @param array  $sqlColumns array of names of the table columns of the temp table
     * @param string $prefix name of the relationship type that is prefixed to the table columns
     */
    static function mergeSameHousehold( $exportTempTable, &$headerRows, &$sqlColumns, $prefix )
    {
        $prefixColumn = $prefix .'_';
        $allKeys = array_keys( $sqlColumns );
        $replaced = array( );

        // name map of the non standard fields in header rows & sql columns
        $mappingFields = array (
                                'civicrm_primary_id'  => 'internal contact id',
                                'url'                 => 'website',
                                'contact_sub_type'    => 'contact_subtype',
                                'is_opt_out'          => 'no_bulk_emails__user_opt_out_',
                                'external_identifier' => 'external_identifier__match_to_contact_',
                                'contact_source'      => 'source_of_contact_data',
                                'user_unique_id'      => 'unique_id__openid_',
                                'contact_source'      => 'source_of_contact_data',
                                'state_province'      => 'state',
                                'is_bulkmail'         => 'use_for_bulk_mail',
                                'im'                  => 'im_screen_name',
                                'groups'              => 'group_s_',
                                'tags'                => 'tag_s_',
                                'notes'               => 'note_s_',
                                'provider_id'         => 'im_service_provider',
                                'phone_type_id'       => 'phone_type'
                                );

        //figure out which columns are to be replaced by which ones
        foreach ( $sqlColumns as $columnNames => $dontCare ) {
            if ( $rep = CRM_Utils_Array::value( $columnNames, $mappingFields ) ) {
                $replaced[$columnNames] = CRM_Utils_String::munge( $prefixColumn . $rep, '_', 64 );
            } else {
                $householdColName = CRM_Utils_String::munge( $prefixColumn . $columnNames, '_', 64 );

                if ( CRM_Utils_Array::value( $householdColName, $sqlColumns ) ) {
                    $replaced[$columnNames] = $householdColName;
                }
            }
        }
        $query = "UPDATE $exportTempTable SET ";
       
        foreach( $replaced as $from => $to ) {
            $clause[] = "$from = $to ";
            unset( $sqlColumns[$to] );
            if ( $key = CRM_Utils_Array::key( $to, $allKeys ) ) {
                unset( $headerRows[$key] );
            }
        }
        $query .= implode( ",\n", $clause );
        $query .= " WHERE {$replaced['civicrm_primary_id']} != ''" ;
                
        CRM_Core_DAO::executeQuery( $query );

        //drop the table columns that store redundant household info
        $dropQuery = "ALTER TABLE $exportTempTable ";
        foreach ( $replaced as $householdColumns ) {
            $dropClause[] = " DROP $householdColumns ";
        }
        $dropQuery .= implode( ",\n", $dropClause );

        CRM_Core_DAO::executeQuery( $dropQuery );

        // also drop the temp table if exists
        $sql = "DROP TABLE IF EXISTS {$exportTempTable}_temp";
        CRM_Core_DAO::executeQuery( $sql );

        // clean up duplicate records
        $query = "
CREATE TABLE {$exportTempTable}_temp SELECT *
FROM {$exportTempTable}
GROUP BY civicrm_primary_id ";

        $dao = CRM_Core_DAO::executeQuery( $query );

        $query = "DROP TABLE $exportTempTable";
        $dao = CRM_Core_DAO::executeQuery( $query );
        
        $query = "ALTER TABLE {$exportTempTable}_temp RENAME TO {$exportTempTable}";
        $dao = CRM_Core_DAO::executeQuery( $query );
    }

    static function writeCSVFromTable( $exportTempTable, $headerRows, $sqlColumns, $exportMode )
    {
        $writeHeader = true;
        $offset = 0;
        $limit  = self::EXPORT_ROW_COUNT;

        $query = "
SELECT *
FROM   $exportTempTable
";
        require_once 'CRM/Core/Report/Excel.php';
        while ( 1 ) {
            $limitQuery = $query . "
LIMIT $offset, $limit
";
            $dao = CRM_Core_DAO::executeQuery( $limitQuery );
           
            if ( $dao->N <= 0 ) {
                break;
            }
            
            $componentDetails = array( );
            while ( $dao->fetch( ) ) {
                $row = array( );

                foreach ( $sqlColumns as $column => $dontCare ) {
                    $row[$column] = $dao->$column;
                }

                $componentDetails[] = $row;
            }
            CRM_Core_Report_Excel::writeCSVFile( self::getExportFileName( 'csv', $exportMode ), $headerRows,
                                                 $componentDetails, null, $writeHeader );
            $writeHeader = false;
            $offset += $limit;
        }
    }
    
    /**
     * Function to manipulate header rows for relationship fields
     * 
     */
    function manipulateHeaderRows( &$headerRows, $contactRelationshipTypes )
    {
        foreach ( $headerRows as &$header ) {
            $split = explode( '-', $header );
            if ( $relationTypeName = CRM_Utils_Array::value( $split[0], $contactRelationshipTypes ) ) {
                $split[0] = $relationTypeName;
                $header = implode( '-', $split );
            }
        }
    }
}
