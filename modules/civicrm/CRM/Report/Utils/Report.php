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


class CRM_Report_Utils_Report {

    static function getValueFromUrl( $instanceID = null ) {
        if ( $instanceID ) {
            $optionVal = CRM_Core_DAO::getFieldValue( 'CRM_Report_DAO_Instance',
                                                      $instanceID,
                                                      'report_id' );
        } else {
            $config = CRM_Core_Config::singleton( );
            $args   = explode( '/', $_GET[$config->userFrameworkURLVar] );

            // remove 'civicrm/report' from args
            array_shift($args);
            array_shift($args);

            // put rest of arguement back in the form of url, which is how value 
            // is stored in option value table
            $optionVal = implode( '/', $args );
        }
        return $optionVal;
    }

    static function getValueIDFromUrl( $instanceID = null ) {
        $optionVal = self::getValueFromUrl( $instanceID );

        if ( $optionVal ) {
            require_once 'CRM/Core/OptionGroup.php';
            $templateInfo = CRM_Core_OptionGroup::getRowValues( 'report_template', "{$optionVal}", 'value' );
            return array( $templateInfo['id'], $optionVal );
        }

        return false;
    }

    static function getInstanceIDForValue( $optionVal ) {
        static $valId = array();

        if ( ! array_key_exists($optionVal, $valId) ) {
            $sql = "
SELECT MAX(id) FROM civicrm_report_instance
WHERE  report_id = %1";
            
            $params = array( 1 => array( $optionVal, 'String' ) );
            $valId[$optionVal] = CRM_Core_DAO::singleValueQuery( $sql, $params );
        }
        return $valId[$optionVal];
    }

    static function getInstanceIDForPath( $path = null ) {
        static $valId = array();

 	 // if $path is null, try to get it from url
	 $path = self::getInstancePath();

	 if ( $path && ! array_key_exists($path, $valId) ) {
	     $sql = "
SELECT MAX(id) FROM civicrm_report_instance
WHERE  TRIM(BOTH '/' FROM CONCAT(report_id, '/', name)) = %1";

	     $params = array( 1 => array( $path, 'String' ) );
	     $valId[$path] = CRM_Core_DAO::singleValueQuery( $sql, $params );
	 }
     return CRM_Utils_Array::value( $path, $valId );
    }

    static function getNextUrl( $urlValue, $query = 'reset=1', $absolute = false, $instanceID = null ) {
        if ( $instanceID ) {
            $instanceID = self::getInstanceIDForValue( $urlValue );
                
            if ( $instanceID ) {
                return CRM_Utils_System::url( "civicrm/report/instance/{$instanceID}", 
                                              "{$query}", $absolute );
            } else {
                return false;
            }
        } else {
            return CRM_Utils_System::url( "civicrm/report/" . trim($urlValue, '/') , 
                                          $query, $absolute );
        }
    }

    // get instance count for a template 
    static function getInstanceCount( $optionVal ) {
        $sql = "
SELECT count(inst.id)
FROM   civicrm_report_instance inst
WHERE  inst.report_id = %1";

        $params = array( 1 => array( $optionVal, 'String' ) );
        $count  = CRM_Core_DAO::singleValueQuery( $sql, $params );
        return $count;
    }

    static function mailReport( $fileContent, $instanceID = null, $outputMode = 'html', $attachments = array() ) {
        if ( ! $instanceID ) {
            return false;
        }

        require_once 'CRM/Core/BAO/Domain.php';
        list( $domainEmailName, 
              $domainEmailAddress ) = CRM_Core_BAO_Domain::getNameAndEmail( );

        $params       = array( 'id' => $instanceID );
        $instanceInfo = array( );
        CRM_Core_DAO::commonRetrieve( 'CRM_Report_DAO_Instance',
                                      $params,
                                      $instanceInfo );

        $params                = array( );
        $params['groupName'  ] = 'Report Email Sender';
        $params['from'       ] = '"' . $domainEmailName . '" <' . $domainEmailAddress . '>';
        $params['toName'     ] = ""; //$domainEmailName;
        $params['toEmail'    ] = CRM_Utils_Array::value( 'email_to', $instanceInfo );
        $params['cc'         ] = CRM_Utils_Array::value( 'email_cc', $instanceInfo );
        $params['subject'    ] = CRM_Utils_Array::value( 'email_subject', $instanceInfo );
        if ( !is_array($instanceInfo['attachments']) ) {
            $instanceInfo['attachments'] = array();
        }
        $params['attachments'] = array_merge(CRM_Utils_Array::value( 'attachments', $instanceInfo ), $attachments);
        $params['text'       ] = '';
        $params['html'       ] = $fileContent;

        require_once "CRM/Utils/Mail.php";
        return CRM_Utils_Mail::send( $params );
    }

    static function export2csv( &$form, &$rows ) {
        //Mark as a CSV file.
        header('Content-Type: text/csv');

        //Force a download and name the file using the current timestamp.
        header('Content-Disposition: attachment; filename=Report_' . $_SERVER['REQUEST_TIME'] . '.csv');
        echo self::makeCsv( $form, $rows );
        CRM_Utils_System::civiExit( );
    }

    /**
     * Utility function for export2csv and CRM_Report_Form::endPostProcess
     * - make CSV file content and return as string.
     */
    static function makeCsv( &$form, &$rows ) {
        require_once 'CRM/Utils/Money.php';
        $config    = CRM_Core_Config::singleton( );
        $csv       = '';
          
        // Add headers if this is the first row.
        $columnHeaders = array_keys( $form->_columnHeaders );

        // Replace internal header names with friendly ones, where available.
        foreach ( $columnHeaders as $header ) {
            if ( isset( $form->_columnHeaders[$header] ) ) {
                $headers[] = '"'. html_entity_decode(strip_tags($form->_columnHeaders[$header]['title'])) . '"';
            }
        }
        // Add the headers.
        $csv .= implode(',', $headers) . "\n";

        $displayRows = array();
        $value       = null;
        foreach ( $rows as $row ) {
            foreach ( $columnHeaders as $k => $v ){
                if ( $value = CRM_Utils_Array::value( $v, $row ) ) {
                    // Remove HTML, unencode entities, and escape quotation marks.
                    $value = 
                        str_replace('"', '""', html_entity_decode(strip_tags($value)));
                    
                    if ( CRM_Utils_Array::value( 'type', $form->_columnHeaders[$v] ) & 4 ) {
                        if ( CRM_Utils_Array::value( 'group_by', $form->_columnHeaders[$v] ) == 'MONTH' ||
                             CRM_Utils_Array::value( 'group_by', $form->_columnHeaders[$v] ) ==  'QUARTER' ) {
                            $value =  CRM_Utils_Date::customFormat( $value, $config->dateformatPartial );
                        } elseif ( CRM_Utils_Array::value( 'group_by', $form->_columnHeaders[$v] ) == 'YEAR' ) {
                            $value =  CRM_Utils_Date::customFormat( $value, $config->dateformatYear );
                        } else {
                            $value =  CRM_Utils_Date::customFormat( $value,'%Y-%m-%d' );
                        }
                    } else if ( CRM_Utils_Array::value( 'type', $form->_columnHeaders[$v] ) == 1024 ) {
                        $value =  CRM_Utils_Money::format( $value );
                    }
                    $displayRows[$v] = '"'. $value .'"'; 
                } else {
                    $displayRows[$v] = " "; 
                }  
            }
            // Add the data row.
            $csv .= implode(',', $displayRows) . "\n";
        }

        return $csv;
    }

    static function add2group( &$form , $groupID ) {

        if ( is_numeric( $groupID ) && isset( $form->_aliases['civicrm_contact'] ) ) {

            require_once 'CRM/Contact/BAO/GroupContact.php';
            $sql = "SELECT DISTINCT {$form->_aliases['civicrm_contact']}.id AS contact_id {$form->_from} {$form->_where} ";
            $dao = CRM_Core_DAO::executeQuery( $sql );

            $contact_ids = array();                        
            // Add resulting contacts to group
            while ( $dao->fetch( ) ) {
                $contact_ids[] = $dao->contact_id;
            }

            CRM_Contact_BAO_GroupContact::addContactsToGroup( $contact_ids, $groupID );
            CRM_Core_Session::setStatus( ts("Listed contact(s) have been added to the selected group."));
        } 
    }
    static function getInstanceID() {

        $config    = CRM_Core_Config::singleton( );
        $arg       = explode( '/', $_GET[$config->userFrameworkURLVar] );
        
        require_once 'CRM/Utils/Rule.php';
        if ( $arg[1] == 'report' &&
             CRM_Utils_Array::value( 2, $arg ) == 'instance' ) {
            if ( CRM_Utils_Rule::positiveInteger( $arg[3] ) ) {
                return $arg[3];
            }
        }
    }

    static function getInstancePath() {
        $config    = CRM_Core_Config::singleton( );
        $arg       = explode( '/', $_GET[$config->userFrameworkURLVar] );
        
        if ( $arg[1] == 'report' &&
             CRM_Utils_Array::value( 2, $arg ) == 'instance' ) {
	     unset($arg[0], $arg[1], $arg[2]);
	     $path = trim( CRM_Utils_Type::escape( implode( '/', $arg ), 'String' ), '/' );
	     return $path;
        }
    }

    static function isInstancePermissioned( $instanceId ) {
        if ( ! $instanceId ) {
            return true;
        }

        $instanceValues = array( );
        $params         = array( 'id' => $instanceId );
        CRM_Core_DAO::commonRetrieve( 'CRM_Report_DAO_Instance',
                                      $params,
                                      $instanceValues );

        if ( !empty($instanceValues['permission']) && 
             ( !(CRM_Core_Permission::check( $instanceValues['permission'] ) ||
                 CRM_Core_Permission::check( 'administer Reports' )) ) ) {
            return false;
        }
        
        return true;
    }
}
