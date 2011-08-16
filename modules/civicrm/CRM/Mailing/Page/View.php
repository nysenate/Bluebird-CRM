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

require_once 'CRM/Core/Page.php';
/**
 * a page for mailing preview
 */
class CRM_Mailing_Page_View extends CRM_Core_Page
{
    protected $_mailingID;
    protected $_mailing;
    protected $_contactID;

    /**
     * Lets do permission checking here
     * First check for valid mailing, if false return fatal
     * Second check for visibility
     * Call a hook to see if hook wants to override visibility setting
     */
    function checkPermission( )
    {
        if ( ! $this->_mailing ) {
            return false;
        }

        // check for visibility, if visibility is Public Pages and they have the permission
        // return true
		require_once 'CRM/Core/Permission.php';
        if ( $this->_mailing->visibility == 'Public Pages' &&
             CRM_Core_Permission::check( 'view public CiviMail content' )) {
            return true;
        }
		
        // if user is an admin, return true
        if ( CRM_Core_Permission::check( 'administer CiviCRM' ) ||
             CRM_Core_Permission::check( 'access CiviMail' ) ) {
            return true;
        }

        return false;
    }

    /** 
     * run this page (figure out the action needed and perform it).
     * 
     * @return void
     */ 
    function run( $id = null, $contact_id = null, $print = true )
    {               
        if ( is_numeric( $id ) ) {
            $this->_mailingID = $id;
        } else {
            $print = true;
            $this->_mailingID = CRM_Utils_Request::retrieve( 'id', 'Integer', CRM_Core_DAO::$_nullObject, true );
        }        

		// # CRM-7651
		// override contactID from the function level if passed in
		if ( isset( $contactID ) &&
             is_numeric( $contactID )) {
			$this->_contactID = $contactID;
		} else {
			$session   =& CRM_Core_Session::singleton( );
			$this->_contactID = $session->get( 'userID' );
        }

        require_once 'CRM/Mailing/BAO/Mailing.php';
        $this->_mailing     = new CRM_Mailing_BAO_Mailing();
        $this->_mailing->id = $this->_mailingID;

        require_once 'CRM/Core/Error.php';
        if ( ! $this->_mailing->find( true ) ||
             ! $this->checkPermission( ) ) {
            require_once 'CRM/Utils/System.php';
            CRM_Utils_System::permissionDenied( );
            return;
        }

        CRM_Mailing_BAO_Mailing::tokenReplace( $this->_mailing );
        
        if ( defined( 'CIVICRM_MAIL_SMARTY' ) &&
             CIVICRM_MAIL_SMARTY ) {
            require_once 'CRM/Core/Smarty/resources/String.php';
            civicrm_smarty_register_string_resource( );
        }

        // get and format attachments
        require_once 'CRM/Core/BAO/File.php';
        $attachments =& CRM_Core_BAO_File::getEntityFile( 'civicrm_mailing',
                                                          $this->_mailing->id );
		
		// get contact detail and compose if contact id exists
		if(isset($this->_contactID)) {
			//get details of contact with token value including Custom Field Token Values.CRM-3734
			$returnProperties = $this->_mailing->getReturnProperties( );
			$params  = array( 'contact_id' => $this->_contactID );
			$details = $this->_mailing->getDetails( $params, $returnProperties );
			$details = $details[0][$this->_contactID];
		} else {
			$details = array('test');
		}
        $mime =& $this->_mailing->compose( null, null, null, 0,
                                           $this->_mailing->from_email,
                                           $this->_mailing->from_email,
                                           true, $details, $attachments );

        if ( isset( $this->_mailing->body_html ) ) {
            $header = 'Content-Type: text/html; charset=utf-8';
            $content = $mime->getHTMLBody();
        } else {
            $header = 'Content-Type: text/plain; charset=utf-8';
            $content = $mime->getTXTBody();
        }
        
        if ( $print ) {
            header( $header );
            print $content;
            CRM_Utils_System::civiExit( );
        } else {
            return $content;
        }
    }
}