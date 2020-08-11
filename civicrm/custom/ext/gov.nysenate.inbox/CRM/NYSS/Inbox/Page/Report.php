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
    //Civi::log()->debug(__FUNCTION__, ['$_GET' => $_GET]);

    $dates = [
      'date_relative' => CRM_Utils_Request::retrieve('date_range', 'String'),
      'date_from' => CRM_NYSS_Inbox_BAO_Inbox::formatDate($_GET['date_from'], 'Y-m-d'),
      'date_to' => CRM_NYSS_Inbox_BAO_Inbox::formatDate($_GET['date_to'], 'Y-m-d'),
    ];

    // Find the date parameters being requested. Handle the "-any-" selection as a special case.
    // All others go through the Civi convert function.
    if ($dates['date_relative'] === '') {
      $dates['date_from'] = NULL;
      $dates['date_to'] = NULL;
    }
    else {
      CRM_Contact_BAO_Query::fixDateValues($dates['date_relative'], $dates['date_from'], $dates['date_to']);
    }

    // If the date range was selected, make sure the "high" date ends at 23:59:59.
    if ($dates['date_relative'] === "0") {
      $dates['date_from'] .= ' 00:00:00';
      $dates['date_to'] .= ' 23:59:59';
    }

    // Get the matches.
    $res = CRM_NYSS_Inbox_BAO_Inbox::getUsageReport($dates['date_from'], $dates['date_to']);
    $res['data']['date_range'] = [
      '_date_relative' => $dates['date_relative'],
      '_date_low' => $dates['date_from'],
      '_date_high' => $dates['date_to'],
    ];
    //Civi::log()->debug(__FUNCTION__, ['$dates' => $dates, '$res' => $res]);

    CRM_Utils_JSON::output($res);
  }
}
