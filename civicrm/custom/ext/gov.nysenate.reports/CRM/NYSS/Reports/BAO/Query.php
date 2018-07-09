<?php

class CRM_NYSS_Reports_BAO_Query extends CRM_Report_BAO_HookInterface{

  public function alterLogTables(&$reportObj, &$logTables) {
    /*Civi::log()->debug('alterLogTables', array(
      'reportObj' => $reportObj,
      'logTables' => $logTables,
    ));*/

    if (is_a($reportObj, 'CRM_Report_Form_Contact_LoggingSummary')) {
      $logTables['log_civicrm_contact'] = [
        'fk' => 'id',
        'joins' => [
          'table' => 'log_civicrm_entity_tag',
          'join' => "fk_table.entity_id = entity_log_civireport.id AND fk_table.entity_table = 'civicrm_contact' AND entity_log_civireport.log_conn_id != fk_table.log_conn_id",
        ],
      ];
    }

    return NULL;
  }
}
