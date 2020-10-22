<?php

// use CRM_Flexmailer_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Flexmailer_Form_FlexmailerAdmin extends CRM_Admin_Form_Setting {

  protected $_settings = array(
    'flexmailer_traditional' => 'Flexmailer Preferences',
  );

  /**
   * Build the form object.
   */
  public function buildQuickForm() {
    parent::buildQuickForm();
  }

}
