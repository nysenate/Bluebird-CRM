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
    //CRM_Core_Error::debug_var('this', $this);

    //get form params
    $queueID = CRM_Utils_Request::retrieve('eq', 'Positive', CRM_Core_DAO::$_nullObject, FALSE, NULL, $_REQUEST);
    $cs = CRM_Utils_Request::retrieve('cs', 'String', CRM_Core_DAO::$_nullObject, FALSE, NULL, $_REQUEST);

    if ( !$queueID || !$cs ) {
      //check to see if set to submitValues
      if ( !empty($this->_submitValues['queueID']) ) {
        $queueID = $this->_submitValues['queueID'];
      }
      if ( !empty($this->_submitValues['cs']) ) {
        $cs = $this->_submitValues['cs'];
      }

      if ( !$queueID || !$cs ) {
        CRM_Core_Error::debug_log_message("No event queue ID or checksum set.");
        CRM_Utils_System::redirect('http://www.nysenate.gov');
      }
    }
    else {
      $this->_queueID = $queueID;
      $this->_cs = $cs;
    }

    //get contact details from event queue and store in object
    $contact = array();
    $dao = CRM_Core_DAO::executeQuery("
      SELECT eq.email_id, eq.contact_id, c.display_name, e.email, e.on_hold, e.mailing_categories
      FROM civicrm_mailing_event_queue eq
      JOIN civicrm_contact c
        ON eq.contact_id = c.id
      JOIN civicrm_email e
        ON eq.email_id = e.id
      WHERE eq.id = {$queueID}
    ");
    if ( $dao->N ) {
      while ( $dao->fetch() ) {
        $contact = array(
          'email_id' => $dao->email_id,
          'contact_id' => $dao->contact_id,
          'display_name' => $dao->display_name,
          'email' => $dao->email,
          'on_hold' => $dao->on_hold,
          'mailing_categories' => $dao->mailing_categories,
        );
      }
    }

    //if contact could not be retrieved from queue ID, exit
    if ( empty($contact) ) {
      CRM_Core_Error::debug_log_message("Unable to locate contact for subscription management tool using event queue: {$queueID}");
      CRM_Utils_System::redirect('http://www.nysenate.gov');
    }
    else {
      $this->_contact = $contact;
    }

    //verify checksum
    if ( !CRM_Contact_BAO_Contact_Utils::validChecksum($contact['contact_id'], $cs) ) {
      CRM_Core_Error::debug_var('Failed attempt to validate checksum in email subscription tool.', $contact);
      CRM_Utils_System::redirect('http://www.nysenate.gov');
    }

    //set page title
    CRM_Utils_System::setTitle( ts('Mass Email Subscriptions') );
  }

  /**
   * Build the form
   *
   * @access public
   * @return void
   */
  public function buildQuickForm() {
    //CRM_Core_Error::debug_var('_contact', $this->_contact);

    //disable BB header
    $this->assign('disableBBheader', 1);

    //get senator name
    $bbconfig = get_bluebird_instance_config();
    $this->assign('senatorFormal', $bbconfig['senator.name.formal']);

    //set contact to template
    $this->assign('contact', $this->_contact);

    //build form elements
    $this->add('hidden', 'cs', $this->_cs, array('id' => 'cs'));
    $this->add('hidden', 'queueID', $this->_queueID, array('id' => 'queueID'));
    $this->add('hidden', 'cid', $this->_contact['contact_id'], array('id' => 'cid'));
    $this->add('hidden', 'emailID', $this->_contact['email_id'], array('id' => 'emailID'));

    //get category options
    $mCats = array();
    $opts = CRM_Core_DAO::executeQuery("
      SELECT ov.label, ov.value
      FROM civicrm_option_value ov
      JOIN civicrm_option_group og
        ON ov.option_group_id = og.id
        AND og.name = 'mailing_categories'
      ORDER BY ov.label
    ");
    while ( $opts->fetch() ) {
      $mCats[$opts->value] = $opts->label;
      $mailingCats[] = $this->createElement('checkbox', $opts->value, NULL, $opts->label);
      $this->addGroup($mailingCats, 'mailing_categories', ts('Mailing Categories'), '<br />');
    }

    $this->addElement('checkbox', 'opt_out', ts('Opt-Out'));

    $this->addButtons(
      array(
        array(
          'type' => 'submit',
          'name' => ts('Save Subscription Options'),
        ),
        array(
          'type' => 'back',
          'name' => ts('Cancel')
        ),
      )
    );

    //set defaults
    $defaults = array();
    foreach ( explode(',', $this->_contact['mailing_categories']) as $mCatID ) {
      $defaults['mailing_categories['.$mCatID.']'] = 1;
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
    //CRM_Core_Error::debug_var('this', $this);

    //get form parameters and create sql criteria
    $formParams = $this->controller->exportValues( $this->_name );
    //CRM_Core_Error::debug_var('formParams', $formParams);

    //validate checksum again
    if ( !CRM_Contact_BAO_Contact_Utils::validChecksum($formParams['cid'], $formParams['cs']) ) {
      CRM_Core_Error::debug_var('Failed attempt to validate checksum when storing subscription options. $formParams', $formParams);
      CRM_Utils_System::redirect('http://www.nysenate.gov');
    }

    //mailing categories
    $mc = 'null';
    if ( !empty($formParams['mailing_categories']) ) {
      $mc = "'".implode(',', array_keys($formParams['mailing_categories']))."'";
    }

    //opt out
    $opt = 0;
    $hold_date = 'null';
    if (!empty($formParams['opt_out'])) {
      $opt = 1;
      $hold_date = "'".date('Y-m-d h:i:s')."'";
    }

    //set values
    CRM_Core_DAO::executeQuery("
      UPDATE civicrm_email
      SET mailing_categories = {$mc}, on_hold = {$opt}, hold_date = {$hold_date}
      WHERE id = {$formParams['emailID']}
    ");

    //now redirect
    //$url = CRM_Utils_System::url('civicrm/nyss/subscription/view', "eq={$formParams['queueID']}&cs={$formParams['cs']}");
    $url = "http://pubfiles.nysenate.gov/{$env[0]}/{$bbconfig['shortname']}/subscription/view/{$formParams['queueID']}/{$formParams['cs']}";
    CRM_Utils_System::redirect($url);

  }//postProcess

}//end class
