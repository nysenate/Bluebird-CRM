<?php

class CRM_NYSS_Inbox_BAO_Inbox {
  const STATUS_UNMATCHED = 0;
  const STATUS_MATCHED = 1;
  const STATUS_CLEARED = 7;
  const STATUS_DELETED = 9;
  const STATUS_UNPROCESSED = 99;

  const USPS_AMS_URL = 'http://geo.nysenate.gov:8080/usps-ams/api/citystate?batch=true';

  const DEFAULT_ACTIVITY_STATUS = 'Completed';
  const DEFAULT_ACTIVITY_TYPE = 'Inbound Email';
  const DEFAULT_AUTH_GROUP = 'Authorized_Forwarders';
  const DEFAULT_CONTACT_ID = 1;

  const POSITION_PARENT_ID = 292;
  const KEYWORD_PARENT_ID = 296;

  /**
   * add common resources
   */
  static function addResources($type = NULL) {
    CRM_Core_Resources::singleton()->addScriptUrl('/sites/default/themes/Bluebird/scripts/bbtree.js');
    CRM_Core_Resources::singleton()->addStyleFile('gov.nysenate.inbox', 'css/inbox.css');
    CRM_Core_Resources::singleton()->addStyleUrl('/sites/default/themes/Bluebird/css/tags/tags.css');

    //add type-specific resources
    switch ($type) {
      case 'unmatched':
        CRM_Core_Resources::singleton()->addScriptFile('gov.nysenate.inbox', 'js/inbox_unmatched.js');
        break;
      case 'matched':
        CRM_Core_Resources::singleton()->addScriptFile('gov.nysenate.inbox', 'js/inbox_matched.js');
        break;
      default:
    }
  }

  /**
   * @param $string
   * @param int $limit
   * @param string $pad
   * @return string
   *
   * trim and truncate the string to the configured length
   * also fix possible encoding issues
   */
  static function cleanText($string, $limit = 0, $pad = "...") {
    $lib = CRM_Core_Resources::singleton()->getPath('gov.nysenate.inbox', 'incl/htmlfixer.class.php');
    require_once "{$lib}";

    $string = trim($string);
    $string = mb_convert_encoding($string, "HTML-ENTITIES", "UTF-8");

    $htmlFixer = new HtmlFixer();
    $string = $htmlFixer->getFixedHtml($string);

    // return with no change if string is shorter than $limit
    if(strlen($string) <= $limit) return $string;

    //truncate
    if ($limit > 0) {
      $string = substr($string, 0, $limit) . $pad;
    }

    return $string;
  }

  /**
   * @param $id
   * @return array
   *
   * get details for a single inbox polling row
   */
  static function getDetails($id) {
    $details = array();

    $sql = "
      SELECT im.id, im.sender_name, im.sender_email, im.subject, im.forwarder,
        im.updated_date, im.email_date, im.body, im.status,
        IFNULL(count(ia.file_name), '0') as attachments,
        count(e.id) AS email_count
      FROM nyss_inbox_messages im
      LEFT JOIN civicrm_email e 
        ON im.sender_email = e.email
      LEFT JOIN nyss_inbox_attachments ia 
        ON im.id = ia.email_id
      WHERE im.id = %1
      GROUP BY im.id
    ";
    $dao = CRM_Core_DAO::executeQuery($sql, array(
      1 => array($id, 'Integer'),
    ));

    while ($dao->fetch()) {
      $attachment = (!empty($dao->attachments)) ? "<div class='icon attachment-icon attachment' title='{$dao->attachments} Attachment(s)'></div>" : '';
      $details = array(
        'id' => $dao->id,
        'sender_name' => $dao->sender_name,
        'sender_email' => $dao->sender_email,
        'email_count' => $dao->email_count,
        'subject' => CRM_NYSS_Inbox_BAO_Inbox::cleanText($dao->subject).$attachment,
        'date_email' => date('m/d/Y', strtotime($dao->email_date)),
        'updated_date' => date('m/d/Y', strtotime($dao->updated_date)),
        'forwarded_by' => $dao->forwarder,
        'body' => CRM_NYSS_Inbox_BAO_Inbox::cleanText($dao->body),
      );
    }

    //Civi::log()->debug('getDetails', array('$details' => $details));
    return $details;
  }

  static function deleteMessages($ids = array()) {
    if (empty($ids) && !empty(CRM_Utils_Array::value('ids', $_REQUEST))) {
      $ids = CRM_Utils_Array::value('ids', $_REQUEST);
    }

    $idList = implode(',', $ids);
    $userId = CRM_Core_Session::getLoggedInContactID();

    if (!empty($idList) && !empty($userId)) {
      CRM_Core_DAO::executeQuery("
        UPDATE nyss_inbox_messages
        SET status = " . self::STATUS_DELETED . ", matcher = %1
        WHERE id IN ({$idList})
      ", array(
        1 => array($userId, 'Positive'),
      ));
    }
  }

  /**
   * @param $params
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
    $orderBy = (!empty($params['sort'])) ? $params['sort'] : 'updated_date DESC';

    //build range SQL; range = 0 -> all time (no where clause)
    $rangeSql = '';
    if ($params['range'] > 0) {
      $rangeSql = "AND (updated_date BETWEEN '".date('Y-m-d H:i:s', strtotime('-'.$params['range'].' days'))."' AND '".date('Y-m-d H:i:s')."')";
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
        $statusSql = 'AND im.status = '.self::STATUS_UNMATCHED;
        break;
      case 'matched':
        $statusSql = 'AND im.status = '.self::STATUS_MATCHED;
        break;
      default:
        $statusSql = '';
        break;
    }

    $sql = "
      SELECT SQL_CALC_FOUND_ROWS im.id, im.sender_name, im.sender_email, im.subject, im.forwarder,
        im.updated_date, im.email_date,
        IFNULL(count(ia.file_name), '0') as attachments,
        count(e.id) AS email_count
      FROM nyss_inbox_messages im
      LEFT JOIN civicrm_email e 
        ON im.sender_email = e.email
      LEFT JOIN nyss_inbox_attachments ia 
        ON im.id = ia.email_id
      WHERE (1)
        {$statusSql}
        {$rangeSql}
        {$termSql}
      GROUP BY im.id
      ORDER BY {$orderBy}
      LIMIT {$params['rowCount']}
      OFFSET {$params['offset']}
    ";
    //Civi::log()->debug('getMessages', array('sql' => $sql));
    $dao = CRM_Core_DAO::executeQuery($sql);
    //Civi::log()->debug('getMessages', array('$dao' => $dao));

    // Add total.
    $params['total'] = CRM_Core_DAO::singleValueQuery('SELECT FOUND_ROWS();');

    $msgs = array();
    if ($dao->N) {
      while ($dao->fetch()) {
        $msg = array();
        $matchCount = (!empty($dao->email_count)) ? 'multi' : 'empty';
        $attachment = (!empty($dao->attachments)) ?
          "<div class='icon attachment-icon attachment' title='{$dao->attachments} Attachment(s)'></div>" : '';
        $senderEmail = (empty($dao->sender_email)) ? '' :
          "<span class='emailbubble'>".CRM_NYSS_Inbox_BAO_Inbox::cleanText($dao->sender_email, 15)."</span>";
        $senderName = CRM_NYSS_Inbox_BAO_Inbox::cleanText($dao->sender_name, 15);

        $msg['DT_RowId'] = "message-{$dao->id}";
        $msg['DT_RowClass'] = 'crm-entity';
        $msg['DT_RowAttr'] = array();
        $msg['DT_RowAttr']['data-entity'] = 'message';
        $msg['DT_RowAttr']['data-id'] = $dao->id;

        $msg['id'] = "<input class='message-select' type='checkbox' id='select-{$dao->id}'>";
        $msg['sender_name'] = "{$senderName}{$senderEmail}
          <span class='matchbubble {$matchCount}'>{$dao->email_count}</span>";
        $msg['subject'] = CRM_NYSS_Inbox_BAO_Inbox::cleanText($dao->subject, 25).$attachment;
        $msg['updated_date'] = date('M d, Y', strtotime($dao->updated_date));
        $msg['forwarder'] = $dao->forwarder;


        switch ($status) {
          case 'unmatched':
            $urlAssign = CRM_Utils_System::url('civicrm/nyss/inbox/assigncontact', "reset=1&id={$dao->id}");
            $links['assign'] =
              "<a href='{$urlAssign}' class='action-item crm-hover-button crm-popup inbox-assign-contact'>Assign Contact</a>";
            break;
          case 'matched':
            $urlProcess = CRM_Utils_System::url('civicrm/nyss/inbox/assigncontact', "reset=1&id={$dao->id}");
            $urlClear = CRM_Utils_System::url('civicrm/nyss/inbox/assigncontact', "reset=1&id={$dao->id}");
            $links['process'] =
              "<a href='{$urlProcess}' class='action-item crm-hover-button crm-popup inbox-process-contact'>Process</a>";
            $links['clear'] =
              "<a href='{$urlClear}' class='action-item crm-hover-button crm-popup inbox-clear-contact'>Clear</a>";
            break;
          default:
        }

        $urlDelete = CRM_Utils_System::url('civicrm/nyss/inbox/delete', "reset=1&id={$dao->id}");
        $links['delete'] = "<a href='{$urlDelete}' class='action-item crm-hover-button crm-popup inbox-delete'>Delete</a>";

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

  /**
   * @param $messageId
   * @param $contactId
   * @return array
   */
  static function assignMessage($messageId, $contactId) {
    if (empty($messageId) || empty($contactId)) {
      return array(
        'is_error' => TRUE,
        'message' => 'Unable to assign the message; missing required values.'
      );
    }

    $message = self::getDetails($messageId);

    if ($message['status'] != self::STATUS_UNMATCHED) {
      return array(
        'is_error' => TRUE,
        'message' => 'Unable to assign the message; it has already been matched.'
      );
    }

    $bbconfig = get_bluebird_instance_config();
    $status = self::DEFAULT_ACTIVITY_STATUS;
    if (isset($bbconfig['imap.activity.status.default'])) {
      $status = $bbconfig['imap.activity.status.default'];
    }

    $forwarder = self::getForwarder($message['forwarded_by']);

    $params = array(
      'activity_label' => 'Inbound Email',
      'source_contact_id' => $forwarder,
      'target_contact_id' => $contactId,
      'subject' => $message['subject'],
      'is_auto' => 0,
      'status_id' => $status,
      'activity_date_time' => $message['date_updated'],
      'details' => $message['body'],
    );
    try {
      $activity = civicrm_api3('activity', 'create', $params);
      $attachments = CRM_NYSS_Inbox_BAO_Inbox::getAttachments($messageId);
      $uploadDir = CRM_Core_Config::singleton()->customFileUploadDir;

      if (!empty($attachments)) {
        foreach ($attachments as $key => $attachment) {
          $attachmentName = $attachment['fileName'];
          $attachmentFull = $attachment['fileFull'];

          if (file_exists($attachmentFull)) {
            $fileName = CRM_Utils_File::makeFileName($attachmentName);
            $fileFull = $uploadDir.$fileName;

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
      } // if attachments
    }
    catch (CiviCRM_API3_Exception $e) {
      Civi::log()->error('assignMessage', array('e' => $e));

      return array(
        'is_error' => TRUE,
        'message' => 'Unable to create the activity.'
      );
    }

    //update the message record
    CRM_Core_DAO::executeQuery("
      UPDATE nyss_inbox_messages
      SET status = %1,
        matcher = %2,
        matched_to = %3
      WHERE id = %4
    ", array(
      1 => array(self::STATUS_MATCHED, 'Integer'),
      2 => array($forwarder, 'Integer'),
      3 => array($contactId, 'Integer'),
      4 => array($messageId, 'Integer'),
    ));

    $contactName = civicrm_api3('contact', 'getvalue', array(
      'id' => $contactId,
      'return' => 'display_name',
    ));
    return array(
      'is_error' => FALSE,
      'message' => "Message successfully assigned to {$contactName}.",
    );
  }

  /**
   * @param $email
   * @return int|null|string
   */
  static function getForwarder($email) {
    $forwarderId = CRM_Core_DAO::singleValueQuery("
      SELECT e.contact_id
      FROM civicrm_group_contact gc, civicrm_group g, civicrm_email e
      WHERE g.name = '".self::DEFAULT_AUTH_GROUP."'
        AND e.email = %1
        AND g.id = gc.group_id
        AND gc.status = 'Added'
        AND gc.contact_id = e.contact_id
      ORDER BY gc.contact_id ASC
      LIMIT 1
    ", array(
      1 => array($email, 'String'),
    ));

    if (empty($forwarderId)) {
      $forwarderId = self::DEFAULT_CONTACT_ID;
    }

    return $forwarderId;
  }

  /**
   * @param $messageId
   * @return array
   *
   * given a messageId, retrieve all attachment details
   */
  static function getAttachments($messageId) {
    $dao = CRM_Core_DAO::executeQuery("
      SELECT * 
      FROM nyss_inbox_attachments 
      WHERE email_id = %1
    ", array(
      1 => array($messageId, 'Integer'),
    ));

    $attachments = array();
    while ($dao->fetch()) {
      $attachments[] = array(
        'fileName' => $dao->file_name,
        'fileFull' => $dao->file_full,
        'rejection' => $dao->rejection,
        'size' => $dao->size,
        'ext' => $dao->ext,
      );
    }

    return $attachments;
  }
}
