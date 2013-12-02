<?php
// processMailboxes.php
//
// Project: BluebirdCRM
// Author: Ken Zalewski & Stefan Crain
// Organization: New York State Senate
// Date: 2011-03-22
// Revised: 2013-04-27
//

// Version number, used for debugging
define('VERSION_NUMBER', 0.05);

// Mailbox settings common to all CRM instances
define('DEFAULT_IMAP_SERVER', 'webmail.senate.state.ny.us');
define('DEFAULT_IMAP_OPTS', '/imap/ssl/notls');
define('DEFAULT_IMAP_MAILBOX', 'Inbox');
define('DEFAULT_IMAP_ARCHIVEBOX', 'Archive');
define('DEFAULT_IMAP_PROCESS_UNREAD_ONLY', false);
define('DEFAULT_IMAP_ARCHIVE_MAIL', true);

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

error_reporting(E_ERROR | E_PARSE | E_WARNING);

//no limit
set_time_limit(0);

$prog = basename(__FILE__);

require_once 'script_utils.php';
$stdusage = civicrm_script_usage();
$usage = "[--server|-s imap_server]  [--imap-user|-u username]  [--imap-pass|-p password]  [--imap-opts|-o imap_options]  [--cmd|-c <poll|list|delarchive>]  [--mailbox|-m name]  [--archivebox|-a name]  [--unread-only|-r]  [--archive-mail|-t]";
$shortopts = "s:u:p:o:c:m:a:rt";
$longopts = array("server=", "imap-user=", "imap-pass=", "imap-opts=", "cmd=", "mailbox=", "archivebox=", "unread-only", "archive-mail");
$optlist = civicrm_script_init($shortopts, $longopts);
if ($optlist === null) {
  error_log("Usage: $prog  $stdusage  $usage\n");
  exit(1);
}

require_once 'CRM/Core/Config.php';
$config =& CRM_Core_Config::singleton();
$session =& CRM_Core_Session::singleton();

require_once 'CRM/Contact/BAO/Contact.php';
require_once 'CRM/Contact/BAO/GroupContact.php';
require_once 'CRM/Activity/BAO/Activity.php';
require_once 'CRM/Core/Transaction.php';
require_once 'CRM/Core/BAO/CustomValueTable.php';
require_once 'CRM/Core/PseudoConstant.php';
require_once 'CRM/Core/Error.php';
require_once 'api/api.php';
require_once 'CRM/Core/DAO.php';
require_once 'CRM/Utils/File.php';
require_once 'CRM/Utils/MessageBodyParser.php';


/* More than one IMAP account can be checked per CRM instance.
** The username and password for each account is specified in the Bluebird
** config file.
**
** The user= and pass= command line args can be used to override the IMAP
** accounts from the config file.
*/
$bbconfig = get_bluebird_instance_config();
$imap_accounts = $bbconfig['imap.accounts'];
$imap_validsenders = strtolower($bbconfig['imap.validsenders']);
$imap_activty_status = $bbconfig['imap.activity.status.default'];

$site = $optlist['site'];
$cmd = $optlist['cmd'];
$imap_server = DEFAULT_IMAP_SERVER;
$imap_opts = DEFAULT_IMAP_OPTS;
$imap_mailbox = DEFAULT_IMAP_MAILBOX;
$imap_archivebox = DEFAULT_IMAP_ARCHIVEBOX;
$imap_process_unread_only = DEFAULT_IMAP_PROCESS_UNREAD_ONLY;
$imap_archive_mail = DEFAULT_IMAP_ARCHIVE_MAIL;

if (!empty($optlist['server'])) {
  $imap_server = $optlist['server'];
}
if (!empty($optlist['imap-user']) && !empty($optlist['imap-pass'])) {
  $imap_accounts = $optlist['imap-user'].'|'.$optlist['imap-pass'];
}
if (!empty($optlist['imap-opts'])) {
  $imap_opts = $optlist['imap-opts'];
}
if (!empty($optlist['mailbox'])) {
  $imap_mailbox = $optlist['mailbox'];
}
if (!empty($optlist['archivebox'])) {
  $imap_archivebox = $optlist['archivebox'];
}
if ($optlist['unread-only'] == true) {
  $imap_process_unread_only = true;
}
if ($optlist['archive-mail'] == true) {
  $imap_archive_mail = true;
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

// Grab default values for activities (priority, status, type).
$aActivityPriority = CRM_Core_PseudoConstant::get('CRM_Activity_DAO_Activity', 'priority_id');
$aActivityType = CRM_Core_PseudoConstant::activityType();
$aActivityStatus = CRM_Core_PseudoConstant::activityStatus();

$activityPriority = array_search('Normal', $aActivityPriority);
$activityType = array_search('Inbound Email', $aActivityType);

if ($imap_activty_status == false || !isset($imap_activty_status)) {
  $activityStatus = array_search('Completed', $aActivityStatus);
}else{
  $activityStatus = array_search($imap_activty_status, $aActivityStatus);
}


$activityDefaults = array('priority' => $activityPriority,
                          'status' => $activityStatus,
                          'type' => $activityType);

// This doesn't seem to be used anywhere
$inboxPollingTagId = getInboxPollingTagId();

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

$authForwarders = getAuthorizedForwarders();
if ($imap_validsenders) {
  // If imap.validsenders was specified in the config file, then add those
  // e-mail addresses to the list of authorized forwarders.  The contact ID
  // for each of these "config file" forwarders will be 1 (Bluebird Admin).
  $validSenders = preg_split('/[\s,]+/', $imap_validsenders, null, PREG_SPLIT_NO_EMPTY);
  foreach ($validSenders as $validSender) {
    if ($validSender && isset($authForwarders[$validSender])) {
      echo "[INFO]    Valid sender [$validSender] from config is already in the auth forwarders list\n";
    }
    else {
      $authForwarders[$validSender] = 1;
    }
  }
}

// Iterate over all IMAP accounts associated with the current CRM instance.

foreach (explode(',', $imap_accounts) as $imap_account) {
  list($imapUser, $imapPass) = explode("|", $imap_account);
  $imap_params = array(
    'site' => $site,
    'server' => $imap_server,
    'opts' => $imap_opts,
    'user' => $imapUser,
    'pass' => $imapPass,
    'mailbox' => $imap_mailbox,
    'archivebox' => $imap_archivebox,
    'unreadonly' => $imap_process_unread_only,
    'archivemail' => $imap_archive_mail,
    'authForwarders' => $authForwarders,
    'activityDefaults' => $activityDefaults,
    'uploadDir' => $uploadDir,
    'uploadInbox' => $uploadInbox
  );

  $rc = processMailboxCommand($cmd, $imap_params);
  if ($rc == false) {
    echo "[ERROR]   Failed to process IMAP account $imapUser@$imap_server\n";
    print_r(imap_errors());
  }
}

echo "[INFO]    Finished processing all mailboxes for CRM instance [$site]\n";
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
      echo "[WARN]    '".AUTH_FORWARDERS_GROUP_NAME."' group already has e-mail address [$email] (cid={$res[$email]}); ignoring cid=$cid\n";
    }
    else {
      $res[$email] = $cid;
    }
  }

  return $res;
} // getAuthorizedForwarders()



function processMailboxCommand($cmd, $params)
{
  $serverspec = '{'.$params['server'].$params['opts'].'}'.$params['mailbox'];
  echo "[INFO]    Opening IMAP connection to {$params['user']}@$serverspec\n";
  $imap_conn = imap_open($serverspec, $params['user'], $params['pass']);

  if ($imap_conn === false) {
    echo "[ERROR]   Unable to open IMAP connection to $serverspec\n";
    return false;
  }

  if ($cmd == IMAP_CMD_POLL) {
    $rc = checkImapAccount($imap_conn, $params);
  }
  else if ($cmd == IMAP_CMD_LIST) {
    $rc = listMailboxes($imap_conn, $params);
  }
  else if ($cmd == IMAP_CMD_DELETE) {
    $rc = deleteArchiveBox($imap_conn, $params);
  }
  else {
    echo "[ERROR]  Invalid command [$cmd], params=".print_r($params, true)."\n";
    $rc = false;
  }

  //clean up moved/deleted messages
  // Using CL_EXPUNGE is same as calling imap_expunge().
  imap_close($imap_conn, CL_EXPUNGE);
  return $rc;
} // processMailboxCommand()



// Check the given IMAP account for new messages, and process them.

function checkImapAccount($mbox, $params)
{
  echo "[INFO]    Polling CRM [".$params['site']."] using IMAP account ".
       $params['user'].'@'.$params['server'].$params['opts']."\n";

  $crm_archivebox = '{'.$params['server'].'}'.$params['archivebox'];

  //create archive box in case it doesn't exist
  //don't report errors since it will almost always fail
  if ($params['archivemail'] == true) {
    $rc = imap_createmailbox($mbox, imap_utf7_encode($crm_archivebox));
    if ($rc) {
      echo "[DEBUG]   Created new mailbox: $crm_archivebox\n";
    }
    else {
      echo "[DEBUG]   Archive mailbox $crm_archivebox already exists.\n";
    }
  }

  // start db connection
  $nyss_conn = new CRM_Core_DAO();
  $nyss_conn = $nyss_conn->getDatabaseConnection();
  $dbconn = $nyss_conn->connection;

  $msg_count = imap_num_msg($mbox);
  $invalid_fwders = array();
  echo "[INFO]    Number of messages: $msg_count\n";

  for ($msg_num = 1; $msg_num <= $msg_count; $msg_num++) {
    echo "- - - - - - - - - - - - - - - - - - \n";
    echo "[INFO]    Retrieving message $msg_num / $msg_count\n";
    $msgMetaData = retrieveMetaData($mbox, $msg_num);
    $fwder = 'crain@nysenate.gov'; //strtolower($msgMetaData->fromEmail);

    // check whether or not the forwarder is valid
    if (array_key_exists($fwder, $params['authForwarders'])) {
      echo "[DEBUG]   Forwarder [$fwder] is allowed to send to this mailbox\n";
      // retrieved msg, now store to Civi and if successful move to archive
      if (storeMessage($mbox, $dbconn, $msgMetaData, $params) == true) {
        // //mark as read
	imap_setflag_full($mbox, $msgMetaData->uid, '\\Seen', ST_UID);
	// move to folder if necessary
	if ($params['archivemail'] == true) {
	  imap_mail_move($mbox, $msg_num, $params['archivebox']);
	}
      }
    }
    else {
      echo "[WARN]    Forwarder [$fwder] is not allowed to forward/send messages to this CRM; deleting message\n";
      $invalid_fwders[$fwder] = true;
      if (imap_delete($mbox, $msg_num) === true) {
	echo "[DEBUG]   Message $msg_num has been deleted\n";
      }
      else {
	echo "[WARN]    Unable to delete message $msg_num from mailbox\n";
      }
    }
  }

  $invalid_fwder_count = count($invalid_fwders);
  if ($invalid_fwder_count > 0) {
    echo "[INFO]    Sending denial e-mails to $invalid_fwder_count e-mail address(es)\n";
    foreach ($invalid_fwders as $invalid_fwder => $dummy) {
      sendDenialEmail($params['site'], $invalid_fwder);
    }
  }

  echo "[INFO]    Finished checking IMAP account ".$params['user'].'@'.$params['server'].$params['opts']."\n";

  echo "[INFO]    Searching for matches on unmatched records\n";
  searchForMatches($dbconn, $params);

  return true;
} // checkImapAccount()



function parseMimePart($mbox, $msgid, $p, $partno, &$attachments)
{
  global $uploadInbox;

  //fetch part
  $part = imap_fetchbody($mbox, $msgid, $partno);

  //if type is not text
  if ($p->type != 0) {
    if ($p->encoding == 3) {
      //decode if base64
      $part = base64_decode($part);
    }
    else if ($p->encoding == 4) {
      //decode if quoted printable
      $part = quoted_printable_decode($part);
    }
    //no need to decode binary or 8bit!

    //get filename of attachment if present
    $filename = '';
    // if there are any dparameters present in this part
    if (count($p->dparameters) > 0) {
      foreach ($p->dparameters as $dparam) {
        $attr = strtoupper($dparam->attribute);
        if ($attr == 'NAME' || $attr == 'FILENAME') {
          $filename = $dparam->value;
        }
      }
    }

    //if no filename found
    if ($filename == '') {
      // if there are any parameters present in this part
      if (count($p->parameters) > 0) {
        foreach ($p->parameters as $param) {
          $attr = strtoupper($param->attribute);
          if ($attr == 'NAME' || $attr == 'FILENAME') {
            $filename = $param->value;
          }
        }
      }
    }

    //write to disk and set $attachments variable
    if ($filename != '') {
      $tempfilename = imap_mime_header_decode($filename);
      for ($i = 0; $i < count($tempfilename); $i++) {
        $filename = $tempfilename[$i]->text;
      }
      $fileSize = strlen($part);
      $fileExt = substr(strrchr($filename, '.'), 1);
      $allowed = false;
      $bodyType = $p->type;
      $pattern = '/^('.ATTACHMENT_FILE_EXTS.')$/';

      // Allow body type 3 (application) with certain file extensions,
      // and allow body types 4 (audio), 5 (image), 6 (video).
      if (($bodyType == 3 && preg_match($pattern, $fileExt))
          || ($bodyType >= 4 && $bodyType <= 6)) {
        $allowed = true;
      }
      else {
        $rejected_reason = "File type [$fileExt] not allowed";
      }

      $newName = CRM_Utils_File::makeFileName($filename);

      if ($allowed) {
        if ($fileSize > MAX_ATTACHMENT_SIZE) {
          $allowed = false;
          $rejected_reason = "File is larger than ".MAX_ATTACHMENT_SIZE." bytes";
        }
      }

      if ($allowed) {
        $fp = fopen("$uploadInbox/$newName", "w+");
        fwrite($fp, $part);
        fclose($fp);
      }

      $attachments[] = array('filename'=>$filename, 'civifilename'=>$newName, 'extension'=>$fileExt, 'size'=>$fileSize, 'allowed'=>$allowed, 'rejected_reason'=>$rejected_reason);
    }
  }

  //if subparts... recurse into function and parse them too!
  if (count($p->parts) > 0) {
    foreach ($p->parts as $pno => $parr) {
      parseMimePart($mbox, $msgid, $parr, $partno.'.'.($pno+1), $attachments);
    }
  }
  return true;
} // parseMimePart()



function retrieveMetaData($mbox, $msgid)
{
  // fetch info
  $timeStart = microtime(true);
  $header = imap_rfc822_parse_headers(imap_fetchheader($mbox, $msgid));
  $imap_uid = imap_uid($mbox, $msgid);

  // build email object
  $metaData = new stdClass();
  $metaData->subject = $header->subject;
  $metaData->fromName = $header->reply_to[0]->personal;
  $metaData->fromEmail = $header->reply_to[0]->mailbox.'@'.$header->reply_to[0]->host;
  $metaData->uid = $imap_uid;
  $metaData->msgid = $msgid;
  $metaData->date = date("Y-m-d H:i:s", strtotime($header->date));
  $timeEnd = microtime(true);
  echo "[DEBUG]   Fetch header time: ".($timeEnd-$timeStart)."\n";
  return $metaData;
} // retrieveMetaData()



// storeMessage
// Parses multipart message and stores in Civi database
// Returns true/false to move the email to archive or not.
function storeMessage($mbox, $db, $msgMeta, $params)
{
  $msgid = $msgMeta->msgid;
  $bSuccess = true;
  $uploadInbox = $params['uploadInbox'];

  $timeStart = microtime(true);

  // check for plain/html body text
  $msgStruct = imap_fetchstructure($mbox, $msgid);

  if (!isset($msgStruct->parts) || !$msgStruct->parts) { // not multipart
    $rawBody[$msgStruct->subtype] = array(
        'encoding' => $msgStruct->encoding,
        'body' => imap_fetchbody($mbox, $msgid, '1'),
        'debug' => $msgStruct->lines." : ".$msgStruct->encoding." : 1");

  }
  else { // multipart: iterate through each part
    foreach ($msgStruct->parts as $partno => $pstruct) {
      $section = $partno + 1;
      $rawBody[$pstruct->subtype] = array(
        'encoding' => $pstruct->encoding,
        'body' => imap_fetchbody($mbox, $msgid, $section),
        'debug' => $pstruct->lines." : ".$pstruct->encoding." : $section");
    }
  }

  $parsedBody = MessageBodyParser::unifiedMessageInfo($rawBody);

  if ($parsedBody['fwd_headers']['fwd_lookup'] == 'LDAP FAILURE') {
    echo "[WARN]    Parse problem: LDAP lookup failure\n";
  }

  if ($parsedBody['message_action'] == "direct") {
    echo "[DEBUG]   Message was sent directly to inbox\n";

    // double check to make sure if was directly sent
    // this message format isn't ideal, it includes message info that is gross looking.
    $rawBody_alt['HTML'] = array(
                 'encoding' => 0,
                 'body' => imap_qprint(imap_body($mbox, $msgid)));
    $parsedBody_alt = MessageBodyParser::unifiedMessageInfo($rawBody_alt);

    if ($parsedBody['message_action'] == "forwarded" || $parsedBody_alt['message_action'] == "forwarded") {
      $headerCheck = array_diff($parsedBody['fwd_headers'], $parsedBody_alt['fwd_headers']);
      if ($headerCheck[0] != NULL) {
        echo "[WARN]    Parse problem: Header difference found\n";
      }
    }
  }

  $timeEnd = microtime(true);
  echo "[DEBUG]   Body download time: ".($timeEnd-$timeStart)."\n";

  // formatting headers
  $fwdEmail = substr($parsedBody['fwd_headers']['fwd_email'],0,255);
  $fwdName = substr($parsedBody['fwd_headers']['fwd_name'],0,255);
  $fwdLookup = $parsedBody['fwd_headers']['fwd_lookup'];
  $fwdSubject = substr( $parsedBody['fwd_headers']['fwd_subject'],0,255);
  $fwdDate = $parsedBody['fwd_headers']['fwd_date'];
  $fwdFormat = $parsedBody['format'];
  $messageAction = $parsedBody['message_action'];
  $fwdBody = $parsedBody['body'];
  $messageId = $msgMeta->uid;
  $oldDate = $msgMeta->date;
  $imapId = 0;
  $fromEmail =substr(mysql_real_escape_string($msgMeta->fromEmail),0,255);
  $fromName = substr(mysql_real_escape_string($msgMeta->fromName),0,255);
  $subject = substr(mysql_real_escape_string($msgMeta->subject),0,255);
  $date = substr(mysql_real_escape_string($msgMeta->date),0,255);

  if ($messageAction == 'direct' && !$parsedBody['fwd_headers']['fwd_email']) {
    $fwdEmail = $fromEmail;
    $fwdName = $fromName;
    $fwdSubject = $subject;
    $fwdDate = $date;
    $fwdBody = mysql_real_escape_string($fwdBody);
    $fwdLookup = 'Headers';
  }

  // debug info for mysql
  $debug = "Msg:$msgid; MessageID:$messageId; Action:$messageAction; bodyFormat:$fwdFormat; fwdLookup:$fwdLookup; fwdEmail:$fwdEmail; fwdName:$fwdName; fwdSubject:$fwdSubject; fwdDate:$fwdDate; FromEmail:$fromEmail; FromName:$fromName; Subject:$subject; Date:$date; Version:".VERSION_NUMBER;

  $status = STATUS_UNPROCESSED;

  $q = "INSERT INTO nyss_inbox_messages
        (message_id, imap_id, sender_name, sender_email, subject, body,
         forwarder, status, format, debug, updated_date, email_date)
        VALUES ($messageId, $imapId, '$fwdName', '$fwdEmail', '$fwdSubject',
                '$fwdBody', '$fromEmail', $status, '$fwdFormat', '$debug',
                CURRENT_TIMESTAMP, '$fwdDate');";

  if (mysql_query($q, $db) == false) {
    echo "[ERROR]   Unable to insert msgid=$messageId, imapid=$imapId\n";
  }

  $q = "SELECT id FROM nyss_inbox_messages
        WHERE message_id=$messageId AND imap_id=$imapId;";
  $res = mysql_query($q, $db);
  $rowCount = 0;
  while ($row = mysql_fetch_assoc($res)) {
    $rowId = $row['id'];
    $rowCount++;
  }
  mysql_free_result($res);

  echo "[DEBUG]   Inserted $rowCount message\n";
  if ($rowCount != 1) {
    echo "[WARN]    Problem inserting message; debug info:\n";
    print_r($fwdBody);
    echo "$q\n";
    $bSuccess = false;
  }

  echo "[INFO]    Fetching attachments\n";
  $timeStart = microtime(true);

  // if there is more then one part to the message
  if (count($msgStruct->parts) > 1) {
    $attachments = array();
    foreach ($msgStruct->parts as $partno => $pstruct) {
      //parse parts of email
      parseMimePart($mbox, $msgid, $pstruct, $partno+1, $attachments);
    }
  }

  $attachmentCount = count($attachments);
  if ($attachmentCount >= 1) {
    foreach ($attachments as $attachment) {
      $date = date('Ymdhis');
      $filename = mysql_real_escape_string($attachment['filename']);
      $size = mysql_real_escape_string($attachment['size']);
      $ext = mysql_real_escape_string($attachment['extension']);
      $allowed = mysql_real_escape_string($attachment['allowed']);
      $rejection = mysql_real_escape_string($attachment['rejected_reason']);
      $fileFull = '';

      if ($allowed) {
        $fileFull = $uploadInbox.'/'.$attachment['civifilename'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $fileFull);
        finfo_close($finfo);
      }

      $q = "INSERT INTO nyss_inbox_attachments
            (email_id, file_name, file_full, size, mime_type, ext, rejection)
            VALUES ($rowId, '$filename', '$fileFull', $size, '$mime', '$ext', '$rejection');";
      if (mysql_query($q, $db) == false) {
        echo "[ERROR]   Unable to insert attachment [$fileFull] for msgid=$rowId\n";
      }
    }
  }

  $timeEnd = microtime(true);
  echo "[DEBUG]   Attachments download time: ".($timeEnd-$timeStart)."\n";

  $q = "SELECT id FROM nyss_inbox_attachments WHERE email_id=$rowId";
  $res = mysql_query($q, $db);
  $dbAttachmentCount = mysql_num_rows($res);
  mysql_free_result($res);

  if ($dbAttachmentCount > 0) {
    echo "[DEBUG]   Inserted $dbAttachmentCount attachments\n";
  }

  return $bSuccess;
} // storeMessage()



// searchForMatches
// Creates an activity from parsed email parts.
// Detects email type (html|plain).
// Looks for the source_contact and if not found uses Bluebird Admin.
// Returns true/false to move the email to archive or not.
function searchForMatches($db, $params)
{
  $authForwarders = $params['authForwarders'];
  $uploadDir = $params['uploadDir'];

  // Check the items we have yet to match (unmatched=0, unprocessed=99)
  $q = "SELECT * FROM nyss_inbox_messages
        WHERE status=".STATUS_UNPROCESSED." OR status=".STATUS_UNMATCHED.";";
  $mres = mysql_query($q, $db);
  echo "[DEBUG]   Unprocessed/Unmatched records: ".mysql_num_rows($mres)."\n";

  while ($row = mysql_fetch_assoc($mres)) {
    $msg_row_id = $row['id'];
    $forwarder = $row['forwarder'];
    $sender_email = $row['sender_email'];
    $message_id = $row['message_id'];
    $imap_id = $row['imap_id'];
    $body = $row['body'];
    $email_date = $row['updated_date'];
    $subject = $row['subject'];
    echo "- - - - - - - - - - - - - - - - - - \n";

    echo "[DEBUG]   Processing Record ID: $msg_row_id\n";

    // Use the e-mail from the body of the message (or header if direct) to
    // find target contact
    echo "[INFO]    Looking for the original sender ($sender_email) in Civi\n";
    $q="SELECT  contact.id,  email.email FROM civicrm_contact contact
    LEFT JOIN civicrm_email email ON (contact.id = email.contact_id)
    WHERE contact.is_deleted=0
    AND email.email LIKE '$sender_email'
    GROUP BY contact.id
    ORDER BY contact.id ASC, email.is_primary DESC";
    $contact = array();
    $result = mysql_query($q, $db);

    while($row = mysql_fetch_assoc($result)) {
      $contact['values'][] = array('id'=>$row['id'],'email'=> $row['email']);
      $contact['id'] = $row['id'];
    }

    $contact['count'] = count($contact['values']);

    // No matches, or more than one match, marks message as UNMATCHED.
    if ($contact['count'] != 1) {
      echo "[DEBUG]   Original sender $sender_email matches [".$contact['count']."] records in this instance; leaving for manual addition\n";
      // mark it to show up on unmatched screen
      $status = STATUS_UNMATCHED;
      $q = "UPDATE nyss_inbox_messages SET status=$status WHERE id=$msg_row_id";
      if (mysql_query($q, $db) == false) {
        echo "[ERROR]   Unable to update status of message id=$msg_row_id\n";
      }
    }
    else {
      // Matched on a single contact.  Success!
      $contactID = $contact['id'];
      echo "[INFO]    Original sender [$sender_email] had a direct match.\n";

      // Set the activity creator ID to the contact ID of the forwarder.
      if (isset($authForwarders[$forwarder])) {
        $forwarderId = $authForwarders[$forwarder];
        echo "[INFO]    Forwarder [$forwarder] mapped to cid=$forwarderId\n";
      }
      else {
        $forwarderId = 1;
        echo "[WARN]    Unable to locate [$forwarder] in the auth forwarder mapping table; using Bluebird Admin\n";
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
                  // "original_id" => $email->uid,
                  "target_contact_id" => $contactID,
                  "version" => 3
      );

      $activityResult = civicrm_api('activity', 'create', $activityParams);

      if ($activityResult['is_error']) {
        echo "[ERROR]   Could not save activity\n";
        var_dump($ActivityResult);
        if ($fromEmail == '') {
          echo "[ERROR]    Forwarding e-mail address not found\n";
        }
      }
      else {
        $activityId = $activityResult['id'];
        echo "[INFO]    CREATED e-mail activity id=$activityId for contact id=$contactID\n";
        $status = STATUS_MATCHED;
        $q = "UPDATE nyss_inbox_messages
              SET status=$status, matcher=0, matched_to=$contactID,
                  activity_id=$activityId
              WHERE id=$msg_row_id";
        if (mysql_query($q, $db) == false) {
          echo "[ERROR]   Unable to update info for message id=$msg_row_id\n";
        }

        $q = "SELECT * FROM nyss_inbox_attachments WHERE email_id=$msg_row_id";
        $ares = mysql_query($q, $db);

        while ($row = mysql_fetch_assoc($ares)) {
          if ((!isset($row['rejection']) || $row['rejection'] == '')
              && file_exists($row['file_full'])) {
            echo "[INFO]    Adding attachment ".$row['file_full']." to activity id=$activityId\n";
            $date = date("Y-m-d H:i:s");
            $newName = CRM_Utils_File::makeFileName($row['file_name']);
            $file = "$uploadDir/$newName";
            // Move file to the CiviCRM custom upload directory
            rename($row['file_full'], $file);

            $q = "INSERT INTO civicrm_file
                  (mime_type, uri, upload_date)
                  VALUES ('{$row['mime_type']}', '$newName', '$date');";
            if (mysql_query($q, $db) == false) {
              echo "[ERROR]   Unable to insert attachment file info for [$newName]\n";
            }

            $q = "SELECT id FROM civicrm_file WHERE uri='{$newName}';";
            $res = mysql_query($q, $db);
            while ($row = mysql_fetch_assoc($res)) {
              $fileId = $row['id'];
            }
            mysql_free_result($res);

            $q = "INSERT INTO civicrm_entity_file
                  (entity_table, entity_id, file_id)
                  VALUES ('civicrm_activity', $activityId, $fileId);";
            if (mysql_query($q, $db) == false) {
              echo "[ERROR]   Unable to insert attachment mapping from activity id=$activityId to file id=$fileId\n";
            }
          }
        } // while rows in nyss_inbox_attachments
        mysql_free_result($ares);
      } // if activity created
    } // if single match on e-mail address
  } // while rows in nyss_inbox_messages

  mysql_free_result($mres);
  echo "[DEBUG]   Finished processing unprocessed/unmatched messages\n";
  return;
} // searchForMatches()



function listMailboxes($mbox, $params)
{
  $inboxes = imap_list($mbox, '{'.$params['server'].'}', "*");
  foreach ($inboxes as $inbox) {
    echo "$inbox\n";
  }
  return true;
} // listMailboxes()



function deleteArchiveBox($mbox, $params)
{
  $crm_archivebox = '{'.$params['server'].'}'.$params['archivebox'];
  echo "[INFO]    Deleting archive mailbox: $crm_archivebox\n";
  return imap_deletemailbox($mbox, $crm_archivebox);
} // deleteArchiveBox()



function getInboxPollingTagId()
{
  require_once 'api/api.php';

  // Check if the tag exists
  $apiParams = array(
    'name' => 'Inbox Polling Unprocessed',
    'version' => 3,
  );
  $result = civicrm_api('tag', 'get', $apiParams);

  if ($result && isset($result['id'])) {
    return $result['id'];
  }

  // If there's no tag, create it.
  $apiParams = array(
    'name' => 'Inbox Polling Unprocessed',
    'description' => 'Tag noting that this activity has been created by Inbox Polling and is still Unprocessed.',
    'parent_id' => 296,
    'used_for' => 'civicrm_contact,civicrm_activity,civicrm_case',
    'created_id' => 1,
    'version' => 3
  );
  $result = civicrm_api('tag', 'create', $apiParams);
  if ($result && isset($result['id'])) {
    return $result['id'];
  }
  else {
    return null;
  }
} // getInboxPollingTagId()



function sendDenialEmail($site, $email)
{
  require_once 'CRM/Utils/Mail.php';
  $subj = INVALID_EMAIL_SUBJECT." [$site]";
  $text = "CRM Instance: $site\n\n".INVALID_EMAIL_TEXT;
  $mailParams = array('from'    => INVALID_EMAIL_FROM,
                      'toEmail' => $email,
                      'subject' => $subj,
                      'html'    => str_replace("\n", '<br/>', $text),
                      'text'    => $text
                     );

  $rc = CRM_Utils_Mail::send($mailParams);
  if ($rc == true) {
    echo "[INFO] Denial e-mail has been sent to $email\n";
  }
  else {
    echo "[WARN] Unable to send a denial e-mail to $email\n";
  }
  return $rc;
} // sendDenialEmail()

?>
