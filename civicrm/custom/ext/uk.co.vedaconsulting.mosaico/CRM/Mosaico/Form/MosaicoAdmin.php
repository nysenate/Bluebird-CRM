<?php

use CRM_Mosaico_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Mosaico_Form_MosaicoAdmin extends CRM_Admin_Form_Setting {

  protected $_settings = [
    'mosaico_layout' => 'Mosaico Preferences',
    'mosaico_graphics' => 'Mosaico Preferences',
    'mosaico_custom_templates_dir' => 'Mosaico Custom Templates Directory',
    'mosaico_custom_templates_url' => 'Mosaico Custom Templates URL'
  ];

  /**
   * Build the form object.
   */
  public function buildQuickForm() {
    parent::buildQuickForm();
  }

}
