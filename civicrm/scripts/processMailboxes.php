<?php
// processMailboxes.php
//
// Project: BluebirdCRM
// Author: Ken Zalewski & Stefan Crain
// Organization: New York State Senate
// Date: 2011-03-22
// Revised: 2013-04-27
// Revised: 2014-09-15 - simplified contact matching logic; added debug control
// Revised: 2015-08-03 - added ability to configure some params from BB config
// Revised: 2015-08-24 - added pattern-matching for auth forwarders
// Revised: 2017-02-23 - re-migrated to mysqli (originally done on 2017-01-11)
// Revised: 2018-08-10 - removed --unread-only command line argument
//                     - added --recheck-unmatched command line argument
//                     - added more default value constants
// Revised: 2019-04-17 - changed mangleHTML() to renderAsHtml()
// Revised: 2020-01-30 - must bootstrap Drupal now
// Revised: 2021-08-12 - change 'server' to 'host'
//                     - end support for multiple IMAP accounts per CRM
//                     - add --deny-unauthorized to reject any emails from
//                       a sender that is not in the forwarder whitelist
// Revised: 2023-08-21 - implemented https://www.php-imap.com/ and OAuth connection support

// Version number, used for debugging
define('VERSION_NUMBER', 3.0);

// Mailbox settings common to all CRM instances
define('DEFAULT_IMAP_HOST', 'imap.example.com');
define('DEFAULT_IMAP_PORT', 143);
define('DEFAULT_IMAP_FLAGS', '/imap/notls');
define('DEFAULT_IMAP_MAILBOX', 'INBOX');
define('DEFAULT_IMAP_ARCHIVEBOX', 'Archive');
define('DEFAULT_IMAP_VALID_SENDERS', false);
define('DEFAULT_IMAP_DENY_UNAUTH', false);
define('DEFAULT_IMAP_ACTIVITY_STATUS', 'Completed');
define('DEFAULT_IMAP_NO_ARCHIVE', false);
define('DEFAULT_IMAP_NO_EMAIL', false);
define('DEFAULT_IMAP_RECHECK', false);

define('IMAP_CMD_POLL', 1);
define('IMAP_CMD_LIST', 2);
define('IMAP_CMD_DELETE', 3);

// Maximum size of an e-mail attachment
define('MAX_ATTACHMENT_SIZE', 2097152);

// Allowed file extensions for "application" file type.
define('ATTACHMENT_FILE_EXT_REGEX', 'pdf|te?xt|rtf|odt|docx?|xlsx?|ppt|csv');

// Status codes for the nyss_inbox_messages table.
define('STATUS_UNMATCHED', 0);
define('STATUS_MATCHED', 1);
define('STATUS_UNPROCESSED', 99);

define('INVALID_EMAIL_FROM', '"Bluebird Admin" <bluebird.admin@nysenate.gov>');
define('INVALID_EMAIL_SUBJECT', 'Bluebird Inbox Error: Not permitted to send e-mails to CRM');
define('INVALID_EMAIL_TEXT', "You do not have permission to forward e-mails to this CRM instance.\n\nIn order to allow your e-mails to be accepted, you must request that your e-mail address be added to the  Authorized Forwarders group for this CRM.\n\nPlease contact Senate Technology Services for more information.\n\n");

// //email address of the contact to file unknown emails against.
// define('UNKNOWN_CONTACT_EMAIL', 'unknown.contact@nysenate.gov');

// The Bluebird predefined group name for contacts who are authorized
// to forward messages to the CRM inbox.
define('AUTH_FORWARDERS_GROUP_NAME', 'Authorized_Forwarders');

error_reporting(E_ERROR | E_PARSE | E_WARNING | E_NOTICE);

if (!ini_get('date.timezone')) {
  date_default_timezone_set('America/New_York');
}

//no limit
set_time_limit(0);

$prog = basename(__FILE__);

require_once 'script_utils.php';
$stdusage = civicrm_script_usage();
$usage = "[--imap-user|-u username]  [--imap-pass|-s password]  [--cmd|-c <poll|list|delarchive>]  [--log-level|-l LEVEL]  [--host|-h imap_host]  [--port|-p imap_port]  [--imap-flags|-f imap_flags]  [--mailbox|-m name]  [--archivebox|-a name]  [--valid-senders|-v EMAILS]  [--deny-unauthorized|-x]  [--default-activity-status|-d <Completed|Scheduled|Cancelled>]  [--no-archive|-n]  [--no-email|-e]  [--recheck-unmatched|-r]";
$shortopts = "u:s:c:l:h:p:f:m:a:v:xd:ner";
$longopts = array("imap-user=", "imap-pass=", "cmd=", "log-level=",
                  "host=", "port=", "imap-flags=", "mailbox=", "archivebox=",
                  "valid-senders=", "deny-unauthorized",
                  "default-activity-status=",
                  "no-archive", "no-email", "recheck-unmatched");

$optlist = civicrm_script_init($shortopts, $longopts);

if ($optlist === null) {
  error_log("Usage: $prog  $stdusage  $usage\n");
  exit(1);
}

if (!empty($optlist['log-level'])) {
  set_bbscript_log_level($optlist['log-level']);
}

/*
** All IMAP parameters for a CRM instance can be specified in the Bluebird
** configuration file.  Each parameter can also be overridden by command
** line options.
*/

$bbconfig = get_bluebird_instance_config();

$site = $optlist['site'];
$cmd = $optlist['cmd'];
$g_crm_instance = $site;

$all_params = [
  // Each element is: paramName, optName, bbcfgName, defaultVal
  ['site', 'site', null, null],
  ['host', 'host', 'imap.host', DEFAULT_IMAP_HOST],
  ['port', 'port', 'imap.port', DEFAULT_IMAP_PORT],
  ['user', 'imap-user', 'imap.user', null],
  ['password', 'imap-pass', 'imap.pass', null],
  ['flags', 'imap-flags', 'imap.flags', DEFAULT_IMAP_FLAGS],
  ['mailbox', 'mailbox', 'imap.mailbox', DEFAULT_IMAP_MAILBOX],
  ['archivebox', 'archivebox', 'imap.archivebox', DEFAULT_IMAP_ARCHIVEBOX],
  ['validsenders', 'valid-senders', 'imap.validsenders', DEFAULT_IMAP_VALID_SENDERS],
  ['denyunauth', 'deny-unauthorized', 'imap.deny.unauth', DEFAULT_IMAP_DENY_UNAUTH],
  ['actstatus', 'default-activity-status', 'imap.activity.status.default', DEFAULT_IMAP_ACTIVITY_STATUS],
  ['noarchive', 'no-archive', null, DEFAULT_IMAP_NO_ARCHIVE],
  ['noemail', 'no-email', null, DEFAULT_IMAP_NO_EMAIL],
  ['recheck', 'recheck-unmatched', null, DEFAULT_IMAP_RECHECK]
];

$imap_params = [];

foreach ($all_params as $param) {
  $val = getImapParam($optlist, $param[1], $bbconfig, $param[2], $param[3]);
  if ($val !== null) {
    $imap_params[$param[0]] = $val;
  }
}

if (empty($imap_params['user'])) {
  echo "$prog: No IMAP username was specified for CRM instance [$site]\n";
  exit(1);
}
elseif (empty($imap_params['password'])) {
  echo "$prog: No IMAP password was specified for CRM instance [$site]\n";
  exit(1);
}

if ($cmd == 'list') {
  $cmd = IMAP_CMD_LIST;
}
elseif ($cmd == 'delarchive') {
  $cmd = IMAP_CMD_DELETE;
}
elseif ($cmd == 'poll' || !$cmd) {
  $cmd = IMAP_CMD_POLL;
}
else {
  error_log("$prog: $cmd: Invalid script command.");
  exit(1);
}

$config = CRM_Core_Config::singleton();
$session = CRM_Core_Session::singleton();

// Grab default values for activities (priority, status, type).
$aActivityPriority = CRM_Core_PseudoConstant::get('CRM_Activity_DAO_Activity', 'priority_id');
$aActivityType = CRM_Core_PseudoConstant::activityType();
$aActivityStatus = CRM_Core_PseudoConstant::activityStatus();

$activityPriority = array_search('Normal', $aActivityPriority);
$activityStatus = array_search($imap_params['actstatus'], $aActivityStatus);
$activityType = array_search('Inbound Email', $aActivityType);

$activityDefaults = [
  'priority' => $activityPriority,
  'status' => $activityStatus,
  'type' => $activityType
];

// Set the session ID for who created the activity
$session->set('userID', 1);

// Directory where file attachments will be written.
$uploadDir = $config->customFileUploadDir;
$uploadInbox = $uploadDir."inbox";
if (!is_dir($uploadInbox)) {
  mkdir($uploadInbox);
  chmod($uploadInbox, 0777);
}

$authForwarders = [
  'emails' => getAuthorizedForwarders(),
  'patterns' => []
];

if ($imap_params['validsenders']) {
  // If imap.validsenders was specified (via cli or config file), then add
  // those e-mail addresses to the list of authorized forwarders.  The contact
  // ID for each of these "config file" forwarders will be 1 (Bluebird Admin).
  // Patterns using wildcards '*' and '?' are acceptable from the config file.
  $validSenders = preg_split('/[\s,]+/', $imap_params['validsenders'], null, PREG_SPLIT_NO_EMPTY);
  foreach ($validSenders as $validSender) {
    if (strpbrk($validSender, '?*') !== false) {
      $senderType = 'patterns';
    }
    else {
      $senderType = 'emails';
    }

    // Attempt to add pattern or email to the corresponding list.
    if (isset($authForwarders[$senderType][$validSender])) {
      bbscript_log(LL::INFO, "Valid sender [$validSender] from config is already in the auth forwarders $senderType list");
    }
    else {
      $authForwarders[$senderType][$validSender] = 1;
    }
  }
}

$imap_params['activityDefaults'] = $activityDefaults;
$imap_params['uploadDir'] = $uploadDir;
$imap_params['uploadInbox'] = $uploadInbox;
$imap_params['authForwarders'] = $authForwarders;

bbscript_log(LL::DEBUG, "imap_params before processing mailbox:", $imap_params);

// Previously, this script would Iterate over all IMAP accounts associated
// with the current CRM instance.  In practice, multiple IMAP accounts were
// never used.  This has been simplified to support a single IMAP username
// and password for each CRM instance.
{
  $rc = processMailboxCommand($cmd, $imap_params);
  if (!$rc) {
    bbscript_log(LL::ERROR, "Failed to process IMAP account {$imap_params['user']}@{$imap_params['host']}");
  }
}

bbscript_log(LL::NOTICE, "Finished processing all mailboxes for CRM instance [$site]");
exit(0);

/*
 * getAuthorizedForwarders()
 * Parameters: None.
 * Returns: Array of contact IDs, indexed by e-mail address, that can forward
 *          messages to the inbox.
 * Note: If more than one contact in the Authorized Forwarders group shares
 *       the same e-mail address, the contact with the lowest ID is stored.
 */
function getAuthorizedForwarders()
{
  $res = array();
  $q = "
    SELECT e.email, e.contact_id
    FROM civicrm_group_contact gc, civicrm_group g, civicrm_email e,
         civicrm_contact c
    WHERE g.name='".AUTH_FORWARDERS_GROUP_NAME."'
      AND g.id=gc.group_id
      AND gc.status='Added'
      AND gc.contact_id=e.contact_id
      AND e.contact_id = c.id
      AND c.is_deleted = 0
    ORDER BY gc.contact_id ASC";

  $dao = CRM_Core_DAO::executeQuery($q);

  while ($dao->fetch()) {
    $email = strtolower($dao->email);
    $cid = $dao->contact_id;
    if (isset($res[$email]) && $res[$email] != $cid) {
      bbscript_log(LL::WARN, "'".AUTH_FORWARDERS_GROUP_NAME."' group already has e-mail address [$email] (cid={$res[$email]}); ignoring cid=$cid");
    }
    else {
      $res[$email] = $cid;
    }
  }

  return $res;
} // getAuthorizedForwarders()



function isAuthForwarder($email, $fwders) {
  bbscript_log(LL::TRACE, '$email: '.$email);
  bbscript_log(LL::TRACE, '$fwders: ', $fwders);

  if (isset($fwders['emails'][$email])) {
    // Exact match on email address
    bbscript_log(LL::TRACE, "Found exact match on forwarder address [$email]");
    return true;
  }
  else {
    // If exact match fails, try a pattern match
    foreach (array_keys($fwders['patterns']) as $pattern) {
      if (fnmatch($pattern, $email, 0)) {
        bbscript_log(LL::TRACE, "Found pattern match for forwarder address [$email]");
        return true;
      }
    }

    bbscript_log(LL::TRACE, "Address [$email] is not an authorized forwarder");
    return false;
  }
} // isAuthForwarder()



function processMailboxCommand($cmd, $params) {
  try {
    $imap_session = new CRM_NYSS_IMAP_Session($params);
    bbscript_log(LL::TRACE, '$imap_session', $imap_session);
  }
  catch (Exception $ex) {
    bbscript_log(LL::ERROR, "Failed to create IMAP session: ".$ex->getMessage());
    $imap_session = NULL;
    return false;
  }

  if ($cmd == IMAP_CMD_POLL) {
    $rc = checkImapAccount($imap_session, $params);
  }
  elseif ($cmd == IMAP_CMD_LIST) {
    $rc = listMailboxes($imap_session);
  }
  elseif ($cmd == IMAP_CMD_DELETE) {
    $rc = deleteArchiveBox($imap_session, $params);
  }
  else {
    bbscript_log(LL::ERROR, "Invalid command [$cmd], params=", $params);
    $rc = false;
  }

  return $rc;
}

/**
 * @param $imapSess
 * @param $params
 * @return bool
 *
 * Check the given IMAP account for new messages and process them.
 */
function checkImapAccount($imap, $params) {
  bbscript_log(LL::NOTICE, "Polling CRM [".$params['site']."] using IMAP account ".$params['user']);

  $imap_conn = $imap->getConnection();

  //get folder listing to determine if we need to create any
  $folders = $imap_conn->getFolders(FALSE);
  bbscript_log(LL::TRACE, '$folders', $folders);

  $folderList = [];
  foreach ($folders as $folder) {
    $folderList[] = $folder->path;
  }
  bbscript_log(LL::TRACE, '$folderList: ', $folderList);

  //create archive folder if missing
  if (!$params['noarchive']) {
    if (!in_array($params['archivebox'], $folderList)) {
      $imap_conn->createFolder(imap_utf7_encode($params['archivebox']));
      bbscript_log(LL::DEBUG, "Created new archive mailbox: {$params['archivebox']}");
    }
    else {
      bbscript_log(LL::DEBUG, "Archive mailbox {$params['archivebox']} already exists");
    }
  }
  else {
    bbscript_log(LL::WARN, "Messages will not be archived since --no-archive was specified");
  }

  //get mailbox folder
  $mailbox = $imap_conn->getFolderByPath($params['mailbox']);
  bbscript_log(LL::TRACE, '$mailbox: ', $mailbox);

  if (empty($mailbox)) {
    return TRUE;
  }

  //get messages in batches of 500
  $messages = $mailbox->query()->all()->limit($limit = 500)->get();
  bbscript_log(LL::TRACE, '$messages: ', $messages);

  $msg_count = count($messages);
  bbscript_log(LL::NOTICE, "Number of messages in IMAP inbox: $msg_count");

  //see https://github.com/Webklex/php-imap/issues/131 for why we collect msgs separately for moving
  $invalid_fwders = $moveMsgs = [];

  //cycle through messages
  foreach ($messages as $message) {
    bbscript_log(LL::INFO, "Retrieving message {$message->getMsgn()} of $msg_count");
    bbscript_log(LL::TRACE, '$message: ', $message);

    $imap_message = new CRM_NYSS_IMAP_Message($message);
    bbscript_log(LL::TRACE, '$imap_message: ', $imap_message);

    $fwder = $message->getFrom()->first()->toArray();
    bbscript_log(LL::TRACE, '$fwder: ', $fwder);

    $isAuth = isAuthForwarder(strtolower($fwder['mail']), $params['authForwarders']);

    /*
     * If the top-level sender is in the authForwarders list, then the message
     * is assumed to be forwarded from a constituent by a staff member.
     * Otherwise, the message is assumed to have originated directly
     * from a constituent, UNLESS --deny-unauthorized was specified, in
     * which case, the message is not processed.
    */
    if ($isAuth === TRUE || $params['denyunauth'] === FALSE) {
      if ($isAuth) {
        bbscript_log(LL::DEBUG, "Sender [{$fwder['full']}] is an authorized forwarder; message is assumed to be forwarded");
      }
      else {
        bbscript_log(LL::DEBUG, "Sender [{$fwder['full']}] is not in the forwarder whitelist; message is assumed to be sent directly from a constituent");
      }

      //store in CiviCRM and archive
      if (storeMessage($imap_message, $message, $params)) {
        //mark as read
        $message->setFlag('Seen');

        //queue to move to archive folder
        if (!$params['noarchive']) {
          array_unshift($moveMsgs, $message);
        }
      }
    }
    else {
      bbscript_log(LL::WARN, "Forwarder [{{$fwder['full']}}] is not allowed to forward/send messages to this CRM; deleting message");
      $invalid_fwders[] = $fwder;

      //delete message
      if ($message->delete()) {
        bbscript_log(LL::DEBUG, "Message {$message->getMsgn()} has been deleted");
      }
      else {
        bbscript_log(LL::WARN, "Unable to delete message {$message->getMsgn()} from mailbox");
      }
    }
  }

  //move messages if optioned and queued
  if (!$params['noarchive'] && !empty($moveMsgs)) {
    foreach ($moveMsgs as $moveMsg) {
      bbscript_log(LL::TRACE, '$moveMsg', $moveMsg);
      $moveMsg->setSequence(3); //IMAP::ST_MSGN

      try {
        if ($moveMsg->move($params['archivebox'])) {
          bbscript_log(LL::DEBUG, "Messsage {$moveMsg->getMsgn()} moved to {$params['archivebox']}");
        }
        else {
          bbscript_log(LL::ERROR, "Failed to move message {$moveMsg->getMsgn()} to {$params['archivebox']}");
        }
      }
      catch (Exception $e) {}
    }
  }

  $invalid_fwder_count = count($invalid_fwders);
  if ($invalid_fwder_count > 0) {
    if (!$params['noemail']) {
      bbscript_log(LL::NOTICE, "Sending denial e-mails to $invalid_fwder_count e-mail address(es)");
      foreach ($invalid_fwders as $invalid_fwder => $dummy) {
        sendDenialEmail($params['site'], $invalid_fwder);
      }
    }
    else {
      bbscript_log(LL::ERROR, "Suppressing the delivery of denial emails to $invalid_fwder_count invalid forwarder address(es) since --no-email was specified");
    }
  }

  bbscript_log(LL::NOTICE, "Finished checking IMAP account ".$params['user'].'@'.$params['host'].$params['flags']);

  bbscript_log(LL::NOTICE, "Searching for matches between message senders and contact records");
  searchForMatches($params);

  return TRUE;
}

/*
 * Store the attachments for the given message in the database and local
 * file system. Metadata about each attachment is stored in the database,
 * while the actual content is stored in the file system.
 * $rowId is the primary key that was generated when the message was stored.
*/
function storeAttachments($message, $params, $rowId) {
  $pattern = '/^('.ATTACHMENT_FILE_EXT_REGEX.')$/';
  $uploadInbox = $params['uploadInbox'].'/'; //note: must have tailing /

  // Load attachment data and save to database and local filesystem.
  $success = 0;

  // Prepare the SQL statement first, so it can be reused in the loop.
  $sql = "
    INSERT INTO nyss_inbox_attachments
    (email_id, file_name, file_full, size, mime_type, ext, rejection)
    VALUES (%1, %2, %3, %4, %5, %6, %7)
  ";

  foreach ($message->getAttachments() as $attachment) {
    bbscript_log(LL::TRACE, '$attachment', $attachment);

    $attributes = $attachment->getAttributes();
    bbscript_log(LL::TRACE, '$attributes', $attributes);

    $civiFilename = CRM_Utils_File::makeFileName($attributes['name']);
    $type = explode('/', $attachment->getContentType())[0];
    $mime = $attachment->getMimeType();
    $rej_reason = '';
    bbscript_log(LL::TRACE, '$type', $type);
    bbscript_log(LL::TRACE, '$mime', $mime);

    if (empty($fileExt = $attachment->getExtension())) {
      $fileExt = substr(strrchr($attributes['name'], '.'), 1);
    }
    bbscript_log(LL::TRACE, '$fileExt', $fileExt);

    // Allow mime type application with certain file extensions,
    // and allow audio/image/video
    // TODO should this include 'text'?
    if (($type == 'application' && preg_match($pattern, $fileExt))
      || in_array($type, ['audio', 'image', 'video'])
    ) {
      if ($attributes['size'] > MAX_ATTACHMENT_SIZE) {
        $rej_reason = "File is larger than ".MAX_ATTACHMENT_SIZE." bytes";
      }
    }
    else {
      $rej_reason = "File type [{$attachment->getContentType()}] not allowed";
    }
    bbscript_log(LL::TRACE, '$rej_reason', $rej_reason ?? 'NONE');

    if (!$rej_reason) {
      bbscript_log(LL::INFO, 'Writing attachment data to '.$uploadInbox.$civiFilename);

      //save attachment to disk
      $status = $attachment->save($uploadInbox, $civiFilename);
      bbscript_log(LL::DEBUG, '$status', $status);

      if ($status) {
        //store record of attachment
        CRM_Core_DAO::executeQuery($sql, [
          1 => [$rowId, 'Positive'],
          2 => [$attributes['name'], 'String'],
          3 => [$uploadInbox.$civiFilename, 'String'],
          4 => [$attributes['size'], 'Positive'],
          5 => [$mime, 'String'],
          6 => [$fileExt, 'String'],
          7 => [$rej_reason, 'String'],
        ]);

        $success++;
      }
      else {
        bbscript_log(LL::ERROR, 'Unable to store attachment to disk.', $attachment);
      }
    }
  }

  bbscript_log(LL::TRACE, "Inserted $success attachments successfully.");

  return (!empty($success) && count($message->getAttachments()));
} // storeAttachments()


/**
 * @param $imapMsg object constructed object in our custom class
 * @param $message object message object passed from php-imap
 * @param $params array
 * @return boolean
 *
 * Store the various metadata of the given message, plus its content.
 * This calls storeAttachments() to download and store the attachments.
 * Returns true/false to move the email to archive or not.
 */
function storeMessage($imapMsg, $message, $params) {
  $authForwarders = $params['authForwarders'];
  $msgMeta = $imapMsg->getMetaData();
  $all_addr = $imapMsg->findFromAddresses($message);

  // check for plain/html body text
  bbscript_log(LL::TRACE, 'all_addr', $all_addr);

  // formatting headers
  $fromEmail = $message->getFrom()->first()->toArray()['mail'];
  $fromName = $message->getFrom()->first()->toArray()['personal'];

  // the subject could be UTF-8
  // CiviCRM will force '<' and '>' to htmlentities, so handle it here
  $msgSubject = mb_strcut(htmlspecialchars($message->getSubject(), ENT_QUOTES), 0, 255);
  $msgDate = $message->getDate()->first()->toArray()['formatted'];
  $msgBody = $message->getHTMLBody() ?? $message->getTextBody();
  $msgUid = $message->getUid();

  /**
   * If there is at least one secondary address, we WILL use an address from
   * this array.  If any address is not an authorized sender, use it,
   * otherwise, use the first one.
   */
  if (is_array($all_addr['secondary']) && !empty($all_addr['secondary'])) {
    $foundIndex = 0;
    foreach ($all_addr['secondary'] as $k => $v) {
      // if this address is NOT an authorized forwarder
      if (!isAuthForwarder($v['address'], $authForwarders)) {
        $foundIndex = $k;
        break;
      }
    }
    $senderEmail = $all_addr['secondary'][$foundIndex]['address'];
    $senderName = $all_addr['secondary'][$foundIndex]['name'];
    $fwderEmail = $fromEmail;
  }
  elseif (!isAuthForwarder($all_addr['primary']['address'], $authForwarders)) {
    // If secondary addresses were not populated, we can use the primary if
    // it is not an authorized forwarder.  This is a direct (non-forwarded)
    // message.
    $senderEmail = $all_addr['primary']['address'];
    $senderName  = $all_addr['primary']['name'];
    $fwderEmail = '';
  }
  else {
    // final failure - no addresses found
    $senderEmail = $senderName = NULL;
    $fwderEmail = $fromEmail;
  }

  if ($senderEmail === NULL) {
    $senderEmail = '';
  }

  if ($senderName === NULL) {
    $senderName = '';
  }

  $sql = "
    INSERT INTO nyss_inbox_messages
    (message_id, sender_name, sender_email, subject, body,
     forwarder, status, updated_date, email_date)
    VALUES (%1, %2, %3, %4, %5, %6, %7, CURRENT_TIMESTAMP, %8)
  ";

  try {
    CRM_Core_DAO::executeQuery($sql, [
      1 => [$msgUid, 'Positive'],
      2 => [$senderName, 'String'],
      3 => [$senderEmail, 'String'],
      4 => [$msgSubject, 'String'],
      5 => [$msgBody ?? '', 'String'],
      6 => [$fwderEmail ?? '', 'String'],
      7 => [STATUS_UNPROCESSED, 'Positive'],
      8 => [$msgDate, 'String'],
    ]);
  }
  catch (CRM_Core_Exception $e) {
    bbscript_log(LL::ERROR, '$e', $e);
    return FALSE;
  }

  $rowId = CRM_Core_DAO::singleValueQuery("SELECT LAST_INSERT_ID()");
  bbscript_log(LL::DEBUG, "Inserted message with rowId = $rowId");

  if ($message->hasAttachments()) {
    bbscript_log(LL::INFO, "Fetching and storing attachments");

    $timeStart = microtime(true);
    if (!storeAttachments($message, $params, $rowId)) {
      bbscript_log(LL::WARN, "Unable to store attachments");
    }
    $totalTime = microtime(true) - $timeStart;
    bbscript_log(LL::DEBUG, "Attachment processing time: $totalTime");
  }

  return TRUE;
} // storeMessage()


// Process each message, looking for a match between the sender's email
// address in the message and a contact record with the same email address.
// If there is a single match on a contact record, an inbound email activity
// is created and associated with the contact.
function searchForMatches($params)
{
  $authForwarders = $params['authForwarders'];
  $uploadDir = $params['uploadDir'];
  $recheck = $params['recheck'];

  // Check the unprocessed messages (status=99)
  $q = 'SELECT id, message_id, sender_email,
               subject, body, forwarder, updated_date
        FROM nyss_inbox_messages
        WHERE status='.STATUS_UNPROCESSED;
  $status_str = 'Unprocessed';

  // If "recheck" was specified, then also check unmatched messages (status=0)
  if ($recheck === true) {
    $q .= ' OR status='.STATUS_UNMATCHED;
    $status_str .= '/Unmatched';
  }

  bbscript_log(LL::NOTICE, "Obtaining list of $status_str messages to be checked");

  $mres = CRM_Core_DAO::executeQuery($q);

  bbscript_log(LL::DEBUG, "$status_str records: ".$mres->N);

  $sql_stmt = "
    SELECT DISTINCT c.id
    FROM civicrm_contact c, civicrm_email e
    WHERE c.id = e.contact_id
      AND c.is_deleted = 0
      AND e.email LIKE %1
    ORDER BY c.id ASC
  ";
  $rows = $mres->fetchAll();

  foreach ($rows as $row) {
    $msg_row_id = $row['id'];
    $message_id = $row['message_id'];
    $sender_email = $row['sender_email'];
    $subject = $row['subject'];
    $body = $row['body'];
    $forwarder = $row['forwarder'];
    $email_date = $row['updated_date'];

    bbscript_log(LL::DEBUG, "Processing Record ID: $msg_row_id");

    // Use the e-mail from the body of the message (or header if direct) to
    // find target contact
    bbscript_log(LL::INFO, "Looking for the original sender ($sender_email) in Civi");

    $results = CRM_Core_DAO::executeQuery($sql_stmt, [1 => [$sender_email, 'String']])->fetchAll();

    $contactID = 0;
    $matched_count = 0;
    foreach ($results as $result) {
      $contactID = $result['id'];
      $matched_count++;
    }

    // No matches, or more than one match, mark message as UNMATCHED.
    if ($matched_count != 1) {
      bbscript_log(LL::DEBUG, "Original sender $sender_email matches [$matched_count] records in this instance; leaving for manual addition");

      // mark it to show up on unmatched screen
      $status = STATUS_UNMATCHED;

      CRM_Core_DAO::executeQuery("
        UPDATE nyss_inbox_messages
        SET status = %1
        WHERE id = %2
      ", [
        1 => [$status, 'String'],
        2 => [$msg_row_id, 'Positive'],
      ]);
    }
    else {
      // Matched on a single contact.  Success!
      bbscript_log(LL::INFO, "Original sender [$sender_email] had a direct match (cid=$contactID)");

      // Set the activity creator ID to the contact ID of the forwarder.
      if (isset($authForwarders['emails'][$forwarder])) {
        $forwarderId = $authForwarders['emails'][$forwarder];
        bbscript_log(LL::INFO, "Forwarder [$forwarder] mapped to cid=$forwarderId");
      }
      else {
        $forwarderId = 1;
        bbscript_log(LL::WARN, "Unable to locate [$forwarder] in the auth forwarder mapping table; using Bluebird Admin");
      }

      // create the activity
      $activityDefaults = $params['activityDefaults'];
      $activityParams = [
        "source_contact_id" => $forwarderId,
        "subject" => $subject,
        "details" =>  $body,
        "activity_date_time" => $email_date,
        "status_id" => $activityDefaults['status'],
        "priority_id" => $activityDefaults['priority'],
        "activity_type_id" => $activityDefaults['type'],
        "duration" => 1,
        "is_auto" => 1,
        "target_contact_id" => $contactID,
        "version" => 3
      ];

      $activityResult = civicrm_api('activity', 'create', $activityParams);

      if ($activityResult['is_error']) {
        bbscript_log(LL::ERROR, "Could not save activity; {$activityResult['error_message']}");
      }
      else {
        $activityId = $activityResult['id'];
        bbscript_log(LL::INFO, "CREATED e-mail activity id=$activityId for contact id=$contactID");
        $status = STATUS_MATCHED;

        //in v2.1 we split matched_id (formerly matched_to) and activity_id into related table
        $q = "
          INSERT IGNORE INTO nyss_inbox_messages_matched
          (row_id, message_id, matched_id, activity_id)
          VALUES
          ({$msg_row_id}, {$message_id}, {$contactID}, {$activityId})
        ";
        CRM_Core_DAO::executeQuery($q);

        //update status to matched
        $q = "
          UPDATE nyss_inbox_messages
          SET status = $status, matcher = 1
          WHERE id = $msg_row_id
        ";
        CRM_Core_DAO::executeQuery($q);

        $q = "
          SELECT file_name, file_full, rejection, mime_type
          FROM nyss_inbox_attachments
          WHERE email_id = $msg_row_id
        ";
        $aresResult = CRM_Core_DAO::executeQuery($q)->fetchAll();

        foreach ($aresResult as $ares) {
          if ((!isset($ares['rejection']) || $ares['rejection'] == '')
              && file_exists($ares['file_full'])
          ) {
            bbscript_log(LL::INFO,
              "Adding attachment ".$ares['file_full']." to activity id=$activityId");
            $date = date("Y-m-d H:i:s");
            $newName = CRM_Utils_File::makeFileName($ares['file_name']);
            $file = "$uploadDir/$newName";
            // Move file to the CiviCRM custom upload directory
            rename($ares['file_full'], $file);

            $q = "
              INSERT INTO civicrm_file
              (mime_type, uri, upload_date)
              VALUES ('{$ares['mime_type']}', '$newName', '$date')
            ";
            CRM_Core_DAO::executeQuery($q);

            $q = "SELECT id FROM civicrm_file WHERE uri='{$newName}' LIMIT 1";
            $fileId = CRM_Core_DAO::singleValueQuery($q);

            $q = "
              INSERT INTO civicrm_entity_file
              (entity_table, entity_id, file_id)
              VALUES ('civicrm_activity', $activityId, $fileId)
            ";
            CRM_Core_DAO::executeQuery($q);
          }
        } // while rows in nyss_inbox_attachments
      } // if activity created
    } // if single match on e-mail address
  } // while rows in nyss_inbox_messages

  bbscript_log(LL::DEBUG, "Finished processing unprocessed/unmatched messages");
} // searchForMatches()


function listMailboxes($imap) {
  $imap_conn = $imap->getConnection();

  //get folder listing to determine if we need to create any
  $folders = $imap_conn->getFolders(FALSE);
  bbscript_log(LL::TRACE, '$folders: ', $folders);

  $folderList = [];
  foreach ($folders as $folder) {
    $folderList[] = $folder->path;
    echo "{$folder->path}\n";
  }
  bbscript_log(LL::TRACE, '$folderList', $folderList);

  return $folderList;
}

function deleteArchiveBox($imap, $params) {
  $imap_conn = $imap->getConnection();
  $archive = $imap_conn->getFolderByPath($params['archivebox']);
  bbscript_log(LL::NOTICE, "Deleting archive mailbox: {$params['archivebox']}");

  return $archive->delete();
}


function sendDenialEmail($site, $email) {
  $subj = INVALID_EMAIL_SUBJECT." [$site]";
  $text = "CRM Instance: $site\n\n".INVALID_EMAIL_TEXT;
  $mailParams = [
    'from' => INVALID_EMAIL_FROM,
    'toEmail' => $email,
    'subject' => $subj,
    'html' => str_replace("\n", '<br/>', $text),
    'text' => $text
  ];

  $rc = CRM_Utils_Mail::send($mailParams);
  if ($rc == true) {
    bbscript_log(LL::NOTICE, "Denial e-mail has been sent to $email");
  }
  else {
    bbscript_log(LL::WARN, "Unable to send a denial e-mail to $email");
  }

  return $rc;
} // sendDenialEmail()


function getImapParam($optlist, $optname, $bbcfg, $cfgname, $defval) {
  if (!empty($optlist[$optname])) {
    return $optlist[$optname];
  }
  elseif ($cfgname && isset($bbcfg[$cfgname])) {
    return $bbcfg[$cfgname];
  }
  else {
    return $defval;
  }
} // getImapParam()
