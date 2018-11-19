<?php

class CRM_NYSS_Reports_BAO_Query extends CRM_Report_BAO_HookInterface{

  public function alterLogTables(&$reportObj, &$logTables) {
    /*Civi::log()->debug('alterLogTables', array(
      'reportObj' => $reportObj,
      'logTables' => $logTables,
    ));*/

    if (is_a($reportObj, 'CRM_Report_Form_Contact_LoggingSummary')) {
      $dsn = defined('CIVICRM_LOGGING_DSN') ? DB::parseDSN(CIVICRM_LOGGING_DSN) : DB::parseDSN(CIVICRM_DSN);
      $this->loggingDB = $dsn['database'];

      //12173 see also alterReportVar hook
      $logTables['log_civicrm_contact'] = [
        'fk' => 'id',
        'extra_joins' => [
          'table' => 'log_civicrm_entity_tag',
          'join' => "extra_table.entity_id = entity_log_civireport.id
              AND extra_table.entity_table = 'civicrm_contact'
              AND entity_log_civireport.log_conn_id = extra_table.log_conn_id
            INNER JOIN `{$this->loggingDB}`.log_civicrm_group_contact extra_table_2
              ON extra_table_2.contact_id = entity_log_civireport.id
              AND entity_log_civireport.log_conn_id = extra_table_2.log_conn_id
          ",
        ],
      ];
    }

    return NULL;
  }
}
