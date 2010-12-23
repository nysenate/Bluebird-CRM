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

require_once 'CRM/Core/Config.php';
require_once 'CRM/Core/Extensions/ExtensionType.php';

/**
 * This class stores logic for managing CiviCRM extensions.
 * On this level, we are only manipulating extension objects.
 * Refer to CRM_Core_Extensions_Extension class for more
 * information on single extension's operations.
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */
class CRM_Core_Extensions
{

    /**
     * The option group name
     */
    const OPTION_GROUP_NAME = 'system_extensions';

    /**
     * Extension info file name
     */
    const EXT_INFO_FILENAME = 'info.xml';

    /**
     * Extension info file name
     */
    const EXT_TEMPLATES_DIRNAME = 'templates';


    /**
     * Allows quickly verifying if extensions are enabled
     * 
     * @access private
     * @var boolean
     */
    public $enabled = FALSE;

    /**
     * Full path to extensions directory
     * 
     * @access private
     * @var null|string
     */
    private $_extDir = null;

    /**
     * List of active (installed) extensions ordered by id
     * 
     * @access private
     * @var null|array
     */
    private $_extById = null;

    /**
     * List of active (installed) extensions ordered by id
     * 
     * @access private
     * @var null|array
     */
    private $_extByKey = null;


    /**
     * Constructor - we're not initializing information here
     * since we don't want any database hits upon object
     * initialization.
     * 
     * @access public
     * @return void
     */
    public function __construct( ) {
        $config =& CRM_Core_Config::singleton( );
        if( isset( $config->extensionsDir ) ) {
            $this->_extDir = $config->extensionsDir;
        }
        if( ! empty( $this->_extDir ) ) {
            $this->enabled = TRUE;
        }        
    }

    /**
     * Populates variables containing information about extension.
	 * This method is not supposed to call on object initialisation.
     * 
     * @access public
     * @param boolean $fullInfo provide full info (read XML files) if true, otherwise only DB stored data
     * @return void
     */
    public function populate( $fullInfo = FALSE ) {
        if( is_null($this->_extDir) || empty( $this->_extDir ) ) {
            return;
        }
        
        $installed = $this->getInstalled( $fullInfo );
        $uploaded = $this->getNotInstalled( );
        $this->_extById = array_merge( $installed, $uploaded );
        $this->_extByKey = array();
        foreach( $this->_extById as $id => $ext ) {
            $this->_extByKey[$ext->key] = $ext;
        }
    }

    /**
     * Returns the list of extensions ordered by extension key.
     * 
     * @access public
     * @param boolean $fullInfo provide full info (read XML files) if true, otherwise only DB stored data
     * @return array the list of installed extensions
     */
    public function getExtensionsByKey( $fullInfo = FALSE ) {
        $this->populate( $fullInfo );
        return $this->_extByKey;
    }
    
    /**
     * Returns the list of extensions ordered by id.
     * 
     * @access public
     * @param boolean $fullInfo provide full info (read XML files) if true, otherwise only DB stored data
     * @return array the list of installed extensions
     */
    public function getExtensionsById( $fullInfo = FALSE ) {
        $this->populate( $fullInfo );
        return $this->_extById;
    }    

    /**
     * @todo DEPRECATE
     * 
     * @access public
     * @param boolean $fullInfo provide full info (read XML files) if true, otherwise only DB stored data
     * @return array list of extensions
     */
    public function getInstalled( $fullInfo = FALSE ) {
        return $this->_discoverInstalled( $fullInfo );
    }

    /**
    * @todo DEPRECATE
     * 
     * @access public
     * @return array list of extensions
     */
    public function getAvailable( ) {
        return $this->_discoverAvailable();
    }

    /**
     * Returns the list of extensions which hasn't been installed.
     * 
     * @access public
     * @return array list of extensions
     */
    public function getNotInstalled( ) {
        $installed = $this->_discoverInstalled();
        $result = $this->_discoverAvailable();
        $instKeys = array();
        foreach( $installed as $id => $ext ) {
            $instKeys[] = $ext->key;
        }
        foreach( $result as $id => $ext ) {
            if( array_key_exists( $ext->key, array_flip( $instKeys ) ) ) {
                unset( $result[$id] );
            }
        }
        return $result;                
    }    


    /**
     * Searches for and returnes installed extensions.
     * 
     * @access private
     * @param boolean $fullInfo provide full info (read XML files) if true, otherwise only DB stored data
     * @return array list of extensions
     */
    private function _discoverInstalled( $fullInfo = FALSE ) {
        require_once 'CRM/Core/OptionValue.php';
        require_once 'CRM/Core/Extensions/Extension.php';
        $result = array();        
        $groupParams = array( 'name' => self::OPTION_GROUP_NAME );
        $links = array();
        $ov = CRM_Core_OptionValue::getRows( $groupParams, $links );
        foreach( $ov as $id => $entry ) {
            $ext = new CRM_Core_Extensions_Extension( $entry['value'], $entry['grouping'], $entry['name'], 
                                                      $entry['label'], $entry['description'], $entry['is_active'] );
            $ext->setId($id);
            if( $fullInfo ) {
                $ext->readXMLInfo();            
            }
            $result[$id] = $ext;
        }
        return $result;
    }

    /**
     * Retrieve all the extension information for all the extensions
	 * in extension directory. Beware, we're relying on scandir's 
	 * extension retrieval order here, array indices will be used as 
	 * ids for extensions that are not installed later on.
     * 
     * @access private
     * @return array list of extensions
     */
    private function _discoverAvailable() {
        require_once 'CRM/Core/Extensions/Extension.php';
        $result = array();
        $e = scandir( $this->_extDir );
        foreach( $e as $dc => $name ) {
            $dir = $this->_extDir . DIRECTORY_SEPARATOR . $name;
            $infoFile = $dir . DIRECTORY_SEPARATOR . self::EXT_INFO_FILENAME;
            if( is_dir( $dir ) && file_exists( $infoFile ) ) {
                $ext = new CRM_Core_Extensions_Extension( $name );
                $ext->readXMLInfo();
                $result[] = $ext;
            }
        }
        return $result;
    }

    /**
     * Given the key, provides the path to file containing
	 * extension's main class.
     * 
     * @access public
     * @param string $key extension key
     * @return string path to file containing extension's main class
     */
    public function keyToPath( $key ) {
        $this->populate();
        $e = $this->_extByKey;
        
        $file = (string) $e[$key]->file;

        return
            $this->_extDir . 
            DIRECTORY_SEPARATOR .
            $key . 
            DIRECTORY_SEPARATOR . 
            $file . 
            '.php';
    }

    /**
     * Given the key, provides extension's class name.
     * 
     * @access public
     * @param string $key extension key
     * @return string name of extension's main class
     */
    public function keyToClass( $key ) {
        return str_replace( '.', '_', $key );
    }

    /**
     * Given the class, provides extension's key.
     * 
     * @access public
     * @param string $clazz extension class name
     * @return string name of extension key
     */
    public function classToKey( $clazz ) {
        return str_replace( '_', '.', $clazz );
    }

    /**
     * Given the class, provides extension path.
     * 
     * @access public
     * @param string $key extension key
     * @return string name of extension key
     */
    public function classToPath( $clazz ) {
        $elements = explode( '_', $clazz );
	$key = implode( '.', $elements );
	return $this->keyToPath( $key );
    }

    /**
     * Given the class, provides the template path.
     * 
     * @access public
     * @param string $clazz extension class name
     * @return string path to extension's templates directory
     */
    public function getTemplatePath( $clazz ) {
        $path = $this->classToPath( $clazz );
        $pathElm = explode( DIRECTORY_SEPARATOR, $path );
        array_pop( $pathElm );
        return implode( DIRECTORY_SEPARATOR, $pathElm ) . DIRECTORY_SEPARATOR . self::EXT_TEMPLATES_DIRNAME;
    }

    /**
     * Given te class, provides the template name.
	 * @todo consider multiple templates, support for one template for now
     * 
     * @access public
     * @param string $clazz extension class name
     * @return string extension's template name
     */    
    public function getTemplateName( $clazz ) {
        $this->populate();
        $e = $this->_extByKey;
        $file = (string) $e[$key]->file;
        $key = $this->classToKey( $clazz );
        return (string) $e[$key]->file . '.tpl' ;
    }    

    /**
     * Given the string, returns true or false if it's an extension key.
     * 
     * @access public
     * @param string $key a string which might be an extension key
     * @return boolean true if given string is an extension name
     */
    public function isExtensionKey( $key ) {
        // check if the string is an extension name or the class
        return ( strpos($key, '.') !== FALSE ) ? TRUE : FALSE;
    }

    /**
     * Given the string, returns true or false if it's an extension class name.
     * 
     * @access public
     * @param string $clazz a string which might be an extension class name
     * @return boolean true if given string is an extension class name
     */    
    public function isExtensionClass( $clazz ) {
        
        if ( substr( $clazz, 0, 4 ) != 'CRM_' ) {
            require_once 'CRM/Core/PseudoConstant.php';
            $extensions = CRM_Core_PseudoConstant::getExtensions( $clazz );
            if ( array_key_exists( $this->classToKey($clazz), $extensions ) ) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Sets extension's record active or disabled.
     * 
     * @access public
     * @param int $id id of option value record
	 * @param boolean $is_active active state
     * @return mixed result of CRM_Core_DAO::setFieldValue
     */
    static public function setIsActive( $id, $is_active ) {
        $extensions = new CRM_Core_Extensions();
        $e = $extensions->getExtensionsByKey();
        foreach( $e as $key => $eo ) {
            if( $eo->id == $id ) {
                $ext = $eo;
            }
        }
        $is_active ? $ext->enable() : $ext->disable();
        return true;
    }

    /**
     * Given the id from selector (generated in $this->_discoverAvailable),
     * fires off appropriate CRM_Core_Extensions_Extension object's install method.
     *
     * @todo change method signature, drop $id, work with $key only
     * 
     * @access public
     * @param int $id id of option value record
	 * @param string $key extension key
     * @return void
     */
    public function install( $id, $key ) {
        $e = $this->getNotInstalled();
        $ext = $e[$id];
        $ext->install();
    }

    /**
    * Given the key, fires off appropriate CRM_Core_Extensions_Extension object's 
	* uninstall method.
	*
	* @todo change method signature, drop $id, work with $key only
    * 
    * @access public
    * @param int $id id of option value record
	* @param string $key extension key
    * @return void
    */
    public function uninstall( $id, $key ) {
        $this->populate();
        $e = $this->getExtensionsByKey( );
        $ext = $e[$key];
        $ext->uninstall();
    }

}

