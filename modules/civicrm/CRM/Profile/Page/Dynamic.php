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

require_once 'CRM/Core/Page.php';

/**
 * Create a page for displaying CiviCRM Profile Fields.
 *
 * Heart of this class is the run method which checks
 * for action type and then displays the appropriate
 * page.
 *
 */
class CRM_Profile_Page_Dynamic extends CRM_Core_Page {
    
    /**
     * The contact id of the person we are viewing
     *
     * @var int
     * @access protected
     */
    protected $_id;

    /**
     * the profile group are are interested in
     * 
     * @var int 
     * @access protected 
     */ 
    protected $_gid;

    /**
     * The profile types we restrict this page to display
     *
     * @var string
     * @access protected
     */
    protected $_restrict;

    /**
     * Should we bypass permissions
     *
     * @var boolean
     * @access prootected
     */
    protected $_skipPermission;

    /**
     * class constructor
     *
     * @param int $id  the contact id
     * @param int $gid the group id
     *
     * @return void
     * @access public
     */
    function __construct( $id, $gid, $restrict, $skipPermission = false ) {
        $this->_id       = $id;
        $this->_gid      = $gid;
        $this->_restrict = $restrict;
        $this->_skipPermission = $skipPermission;

        parent::__construct( );
    }

    /**
     * Get the action links for this page.
     *
     * @return array $_actionLinks
     *
     */
    function &actionLinks()
    {
        return null;
    }
    
    /**
     * Run the page.
     *
     * This method is called after the page is created. It checks for the  
     * type of action and executes that action. 
     *
     * @return void
     * @access public
     *
     */
    function run()
    {
        $template = CRM_Core_Smarty::singleton( ); 
        if ( $this->_id && $this->_gid ) {

            // first check that id is part of the limit group id, CRM-4822
            $limitListingsGroupsID = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_UFGroup',
                                                                  $this->_gid,
                                                                  'limit_listings_group_id' );
            $config = CRM_Core_Config::singleton( );
            if ( $limitListingsGroupsID ) {
                require_once 'CRM/Contact/BAO/GroupContact.php';
                
                if ( !CRM_Contact_BAO_GroupContact::isContactInGroup( $this->_id, 
                                                                      $limitListingsGroupsID ) ) {
                    CRM_Utils_System::setTitle( ts( 'Profile View - Permission Denied' ) );
                    return CRM_Core_Session::setStatus(ts('You do not have permission to view this contact record. Contact the site administrator if you need assistance.'));
                }
            }
            
            require_once 'CRM/Core/BAO/UFGroup.php';

            $values = array( );
            $fields = CRM_Core_BAO_UFGroup::getFields( $this->_gid, false, CRM_Core_Action::VIEW,
                                                       null, null, false, $this->_restrict, $this->_skipPermission,
                                                       null,
                                                       CRM_Core_Permission::VIEW );


            require_once 'CRM/Contact/BAO/Contact/Permission.php';
            
            // make sure we dont expose all fields based on permission
            $admin = false; 
            $session  = CRM_Core_Session::singleton( ); 
            if ( ( ! $config->userFrameworkFrontend &&
                   ( CRM_Core_Permission::check( 'administer users' )  ||
                     CRM_Core_Permission::check( 'view all contacts' ) ||
                     CRM_Contact_BAO_Contact_Permission::allow( $this->_id, CRM_Core_Permission::VIEW ) ) ) ||
                 $this->_id == $session->get( 'userID' ) ) {
                $admin = true; 
            }

            if ( ! $admin ) {
                foreach ( $fields as $name => $field ) {
                    // make sure that there is enough permission to expose this field 
                    if ( $field['visibility'] == 'User and User Admin Only' ) {
                        unset( $fields[$name] );
                    }
                }
            }
            CRM_Core_BAO_UFGroup::getValues( $this->_id, $fields, $values );
            
            // $profileFields array can be used for customized display of field labels and values in Profile/View.tpl
            $profileFields = array( );
            $labels = array( );

            foreach ( $fields as $name => $field ) {
                $labels[$field['title']] = preg_replace('/\s+|\W+/', '_', $name);
            }
            
            foreach ( $values as $title => $value ) {
                $profileFields[$labels[$title]] = array( 'label' => $title,
                                                         'value' => $value );
            }
            
            $template->assign_by_ref( 'row', $values );
            $template->assign_by_ref( 'profileFields', $profileFields );
        }
        
        $name = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_UFGroup', $this->_gid, 'name' );
        
        if ( $name == 'summary_overlay' ) {
        	$template->assign( 'overlayProfile', true );
        }

        $title    = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_UFGroup', $this->_gid, 'title' );
        
        //CRM-4131.
        $displayName    = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact', $this->_id, 'display_name' );
        if ( $displayName ) {
            require_once 'CRM/Core/Permission.php';
            require_once 'CRM/Contact/BAO/Contact/Permission.php';
            $session   = CRM_Core_Session::singleton( );
            $config    = CRM_Core_Config::singleton( );
            if ( $session->get( 'userID' ) && 
                 CRM_Core_Permission::check('access CiviCRM') &&
                 CRM_Contact_BAO_Contact_Permission::allow( $session->get( 'userID' ), CRM_Core_Permission::VIEW ) &&
                 !$config->userFrameworkFrontend ) {
                $contactViewUrl = CRM_Utils_System::url('civicrm/contact/view', "action=view&reset=1&cid={$this->_id}", true);
                $this->assign( 'displayName', $displayName);
                $displayName = "<a href=\"$contactViewUrl\">{$displayName}</a>";
            } 
            $title .= ' - ' . $displayName;
        }
        
        CRM_Utils_System::setTitle( $title );

        // invoke the pagRun hook, CRM-3906
        require_once 'CRM/Utils/Hook.php';
        CRM_Utils_Hook::pageRun( $this );

        return trim( $template->fetch(  $this->getTemplateFileName( ) ) );
    }

    function getTemplateFileName() {
        if ( $this->_gid ) {
            $templateFile = "CRM/Profile/Page/{$this->_gid}/Dynamic.tpl";
            $template     =& CRM_Core_Page::getTemplate( );
            if ( $template->template_exists( $templateFile ) ) {
                return $templateFile;
            }
        }
        return parent::getTemplateFileName( );
    }

}


