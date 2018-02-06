<?php

/*
 * NYSS functions related to logging
 */
class CRM_NYSS_BAO_Log {
  /**
   * NYSS pull changelog count via ajax
   * Function to get the count of the change log.
   */
  static function getTabCount() {
    $contactId = CRM_Utils_Array::value('contactId', $_POST, NULL);
    //CRM_Core_Error::debug_var('getTabCount contactId', $contactId, TRUE, TRUE, 'logCount');

    if ($contactId && CRM_Core_BAO_Log::useLoggingReport()) {
      //NYSS 6719/11729 call count function directly
      $count = self::getEnhancedContactLogCountReport($contactId);
      echo $count;
    }
    CRM_Utils_System::civiExit( );
  }

  /*
   * alternate method for retrieving contact log report count by silently running the report
   * based on unit test from:
   * https://github.com/civicrm/civicrm-core/blob/master/tests/phpunit/CiviTest/CiviReportTestCase.php
   */
  static function getEnhancedContactLogCountReport($contactID) {
    $reportClass = 'CRM_Report_Form_Contact_LoggingSummary';
    $instanceId = CRM_Core_DAO::singleValueQuery("
      SELECT id
      FROM civicrm_report_instance
      WHERE report_id = 'logging/contact/summary'
        AND is_reserved = 1
      ORDER BY id ASC
      LIMIT 1
    ");
    $inputParams['filters'] = array(
      'altered_contact_id_op' => 'eq',
      'altered_contact_id_value' => $contactID,
      'cid' => $contactID,
      'context' => 'contact',
      'instanceId' => $instanceId,
    );

    $config = CRM_Core_Config::singleton();
    $config->keyDisable = TRUE;
    $controller = new CRM_Core_Controller_Simple($reportClass, ts('Changelog Summary'));
    $tmpReportVal = explode('_', $reportClass);
    $reportName = array_pop($tmpReportVal);
    $reportObj =& $controller->_pages[$reportName];
    $tmpGlobals = array();
    $tmpGlobals['_REQUEST']['force'] = 1;
    $tmpGlobals['_GET'][$config->userFrameworkURLVar] = 'civicrm/placeholder';
    $tmpGlobals['_SERVER']['QUERY_STRING'] = '';
    if (!empty($inputParams['fields'])) {
      $fields = implode(',', $inputParams['fields']);
      $tmpGlobals['_GET']['fld'] = $fields;
      $tmpGlobals['_GET']['ufld'] = 1;
    }
    if (!empty($inputParams['filters'])) {
      foreach ($inputParams['filters'] as $key => $val) {
        $tmpGlobals['_GET'][$key] = $val;
      }
    }
    if (!empty($inputParams['group_bys'])) {
      $groupByFields = implode(' ', $inputParams['group_bys']);
      $tmpGlobals['_GET']['gby'] = $groupByFields;
    }
    CRM_Utils_GlobalStack::singleton()->push($tmpGlobals);
    try {
      $reportObj->storeResultSet();
      $reportObj->buildForm();
      return $reportObj->getVar('_rowsFound');
    }
    catch (Exception $e) {
      CRM_Utils_GlobalStack::singleton()->pop();
      //throw $e;
    }
    CRM_Utils_GlobalStack::singleton()->pop();
    /*Civi::log()->debug('', array(
      '$reportObj' => $reportObj,
      '$reportObj->getVar(_rowsFound)' => $reportObj->getVar('_rowsFound'),
    ));*/
  }
}
