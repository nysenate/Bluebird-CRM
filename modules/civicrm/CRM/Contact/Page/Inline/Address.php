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
 * Dummy page for details of address 
 *
 */
class CRM_Contact_Page_Inline_Address {

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
    $locBlockNo = CRM_Utils_Request::retrieve('locno', 'Positive', $this, TRUE, NULL, $_REQUEST);
    $addressId = CRM_Utils_Request::retrieve('aid', 'Positive', $thisi, FALSE, NULL, $_REQUEST);

    $address = array();
    if ( $addressId > 0 ) {
      $locationTypes = CRM_Core_PseudoConstant::locationDisplayName();

      $entityBlock = array('id' => $addressId);
      $address = CRM_Core_BAO_Address::getValues($entityBlock, FALSE, 'id');
      if (!empty($address)) {
        foreach ($address as $key =>& $value) {
          $value['location_type'] = $locationTypes[$value['location_type_id']];
        }
      }
    }

    // we just need current address block
    $currentAddressBlock['address'][$locBlockNo] = array_pop( $address ); 
    
    $template = CRM_Core_Smarty::singleton();
    if ( !empty( $currentAddressBlock['address'][$locBlockNo] ) ) {
      // get contact name of shared contact names
      $sharedAddresses = array();
      $shareAddressContactNames = CRM_Contact_BAO_Contact_Utils::getAddressShareContactNames($currentAddressBlock['address']);
      foreach ($currentAddressBlock['address'] as $key => $addressValue) {
        if (CRM_Utils_Array::value('master_id', $addressValue) &&
          !$shareAddressContactNames[$addressValue['master_id']]['is_deleted']
        ) {
          $sharedAddresses[$key]['shared_address_display'] = array(
            'address' => $addressValue['display'],
            'name' => $shareAddressContactNames[$addressValue['master_id']]['name'],
          );
        }
      }

      // add custom data of type address
      $page = new CRM_Core_Page();
      $groupTree = CRM_Core_BAO_CustomGroup::getTree( 'Address',
        $page, $currentAddressBlock['address'][$locBlockNo]['id']
      );

      // we setting the prefix to dnc_ below so that we don't overwrite smarty's grouptree var.
      $currentAddressBlock['address'][$locBlockNo]['custom'] = CRM_Core_BAO_CustomGroup::buildCustomDataView( $page, $groupTree, FALSE, NULL, "dnc_");
      $page->assign("dnc_viewCustomData", NULL);
    
      $template->assign('add', $currentAddressBlock['address'][$locBlockNo]);
      $template->assign('sharedAddresses', $sharedAddresses);
    }

    $template->assign('contactId', $contactId);
    $template->assign('locationIndex', $locBlockNo);
    $template->assign('addressId', $addressId);

    $appendBlockIndex = CRM_Core_BAO_Address::getAddressCount($contactId);

    // check if we are adding new address, then only append add link 
    if ( $appendBlockIndex == $locBlockNo ) {
      if ( $appendBlockIndex ) {
        $appendBlockIndex++;
      }
    }
    else {
      $appendBlockIndex = 0; 
    }
    $template->assign('appendBlockIndex', $appendBlockIndex);
    
    // check logged in user permission
    $page = new CRM_Core_Page();
    CRM_Contact_Page_View::checkUserPermission($page, $contactId);
    $template->append($page);

    echo $content = $template->fetch('CRM/Contact/Page/Inline/Address.tpl');
    CRM_Utils_System::civiExit();
  }
}

