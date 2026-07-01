<?php
declare(strict_types = 1);

use CRM_NYSS_CaseloadDash_ExtensionUtil as E;

class CRM_NYSS_CaseloadDash_Page_CaseloadDashboardPage extends CRM_Core_Page {

  public function run() {
    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    CRM_Utils_System::setTitle(E::ts('Caseload Dashboard Page'), '');
    // Used to hide h1.page-title on the dashboard page.
    CRM_Core_Resources::singleton()->addScript(
      'document.body.classList.add("page-nyss-caseload-dashboard");'
    );

    Civi::service('angularjs.loader')->addModules(['crmNyssCaseloadDash','afsearchNYSSCaseloadOverviewTotals','afsearchNYSSCaseloadOverviewDetails', 'afsearchNYSSCaseloadCaseManagerTotals']);
    parent::run();
  }

}
