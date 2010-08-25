<?

//error_reporting(E_ERROR | E_PARSE);

//no limit
set_time_limit(0);

require_once dirname(__FILE__)."/../commonLibs/config.php";
require_once dirname(__FILE__).'/../commonLibs/lib.inc.php';

$site="";
if (isset($argv[1])) $site = $argv[1];
define('CIVICRM_CONFDIR',RAYROOTDIR."sites/{$site}".RAYROOTDOMAIN."/");

require_once CIVICRM_CONFDIR.'civicrm.settings.php';
require_once 'CRM/Core/Config.php';
require_once 'CRM/Contact/BAO/Contact.php';
require_once 'CRM/Activity/BAO/Activity.php';
require_once 'CRM/Core/BAO/CustomValueTable.php';
require_once 'CRM/Core/PseudoConstant.php';
require_once 'CRM/Core/Error.php';

$config =& CRM_Core_Config::singleton();
$session =& CRM_Core_Session::singleton();

/* requires something like this in the config

$aIncomingIMAPAccount[0] = array(
	'server'=>'mail.messagingengine.com',
	'mailbox'=>'INBOX',
	'archiveMailbox'=>'archive',
	'processUnreadOnly'=>true,
	'moveMailToArchive' => true,
	'login' =>'usr',
	'password' => 'pwd',
	'imapOpts' => '/imap/ssl/notls',
	'customHandler' => null 
);
*/

global $activityType;
global $activityPriority;
global $activityStatus;

$aActivityPriority = CRM_Core_PseudoConstant::priority( );
$aActivityType = CRM_Core_PseudoConstant::activityType( );
$aActivityStatus = CRM_Core_PseudoConstant::activityStatus( );

$activityPriority = array_search( 'Normal', $aActivityPriority );
$activityStatus = array_search( 'Not Required', $aActivityStatus );
$activityType = array_search( 'Email Received', $aActivityType );

//set the session ID for who careated the activity
$session->set( 'userID',1 );

if (!isset($aIncomingIMAPAccount[$site])) die("no mailboxes to process...");

foreach ($aIncomingIMAPAccount[$site] as $act) {

	cLog(0,'info',"polling {$server}...");
	$connection = imap_open("{".$act['server'].$act['imapOpts']."}", $act['login'], $act['password']);

	//create archive box in case it doesn't exist
	//don't report errors since it will almost always fail
	if ($act['moveMailToArchive']) @imap_createmailbox($connection, imap_utf7_encode("{".$act['server']."}INBOX.".$act['archiveMailbox']));

	$count = imap_num_msg($connection);
	cLog(0,'info',"number of messages: $count");

	for($i = 1; $i <= $count; $i++) {

		//get the header
		$header = imap_headerinfo($connection, $i);

		$email = new stdClass();
                $email->id = $header->message_id;
		$email->subject = $header->subject;
		$email->replyTo = $header->reply_to[0]->mailbox.'@'.$header->reply_to[0]->host;
		$email->date = $header->MailDate;

                //skip if we are processing unread only
                if ($header->Unseen!="U" && $act['processUnreadOnly']) {
                        cLog(0,'info',"skipping (processUnreadOnly flag set): {$email->id} {$email->replyTo} {$email->subject}");
                        continue;
                }

		cLog(0,'info',"processing email $i/$count: ".$email->id." from ".$email->replyTo);

		//get the structure
		$structure = imap_fetchstructure($connection, $i);

		//get the body: depending on email type use different functions.
		if($structure->type == 1) $email->body = imap_fetchbody($connection,$i,"1");
		else $email->body = imap_body($connection, $i);
 
		//get the attachments
		$email->attachments = getAttachments($structure, $connection, $i);

		//cLog($email);

		//email complete, now try to store to civi and if successful move to archive
		if (civiProcessEmail($email, $act)) {
			
			//mark as read
		    	imap_setflag_full($connection,imap_uid($connection,$i),'\\SEEN',SE_UID); 

			cLog(0,'debug',"marked as read");
	
			//move to folder if necessary
			if($act['moveMailToArchive']) imap_mail_move($connection,$i,$act['archiveMailbox']); 
		}
	}

	//clean up moved/deleted messages
	imap_expunge($connection);
	imap_close($connection);
}

//attaches email to civiCRM
//default: if no contact found, don't do anything
//returns true/false to move the email to archive or not
function civiProcessEmail($email, $account) {

	global $activityPriority, $activityType, $activityStatus;
	
	$session =& CRM_Core_Session::singleton();

	$bSuccess=false;

	//match against allowed email list


	//parse out the first email address which will be the "from" address of the forwarded email
	$matches = array();

	$qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
	$dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
	$atom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c'.
		'\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
	$quoted_pair = '\\x5c[\\x00-\\x7f]';
	$domain_literal = "\\x5b($dtext|$quoted_pair)*\\x5d";
	$quoted_string = "\\x22($qtext|$quoted_pair)*\\x22";
	$domain_ref = $atom;
	$sub_domain = "($domain_ref|$domain_literal)";
	$word = "($atom|$quoted_string)";
	$domain = "$sub_domain(\\x2e$sub_domain)*";
	$local_part = "$word(\\x2e$word)*";
	$addr_spec = "$local_part\\x40$domain";
	$count = preg_match("/$addr_spec/i", $email->body, $matches);

	//use the forward address, otherwise use the direct from address
        if ($count>0) {
              $email->body = "Forwarded by: " . $email->replyTo."\n\n".$email->body;
              $email->replyTo = $matches[0];
        } 

	//find a contact that matches by email
	$c = new CRM_Contact_BAO_Contact();
	$cobj = $c->matchContactOnEmail($email->replyTo);

	if (isset($cobj->contact_id)) $contactID = $cobj->contact_id;

	if ($contactID) {
		$str = " matched contactID ";
		$bSuccess=true;
	} else { 
		$str = "no match, assigning to anonymous contact.";
	        $cobj = $c->matchContactOnEmail(UNKNOWNCONTACTEMAIL);
	        if (isset($cobj->contact_id)) $contactID = $cobj->contact_id;
	}
	
	cLog(0,'debug',"matching based on {$email->replyTo}, {$str}{$contactID}");

	//process any custom mailbox specific rules
	if ($account['customHandler']!=null) {
		
		include($account['customHandler']);
	}
	//standard handling, add activity if we found a match
	elseif ($bSuccess) {

	        cLog(0,'debug',"Adding standard activity to contact {$contactID}");

		$params = array("source_contact_id" => $session->get("userID"),
				"subject" => $email->subject,
				"details" => $email->body,
				"activity_date_time" => date('YmdHis',strtotime($email->date)),
				"status_id" => $activityStatus,
				"priority_id" => $activityPriority,
                                "activity_type_id" => $activityType,
				"target_contact_id" => array($contactID),
			);

 		$activity = new CRM_Activity_BAO_Activity( );

        	// start transaction
        	require_once 'CRM/Core/Transaction.php';
        	$transaction = new CRM_Core_Transaction( );
        
		$result = $activity->create( $params );

        	if ( is_a( $result, 'CRM_Core_Error' ) ) {

        	    $transaction->rollback( );
		    cLog(0,'error',"COULD NOT SAVE ACTIVITY!\n".print_r($params,true));
        	    return false;
        	} else {

		    cLog(0,'info',"created activity ".$activity->id);
		}
	}

	return $bSuccess;
}

function getAttachments($structure, $connection, $num) {

   $attachments = array();
   if(isset($structure->parts) && count($structure->parts)) {

	//attachment counter
	$numAttachments=0;

	for($i = 0; $i < count($structure->parts); $i++) {

		$attachments[$numAttachments] = array(
			'is_attachment' => false,
			'filename' => '',
			'name' => '',
			'attachment' => ''
		);
		
		if($structure->parts[$i]->ifdparameters) {
			foreach($structure->parts[$i]->dparameters as $object) {
				if(strtolower($object->attribute) == 'filename') {
					$attachments[$numAttachments]['is_attachment'] = true;
					$attachments[$numAttachments]['filename'] = $object->value;
				}
			}
		}
		
		if($structure->parts[$i]->ifparameters) {
			foreach($structure->parts[$i]->parameters as $object) {
				if(strtolower($object->attribute) == 'name') {
					$attachments[$numAttachments]['is_attachment'] = true;
					$attachments[$numAttachments]['name'] = $object->value;
				}
			}
		}
		
		if($attachments[$numAttachments]['is_attachment']) {
			$attachments[$numAttachments]['attachment'] = imap_fetchbody($connection, $num, $i+1);
			if($structure->parts[$i]->encoding == 3) { // 3 = BASE64
				$attachments[$numAttachments]['attachment'] = base64_decode($attachments[$numAttachments]['attachment']);
			}
			elseif($structure->parts[$i]->encoding == 4) { // 4 = QUOTED-PRINTABLE
				$attachments[$numAttachments]['attachment'] = quoted_printable_decode($attachments[$numAttachments]['attachment']);
			}
		}

		if ($attachments[$numAttachments]['is_attachment']) ++$numAttachments;
		else unset($attachments[$numAttachments]);
	}

	return $attachments;
}
}
?>
