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
 * This class stores logic for managing CiviCRM extensions.
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

require_once 'CRM/Core/Config.php';
require_once 'CRM/Core/Extensions.php';

class CRM_Core_Extensions_ExtensionType
{

    /**
     * 
     */
    const OPTION_GROUP_NAME = 'system_extensions';

    private $allowedExtTypes = array( 'payment', 'search', 'report' );

    protected static $_extensions = null;

    function __construct( ) {
        $ext = CRM_Core_Extensions::singleton();
        self::$_extensions = $ext->getExtensions();
        $config = CRM_Core_Config::singleton( );
        $this->extDir = $config->extensionsDir;
    }
    
    public function install( $id, $key ) {
        $this->createEntry( $id, $key );       
    }

    public function deinstall( $id, $key ) {
        $this->deleteEntry( $id, $key, true );
    }

    public function moveFiles( $id, $key, $deleteOrginal = false ) {
        $e = self::$_extensions;
        if( $e['per_id'][$id]['status'] === 'uploaded' ) {
            require_once( 'CRM/Utils/File.php' );
            CRM_Utils_File::copyDir( $e['per_id'][$id]['path'], $this->extDir . DIRECTORY_SEPARATOR . $e['per_id'][$id]['type'] . DIRECTORY_SEPARATOR . $e['per_id'][$id]['key'] );
            
            if ( $deleteOrginal ) {
                $this->deleteFiles( $id, $key );
            }
        }
    }

    public function createEntry( $id, $key ) {
        $e = self::$_extensions;

        $ids = array();

        $groupId = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionGroup', self::OPTION_GROUP_NAME, 'id', 'name' );
            
        $params = array( 'option_group_id' => $groupId,
                         'weight' => CRM_Utils_Weight::getDefaultWeight( 'CRM_Core_DAO_OptionValue',
                                                                          array( 'option_group_id' => $groupId) ),
                         'label' => $e['per_id'][$id]['label'],
                         'name'  => $e['per_id'][$id]['label'],
                         'value' => $key,
                         'grouping' => $e['per_id'][$id]['type'],
                         'is_active' => 1
                      );
        $optionValue = CRM_Core_BAO_OptionValue::add($params, $ids);
                
    }

    public function deleteEntry( $id, $key ) {
        $e = self::$_extensions;
        if( $e['per_id'][$id]['status'] === 'enabled' ) {
            require_once( 'CRM/Core/DAO/OptionValue.php' );
            $optionValue = new CRM_Core_DAO_OptionValue( );
            $optionValue->id = $id;
            return $optionValue->delete();
        }
        return false;
    }
    
    public function deleteFiles( $id, $key ) {
        require_once( 'CRM/Utils/File.php' );
        $e = self::$_extensions;
        CRM_Utils_File::cleanDir( $e['per_id'][$id]['path'] );
    }
    
}