<?php

/**
 * Nyss.cleandates API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_nyss_cleandates_spec(&$spec) {
  $spec['dryrun'] = [
    'title' => 'Dry-run',
    'type' => CRM_Utils_Type::T_BOOLEAN,
    'api.required' => TRUE,
    'api.default' => TRUE,
  ];
}

/**
 * Nyss.cleandates API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_nyss_cleandates($params) {
  $result = [
    'dryrun_nullable' => [],
    'non_nullable' => [],
    'dryrun_fixable' => [],
  ];

  //get all date fields
  $dao = CRM_Core_DAO::executeQuery("
    SELECT *
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = %1
      AND DATA_TYPE IN ('timestamp', 'datetime')
  ", [
    1 => [DB::parseDSN(CRM_Core_Config::singleton()->dsn)['database'], 'String'],
  ]);

  $idExceptions = ['redist_report_note_cache'];

  while ($dao->fetch()) {
    $select = !in_array($dao->TABLE_NAME, $idExceptions) ? "id, {$dao->COLUMN_NAME}" : $dao->COLUMN_NAME;

    if ($dao->IS_NULLABLE == 'YES') {
      if ($params['dryrun']) {
        $rows = CRM_Core_DAO::executeQuery("
          SELECT {$select}
          FROM {$dao->TABLE_NAME}
          WHERE {$dao->COLUMN_NAME} LIKE '0000-00-00%'
        ");

        while ($rows->fetch()) {
          $result['dryrun_nullable'][] = [
            'table' => $dao->TABLE_NAME,
            'column' => $dao->COLUMN_NAME,
            'row_id' => $rows->id,
          ];
        }
      }
      else {
        CRM_Core_DAO::executeQuery("
          UPDATE {$dao->TABLE_NAME}
          SET {$dao->COLUMN_NAME} = NULL
          WHERE {$dao->COLUMN_NAME} LIKE '0000-00-00%'
        ");
      }
    }
    else {
      //handle non-nullable where we can and document the rest
      $select = ($dao->TABLE_NAME == 'civicrm_note') ? $select.', subject' : $select;

      $rows = CRM_Core_DAO::executeQuery("
        SELECT {$select}
        FROM {$dao->TABLE_NAME}
        WHERE {$dao->COLUMN_NAME} LIKE '0000-00-00%'
      ");

      while ($rows->fetch()) {
        //Civi::log()->debug(__METHOD__, ['rows' => $rows]);

        if ($dao->TABLE_NAME == 'civicrm_note' && str_contains($rows->subject, 'OMIS')) {
          if ($params['dryrun']) {
            $result['dryrun_fixable'][] = [
              'table' => $dao->TABLE_NAME,
              'column' => $dao->COLUMN_NAME,
              'row_id' => $rows->id,
            ];
          }
          else {
            CRM_Core_DAO::executeQuery("
              UPDATE {$dao->TABLE_NAME}
              SET {$dao->COLUMN_NAME} = '2010-01-01'
              WHERE {$dao->COLUMN_NAME} LIKE '0000-00-00%'
            ");
          }
        }
        else {
          $result['non_nullable'][] = [
            'table' => $dao->TABLE_NAME,
            'column' => $dao->COLUMN_NAME,
            'row_id' => $rows->id,
          ];
        }
      }
    }
  }

  return civicrm_api3_create_success(['results' => $result], $params, 'Nyss', 'cleandates');
}
