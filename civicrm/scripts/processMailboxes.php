<?php
// processMailboxes.php
//
// Project: BluebirdCRM
// Author: Ken Zalewski & Stefan Crain
// Organization: New York State Senate
// Date: 2011-03-22
// Revised: 2013-3-21
//

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
require_once 'CRM/Utils/parseMessageBody.php';


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
$site = $optlist['site'];
$cmd = $optlist['cmd'];
$imap_server = DEFAULT_IMAP_SERVER;
$imap_user = null;
$imap_pass = null;
$imap_opts = DEFAULT_IMAP_OPTS;
$imap_mailbox = DEFAULT_IMAP_MAILBOX;
$imap_archivebox = DEFAULT_IMAP_ARCHIVEBOX;
$imap_process_unread_only = DEFAULT_IMAP_PROCESS_UNREAD_ONLY;
$imap_archive_mail = DEFAULT_IMAP_ARCHIVE_MAIL;
$version_number = 0.03; // helpful in debug to check parsing version

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

global $activityPriority, $activityStatus, $activityType, $inboxPollingTagId;

$aActivityPriority = CRM_Core_PseudoConstant::priority();
$aActivityStatus = CRM_Core_PseudoConstant::activityStatus();
$aActivityType = CRM_Core_PseudoConstant::activityType();

$activityPriority = array_search('Normal', $aActivityPriority);
$activityStatus = array_search('Not Required', $aActivityStatus);
$activityType = array_search('Inbound Email', $aActivityType);

$inboxPollingTagId = getInboxPollingTagId();

//set the session ID for who created the activity
$session->set('userID', 1);

//where to write file attachments to:
require_once 'CRM/Utils/File.php';
$config = CRM_Core_Config::singleton( );
$uploadInbox = $config->customFileUploadDir.'inbox/';
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
  // e-mail addresses to the list of authorized forwarders.
  $validSenders = explode(',', $imap_validsenders);
  $authForwarders = array_merge($authForwarders, $validSenders);
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
    'validsenders' => $authForwarders
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
 * Returns: Array of e-mail addresses that can forward messages to the inbox.
 */
function getAuthorizedForwarders()
{
  $res = array();
  $query = "
SELECT e.email
FROM civicrm_group_contact gc, civicrm_group g, civicrm_email e
WHERE g.name='".AUTH_FORWARDERS_GROUP_NAME."'
  AND g.id=gc.group_id
  AND gc.status='Added'
  AND gc.contact_id=e.contact_id
ORDER BY gc.contact_id ASC";

  $dao = CRM_Core_DAO::executeQuery($query);

  while ($dao->fetch()) {
    $res[] = $dao->email;
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
    echo "[ERROR] Invalid command [$cmd], params=".print_r($params, true)."\n";
    $rc = false;
  }

  //clean up moved/deleted messages
  // Using CL_EXPUNGE is same as calling imap_expunge().
  imap_close($imap_conn, CL_EXPUNGE);
  return $rc;
} // processMailboxCommand()



// Check the given IMAP account for new messages, and process them.

function checkImapAccount($conn, $params)
{
  echo "[INFO]    Polling CRM [".$params['site']."] using IMAP account ".
       $params['user'].'@'.$params['server'].$params['opts']."\n";

  $crm_archivebox = '{'.$params['server'].'}'.$params['archivebox'];

  //create archive box in case it doesn't exist
  //don't report errors since it will almost always fail
  if ($params['archivemail'] == true) {
    $rc = imap_createmailbox($conn, imap_utf7_encode($crm_archivebox));
    if ($rc) {
      echo "[DEBUG]   Created new mailbox: $crm_archivebox\n";
    }
    else {
      echo "[DEBUG]   Archive mailbox $crm_archivebox already exists.\n";
    }
  }

  $msg_count = imap_num_msg($conn);
  $invalid_senders = array();
  echo "[INFO]    Number of messages: $msg_count\n";

  for ($msg_num = 1; $msg_num <= $msg_count; $msg_num++) {
    echo "- - - - - - - - - - - - - - - - - - \n";
    echo "[INFO]    Retrieving message $msg_num / $msg_count\n";
    $email = retrieveMessage($conn, $msg_num);
    $sender = strtolower($email->fromEmail);

    // check whether or not the forwarder/sender is valid
    // if (in_array('crain@nysenate.gov', $params['validsenders'])) {
    if (in_array($sender, $params['validsenders'])) {
      echo "[DEBUG]   Sender $sender is allowed to send to this mailbox\n";
      // retrieved msg, now store to Civi and if successful move to archive
      if (civiProcessEmail($conn, $email, null) == true) {
        //mark as read

        imap_setflag_full($conn, $email->uid, '\\Seen', ST_UID);
        // move to folder if necessary
        if ($params['archivemail'] == true) {
          imap_mail_move($conn, $msg_num, $params['archivebox']);
        }
      }
    }
    else {
       echo "[WARN]    Sender $sender is not allowed to forward/send messages to this CRM; deleting message\n";
      $invalid_senders[$sender] = true;
      if (imap_delete($conn, $msg_num) === true) {
        echo "[DEBUG]   Message $msg_num has been deleted\n";
      }
      else {
        echo "[WARN]     Unable to delete message $msg_num from mailbox\n";
      }
    }
  }


  $invalid_sender_count = count($invalid_senders);
  if ($invalid_sender_count > 0) {
    echo "[INFO]    Sending denial e-mails to $invalid_sender_count e-mail address(es)\n";
    foreach ($invalid_senders as $invalid_sender => $dummy) {
      sendDenialEmail($params['site'], $invalid_sender);
    }
  }

  echo "[INFO]    Finished checking IMAP account ".$params['user'].'@'.$params['server'].$params['opts']."\n";

  echo "[INFO]    Searching For matches on unMatched Records\n";
  searchForMatches();

  return true;
} // checkImapAccount()

function parsepart($mbox,$msgid,$p, $global_i,$partsarray){

    //where to write file attachments to:
    $config = CRM_Core_Config::singleton( );
    $uploadDir = $config->customFileUploadDir;
    $uploadInbox = $uploadDir.'inbox/';

    if (!is_dir($uploadInbox)) {
      mkdir($uploadInbox);
      chmod($uploadInbox, 0777);
    }

    //fetch part
    $part=imap_fetchbody($mbox, $msgid, $global_i);
    //if type is not text
    if ($p->type != 0){
        //DECODE PART
        //decode if base64
        if ($p->encoding == 3) {
            $part=base64_decode($part);
        }
        //decode if quoted printable
        if ($p->encoding == 4) {
            $part=quoted_printable_decode($part);
        }
        //no need to decode binary or 8bit!

        //get filename of attachment if present
        $filename = '';
        // if there are any dparameters present in this part
        if (count($p->dparameters) > 0){
            foreach ($p->dparameters as $dparam){
                if ((strtoupper($dparam->attribute) == 'NAME') ||(strtoupper($dparam->attribute) == 'FILENAME')) {
                    $filename = $dparam->value;
                }
            }
        }
        //if no filename found
        if ($filename == ''){
            // if there are any parameters present in this part
            if (count($p->parameters) > 0){
                foreach ($p->parameters as $param){
                    if ((strtoupper($param->attribute) == 'NAME') ||(strtoupper($param->attribute) == 'FILENAME')) {
                        $filename = $param->value;
                    }
                }
            }
        }
        //write to disk and set partsarray variable
        if ($filename != ''){
            $tempfilename = imap_mime_header_decode($filename);
            for ($i = 0; $i < count($tempfilename); $i++) {
                $filename =  $tempfilename[$i]->text;
            }
            // $partsarray['attachments']['count']=$attachmentCount+1;
            $attachmentCount ++;
            $fileSize = strlen($part);
            $fileExt = substr(strrchr($filename,'.'),1);

            switch ($p->type) {
                case '0':
                    // message body
                    $allowed = false;
                    $rejected_reason = "File type [".$fileExt."] not allowed,";

                break;
                case '1':
                    // multi-part headers
                    $allowed = false;
                    $rejected_reason = "File type [".$fileExt."] not allowed,";
                break;
                case '2':
                    // attached message headers
                    $allowed = false;
                    $rejected_reason = "File type [".$fileExt."] not allowed,";
                break;
                case '3':
                    // Application ( pdf, exe, doc, etc)

                    if($fileExt == 'pdf'||$fileExt == 'txt'||$fileExt == 'text'||$fileExt == 'rtf'||$fileExt == 'odt'||$fileExt == 'doc'||$fileExt == 'ppt'||$fileExt == 'csv'||$fileExt == 'doc'||$fileExt == 'docx'||$fileExt == 'xls'){
                        $allowed = true;
                    }else{
                        $allowed = false;
                        $rejected_reason = "File type [".$fileExt."] not allowed,";
                    }
                break;
                case '4':
                    // Audo
                    $allowed = true;
                break;
                case '5':
                    // Image
                    $allowed = true;
                break;
                case '6':
                    // Video
                    $allowed = true;
                break;
                case '7':
                    // Other
                    $allowed = false;
                    $rejected_reason = "File type [".$fileExt."] not allowed,";
                break;

            }
            // echo $p->type;
            $newName = CRM_Utils_File::makeFileName($filename);

            if($allowed){
              if($fileSize > 2097152){
                $allowed = false;
                $rejected_reason .= " File is larger than 2mb";
              }
            }
            if($allowed){
              $fp = fopen($uploadInbox.$newName, "w+");
              fwrite($fp, $part);
              fclose($fp);
            }
            // var_dump(array('filename'=>$filename,'extension'=>$fileExt,'size'=>$fileSize,'allowed'=>$allowed,'rejected_reason'=>$rejected_reason));
            // $count = count($partsarray['attachments']);
            $partsarray['attachments'][] = array('filename'=>$filename,'civifilename'=>$newName,'extension'=>$fileExt,'size'=>$fileSize,'allowed'=>$allowed,'rejected_reason'=>$rejected_reason);
         }
    //end if type!=0
    }

    //if part is text
    else if($p->type == 0) {
        //decode text
        //if QUOTED-PRINTABLE
        if ($p->encoding == 4) {
            $part = quoted_printable_decode($part);
        }
        //if base 64
        if ($p->encoding == 3) {
            $part = base64_decode($part);
        }

        //OPTIONAL PROCESSING e.g. nl2br for plain text
        //if plain text
        if (strtoupper($p->subtype) == 'PLAIN')1;
        //if HTML
        else if (strtoupper($p->subtype) == 'HTML')1;
        $partsarray[$global_i][text] = array('type'=>$p->subtype, 'string'=>$part);
    }

    //if subparts... recurse into function and parse them too!
    if (count($p->parts) > 0){
        foreach ($p->parts as $pno=>$parr){
            parsepart($mbox, $msgid,$parr, ($global_i . '.' . ($pno+1)),$partsarray);
        }
    }
  return $partsarray;

}

function retrieveMessage($mbox, $msgid){
  // fetch info
  $headerStart = microtime(true);
  $header = imap_rfc822_parse_headers(imap_fetchheader($mbox, $msgid));
  $imap_uid = imap_uid($mbox, $msgid);

  // build email object
  $email = new stdClass();
  $email->subject = $header->subject;
  $email->fromName = $header->reply_to[0]->personal;
  $email->fromEmail = $header->reply_to[0]->mailbox.'@'.$header->reply_to[0]->host;
  $email->uid = $imap_uid;
  $email->msgid = $msgid;
  $email->date = date("Y-m-d H:i:s", strtotime($header->date));
  $headerEnd = microtime(true);
  echo "[DEBUG]   Fetch Header Time: ".($headerEnd-$headerStart)."\n";
  return $email;

} // retrieveMessage()


// civiProcessEmail
// Creates an activity from parsed email parts
// Detects email type (html|plain)
// Parses message body in search of target_contact & orgional subject
// Looks for the source_contact and if not found uses bluebird admin as a stand in
// Returns true/false to move the email to archive or not
function civiProcessEmail($mbox, $email, $customHandler)
{
  global $activityPriority, $activityType, $activityStatus, $inboxPollingTagId,$imap_user;

  $Parse = New parseMessageBody();
  $msgid = $email->msgid;
  $session =& CRM_Core_Session::singleton();
  $bSuccess = true;

  //where to write file attachments to:
  require_once 'CRM/Utils/File.php';
  $config = CRM_Core_Config::singleton( );
  $uploadDir = $config->customFileUploadDir;
  $uploadInbox = $uploadDir.'inbox/';

  $bodyStart = microtime(true);

  //  check for plain / html body text
  $s = imap_fetchstructure($mbox, $msgid);
  # github.com/hobsonlane/ships_log/blob/61aca0a32fae0da4262587a2a6bdd8bd26b71d6c/_random_stuff_/decode_textfile.php
  // print_r($s);
  $attachments = array();
  if ( (!isset($s->parts)) || (!$s->parts) ){ // not multipart
    var_dump($mbox,$msgid,$s,0,$htmlmsg,$plainmsg,$charset,$attachments); // no part-number, so pass 0
    $RawBody[$s->subtype] = array('encoding' => $s->encoding, 'body'=> imap_fetchbody($mbox,$msgid,$partno0+1), 'debug'=> $s->lines." : ".$s->encoding." : 0" );

  }else{ // multipart: iterate through each part
      foreach ($s->parts as $partno0=>$p){
        $RawBody[$p->subtype] = array('encoding' => $p->encoding, 'body'=> imap_fetchbody($mbox,$msgid,$partno0+1), 'debug'=> $p->lines." : ".$p->encoding." : ".($partno0+1) );
      }
  }
  // print_r($RawBody);

  // exit();
  $parsedBody = $Parse->unifiedMessageInfo($RawBody);
  var_dump($parsedBody);
  if($parsedBody['fwd_headers']['fwd_lookup'] == "LDAP FAILURE"){
    echo "[WARN]    Parse problem : LDAP LOOKUP FAILURE \n";
  }

  if($parsedBody['message_action'] == "direct"){
    echo "[DEBUG]   Message was sent directly to inbox \n";

    // double check to make sure if was directly sent
    // this message format isn't ideal, it includes message info that is gross looking.
    $messagebody_alt  = imap_qprint(imap_body($mbox, $msgid));
    $parsedBody_alt = $Parse->unifiedMessageInfo($messagebody_alt);

    if($parsedBody['message_action'] == "forwarded" || $parsedBody_alt['message_action'] == "forwarded"){
      $headerCheck = array_diff($parsedBody['fwd_headers'], $parsedBody_alt['fwd_headers']);
      if($headerCheck[0] != NULL){
        echo "[WARN]    Parse problem : Header difference found \n";
      }
    }
  }

  $bodyEnd = microtime(true);
  echo "[DEBUG]   Body Download Time: ".($bodyEnd-$bodyStart)."\n";

  // formatting headers
  $fwdEmail = $parsedBody['fwd_headers']['fwd_email'];
  $fwdName = $parsedBody['fwd_headers']['fwd_name'];
  $fwdLookup = $parsedBody['fwd_headers']['fwd_lookup'];
  $fwdSubject = $parsedBody['fwd_headers']['fwd_subject'];
  $fwdDate = $parsedBody['fwd_headers']['fwd_date'];
  $fwdFormat = $parsedBody['format'];
  $messageAction = $parsedBody['message_action'];
  $fwdbody = $parsedBody['body'];
  $messageId = $email->uid;
  $oldDate = $email->date;
  $imapId = 0;
  $fromEmail = mysql_real_escape_string($email->fromEmail);
  $fromName = mysql_real_escape_string($email->fromName);
  $subject = mysql_real_escape_string($email->subject);
  $date = mysql_real_escape_string($email->date);
  if($messageAction == 'direct' && !$parsedBody['fwd_headers']['fwd_email']){
    $fwdEmail = $fromEmail;
    $fwdName = $fromName;
    $fwdSubject = $subject;
    $fwdDate = $date;
    $fwdbody = mysql_real_escape_string($messagebody);
    $fwdLookup = 'Headers';
  }
  // debug info for mysql
  $debug = "Msg: ".$msgid."; MessageID: ".$messageId.";Action: ".$messageAction.";bodyFormat: ".$fwdFormat.";fwdLookup: ".$fwdLookup.";fwdEmail: ".$fwdEmail.";fwdName: ".$fwdName.";fwdSubject: ".$fwdSubject.";fwdDate: ".$fwdDate.";FromEmail: ".$fromEmail.";FromName: ".$fromName.";Subject: ".$subject.";Date: ".$date."; Version #: ".$version_number;

  // start db connection
  $nyss_conn = new CRM_Core_DAO();
  $nyss_conn = $nyss_conn->getDatabaseConnection();
  $dbconn = $nyss_conn->connection;

  $MessageInsert = "INSERT INTO `nyss_inbox_messages` (`message_id`, `imap_id`, `sender_name`, `sender_email`, `subject`, `body`, `forwarder`, `status`, `format`, `debug`, `updated_date`, `email_date`) VALUES ('{$messageId}', '{$imapId}', '{$fwdName}', '{$fwdEmail}', '{$fwdSubject}', '{$fwdbody}', '{$fromEmail}', '99', '{$fwdFormat}', '$debug', CURRENT_TIMESTAMP, '{$fwdDate}');";

  $MessageResults = mysql_query($MessageInsert, $dbconn);

  $SearchQuery = "select * from nyss_inbox_messages where `message_id` = {$messageId} && `imap_id` = {$imapId}";
  $SearchForExisting = mysql_query($SearchQuery, $dbconn);
  while($row = mysql_fetch_assoc($SearchForExisting)){
    $rowId=$row['id'];
  }
  echo "[DEBUG]   Inserted Rows: ".mysql_num_rows($SearchForExisting)."\n";
  if(mysql_num_rows($SearchForExisting) < 1){
    echo "[WARN]    Problem inserting Message, Debug info:\n";
    print_r($messagebody);
    echo $MessageInsert."\n";
    $bSuccess = false;
  }

  echo "[INFO]    Fetching attachments\n";
  $AttachmentsStart = microtime(true);
  $s = imap_fetchstructure($mbox, $msgid);
  // echo "[INFO]    Message Parts: ".count($s->parts)."\n";

  // if there is more then one part to the message
  if (count($s->parts) > 1){
    foreach ($s->parts as $partno=>$partarr) {
      //parse parts of email
      $partsarray = parsepart($mbox, $msgid, $partarr, $partno+1,$partsarray);
    }
  }

  if($partsarray['attachments']){
    foreach ($partsarray['attachments'] as $key => $value) {
      $date   =  date( 'Ymdhis' );
      $filename = mysql_real_escape_string($value['filename']);
      $size = mysql_real_escape_string($value['size']);
      $ext = mysql_real_escape_string($value['extension']);
      $allowed = mysql_real_escape_string($value['allowed']);
      $rejection = mysql_real_escape_string($value['rejected_reason']);
      $fileFull = '';

      if($allowed == 1){
        $fileFull = $uploadInbox.$value['civifilename'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $fileFull);
        finfo_close($finfo);
       }

      $insertAttachments = "INSERT INTO `nyss_inbox_attachments` (`email_id`, `file_name`,`file_full`, `size`, `mime_type`, `ext`,`rejection`) VALUES ({$rowId},'{$filename}','{$fileFull}',{$size},'{$mime}','{$ext}','{$rejection}');";
      // var_dump($insertAttachments);
      $insertMessage = mysql_query($insertAttachments, $dbconn);
    }
  }
  $partsarray['attachments'] = '';
  $AttachmentsEnd = microtime(true);
  echo "[DEBUG]   Attachments Download Time: ".($AttachmentsEnd-$AttachmentsStart)."\n";

  $SearchQuery = "select * from nyss_inbox_attachments where `email_id` = {$rowId}";
  $SearchForExisting = mysql_query($SearchQuery, $dbconn);

  if(mysql_num_rows($SearchForExisting) > 0)
    echo "[DEBUG]   Inserted Attachments: ".mysql_num_rows($SearchForExisting)."\n";

  return $bSuccess;
} // civiProcessEmail()


// searchForMatches
// Creates an activity from parsed email parts
// Detects email type (html|plain)
// Looks for the source_contact and if not found uses bluebird admin as a stand in
// Returns true/false to move the email to archive or not
function searchForMatches()
{
  global $activityPriority, $activityType, $activityStatus, $inboxPollingTagId,$imap_user;

  // start db connection
  $nyss_conn = new CRM_Core_DAO();
  $nyss_conn = $nyss_conn->getDatabaseConnection();
  $dbconn = $nyss_conn->connection;
  $config = CRM_Core_Config::singleton( );
  $uploadInbox = $config->customFileUploadDir.'inbox/';
  $uploadDir = $config->customFileUploadDir;

  // Check the items we have yet to match (unmatched - 0, and unprocessed - 99)
  $UnprocessedQuery = " SELECT *
  FROM `nyss_inbox_messages`
  WHERE `status` = 99 OR `status` = 0";


  $UnprocessedResult = mysql_query($UnprocessedQuery, $dbconn);
  $UnprocessedOutput = array();
  echo "[DEBUG]   Unprocessed Records: ".mysql_num_rows($UnprocessedResult)."\n";
  while($row = mysql_fetch_assoc($UnprocessedResult)) {
    // print_r($row);
    $message_row_id = $row['id'];
    $forwarder = $row['forwarder'];
    $sender_email = $row['sender_email'];
    $message_id = $row['message_id'];
    $imap_id = $row['imap_id'];
    $body = $row['body'];
    $email_date = $row['updated_date'];
    $subject = $row['subject'];
    echo "- - - - - - - - - - - - - - - - - - \n";

    echo "[DEBUG]   Processing Record ID: ".$row['id']."\n";

    // Use the e-mail from the body of the message (or header if direct) to find traget contact
    $params = array('version'   =>  3, 'activity'  =>  'get', 'email' => $sender_email, );
    $contact = civicrm_api('contact', 'get', $params);
    echo "[INFO]    Looking for the orgional Sender (".$sender_email.") in Civi\n";

    // if there is more then one target for the message leave if for the user to deal with
    if ($contact['count'] != 1 ){
      error_log("[DEBUG]   Orgional Sender  ".$sender_email." Matches [".$contact['count']."] Records in this instance . Leaving for manual addition.");

      // mark it to show up on unmatched screen
      $updateMessages = "UPDATE `nyss_inbox_messages`
        SET  `status`= 0
        WHERE `message_id` =  {$message_id} && `imap_id`= {$imap_id}";
      $updateMessagesResult = mysql_query($updateMessages, $dbconn);
      $Success = false;

    }else{
      $contactID = $contact['id'];
      $Success = true;
      echo "[INFO]    Orgional Sender ".$sender_email." had a direct match.\n";
    }
    if ($Success) {

      // Let's find the userID for the source of the activity
      $ForwarderSearch = "
    SELECT e.contact_id
    FROM civicrm_group_contact gc, civicrm_group g, civicrm_email e
    WHERE g.title='".AUTH_FORWARDERS_GROUP_NAME."'
      AND e.email='".$forwarder."'
      AND g.id=gc.group_id
      AND gc.status='Added'
      AND gc.contact_id=e.contact_id
    ORDER BY gc.contact_id ASC";
    // echo($ForwarderSearch);

      $ForwarderResult = mysql_query($ForwarderSearch, $dbconn);
      $results = array();
      while($row = mysql_fetch_assoc($ForwarderResult)) {
          $results[] = $row;
      }
      if (count($results) != 1 ){
        echo "[WARN]    Forwarder search ".$forwarder." within 'Authorized Forwarders' resulted in ".count($results)." making bluebird admin the owner\n";
      }else{
        echo "[INFO]    Forwarder search ".$forwarder." is authorized\n";
      }

      // error checking for forwarderId
      if (!$results){
        $forwarderId = 1; // bluebird admin
      } else{
        $forwarderId = $results[0]['contact_id'];
      };


      // create the activitiy
      $ActivityParams = array(
                  "source_contact_id" => $forwarderId,
                  "subject" => $subject,
                  "details" =>  $body,
                  "activity_date_time" => $email_date,
                  "status_id" => $activityStatus,
                  "priority_id" => $activityPriority,
                  "activity_type_id" => $activityType,
                  "duration" => 1,
                  "is_auto" => 1,
                  // "original_id" => $email->uid,
                  "target_contact_id" => $contactID,
                  "version" => 3
      );
      // print_r($ActivityParams);
      $ActivityResult = civicrm_api('activity', 'create', $ActivityParams);

      if ($ActivityResult['is_error']) {
        echo "[ERROR]   Could not save Activity\n";
        var_dump($ActivityResult);
        if ($fromEmail == '') {
          echo "[ERROR]    Forwarding e-mail address not found\n";
        }
        return false;
      }else {
        echo "[INFO]    CREATED e-mail activity id=".$ActivityResult['id']." for contact id=".$contactID."\n";
        $activityId = $ActivityResult['id'];
        $updateMessages = "UPDATE `nyss_inbox_messages`
        SET  `status`= 1, `matcher` = 0,  `matched_to` = $contactID,`activity_id` = {$ActivityResult['id']}
        WHERE `message_id` =  {$message_id} && `imap_id`= {$imap_id}";
        $updateMessagesResult = mysql_query($updateMessages, $dbconn);

        $AttachmentsQuery = "select * from nyss_inbox_attachments where `email_id` = {$message_row_id}";
        $AttachmentsResult = mysql_query($AttachmentsQuery, $dbconn);
        while($row = mysql_fetch_assoc($AttachmentsResult)) {
          if (isset($row['rejection']) && ($row['rejection']=='') && file_exists($row['file_full'])){

            echo "[INFO]    Adding attachment ".$row['file_full']." to id=".$ActivityResult['id']."\n";
            $date   =  date( "Y-m-d H:i:s" );
            $newName = CRM_Utils_File::makeFileName( $row['file_name'] );
            $file = $uploadDir. $newName;
            // move file to the civicrm customUpload directory
            rename( $row['file_full'], $file );

            $insertFIleQuery = "INSERT INTO `civicrm_file` (`mime_type`, `uri`,`upload_date`) VALUES ( '{$row['mime_type']}', '{$newName}','{$date}');";
            // echo $insertFIleQuery."\n";
            $rowUpdated = "SELECT id FROM civicrm_file WHERE uri = '{$newName}';";
            $insertFileResult = mysql_query($insertFIleQuery, $dbconn);
            $rowUpdatedResult = mysql_query($rowUpdated, $dbconn);
            $insertFileOutput = array();

            while($row = mysql_fetch_assoc($rowUpdatedResult)) {
              $fileId = $row['id'];
            }
            $insertEntityQuery = "INSERT INTO `civicrm_entity_file` (`entity_table`, `entity_id`, `file_id`) VALUES ('civicrm_activity','{$activityId}', '{$fileId}');";
            // echo $insertEntityQuery."\n";
            $insertEntity = mysql_query($insertEntityQuery, $dbconn);

          }
        }
      }

    } // success
  } // while
  echo "[DEBUG]   Finished Processing Unmatched messages\n";

} // searchForMatches()



function listMailboxes($conn, $params)
{
  $inboxes = imap_list($conn, '{'.$params['server'].'}', "*");
  foreach ($inboxes as $inbox) {
    echo "$inbox\n";
  }
  return true;
} // listMailboxes()



function deleteArchiveBox($conn, $params)
{
  $crm_archivebox = '{'.$params['server'].'}'.$params['archivebox'];
  echo "[INFO]    Deleting archive mailbox: $crm_archivebox\n";
  return imap_deletemailbox($conn, $crm_archivebox);
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
}


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
