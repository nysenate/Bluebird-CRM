<?php
// AJAX.php
// Project: BluebirdCRM
// Authors: Stefan Crain, Ken Zalewski
// Organization: New York State Senate
// Revised: 2013-12-16
// Revised: 2015-02-26 - fixed JSON encoding; tons of code cleanup
// Revised: 2015-03-09 - simplified and enhanced message parser
// Revised: 2015-03-20 - finished up Matched and Report interfaces
// Revised: 2015-03-26 - bug fixes and better error handling

class CRM_NYSS_IMAP_AJAX
{
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
   * Occasionally we need the raw database connection to do
   * some processing, this will get the database connection from
   * CiviCRM and set it to a static variable.
   * @return None
   */
  private static function db() {
    static $mysql_conn = null;

    // Load the DAO Object and pull the connection
    if ($mysql_conn == null) {
      $nyss_conn = new CRM_Core_DAO();
      $mysql_conn = $nyss_conn->getDatabaseConnection()->connection;
    }
    return $mysql_conn;
  }//db()


  /**
   * We want to be able to escape the string so when we use
   * the key in a query, it is already sanitized.
   * @param  [string] $key  The name of the input in the GET message.
   * @return [string] The escaped string.
   */
  private static function get($key) {
    if (isset($_GET[$key])) {
      return CRM_Core_DAO::escapeString($_GET[$key]);
    }
    else {
      return null;
    }
  } // get()


  /**
   * This function assumes that the keyed value is a comma-delimited string
   * of values, and returns an array of those values.
   *
   * Note that array elements that contain no value are removed.  This includes
   * array elements with all whitespace.
   */
  private static function get_array($key) {
    $val = self::get($key);
    return array_filter(explode(',', $val), function ($v) {
      return trim($v) !== '';
    });
  } // get_array()


  private static function getContactFields() {
    $fields = array(
      'prefix'=>null,
      'first_name'=>null,
      'middle_name'=>null,
      'last_name'=>null,
      'suffix'=>null,
      'dob'=>null,
      'email_address'=>null,
      'phone'=>null,
      'street_address'=>null,
      'street_address_2'=>null,
      'city'=>null,
      'state'=>null,
      'postal_code'=>null
    );

    // Fill in the $fields[] array with our search criteria.
    foreach ($fields as $fldname => $fldval) {
      $formval = trim(self::get($fldname));
      if ($fldname == 'dob') {
        if ($formval && $formval != 'yyyy-mm-dd') {
          $ymd = date('Y-m-d', strtotime($formval));
          if ($ymd != '1969-12-31') {     // block Epoch date
            $fields[$fldname] = date('Y-m-d', strtotime($formval));
          }
        }
      }
      else {
        $label = str_replace('_', ' ', strtolower($fldname));
        if ($formval && $formval != $label) {
          $fields[$fldname] = $formval;
        }
      }
    }

    return $fields;
  } // getContactFields()


  /**
   * Format message details from DB
   * @param  [int] $messageId The ID of the message being retrieved.
   * @return [array] Array of fields with all information about the message,
   *   or NULL if the message cannot be located by ID.
   */
  private static function getFullMessage($messageId) {
    $debug = self::get('debug');

    $query = "
      SELECT * 
      FROM nyss_inbox_messages 
      WHERE id = $messageId
    ";
    $res = CRM_Core_DAO::executeQuery($query);

    $row = array();
    while ($res->fetch()) {
      $row = get_object_vars($res);
    }
    //CRM_Core_Error::debug_var('$query: ', $query);
    //CRM_Core_Error::debug_var('row: ', $row);

    if (empty($row)) {
      return NULL;
    }

    $msgFields = self::postProcess($row);

    // useful when setting status for other unmatched messages
    $senderEmail = $msgFields['sender_email'];
    $msgFields['key'] = $senderEmail;

    // find matches
    $query = "
      SELECT c.id, e.email
      FROM civicrm_contact c
      LEFT JOIN civicrm_email e
        ON c.id = e.contact_id
      WHERE c.is_deleted=0
        AND e.email LIKE '$senderEmail'
      GROUP BY c.id, e.email
    ";
    $res = CRM_Core_DAO::executeQuery($query);
    $msgFields['matches_count'] = $res->N;

    // attachments
    $attachments = array();
    $query = "
      SELECT * 
      FROM nyss_inbox_attachments 
      WHERE email_id = $messageId
    ";
    $res = CRM_Core_DAO::executeQuery($query);
    while ($res->fetch()) {
      $attachments[] = array(
        'fileName' => $res->file_name,
        'fileFull' => $res->file_full,
        'rejection' => $res->rejection,
        'size' => $res->size,
        'ext' => $res->ext,
      );
    }

    $msgFields['attachments'] = $attachments;

    if ($debug) {
      echo "\n<h1>Full Email OUTPUT</h1>\n<pre>\n";
      var_dump($msgFields);
      echo "\n</pre>\n";
    }

    //CRM_Core_Error::debug_var('getFullMessage $msgFields: ', $msgFields);
    return $msgFields;
  } // getFullMessage()


  private static function getMessageList($list_type)
  {
    $startTime = microtime(true);
    $debug = self::get('debug');
    $range = self::get('range');
    $resultData = array();

    $rangeSql = '';
    if (is_numeric($range) && $range >= 1) {
      $rangeSql = "AND (updated_date BETWEEN '".date('Y-m-d H:i:s', strtotime('-'.$range.' days'))."' AND '".date('Y-m-d H:i:s')."')";
    }

    if ($list_type == self::STATUS_UNMATCHED) {
      $list_key = 'Unprocessed';
      $query = "
        SELECT im.id, im.sender_name, im.sender_email, im.subject, im.forwarder,
               im.updated_date, im.email_date,
               IFNULL(count(ia.file_name), '0') as attachments,
               count(e.id) AS email_count
        FROM nyss_inbox_messages im
        LEFT JOIN civicrm_email e ON im.sender_email = e.email
        LEFT JOIN nyss_inbox_attachments ia ON im.id = ia.email_id
        WHERE im.status = ".self::STATUS_UNMATCHED." $rangeSql
        GROUP BY im.id";
    }
    elseif ($list_type == self::STATUS_MATCHED) {
      $list_key = 'Processed';
      $query = "
        SELECT im.id, im.sender_name, im.sender_email, im.subject, im.forwarder,
               im.matcher, im.matched_to, im.activity_id,
               im.updated_date, im.email_date,
               IFNULL(count(ia.file_name), '0') as attachments,
               c1.display_name as matcher_name,
               c2.display_name as fromName, c2.first_name as firstName,
               c2.last_name as lastName, c2.contact_type as contactType
        FROM nyss_inbox_messages im
        LEFT JOIN civicrm_contact c1 ON im.matcher = c1.id
        LEFT JOIN civicrm_contact c2 ON im.matched_to = c2.id
        LEFT JOIN nyss_inbox_attachments ia ON im.id = ia.email_id
        WHERE im.status = ".self::STATUS_MATCHED." $rangeSql
        GROUP BY im.id";
    }
    else {
      return null;
    }

    $dao = CRM_Core_DAO::executeQuery($query);
    //Civi::log()->debug('CRM_NYSS_IMAP_AJAX', array('query' => $query, 'dao' => $dao));

    $msgCount = 0;
    while ($dao->fetch()) {
      $id = $dao->id;
      $resultData[$list_key][$id] = self::postProcess($dao);
      $msgCount++;
    }

    $endTime = microtime(true);
    $resultData['stats']['overview']['successes'] = $msgCount;
    $resultData['stats']['overview']['time'] = $endTime - $startTime;

    // Encode the messages variable and return it to the AJAX call
    if ($debug) {
      echo "\n<pre>\n";
      print_r($resultData);
      echo "\n</pre>\n";
    }
    //Civi::log()->debug('getMessageList', array('resultData' => $resultData));//NYSS

    return $resultData;
  } // getMessageList()


  private static function postProcess($fields) {
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
  } // postProcess()


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


  /**
   * Get full details about Contact, Activity, or Tag
   * @param  [string] $type The type of object you are searching for
   * @param  [int]  $id The id you are searching for
   * @return [array]  CIVI generated array
   */
  private static function getCiviObject($type, $id)
  {
    require_once 'api/api.php';
    $params = array('version' => 3, 'activity' => 'get', 'id' => $id);
    return civicrm_api($type, 'get', $params);
  } // getCiviObject()


  /**
   * Find possible city/states from zipcodes with USPS-AMS service.
   * Docs here: http://geo.nysenate.gov:8080/usps-ams/docs/
   * @param [array] $zipcodes An array of zipcodes
   * @return [array] An array of results, where each result is an array
   *         that contains 'cityName' and 'stateAbbr' elements
   */
  private static function getCities($zipcodes)
  {
    $jsonZips = json_encode($zipcodes);

    $ch = curl_init(self::USPS_AMS_URL);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonZips);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            'Content-Type: application/json',
                            'Content-Length: '.strlen($jsonZips)));
    $res = json_decode(curl_exec($ch), true);
    return array_unique($res['results'], SORT_REGULAR);
  } // getCities()


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

  private static function getOtherLocationId() {
    $query = "
      SELECT id 
      FROM civicrm_location_type 
      WHERE name = 'Other'
    ";
    $id = CRM_Core_DAO::singleValueQuery($query);
    return $id;
  } // getOtherLocationId()

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


  /**
   * Generate an array of e-mail addresses, phone numbers, city/state/zips,
   * and proper names that were found in the message body.
   */
  private static function parseMessage($msgBody)
  {
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
    preg_match_all('/(?<city>[A-Z][A-Za-z\-\.\ ]+[a-z])\h*,\h*(?<stateAbbr>[A-Z]{2})\h+(?<zip>\d{5}(?:\-\d{4})?)/', $text, $addresses, PREG_SET_ORDER);
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
        $text = preg_replace($re, "<span class='found $itemClass' data-search='$0' title='Click to use this $itemText'>$0</span>", $text);
      }
      else {
        foreach ($itemList as $search => $json) {
          $re = preg_quote($search);
          $text = preg_replace("/$re/", "<span class='found $itemClass' data-json='$json' title='Click to use this $itemText'>$0</span>", $text);
        }
      }
    }
    return $text;
  } // highlightItems()


  /**
   * Add a group of tags to either a contact or an activity.
   */
  private static function addEntityTags($entityId, $entityTab, $parentId, $tags)
  {
    require_once 'api/api.php';
    $existingTags = CRM_Core_BAO_EntityTag::getTag($entityId, $entityTab);
    $entityTagIds = array();

    foreach ($tags as $tagId) {
      if (!is_numeric($tagId)) {
        // check if user has selected existing tag or is creating new tag
        // this is done to allow numeric tags etc.
        $tagValue = explode(':::', $tagId);
        if (isset($tagValue[1]) && $tagValue[1] == 'value') {
          // does tag already exist ?
          $params = array('version' => 3,
                          'activity' => 'get',
                          'name' => $tagValue[0],
                          'parent_id' => $parentId);
          $apires = civicrm_api('tag', 'get', $params);
          if ($apires['count'] != 0) {
            $tagId = strval($apires['id']);
          }
          else {
            if ($parentId == self::KEYWORD_PARENT_ID) {
              $used_for = 'civicrm_contact,civicrm_activity,civicrm_case';
            }
            else {
              $used_for = $entityTab;
            }
            $params = array(
              'name' => $tagValue[0],
              'parent_id' => $parentId,
              'used_for' => $used_for
            );
            $tagObj = CRM_Core_BAO_Tag::add($params, CRM_Core_DAO::$_nullArray);
            $tagId = strval($tagObj->id);
          }
        }
      }
      if (!array_key_exists($tagId, $existingTags)) {
        $entityTagIds[] = $tagId;
      }
    }

    $tagCount = 0;
    if (!empty($entityTagIds)) {
      // New tag ids can be inserted directly into the db table.
      $insertValues = array();
      foreach ($entityTagIds as $tagId) {
        $insertValues[] = "( $tagId, $entityId, '$entityTab' )";
        $tagCount++;
      }
      $query = "
        INSERT INTO civicrm_entity_tag
        (tag_id, entity_id, entity_table)
        VALUES ".implode(',', $insertValues);
      CRM_Core_DAO::executeQuery($query);
    }
    return $tagCount;
  } // addEntityTags()


  private static function exitSuccess($msg, $data = null)
  {
    self::exitOutputJson(false, $msg, $data);
  } // exitSuccess()


  private static function exitError($msg, $data = null)
  {
    self::exitOutputJson(true, $msg, $data);
  } // exitError()


  private static function exitOutputJson($isError, $msg, $data = null)
  {
    $res = array('is_error'=>$isError, 'message'=>$msg);
    if ($data !== null) {
      $res['data'] = $data;
    }

    $jsonResult = json_encode($res);
    if ($jsonResult === false) {
      $res = array('is_error'=>true, 'message'=>'Fatal JSON encoding error');
      echo json_encode($res);
    }
    else {
      echo $jsonResult;
    }
    CRM_Utils_System::civiExit();
  } // exitOutputJson()



  /**
   * This function grabs all of the messages previously processed by the
   * process mailbox polling script and formats a JSON object.
   * For Unmatched screen Overview
   * @return [JSON Object]  Messages that have have not been matched
   */
  public static function UnmatchedList()
  {
    $res = self::getMessageList(self::STATUS_UNMATCHED);
    if ($res) {
      self::exitSuccess('Retrieved unmatched message list', $res);
    }
    else {
      self::exitError('Unable to retrieve unmatched message list');
    }
  } // UnmatchedList()


  /**
   * This function grabs the unassigned message from the db,
   * returns an error if the message is no longer unassigned
   * @return  [JSON Object]  Messages that have have not been matched
   */
  public static function UnmatchedDetails()
  {
    $startTime = microtime(true);
    $messageId = self::get('id');
    $debug = self::get('debug');

    $errmsg = null;
    $msg = self::getFullMessage($messageId);

    if ($msg) {
      $status = $msg['status'];

      if ($status == self::STATUS_UNMATCHED) {
        $body = $msg['body'];
        // Find e-mail addresses, phone numbers, addresses, and names.
        $parsed = self::parseMessage($body);
        // Highlight everything that was found.
        $msg['body'] = self::highlightItems($body, $parsed);
        $msg['found_emails'] = $parsed['emails'];
        $endTime = microtime(true);
        $msg['stats']['overview']['time'] = $endTime - $startTime;
      }
      else if ($status == self::STATUS_MATCHED) {
        $errmsg = 'Message is already assigned';
      }
      else if ($status == self::STATUS_CLEARED) {
        $errmsg = 'Message has been cleared from inbox';
      }
      else if ($status == self::STATUS_DELETED) {
        $errmsg = 'Message has been deleted';
      }
      else {
        $errmsg = 'Message has an unknown status';
      }
    }
    else {
      $errmsg = 'Message not found';
    }

    if ($debug) {
      echo "\n<pre>\n";
      print_r($msg);
      echo "\n</pre>\n";
    }
    //file_put_contents("/tmp/inbound_email/msg", print_r($msg, true));

    if ($errmsg) {
      self::exitError($errmsg);
    }
    else {
      self::exitSuccess('Retrieved message', $msg);
    }
  } // UnmatchedDetails()


  /**
   * Switch the message status to deleted
   * For Unmatched & Matched screen Delete
   * @return [JSON Object]  Status message
   */
  public static function UnmatchedDelete() {
    $ids = self::get_array('id');
    $userId = CRM_Core_Session::getLoggedInContactID();

    foreach ($ids as $id) {
      // Delete the message with the specified UID
      $query = "
        UPDATE nyss_inbox_messages
        SET status = ".self::STATUS_DELETED.", matcher = $userId
        WHERE id = $id
      ";
      CRM_Core_DAO::executeQuery($query);
    }

    self::exitSuccess('Message deleted', $ids);
  } // UnmatchedDelete()


  /**
   * Does a search for contacts and return them as a JSON object.
   * Uses BB_NORMALIZE to prevent dupes
   * For Unmatched & Matched screen Search
   * @return [JSON Object]  List of matching contacts
   */
  public static function ContactSearch()
  {
    $debug = self::get('debug');
    $fields = self::getContactFields();

    if ($debug) {
      echo "\n<h1>inputs</h1>\n";
      var_dump($fields);
    }

    $where = "WHERE c.is_deleted=0";
    $order = "ORDER BY c.id ASC";

    if ($fields['first_name']) {
      $val = $fields['first_name'];
      $where .= " AND (c.first_name LIKE BB_NORMALIZE('$val') OR c.organization_name LIKE BB_NORMALIZE('$val'))";
    }

    if ($fields['last_name']) {
      $val = $fields['last_name'];
      $where .= " AND (c.last_name LIKE BB_NORMALIZE('$val') OR c.household_name LIKE BB_NORMALIZE('$val'))";
    }

    if ($fields['email_address']) {
      $where .= " AND e.email LIKE '{$fields['email_address']}'";
      $order .= ", e.is_primary DESC";
    }

    if ($fields['dob']) {
      $val = $fields['dob'];
      $where .= " AND c.birth_date = '$val'";
    }

    if ($fields['street_address'] || $fields['city']) {
      $order .= ", a.is_primary DESC";
      if ($fields['street_address']) {
        $val = $fields['street_address'];
        $where .= " AND a.street_address LIKE BB_NORMALIZE_ADDR('$val')";
      }
      if ($fields['city']) {
        $val = $fields['city'];
        $where .= " AND a.city LIKE BB_NORMALIZE_ADDR('$val')";
      }
    }

    if ($fields['state']) {
      $where .= " AND sp.id = {$fields['state']}";
    }

    if ($fields['phone']) {
      $val = $fields['phone'];
      $where .= " AND p.phone LIKE '%$val%'";
    }

    $errmsg = null;

    // If at least one field has a value to search on, execute query
    if (array_filter($fields)) {
      $query = "
        SELECT c.id, c.display_name, c.contact_type, c.birth_date, sp.name,
          a.street_address, a.postal_code, a.city, p.phone, e.email, e.is_primary
        FROM civicrm_contact c
        LEFT JOIN civicrm_email e
          ON c.id = e.contact_id
        LEFT JOIN civicrm_address a
          ON c.id = a.contact_id
        LEFT JOIN civicrm_phone p
          ON c.id = p.contact_id
        LEFT JOIN civicrm_state_province sp
          ON a.state_province_id=sp.id
        $where
        GROUP BY c.id, c.display_name, c.contact_type, c.birth_date, sp.name,
          a.street_address, a.postal_code, a.city, p.phone, e.email, e.is_primary
        $order
      ";

      $dbres = CRM_Core_DAO::executeQuery($query);
      $res = array();
      while ($dbres->fetch()) {
        $res[] = get_object_vars($dbres);
      }
    }
    else {
      // do nothing if no query
      $errmsg = 'Please enter a query';
    }

    if ($errmsg) {
      self::exitError($errmsg);
    }
    else {
      self::exitSuccess('Search succeeded', $res);
    }
  } // ContactSearch()


  /**
   * Adds an email address to the specified contacts
   * @return [JSON Object]  Status message
   */
  public static function ContactAddEmail()
  {
    require_once 'api/api.php';

    $contactIds = self::get_array('contacts');
    $senderEmail = strtolower(self::get('email'));
    $debug = self::get('debug');
    $gotError = false;

    $params = array(
      'contact_id' => null,   // set to NULL for now
      'email' => $senderEmail,
      'location_type_id' => self::getOtherLocationId(),
      'version' => 3,
    );

    foreach ($contactIds as $contactId) {
      // Check to see if contact has the email address being assigend to it,
      // if doesn't have email address, add it to contact
      $query = "
        SELECT e.email 
        FROM civicrm_email e 
        WHERE e.contact_id = $contactId
      ";
      $dbres = CRM_Core_DAO::executeQuery($query);
      $emailFound = false;
      while ($dbres->fetch()) {
        if (strtolower($dbres->email) == $senderEmail) {
          $emailFound = true;
        }
      }

      if (!$emailFound) {
        $params['contact_id'] = $contactId;
        $apires = civicrm_api('email', 'create', $params);
        if ($apires['is_error'] == 1) {
          $gotError = true;
          break;
        }
      }
    }

    if ($gotError) {
      self::exitError('Unable to add email');
    }
    else {
      self::exitSuccess('Email was added');
    }
  } // ContactAddEmail()


  /**
   * Creates activity from unmatched message
   * Assigns it to contact ID.
   * For Unmatched screen Match
   * @return [JSON Object]  Status message
   */
  public static function UnmatchedAssign()
  {
    require_once 'api/api.php';
    require_once 'CRM/Utils/File.php';

    $recId = self::get('id');
    $contactIds = self::get_array('contactId');
    $session = CRM_Core_Session::singleton();
    $userId = $session->get('userID');
    $debug = self::get('debug');

    if (!$recId || !$contactIds) {
      self::exitError('Missing message ID or contact ID');
    }

    $msg = self::getFullMessage($recId);

    if ($msg == null) {
      self::exitError('Message not found');
    }

    $status = $msg['status'];

    if ($status != self::STATUS_UNMATCHED) {
      self::exitError('Message is already matched');
    }

    $aActivityStatus = CRM_Core_PseudoConstant::activityStatus();
    $aActivityType = CRM_Core_PseudoConstant::activityType();

    $bbconfig = get_bluebird_instance_config();
    if (isset($bbconfig['imap.activity.status.default'])) {
      $activityStatusName = $bbconfig['imap.activity.status.default'];
    }
    else {
      $activityStatusName = self::DEFAULT_ACTIVITY_STATUS;
    }
    $activityStatus = array_search($activityStatusName, $aActivityStatus);
    $activityType = array_search(self::DEFAULT_ACTIVITY_TYPE, $aActivityType);

    // where to write file attachments to
    $config = CRM_Core_Config::singleton();
    $uploadDir = $config->customFileUploadDir;
    $uploadInbox = $uploadDir.'inbox/';

    $oldActivityId = $msg['activity_id'];
    $senderEmail = $msg['sender_email'];
    $senderName = CRM_Core_DAO::escapeString($msg['sender_name']);
    $forwarder = $msg['forwarder'];
    $upddate = $msg['updated_date'];
    $fwddate = $msg['email_date'];
    $subject = CRM_Core_DAO::escapeString($msg['subject']);
    $body = CRM_Core_DAO::escapeString($msg['body']);
    $messageId = $msg['message_id'];
    $imapId = $msg['imap_id'];

    // Search for contact in Auth Forwarders group with given e-mail.
    $query = "
      SELECT e.contact_id
      FROM civicrm_group_contact gc, civicrm_group g, civicrm_email e
      WHERE g.name='".self::DEFAULT_AUTH_GROUP."'
        AND e.email='".$forwarder."'
        AND g.id=gc.group_id
        AND gc.status='Added'
        AND gc.contact_id=e.contact_id
      ORDER BY gc.contact_id ASC
    ";
    $contactRes = array();
    $dbres = CRM_Core_DAO::executeQuery($query);
    while ($dbres->fetch()) {
      $contactRes[] = get_object_vars($dbres);
    }

    // Authorization of forwarders is done by e-mail address.
    // The forwarder is typically a Bluebird user, but could also be
    // a non-user.  In that case, use the Bluebird admin.
    if (count($contactRes) > 0) {
      $forwarderId = $contactRes[0]['contact_id'];
    }
    else {
      $forwarderId = self::DEFAULT_CONTACT_ID;
    }

    $contactCount = 0;
    foreach ($contactIds as $contactId) {
      // get contact info for return message
      $contactInfo = self::getCiviObject('contact', $contactId);
      $contactName = $contactInfo['values'][$contactId]['display_name'];

      // Submit the activity information and assign it to the right user
      $params = array(
        'activity_type_id' => $activityType,
        'source_contact_id' => $forwarderId,
        'target_contact_id' => $contactId,
        'subject' => $subject,
        'is_auto' => 0, // we manually add it, right ?
        'status_id' => $activityStatus,
        'activity_date_time' => $upddate,
        'details' => $body,
        'version' => 3
      );
      $apires = civicrm_api('activity', 'create', $params);

      // Handle activity creation error.
      if ($apires['is_error'] == 1 || $apires['values'] == null || count($apires['values']) != 1) {
        self::exitError($apires['error_message']);
      }

      $activityId = $apires['id'];
      $msgs[] = array('message'=> "Message assigned to ".$contactName, 'key'=>$senderEmail, 'contact'=>$contactId);

      // if this is not the first contact, add a new row to the table
      if ($contactCount > 0) {
        $debug_line = 'Added on assignment to #'.$contactCount;
        $query = "
          INSERT INTO nyss_inbox_messages
           (message_id, imap_id, sender_name, sender_email, subject,
            body, forwarder, status, debug, updated_date, email_date,
            activity_id, matched_to, matcher)
          VALUES
            ($messageId, $imapId, '$senderName', '$senderEmail', '$subject',
             '$body', '$forwarder', 1, '$debug_line', '$upddate', '$fwddate',
             $activityId, $contactId, $userId)";
      }
      else {
        $query = "
          UPDATE nyss_inbox_messages
          SET status=".self::STATUS_MATCHED.", activity_id=$activityId,
              matcher=$userId, matched_to=$contactId, updated_date='$upddate'
          WHERE id = $recId";
      }

      $attachments = $msg['attachments'];

      if (isset($attachments[0])) {
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

            $query = "
              INSERT INTO civicrm_file (mime_type, uri, upload_date)
              VALUES ('$mime', '$fileName', 'upddate')
            ";
            CRM_Core_DAO::executeQuery($query);
            $fileId = mysql_insert_id(self::db());

            $query = "
              INSERT INTO civicrm_entity_file (entity_table, entity_id, file_id)
              VALUES ('civicrm_activity', $activityId, $fileId)";
            CRM_Core_DAO::executeQuery($query);
          }
        }
      } // if attachments
      $contactCount++;
    } // foreach contact

    self::exitSuccess('Assigned message', $msgs);
  } // UnmatchedAssign()


  /**
   * Retrieves a list of Matched messages that have not been cleared,
   * For Matched screen overview
   * @return [JSON Object]  List overview of messages
   */
  public static function MatchedList()
  {
    $res = self::getMessageList(self::STATUS_MATCHED);
    if ($res) {
      self::exitSuccess('Retrieved matched message list', $res);
    }
    else {
      self::exitError('Unable to retrieve matched message list');
    }
  } // MatchedList()


  /**
   * Retrieve single activity details
   * For Matched screen EDIT or TAG
   * @return [JSON Object]  Encoded message output, OR error codes
   */
  public static function MatchedDetails()
  {
    $startTime = microtime(true);
    $messageId = self::get('id');
    $debug = self::get('debug');

    $errmsg = null;
    $msg = self::getFullMessage($messageId);

    if ($msg == null) {
      self::exitError('Message not found');
    }

    // overwrite incorrect details
    $msg['orig_sender_name'] = $msg['sender_name'];
    $msg['orig_sender_email'] = $msg['sender_email'];
    $apiRes = self::getCiviObject('contact', $msg['matched_to']);
    $msg['sender_name'] = $apiRes['values'][$msg['matched_to']]['display_name'];
    $msg['sender_email'] = $apiRes['values'][$msg['matched_to']]['email'];
    $status = $msg['status'];

    if ($status == self::STATUS_MATCHED) {
      $body = $msg['body'];
      // Find e-mail addresses, phone numbers, addresses, and names.
      $parsed = self::parseMessage($body);
      // Highlight everything that was found.
      $msg['body'] = self::highlightItems($body, $parsed);
      $msg['found_emails'] = $parsed['emails'];
      $endTime = microtime(true);
      $msg['stats']['overview']['time'] = $endTime - $startTime;
    }
    else if ($status == self::STATUS_UNMATCHED) {
      $errmsg = 'Message has not yet been matched';
    }
    else if ($status == self::STATUS_CLEARED) {
      $errmsg = 'Message has been cleared from inbox';
    }
    else if ($status == self::STATUS_DELETED) {
      $errmsg = 'Message has been deleted';
    }
    else {
      $errmsg = 'Message has an unknown status';
    }

    if ($debug) {
      echo "\n<pre>\n";
      print_r($msg);
      echo "\n</pre>\n";
    }
    //file_put_contents("/tmp/inbound_email/msg", print_r($msg, true));
    if ($errmsg) {
      self::exitError($errmsg);
    }
    else {
      self::exitSuccess('Retrieved message', $msg);
    }
  } // MatchedDetails()


  /**
   * Removes activity from civi and sets inbound email reference to DELETED
   * For Matched screen DELETE
   * @return [JSON Object]  JSON encoded response, OR error codes
   */
  public static function MatchedDelete()
  {
    require_once 'api/api.php';
    $ids = self::get_array('id');
    $session = CRM_Core_Session::singleton();
    $userId =  $session->get('userID');
    $success = true;

    foreach ($ids as $messageId) {
      $msg = self::getFullMessage($messageId);
      $activityId = $msg['activity_id'];

      // deleting an activity
      $params = array(
        'id' => $activityId,
        'activity_type_id' => 1,
        'version' => 3,
      );

      $deleteActivity = civicrm_api('activity', 'delete', $params);

      // TODO: Need to add to function to delete entity tags since
      // they are not cleaned up

      if ($deleteActivity['is_error'] == 1) {
        $success = false;
      }
      else {
        $query = "
          UPDATE nyss_inbox_messages
          SET status=".self::STATUS_DELETED.", matcher=$userId
          WHERE id=$messageId
        ";
        CRM_Core_DAO::executeQuery($query);
      }
    }

    if ($success) {
      self::exitSuccess('Messages and activities deleted', $ids);
    }
    else {
      self::exitError('Unable to delete all messages and activities', $ids);
    }
  } // MatchedDelete()


  /**
   * Clears from inbound email Matched screen by setting status to 7
   * For Matched screen CLEAR
   * @return [JSON Object]  JSON encoded response, OR error codes
   */
  public static function MatchedClear() {
    $ids = self::get_array('id');
    $session = CRM_Core_Session::singleton();
    $userId =  $session->get('userID');
    $success = true;

    foreach ($ids as $messageId) {
      $msg = self::getFullMessage($messageId);
      if ($msg) {
        $query = "
          UPDATE nyss_inbox_messages
          SET status = ".self::STATUS_CLEARED.", matcher = $userId
          WHERE id = $messageId
        ";
        CRM_Core_DAO::executeQuery($query);
      }
      else {
        $success = false;
      }
    }

    if ($success) {
      self::exitSuccess('Cleared messages');
    }
    else {
      self::exitError('Unable to clear all messages');
    }
  } // MatchedClear()

  /**
   * Assign an inbound email activity to a different contact
   * For Matched screen EDIT
   * @return [JSON Object]  JSON encoded response, OR error codes
   */
  public static function MatchedReassign() {
    $id = self::get('id');
    $contactIds = self::get_array('contactId');
    $session = CRM_Core_Session::singleton();
    $userId = $session->get('userID');
    $debug = self::get('debug');
    $success = true;

    $msg = self::getFullMessage($id);
    $contact = $msg['matched_to'];
    $activityId = $msg['activity_id'];
    $upddate = $msg['updated_date'];

    $results = array();

    if ($debug) {
      echo "<h1>inputs</h1>";
      var_dump($contact);
      var_dump($activityId);
    }

    // check to see if the activity is still assigned to the same contact
    // if not, kill it
    // 1 = assignee, 2 = source, 3 = target
    $query = "
      SELECT COUNT(id) 
      FROM civicrm_activity_contact
      WHERE activity_id = $activityId
        AND contact_id = $contact
        AND record_type_id = 3
    ";
    $check = CRM_Core_DAO::singleValueQuery($query);

    if ($check != '1') {
      self::exitError('Activity is not assigned to this contact. Please reload.');
    }

    foreach ($contactIds as $contactId) {
      $apires = self::getCiviObject('contact', $contactId);
      $changeName = trim($apires['values'][$contactId]['display_name']);
      $firstName = trim($apires['values'][$contactId]['first_name']);
      $lastName = trim($apires['values'][$contactId]['last_name']);
      $contactType = trim($apires['values'][$contactId]['contact_type']);
      $email = trim($apires['values'][$contactId]['email']);

      // change the contact
      $query = "
        UPDATE civicrm_activity_contact
        SET contact_id = $contactId
        WHERE activity_id = $activityId
          AND record_type_id = 3
      ";
      CRM_Core_DAO::executeQuery($query);

      $query = "
        UPDATE civicrm_activity
        SET is_auto=0
        WHERE id = $activityId
      ";
      CRM_Core_DAO::executeQuery($query);

      $query = "
        UPDATE nyss_inbox_messages
        SET matcher=$userId, matched_to=$contactId, sender_name='$changeName',
          sender_email='$email', updated_date='$upddate'
        WHERE id = $id
      ";
      CRM_Core_DAO::executeQuery($query);
    }

    $res = array(
      'id' => $id,
      'contact_id' => $contactId,
      'contact_type' => $contactType,
      'first_name' => $firstName,
      'last_name' => $lastName,
      'display_name' => $changeName,
      'email' => $email,
      'activity_id' => $id,
    );
    self::exitSuccess("Activity reassigned to $changeName", $res);
  }//MatchedReassign()

  /**
   * Edit the Assignee for an inbound email activity to a different user
   * For Matched screen EDIT
   * @return [JSON Object]  JSON encoded response, OR error codes
   */
  public static function MatchedEdit() {
    $activityIds = self::get_array('activity_id');
    $activity_contactId = self::get('activity_contact');
    $activity_statusId = self::get('activity_status_id');
    $activity_date = self::get('activity_date');

    foreach ($activityIds as $activityId) {
      if (!empty($activity_statusId)) {
        $query = "
          UPDATE civicrm_activity
          SET status_id=$activity_statusId
          WHERE id = $activityId
        ";
        CRM_Core_DAO::executeQuery($query);
      }

      if (!empty($activity_date)) {
        $query = "
          UPDATE civicrm_activity
          SET activity_date_time = '$activity_date'
          WHERE id = $activityId
        ";
        CRM_Core_DAO::executeQuery($query);
      }

      // change the contact
      if (!empty($activity_contactId)) {
        $query = "
          INSERT INTO civicrm_activity_contact
            (activity_id, contact_id, record_type_id)
          VALUES ($activityId, $activity_contactId, 1)
        ";
        CRM_Core_DAO::executeQuery($query);

        // NYSS - 7929
        $attachments = CRM_Core_BAO_File::getEntityFile('civicrm_activity', $activityId );
        $assigneeContacts = CRM_Activity_BAO_ActivityAssignment::getAssigneeNames($activityId, true, false);
        //build an associative array with unique email addresses.
        foreach ($assigneeContacts as $id => $value) {
          $mailToContacts[$assigneeContacts[$id]['email']] = $assigneeContacts[$id];
        }
        CRM_Case_BAO_Case::sendActivityCopy(null, $activityId, $mailToContacts, $attachments, null);
      }
    }

    self::exitSuccess('Edited activity');
  } // MatchedEdit()

  /**
   * Autocomplete Keyword search for tags
   * For Matched screen TAG
   * @return [JSON Object]  JSON encoded response, OR error codes
   */
  public static function KeywordSearch() {
    $name = self::get('name');

    $query = "
      SELECT id, name 
      FROM civicrm_tag
      WHERE parent_id=".self::KEYWORD_PARENT_ID."
        AND name LIKE '$name%'
    ";
    $dbres = CRM_Core_DAO::executeQuery($query);

    $res = array();
    $exactMatch = false;
    while ($dbres->fetch()) {
      $res[] = get_object_vars($dbres);
      if ($dbres->name == $name) {
        $exactMatch = true;
      }
    }

    // If no exact match to the provided tag name, then prepend a new tag
    // of that name to the results.
    if (!$exactMatch) {
      $res = array_merge(array(array('id'=>$name.':::value', 'name'=>$name)), $res);
    }

    self::exitSuccess('Matched tags', $res);
  } // KeywordSearch()


  /**
   * Assign Keywords or Positions to a Contact or a Activity
   * For Matched screen TAG
   * @return [JSON Object]   JSON encoded response, OR error codes
   */
  public static function TagAdd() {
    $activityIds = self::get_array('activityId');
    $contactIds = self::get_array('contactId');
    $tagIds = self::get_array('tags');
    $parentId = self::get('parentId');
    $success = true;

    if (!$parentId) {
      $parentId = self::KEYWORD_PARENT_ID;
    }

    switch ($parentId) {
      case self::POSITION_PARENT_ID: $tagType = 'Position'; break;
      case self::KEYWORD_PARENT_ID: $tagType = 'Keyword'; break;
      default: $tagType = 'Issue Code'; break;
    }

    foreach ($contactIds as $contactId) {
      $ntags = self::addEntityTags($contactId, 'civicrm_contact', $parentId, $tagIds);
    }

    foreach ($activityIds as $activityId) {
      $ntags += self::addEntityTags($activityId, 'civicrm_activity', $parentId, $tagIds);
    }

    if ($ntags > 0) {
      self::exitSuccess("Successfully assigned $ntags $tagType tag(s)");
    }
    else {
      self::exitError("Could not assign any $tagType tags");
    }
  } // TagAdd()


  /**
   * Assign tags to a Contact or a Activity
   * For Matched screen TAG
   * @return [JSON Object]  JSON encoded response, OR error codes
   */
  public static function Issuecode() {
    require_once 'api/api.php';
    $tags = self::get_array('issuecodes');
    $contacts = self::get_array('contacts');
    $action = self::get('action');

    switch ($action) {
      case 'create':
        $actionText = 'added to';
        break;
      case 'delete':
        $actionText = 'deleted from';
        break;
      default:
        self::exitError('Invalid action specified');
    }

    $tagNames = array();
    $contactNames = array();
    $errorMessage = '';

    foreach ($tags as $tagId) {
      $apires = self::getCiviObject('tag', $tagId);
      $tagNames[] = "'".$apires['values'][$tagId]['name']."'";

      foreach ($contacts as $contactId) {
        if ($contactId == 0) break;
        $params = array(
          'entity_table' => 'civicrm_contact',
          'entity_id' => $contactId,
          'tag_id' => $tagId,
          'version' => 3
        );
        $apires = civicrm_api('entity_tag', $action, $params);
        if ($apires['is_error'] == 1) {
          $errorMessage = $apires['error_message'];
          break;
        }
        $apires = self::getCiviObject('contact', $contactId);
        $contactNames[] = "'".$apires['values'][$contactId]['display_name']."'";
      }
    }

    $tagString = implode(',', $tagNames);
    $nameString = implode(',', $contactNames);

    if (empty($errorMessage)) {
      self::exitSuccess("Issue codes $tagString were $actionText $nameString");
    }
    else {
      self::exitError("$errorMessage: All tags $tagString could not be $actionText $nameString");
    }
  } // Issuecode()

  /**
   * Create New contact from message
   * For Matched edit & Unmatched find
   * @return [JSON Object]  JSON encoded response, OR error codes
   */
  public static function ContactAdd() {
    require_once 'api/api.php';

    $debug = self::get('debug');
    $fields = self::getContactFields();

    if ($debug) {
      echo "<h1>inputs</h1>";
      var_dump($fields);
    }

    if (isset($fields['first_name']) || isset($fields['last_name'])) {
      $display_name = trim($fields['first_name'].' '.$fields['last_name']);
      $sort_name = trim($fields['last_name'].', '.$fields['first_name']);
    }
    else if (isset($fields['email_address'])) {
      $display_name = $sort_name = $fields['email_address'];
    }
    else {
      self::exitError('Required: First name or last name or email');
    }

    // Create the contact first
    $params = array(
      'first_name' => $fields['first_name'],
      'middle_name'=> $fields['middle_name'],
      'last_name' => $fields['last_name'],
      'prefix_id' => $fields['prefix'],
      'suffix_id' => $fields['suffix'],
      'sort_name' => $sort_name,
      'display_name' => $display_name,
      'contact_type' => 'Individual',
      'birth_date' => $fields['dob'],
      'version' => 3,
    );

    $errmsg = null;
    $apires = civicrm_api('contact', 'create', $params);
    if ($apires['is_error'] != 1) {
      $contactId = $apires['id'];
      $otherLocationId = self::getOtherLocationId();

      // add the email
      if (isset($fields['email_address'])) {
        $params = array(
          'contact_id' => $contactId,
          'email' => $fields['email_address'],
          'location_type_id' => $otherLocationId,
          'version' => 3
        );
        $api_email = civicrm_api('email', 'create', $params);
      }

      // add the phone number
      if (isset($fields['phone'])) {
        $params = array(
          'contact_id' => $contactId,
          'phone' => $fields['phone'],
          'location_type_id' => $otherLocationId,
          'version' => 3
        );
        $api_phone = civicrm_api('phone', 'create', $params);
      }

      if (isset($fields['street_address']) || isset($fields['street_address_2'])
          || isset($fields['city']) || isset($fields['state'])
          || isset($fields['postal_code'])) {
        $params = array(
          'contact_id' => $contactId,
          'street_address' => $fields['street_address'],
          'supplemental_address_1' => $fields['street_address_2'],
          'city' => $fields['city'],
          'state_province_id' => $fields['state'],
          'postal_code' => $fields['postal_code'],
          'is_primary' => 1,
          'country_id' => 1228,
          'location_type_id' => 1,
          'version' => 3,
          'debug' => 1
        );
        $api_address = civicrm_api('address', 'create', $params);
      }

      if ($api_email['is_error'] == 1 || $api_phone['is_error'] == 1
          || $api_address['is_error'] == 1) {
        $errmsg = 'Unable to add contact details (email/phone/address)';
      }
    }
    else {
      $errmsg = 'Unable to add contact';
    }

    if ($errmsg) {
      self::exitError($errmsg);
    }
    else {
      self::exitSuccess('Contact added', $contactId);
    }
  } // ContactAdd()

  /**
   * Generate Usage Report
   * Current report shows Statistics & every message list
   * For Report screen
   * @return [JSON Object]  JSON encoded response, OR error codes
   */
  public static function getReports() {
    $debug = self::get('debug');
    $range = self::get('range');

    $rangeSql = '';
    if (is_numeric($range) && $range >= 1) {
      $rangeSql = "AND (updated_date BETWEEN '".date('Y-m-d H:i:s', strtotime('-'.$range.' days'))."' AND '".date('Y-m-d H:i:s')."')";
    }

    $query = "
      SELECT im.id, updated_date, email_date,
        CASE
          WHEN im.status = ".self::STATUS_UNMATCHED." THEN 'unmatched'
          WHEN im.status = ".self::STATUS_MATCHED." THEN 'matched'
          WHEN im.status = ".self::STATUS_CLEARED." THEN 'cleared'
          WHEN im.status = ".self::STATUS_DELETED." THEN 'deleted'
          WHEN im.status = ".self::STATUS_UNPROCESSED." THEN 'unprocessed'
          ELSE 'unknown'
        END as status_icon_class,
        CASE
            WHEN im.status = ".self::STATUS_UNMATCHED." THEN 'Unmatched'
            WHEN im.status = ".self::STATUS_MATCHED." THEN CONCAT('Matched by ', IFNULL(matcher.display_name,'Unknown Contact'))
            WHEN im.status = ".self::STATUS_CLEARED." THEN 'Cleared'
            WHEN im.status = ".self::STATUS_DELETED." THEN 'Deleted'
            WHEN im.status = ".self::STATUS_UNPROCESSED." THEN 'Unprocessed'
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
        ON im.message_id = imm.message_id
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

    $msgs = array();
    $res = array(
      'Total' => 0,
      'Unmatched' => 0,
      'Matched' => 0,
      'Cleared' => 0,
      'Deleted' => 0,
      'Errors' => 0,
      'Messages' => null
    );

    while ($dbres->fetch()) {
      $msg = self::postProcess(get_object_vars($dbres));
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

    if ($debug) {
      echo "<h1>Building Reports</h1>";
      var_dump($query);
      echo "Response <br/>";
      var_dump($res);
    }

    //file_put_contents("/tmp/inbound_email/report", print_r($res, true));
    self::exitSuccess('Generated report', $res);
  } // getReports()

  /**
   * Generate Usage Report
   * Current report shows Statistics & every message list
   * For Report screen
   * @return [JSON Object]  JSON encoded response, OR error codes
   */
  public static function getTags() {
    $id = (int)$_GET['id'];
    $tags = array();
    $tltext = '';
    if ($id) {
      // get the tags for this activity
      $q = "
        SELECT c.name
        FROM nyss_inbox_messages a
        JOIN nyss_inbox_messages_matched mm
          ON a.message_id = mm.message_id
        JOIN civicrm_entity_tag b
          ON b.entity_id = mm.activity_id
        JOIN civicrm_tag c
          ON b.tag_id = c.id
        WHERE a.id = {$id}
          AND b.entity_table = 'civicrm_activity';
      ";
      $res = CRM_Core_DAO::executeQuery($q);
      while ($res->fetch()) {
        $new = trim($res->name);
        if ($new) { $tags[] = $new; }
      }
      // build the HTML
      if (count($tags)) {
        $tltext = '<div class="mail-merge-activity-tag-list-header">Tags assigned:</div>' .
                  '<div class="mail-merge-activity-tag-list">' .
                  implode('</span>, <span class="mail-merge-activity-tag">',$tags) .
                  "</span></div>";
      }
      else {
        $tltext = '<div class="mail-merge-activity-tag-list-header">No tags assigned</div>';
      }
    }
    $ret = $tltext ? $tltext : '';
    die($ret);
  }

} // CRM_NYSS_IMAP_AJAX
