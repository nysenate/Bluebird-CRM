<?php
use CRM_Mosaico_ExtensionUtil as E;

class CRM_Mosaico_Page_TemplateListing extends CRM_Core_Page {
  
  /**
   * Browse all mosaico templates
   */
  public function browse() {
    $controller = new CRM_Core_Controller_Simple(
      'CRM_Mosaico_Form_TemplateFilter',
      ts('Template Filter'),
      NULL,
      FALSE, FALSE, TRUE
    );
    $controller->setEmbedded(TRUE);
    $controller->run();
  }

  public function run() {
    $this->browse();

    parent::run();
  }

}
