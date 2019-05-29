<?php
use CRM_Api4_ExtensionUtil as E;

class CRM_Api4_Page_Api4Explorer extends CRM_Core_Page {

  public function run() {
    $loader = new \Civi\Angular\AngularLoader();
    $loader->setModules(['api4Explorer']);
    $loader->setPageName('civicrm/api4');
    $loader->useApp([
      'defaultRoute' => '/explorer',
    ]);
    $loader->load();
    CRM_Utils_System::setTitle('CiviCRM');
    parent::run();
  }

}
