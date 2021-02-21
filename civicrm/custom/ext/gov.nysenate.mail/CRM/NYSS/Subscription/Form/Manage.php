<?php

/*
 * NYSS 5581
 * Subscription Management
 * Created: April, 2014
 * Author:  Brian Shaughnessy
 */

/**
 * This class generates form components
 *
 */
class CRM_NYSS_Subscription_Form_Manage extends CRM_Core_Form
{
  /**
   * pre-form data checks
   *
   * @return void
   * @access public
   */
  function preProcess( ) {
    //CRM_Core_Error::debug_var('REQUEST', $_REQUEST);
    //CRM_Core_Error::debug_var('this', $this);
    //CRM_Core_Error::debug_log_message('CRM_NYSS_Subscription_Form_Manage::preProcess');

    //get form params
    $eq = CRM_Utils_Request::retrieve('eq', 'Positive', CRM_Core_DAO::$_nullObject, FALSE, NULL, $_REQUEST);
    $cs = CRM_Utils_Request::retrieve('cs', 'String', CRM_Core_DAO::$_nullObject, FALSE, NULL, $_REQUEST);

    if ( !$eq || !$cs ) {
      //check to see if set to submitValues
      if ( !empty($this->_submitValues['eq']) ) {
        $eq = $this->_submitValues['eq'];
      }
      if ( !empty($this->_submitValues['cs']) ) {
        $cs = $this->_submitValues['cs'];
      }

      if ( !$eq || !$cs ) {
        CRM_Core_Error::debug_log_message("No event queue ID or checksum set.");
        CRM_Utils_System::redirect('http://www.nysenate.gov');
      }
    }
    else {
      $this->_eq = $eq;
      $this->_cs = $cs;
    }

    //get contact details from event queue and store in object
    $contact = [];
    $dao = CRM_Core_DAO::executeQuery("
      SELECT eq.email_id, eq.contact_id, c.display_name, e.email, e.on_hold, e.mailing_categories
      FROM civicrm_mailing_event_queue eq
      JOIN civicrm_contact c
        ON eq.contact_id = c.id
      JOIN civicrm_email e
        ON eq.email_id = e.id
      WHERE eq.id = {$eq}
    ");
    if ( $dao->N ) {
      while ( $dao->fetch() ) {
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

    //if contact could not be retrieved from queue ID, exit
    if ( empty($contact) ) {
      CRM_Core_Error::debug_log_message("Unable to locate contact for subscription management tool using event queue: {$eq}");
      CRM_Utils_System::redirect('http://www.nysenate.gov');
    }
    else {
      $this->_contact = $contact;
    }

    $bbconfig = get_bluebird_instance_config();

    //verify checksum
    if ( !CRM_Contact_BAO_Contact_Utils::validChecksum($contact['contact_id'], $cs) ) {
      CRM_Core_Error::debug_var('Failed attempt to validate checksum in email subscription tool.', $contact);
      $url = "{$bbconfig['public.url.base']}/{$bbconfig['envname']}/{$bbconfig['shortname']}/subscription/expired";
      CRM_Utils_System::redirect($url);
    }

    //set page title
    CRM_Utils_System::setTitle( ts('Manage Email Subscriptions') );

    //alter form action to use pubfiles version
    $action = "{$bbconfig['public.url.base']}/{$bbconfig['envname']}/{$bbconfig['shortname']}/subscription/manage";
    $this->_attributes['action'] = $action;
    $this->_attributes['method'] = 'get';

    //CRM_Core_Error::debug_var('$this->_attributes', $this->_attributes);
    //CRM_Core_Error::debug_var('action', $action);
    //CRM_Core_Error::debug_var('this', $this);
  }

  /**
   * Build the form
   *
   * @access public
   * @return void
   */
  public function buildQuickForm() {
    //CRM_Core_Error::debug_var('this', $this);
    //CRM_Core_Error::debug_var('_contact', $this->_contact);

    //disable BB header
    $this->assign('disableBBheader', 1);

    //get senator name
    $bbconfig = get_bluebird_instance_config();
    $this->assign('senatorFormal', $bbconfig['senator.name.formal']);

    //set contact to template
    $this->assign('contact', $this->_contact);

    //build form elements
    $this->add('hidden', 'cs', $this->_cs, ['id' => 'cs']);
    $this->add('hidden', 'eq', $this->_eq, ['id' => 'eq']);
    $this->add('hidden', 'cid', $this->_contact['contact_id'], ['id' => 'cid']);
    $this->add('hidden', 'emailID', $this->_contact['email_id'], ['id' => 'emailID']);

    //get category options
    $mCats = [];
    $opts = CRM_Core_DAO::executeQuery("
      SELECT ov.label, ov.value
      FROM civicrm_option_value ov
      JOIN civicrm_option_group og
        ON ov.option_group_id = og.id
        AND og.name = 'mailing_categories'
      ORDER BY ov.weight
    ");
    while ( $opts->fetch() ) {
      $mCats[$opts->value] = $opts->label;
      $mailingCats[] = $this->createElement('checkbox', $opts->value, NULL, $opts->label);
      $this->addGroup($mailingCats, 'mailing_categories', ts('Mailing Categories'), '<br />');
    }

    $this->addElement('checkbox', 'opt_out', ts('Opt-Out'));

    //don't use qfKey
    $this->removeElement('qfKey');

    $this->addButtons(
      [
        [
          'type' => 'submit',
          'name' => ts('Save Subscription Settings'),
        ],
      ]
    );

    //set defaults; translate opt-outs to present as opt-ins
    $defaults = [];
    $existingOptOuts = explode(',', $this->_contact['mailing_categories']);
    foreach ( $mCats as $mCatID => $mCatLabel ) {
      if ( !in_array($mCatID, $existingOptOuts) ) {
        $defaults['mailing_categories['.$mCatID.']'] = 1;
      }
    }
    $defaults['opt_out'] = $this->_contact['on_hold'];
    //CRM_Core_Error::debug_var('$defaults', $defaults);
    $this->setDefaults($defaults);
  }

  /**
   * process the form after the input has been submitted and validated
   *
   * @access public
   * @return None
   */
  public function postProcess() {
    //get form parameters and create sql criteria
    $formParams = $_REQUEST;
    //CRM_Core_Error::debug_var('formParams', $formParams);

    //validate checksum again
    if (!CRM_Contact_BAO_Contact_Utils::validChecksum($formParams['cid'], $formParams['cs'])) {
      CRM_Core_Error::debug_var('Failed attempt to validate checksum when storing subscription options. $formParams', $formParams);
      CRM_Utils_System::redirect('http://www.nysenate.gov');
    }

    //mailing categories
    $mCats = [];
    $mc = 'null';
    $opts = CRM_Core_DAO::executeQuery("
      SELECT ov.label, ov.value
      FROM civicrm_option_value ov
      JOIN civicrm_option_group og
        ON ov.option_group_id = og.id
        AND og.name = 'mailing_categories'
      ORDER BY ov.weight
    ");
    while ( $opts->fetch() ) {
      $mCats[$opts->value] = $opts->label;
    }

    //translate opt-outs to present as opt-ins
    $unselectedOpts = [];
    foreach ($mCats as $mCatID => $mCatLabel) {
      if (!array_key_exists($mCatID, $formParams['mailing_categories'])) {
        $unselectedOpts[] = $mCatID;
      }
    }
    if (!empty($unselectedOpts)) {
      $mc = "'".implode(',', $unselectedOpts)."'";
    }

    //opt out
    $opt = 0;
    $hold_date = 'null';
    if (!empty($formParams['opt_out'])) {
      $opt = 2;
      $hold_date = "'".date('Y-m-d h:i:s')."'";

      self::storeUnsubscribe($formParams['eq']);
    }

    //set values
    $sql = "
      UPDATE civicrm_email
      SET mailing_categories = {$mc}, on_hold = {$opt}, hold_date = {$hold_date}
      WHERE id = {$formParams['emailID']}
    ";
    //CRM_Core_Error::debug_var('sql', $sql);
    CRM_Core_DAO::executeQuery($sql);

    //now redirect
    $bbconfig = get_bluebird_instance_config();
    //$url = CRM_Utils_System::url('civicrm/nyss/subscription/view', "eq={$formParams['eq']}&cs={$formParams['cs']}");
    $url = "{$bbconfig['public.url.base']}/{$bbconfig['envname']}/{$bbconfig['shortname']}/subscription/view/{$formParams['eq']}/{$formParams['cs']}";
    //CRM_Core_Error::debug_var('$url', $url);
    CRM_Utils_System::redirect($url);
  }//postProcess

  function storeUnsubscribe($eqId) {
    try {
      CRM_Core_DAO::executeQuery("
      INSERT IGNORE INTO civicrm_mailing_event_unsubscribe
      (event_queue_id, org_unsubscribe, time_stamp)
      VALUES
      (%1, 1, %2)
    ", [
        1 => [$eqId, 'Positive'],
        2 => [date('YmdHis'), 'Timestamp'],
      ]);
    }
    catch (CiviCRM_API3_Exception $e) {}
  }
}//end class
