<?php

class CRM_NYSS_Inbox_BAO_Inbox {

  const STATUS_UNMATCHED = 0;

  const STATUS_MATCHED = 1;

  const STATUS_CLEARED = 7;

  const STATUS_DELETED = 9;

  const STATUS_UNPROCESSED = 99;

  const DEFAULT_ACTIVITY_STATUS = 'Completed';

  const DEFAULT_AUTH_GROUP = 'Authorized_Forwarders';

  const DEFAULT_CONTACT_ID = 1;



  /**
   * add common resources
   */
  static function addResources($type = NULL) {
    CRM_Core_Resources::singleton()
      ->addStyleFile('gov.nysenate.inbox', 'css/inbox.css');

    //add type-specific resources
    switch ($type) {
      case 'unmatched':
        CRM_Core_Resources::singleton()
          ->addScriptFile('gov.nysenate.inbox', 'js/inbox.js');
        CRM_Core_Resources::singleton()
          ->addScriptFile('gov.nysenate.inbox', 'js/inbox_unmatched.js');
        CRM_Core_Resources::singleton()
          ->addVars('NYSS', ['inboxType' => $type]);
        break;
      case 'matched':
        CRM_Core_Resources::singleton()
          ->addScriptFile('gov.nysenate.inbox', 'js/inbox.js');
        CRM_Core_Resources::singleton()
          ->addScriptFile('gov.nysenate.inbox', 'js/inbox_matched.js');
        CRM_Core_Resources::singleton()
          ->addVars('NYSS', ['inboxType' => $type]);
        break;
      case 'process':
        CRM_Core_Resources::singleton()
          ->addScriptFile('gov.nysenate.inbox', 'js/inbox_process.js');
        break;
      case 'report':
        CRM_Core_Resources::singleton()
          ->addScriptFile('gov.nysenate.inbox', 'js/inbox_report.js');
        CRM_Core_Resources::singleton()
          ->addStyleFile('gov.nysenate.inbox', 'css/inbox_report.css');
        break;
      case 'assign':
        CRM_Core_Resources::singleton()
          ->addScriptFile('gov.nysenate.inbox', 'js/inbox_assign.js');
        break;
      default:
    }
  }

  /**
   * @param $string
   * @param int $limit
   * @param string $pad
   *
   * @return string
   *
   * trim and truncate the string to the configured length
   * also fix possible encoding issues
   */
  static function cleanText($string, $limit = 0, $pad = "...") {
    $lib = CRM_Core_Resources::singleton()
      ->getPath('gov.nysenate.inbox', 'incl/htmlfixer.class.php');
    require_once "{$lib}";

    $string = trim($string);
    $string = mb_convert_encoding($string, "HTML-ENTITIES", "UTF-8");

    $htmlFixer = new HtmlFixer();
    $string = $htmlFixer->getFixedHtml($string);
    $string = str_replace('&nbsp;', ' ', $string);

    //cludgy way to try to strip out text chunks with tons of line breaks...
    $string = str_replace('<br /><br /><br />', '<br /> <br />', $string);
    $string = str_replace('<br /><br /><br />', '<br /> <br />', $string);
    $string = str_replace('<br /> <br /> <br />', '<br /> <br />', $string);
    $string = str_replace('<br /> <br /> <br />', '<br /> <br />', $string);
    $string = str_replace('<br /> <br /> <br />', '<br /> <br />', $string);
    $string = str_replace('<br /> <br /> <br />', '<br /> <br />', $string);

    $string = trim($string);

    // return with no change if string is shorter than $limit
    if (strlen($string) <= $limit) {
      return $string;
    }

    //truncate
    if ($limit > 0) {
      $string = substr($string, 0, $limit) . $pad;
    }

    return $string;
  }

  /**
   * Reads the Bluebird config file for the list of blacklist addresses to
   * ignore during automated matching.
   *
   * @return array[]|false|string[]
   */
  static function getBlacklistAddresses() {
    $bbconfig = get_bluebird_instance_config();
    $blacklist_cfg = [];
    if (isset($bbconfig['imap.sender.blacklist_file'])) {
      $fn = $bbconfig['imap.sender.blacklist_file'];
      if (file_exists($fn)) {
        $fn_read = file($fn, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $blacklist_cfg = $fn_read ? $fn_read : [];
      }
    }
    return $blacklist_cfg;
  }

  /**
   * @param $id
   *
   * @return array
   *
   * get details for a single inbox polling row/contact
   */
  static function getDetails($rowId, $matched_id = '') {
    $details = [];
    $sqlParams = [1 => [$rowId, 'Positive']];

    if ($matched_id && $matched_id != 'unmatched') {
      $matched_id_sql = "AND imm.matched_id = %2";
      $sqlParams[2] = [$matched_id, 'Positive'];
    }

    $sql = "
      SELECT im.id, im.message_id, im.sender_name, im.sender_email, im.subject, im.body, im.forwarder,
        im.status, im.matcher, imm.matched_id, imm.activity_id, im.updated_date, im.email_date, 
        IFNULL(count(ia.file_name), '0') as attachments,
        COUNT(e.id) AS email_count, GROUP_CONCAT(DISTINCT e.id) AS email_ids,
        GROUP_CONCAT(DISTINCT e.contact_id) AS matched_ids
      FROM nyss_inbox_messages im
      LEFT JOIN nyss_inbox_messages_matched imm 
        ON im.id = imm.row_id
      LEFT JOIN (
        SELECT civicrm_email.id, email, contact_id
        FROM civicrm_email
        JOIN civicrm_contact
          ON civicrm_email.contact_id = civicrm_contact.id
          AND civicrm_contact.is_deleted != 1
      ) e
        ON im.sender_email = e.email
      LEFT JOIN nyss_inbox_attachments ia 
        ON im.id = ia.email_id
      WHERE im.id = %1
        {$matched_id_sql}
      GROUP BY im.id, imm.id
    ";
    $dao = CRM_Core_DAO::executeQuery($sql, $sqlParams);

    //if we get more than one row, we should have been passed a contact ID...
    if ($dao->N > 1) {
      return [
        'is_error' => TRUE,
        'msg' => 'Unable to return a details for this message.'
      ];
    }

    while ($dao->fetch()) {
      $attachment = (!empty($dao->attachments)) ?
        "<div class='icon attachment-icon attachment' title='{$dao->attachments} Attachment(s)'></div>" : '';
      $matched = self::getMatched($dao->matched_id);
      $body = CRM_NYSS_Inbox_BAO_Inbox::cleanText($dao->body);
      $parsed = self::parseMessage($body);
      $details = [
        'id' => $dao->id,
        'message_id' => $dao->message_id,
        'sender_name' => CRM_NYSS_Inbox_BAO_Inbox::cleanText($dao->sender_name),
        'sender_email' => $dao->sender_email,
        'subject' => CRM_NYSS_Inbox_BAO_Inbox::cleanText($dao->subject),
        'subject_display' => CRM_NYSS_Inbox_BAO_Inbox::cleanText($dao->subject) . $attachment,
        'body_raw' => $body,
        'body' => self::highlightItems($body, $parsed),
        'forwarded_by' => $dao->forwarder,
        'status' => $dao->status,
        'matcher' => $dao->matcher,
        'matched_id' => $dao->matched_id,
        'matched_to_display' => implode(', ', $matched),
        'activity_id' => $dao->activity_id,
        'updated_date' => date('m/d/Y', strtotime($dao->updated_date)),
        'date_email' => date('m/d/Y', strtotime($dao->email_date)),
        'attachments' => $dao->attachments,
        'email_count' => $dao->email_count,
        'email_ids' => $dao->email_ids,
        'matched_ids' => $dao->matched_ids,
      ];
    }

    //Civi::log()->debug('getDetails', array('$details' => $details));
    return $details;
  }

  /**
   * @param array $ids
   *
   * retrieve list of ids to delete
   * either passed to function or via $_REQUEST (AJAX)
   *
   * this will be an array of arrays with a pairing: row_id, matched_id (optional)
   */
  static function deleteMessages($ids = []) {
    if (empty($ids) && !empty(CRM_Utils_Array::value('ids', $_REQUEST))) {
      $ids = CRM_Utils_Array::value('ids', $_REQUEST);
    }
    //Civi::log()->debug('deleteMessages', array('$ids' => $ids));

    $userId = CRM_Core_Session::getLoggedInContactID();
    if (empty($userId)) {
      return [
        'is_error' => TRUE,
        'msg' => 'Unable to determine the logged in user in order to track the message deletion.',
        'details' => ['ids' => $ids],
      ];
    }

    $ret = [
      'is_error' => FALSE,
      'msg' => "Messages have been deleted/unmatched.",
      'details' => [],
    ];
    foreach ($ids as $idPair) {
      $action = '';
      //if passed from single delete form, we get an array; if multiple-deleted, we need to split
      if (!is_array($idPair)) {
        $pair = explode('-', $idPair);
        $idPair = [
          'row_id' => $pair[0],
          'matched_id' => $pair[1],
        ];
      }

      //check if message has > 1 matched contact
      $messageMatches = self::getMessageMatches($idPair['row_id']);
      //Civi::log()->debug('deleteMessages', array('$messageMatches' => $messageMatches));
      //if more than 1, we only delete the match record
      if (count($messageMatches) > 1) {
        //we can only delete the match record if one exists; record may be unmatched
        if (!empty($idPair['matched_id'])) {
          self::deleteMessageMatch($idPair['row_id'], $idPair['matched_id']);
          $action = 'unmatched';
        }
      }
      else {
        //leave match record intact; just delete message
        CRM_Core_DAO::executeQuery("
          UPDATE nyss_inbox_messages
          SET status = " . self::STATUS_DELETED . ", matcher = %1
          WHERE id = %2
        ", [
          1 => [$userId, 'Positive'],
          2 => [$idPair['row_id'], 'Positive']
        ]);
        $action = 'deleted';
      }
      if ($action) {
        $ret['details'][] = ['id' => $idPair['row_id'], 'action' => $action];
      }
    }

    CRM_Utils_JSON::output($ret);
  }

  /**
   * @param null $rowId
   * @param null $messageId
   * @param $matchedId
   *
   * @return boolean
   *
   *
   */
  static function deleteMessageMatch($rowId, $matchedId) {
    //Civi::log()->debug('deleteMessageMatch', array('rowId' => $rowId, 'matchedId' => $matchedId));

    CRM_Core_DAO::executeQuery("
      DELETE FROM nyss_inbox_messages_matched
      WHERE matched_id = %1
        AND row_id = %2
    ", [
      1 => [$matchedId, 'Positive'],
      2 => [$rowId, 'Positive'],
    ]);

    return TRUE;
  }

  /**
   * @param array $ids
   *
   * retrieve list of ids to clear
   * either passed to function or via $_REQUEST (AJAX)
   */
  static function clearMessages($ids = []) {
    if (empty($ids) && !empty(CRM_Utils_Array::value('ids', $_REQUEST))) {
      $ids = CRM_Utils_Array::value('ids', $_REQUEST);
    }

    $idList = implode(',', $ids);
    $userId = CRM_Core_Session::getLoggedInContactID();

    if (!empty($idList) && !empty($userId)) {
      CRM_Core_DAO::executeQuery("
        UPDATE nyss_inbox_messages
        SET status = " . self::STATUS_CLEARED . ", matcher = %1
        WHERE id IN ({$idList})
      ", [
        1 => [$userId, 'Positive'],
      ]);
    }
  }

  /**
   * @param $params
   *
   * @return array
   */
  static function getMessages($params, $status = 'unmatched') {
    //Civi::log()->debug('getMessages', array('params' => $params));

    // Format the params.
    $params['offset'] = ($params['page'] - 1) * $params['rp'];
    $params['rowCount'] = $params['rp'];
    $params['sort'] = CRM_Utils_Array::value('sortBy', $params);
    $params['range'] = CRM_Utils_Array::value('range', $params, 0);
    $params['term'] = json_decode(CRM_Utils_Array::value('term', $params, NULL));
    $orderBy = (!empty($params['sort'])) ? $params['sort'] : 'updated_date DESC, subject, matched_id';

    //build range SQL; range = 0 -> all time (no where clause)
    $rangeSql = '';
    if ($params['range'] > 0) {
      $rangeSql = "AND (updated_date BETWEEN '" . date('Y-m-d H:i:s', strtotime('-' . $params['range'] . ' days')) . "' AND '" . date('Y-m-d H:i:s') . "')";
    }

    //build search term SQL
    $termSql = '';
    if (!empty($params['term'])) {
      $term = CRM_Core_DAO::escapeString($params['term']);
      $termSql = "AND (
        im.sender_name LIKE '%{$term}%' OR im.sender_email LIKE '%{$term}%' OR im.subject LIKE '%{$term}%'
      )";
    }

    switch ($status) {
      case 'unmatched':
        $statusSql = 'AND im.status = ' . self::STATUS_UNMATCHED;
        $matchedSql = '';
        break;
      case 'matched':
        $statusSql = 'AND im.status = ' . self::STATUS_MATCHED;
        $matchedSql = 'AND imm.id IS NOT NULL';
        break;
      default:
        $statusSql = '';
        break;
    }

    $sql = "
      SELECT SQL_CALC_FOUND_ROWS im.id, im.sender_name,
        im.sender_email, im.subject, im.forwarder, im.updated_date,
        im.email_date, im.matcher, imm.matched_id, mc.display_name matcher_name,
        IFNULL(count(ia.file_name), '0') as attachments,
        count(e.id) AS email_count
      FROM nyss_inbox_messages im
      LEFT JOIN nyss_inbox_messages_matched imm 
        ON im.id = imm.row_id
      LEFT JOIN (
          SELECT civicrm_email.id, email
          FROM civicrm_email
          JOIN civicrm_contact
            ON civicrm_email.contact_id = civicrm_contact.id
            AND civicrm_contact.is_deleted != 1
        ) e
        ON im.sender_email = e.email
      LEFT JOIN nyss_inbox_attachments ia 
        ON im.id = ia.email_id
      LEFT JOIN civicrm_contact mc
        ON im.matcher = mc.id
        AND im.matcher != 1
      WHERE (1)
        {$statusSql}
        {$rangeSql}
        {$termSql}
        {$matchedSql}
      GROUP BY im.id, imm.matched_id
      ORDER BY {$orderBy}
      LIMIT {$params['rowCount']}
      OFFSET {$params['offset']}
    ";
    //Civi::log()->debug('getMessages', array('sql' => $sql));
    $dao = CRM_Core_DAO::executeQuery($sql);
    //Civi::log()->debug('getMessages', array('$dao' => $dao));

    // Add total.
    $params['total'] = CRM_Core_DAO::singleValueQuery('SELECT FOUND_ROWS();');

    $msgs = [];
    if ($dao->N) {
      while ($dao->fetch()) {
        $msg = [];
        $matchCount = (!empty($dao->email_count)) ? 'multi' : 'empty';
        $attachment = (!empty($dao->attachments)) ?
          "<div class='icon attachment-icon attachment' title='{$dao->attachments} Attachment(s)'></div>" : '';
        $senderName = CRM_NYSS_Inbox_BAO_Inbox::cleanText($dao->sender_name, 15);
        $matchId = (!empty($dao->matched_id)) ? $dao->matched_id : 'unmatched';

        $msg['DT_RowId'] = "message-{$dao->id}-{$matchId}";
        $msg['DT_RowClass'] = 'crm-entity';
        $msg['DT_RowAttr'] = [];
        $msg['DT_RowAttr']['data-entity'] = 'message';
        $msg['DT_RowAttr']['data-id'] = "{$dao->id}-{$matchId}";

        $msg['id'] = "<input class='message-select' type='checkbox' id='select-{$dao->id}-{$matchId}'>";

        //sender's info varies based on matched/unmatched view
        switch ($status) {
          case 'unmatched':
            $senderEmail = (empty($dao->sender_email)) ? '' :
              "<span class='emailbubble'>" . CRM_NYSS_Inbox_BAO_Inbox::cleanText($dao->sender_email, 15) . "</span>";
            $msg['sender_name'] = "{$senderName}{$senderEmail}
              <span class='matchbubble {$matchCount}'>{$dao->email_count}</span>";
            break;

          case 'matched':
            if ($dao->matcher != 1) {
              $matchTypeCSS = 'match-manual';
              $matchTypeText = 'M';
              $matchString = "Manually matched by {$dao->matcher_name}";
            }
            else {
              $matchTypeCSS = 'match-auto';
              $matchTypeText = 'A';
              $matchString = 'Automatically matched';
            }
            try {
              $matchedName = civicrm_api3('contact', 'getvalue', [
                'id' => $dao->matched_id,
                'return' => 'sort_name',
              ]);
            }
            catch (CiviCRM_API3_Exception $e) {
            }
            $matchedUrl = CRM_Utils_System::url('civicrm/contact/view',
              "reset=1&cid={$dao->matched_id}");
            $msg['sender_name'] = "<a href='{$matchedUrl}'>{$matchedName}</a>
              <span class='matchbubble {$matchTypeCSS}' title='This email was {$matchString}'>{$matchTypeText}</span>";
            break;

          default:
            $msg['sender_name'] = $senderName;
        }

        $msg['subject'] = CRM_NYSS_Inbox_BAO_Inbox::cleanText($dao->subject, 25) . $attachment;
        $msg['updated_date'] = date('M d, Y', strtotime($dao->updated_date));
        $msg['forwarder'] = $dao->forwarder;


        switch ($status) {
          case 'unmatched':
            $urlAssign = CRM_Utils_System::url('civicrm/nyss/inbox/assigncontact', "reset=1&id={$dao->id}");
            $links['assign'] =
              "<a href='{$urlAssign}' class='action-item crm-hover-button crm-popup inbox-assign-contact'>Assign Contact</a>";
            break;
          case 'matched':
            $urlProcess = CRM_Utils_System::url('civicrm/nyss/inbox/process',
              "reset=1&id={$dao->id}&matched_id={$dao->matched_id}");
            $urlClear = CRM_Utils_System::url('civicrm/nyss/inbox/clear',
              "reset=1&id={$dao->id}&matched_id={$dao->matched_id}");
            $links['process'] =
              "<a href='{$urlProcess}' class='action-item crm-hover-button crm-popup inbox-process-contact'>Process</a>";
            $links['clear'] =
              "<a href='{$urlClear}' class='action-item crm-hover-button crm-popup inbox-clear-contact'>Clear</a>";
            break;
          default:
        }

        $urlDelete = CRM_Utils_System::url('civicrm/nyss/inbox/delete',
          "reset=1&id={$dao->id}&matched_id={$matchId}");
        $links['delete'] = "<a href='{$urlDelete}' class='action-item crm-hover-button crm-popup inbox-delete'>Delete</a>";

        $msg['links'] = implode(' ', $links);

        array_push($msgs, $msg);
      }
    }

    $msgsDT = [];
    $msgsDT['data'] = $msgs;
    $msgsDT['recordsTotal'] = $params['total'];
    $msgsDT['recordsFiltered'] = $params['total'];

    //Civi::log()->debug('getUnmatchedMessages', array($msgsDT));
    return $msgsDT;
  }

  /**
   * @param $rowId
   * @param $contactIds array()
   *
   * @return array
   *
   * Important: This method should only be used when FIRST matching a message with
   * contacts. Reassignments do not support multiple contacts and are already
   * linked with an activity ID.
   */
  static function assignMessage($rowId, $contactIds) {
    if (empty($rowId) || empty($contactIds)) {
      return [
        'is_error' => TRUE,
        'message' => 'Unable to assign the message; missing required values.'
      ];
    }
    //Civi::log()->debug('assignMessage', array('$contactIds' => $contactIds));

    //array to hold details of each processed match
    $matches = [];

    $bbconfig = get_bluebird_instance_config();
    $status = self::DEFAULT_ACTIVITY_STATUS;
    if (isset($bbconfig['imap.activity.status.default'])) {
      $status = $bbconfig['imap.activity.status.default'];
    }

    $message = self::getDetails($rowId);
    $forwarder = self::getForwarder($message['forwarded_by']);

    //exit immediately if the message has already been matched
    if ($message['status'] != self::STATUS_UNMATCHED) {
      return [
        'is_error' => TRUE,
        'message' => 'Unable to assign the message; it has already been matched.'
      ];
    }

    foreach ($contactIds as $contactId) {
      //cleanup subject line
      $subject = str_replace('>', '', $message['subject']);
      $subject = str_replace('  ', ' ', $subject);
      $subject = substr($subject, 0, 250);

      $params = [
        'activity_label' => 'Inbound Email',
        'source_contact_id' => $forwarder,
        'target_contact_id' => $contactId,
        'subject' => $subject,
        'is_auto' => 0,
        'status_id' => $status,
        'activity_date_time' => $message['date_updated'],
        'details' => $message['body'],
      ];
      /*Civi::log()->debug('assignMessage', array(
        '$params' => $params,
        'subject length' => strlen($params['subject']),
      ));*/

      try {
        $activity = civicrm_api3('activity', 'create', $params);
        $attachments = CRM_NYSS_Inbox_BAO_Inbox::getAttachments($rowId);
        $uploadDir = CRM_Core_Config::singleton()->customFileUploadDir;

        if (!empty($attachments)) {
          foreach ($attachments as $key => $attachment) {
            $attachmentName = $attachment['fileName'];
            $attachmentFull = $attachment['fileFull'];

            if (file_exists($attachmentFull)) {
              $fileName = CRM_Utils_File::makeFileName($attachmentName);
              $fileFull = $uploadDir . $fileName;

              // move file to the civicrm upload directory
              rename($attachmentFull, $fileFull);

              $finfo = finfo_open(FILEINFO_MIME_TYPE);
              $mime = finfo_file($finfo, $fileFull);
              finfo_close($finfo);

              $fileDAO = new CRM_Core_DAO_File();
              $fileDAO->mime_type = $mime;
              $fileDAO->uri = $fileName;
              $fileDAO->upload_date = date('YmdHis');
              $fileDAO->save();

              $entityFileDAO = new CRM_Core_DAO_EntityFile();
              $entityFileDAO->entity_table = 'civicrm_activity';
              $entityFileDAO->entity_id = $activity['id'];
              $entityFileDAO->file_id = $fileDAO->id;
              $entityFileDAO->save();
            }
          }
        }
      }
      catch (CiviCRM_API3_Exception $e) {
        Civi::log()->error('assignMessage', ['e' => $e]);

        //we arguably should attempt to process all contacts before we
        //decide if we are returning with an error; but there's an argument
        //to be made for exiting immediately...
        return [
          'is_error' => TRUE,
          'message' => 'Unable to create the activity.'
        ];
      }

      $matches[] = [
        'row_id' => $rowId,
        'message_id' => $message['message_id'],
        'matched_id' => $contactId,
        'activity_id' => $activity['id'],
      ];
    }

    //update the message record status and matcher
    CRM_Core_DAO::executeQuery("
      UPDATE nyss_inbox_messages
      SET status = %1,
        matcher = %2
      WHERE id = %3
    ", [
      1 => [self::STATUS_MATCHED, 'Integer'],
      2 => [$forwarder, 'Integer'],
      3 => [$rowId, 'Integer'],
    ]);

    //store in messages_matched table
    $matchedContacts = [];
    foreach ($matches as $match) {
      CRM_Core_DAO::executeQuery("
        INSERT INTO nyss_inbox_messages_matched
        (row_id, message_id, matched_id, activity_id)
        VALUES
        (%1, %2, %3, %4)
      ", [
        1 => [$match['row_id'], 'Positive'],
        2 => [$match['message_id'], 'Positive'],
        3 => [$match['matched_id'], 'Positive'],
        4 => [$match['activity_id'], 'Positive'],
      ]);

      $contactName = civicrm_api3('contact', 'getvalue', [
        'id' => $match['matched_id'],
        'return' => 'display_name',
      ]);

      $matchedContacts[] = $contactName;
    }
    $matchedContactsList = implode(', ', $matchedContacts);

    return [
      'is_error' => FALSE,
      'message' => "Message successfully assigned to {$matchedContactsList}.",
    ];
  }

  /**
   * @param $params
   *
   * @return array
   *
   * process message record: assignment, contact tags, activity tags, activity details
   */
  static function processMessages($values) {
    //Civi::log()->debug('processMessages', array('values' => $values, '$_REQUEST' => $_REQUEST));

    $msg = [];
    if (!empty($values['is_multiple'])) {
      $rows = json_decode($values['multi_ids'], TRUE);
    }
    else {
      $rows = [
        [
          'row_id' => $values['row_id'],
          'matched_id' => $values['matched_id'],
          'activity_id' => $values['activity_id'],
          'current_assignee' => $values['matched_id'],
        ]
      ];
    }
    //Civi::log()->debug('processMessages', array('$rows' => $rows));

    foreach ($rows as $row) {
      if (!empty($values['assignee'])) {
        //check if we have already matched with this contact
        $matchId = CRM_Core_DAO::singleValueQuery("
          SELECT id
          FROM nyss_inbox_messages_matched
          WHERE row_id = %1
            AND matched_id = %2
        ", [
          1 => [$row['row_id'], 'Positive'],
          2 => [$values['assignee'], 'Positive'],
        ]);

        if ($matchId) {
          //delete match record
          self::deleteMessageMatch($row['row_id'], $values['assignee']);
          $msg[] = 'This message was already matched with the selected contact. Removing duplicate match.';
        }
        else {
          CRM_Core_DAO::executeQuery("
            UPDATE nyss_inbox_messages_matched
            SET matched_id = %1
            WHERE row_id = %2
              AND matched_id = %3
          ", [
            1 => [$values['assignee'], 'Positive'],
            2 => [$row['row_id'], 'Positive'],
            3 => [$row['matched_id'], 'Positive'],
          ]);
        }

        //also reassign activity target
        if ($row['activity_id']) {
          try {
            civicrm_api3('activity', 'create', [
              'id' => $row['activity_id'],
              'target_contact_id' => $values['assignee'],
            ]);
          }
          catch (CiviCRM_API3_Exception $e) {
            Civi::log()
              ->debug('processMessages update activity target', ['e' => $e]);
            $msg[] = 'Unable to update activity target.';
          }
        }
      }

      //get assignee ID (from new or existing) and all existing tags
      $assigneeId = (!empty($values['assignee'])) ? $values['assignee'] : $row['current_assignee'];
      $assigneeTags = CRM_Core_BAO_EntityTag::getTag($assigneeId);
      //Civi::log()->debug('processMessages', array('$assigneeTags' => $assigneeTags));

      if (!empty($values['contact_keywords'])) {
        foreach (explode(',', $values['contact_keywords']) as $tagID) {
          if (!in_array($tagID, $assigneeTags)) {
            try {
              civicrm_api3('entity_tag', 'create', [
                'entity_id' => (!empty($values['assignee'])) ? $values['assignee'] : $row['current_assignee'],
                'tag_id' => $tagID,
                'entity_table' => 'civicrm_contact',
              ]);
            }
            catch (CiviCRM_API3_Exception $e) {
              //Civi::log()->debug('processMessages contact keywords', array('e' => $e));
              //$msg[] = 'Unable to assign all keywords to the contact.';
            }
          }
        }
      }

      if (!empty($values['tag'])) {
        $tags = explode(',', CRM_Utils_Array::value('tag', $values));
        foreach ($tags as $tagID) {
          if (!in_array($tagID, $assigneeTags)) {
            try {
              civicrm_api3('entity_tag', 'create', [
                'entity_id' => (!empty($values['assignee'])) ? $values['assignee'] : $row['current_assignee'],
                'tag_id' => $tagID,
                'entity_table' => 'civicrm_contact',
              ]);
            }
            catch (CiviCRM_API3_Exception $e) {
              //Civi::log()->debug('processMessages contact issue codes', array('e' => $e));
              //$msg[] = 'Unable to assign all issue codes to the contact.';
            }
          }
        }
      }

      if (!empty($values['contact_positions'])) {
        foreach (explode(',', $values['contact_positions']) as $tagID) {
          if (!in_array($tagID, $assigneeTags)) {
            try {
              civicrm_api3('entity_tag', 'create', [
                'entity_id' => (!empty($values['assignee'])) ? $values['assignee'] : $row['current_assignee'],
                'tag_id' => $tagID,
                'entity_table' => 'civicrm_contact',
              ]);
            }
            catch (CiviCRM_API3_Exception $e) {
              //Civi::log()->debug('processMessages contact positions', array('e' => $e));
              //$msg[] = 'Unable to assign all positions to the contact.';
            }
          }
        }
      }

      //groups
      if (!empty($values['group_id'])) {
        try {
          civicrm_api3('group_contact', 'create', [
            'contact_id' => (!empty($values['assignee'])) ? $values['assignee'] : $row['current_assignee'],
            'group_id' => $values['group_id'],
          ]);
        }
        catch (CiviCRM_API3_Exception $e) {
          //Civi::log()->debug('processMessages groups', array('e' => $e));
        }
      }

      //ensure we have an activity ID before processing these
      if ($row['activity_id']) {
        if (!empty($values['activity_keywords'])) {
          foreach (explode(',', $values['activity_keywords']) as $tagID) {
            try {
              civicrm_api3('entity_tag', 'create', [
                'entity_id' => $row['activity_id'],
                'tag_id' => $tagID,
                'entity_table' => 'civicrm_activity',
              ]);
            }
            catch (CiviCRM_API3_Exception $e) {
              //Civi::log()->debug('processMessages activity keywords', array('e' => $e));
              //$msg[] = 'Unable to assign all keywords to the activity.';
            }
          }
        }

        if (!empty($values['activity_assignee']) || !empty($values['activity_status'])) {
          $params = ['id' => $row['activity_id']];

          if (!empty($values['activity_assignee'])) {
            $params['assignee_contact_id'] = $values['activity_assignee'];
          }

          if (!empty($values['activity_status'])) {
            $params['status_id'] = $values['activity_status'];
          }

          try {
            civicrm_api3('activity', 'create', $params);

            if (!empty($values['activity_assignee'])) {
              $sendEmail = self::sendActivityAssigneeEmail($params);
              if ($sendEmail['status']) {
                $msg[] = 'Message(s) has been processed.';
                $msg[] = $sendEmail['msg'];
              }
            }
          }
          catch (CiviCRM_API3_Exception $e) {
            Civi::log()->debug('processMessages create activity', ['e' => $e]);
            $msg[] = 'Unable to update activity record.';
          }
        }
      }

      //if working with a single contact, check if email should be updated
      if (!$values['is_multiple']) {
        $matchId = (!empty($values['assignee'])) ? $values['assignee'] : $row['current_assignee'];
        foreach (['phone', 'email'] as $type) {
          $new_val = CRM_Utils_Array::value("{$type}-" . $matchId, $_REQUEST);
          $orig_val = CRM_Utils_Array::value("{$type}orig-" . $matchId, $_REQUEST);

          if ($new_val != $orig_val) {
            try {
              if (!empty($new_val)) {
                civicrm_api3($type, 'create', [
                  'contact_id' => $matchId,
                  $type => $new_val,
                  'is_primary' => TRUE,
                  'location_type_id' => "Home",
                ]);
              }
              else {
                //allow an empty value to delete existing email record
                $primary = civicrm_api3($type, 'getsingle', [
                  'contact_id' => $matchId,
                  'is_primary' => TRUE,
                ]);

                if ($primary[$type] == $orig_val) {
                  civicrm_api3($type, 'delete', [
                    'id' => $primaryEmail['id'],
                  ]);
                }
              }
            }
            catch (CiviCRM_API3_Exception $e) {
            }
          }
        }
      }
    }

    $msg = array_filter($msg);
    return $msg;
  }

  /**
   * @param $email
   *
   * @return int|null|string
   */
  static function getForwarder($email) {
    $forwarderId = CRM_Core_DAO::singleValueQuery("
      SELECT e.contact_id
      FROM civicrm_group_contact gc, civicrm_group g, civicrm_email e
      WHERE g.name = '" . self::DEFAULT_AUTH_GROUP . "'
        AND e.email = %1
        AND g.id = gc.group_id
        AND gc.status = 'Added'
        AND gc.contact_id = e.contact_id
      ORDER BY gc.contact_id ASC
      LIMIT 1
    ", [
      1 => [$email, 'String'],
    ]);

    if (empty($forwarderId)) {
      $forwarderId = self::DEFAULT_CONTACT_ID;
    }

    return $forwarderId;
  }

  /**
   * @param $messageId
   *
   * @return array
   *
   * given a messageId, retrieve all attachment details
   */
  static function getAttachments($messageId) {
    $dao = CRM_Core_DAO::executeQuery("
      SELECT * 
      FROM nyss_inbox_attachments 
      WHERE email_id = %1
    ", [
      1 => [$messageId, 'Integer'],
    ]);

    $attachments = [];
    while ($dao->fetch()) {
      $attachments[] = [
        'fileName' => $dao->file_name,
        'fileFull' => $dao->file_full,
        'rejection' => $dao->rejection,
        'size' => $dao->size,
        'ext' => $dao->ext,
      ];
    }

    return $attachments;
  }

  /**
   * @param $cid
   *
   * @return array
   */
  static function getMatched($cids) {
    $matchedContacts = [];

    if (empty($cids)) {
      return NULL;
    }

    try {
      foreach (explode(',', $cids) as $cid) {
        $contact = civicrm_api3('contact', 'getsingle', [
          'id' => $cid,
        ]);

        $matchedUrl = CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid={$cid}");
        $matchedContacts[] = "<a href='{$matchedUrl}'>{$contact['display_name']}</a>";
      }
    }
    catch (CiviCRM_API3_Exception $e) {
    }

    return $matchedContacts;
  }

  /**
   * @param $rowId
   *
   * @return array
   *
   * given a message rowId, retrieve an array of all matched contact IDs
   */
  static function getMessageMatches($rowId) {
    $dao = CRM_Core_DAO::executeQuery("
      SELECT imm.*
      FROM nyss_inbox_messages_matched imm
      WHERE imm.row_id = %1
    ", [
      1 => [$rowId, 'Positive'],
    ]);

    $matchedIds = [];
    while ($dao->fetch()) {
      $matchedIds[] = $dao->matched_id;
    }

    return $matchedIds;
  }

  /**
   * Generate an array of e-mail addresses, phone numbers, city/state/zips,
   * and proper names that were found in the message body.
   */
  private static function parseMessage($msgBody) {
    $res = [];

    // Convert message body into tagless text.
    $text = preg_replace('/<(p|br)[^>]*>|\r/', "\n", $msgBody);
    $text = strip_tags($text);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML401, 'ISO-8859-1');

    // Find email addresses within the text.
    preg_match_all('/[\w\.\-\+]+@[a-z\d\-]+(\.[a-z\d\-]+)*/i', $text, $emails);
    $res['emails'] = array_unique($emails[0]);

    // Isolate blacklist senders.
    $res['blacklist'] = self::getBlacklistAddresses();
    $res['emails'] = array_values(array_diff($res['emails'], $res['blacklist']));

    // Find possible phone numbers
    preg_match_all('/([(]\d{3}[)] *|\d{3}[\-\.\ ])?\d{3}[\-\.]\d{4}/', $text, $phones);
    $res['phones'] = array_unique($phones[0]);

    // Search for "City, STATE Zip5-Zip4"
    preg_match_all('/(?<city>[A-Z][A-Za-z\-\.\ ]+[a-z])\h*,\h*(?<stateAbbr>[A-Z]{2})\h+(?<zip>\d{5}(?:\-\d{4})?)/',
      $text,
      $addresses,
      PREG_SET_ORDER);
    // Expand state abbreviations into full state names.
    foreach ($addresses as $id => &$addrInfo) {
      $addrInfo['state'] = self::getStateName($addrInfo['stateAbbr']);
    }
    $res['addrs'] = self::reformulate_preg_array($addresses);

    // Find possible names
    preg_match_all("/(?:(?<prefix>(?:Mr|MR|Ms|MS|Miss|MISS|Mrs|MRS|Dr|DR|Sir|Madam|Senator|(?:Assembly|Congress)(?:wo)?man)\.?)\h+)?(?<first>[A-Z](?:[a-z]+(?:\-[A-Z][a-z]+)?|\.))\h+(?<middle>[A-Z](?:[a-z]+|\.)\h+)?(?<last>(?:[A-Z][a-z]{,2}[\.\']?\ ?)?[A-Z][a-z]+(?:\-[A-Z][a-z]+)?)(?<suffix>(?:\h*,\h)?(?:Jr|JR|Sr|SR|II|III|PhD|PHD|MD|Esq))?/", $text, $names, PREG_SET_ORDER);

    // Use dedupe rules to eliminate names not found in the system.
    foreach ($names as $id => $nameInfo) {
      if (isset($nameInfo['first'])) {
        $firstName = strtolower($nameInfo['first']);
        $query = "
          SELECT COUNT(id) count_id
          FROM fn_group 
          WHERE given 
          LIKE '$firstName'
        ";
        $dbres = CRM_Core_DAO::singleValueQuery($query);
        if ($dbres < 1) {
          unset($names[$id]);
        }
      }
    }
    $res['names'] = self::reformulate_preg_array($names);

    return $res;
  } // parseMessage()

  /**
   * Given an array of items found in the body of the email, generate
   * HTML to highlight those items.
   */
  private static function highlightItems($text, $items) {
    //file_put_contents("/tmp/inbound_email/items", print_r($items, true));
    $itemMap = [
      'emails' => ['class' => 'email_address', 'text' => 'email'],
      'blacklist' => [
        'class' => 'aggregator_email',
        'text' => 'aggregator email'
      ],
      'phones' => ['class' => 'phone', 'text' => 'phone number'],
      'addrs' => ['class' => 'zip', 'text' => 'city/state/zip'],
      'names' => ['class' => 'name', 'text' => 'name']
    ];

    foreach ($items as $itemType => $itemList) {
      $itemClass = $itemMap[$itemType]['class'];
      $itemText = $itemMap[$itemType]['text'];

      if (count($itemList) == 0) {
        continue;
      }

      if (in_array($itemType, ['emails', 'blacklist', 'phones'])) {
        $re = implode('###', $itemList);
        $re = preg_quote($re);
        $re = '/' . preg_replace('/###/', '|', $re) . '/';
        $text = preg_replace($re,
          "<span class='found $itemClass' data-search='$0' title='Click to use this $itemText'>$0</span>",
          $text
        );
      }
      else {
        foreach ($itemList as $search => $json) {
          $re = preg_quote($search);
          $text = preg_replace("/$re/",
            "<span class='found $itemClass' data-json='$json' title='Click to use this $itemText'>$0</span>",
            $text
          );
        }
      }
    }
    return $text;
  } // highlightItems()

  private static function getStateName($abbr) {
    static $stateNameMap = NULL;

    if ($stateNameMap == NULL) {
      $query = "
        SELECT abbreviation, name 
        FROM civicrm_state_province
        WHERE country_id =
          (SELECT id FROM civicrm_country WHERE iso_code='US')
      ";
      $dbres = CRM_Core_DAO::executeQuery($query);
      while ($dbres->fetch()) {
        $stateNameMap[$dbres->abbreviation] = $dbres->name;
      }
    }

    if (isset($stateNameMap[$abbr])) {
      return $stateNameMap[$abbr];
    }
    else {
      return NULL;
    }
  } // getStateName()

  private static function reformulate_preg_array($preg_res) {
    $res = [];
    foreach ($preg_res as $item) {
      $k = $item[0]; // save the 0-key value, which is the full pattern match
      // eliminate all numeric keys for the current item
      foreach ($item as $key => $val) {
        if (is_int($key)) {
          unset($item[$key]);
        }
      }
      $res[$k] = json_encode($item);
    }
    return $res;
  } // reformulate_preg_array()

  /**
   * @param $rows
   *
   * @return array
   *
   * given the array structure for multiple rows, extract and return a simple array
   * of row IDs
   */
  public static function getMultiRowIds($rows) {
    $rowIds = [];
    foreach ($rows as $row) {
      $rowIds[] = $row['row_id'];
    }

    return $rowIds;
  }

  static function sendActivityAssigneeEmail($params) {
    $mailStatus = ['status' => FALSE, 'msg' => ''];

    if (Civi::settings()->get('activity_assignee_notification')) {
      $activityIDs = [$params['id']];
      $assignees = [$params['assignee_contact_id']];
      $assigneeContacts = CRM_Activity_BAO_ActivityAssignment::getAssigneeNames($activityIDs, TRUE, FALSE);

      if (!CRM_Utils_Array::crmIsEmptyArray($assignees)) {
        $mailToContacts = [];

        // Build an associative array with unique email addresses
        foreach ($assignees as $id) {
          if (isset($id) && array_key_exists($id, $assigneeContacts)) {
            $mailToContacts[$assigneeContacts[$id]['email']] = $assigneeContacts[$id];
          }
        }

        $activity = new CRM_Activity_DAO_Activity();
        $activityParams = ['id' => $params['id']];
        $activity->copyValues($activityParams);

        if ($activity->find(TRUE)) {
          $sent = CRM_Activity_BAO_Activity::sendToAssignee($activity, $mailToContacts);
          if ($sent) {
            $mailStatus['status'] = TRUE;
            $mailStatus['msg'] = ts("A copy of the activity has also been sent to assignee contacts(s).");
          }
        }
      }

      /*Civi::log()->debug('sendActivityAssigneeEmail', array(
        '$params' => $params,
        '$activity' => $activity,
        '$mailToContacts' => $mailToContacts,
        '$mailStatus' => $mailStatus,
      ));*/
    }

    return $mailStatus;
  }

  public static function getUsageReport($range_low = NULL, $range_high = NULL) {
    $rangeSql = '';
    if ($range_low && $range_high) {
      $rangeSql = "AND (updated_date BETWEEN '" . $range_low . "' AND '" . $range_high . "')";
    }

    $query = "
      SELECT im.id, updated_date, email_date,
        CASE
          WHEN im.status = " . self::STATUS_UNMATCHED . " THEN 'unmatched'
          WHEN im.status = " . self::STATUS_MATCHED . " THEN 'matched'
          WHEN im.status = " . self::STATUS_CLEARED . " THEN 'cleared'
          WHEN im.status = " . self::STATUS_DELETED . " THEN 'deleted'
          WHEN im.status = " . self::STATUS_UNPROCESSED . " THEN 'unprocessed'
          ELSE 'unknown'
        END as status_icon_class,
        CASE
            WHEN im.status = " . self::STATUS_UNMATCHED . " THEN 'Unmatched'
            WHEN im.status = " . self::STATUS_MATCHED . " THEN CONCAT('Matched by ', IFNULL(matcher.display_name,'Unknown Contact'))
            WHEN im.status = " . self::STATUS_CLEARED . " THEN 'Cleared'
            WHEN im.status = " . self::STATUS_DELETED . " THEN 'Deleted'
            WHEN im.status = " . self::STATUS_UNPROCESSED . " THEN 'Unprocessed'
            ELSE 'Unknown Status'
        END as status_string,
        imm.matched_id matched_to, im.sender_email, im.subject, im.forwarder, imm.activity_id,
        im.matcher, im.status,
        IFNULL(count(ia.file_name), 0) as attachments,
        matcher.display_name as matcher_name,
        IFNULL(matched_to.display_name, im.sender_email) as fromName,
        matched_to.first_name as firstName, matched_to.last_name as lastName,
        matched_to.contact_type as contactType,
        COUNT(civi_t.id) as tagCount
      FROM nyss_inbox_messages as im
      LEFT JOIN nyss_inbox_messages_matched imm
        ON im.id = imm.row_id
      LEFT JOIN civicrm_contact as matcher
        ON im.matcher = matcher.id
      LEFT JOIN civicrm_contact as matched_to
        ON imm.matched_id = matched_to.id
      LEFT JOIN nyss_inbox_attachments ia
        ON im.id = ia.email_id
      LEFT JOIN civicrm_entity_tag as civi_et
        ON imm.activity_id = civi_et.entity_id
        AND civi_et.entity_table = 'civicrm_activity'
      LEFT JOIN civicrm_tag as civi_t
        ON civi_et.tag_id = civi_t.id
      WHERE im.status != 99 $rangeSql
      GROUP BY im.id, imm.id
      LIMIT 0 , 100000
    ";
    $dbres = CRM_Core_DAO::executeQuery($query);

    $msgs = [];
    $res = [
      'Total' => 0,
      'Unmatched' => 0,
      'Matched' => 0,
      'Cleared' => 0,
      'Deleted' => 0,
      'Errors' => 0,
      'Messages' => NULL,
    ];

    while ($dbres->fetch()) {
      $msg = self::processUsageRecords(get_object_vars($dbres));
      $msgs[] = $msg;
      $res['Total']++;

      switch ($msg['status']) {
        case self::STATUS_UNMATCHED:
          $res['Unmatched']++;
          break;
        case self::STATUS_MATCHED:
          $res['Matched']++;
          break;
        case self::STATUS_CLEARED:
          $res['Cleared']++;
          break;
        case self::STATUS_DELETED:
          $res['Deleted']++;
          break;
        default:
          $res['Errors']++;
          break;
      }
    }

    $res['Messages'] = $msgs;

    $return = [
      'is_error' => false,
      'message' => 'Report generated',
      /* For debugging purposes only! */
      /*'meta' => ['query' => $query],*/
      'data' => $res,
    ];

    return $return;
  }

  private static function processUsageRecords($fields) {
    $res = array();

    foreach ($fields as $key => $val) {
      /*** the old way of cleaning fields....
      $val = str_replace(chr(194).chr(160), ' ', $val);
      $val = htmlspecialchars_decode(stripslashes($val));
      $val = preg_replace('/[^a-zA-Z0-9\s\p{P}]/', '', trim($val));
      $val = substr($val, 0, 240);
       ***/
      if (in_array($key, array(
        'id',
        'message_id',
        'imap_id',
        'status',
        'matcher',
        'matched_to',
        'activity_id'))
      ) {
        // convert string ID to integer ID
        $res[$key] = (int)$val;
      }
      elseif (in_array($key, array('format', 'updated_date', 'email_date'))) {
        // no conversion necessary for these fields
        $res[$key] = $val;
      }
      else {
        // all other fields get converted to UTF-8
        $res[$key] = utf8_encode($val);
      }
    }

    // set various date formats
    $expandedDate = self::expandDate($res['updated_date']);
    $res['updated_date_long'] = $expandedDate['long'];
    $res['updated_date_short'] = $expandedDate['short'];
    $res['updated_date_unix'] = $expandedDate['unix'];

    $expandedDate = self::expandDate($res['email_date']);
    $res['email_date_long'] = $expandedDate['long'];
    $res['email_date_short'] = $expandedDate['short'];
    $res['email_date_unix'] = $expandedDate['unix'];

    return $res;
  } // processUsageRecords()

  /**
   * Given a textual date, return date in multiple formats
   * @param  [string] $date The date to be converted
   * @return [array] The date converted to three formats: unix, long, short
   */
  private static function expandDate($date)
  {
    $unixTime = strtotime($date);

    if (date('Ymd') == date('Ymd', $unixTime)) {
      // if provided date is today
      $shortForm = 'Today '.date('h:i A', $unixTime);
    }
    else if (date('Y') == date('Y', $unixTime)) {
      // if provided date is this year
      $shortForm = date('M d h:i A', $unixTime);
    }
    else {
      $shortForm = date('M d, Y', $unixTime);
    }

    return array('unix' => $unixTime,
      'long' => date('M d, Y h:i A', $unixTime),
      'short' => $shortForm);
  } // expandDate()
}
