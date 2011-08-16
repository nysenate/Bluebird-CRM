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
 * This is a part of CiviCRM extension management functionality.
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

require_once 'CRM/Core/Page/Basic.php';

/**
 * This page displays the list of extensions registered in the system.
 */
class CRM_Admin_Page_Extensions extends CRM_Core_Page_Basic 
{

    /**
     * The action links that we need to display for the browse screen
     *
     * @var array
     * @static
     */
    static $_links = null;

    static $_extensions = null;

    /**
     * Obtains the group name from url and sets the title.
     *
     * @return void
     * @access public
     *
     */
    function preProcess( )
    {
        require_once 'CRM/Core/Extensions.php';
        $ext = new CRM_Core_Extensions();
        if( $ext->enabled === TRUE ) {
            self::$_extensions = $ext->getExtensions( );
        }
        CRM_Utils_System::setTitle(ts('CiviCRM Extensions'));
    }

    /**
     * Get BAO Name
     *
     * @return string Classname of BAO.
     */
    function getBAOName() 
    {
        return 'CRM_Core_BAO_OptionValue';
    }

    /**
     * Get action Links
     *
     * @return array (reference) of action links
     */
    function &links()
    {
        if (!(self::$_links)) {
            self::$_links = array(
                                  CRM_Core_Action::ADD     => array(
                                                                    'name'  => ts('Install'),
                                                                    'url'   => 'civicrm/admin/extensions',
                                                                    'qs'    => 'action=add&id=%%id%%&key=%%key%%',
                                                                    'title' => ts('Install')
                                                                    ),
                                  CRM_Core_Action::ENABLE  => array(
                                                                    'name'  => ts('Enable'),
                                                                    'extra' => 'onclick = "enableDisable( \'%%id%%\',\''. 'CRM_Core_Extensions' . '\',\'' . 'disable-enable' . '\',\'' . 'true' . '\' );"',
                                                                    'ref'   => 'enable-action',
                                                                    'title' => ts('Enable')
                                                                    ),
                                  CRM_Core_Action::DISABLE => array(
                                                                    'name'  => ts('Disable'),
                                                                    'extra' => 'onclick = "enableDisable( \'%%id%%\',\''. 'CRM_Core_Extensions' . '\',\'' . 'enable-disable' . '\',\'' . 'true' . '\' );"',
                                                                    'ref'   => 'disable-action',
                                                                    'title' => ts('Disable')
                                                                    ),

                                  CRM_Core_Action::DELETE  => array(
                                                                    'name'  => ts('Uninstall'),
                                                                    'url'   => 'civicrm/admin/extensions',
                                                                    'qs'    => 'action=delete&id=%%id%%&key=%%key%%',
                                                                    'title' => ts('Uninstall Extension') 
                                                                    ),
                                  CRM_Core_Action::UPDATE  => array(
                                                                    'name'  => ts('Upgrade'),
                                                                    'url'   => 'civicrm/admin/extensions',
                                                                    'qs'    => 'action=update&id=%%id%%&key=%%key%%',
                                                                    'title' => ts('Upgrade Extension') 
                                                                    )                                                                    
                                  );
            
        }
        return self::$_links;
    }

    /**
     * Run the basic page (run essentially starts execution for that page).
     *
     * @return void
     */
    function run()
    {
        $this->preProcess();
        parent::run();
    }
    
    /**
     * Browse all options
     *  
     * 
     * @return void
     * @access public
     * @static
     */
    function browse()
    {

        $this->assign('extEnabled', FALSE );
        if ( self::$_extensions ) {
            $this->assign('extEnabled', TRUE );
        } else {
            return;
        }

        $extensionRows = array();
        $em  = self::$_extensions;

        $fid = 1;
        require_once 'CRM/Core/Extensions/Extension.php';
        foreach( $em as $key => $obj ) {

            // rewrite ids to be numeric, but keep those which are 
            // installed (they have option_value table id)
            // It's totally unlikely, that installed extensions will
            // have ids below 50.
            if ( isset( $obj->id ) ) {
                $id = $obj->id;
            } else {
                $id = $fid++;
            }

            $extensionRows[$id] = (array) $obj;

            // assign actions
            if( $obj->status == CRM_Core_Extensions_Extension::STATUS_INSTALLED ) {
                if( $obj->is_active ) {
                    $action = CRM_Core_Action::DISABLE;
                    if( $obj->upgradable ) { $action += CRM_Core_Action::UPDATE; }
                } else {
                    $action = array_sum(array_keys($this->links()));
                    $action -= CRM_Core_Action::DISABLE;
                    $action -= CRM_Core_Action::ADD;
                    if( ! $obj->upgradable ) { $action -= CRM_Core_Action::UPDATE; }
                }
                $extensionRows[$id]['action'] = CRM_Core_Action::formLink(self::links(), $action,
                                                                          array('id' => $id, 
                                                                                'key' => $obj->key ));                
            } else {
                $action = array_sum(array_keys($this->links()));
                $action -= CRM_Core_Action::DISABLE;
                $action -= CRM_Core_Action::ENABLE;
                $action -= CRM_Core_Action::DELETE;
                $action -= CRM_Core_Action::UPDATE;
                $extensionRows[$id]['action'] = CRM_Core_Action::formLink(self::links(), $action,
                                                                          array('id' => $id, 
                                                                                'key' => $obj->key ));            
            } 
                        
            
        }

        $this->assign('extensionRows', $extensionRows);        

    }
    
    /**
     * Get name of edit form
     *
     * @return string Classname of edit form.
     */
    function editForm() 
    {
        return 'CRM_Admin_Form_Extensions';
    }
    
    /**
     * Get edit form name
     *
     * @return string name of this page.
     */
    function editName() 
    {
        return 'CRM_Admin_Form_Extensions';
    }
    
    /**
     * Get user context.
     *
     * @return string user context.
     */
    function userContext($mode = null) 
    {
        return 'civicrm/admin/extensions';
    }

    /**
     * function to get userContext params
     *
     * @param int $mode mode that we are in
     *
     * @return string
     * @access public
     */
    function userContextParams( $mode = null ) {
        return 'reset=1&action=browse';
    }

}


