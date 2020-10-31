<?php

use Civi\Angular\AngularLoader as AngularLoader;

/**
 * Dashboard Tabs Page.
 */
class CRM_Civicase_Page_DashboardTabs extends CRM_Contact_Page_DashBoard {

  /**
   * Adds the CRM dashboard's CSS file and Dashboard page content.
   */
  public function run() {
    $loader = new AngularLoader();

    $loader->setPageName('civicrm/dashboard');
    $loader->setModules(['crmApp', 'civicase']);
    $loader->load();

    CRM_Core_Resources::singleton()
      ->addScriptFile('uk.co.compucorp.civicase', 'packages/moment.min.js');

    CRM_Core_Resources::singleton()
      ->addStyleFile('uk.co.compucorp.civicase', 'css/civicase.min.css');

    return parent::run();
  }

}
