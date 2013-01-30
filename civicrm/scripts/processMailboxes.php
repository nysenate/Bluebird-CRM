<?php
// processMailboxes.php
//
// Project: BluebirdCRM
// Author: Ken Zalewski
// Organization: New York State Senate
// Date: 2011-03-22
// Revised: 2013-1-18
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
define('INVALID_EMAIL_SUBJECT', 'Bluebird Inbox Polling Error: Not permitted to send e-mails to CRM');
define('INVALID_EMAIL_TEXT', "You do not have permission to forward e-mails to this CRM instance.\n\nIn order to allow your e-mails to be accepted, you must request that your e-mail address be added to the Valid Senders list for this CRM.\n\nPlease contact Senate Technology Services for more information.\n\n");

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
$activityType = array_search('Email (incoming)', $aActivityType);

$inboxPollingTagId = getInboxPollingTagId();

//set the session ID for who created the activity
$session->set('userID', 1);

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
    echo "[ERROR] Failed to process IMAP account $imapUser@$imap_server\n";
    print_r(imap_errors());
  }
}

echo "[INFO] Finished processing all mailboxes for CRM instance [$site]\n";
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
  echo "[INFO] Opening IMAP connection to {$params['user']}@$serverspec\n";
  $imap_conn = imap_open($serverspec, $params['user'], $params['pass']);

  if ($imap_conn === false) {
    echo "[ERROR] Unable to open IMAP connection to $serverspec\n";
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
  echo "[INFO] Polling CRM [".$params['site']."] using IMAP account ".
       $params['user'].'@'.$params['server'].$params['opts']."\n";

  $crm_archivebox = '{'.$params['server'].'}'.$params['archivebox'];

  //create archive box in case it doesn't exist
  //don't report errors since it will almost always fail
  if ($params['archivemail'] == true) {
    $rc = imap_createmailbox($conn, imap_utf7_encode($crm_archivebox));
    if ($rc) {
      echo "[DEBUG] Created new mailbox: $crm_archivebox\n";
    }
    else {
      echo "[DEBUG] Archive mailbox $crm_archivebox already exists.\n";
    }
  }

  $msg_count = imap_num_msg($conn);
  $invalid_senders = array();
  echo "[INFO] Number of messages: $msg_count\n";

  for ($msg_num = 1; $msg_num <= $msg_count; $msg_num++) {
    echo "[INFO] Retrieving message $msg_num / $msg_count\n";
    $email = retrieveMessage($conn, $msg_num);
    $sender = strtolower($email->replyTo);

    // check whether or not the forwarder/sender is valid
    // if (in_array('crain@nysenate.gov', $params['validsenders'])) {
    if (in_array($sender, $params['validsenders'])) {
      echo "[DEBUG] Sender $sender is allowed to send to this mailbox\n";
      // retrieved msg, now store to Civi and if successful move to archive
      if (civiProcessEmail($email, null) == true) {
        //mark as read
        imap_setflag_full($conn, $email->uid, '\\Seen', ST_UID);
        //move to folder if necessary
        if ($params['archivemail'] == true) {
          imap_mail_move($conn, $msg_num, $params['archivebox']);
        }
      }
    }
    else {
       echo "[WARN] Sender $sender is not allowed to forward/send messages to this CRM; deleting message\n";
      $invalid_senders[$sender] = true;
      if (imap_delete($conn, $msg_num) === true) {
        echo "[DEBUG] Message $msg_num has been deleted\n";
      }
      else {
        echo "[WARN] Unable to delete message $msg_num from mailbox\n";
      }
    }
  }

  $invalid_sender_count = count($invalid_senders);
  if ($invalid_sender_count > 0) {
    echo "[INFO] Sending denial e-mails to $invalid_sender_count e-mail address(es)\n";
    foreach ($invalid_senders as $invalid_sender => $dummy) {
      sendDenialEmail($params['site'], $invalid_sender);
    }
  }

  echo "[INFO] Finished checking IMAP account ".$params['user'].'@'.$params['server'].$params['opts']."\n";
  return true;
} // checkImapAccount()



function retrieveMessage($conn, $idx)
{
  //get the header
  $header = imap_headerinfo($conn, $idx);

  // echo($idx);
  $email = new stdClass();
  // $email->header = $header;
  $email->id = $header->message_id;
  $email->subject = $header->subject;
  $reply_to = $header->reply_to[0];
  $email->replyTo = $reply_to->mailbox.'@'.$reply_to->host;
  $email->date = $header->MailDate;
  $email->uid = imap_uid($conn, $idx);

  echo "[INFO] Message #$idx (uid={$email->uid}): {$email->id} from {$email->replyTo}\n";

  // // Skip message if we are processing unread only.
  if ($header->Unseen != "U" && $params['unreadonly']) {
    echo "[INFO] Skipping (PROCESS_UNREAD_ONLY flag set): {$email->id} {$email->replyTo} {$email->subject}\n";
    return null;
  }

  //get the structure
  $msg_struct = imap_fetchstructure($conn, $idx);

  // Extract the body using one of two functions, depending on the message type.
  if ($msg_struct->type == TYPEMULTIPART) {
    $email->body = imap_fetchbody($conn, $idx, "1");
  }
  else {
    $email->body = imap_body($conn, $idx);
  }
 
  //get the attachments
  if (isset($msg_struct->parts) && count($msg_struct->parts > 0)) {
    $email->attachments = getAttachments($conn, $idx, $msg_struct->parts);
  }
  else {
    $email->attachments = null;
  }

  return $email;
} // retrieveMessage()



function getAttachments($conn, $num, $parts)
{
  $attachments = array();

  for ($i = 0; $i < count($parts); $i++) {
    $cur_att = array(
      'is_attachment' => false,
      'filename' => '',
      'name' => '',
      'attachment' => ''
    );
  
    if ($parts[$i]->ifdparameters == true) {
      foreach ($parts[$i]->dparameters as $object) {
        if (strtolower($object->attribute) == 'filename') {
          $cur_att['is_attachment'] = true;
          $cur_att['filename'] = $object->value;
        }
      }
    }
  
    if ($parts[$i]->ifparameters == true) {
      foreach ($parts[$i]->parameters as $object) {
        if (strtolower($object->attribute) == 'name') {
          $cur_att['is_attachment'] = true;
          $cur_att['name'] = $object->value;
        }
      }
    }
  
    if ($cur_att['is_attachment'] == true) {
      $cur_att['attachment'] = imap_fetchbody($conn, $num, $i + 1);
      if ($parts[$i]->encoding == ENCBASE64) {
        $cur_att['attachment'] = base64_decode($cur_att['attachment']);
      }
      elseif ($parts[$i]->encoding == ENCQUOTEDPRINTABLE) {
        $cur_att['attachment'] = quoted_printable_decode($cur_att['attachment']);
      }
      $attachments[] = $cur_att;
    }
  }

  return $attachments;
} // getAttachments()


// This is my email body text / LDAP parser 
function extract_email_address ($string) {
  // we have to parse out ldap stuff because sometimes addresses are
  // embedded and, see NYSS #5748 for more details 

  // if o= is appended to the end of the email address remove it 
  $string = preg_replace('/\/senate@senate/i', '/senate', $string);
  $string = preg_replace('/mailto|\(|\)|:/i', '', $string);
  $string = preg_replace('/"|\'/i', '', $string);
  // ldap addresses have slashes, so we do an internal lookup
  $internal = preg_match("/\/senate/i", $string, $matches);
  if($internal == 1){
    $ldapcon = ldap_connect("ldap://webmail.senate.state.ny.us", 389);
      $retrieve = array("sn","givenname", "mail");
      $search = ldap_search($ldapcon, "o=senate", "(displayname=$string)", $retrieve);
      $info = ldap_get_entries($ldapcon, $search);
    if($info[0]){
      $name = $info[0]['givenname'][0].' '.$info[0]['sn'][0];
      $return = array('type'=>'LDAP','name'=>$name,'email'=>$info[0]['mail'][0]);
      return $return;
    }else{
      $return = array('type'=>'LDAP FAILURE','name'=>'LDAP lookup Failed','email'=>'LDAP lookup Failed on string '.$string);
      return $return;
    }
    
  }else{
    // clean out any anything that wouldn't be a name or email, html or plain-text
    $string = preg_replace('/&lt;|&gt;|&quot;|&amp;/i', '', $string);
    $string = preg_replace('/<|>|"|\'/i', '', $string);
    foreach(preg_split('/ /', $string) as $token) {
      $name .=$token." ";
        $email = filter_var(filter_var($token, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);
        if ($email !== false) {
            $emails[] = $email;
            break; // only want one match 
        }
    }
    $name = trim(str_replace($emails[0], '', $name));
    $return = array('type'=>'inline','name'=>$name,'email'=>$emails[0]);
    return $return;
  }
}


function cleanDate($date_string){
        $matches = array();

        // search for the word date
        $count = preg_match("/(Date:|date:)\s*([^\r\n]*)/i", $date_string, $matches);
        $date_string_short = ($count == 1 ) ? $matches[2]  : $date_string;

        // sometimes email clients think its fun to add stuff to the date, remove it here.
        $date_string_short = preg_replace("/ (at) /i", "", $date_string_short);
   
        // check if the message is from last year
        if ( (date("Y", strtotime($date_string_short)) - date("Y")) < 0 ){
          $formatted = date("M d, Y", strtotime($date_string_short));
        }else{
          if ( (date("d", strtotime($date_string_short)) - date("d")) < 0 ){
            $formatted = date("M d h:i A", strtotime($date_string_short));
          }else{
            $formatted = 'Today '.date("h:i A", strtotime($date_string_short));
          }
        }
        return array('debug'=>$date_string_short, 
                  'long'=> date("M d, Y h:i A", strtotime($date_string_short)), 
                  'u'=>date("U", strtotime($date_string_short)),
                  'short'=>$formatted);
}


// civiProcessEmail
// Creates an activity from parsed email parts
// Detects email type (html|plain)
// Parses message body in search of target_contact & orgional subject
// Looks for the source_contact and if not found uses bluebird admin as a stand in
// Returns true/false to move the email to archive or not
 function civiProcessEmail($email, $customHandler)
{
  global $activityPriority, $activityType, $activityStatus, $inboxPollingTagId;
  $session =& CRM_Core_Session::singleton();
  $bSuccess = false;
  // print_r($email); exit();

  // remove weirdness in html encoded messages 
  $body = quoted_printable_decode($email->body);

  // detect if message is html / plaintext from body only
  preg_match("/<br*>/i", $body, $html);

  // convert html/plain line endings to be in the same format for our regexs' sake 

  // check for fake html
  // we don't care if the body only has <br/> tags
  if(strip_tags($details) != strip_tags($details,"<br>")){
    $format = true;
  }

  if($html){
    $format = "html";
    $tempbody = $body;
    $tempbody = preg_replace("/<br>/i", "\r\n<br>\n", $tempbody);
  }else{
    $format = "plain";
    $tempbody = preg_replace("/(=|\r\n|\r|\n)/i", "\r\n<br>\n", $body);
  }

  // check to see if we can find an email address in the message body,
  // else we use the email from the message header and call it a direct message
  preg_match("/(From:|from:)\s*([^\r\n]*)/i", $tempbody, $froms);

  if($froms['2']){ 
    $fromEmail = extract_email_address($froms['2']);
    if(!$fromEmail['email']){
      $status = 'forwarded';
      $fromEmail = array('email' => $email->replyTo, 'type'=>'direct');
    }
  }else{
    $fromEmail = array('email' => $email->replyTo, 'type'=>'direct');
    $status = 'direct';
  }
  
  // check to see if we can find a subject in the message body,
  preg_match("/(Subject:|subject:)\s*([^\r\n]*)/i", $tempbody, $subjects);

  $subject = ($status == 'direct') ?  preg_replace("/(Fwd:|fwd:|Fw:|fw:|Re:|re:) /i", "", $email->subject) : preg_replace("/(Fwd:|fwd:|Fw:|fw:|Re:|re:) /i", "", $subjects['2']);
  
  // remove () sometimes found in emails with no subject
  $subject = preg_replace("/(\(|\))/i", "", $subject);
  if( trim(strip_tags($subject)) == "" | trim($subject) == "no subject"){
    $subject = "No Subject";
  }

  // html emails need to be pre tagged

    $email->body = '<pre>'.$email->body.'</pre>';

  // Use the e-mail from the body of the message (or header if direct) to find traget contact
  $params = array('version'   =>  3, 'activity'  =>  'get', 'email' => $fromEmail['email'], );
  $contact = civicrm_api('contact', 'get', $params);
  echo "[INFO] FINDING match for the target email ".$fromEmail['email']." in Civi\n";

  // if there is more then one target for the message leave if for the user to deal with
  if ($contact['count'] != 1 ){
    error_log("[DEBUG] TARGET ".$fromEmail['email']." Matches [".$contact['count']."] Records in this instance . Leaving for manual addition.");
  }else{
    $contactID = $contact['id'];
    $bSuccess = true;
  }

  //process any custom mailbox specific rules
  if ($customHandler != null) {
    include($customHandler);
  }
  //standard handling, add activity if we found a match
  elseif ($bSuccess) {

    // Let's find the userID for the source of the activity
    $apiParams = array( 
      'email' =>  $email->replyTo,
      'version' => 3
    );
    $result = civicrm_api('contact', 'get', $apiParams);

    // In cases where there is more the one user found mark the message as assigned from bluebird admin 
    $userId = 1;
    if ($result['count'] == 1) {
      $userId = $result['values'][ $result['id'] ]['contact_id'];
    }

    // make note of this
    // TODO: send out email to offending user asking them to clean up their accounts
    if ($result['count'] != 1) {
      error_log("[DEBUG] SOURCE ".$email->replyTo." Matches [".$result['count']."] Records in this instance . Adding with source Bluebird Admin.");
    }

    $fwdDate = cleanDate($tempbody);
    error_log("[DEBUG] FWD Date ".$fwdDate['long']);

    echo "[INFO] ADDING standard activity to target $contactID ({$fromEmail['email']}) source $userId \n";
    $apiParams = array(
                "source_contact_id" => $userId,
                "subject" => $subject,
                "details" => imap_qprint($email->body),
                "activity_date_time" => $fwdDate['long'],
                "status_id" => $activityStatus,
                "priority_id" => $activityPriority,
                "activity_type_id" => $activityType,
                "duration" => 1,
                "is_auto" => 1,
                "original_id" => $email->uid,
                "target_contact_id" => $contactID,
                "version" => 3
    );

    $result = civicrm_api('activity', 'create', $apiParams);
    if ($result['is_error']) {
      echo "[WARN] Could not save Activity\n";
      if ($fromEmail['email'] == '') {
        echo "[WARN] Forwarding e-mail address not found\n";
      }
      return false;
    }else {
      echo "[INFO] CREATED e-mail activity id=".$result['id']." for contact id=".$contactID."\n";
      $activityId = $result['id'];
      require_once 'CRM/Core/DAO.php';
      $nyss_conn = new CRM_Core_DAO();
      $nyss_conn = $nyss_conn->getDatabaseConnection();
      $conn = $nyss_conn->connection;
      $query = "SELECT * FROM civicrm_entity_tag
                WHERE entity_table='civicrm_activity'
                  AND entity_id=$activityId
                  AND tag_id=$inboxPollingTagId;";
      $result = mysql_query($query, $conn);

      if (mysql_num_rows($result) == 0) {
        $query = "INSERT INTO civicrm_entity_tag (entity_table,entity_id,tag_id)
                  VALUES ('civicrm_activity',$activityId,$inboxPollingTagId);";
        $result = mysql_query($query, $conn);
        if ($result) {
          echo "[DEBUG] ADDED Tag id=$inboxPollingTagId to Activity id=$activityId\n";
        }
        else {
          echo "[ERROR] COULD NOT add Tag id=$inboxPollingTagId to Activity id=$activityId\n";
        }
      }
    }
  }
  return $bSuccess;
} // civiProcessEmail()



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
  echo "[INFO] Deleting archive mailbox: $crm_archivebox\n";
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
  // require_once 'CRM/Utils/Mail.php';
  // $subj = INVALID_EMAIL_SUBJECT." [$site]";
  // $text = "CRM Instance: $site\n\n".INVALID_EMAIL_TEXT;
  // $mailParams = array('from'    => INVALID_EMAIL_FROM,
  //                     'toEmail' => $email,
  //                     'subject' => $subj,
  //                     'html'    => str_replace("\n", '<br/>', $text),
  //                     'text'    => $text
  //                    );

  // $rc = CRM_Utils_Mail::send($mailParams);
  // if ($rc == true) {
  //   echo "[INFO] Denial e-mail has been sent to $email\n";
  // }
  // else {
  //   echo "[WARN] Unable to send a denial e-mail to $email\n";
  // }
  // return $rc;
} // sendDenialEmail()

?>
