<?php
/* Sendgrid integration for CiviCRM/Bluebird
 *
 * New York State Senate
 * Brian Shaughnessy
 * June, 2011
 */

require_once 'script_utils.php';
define('DEFAULT_BOUNCE_TYPE', 6);

//process the script
function run() {

    $prog = basename(__FILE__);
    $shortopts = 'pbu';
    $longopts = array('profile', 'bounce', 'unsubscribe');
    $stdusage = civicrm_script_usage();
    $usage = "[--profile|-p]  [--bounce|-b]  [--unsubscribe|-u]";

    $optlist = civicrm_script_init($shortopts, $longopts);
    //print_r($optlist);
    if ($optlist === null) {
      error_log("Usage: $prog  $stdusage  $usage");
      exit(1);
    }
    
    $instance = $optlist['site'];

    //log the execution of script
    require_once 'CRM/Core/Error.php';
    require_once 'api/v2/utils.php';
    //CRM_Core_Error::debug_log_message('sendGridPull.php');

    //instantiate CiviCRM
    require_once 'CRM/Core/Config.php';
    $config = CRM_Core_Config::singleton();
    //print_r($config);
    
    //retrieve Bluebird config variables
    $bbconfig = get_bluebird_instance_config();
    $smtpuser = $bbconfig['sendgrid.username'];
    $smtppass = $bbconfig['sendgrid.password'];
    $smtpsubuser = $bbconfig['smtp.username'];
    $smtpsubpass = $bbconfig['smtp.password'];
    //print_r($bbconfig);
    
    if ( !$smtpuser || !$smtppass ) {
        exit();
    }
    
    //now decide on the actions to process
    if ( $optlist['profile'] == true ) {
        getProfile( $smtpuser, $smtppass );
    }
    
    if ( $optlist['bounce'] == true ) {
        bounceRetrieve( $smtpuser, $smtppass, $smtpsubuser );
    }
    
    if ( $optlist['unsubscribe'] == true ) {
        unsubscribeRetrieve( $smtpuser, $smtppass, $smtpsubuser );
    }

}



//retrieve the sendgrid account profile
function getProfile( $smtpuser, $smtppass ) {

    $profileUrl = "https://sendgrid.com/api/profile.get.xml?api_user=$smtpuser&api_key=$smtppass";
    $getProfile = simplexml_load_file($profileUrl);
    print_r($getProfile);

}

//retrieve bounced emails and process
function bounceRetrieve( $smtpuser, $smtppass, $smtpsubuser, $delete = true ) {

    require_once 'CRM/Core/DAO.php';
	require_once 'CRM/Mailing/Event/BAO/Bounce.php';

    $bounceUrl = "https://sendgrid.com/apiv2/customer.bounces.xml?api_user=$smtpuser&api_key=$smtppass&user=$smtpsubuser&task=get&date=1";
    $bounceRetrieve = simplexml_load_file($bounceUrl);
    //CRM_Core_Error::debug('bounceRetrieve', $bounceRetrieve);
    
    foreach ( $bounceRetrieve as $bounce ) {
        //get parameters
        $fixdate = date("Y-m-d H:i:s", time($bounce->created)+60*60 );
        $email   = $bounce->email;
        $reason  = $bounce->reason;
        //echo "find: $email\n";
        //echo "reason: $reason\n";
        
        //find the email and job details
        $queue = findEmailJob( mysql_real_escape_string($email) );
        
        //TODO we need to check to see if the email has already been processed
        //OR we need to delete the bounce from SendGrid after processing
        
        //now lets process the bounce if we found the necessary values
        if ( $queue['jobID'] != 0 ) {
            //first make sure an existing bounce record does not exist
			$queueID = $queue['queueID'];
			$sql     = "SELECT id
			            FROM civicrm_mailing_event_bounce
					    WHERE event_queue_id = $queueID";
			$bounceID = CRM_Core_DAO::singleValueQuery($sql);

			//only create if a bounce record does not exist
			if ( !$bounceID ) {
            	$params = array ( 'job_id'         => $queue['jobID'],
            	                  'event_queue_id' => $queue['queueID'],
            	                  'hash'           => $queue['hash'],
            	                  'bounce_type_id' => DEFAULT_BOUNCE_TYPE,
            	                  'bounce_reason'  => $reason.'', //typecast as string
            	                );
            	//print_r($params);
            	CRM_Mailing_Event_BAO_Bounce::create($params);
			}

			if ( $delete ) {
				$bounceDeleteUrl = "https://sendgrid.com/apiv2/customer.bounces.xml?api_user=$smtpuser&api_key=$smtppass&user=$smtpsubuser&task=delete&email=$email";
				$bounceDelete = simplexml_load_file($bounceDeleteUrl);
				//echo $bounceDelete;
			}
			
        } else {
            echo "No email and/or job found to process. It's possible the email has already been processed.\n\n";
        }
        
    }
} //end bounceRetrieve

//retrieve unsubscribe requests and process as domain level unsubscribe (optout)
function unsubscribeRetrieve( $smtpuser, $smtppass, $smtpsubuser, $delete = true ) {

    require_once 'CRM/Core/DAO.php';

    $unsubscribeUrl = "https://sendgrid.com/apiv2/customer.unsubscribes.xml?api_user=$smtpuser&api_key=$smtppass&user=$smtpsubuser&task=get&date=1";
    $unsubscribeRetrieve = simplexml_load_file($unsubscribeUrl);
    //CRM_Core_Error::debug('unsubscribeRetrieve', $unsubscribeRetrieve);
    
    foreach ( $unsubscribeRetrieve as $unsubscribe ) {
        //get parameters
        $fixdate = date("Y-m-d H:i:s", time($unsubscribe->created)+60*60 );
        $email   = $unsubscribe->email;
        //echo "find: $email\n";
        
        //find the email and job details
        $queue = findEmailJob( $email );

        //TODO we need to check to see if the email has already been processed
        //OR we need to delete the bounce from SendGrid after processing
        
        //now lets process the bounce if we found the necessary values
        if ( $queue['jobID'] != 0 ) {
            require_once 'CRM/Mailing/Event/BAO/Unsubscribe.php';
            $unsubs = CRM_Mailing_Event_BAO_Unsubscribe::unsub_from_domain($queue['jobID'],$queue['queueID'],$queue['hash']);
            if ( !$unsubs ) {
                return civicrm_create_error( ts( 'Queue event could not be found' ) );
            } elseif ( $delete ) {
				$unsubscribeDeleteUrl = "https://sendgrid.com/apiv2/customer.unsubscribes.xml?api_user=$smtpuser&api_key=$smtppass&user=$smtpsubuser&task=delete&email=$email";
				$unsubscribeDelete = simplexml_load_file($unsubscribeDeleteUrl);
				//echo $unsubscribeDelete;
			}
        } else {
            echo "No email and/or job found to process. It's possible the email has already been processed.\n\n";
        }
    }
    
} //end unsubscribeRetrieve

//common function to take an email and determine the job and email id as best we are able
//returns an array with details from the queue table
function findEmailJob( $email ) {

    //find email id; we can ignore if already on_hold
    $findEmails = "SELECT id FROM civicrm_email WHERE email = '$email' AND (is_primary = 1 OR is_bulkmail = 1)";
    $e_result   = CRM_Core_DAO::executeQuery( $findEmails );
        
    //find most recent job using all matching emails
    $queue['jobID'] = 0;
    while ( $e_result->fetch() ) {
        //CRM_Core_Error::debug('e_result', $j_result);
        $eid = $e_result->id;
        $findJobs = "SELECT id AS queueID, max(job_id) AS jobID, email_id AS emailID, contact_id AS contactID, hash 
                     FROM civicrm_mailing_event_queue 
                     WHERE email_id = $eid 
                       AND job_id = (SELECT max(job_id) FROM civicrm_mailing_event_queue WHERE email_id = $eid)";
        $j_result = CRM_Core_DAO::executeQuery( $findJobs );
            
        //get most recent job id
        while ( $j_result->fetch() ) {
            //CRM_Core_Error::debug('j_result', $j_result);
            if ( $j_result->jobID > $queue['jobID'] ) {
                $queue['jobID']     = $j_result->jobID;
                $queue['queueID']   = $j_result->queueID;
                $queue['emailID']   = $j_result->emailID;
                $queue['contactID'] = $j_result->contactID;
                $queue['hash']      = $j_result->hash;
            }
        }
        $j_result->free( );
      }
    $e_result->free( );
    //print_r($queue);

    return $queue;
}

run();


?>
