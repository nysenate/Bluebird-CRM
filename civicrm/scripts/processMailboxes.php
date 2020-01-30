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
//

// Version number, used for debugging
define('VERSION_NUMBER', 2.1);

// Mailbox settings common to all CRM instances
define('DEFAULT_IMAP_SERVER', 'senmail.nysenate.gov');
define('DEFAULT_IMAP_PORT', 143);
define('DEFAULT_IMAP_FLAGS', '/imap/notls');
define('DEFAULT_IMAP_MAILBOX', 'INBOX');
define('DEFAULT_IMAP_ARCHIVEBOX', 'Archive');
define('DEFAULT_IMAP_VALID_SENDERS', false);
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
define('ATTACHMENT_FILE_EXTS', 'pdf|txt|text|rtf|odt|doc|ppt|csv|doc|docx|xls');

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
$usage = "[--imap-user|-u username]  [--imap-pass|-P password]  [--cmd|-c <poll|list|delarchive>]  [--log-level|-l LEVEL]  [--server|-s imap_server]  [--port|-p imap_port]  [--imap-flags|-f imap_flags]  [--mailbox|-m name]  [--archivebox|-a name]  [--valid-senders|-v EMAILS]  [--default-activity-status|-d <Completed|Scheduled|Cancelled>]  [--no-archive|-n]  [--no-email|-e]  [--recheck-unmatched|-r]";
$shortopts = "u:P:c:l:s:p:f:m:a:v:d:ner";
$longopts = array("imap-user=", "imap-pass=", "cmd=", "log-level=",
                  "server=", "port=", "imap-flags=", "mailbox=", "archivebox=",
                  "valid-senders=", "default-activity-status=",
                  "no-archive", "no-email", "recheck-unmatched");

$optlist = civicrm_script_init($shortopts, $longopts);

if ($optlist === null) {
  error_log("Usage: $prog  $stdusage  $usage\n");
  exit(1);
}

if (!empty($optlist['log-level'])) {
  set_bbscript_log_level($optlist['log-level']);
}

/* More than one IMAP account can be checked per CRM instance.
** The username and password for each account is specified in the Bluebird
** config file.
**
** The user= and pass= command line args can be used to override the IMAP
** accounts from the config file.
*/

$bbconfig = get_bluebird_instance_config();

$site = $optlist['site'];
$cmd = $optlist['cmd'];
$g_crm_instance = $site;

$all_params = [
  // Each element is: paramName, optName, bbcfgName, defaultVal
  array('site', 'site', null, null),
  array('server', 'server', 'imap.server', DEFAULT_IMAP_SERVER),
  array('port', 'port', 'imap.port', DEFAULT_IMAP_PORT),
  array('flags', 'imap-flags', 'imap.flags', DEFAULT_IMAP_FLAGS),
  array('mailbox', 'mailbox', 'imap.mailbox', DEFAULT_IMAP_MAILBOX),
  array('archivebox', 'archivebox', 'imap.archivebox', DEFAULT_IMAP_ARCHIVEBOX),
  array('validsenders', 'valid-senders', 'imap.validsenders', DEFAULT_IMAP_VALID_SENDERS),
  array('actstatus', 'default-activity-status', 'imap.activity.status.default', DEFAULT_IMAP_ACTIVITY_STATUS),
  array('noarchive', 'no-archive', null, DEFAULT_IMAP_NO_ARCHIVE),
  array('noemail', 'no-email', null, DEFAULT_IMAP_NO_EMAIL),
  array('recheck', 'recheck-unmatched', null, DEFAULT_IMAP_RECHECK)
];

$imap_params = [];

foreach ($all_params as $param) {
  $val = getImapParam($optlist, $param[1], $bbconfig, $param[2], $param[3]);
  if ($val !== null) {
    $imap_params[$param[0]] = $val;
  }
}

if (!empty($optlist['imap-user']) && !empty($optlist['imap-pass'])) {
  $imap_accounts = $optlist['imap-user'].'|'.$optlist['imap-pass'];
}
else {
  $imap_accounts = $bbconfig['imap.accounts'];
}

if ($cmd == 'list') {
  $cmd = IMAP_CMD_LIST;
}
else if ($cmd == 'delarchive') {
  $cmd = IMAP_CMD_DELETE;
}
else if ($cmd == 'poll' || !$cmd) {
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

if (empty($imap_accounts)) {
  echo "$prog: No IMAP accounts to process for CRM instance [$site]\n";
  exit(1);
}

$authForwarders = array(
  'emails' => getAuthorizedForwarders(),
  'patterns' => array()
);

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

bbscript_log(LL::DEBUG, "imap_params before account loop:", $imap_params);


// Iterate over all IMAP accounts associated with the current CRM instance.

foreach (explode(',', $imap_accounts) as $imap_account) {
  list($imapUser, $imapPass) = explode("|", $imap_account);
  $imap_params['user'] = $imapUser;
  $imap_params['password'] = $imapPass;
  $rc = processMailboxCommand($cmd, $imap_params);
  if ($rc == false) {
    bbscript_log(LL::ERROR, "Failed to process IMAP account $imapUser@{$imap_params['server']}\n".print_r(imap_errors(), true));
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



function isAuthForwarder($email, $fwders)
{
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



function processMailboxCommand($cmd, $params)
{
  try {
    $imap_session = new CRM_NYSS_IMAP_Session($params);
  }
  catch (Exception $ex) {
    bbscript_log(LL::ERROR, "Failed to create IMAP session: ".$ex->getMessage());
    $imap_session = null;
    return false;
  }

  if ($cmd == IMAP_CMD_POLL) {
    $rc = checkImapAccount($imap_session, $params);
  }
  else if ($cmd == IMAP_CMD_LIST) {
    $rc = listMailboxes($imap_session, $params);
  }
  else if ($cmd == IMAP_CMD_DELETE) {
    $rc = deleteArchiveBox($imap_session, $params);
  }
  else {
    bbscript_log(LL::ERROR, "Invalid command [$cmd], params=".print_r($params, true));
    $rc = false;
  }

  // Changes to the IMAP mailbox do not take effect unless the CL_EXPUNGE
  // flag is provided to the imap_close() call, or if imap_expunge() is
  // explicitly called.  Also note that if the connection was opened with
  // the readonly flag set, then no changes will be made to the mailbox.
  // The destructor handles all of this.
  $imap_session = null;

  return $rc;
} // processMailboxCommand()



// Check the given IMAP account for new messages, and process them.

function checkImapAccount($imapSess, $params)
{
  bbscript_log(LL::NOTICE, "Polling CRM [".$params['site']."] using IMAP account ".$params['user'].'@'.$params['server'].$params['flags']);

  $imap_conn = $imapSess->getConnection();
  $crm_archivebox = '{'.$params['server'].'}'.$params['archivebox'];

  //create archive box in case it doesn't exist
  //don't report errors since it will almost always fail
  if ($params['noarchive'] == false) {
    $rc = imap_createmailbox($imap_conn, imap_utf7_encode($crm_archivebox));
    if ($rc) {
      bbscript_log(LL::DEBUG, "Created new archive mailbox: $crm_archivebox");
    }
    else {
      bbscript_log(LL::DEBUG, "Archive mailbox $crm_archivebox already exists");
    }
  }
  else {
    bbscript_log(LL::WARN, "Messages will not be archived since --no-archive was specified");
  }

  // start db connection
  $nyss_conn = new CRM_Core_DAO();
  $nyss_conn = $nyss_conn->getDatabaseConnection();
  $dbconn = $nyss_conn->connection;

  $msg_count = $imapSess->fetchMessageCount();
  $invalid_fwders = array();
  bbscript_log(LL::NOTICE, "Number of messages in IMAP inbox: $msg_count");

  for ($msg_num = 1; $msg_num <= $msg_count; $msg_num++) {
    bbscript_log(LL::INFO, "Retrieving message $msg_num / $msg_count");
    $imap_message = new CRM_NYSS_IMAP_Message($imapSess, $msg_num);
    $msgMetaData = $imap_message->getMetaData();
    bbscript_log(LL::DEBUG, "metadata", $msgMetaData);
    $fwder = strtolower($msgMetaData->fromEmail);

    // check whether or not the forwarder is valid
    if (isAuthForwarder($fwder, $params['authForwarders'])) {
      bbscript_log(LL::DEBUG, "Forwarder [$fwder] is allowed to send to this mailbox");

      // retrieved msg, now store to Civi and if successful move to archive
      if (storeMessage($imap_message, $dbconn, $params) == true) {
        //mark as read
        imap_setflag_full($imap_conn, $msgMetaData->uid, '\\Seen', ST_UID);
        // move to folder if necessary
        if ($params['noarchive'] == false) {
          $abox = $params['archivebox'];
          if (imap_mail_move($imap_conn, $msg_num, $abox)) {
            bbscript_log(LL::DEBUG, "Messsage $msg_num moved to $abox");
          }
          else {
            bbscript_log(LL::ERROR, "Failed to move message $msg_num to $abox");
          }
        }
      }
    }
    else {
      bbscript_log(LL::WARN, "Forwarder [$fwder] is not allowed to forward/send messages to this CRM; deleting message");
      $invalid_fwders[$fwder] = true;
      if (imap_delete($imap_conn, $msg_num) === true) {
        bbscript_log(LL::DEBUG, "Message $msg_num has been deleted");
      }
      else {
        bbscript_log(LL::WARN, "Unable to delete message $msg_num from mailbox");
      }
    }
  }

  $invalid_fwder_count = count($invalid_fwders);
  if ($invalid_fwder_count > 0) {
    if ($params['noemail'] == false) {
      bbscript_log(LL::NOTICE, "Sending denial e-mails to $invalid_fwder_count e-mail address(es)");
      foreach ($invalid_fwders as $invalid_fwder => $dummy) {
        sendDenialEmail($params['site'], $invalid_fwder);
      }
    }
    else {
      bbscript_log(LL::ERROR, "Suppressing the delivery of denial emails to $invalid_fwder_count invalid forwarder address(es) since --no-email was specified");
    }
  }

  bbscript_log(LL::NOTICE, "Finished checking IMAP account ".$params['user'].'@'.$params['server'].$params['flags']);

  bbscript_log(LL::NOTICE, "Searching for matches between message senders and contact records");
  searchForMatches($dbconn, $params);

  return true;
} // checkImapAccount()



// Store the attachments for the given message in the database and local
// file system.  Meta data about each attachment is stored in the database,
// while the actual content is stored in the file system.
// $rowId is the primary key that was generated when the message was stored.
function storeAttachments($imapMsg, $db, $params, $rowId)
{
  $bSuccess = true;
  $pattern = '/^('.ATTACHMENT_FILE_EXTS.')$/';
  $uploadInbox = $params['uploadInbox'];

  // Load attachment data and save to database and local filesystem.

  // Prepare the SQL statement first, so it can be reused in the loop.
  $q = "INSERT INTO nyss_inbox_attachments
        (email_id, file_name, file_full, size, mime_type, ext, rejection)
        VALUES (?, ?, ?, ?, ?, ?, ?)";
  $sql_stmt = mysqli_prepare($db, $q);
  if ($sql_stmt === false) {
    bbscript_log(LL::ERROR, "Unable to prepare SQL statement [$q]");
    return false;
  }

  foreach ($imapMsg->fetchAttachments() as $attachment) {
    $fname = $attachment->name;
    $size = $attachment->size;
    $type = $attachment->type;
    $content = $attachment->data;
    $fileExt = substr(strrchr($fname, '.'), 1);
    $civiFilename = CRM_Utils_File::makeFileName($fname);
    $rej_reason = null;

    // Allow body type 3 (application) with certain file extensions,
    // and allow body types 4 (audio), 5 (image), 6 (video).
    if (($type == TYPEAPPLICATION && preg_match($pattern, $fileExt))
        || $type == TYPEAUDIO || $type == TYPEIMAGE || $type == TYPEVIDEO) {
      if ($size > MAX_ATTACHMENT_SIZE) {
        $rej_reason = "File is larger than ".MAX_ATTACHMENT_SIZE." bytes";
      }
    }
    else {
      $label = $imapMsg->getBodyTypeLabel($type);
      $rej_reason = "File type [$label/$fileExt] not allowed";
    }

    if ($rej_reason == null) {
      $fileFull = $uploadInbox.'/'.$civiFilename;
      bbscript_log(LL::INFO, "Writing attachment data to $fileFull");
      $fp = fopen("$fileFull", "w+");
      fwrite($fp, $content);
      fclose($fp);
      bbscript_log(LL::DEBUG, "Getting mime type of file $fileFull");
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mime = finfo_file($finfo, $fileFull);
      finfo_close($finfo);
    }
    else {
      $fileFull = '';
      $mime = '';
    }

    if (mysqli_stmt_bind_param($sql_stmt, 'ississs', $rowId, $fname, $fileFull,
                               $size, $mime, $fileExt, $rej_reason) == false) {
      bbscript_log(LL::ERROR, "Unable to bind params for attachment [$fname]");
      $bSuccess = false;
      continue;
    }

    if (mysqli_stmt_execute($sql_stmt) == false) {
      bbscript_log(LL::ERROR, "Unable to insert attachment [$fileFull] for msgid=$rowId");
      $errorDetails = print_r(mysqli_stmt_error($sql_stmt), true);
      bbscript_log(LL::ERROR, "<pre>{$errorDetails}</pre>");
      $bSuccess = false;
    }
  }

  mysqli_stmt_close($sql_stmt);

  $q = "SELECT id FROM nyss_inbox_attachments WHERE email_id=$rowId";
  $res = mysqli_query($db, $q);
  $dbAttachmentCount = mysqli_num_rows($res);
  mysqli_free_result($res);

  if ($dbAttachmentCount > 0) {
    bbscript_log(LL::DEBUG, "Inserted $dbAttachmentCount attachments");
  }
  return $bSuccess;
} // storeAttachments()



// Store the various metadata of the given message, plus its content.
// This calls storeAttachments() to download and store the attachments.
// Returns true/false to move the email to archive or not.
function storeMessage($imapMsg, $db, $params)
{
  $authForwarders = $params['authForwarders'];
  $msgMeta = $imapMsg->getMetaData();
  $all_addr = $imapMsg->findFromAddresses();

  // check for plain/html body text
  $msgStruct = $imapMsg->getStructure();
  bbscript_log(LL::DEBUG, "all_addr", $all_addr);

  // formatting headers
  $fromEmail = substr($msgMeta->fromEmail, 0, 200);
  $fromName = substr($msgMeta->fromName, 0, 200);  // appears to be unused
  // the subject could be UTF-8
  // CiviCRM will force '<' and '>' to htmlentities, so handle it here
  $fwdSubject = mb_strcut(htmlspecialchars($msgMeta->subject, ENT_QUOTES), 0, 255);
  $fwdDate = $msgMeta->date;
  $fwdBody = $imapMsg->renderAsHtml();
  $msgUid = $msgMeta->uid;

  /** If there is at least one secondary address, we WILL use an address from
   *  this array.  If any address is not an authorized sender, use it,
   *  otherwise, use the first one.
   */
  if (is_array($all_addr['secondary']) && count($all_addr['secondary']) > 0) {
    $foundIndex = 0;
    foreach ($all_addr['secondary'] as $k => $v) {
      // if this address is NOT an authorized forwarder
      if (!isAuthForwarder($v['address'], $authForwarders)) {
        $foundIndex = $k;
        break;
      }
    }
    $fwdEmail = $all_addr['secondary'][$foundIndex]['address'];
    $fwdName = $all_addr['secondary'][$foundIndex]['name'];
  }
  elseif (!isAuthForwarder($all_addr['primary']['address'], $authForwarders)) {
    // if secondary addresses were not populated, we can use the primary if
    // it is not an authorized forwarder
    $fwdEmail = $all_addr['primary']['address'];
    $fwdName  = $all_addr['primary']['name'];
  }
  else {
    // final failure - no addresses found
    $fwdEmail = $fwdName = null;
  }

  if ($fwdEmail === null) {
    $fwdEmail = '';
  }
  if ($fwdName === null) {
    $fwdName = '';
  }

  // The default status for newly saved messages is UNPROCESSED.
  $status = STATUS_UNPROCESSED;

  $q = "INSERT INTO nyss_inbox_messages
        (message_id, sender_name, sender_email, subject, body,
         forwarder, status, updated_date, email_date)
        VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, ?)";

  $sql_stmt = mysqli_prepare($db, $q);
  if ($sql_stmt == false) {
    bbscript_log(LL::ERROR, "Unable to prepare SQL statement [$q]");
    return false;
  }

  if (mysqli_stmt_bind_param($sql_stmt, 'isssssis', $msgUid, $fwdName,
                             $fwdEmail, $fwdSubject, $fwdBody, $fromEmail,
                             $status, $fwdDate) == false) {
    bbscript_log(LL::ERROR, "Unable to bind params for msgUid=$msgUid");
    mysqli_stmt_close($sql_stmt);
    return false;
  }

  if (mysqli_stmt_execute($sql_stmt) == false) {
    bbscript_log(LL::ERROR, "Unable to insert msgid=$msgUid; ".mysqli_error($db)."; query:", $q);
    mysqli_stmt_close($sql_stmt);
    return false;
  }

  mysqli_stmt_close($sql_stmt);
  $rowId = mysqli_insert_id($db);
  bbscript_log(LL::DEBUG, "Inserted message with id=$rowId");

  if ($imapMsg->hasAttachments()) {
    bbscript_log(LL::INFO, "Fetching and storing attachments");
    $timeStart = microtime(true);
    if (storeAttachments($imapMsg, $db, $params, $rowId) == false) {
      bbscript_log(LL::WARN, "Unable to store attachments");
    }
    $totalTime = microtime(true) - $timeStart;
    bbscript_log(LL::DEBUG, "Attachment processing time: $totalTime");
  }
  return true;
} // storeMessage()


// Process each message, looking for a match between the sender's email
// address in the message and a contact record with the same email address.
// If there is a single match on a contact record, an inbound email activity
// is created and associated with the contact.
function searchForMatches($db, $params)
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

  $mres = mysqli_query($db, $q);
  if ($mres === false) {
    bbscript_log(LL::ERROR, "Unable to retrieve $status_str messages; ".mysqli_error($db));
    return false;
  }

  bbscript_log(LL::DEBUG, "$status_str records: ".mysqli_num_rows($mres));

  $q = "SELECT DISTINCT c.id FROM civicrm_contact c, civicrm_email e
        WHERE c.id = e.contact_id AND c.is_deleted=0 AND e.email LIKE ?
        ORDER BY c.id ASC";
  $sql_stmt = mysqli_prepare($db, $q);
  if ($sql_stmt == false) {
    bbscript_log(LL::ERROR, "Unable to prepare SQL query [$q]");
    mysqli_free_result($mres);
    return false;
  }

  while ($row = mysqli_fetch_assoc($mres)) {
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

    mysqli_stmt_bind_param($sql_stmt, 's', $sender_email);
    mysqli_stmt_execute($sql_stmt);
    $result = mysqli_stmt_get_result($sql_stmt);
    if ($result === false) {
      bbscript_log(LL::ERROR, "Query for match on [$sender_email] failed; ".mysqli_error($db));
      continue;
    }

    $contactID = 0;
    $matched_count = 0;
    while ($row = mysqli_fetch_assoc($result)) {
      $contactID = $row['id'];
      $matched_count++;
    }
    mysqli_free_result($result);

    // No matches, or more than one match, marks message as UNMATCHED.
    if ($matched_count != 1) {
      bbscript_log(LL::DEBUG, "Original sender $sender_email matches [$matched_count] records in this instance; leaving for manual addition");
      // mark it to show up on unmatched screen
      $status = STATUS_UNMATCHED;
      $q = "UPDATE nyss_inbox_messages SET status=$status WHERE id=$msg_row_id";
      if (mysqli_query($db, $q) == false) {
        bbscript_log(LL::ERROR, "Unable to update status of message id=$msg_row_id");
      }
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
      $activityParams = array(
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
      );

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
        if (mysqli_query($db, $q) == false) {
          bbscript_log(LL::ERROR,
            "Unable to store matched_id and activity_id for message id=$msg_row_id");
        }
        else {
          //update status to matched
          $q = "
            UPDATE nyss_inbox_messages
            SET status = $status, matcher = 1
            WHERE id = $msg_row_id
          ";
          if (mysqli_query($db, $q) == false) {
            bbscript_log(LL::ERROR,
              "Unable to update status for message id=$msg_row_id");
          }
        }

        $q = "
          SELECT file_name, file_full, rejection, mime_type
          FROM nyss_inbox_attachments
          WHERE email_id = $msg_row_id";
        $ares = mysqli_query($db, $q);

        while ($row = mysqli_fetch_assoc($ares)) {
          if ((!isset($row['rejection']) || $row['rejection'] == '')
              && file_exists($row['file_full'])) {
            bbscript_log(LL::INFO,
              "Adding attachment ".$row['file_full']." to activity id=$activityId");
            $date = date("Y-m-d H:i:s");
            $newName = CRM_Utils_File::makeFileName($row['file_name']);
            $file = "$uploadDir/$newName";
            // Move file to the CiviCRM custom upload directory
            rename($row['file_full'], $file);

            $q = "
              INSERT INTO civicrm_file
              (mime_type, uri, upload_date)
              VALUES ('{$row['mime_type']}', '$newName', '$date')
            ";
            if (mysqli_query($db, $q) == false) {
              bbscript_log(LL::ERROR,
                "Unable to insert attachment file info for [$newName]");
            }

            $q = "SELECT id FROM civicrm_file WHERE uri='{$newName}'";
            $res = mysqli_query($db, $q);
            while ($row = mysqli_fetch_assoc($res)) {
              $fileId = $row['id'];
            }
            mysqli_free_result($res);

            $q = "
              INSERT INTO civicrm_entity_file
              (entity_table, entity_id, file_id)
              VALUES ('civicrm_activity', $activityId, $fileId)
            ";
            if (mysqli_query($db, $q) == false) {
              bbscript_log(LL::ERROR,
                "Unable to insert attachment mapping from activity id=$activityId to file id=$fileId");
            }
          }
        } // while rows in nyss_inbox_attachments
        mysqli_free_result($ares);
      } // if activity created
    } // if single match on e-mail address
  } // while rows in nyss_inbox_messages

  mysqli_stmt_close($sql_stmt);
  mysqli_free_result($mres);
  bbscript_log(LL::DEBUG, "Finished processing unprocessed/unmatched messages");

  return;
} // searchForMatches()


function listMailboxes($imapSess, $params) {
  $inboxes = $imapSess->listFolders('*', true);
  foreach ($inboxes as $inbox) {
    echo "$inbox\n";
  }
  return true;
} // listMailboxes()


function deleteArchiveBox($imapSess, $params) {
  $crm_archivebox = '{'.$params['server'].'}'.$params['archivebox'];
  bbscript_log(LL::NOTICE, "Deleting archive mailbox: $crm_archivebox");
  return imap_deletemailbox($imapSess->getConnection(), $crm_archivebox);
} // deleteArchiveBox()


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
  else if ($cfgname && isset($bbcfg[$cfgname])) {
    return $bbcfg[$cfgname];
  }
  else {
    return $defval;
  }
} // getImapParam()
