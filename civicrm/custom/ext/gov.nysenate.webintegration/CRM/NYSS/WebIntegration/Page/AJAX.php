<?php

class CRM_NYSS_WebIntegration_Page_AJAX extends CRM_Core_Page {
  static function getUnmatched() {
    //Civi::log()->debug('getUnmatched', array('$_GET' => $_GET));

    $params = CRM_Core_Page_AJAX::defaultSortAndPagerParams();
    //Civi::log()->debug('getUnmatched', array('params' => $params));

    //get unmatched records
    $unmatched = self::getUnmatchedMessages($params);

    CRM_Utils_JSON::output($unmatched);
  }

  /**
   * @param $params
   * @return array
   */
  static function getUnmatchedMessages($params) {
    // Format the params.
    $params['offset'] = ($params['page'] - 1) * $params['rp'];
    $params['rowCount'] = $params['rp'];
    $params['sort'] = CRM_Utils_Array::value('sortBy', $params);
    $orderBy = (!empty($params['sort'])) ? $params['sort'] : 'date DESC';

    //get direct/contextual msgs with no matching activity
    $sql = "
      SELECT n.id, n.entity_id, n.entity_table type, n.note, n.modified_date date, c.sort_name contact, di.county_50 county
      FROM civicrm_note n
      LEFT JOIN nyss_web_msg_activity ma 
        ON n.id = ma.note_id
      LEFT JOIN civicrm_contact c 
        ON n.entity_id = c.id
      LEFT JOIN civicrm_address a 
        ON n.entity_id = a.contact_id
        AND a.is_primary = 1
      LEFT JOIN civicrm_value_district_information_7 di 
        ON a.id = di.entity_id
      WHERE ma.id IS NULL
        AND n.entity_table LIKE 'nyss_%'
      ORDER BY {$orderBy}
      LIMIT {$params['rowCount']}
      OFFSET {$params['offset']}
    ";
    $dao = CRM_Core_DAO::executeQuery($sql);

    // Add total.
    $params['total'] = $dao->N;

    $msgs = array();
    if ($dao->N) {
      while ($dao->fetch()) {
        $msg = array();
        $msg['DT_RowId'] = $dao->id;
        $msg['DT_RowClass'] = 'crm-entity';
        $msg['DT_RowAttr'] = array();
        $msg['DT_RowAttr']['data-entity'] = 'note';
        $msg['DT_RowAttr']['data-id'] = $dao->id;

        switch ($dao->type) {
          case 'nyss_contextmsg':
            $msg['type'] = '<span class="msg-type">Contextual</span>';
            break;
          case 'nyss_directmsg':
            $msg['type'] = '<span class="msg-type">Direct</span>';
            break;
          default:
        }

        $msg['id'] = $dao->id;
        $msg['contact_id'] = $dao->entity_id;
        $msg['contact'] = $dao->contact;
        $msg['date'] = date('m/d/Y', strtotime($dao->date));
        $msg['county'] = $dao->county;

        /*$activity['source_contact_name'] = $srcTypeImage . CRM_Utils_System::href($values['source_contact_name'],
            'civicrm/contact/view', "reset=1&cid={$values['source_contact_id']}");*/

        // build links
        $note = nl2br($dao->note);
        $links = array(
          'note' => "<a href='#' id='view-msg-{$dao->id}' class='action-item crm-hover-button view-msg'>View</a>",
          'message' => "<div title='Message Text' style='display:none;' id='msg-{$dao->id}'>{$note}</div>",
          'activity_create' => "<a href='#' id='create-activity-{$dao->id}' contact_id='{$dao->entity_id}' class='action-item crm-hover-button create-activity'>Create Activity</a>",
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
