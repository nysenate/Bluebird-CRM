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
class CRM_NYSS_Subscription_Form_Admin extends CRM_Core_Form
{
  /**
   * pre-form data checks
   *
   * @return void
   * @access public
   */
  function preProcess( ) {
    //CRM_Core_Error::debug_var('preProcess REQUEST', $_REQUEST);
    //CRM_Core_Error::debug_var('this', $this);
    //CRM_Core_Error::debug_log_message('CRM_NYSS_Subscription_Form_Manage::preProcess');

    //get form params
    $eid = CRM_Utils_Request::retrieve('emailId', 'Positive', CRM_Core_DAO::$_nullObject, FALSE, NULL, $_REQUEST);

    if ( !$eid ) {
      //check to see if set to submitValues
      if ( !empty($this->_submitValues['eid']) ) {
        $eid = $this->_submitValues['eid'];
      }

      if ( !$eid ) {
        CRM_Core_Error::debug_log_message("No email ID provided.");
        CRM_Utils_System::redirect('http://www.nysenate.gov');
      }
    }
    else {
      $this->_eid = $eid;
    }

    //set page title
    CRM_Utils_System::setTitle( ts('Administer Email Subscriptions') );
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

    if ( empty($this->_eid) ) {
      return;
    }

    //build form elements
    $this->add('hidden', 'eid', $this->_eid, array('id' => 'eid'));

    $email = CRM_Core_DAO::singleValueQuery("
      SELECT email
      FROM civicrm_email
      WHERE id = {$this->_eid}
    ");
    $this->assign('email', $email);

    //get category options
    $mCats = $mailingCats = array();
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

    $this->addButtons(
      array(
        /*array(
          'type' => 'submit',
          'name' => ts('Save Subscription Options'),
        ),*/
        /*array(
          'type' => 'cancel',
          'name' => ts('Cancel'),
        ),*/
      )
    );

    //set defaults; translate opt-outs to present as opt-ins
    $defaults = array();
    $existingOptOuts = CRM_Core_DAO::singleValueQuery("
      SELECT mailing_categories
      FROM civicrm_email
      WHERE id = {$this->_eid}
    ");
    $existingOptOuts = explode(',', $existingOptOuts);
    foreach ( $mCats as $mCatID => $mCatLabel ) {
      if ( !in_array($mCatID, $existingOptOuts) ) {
        $defaults['mailing_categories['.$mCatID.']'] = 1;
      }
    }
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
    //CRM_Core_Error::debug_var('postProcess $_REQUEST', $_REQUEST);

    //get form parameters
    $formParams = $_REQUEST;
    //CRM_Core_Error::debug_var('formParams', $formParams);

    self::_processSubscriptions($formParams);
  }//postProcess

  static function _processSubscriptions($formParams = array()) {
    if ( empty($formParams['eid']) ) {
      $formParams = $_REQUEST;

      //if still empty return now
      if ( empty($formParams['eid']) ) {
        return;
      }
    }

    //if passed in list format, reconstruct for consistency
    if ( empty($formParams['mailing_categories']) && !empty($formParams['mailing_categories_list']) ) {
      $mcs = explode(',', $formParams['mailing_categories_list']);
      foreach ( $mcs as $mcid ) {
        $formParams['mailing_categories'][$mcid] = 1;
      }
    }
    //CRM_Core_Error::debug_var('_processSubscriptions $formParams', $formParams);

    //mailing categories
    $mCats = array();
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
    $unselectedOpts = array();
    foreach ( $mCats as $mCatID => $mCatLabel ) {
      if ( !array_key_exists($mCatID, $formParams['mailing_categories']) ) {
        $unselectedOpts[] = $mCatID;
      }
    }
    if ( !empty($unselectedOpts) ) {
      $mc = "'".implode(',', $unselectedOpts)."'";
    }

    //set values
    $sql = "
      UPDATE civicrm_email
      SET mailing_categories = {$mc}
      WHERE id = {$formParams['eid']}
    ";
    //CRM_Core_Error::debug_var('sql', $sql);
    $dao = CRM_Core_DAO::executeQuery($sql);

    $returnArray = array(
      'emailID' => $formParams['eid'],
      'unsubList' => $mc,
      'subUpdateResponse' => ($dao->is_error) ? FALSE : TRUE,
    );
    //CRM_Core_Error::debug_var('_processSubscriptions $returnArray', $returnArray);

    if ( $formParams['isajax'] ) {
      echo json_encode($returnArray);
      CRM_Utils_System::civiExit();
    }
    else {
      return $returnArray;
    }
  }//_processSubscriptions

}//end class
