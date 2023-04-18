<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
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
 * @copyright CiviCRM LLC (c) 2004-2013
 * $Id$
 *
 */

/**
 * Main page for Cases dashlet
 *
 */
class CRM_NYSS_Subscription_Page_View extends CRM_Core_Page {

  /**
   * View subscriptions
   *
   * @return none
   *
   * @access public
   */
  function run() {
    CRM_Utils_System::setTitle('Email Subscriptions');

    //disable BB header
    $this->assign('disableBBheader', 1);

    //get senator name
    $bbconfig = get_bluebird_instance_config();
    $this->assign('senatorFormal', $bbconfig['senator.name.formal']);

    $queueID = CRM_Utils_Request::retrieve('eq', 'Positive');
    $cs = CRM_Utils_Request::retrieve('cs', 'String');

    //get contact details from event queue and store in object
    $contact = [];
    $dao = CRM_Core_DAO::executeQuery("
      SELECT eq.email_id, eq.contact_id, c.display_name, e.email, e.on_hold, e.mailing_categories
      FROM civicrm_mailing_event_queue eq
      JOIN civicrm_contact c
        ON eq.contact_id = c.id
      JOIN civicrm_email e
        ON eq.email_id = e.id
      WHERE eq.id = {$queueID}
    ");
    if ($dao->N) {
      while ($dao->fetch()) {
        $contact = [
          'email_id' => $dao->email_id,
          'contact_id' => $dao->contact_id,
          'display_name' => $dao->display_name,
          'email' => $dao->email,
          'on_hold' => $dao->on_hold,
          'mailing_categories' => $dao->mailing_categories,
        ];
      }
    }

    //convert mailing categories; display categories IN and OUT
    $mCats = [];
    $opts = CRM_Core_DAO::executeQuery("
      SELECT ov.label, ov.value
      FROM civicrm_option_value ov
      JOIN civicrm_option_group og
        ON ov.option_group_id = og.id
        AND og.name = 'mailing_categories'
      ORDER BY ov.weight
    ");
    while ($opts->fetch()) {
      $mCats[$opts->value] = $opts->label;
    }

    $unselectedOpts = explode(',', $contact['mailing_categories']);

    foreach ($mCats as $mCatID => $mCatLabel) {
      if (in_array($mCatID, $unselectedOpts)) {
        $contact['opt_unselected'][] = $mCatLabel;
      }
      else {
        $contact['opt_selected'][] = $mCatLabel;
      }
    }

    $contact['opt_unselected_list'] = implode(', ', $contact['opt_unselected'] ?? []);
    $contact['opt_selected_list'] = implode(', ', $contact['opt_selected'] ?? []);

    //convert on_hold
    $contact['opt_out'] = (!empty($contact['on_hold'])) ? 'Yes' : 'No';

    //verify checksum
    if (!CRM_Contact_BAO_Contact_Utils::validChecksum($contact['contact_id'], $cs)) {
      CRM_Core_Error::debug_var('Failed attempt to validate checksum in email subscription page.', $contact);
      CRM_Utils_System::redirect('http://www.nysenate.gov');
    }

    //CRM_Core_Error::debug_var('subscription view contact', $contact);
    $this->assign('contact', $contact);

    parent::run();
  }
}
