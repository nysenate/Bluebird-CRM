<?php

require_once 'CRM/Core/Page.php';

class CRM_NYSS_Inbox_Page_Unmatched extends CRM_Core_Page {
  public function run() {
    CRM_Core_Resources::singleton()->addScriptFile('gov.nysenate.inbox', 'js/inbox_unmatched.js');
    CRM_Core_Resources::singleton()->addScriptUrl('/sites/default/themes/Bluebird/scripts/bbtree.js');
    CRM_Core_Resources::singleton()->addStyleFile('gov.nysenate.inbox', 'css/inbox.css');
    CRM_Core_Resources::singleton()->addStyleUrl('/sites/default/themes/Bluebird/css/tags/tags.css');

    $this->assign('title', 'Unmatched Messages');
    $this->assign('toggleAll', "<input class='select-all' type='checkbox'>");

    $controller = new CRM_Core_Controller_Simple('CRM_NYSS_Inbox_Form_MessageFilter',
      ts('Message Filter'), NULL
    );
    $controller->setEmbedded(TRUE);
    $controller->run();

    parent::run();
  }

  static function getUnmatched() {
    //Civi::log()->debug('getUnmatched', array('$_GET' => $_GET));

    $requiredParameters = array();
    $optionalParameters = array(
      'range' => 'Integer',
    );
    $params = CRM_Core_Page_AJAX::defaultSortAndPagerParams();
    $params += CRM_Core_Page_AJAX::validateParams($requiredParameters, $optionalParameters);
    Civi::log()->debug('getUnmatched', array('params' => $params));

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
    $params['range'] = CRM_Utils_Array::value('range', $params, 0);
    $orderBy = (!empty($params['sort'])) ? $params['sort'] : 'updated_date DESC';

    //build range SQL; range = 0 -> all time (no where clause)
    if ($params['range'] > 0) {
      $rangeSql = "AND (updated_date BETWEEN '" . date('Y-m-d H:i:s', strtotime('-' . $params['range'] . ' days')) . "' AND '" . date('Y-m-d H:i:s') . "')";
    }

    $sql = "
      SELECT im.id, im.sender_name, im.sender_email, im.subject, im.forwarder,
        im.updated_date, im.email_date,
        IFNULL(count(ia.file_name), '0') as attachments,
        count(e.id) AS email_count
      FROM nyss_inbox_messages im
      LEFT JOIN civicrm_email e 
        ON im.sender_email = e.email
      LEFT JOIN nyss_inbox_attachments ia 
        ON im.id = ia.email_id
      WHERE im.status = 0 
        $rangeSql
      GROUP BY im.id
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
        $matchCount = (!empty($dao->email_count)) ? 'multi' : 'empty';
        $attachment = (!empty($dao->attachments)) ? "<div class='icon attachment-icon attachment' title='{$dao->attachments} Attachment(s)'></div>" : '';

        $msg['DT_RowId'] = "message-{$dao->id}";
        $msg['DT_RowClass'] = 'crm-entity';
        $msg['DT_RowAttr'] = array();
        $msg['DT_RowAttr']['data-entity'] = 'message';
        $msg['DT_RowAttr']['data-id'] = $dao->id;

        $msg['id'] = "<input class='message-select' type='checkbox' id='select-{$dao->id}'>";
        $msg['sender_info'] = "{$dao->sender_name}<br />
          <span class='emailbubble'>{$dao->sender_email}</span>
          <span class='matchbubble {$matchCount}'>{$dao->email_count}</span>";
        $msg['subject'] = trim($dao->subject).$attachment;
        $msg['date_forwarded'] = date('m/d/Y', strtotime($dao->updated_date));
        $msg['forwarded_by'] = $dao->forwarder;

        $links = array(
          'assign' => "<a href='#' class='action-item crm-hover-button inbox-assign-contact'>Assign Contact</a>",
          'delete' => "<a href='#' class='action-item crm-hover-button inbox-delete'>Delete</a>",
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
