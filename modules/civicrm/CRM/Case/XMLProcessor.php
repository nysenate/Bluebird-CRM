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

class CRM_Case_XMLProcessor {

    static protected $_xml;

    function retrieve( $caseType ) {
        require_once 'CRM/Utils/String.php';
        require_once 'CRM/Utils/Array.php';

        // trim all spaces from $caseType
        $caseType = str_replace('_', ' ', $caseType );
        $caseType = CRM_Utils_String::munge( ucwords($caseType), '', 0 );
       
        if ( ! CRM_Utils_Array::value( $caseType, self::$_xml ) ) {
            if ( ! self::$_xml ) {
                self::$_xml = array( );
            }

            // first check custom templates directory
            $fileName = null;
            $config   = CRM_Core_Config::singleton( );
            if ( isset( $config->customTemplateDir ) &&
                 $config->customTemplateDir ) {
                // check if the file exists in the custom templates directory
                $fileName = implode( DIRECTORY_SEPARATOR,
                                     array( $config->customTemplateDir,
                                            'CRM',
                                            'Case', 
                                            'xml',
                                            'configuration',
                                            "$caseType.xml" ) );
            }
            
            if ( ! $fileName ||
                 ! file_exists( $fileName ) ) {
                // check if file exists locally
                $fileName = implode( DIRECTORY_SEPARATOR,
                                     array( dirname( __FILE__ ),
                                            'xml',
                                            'configuration',
                                            "$caseType.xml" ) );
                
                if ( ! file_exists( $fileName ) ) {
                    return false;
                }
            }

            // read xml file
            $dom = DomDocument::load( $fileName );
            $dom->xinclude( );
            self::$_xml[$caseType] = simplexml_import_dom( $dom );
        }
        return self::$_xml[$caseType];
    }

    function &allActivityTypes( $indexName = true, $all = false ) {
        static $activityTypes = null;
        if ( ! $activityTypes ) {
            require_once 'CRM/Case/PseudoConstant.php';
            $activityTypes = CRM_Case_PseudoConstant::activityType( $indexName, $all );
        }
        return $activityTypes; 
    }

    function &allRelationshipTypes( ) {
        static $relationshipTypes = array( );

        if ( ! $relationshipTypes ) {
            require_once 'CRM/Core/PseudoConstant.php';
            $relationshipInfo  = CRM_Core_PseudoConstant::relationshipType( );

            $relationshipTypes = array( );
            foreach ( $relationshipInfo as $id => $info ) {
                $relationshipTypes[$id] = $info['label_b_a'];
            }
        }

        return $relationshipTypes;
    }

}