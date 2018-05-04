<?php

class CRM_NYSS_Inbox_Page_Report extends CRM_Core_Page {
  public function run() {
    CRM_NYSS_Inbox_BAO_Inbox::addResources('report');

    $this->assign('title', 'Inbox Reports');
    $this->assign('list', 'report');

    $controller = new CRM_Core_Controller_Simple('CRM_NYSS_Inbox_Form_DateFilter', 'Select Date Range');
    $controller->setEmbedded(TRUE);
    $controller->run();

    parent::run();
  }

  static function getFiltered() {

    // Find the date parameters being requested
    $dateParams = [
      '_date_relative' => $_GET['date_range'],
      '_date_low' => $_GET['date_from'],
      '_date_high' => $_GET['date_to'],
    ];
    CRM_Contact_BAO_Query::convertFormValues($dateParams);

    // If the date range was selected, make sure the "high" date ends at 23:59:59.
    if ($dateParams['_date_relative'] === "0") {
      $dateParams['_date_high'] .= ' 23:59:59';
    }
    // Expected range format is 'Y-m-d H:i:s'.
    foreach (['_date_low', '_date_high'] as $k) {
      $dateParams[$k] = date('Y-m-d H:i:s', strtotime($dateParams[$k]));
    }

    // Get the matches.
    $res = CRM_NYSS_Inbox_BAO_Inbox::getUsageReport($dateParams['_date_low'], $dateParams['_date_high']);
    $res['data']['date_range'] = $dateParams;

    CRM_Utils_JSON::output($res);
  }
}
