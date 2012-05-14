<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.1                                                |
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

/**
 * Dummy page for details of PHone 
 *
 */
class CRM_Contact_Page_Inline_Phone {
  /**
   * Run the page.
   *
   * This method is called after the page is created.
   *
   * @return void
   * @access public
   *
   */
  function run() {
    // get the emails for this contact      
    $contactId = CRM_Utils_Request::retrieve( 'cid', 'Positive', CRM_Core_DAO::$_nullObject, true, null, $_REQUEST );
 
    $locationTypes = CRM_Core_PseudoConstant::locationDisplayName();
    $phoneTypes = CRM_Core_PseudoConstant::phoneType();
    
    $entityBlock = array( 'contact_id' => $contactId );
    $phones = CRM_Core_BAO_Phone::getValues( $entityBlock );
    if ( !empty( $phones ) ) {
      foreach( $phones as $key => &$value ) {
        $value['location_type'] = $locationTypes[$value['location_type_id']];
        $value['phone_type']    = $phoneTypes[$value['phone_type_id']];
      }  
    }
    
    $template = CRM_Core_Smarty::singleton( );
    $template->assign( 'contactId', $contactId );
    $template->assign( 'phone', $phones );

    echo $content = $template->fetch('CRM/Contact/Page/Inline/Phone.tpl');
    CRM_Utils_System::civiExit( ); 
  }
}

