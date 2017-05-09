<?php
/**
 * API functions to support inbox polling feature
 */

function civicrm_api3_inbox_assignee_getList($params) {
  $result = array();

  if (!empty($params['input'])) {
    $staffGroupID = civicrm_api3('group', 'getvalue', array('name' => 'Office_Staff', 'return' => 'id'));
    $sql = "
      SELECT c.id, sort_name
      FROM civicrm_contact c
      JOIN civicrm_group_contact gc
        ON c.id = gc.contact_id
        AND gc.group_id = %2
      WHERE sort_name LIKE %1
        AND is_deleted != 1
      ORDER BY sort_name
      LIMIT 10
    ";
    $dao = CRM_Core_DAO::executeQuery($sql, array(
      1 => array($params['input'], 'String', TRUE),
      2 => array($staffGroupID, 'Positive'),
      //third param indicates wildcard search
    ));

    while ($dao->fetch()) {
      $result['values'][] = array(
        'label' => $dao->sort_name,
        'id' => $dao->id,
      );
    }
  }

  /*Civi::log()->debug('civicrm_api3_inbox_assignee_getList', array(
    'params' => $params,
    '$dao' => $dao,
    'result' => $result,
  ));*/
  return $result;
}

/**
 * Adjust Metadata for Get action
 *
 * The metadata is used for setting defaults, documentation, validation, aliases, etc.
 *
 * @param array $params
 */
function _civicrm_api3_inbox_assignee_get_spec(&$params) {
}
