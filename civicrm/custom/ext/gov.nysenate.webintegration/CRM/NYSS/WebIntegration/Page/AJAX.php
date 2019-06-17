<?php

class CRM_NYSS_WebIntegration_Page_AJAX extends CRM_Core_Page {
  static function getMessages() {
    //Civi::log()->debug('getUnmatched', array('$_GET' => $_GET));

    $params = CRM_Core_Page_AJAX::defaultSortAndPagerParams();
    //Civi::log()->debug('getUnmatched', array('params' => $params));

    //get unmatched records
    $unmatched = self::getMessageActivities($params);

    CRM_Utils_JSON::output($unmatched);
  }

  /**
   * @param $params
   * @return array
   */
  static function getMessageActivities($params) {
    // Format the params.
    $params['offset'] = ($params['page'] - 1) * $params['rp'];
    $params['rowCount'] = $params['rp'];
    $params['sort'] = CRM_Utils_Array::value('sortBy', $params);
    $orderBy = (!empty($params['sort'])) ? $params['sort'] : 'date DESC';

    $actDirect = CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_type_id', 'website_direct_message');
    $actContextual = CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_type_id', 'website_contextual_message');

    //get direct/contextual msg activities
    $sql = "
      SELECT SQL_CALC_FOUND_ROWS a.id aid, c.id cid, a.activity_type_id type, a.details note, 
        a.activity_date_time date, c.sort_name contact, di.county_50 county
      FROM civicrm_activity a
      JOIN civicrm_activity_contact ac
        ON a.id = ac.activity_id
        AND ac.record_type_id = 3
      JOIN civicrm_contact c
        ON ac.contact_id = c.id
        AND c.is_deleted = 0
      LEFT JOIN civicrm_address ad 
        ON c.id = ad.contact_id
        AND ad.is_primary = 1
      LEFT JOIN civicrm_value_district_information_7 di 
        ON ad.id = di.entity_id
      WHERE a.activity_type_id IN (%1, %2)
      ORDER BY {$orderBy}
      LIMIT {$params['rowCount']}
      OFFSET {$params['offset']}
    ";
    $dao = CRM_Core_DAO::executeQuery($sql, [
      1 => [$actDirect, 'Positive'],
      2 => [$actContextual, 'Positive'],
    ]);

    // Add total.
    $params['total'] = CRM_Core_DAO::singleValueQuery('SELECT FOUND_ROWS();');

    $msgs = array();
    if ($dao->N) {
      while ($dao->fetch()) {
        $msg = array();
        $msg['DT_RowId'] = $dao->aid;
        $msg['DT_RowClass'] = 'crm-entity';
        $msg['DT_RowAttr'] = [];
        $msg['DT_RowAttr']['data-entity'] = 'note';
        $msg['DT_RowAttr']['data-id'] = $dao->aid;

        switch ($dao->type) {
          case $actContextual:
            $msg['type'] = '<span class="msg-type">Contextual</span>';
            break;
          case $actDirect:
            $msg['type'] = '<span class="msg-type">Direct</span>';
            break;
          default:
        }

        $contactLink = CRM_Utils_System::url('civicrm/contact/view',
          "reset=1&cid={$dao->cid}");

        $msg['id'] = $dao->aid;
        $msg['contact_id'] = $dao->cid;
        $msg['contact'] = "<a href='{$contactLink}'>{$dao->contact}</a>";
        $msg['date'] = date('m/d/Y', strtotime($dao->date));
        $msg['county'] = (!empty($dao->county)) ? CRM_NYSS_Resources_Resources::getCountyCodes($dao->county) : '';

        /*$activity['source_contact_name'] = $srcTypeImage . CRM_Utils_System::href($values['source_contact_name'],
            'civicrm/contact/view', "reset=1&cid={$values['source_contact_id']}");*/

        // build links
        $note = nl2br($dao->note);
        $activityView = CRM_Utils_System::url('civicrm/activity',
          "atype={$dao->type}&action=view&reset=1&id={$dao->aid}&cid={$dao->cid}&context=dashlet");
        $links = array(
          'note' => "<a href='#' id='view-msg-{$dao->aid}' class='action-item crm-hover-button view-msg'>View Message</a>",
          'message' => "<div title='Message Text' style='display:none;' id='msg-{$dao->aid}'>{$note}</div>",
          //'activity_create' => "<a href='#' id='create-activity-{$dao->id}' contact_id='{$dao->entity_id}' class='action-item crm-hover-button create-activity'>Create Activity</a>",
          'activity_view' => "<a href='$activityView' id='activity-{$dao->aid}' contact_id='{$dao->cid}' class='action-item crm-hover-button view-activity crm-popup'>View Activity</a>",
        );

        $msg['links'] = implode(' ', $links);

        array_push($msgs, $msg);
      }
    }

    $msgsDT = array();
    $msgsDT['data'] = $msgs;
    $msgsDT['recordsTotal'] = $params['total'];
    $msgsDT['recordsFiltered'] = $params['total'];

    //Civi::log()->debug('getUnmatchedMessages', array($msgsDT));
    return $msgsDT;
  }
}
