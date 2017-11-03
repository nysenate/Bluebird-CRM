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
    CRM_Core_Resources::singleton()->addScriptFile('gov.nysenate.inbox', 'js/inbox.js');
    CRM_Core_Resources::singleton()->addStyleFile('gov.nysenate.inbox', 'css/inbox.css');

    //pass type as js var
    CRM_Core_Resources::singleton()->addVars('NYSS', array('inboxType' => $type));

    //add type-specific resources
    switch ($type) {
      case 'unmatched':
        CRM_Core_Resources::singleton()->addScriptFile('gov.nysenate.inbox', 'js/inbox_unmatched.js');
        break;
      case 'matched':
        CRM_Core_Resources::singleton()->addScriptFile('gov.nysenate.inbox', 'js/inbox_matched.js');
        break;
      case 'process':
        CRM_Core_Resources::singleton()->addScriptFile('gov.nysenate.inbox', 'js/inbox_process.js');
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
      SELECT im.id, im.message_id, im.sender_name, im.sender_email, im.subject, im.body, im.forwarder,
        im.status, im.matcher, im.matched_to, im.activity_id, im.updated_date, im.email_date, 
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
      $attachment = (!empty($dao->attachments)) ?
        "<div class='icon attachment-icon attachment' title='{$dao->attachments} Attachment(s)'></div>" : '';
      $matched = self::getMatched($dao->matched_to);
      $body = CRM_NYSS_Inbox_BAO_Inbox::cleanText($dao->body);
      $parsed = self::parseMessage($body);
      $details = array(
        'id' => $dao->id,
        'message_id' => $dao->message_id,
        'sender_name' => $dao->sender_name,
        'sender_email' => $dao->sender_email,
        'subject' => CRM_NYSS_Inbox_BAO_Inbox::cleanText($dao->subject),
        'subject_display' => CRM_NYSS_Inbox_BAO_Inbox::cleanText($dao->subject).$attachment,
        'body_raw' => $body,
        'body' => self::highlightItems($body, $parsed),
        'forwarded_by' => $dao->forwarder,
        'status' => $dao->status,
        'matcher' => $dao->matcher,
        'matched_to' => $dao->matched_to,
        'matched_to_display' => $matched,
        'activity_id' => $dao->activity_id,
        'updated_date' => date('m/d/Y', strtotime($dao->updated_date)),
        'date_email' => date('m/d/Y', strtotime($dao->email_date)),
        'attachments' => $dao->attachments,
        'email_count' => $dao->email_count,
      );
    }

    //Civi::log()->debug('getDetails', array('$details' => $details));
    return $details;
  }

  /**
   * @param array $ids
   *
   * retrieve list of ids to delete
   * either passed to function or via $_REQUEST (AJAX)
   */
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
   * @param array $ids
   *
   * retrieve list of ids to clear
   * either passed to function or via $_REQUEST (AJAX)
   */
  static function clearMessages($ids = array()) {
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
        im.updated_date, im.email_date, im.matcher, im.matched_to, mc.display_name matcher_name,
        IFNULL(count(ia.file_name), '0') as attachments,
        count(e.id) AS email_count
      FROM nyss_inbox_messages im
      LEFT JOIN civicrm_email e 
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
        $senderName = CRM_NYSS_Inbox_BAO_Inbox::cleanText($dao->sender_name, 15);

        $msg['DT_RowId'] = "message-{$dao->id}";
        $msg['DT_RowClass'] = 'crm-entity';
        $msg['DT_RowAttr'] = array();
        $msg['DT_RowAttr']['data-entity'] = 'message';
        $msg['DT_RowAttr']['data-id'] = $dao->id;

        $msg['id'] = "<input class='message-select' type='checkbox' id='select-{$dao->id}'>";

        //sender's info varies based on matched/unmatched view
        switch ($status) {
          case 'unmatched':
            $senderEmail = (empty($dao->sender_email)) ? '' :
              "<span class='emailbubble'>".CRM_NYSS_Inbox_BAO_Inbox::cleanText($dao->sender_email, 15)."</span>";
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
            $matchedUrl = CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid={$dao->matched_to}");
            $msg['sender_name'] = "<a href='{$matchedUrl}'>{$senderName}</a>
              <span class='matchbubble {$matchTypeCSS}' title='This email was {$matchString}'>{$matchTypeText}</span>";
            break;

          default:
            $msg['sender_name'] = $senderName;
        }

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
            $urlProcess = CRM_Utils_System::url('civicrm/nyss/inbox/process', "reset=1&id={$dao->id}");
            $urlClear = CRM_Utils_System::url('civicrm/nyss/inbox/clear', "reset=1&id={$dao->id}");
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
      'subject' => substr($message['subject'], 0, 250),
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
      }

      //store activity_id in messages table
      CRM_Core_DAO::executeQuery("
        UPDATE nyss_inbox_messages
        SET activity_id = %1
        WHERE id = %2
      ", array(
        1 => array($activity['id'], 'Positive'),
        2 => array($messageId, 'Positive'),
      ));
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
   * @param $params
   * @return array
   *
   * process message record: assignment, contact tags, activity tags, activity details
   */
  static function processMessages($values) {
    $msg = array();

    if (!empty($values['assignee'])) {
      CRM_Core_DAO::executeQuery("
        UPDATE nyss_inbox_messages
        SET matched_to = %1
        WHERE id = %2
      ", array(
        1 => array($values['assignee'], 'Integer'),
        2 => array($values['id'], 'Integer'),
      ));
    }

    if (!empty($values['contact_keywords'])) {
      foreach (explode(',', $values['contact_keywords']) as $tagID) {
        try {
          civicrm_api3('entity_tag', 'create', array(
            'entity_id' => (!empty($values['assignee'])) ? $values['assignee'] : $values['current_assignee'],
            'tag_id' => $tagID,
            'entity_table' => 'civicrm_contact',
          ));
        }
        catch (CiviCRM_API3_Exception $e) {
          //Civi::log()->debug('processMessages contact keywords', array('e' => $e));
          //$msg[] = 'Unable to assign all keywords to the contact.';
        }
      }
    }

    //TODO retrieve via request better
    if (!empty($_REQUEST['tag'])) {
      foreach ($_REQUEST['tag'] as $tagID => $dontCare) {
        try {
          civicrm_api3('entity_tag', 'create', array(
            'entity_id' => (!empty($values['assignee'])) ? $values['assignee'] : $values['current_assignee'],
            'tag_id' => $tagID,
            'entity_table' => 'civicrm_contact',
          ));
        }
        catch (CiviCRM_API3_Exception $e) {
          //Civi::log()->debug('processMessages contact issue codes', array('e' => $e));
          //$msg[] = 'Unable to assign all issue codes to the contact.';
        }
      }
    }

    if (!empty($values['contact_positions'])) {
      foreach (explode(',', $values['contact_positions']) as $tagID) {
        try {
          civicrm_api3('entity_tag', 'create', array(
            'entity_id' => (!empty($values['assignee'])) ? $values['assignee'] : $values['current_assignee'],
            'tag_id' => $tagID,
            'entity_table' => 'civicrm_contact',
          ));
        }
        catch (CiviCRM_API3_Exception $e) {
          //Civi::log()->debug('processMessages contact positions', array('e' => $e));
          //$msg[] = 'Unable to assign all positions to the contact.';
        }
      }
    }

    //ensure we have an activity ID before processing these
    if ($values['activity_id']) {
      if (!empty($values['activity_keywords'])) {
        foreach (explode(',', $values['activity_keywords']) as $tagID) {
          try {
            civicrm_api3('entity_tag', 'create', array(
              'entity_id' => $values['activity_id'],
              'tag_id' => $tagID,
              'entity_table' => 'civicrm_activity',
            ));
          }
          catch (CiviCRM_API3_Exception $e) {
            //Civi::log()->debug('processMessages activity keywords', array('e' => $e));
            //$msg[] = 'Unable to assign all keywords to the activity.';
          }
        }
      }

      if (!empty($values['activity_assignee']) || !empty($values['activity_status'])) {
        $params = array('id' => $values['activity_id']);

        if (!empty($values['activity_assignee'])) {
          $params['assignee_contact_id'] = $values['activity_assignee'];
        }

        if (!empty($values['activity_status'])) {
          $params['status_id'] = $values['activity_status'];
        }

        try {
          civicrm_api3('activity', 'create', $params);
        }
        catch (CiviCRM_API3_Exception $e) {
          Civi::log()->debug('processMessages create activity', array('e' => $e));
          $msg[] = 'Unable to update activity record.';
        }
      }
    }

    $msg = array_filter($msg);
    return $msg;
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

  /**
   * @param $cid
   * @return null|string
   */
  static function getMatched($cid) {
    if (empty($cid)) {
      return NULL;
    }

    try {
      $contact = civicrm_api3('contact', 'getsingle', array(
        'id' => $cid,
      ));

      $matchedUrl = CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid={$cid}");
      $matchedContact = "<a href='{$matchedUrl}'>{$contact['display_name']}</a><br /><{$contact['email']}>";

      return $matchedContact;
    }
    catch (CiviCRM_API3_Exception $e) {}

    return NULL;
  }

  /**
   * Generate an array of e-mail addresses, phone numbers, city/state/zips,
   * and proper names that were found in the message body.
   */
  private static function parseMessage($msgBody) {
    $res = array();

    // Convert message body into tagless text.
    $text = preg_replace('/<(p|br)[^>]*>|\r/', "\n", $msgBody);
    $text = strip_tags($text);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML401, 'ISO-8859-1');

    // Find email addresses within the text.
    preg_match_all('/[\w\.\-\+]+@[a-z\d\-]+(\.[a-z\d\-]+)*/i', $text, $emails);
    $res['emails'] = array_unique($emails[0]);

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
        $dbres = CRM_Core_DAO::executeQuery($query);
        if ($dbres->count_id < 1) {
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
    $itemMap = array(
      'emails' => array('class' => 'email_address', 'text' => 'email'),
      'phones' => array('class' => 'phone', 'text' => 'phone number'),
      'addrs' => array('class' => 'zip', 'text' => 'city/state/zip'),
      'names' => array('class' => 'name', 'text' => 'name')
    );

    foreach ($items as $itemType => $itemList) {
      $itemClass = $itemMap[$itemType]['class'];
      $itemText = $itemMap[$itemType]['text'];

      if (count($itemList) == 0) {
        continue;
      }

      if ($itemType == 'emails' || $itemType == 'phones') {
        $re = implode('###', $itemList);
        $re = preg_quote($re);
        $re = '/'.preg_replace('/###/', '|', $re).'/';
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
    static $stateNameMap = null;

    if ($stateNameMap == null) {
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
      return null;
    }
  } // getStateName()

  private static function reformulate_preg_array($preg_res) {
    $res = array();
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
}
