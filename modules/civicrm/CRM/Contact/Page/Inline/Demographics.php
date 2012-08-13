<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id$
 *
 */

/**
 * Dummy page for details of demographics 
 *
 */
class CRM_Contact_Page_Inline_Demographics {

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
    $contactId = CRM_Utils_Request::retrieve('cid', 'Positive', CRM_Core_DAO::$_nullObject, TRUE, NULL, $_REQUEST);

    $params = array(
      'id' => $contactId
    );

    $defaults = array();
    CRM_Contact_BAO_Contact::getValues( $params, $defaults );

    if (CRM_Utils_Array::value('gender_id', $defaults)) {
      $gender = CRM_Core_PseudoConstant::gender();
      $defaults['gender_display'] = $gender[CRM_Utils_Array::value('gender_id', $defaults)];
    }

    $template = CRM_Core_Smarty::singleton();
    $template->assign('contactId', $contactId);
    $template->assign($defaults);
    
    // check logged in user permission
    $page = new CRM_Core_Page();
    CRM_Contact_Page_View::checkUserPermission($page, $contactId);
    $template->append($page);
 
    //for birthdate format with respect to birth format set
    $template->assign('birthDateViewFormat', CRM_Utils_Array::value('qfMapping', CRM_Utils_Date::checkBirthDateFormat()));

    echo $content = $template->fetch('CRM/Contact/Page/Inline/Demographics.tpl');
    CRM_Utils_System::civiExit();
  }
}

