<?php
require_once 'CRM/Core/Error.php';
require_once 'CRM/Utils/IMAP.php';
require_once 'CRM/Core/DAO.php';

class CRM_IMAP_AJAX {
    private static $db = null;
    private static $server = "{webmail.senate.state.ny.us/imap/ssl/notls}";
    private static $imap_accounts = array();
    private static $bbconfig = null;

    /* setupImap()
     * Parameters: None.
     * Returns: None.
     * This function loads the Bluebird config then parses out the
     * listed IMAP accounts and stores the data in an array
     * so they can be looped through later.
     */
    private static function setupImap() {
        // Pull Bluebird config and assign it to the $bbconfig variable in case we need it later
        require_once dirname(__FILE__).'/../../../../../civicrm/scripts/bluebird_config.php';
        self::$bbconfig = get_bluebird_instance_config();

        // The format of the accounts is:
        // user1|pass1,user2|pass2,user3|pass3
        // So we'll split on commas then split on pipes to assign to user and pass variables
        $imapAccounts = explode(',', self::$bbconfig['imap.accounts']);
        foreach($imapAccounts as $imapAccount) {
            list($user, $pass) = explode('|', $imapAccount);
            self::$imap_accounts[] = array( 'user'  =>  $user,
                                            'pass'  =>  $pass);
        }

    }

    /* db()
     * Parameters: None.
     * Returns: The database object for the instance.
     * Occasionally we'll need the raw database connection to do
     * some processing, this will get the database connection from
     * CiviCRM and set it to a static variable.
     */
    private static function db() {
        // Load the DAO Object and pull the connection
        if (self::$db == null) {
            $nyss_conn = new CRM_Core_DAO();
            $nyss_conn = $nyss_conn->getDatabaseConnection();
            self::$db = $nyss_conn->connection;
        }
        return self::$db;
    }

    /* get($key)
     * Parameters: $key: The name of the input in the GET message.
     * Returns: The escaped string.
     * We want to be able to escape the string so when we use
     * the key in a query, it's already sanitized.
     */
    private static function get($key) {
        // Call mysql_real_escape_string using the db() connection object
        return mysql_real_escape_string($_GET[$key], self::db());
    }

    /* unifiedMessageInfo()
     * Parameters: messageId = messageid
     * Returns: An Object message details to map to the output.
     */
    public static function unifiedMessageInfo($messageId) {

        $debug = self::get('debug');
        $uniStart = microtime(true);

        $UnprocessedQuery = " SELECT *
        FROM `nyss_inbox_messages`
        WHERE `id` = $messageId
        LIMIT 1";

        $UnprocessedResult = mysql_query($UnprocessedQuery, self::db());
        $UnprocessedOutput = array();
        while($row = mysql_fetch_assoc($UnprocessedResult)) {

          $returnMessage = $row;
          // clean up dates
          $cleanDate = self::cleanDate($row['updated_date']);
          $returnMessage['date_short'] = $cleanDate['short'];
          $returnMessage['date_u'] = $cleanDate['u'];
          $returnMessage['date_long'] = $cleanDate['long'];

          $returnMessage['updated_long'] = date("M d, Y h:i A", strtotime($row['updated_date']));
          $returnMessage['updated_unix'] = date("U", strtotime($row['updated_date']));


          // usefully when setting status for other unmatched messages
          $returnMessage['key'] = $row['sender_email'];
          // find matches
          $senderEmail = $row['sender_email'];
          $rowId = $row['id'];

          $Query="SELECT  contact.id,  email.email FROM civicrm_contact contact
          LEFT JOIN civicrm_email email ON (contact.id = email.contact_id)
          WHERE contact.is_deleted=0
          AND email.email LIKE '$senderEmail'
          GROUP BY contact.id
          ORDER BY contact.id ASC, email.is_primary DESC";
          $matches = array();
          $result = mysql_query($Query, self::db());
          while($row = mysql_fetch_assoc($result)) {
            $matches[] = $row;
          }
          $returnMessage['matches_count'] = count($matches);
          // attachments
          $attachments= array();
          $AttachmentsQuery ="SELECT * FROM nyss_inbox_attachments WHERE `email_id` = $rowId";
          $AttachmentResult = mysql_query($AttachmentsQuery, self::db());
          while($row = mysql_fetch_assoc($AttachmentResult)) {
            $attachments[] = array('fileName'=>$row['file_name'],'rejection'=>$row['rejection'],'fileFull'=>$row['file_full'],'size'=>$row['size'],'ext'=>$row['ext'] );
          }
          $returnMessage['attachments']=$attachments;

          $uniEnd = microtime(true);
          // $output['stats']['overview']['time'] = $uniEnd-$uniStart;

          if ($debug){
            echo "<h1>Full Email OUTPUT</h1>";
            var_dump($returnMessage);
          }
        }
        if(!is_array($returnMessage)){
          $returnMessage = array('code' =>  'ERROR',
              'message'   => 'This Message no longer exists', 'clear'=>'true');
        }else{
          return $returnMessage;
        }
    }

    // properly encode bytes to larger numbers
    public static function decodeSize($bytes){
        $types = array( 'B', 'KB', 'MB', 'GB', 'TB' );
        for( $i = 0; $bytes >= 1024 && $i < ( count( $types ) -1 ); $bytes /= 1024, $i++ );
        return( round( $bytes, 2 ) . " " . $types[$i] );
    }


    /* getUnmatchedMessages()
     * Parameters: None.
     * Returns: A JSON Object of messages in all IMAP inboxes.
     * This function grabs all of the messages in each IMAP Inbox,
     * populates and parses the variables to send back, and then
     * encodes it as a JSON object and shoots it back.
     */
    public static function getUnmatchedMessages() {
        $debug = self::get('debug');
        $start = microtime(true);

       $UnprocessedQuery = " SELECT
        nyss_inbox_messages.id,nyss_inbox_messages.updated_date,nyss_inbox_messages.matched_to,nyss_inbox_messages.sender_email,nyss_inbox_messages.subject,nyss_inbox_messages.forwarder,nyss_inbox_messages.activity_id,nyss_inbox_messages.sender_name,
        nyss_inbox_attachments.file_name,nyss_inbox_attachments.rejection,nyss_inbox_attachments.size
        FROM `nyss_inbox_messages`
        LEFT JOIN nyss_inbox_attachments ON (nyss_inbox_messages.id = nyss_inbox_attachments.email_id)
        WHERE `status` = 0 LIMIT 0 , 100000";

        // echo $UnprocessedQuery;

        $UnprocessedResult = mysql_query($UnprocessedQuery, self::db());
        $UnprocessedOutput = array();
        while($row = mysql_fetch_assoc($UnprocessedResult)) {
            // var_dump($row);
            // exit();
            $UnprocessedOutput = $row;
            $message_id = $row['id'];
            // $returnMessage['successes'][$message_id] = $UnprocessedOutput;
            $returnMessage['Unprocessed'][$message_id]['id'] = $message_id;
            $returnMessage['Unprocessed'][$message_id]['message_id'] = $row['message_id'];
            $returnMessage['Unprocessed'][$message_id]['imap_id'] = $row['imap_id'];
            $returnMessage['Unprocessed'][$message_id]['sender_name'] = $row['sender_name'];
            $returnMessage['Unprocessed'][$message_id]['sender_email'] = $row['sender_email'];
            $returnMessage['Unprocessed'][$message_id]['subject'] = $row['subject'];
            // $returnMessage['successes'][$message_id]['body'] = $row['body'];
            $returnMessage['Unprocessed'][$message_id]['forwarder'] = $row['forwarder'];


            $cleanDate = self::cleanDate($row['updated_date']);
            $returnMessage['Unprocessed'][$message_id]['date_short'] = $cleanDate['short'];
            $returnMessage['Unprocessed'][$message_id]['date_u'] = $cleanDate['u'];
            $returnMessage['Unprocessed'][$message_id]['date_long'] = $cleanDate['long'];
            $returnMessage['Unprocessed'][$message_id]['key'] = $row['sender_email'];


            if($row['file_name']){
              $returnMessage['Unprocessed'][$message_id]['attachments'][] =  array('fileName'=>$row['file_name'],'size'=>$row['size'],'rejection'=>$row['rejection'] );
            }else{
              $returnMessage['Unprocessed'][$message_id]['attachments'] ='';
            }


            // get matched_to info
            $Query="SELECT  contact.id,  email.email FROM civicrm_contact contact
            LEFT JOIN civicrm_email email ON (contact.id = email.contact_id)
            WHERE contact.is_deleted=0
            AND email.email LIKE '".$row['sender_email']."'
            GROUP BY contact.id
            ORDER BY contact.id ASC, email.is_primary DESC";
            $matches = array();
            $result = mysql_query($Query, self::db());
            while($row = mysql_fetch_assoc($result)) {
              $matches[] = $row;
            }
            // var_dump($matches);
            $returnMessage['Unprocessed'][$message_id]['matches_count'] = count($matches);


        }
        mysql_close(self::$db);
        // $returnMessage = array('code' => 'ERROR','message'=>$header->uid." on {$name}");
        $returnMessage['stats']['overview']['Unprocessed'] = count($returnMessage['Unprocessed']);
        $returnMessage['stats']['overview']['total'] =  count($ids);
        $end = microtime(true);
        $returnMessage['stats']['overview']['time'] = $end-$start;

         // Encode the messages variable and return it to the AJAX call
        if ($debug) echo "<pre>";
        echo (!$debug) ?  json_encode($returnMessage) : print_r($returnMessage);
        if ($debug) echo "</pre>";

        CRM_Utils_System::civiExit();
    }

    /* getMessageDetails()
     * Parameters: None.
     * Returns: None.
     * This function grabs the unassigned message from the bd,
     * returns an error if the message is nolonger unassigned
     */
    public static function getMessageDetails() {
        $messageId = self::get('id');
        $output = self::unifiedMessageInfo($messageId);
        $admin = CRM_Core_Permission::check('administer CiviCRM');
        $output['filebug'] = $admin;
        $status = $output['status'];
        if($status != ''){
           switch ($status) {
            case '0':
              echo json_encode($output);
              break;
            case '1':
              $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'Message is already Assigned','clear'=>'true');
              echo json_encode($returnCode);
              break;
            case '7':
              $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'Message has been cleared from inbox','clear'=>'true');
              echo json_encode($returnCode);
              break;
            case '8':
            case '9':
              $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'Message has been deleted','clear'=>'true');
              echo json_encode($returnCode);
              break;
          }
        }else{
          $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'Message not found','clear'=>'true');
          echo json_encode($returnCode);
        }


        CRM_Utils_System::civiExit();
    }


    /* cleanDate
     * Parameters: $date: A date
     * Returns: 3 date formats
     */
    public static function cleanDate($date){
      $strtotime = strtotime($date);

      if(date('Ymd') == date('Ymd', $strtotime)){ $today = true; };

      // check if the message is from last year
      if ( (date("Y", $strtotime) - date("Y")) < 0 ){
        $formatted = date("M d, Y", $strtotime);
      }else{
        // if the message is from this year, see if the message is from today
        if ($today){
          $formatted = 'Today '.date("h:i A", $strtotime);
        }else{
          $formatted = date("M d h:i A", $strtotime);
        }
      }
      return array(
        'long'=> date("M d, Y h:i A", $strtotime),
        'u'=>date("U", $strtotime),
        'short'=>$formatted);

    }

    /* deleteMessage()
     * Parameters: None.
     * Returns: None.
     * This function connects to the IMAP server with the specified user name
     * and password, then deletes the message based on the UID
     */
    public static function deleteMessage() {
        // Set up IMAP variables
        self::setupImap();
        $id = self::get('id');
        $output = self::unifiedMessageInfo($id);
        $imap_id = $output['imap_id'];
        $message_id = $output['message_id'];

        $session = CRM_Core_Session::singleton();
        $userId =  $session->get('userID');

        // Delete the message with the specified UID
        $returnCode = array('code'=>'SUCCESS','status'=> '0','message'=>'Message Deleted');
        $UPDATEquery = "UPDATE `nyss_inbox_messages`
        SET  `status`= 9, `matcher` = $userId
        WHERE `id` =  {$id}";
        $UPDATEresult = mysql_query($UPDATEquery, self::db());

        echo json_encode($returnCode);
        CRM_Utils_System::civiExit();
    }

    /* searchContacts
     * Paramters: None.
     * Returns: None.
     * This function will grab the inputs from the GET variable and
     * do a search for contacts and return them as a JSON object.
     * Only returns Records with Primary emails & address (so no dupes)
     */
     public static function searchContacts() {
        $start = microtime(true);
        $s = self::get('s');
        $debug = self::get('debug');

        $from = "FROM civicrm_contact contact\n";
        $where = "WHERE contact.is_deleted=0\n";
        $order = "ORDER BY contact.id ASC";

        $from.=" LEFT JOIN civicrm_email email ON (contact.id = email.contact_id)\n";
        $from.=" LEFT JOIN civicrm_address address ON (contact.id = address.contact_id)\n";
        $from.=" LEFT JOIN civicrm_phone phone ON (contact.id = phone.contact_id)\n";
        $from.=" LEFT JOIN civicrm_state_province AS state ON address.state_province_id=state.id\n";

        if(self::get('first_name')) $first_name = (strtolower(self::get('first_name')) == 'first name' || trim(self::get('first_name')) =='') ? NULL : self::get('first_name');
	if($first_name) $where .=" AND (contact.first_name LIKE BB_NORMALIZE('$first_name') OR contact.organization_name LIKE BB_NORMALIZE('$first_name'))\n";

        if(self::get('last_name')) $last_name = (strtolower(self::get('last_name')) == 'last name' || trim(self::get('last_name')) =='') ? NULL : self::get('last_name');
	if($last_name) $where .=" AND (contact.last_name LIKE BB_NORMALIZE('$last_name') OR contact.household_name LIKE BB_NORMALIZE('$last_name') )\n";

        if(self::get('email_address')) $email_address = (strtolower(self::get('email_address')) == 'email address' || trim(self::get('email_address')) =='') ? NULL : self::get('email_address');
        if($email_address) {
          // $from.=" JOIN civicrm_email email ON (email.email = '$email_address')\n";
          $where.=" AND email.email LIKE '$email_address'\n";
          $order.=", email.is_primary DESC";
        }

        if(self::get('dob')) $dob = (self::get('dob') == 'yyyy-mm-dd'|| trim(self::get('dob')) =='') ? NULL : date('Y-m-d', strtotime(self::get('dob')));
        // block epoch date
        if ($dob == '1969-12-31') $dob = NULL ;
        // convert dob to standard format
        if($dob) $where.=" AND contact.birth_date = '$dob'\n";

        if(self::get('street_address')) $street_address = (strtolower(self::get('street_address')) == 'street address'|| trim(self::get('street_address')) =='') ? NULL : self::get('street_address');
        if(self::get('city')) $city = (strtolower(self::get('city')) == 'city'|| trim(self::get('city')) =='') ? NULL : self::get('city');


        if($street_address || $city){
          $order.=", address.is_primary DESC";
          if($street_address) {
	    $where.=" AND address.street_address LIKE BB_NORMALIZE_ADDR('$street_address')\n";
          }
          if ($city) {
	    $where.=" AND address.city LIKE BB_NORMALIZE_ADDR('$city')\n";
          }
        }


        $state_id = self::get('state');
        if($state_id && trim(self::get('state')) !='' ) {
          $where.=" AND state.id='$state_id'\n";
        } 

        if(self::get('phone')) $phone = (strtolower(self::get('phone')) == 'phone number'|| trim(self::get('phone')) =='') ? NULL : self::get('phone');
        if ($phone) {
          $where.=" AND phone.phone LIKE '%$phone%'";
        }

        if ($debug){
          echo "<h1>inputs</h1>";
          var_dump($first_name);
          var_dump($last_name);
          var_dump($email_address);
          var_dump($dob);
          var_dump($phone);
          var_dump($street_address);
          var_dump($city);

        }

        if($first_name || $last_name|| $email_address || $dob || $street_address || $city || $phone){
          $query = "SELECT contact.id, contact.display_name, contact.contact_type, contact.birth_date, state.name, address.street_address, address.postal_code, address.city, phone.phone, email.email $from\n$where\nGROUP BY contact.id\n$order";
        }else{
          // do nothing if no query
          $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'Please Enter a query.');
          echo json_encode($returnCode);
          mysql_close(self::$db);
          CRM_Utils_System::civiExit();
        }


        $result = mysql_query($query, self::db());
        $results = array();
        while($row = mysql_fetch_assoc($result)) {
            $results[] = $row;
        }
        if ($debug){
          echo "<h1>Query</h1><pre>";
          print_r($query);
          echo "</pre><h1>Results <small>(".count($results).")</small></h1>";
          var_dump($results);
          echo"<pre>";
        }
        if(count($results) > 0){
          $returnCode = $results;
        }else{
          $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'No Records Found');
        }

        if (!$debug){
          echo json_encode($returnCode);
        }
        $end = microtime(true);
        if(self::get('debug')) echo $end-$start;
        mysql_close(self::$db);
        CRM_Utils_System::civiExit();
    }

    /* addEmail()
     * Parameters: None.
     * Returns: None.
     * Takes message information and saves it as an activity and assigns it to
     * the selected contact ID.
     */
    public static function addEmail() {
        require_once 'api/api.php';
        require_once 'CRM/Utils/File.php';
        require_once 'CRM/Utils/IMAP.php';

        $contactIds = self::get('contacts');
        $senderEmail = self::get('email');
        $debug = self::get('debug');

        $contactIds = explode(',', $contactIds);
        $ContactCount = 0;
        foreach($contactIds as $contactId) {
              // Check to see if contact has the email address being assigend to it,
              // if doesn't have email address, add it to contact
              $emailQuery = "SELECT email.email FROM civicrm_email email WHERE email.contact_id = $contactId";
              $emailResult = mysql_query($emailQuery, self::db());
              $emailResults = array();
              while($row = mysql_fetch_assoc($emailResult)) {
                  $emailResults[] = $row;
              }
              if ($debug){
                  echo "<h1>Contact ".$contactId." has the following emails </h1>";
                  var_dump($emailResults);
              }
              $emailsCount = count($emailResults);

              $matches = 0;
              if ($debug){
                echo "<h1>Contact Non matching results </h1>";
              }
              // if the records don't match, count it, an if the number is > 1 add the record
              foreach($emailResults as $email) {
                  if(strtolower($email['email']) == strtolower($senderEmail)){
                      if ($debug) echo "<p>".$email['email'] ." == ".strtolower($senderEmail)."</p>";
                  }else{
                      $matches++;
                      if ($debug) echo "<p>".$email['email'] ." != ".strtolower($senderEmail)."</p>";
                  }
              }
	      $locationQuery = "SELECT  id FROM `civicrm_location_type` WHERE `name` = 'Other'";
	      $locationResult = mysql_query($locationQuery, self::db());
	      $locationResults = array();
	      while($row = mysql_fetch_assoc($locationResult)) {
		$locationResults[] = $row['id'];
	      }

               // Prams to add email to user
              $params = array(
                  'contact_id' => $contactId,
                  'email' => $senderEmail,
		  'location_type_id' => $locationResults[0],   // Other
                  'version' => 3,
              );
              if(($emailsCount-$matches) == 0){
                  if ($debug) echo "<p> added ".$senderEmail."</p><hr/>";
                  $result = civicrm_api( 'email','create',$params );
              }

        }
        $returnCode = array('code' => 'SUCCESS', 'message' => 'Email was added');
        echo json_encode($returnCode);

        CRM_Utils_System::civiExit();

    }


    /* assignMessage()
     * Parameters: None.
     * Returns: None.
     * Takes message information and saves it as an activity and assigns it to
     * the selected contact ID.
     */
    public static function assignMessage() {
        require_once 'api/api.php';
        require_once 'CRM/Utils/File.php';
        require_once 'CRM/Utils/IMAP.php';
	  $bbconfig = get_bluebird_instance_config();
        $debug = false;
        $debug = self::get('debug');
        $messageUid = self::get('messageId');
        $contactIds = self::get('contactId');

        $session = CRM_Core_Session::singleton();
        $userId =  $session->get('userID');

        //where to write file attachments to:
        $config = CRM_Core_Config::singleton( );
        $uploadDir = $config->customFileUploadDir;
        $uploadInbox = $uploadDir.'inbox/';

        if(!$messageUid || !$contactIds){
          $returnCode = array('code'      =>  'ERROR',
              'message'   =>  'Something went wrong here' );
            echo json_encode($returnCode);
            CRM_Utils_System::civiExit();
        }

        $output = self::unifiedMessageInfo($messageUid);
        $oldActivityId =  mysql_real_escape_string($output['activity_id']);
	$senderEmail = substr(mysql_real_escape_string($output['sender_email']),0,255);
	$senderName = substr(mysql_real_escape_string($output['sender_name']),0,255);
	$forwarder = substr(mysql_real_escape_string($output['forwarder']),0,255);
        $date = mysql_real_escape_string($output['updated_date']);
        $FWDdate = mysql_real_escape_string($output['email_date']);
	$subject =substr( strip_tags( htmlspecialchars_decode( mysql_real_escape_string($output['subject'])) ) ,0,249);
        $body = mysql_real_escape_string($output['body']);
        $status = mysql_real_escape_string($output['status']);
        $key = mysql_real_escape_string($output['sender_email']);
        $messageId = mysql_real_escape_string($output['message_id']);
        $imapId = mysql_real_escape_string($output['imap_id']);

        if($status != 1){
          $attachments = $output['attachments'];

          if ($debug){
            var_dump($messageUid);
            echo "<h1>inputs</h1>";
            var_dump($senderName);
            var_dump($senderEmailAddress);
            var_dump($date);
            var_dump($subject);
            var_dump($body);
            var_dump($messageUid);
            echo "<h1>Attachments</h1>";
            var_dump($attachments);
            echo "<h1>Key</h1>";
            var_dump($key);
          }

          // if this email has been moved / assigned already
          if( $output['code'] == "ERROR"){
            $returnCode = array('code'      =>  'ERROR',
                'message'   =>  $output['message'] );
              echo json_encode($returnCode);
              CRM_Utils_System::civiExit();
          }

  $query = "
  SELECT e.contact_id
  FROM civicrm_group_contact gc, civicrm_group g, civicrm_email e
  WHERE g.name='Authorized_Forwarders'
    AND e.email='".$forwarder."'
    AND g.id=gc.group_id
    AND gc.status='Added'
    AND gc.contact_id=e.contact_id
  ORDER BY gc.contact_id ASC";

              $result = mysql_query($query, self::db());
              $results = array();
              while($row = mysql_fetch_assoc($result)) {
                  $results[] = $row;
              }

          if ($debug){
            echo "<h1>Get forwarder Contact Record for {$forwarder}</h1>";
            if (count($results) != 1 ) echo "<p>If there are no results, or multiple contacts we make bluebird admin the owner</p>";
            var_dump($results);
          }

          // error checking for forwarderId
          if (!$results){
            $forwarderId = 1; // bluebird admin
          } else{
            $forwarderId = $results[0]['contact_id'];
          };

          if ($debug){
            echo "<h1>forwarder ID</h1>";
            var_dump($forwarderId);
          }

          if ($debug){
            echo "<h1>Attach activity to</h1>";
            var_dump($senderEmail);
          }

          $contactIds = explode(',', $contactIds);
          $ContactCount = 0;
          foreach($contactIds as $contactId) {

            // get contact info for return message
            $ContactInfo = self::contactRaw($contactId);
            $ContactName = $ContactInfo['values'][$contactId]['display_name'];

            $aActivityType = CRM_Core_PseudoConstant::activityType();
            $activityType = array_search('Inbound Email', $aActivityType);
	    $aActivityStatus = CRM_Core_PseudoConstant::activityStatus();

	    $imap_activty_status = strtolower($bbconfig['imap.activity.status.default']);
	    if ($imap_activty_status == false || !isset($imap_activty_status)) {
	      $activityStatus = array_search('Completed', $aActivityStatus);
	    }else{
	      $activityStatus = array_search($imap_activty_status, $aActivityStatus);
	    }

            // Submit the activity information and assign it to the right user
            $params = array(
                'activity_type_id' => $activityType,
                'source_contact_id' => $forwarderId,
                'target_contact_id' => $contactId,
                'subject' => $subject,
                'is_auto' => 0, // we manually add it, right ?
                'status_id' => $activityStatus,
                'activity_date_time' => $date,
                'details' => $body,
                'version' => 3
            );
            $activity = civicrm_api('activity', 'create', $params);

	    if ($debug){
	      echo "<h1>Activity Created ?</h1>";
	      var_dump($activity);
	    }

            // if its an error or doesnt return we need errors
            if (($activity['is_error']==1) || ($activity['values']==null ) || (count($activity['values']) !=  1 )){
              $returnCode = array('code'      =>  'ERROR',
                'message'   =>  $activity['error_message']);
              echo json_encode($returnCode);
              CRM_Utils_System::civiExit();
            } else{

              // Now we need to assign the tag to the activity
              $tagid= self::getInboxPollingTagId();
              $assignTag = self::assignTag($activity['id'], 0, $tagid, "quiet");

              if($assignTag['code'] == "ERROR"){
                // var_dump($assignTag);
                $returnCode = array('code'      =>  'ERROR',
                'message'   =>  $assignTag['message']);
                echo json_encode($returnCode);
                CRM_Utils_System::civiExit();
              }else{
                $activity_id =$activity['id'];
                $returnCode['code'] = 'SUCCESS';
                $returnCode['assigned'][] = array('code' =>'SUCCESS','message'=> "Message Assigned to ".$ContactName,'key'=>$key,'contact'=>$contactId);

                // if this is not the first contact, add a new row to the table
                if($ContactCount > 0){
                  $debug= 'Added on assignment to #'.$ContactCount;
                  $UPDATEquery = "INSERT INTO `nyss_inbox_messages` (`message_id`, `imap_id`, `sender_name`, `sender_email`, `subject`, `body`, `forwarder`, `status`, `debug`, `updated_date`, `email_date`,`activity_id`,`matched_to`,`matcher`) VALUES ('{$messageId}', '{$imapId}', '{$senderName}', '{$senderEmail}', '{$subject}', '{$body}', '{$forwarder}', '1', '$debug', '$date', '{$FWDdate}','{$activity_id}','{$contactId}','{$userId}');";


                }else{
                  $UPDATEquery = "UPDATE `nyss_inbox_messages`
                  SET  `status`= 1, `matcher` = $userId, `activity_id` = $activity_id, `matched_to` = $contactId, `updated_date` = '$date'
                  WHERE `id` =  {$messageUid}";
                }
                $ContactCount++;


                $UPDATEresult = mysql_query($UPDATEquery, self::db());
                // var_dump($attachments);
                // var_dump(is_array($attachments[0]));

                // exit();
                if(isset($attachments[0])){
                  foreach ($attachments as $key => $attachment) {
                    $fileName = $attachment['fileName'];
                    $fileFull = $attachment['fileFull'];
                    // var_dump("Origin File Full : ". $fileFull);
                    // var_dump("Origin File NAME : ". $fileName);
                    if (file_exists($fileFull)){


                      $newName = CRM_Utils_File::makeFileName( $fileName );
                      $file = $uploadDir. $newName;
                      // var_dump("Final File Full : ". $file);

                      // move file to the civicrm upload directory
                      rename( $fileFull, $file );

                      $finfo = finfo_open(FILEINFO_MIME_TYPE);
                      $mime = finfo_file($finfo, $file);
                      finfo_close($finfo);
                      // var_dump("Mime Type : ". $mime);

                      // // mimeType, uri, orgin date -> return id
                      $insertFIleQuery = "INSERT INTO `civicrm_file` (`mime_type`, `uri`,`upload_date`) VALUES ( '{$mime}', '{$newName}','{$output['updated_date']}');";
                      $rowUpdated = "SELECT id FROM civicrm_file WHERE uri = '{$newName}';";

                      $insertFileResult = mysql_query($insertFIleQuery, self::db());
                      $rowUpdatedResult = mysql_query($rowUpdated, self::db());

                      $insertFileOutput = array();
                      while($row = mysql_fetch_assoc($rowUpdatedResult)) {
                        $fileId = $row['id'];
                      }

                      $insertEntityQuery = "INSERT INTO `civicrm_entity_file` (`entity_table`, `entity_id`, `file_id`) VALUES ('civicrm_activity','{$activity['id']}', '{$fileId}');";
                      $insertEntity = mysql_query($insertEntityQuery, self::db());
                    }else{
                      // echo "File Exists";
                    }
                  }
                }
              }
            }
          }
        echo json_encode($returnCode);
        }else{
          $returnCode = array('code'      =>  'ERROR', 'message'   =>  'Message is already matched' );
          echo json_encode($returnCode);
        }
        CRM_Utils_System::civiExit();
    }


    // Get Raw data for verbose error logging
    public static function contactRaw($id){
        require_once 'api/api.php';
        $params = array('version'   =>  3, 'activity'  =>  'get', 'id' => $id, );
        $contact = civicrm_api('contact', 'get', $params);
        return $contact;
    }
    public static function activityRaw($id){
        require_once 'api/api.php';
        $params = array('version'   =>  3, 'activity'  =>  'get', 'id' => $id, );
        $activity = civicrm_api('activity', 'get', $params);
        return $activity;
    }
    public static function tagRaw($id){
        require_once 'api/api.php';
        $params = array('version'   =>  3, 'activity'  =>  'get', 'id' => $id, );
        $tag = civicrm_api('tag', 'get', $params);
        return $tag;
    }


    public static function assignTag($inActivityIds = null, $inContactIds = null, $inTagIds = null, $response = null) {
        $activityIds    =   ($inActivityIds) ? $inActivityIds : self::get('activityIds');
        $contactIds     =   ($inContactIds) ? $inContactIds : self::get('contactIds');
        $tagIds         =   ($inTagIds) ? $inTagIds : self::get('tagIds');
        $activityIds    =   split(',', $activityIds);
        $contactIds     =   split(',', $contactIds);
        $tagIds         =   split(',', $tagIds);

        // If there are no tagIds or it's zero, return an error message
        // via JSON so we can display it to the user.
        if(is_null($tagIds) || $tagIds == 0) {
            $returnCode = array('code'      =>  'ERROR',
                                'message'   =>  'No valid tags.');
            echo json_encode($returnCode);
            CRM_Utils_System::civiExit();
        }
        require_once 'api/api.php';

        require_once 'CRM/Core/DAO.php';
        $nyss_conn = new CRM_Core_DAO();
        $nyss_conn = $nyss_conn->getDatabaseConnection();
        $conn = $nyss_conn->connection;

        $returnCode = array();

        foreach($tagIds as $tagId) {
          // check to see if it is a new tag
          preg_match('/\:\:\:/',$tagId,$matches,PREG_OFFSET_CAPTURE);

          if(count($matches) > 0){
            $newtag = substr($tagId, 0, $matches[0][1]);
            // If there's no tag, create it.
            $params = array(
                'name' => $newtag,
                'version' => 3,
                'parent_id' => 296
            );
            $result = civicrm_api('tag', 'create', $params);
            $tagId = $result['id'];
          }

          //get data about tag
          $data = self::tagRaw($tagId);
          $tagName = $data['values'][$tagId]['name'];

            foreach($contactIds as $contactId) {
                if($contactId == 0)  break;
                $params = array(
                                'entity_table'  =>  'civicrm_contact',
                                'entity_id'     =>  $contactId,
                                'tag_id'        =>  $tagId,
                                'version'       =>  3,
                                );

                $result = civicrm_api('entity_tag', 'create', $params );
                //get data about contact
                $data = self::contactRaw($contactId);
                $name = $data['values'][$contactId]['display_name'];
                // exit();
                if($result['is_error']==1){
                    $returnCode = array('code' => 'ERROR','message'=>$result['error_message']." on {$name}");
                }elseif ($result['not_added']==1 ) {
                    $returnCode = array('code' => 'ERROR','message'=>"Tag '{$tagName}' Already exists on {$name}");
                }else{
                    $returnCode = array('code' =>'SUCCESS','message'=> "Tag '{$tagName}' Added to {$name}");
                }

            }
            foreach($activityIds as $id) {
                $output = self::unifiedMessageInfo($id);
                $activityId = $output['activity_id'];
                if($activityId == 0) break;
                //get data about tag
                $data = self::activityRaw($activityId);
                $subject = $data['values'][$activityId]['subject'];
                // exit();

                $query = "SELECT * FROM civicrm_entity_tag
                            WHERE entity_table='civicrm_activity'
                            AND entity_id={$activityId}
                            AND tag_id={$tagId};";
                $result = mysql_query($query, $conn);

                if(mysql_num_rows($result) == 0) {
                    $query = "INSERT INTO civicrm_entity_tag(entity_table,entity_id,tag_id)
                              VALUES('civicrm_activity',{$activityId},{$tagId});";
                    $result = mysql_query($query, $conn);

                    if($result == null) {
                        $returnCode = array('code'=>'ERROR','message'=> "'$subject' on  '{$tagName}'");
                    }else{
                         $returnCode = array('code'=>'SUCCESS','status'=> '1','message'=>"Tag '{$tagName}' Added to {$subject}",'clear'=>'true');
                    }
                }else{
                    $returnCode = array('code'=>'ERROR','message'=> "'$subject' on  '{$tagName}'");
                }
          }
        }
        if($response){
          return $returnCode;
        }else{
          echo json_encode($returnCode);
        }

        //the following causes exit before the loop in assignMessage can complete. commenting it allows multi-match
        //but without it the script returns a full page, a new addition in 1.4
        // CRM_Utils_System::civiExit();
    }

    public static function getMatchedMessages() {
        require_once 'api/api.php';
        $debug = self::get('debug');


        // $UnprocessedQuery = " SELECT id,updated_date,matched_to,sender_email
        // FROM `nyss_inbox_messages`
        // LEFT JOIN nyss_inbox_attachments ON (nyss_inbox_messages.id = nyss_inbox_attachments.email_id)

        // WHERE `status` = 1
        // LIMIT 0 , 100000";
       $UnprocessedQuery = " SELECT
        nyss_inbox_messages.id,nyss_inbox_messages.updated_date,nyss_inbox_messages.matched_to,nyss_inbox_messages.sender_email,nyss_inbox_messages.subject,nyss_inbox_messages.forwarder,nyss_inbox_messages.activity_id,nyss_inbox_messages.matcher,
        nyss_inbox_attachments.file_name,nyss_inbox_attachments.rejection,nyss_inbox_attachments.size,
        civicrm_contact.display_name
        FROM `nyss_inbox_messages`
        LEFT JOIN nyss_inbox_attachments ON (nyss_inbox_messages.id = nyss_inbox_attachments.email_id)
        LEFT JOIN civicrm_contact ON (nyss_inbox_messages.matcher = civicrm_contact.id)
        WHERE `status` = 1 LIMIT 0 , 100000";

        // echo $UnprocessedQuery;

        $UnprocessedResult = mysql_query($UnprocessedQuery, self::db());
        $UnprocessedOutput = array();
        while($row = mysql_fetch_assoc($UnprocessedResult)) {
            // var_dump($row);
            // exit();
            $UnprocessedOutput = $row;
            $message_id = $row['id'];
            // $returnMessage['successes'][$message_id] = $UnprocessedOutput;
            $returnMessage['successes'][$message_id]['activity_id'] = $row['activity_id'];
            $returnMessage['successes'][$message_id]['id'] = $message_id;

            $cleanDate = self::cleanDate($row['updated_date']);

            $returnMessage['successes'][$message_id]['date_short'] = $cleanDate['short'];
            $returnMessage['successes'][$message_id]['date_u'] = $cleanDate['u'];
            $returnMessage['successes'][$message_id]['date_long'] = $cleanDate['long'];
            $returnMessage['successes'][$message_id]['sender_email'] = $row['sender_email'];
            $returnMessage['successes'][$message_id]['forwarder'] = $row['forwarder'];

            if($row['sender_name']){
              $returnMessage['successes'][$message_id]['sender_name'] = $row['sender_name'];
            }else{
              $returnMessage['successes'][$message_id]['sender_name'] = '';
            }


            $returnMessage['successes'][$message_id]['subject'] = $row['subject'];
            $returnMessage['successes'][$message_id]['matcher'] =  $row['matcher'];

            if(!$row['display_name']){
              $returnMessage['successes'][$message_id]['matcher_name'] =  'Automatically Matched';
            }else{
              $returnMessage['successes'][$message_id]['matcher_name'] =  $row['display_name'];
            }

            if($row['file_name']){
              $returnMessage['successes'][$message_id]['attachments'][] =  array('fileName'=>$row['file_name'],'size'=>$row['size'],'rejection'=>$row['rejection'] );
            }else{
              $returnMessage['successes'][$message_id]['attachments'] ='';
            }

            // get matched_to info
            $returnMessage['successes'][$message_id]['matched_to'] = $row['matched_to'];
            $MatchedToQuery = " SELECT contact_type,first_name,last_name,display_name
            FROM `civicrm_contact`
            WHERE `id` = ".$row['matched_to']." LIMIT 1";

            $MatchedToResult = mysql_query($MatchedToQuery, self::db());
            $MatchedToOutput = array();

            while($row = mysql_fetch_assoc($MatchedToResult)) {
              $returnMessage['successes'][$message_id]['contactType'] = $row['contact_type'];
              $returnMessage['successes'][$message_id]['firstName'] = $row['first_name'];
              $returnMessage['successes'][$message_id]['lastName'] = $row['last_name'];
              $returnMessage['successes'][$message_id]['fromName'] = $row['display_name'];
            }
            // exit();





        }
        mysql_close(self::$db);

        $returnMessage['stats']['overview']['successes'] = count($returnMessage['successes']);
        $returnMessage['stats']['overview']['errors'] =   count($errors);
        $returnMessage['stats']['overview']['total'] =  count($returnMessage['successes']) + count($errors);
        // $returnMessage['stats']['overview']['time'] = $end-$start;
        // $returnMessage['errors'] = $errors;

        if ($debug) echo "<pre>";
        echo (!$debug) ?  json_encode($returnMessage) : print_r($returnMessage);
        if ($debug) echo "</pre>";
        CRM_Utils_System::civiExit();
    }

    public static function getActivityDetails() {
      $id = self::get('id');
      $output = self::unifiedMessageInfo($id);
      // overwrite incorrect details
      $changeData = self::contactRaw($output['matched_to']);
      $output['sender_name'] = $changeData['values'][$output['matched_to']]['display_name'];
      $output['sender_email'] = $changeData['values'][$output['matched_to']]['email'];
      $admin = CRM_Core_Permission::check('administer CiviCRM');
      $output['filebug'] = $admin;
      if(!$output){
        $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'Activity not found');#,'clear'=>'true');
      }else{
        $status = $output['status'];
        if($status != ''){
           switch ($status) {
            case '1':
              echo json_encode($output);
              break;
            case '7':
              $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'Message has been cleared from inbox','clear'=>'true');
              echo json_encode($returnCode);
              break;
            case '8':
            case '9':
              $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'Message has been deleted','clear'=>'true');
              echo json_encode($returnCode);
              break;
          }
       }
      }
      CRM_Utils_System::civiExit();
    }


    // delete activit and enttity ref
    public static function deleteActivity() {
        require_once 'api/api.php';
        $messageId = self::get('id');
        $session = CRM_Core_Session::singleton();
        $userId =  $session->get('userID');

        $output = self::unifiedMessageInfo($messageId);
        $activity_id = $output['activity_id'];
        $tagid = self::getInboxPollingTagId();
        $error = false;
        $debug = self::get('debug');

        // deleteing a activity
        $params = array(
            'id' => $activity_id,
            'activity_type_id' => 1,
            'version' => 3,
        );

        $deleteActivity = civicrm_api('activity','delete',$params );

        if($deleteActivity['is_error'] == 1){
          $error = true;
        }

        if(!$error){
          $UPDATEquery = "UPDATE `nyss_inbox_messages`
          SET  `status`= 9, `matcher` = $userId
          WHERE `id` =  {$messageId}";
          $UPDATEresult = mysql_query($UPDATEquery, self::db());
          $returnCode = array('code'=>'SUCCESS','id'=>$messageId, 'message'=>'Activity Deleted');
        }else{
          $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'Activity not found','clear'=>'true');
        }
        echo json_encode($returnCode);

        mysql_close(self::$db);
        CRM_Utils_System::civiExit();
    }

    // remove the activity tag
    public static function untagActivity() {
        require_once 'api/api.php';
        $messageId = self::get('id');
        $session = CRM_Core_Session::singleton();
        $userId =  $session->get('userID');
        $output = self::unifiedMessageInfo($messageId);
        if(!$output){
          $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'Message not found');#,'clear'=>'true');
        }else{
          $status = $output['status'];
          if($status != ''){
             switch ($status) {
              case '1':
                $activity_id = $output['activity_id'];
                $UPDATEquery = "UPDATE `nyss_inbox_messages`
                SET  `status`= 7, `matcher` = $userId
                WHERE `id` =  {$messageId}";
                $UPDATEresult = mysql_query($UPDATEquery, self::db());
                $returnCode = array('code'=>'SUCCESS','id'=>$messageId, 'message'=>'Activity Cleared');
                echo json_encode($returnCode);
                mysql_close(self::$db);
                break;
              case '7':
                $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'Message has been cleared from inbox','clear'=>'true');
                echo json_encode($returnCode);
                break;
              case '8':
              case '9':
                $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'Message has been deleted','clear'=>'true');
                echo json_encode($returnCode);
                break;
            }
         }
        }

        CRM_Utils_System::civiExit();

    }

    // reAssignActivity
    public static function reassignActivity() {
      require_once 'api/api.php';
      $id = self::get('id');
      $debug = self::get('debug');

      $output = self::unifiedMessageInfo($id);
      $contact = mysql_real_escape_string($output['matched_to']);
      $activityId = mysql_real_escape_string($output['activity_id']);
      $date = mysql_real_escape_string($output['updated_date']);

      $change = self::get('change');
      $results = array();
      $changeData = self::contactRaw($change);
      $changeName = $changeData['values'][$change]['display_name'];
      $firstName = $changeData['values'][$change]['first_name'];
      $LastName = $changeData['values'][$change]['last_name'];
      $contactType = $changeData['values'][$change]['contact_type'];
      $email = $changeData['values'][$change]['email'];
      $tagid = self::getInboxPollingTagId();

      if ($debug){
        echo "<h1>inputs</h1>";
        var_dump($contact);
        var_dump($activityId);
        echo "<h1>Contact Info</h1>";
        var_dump($change);
        var_dump($changeName);
        var_dump($firstName);
        var_dump($LastName);
        var_dump($contactType);
        var_dump($email);
        var_dump($tagid);
      }

      // we need to check to see if the activity is still assigned to the same contact
      // if not, kill it

      $query = <<<EOQ
SELECT COUNT(id)
FROM `civicrm_activity_target`
WHERE `activity_id` = $activityId
AND `target_contact_id` = $contact
EOQ;
      $check_result = mysql_query($query, self::db());
      if($row = mysql_fetch_assoc($check_result)) {
        $check = $row['COUNT(id)'];
      }
      if ($debug){
        echo "<h1>check</h1>";
        var_dump($check);
      }
      if($check != '1'){
        $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'Activity is not assigned to this Contact, Please Reload','clear'=>'true');
        echo json_encode($returnCode);
        CRM_Utils_System::civiExit();
      }
      // change the contact
      $Update = <<<EOQ
UPDATE `civicrm_activity_target`
SET  `target_contact_id`= $change
WHERE `activity_id` =  $activityId
EOQ;

      // change the row
      $Updated_results = mysql_query($Update, self::db());
      while($row = mysql_fetch_assoc($Updated_results)) {
           $results[] = $row;
      }

      $Source_update = <<<EOQ
UPDATE `civicrm_activity`
SET  `is_auto`= 0
WHERE `id` =  $activityId
EOQ;
      $Source_results = mysql_query($Source_update, self::db());

      $session = CRM_Core_Session::singleton();
      $userId =  $session->get('userID');
      $UPDATEquery = "UPDATE `nyss_inbox_messages`
      SET  `matcher` = $userId,  `matched_to` = $change, `sender_name` = '$changeName',`sender_email` = '$email', `updated_date` = '$date'
      WHERE `id` =  {$id}";
      $UPDATEresult = mysql_query($UPDATEquery, self::db());

      $returnCode = array('code'=>'SUCCESS','id'=>$id,'contact_id'=>$change,'contact_type'=>$contactType,'first_name'=>$firstName,'last_name'=>$LastName,'display_name'=>$changeName,'email'=>$email,'activity_id'=>$id,'message'=>'Activity Reassigned to '.$changeName);

      echo json_encode($returnCode);
      mysql_close(self::$db);
      CRM_Utils_System::civiExit();
    }

    public static function searchTags() {
        require_once 'api/api.php';
        $name = self::get('name');
        $start = self::get('timestamp');

        $results = array();

        $query = <<<EOQ
SELECT id, name
FROM `civicrm_tag`
WHERE `parent_id` ='296' && `name` LIKE '$name%'
EOQ;

        $result = mysql_query($query, self::db());

        // there are no results, add a new tag
        if(mysql_num_rows($result) == 0) {
          $output = array(array("name"=>$name, "id"=> $name.':::value'));
        } else {

          // add results to the output
          $output = array();
          while($row = mysql_fetch_assoc($result)) {
              array_push( $output, array("name"=>$row['name'], "id"=>$row['id']));
          }
          // if not result exactly matches out search, make it a new tag at the beginning
          $matches = 0;
          foreach ($output as $key => $value) {
            if($value['name'] != $name ){
              $matches++;
            }
          }
          if($matches == count($output)) $output = array_merge( array(array("name"=>$name, "id"=> $name.':::value')), $output);
        }

        echo json_encode($output);
        mysql_close(self::$db);
        CRM_Utils_System::civiExit();
    }

    public static function addTags() {
        require_once 'api/api.php';
        $tag_ids = self::get('tags');
        $activityId = self::get('activityId');
        $contactId = self::get('contactId');
        // self::assignTag($activityId, $contactId, $tag_ids );
        self::assignTag($activityId, $contactId, $tag_ids,'quiet');
    }

    function getInboxPollingTagId() {
      require_once 'api/api.php';

      // Check if the tag exists
      $params = array(
        'name' => 'Inbox Polling Unprocessed',
        'version' => 3,
      );
      $result = civicrm_api('tag', 'get', $params);
      if($result && isset($result['id'])) {
        return $result['id'];
      }

      // If there's no tag, create it.
      $params = array(
          'name' => 'Inbox Polling Unprocessed',
          'description' => 'Tag noting that this activity has been created by Inbox Polling and is still Unprocessed.',
          'version' => 3,
      );
      $result = civicrm_api('tag', 'create', $params);
        if($result && isset($result['id'])) {
        return $result['id'];
      }
    }

    public static function getReports() {
      $Query = " SELECT
	nyss_inbox_messages.id,nyss_inbox_messages.updated_date,nyss_inbox_messages.email_date,nyss_inbox_messages.matched_to,nyss_inbox_messages.sender_email,nyss_inbox_messages.subject,nyss_inbox_messages.forwarder,nyss_inbox_messages.activity_id,nyss_inbox_messages.sender_name,nyss_inbox_messages.status,nyss_inbox_messages.matcher,
	nyss_inbox_attachments.file_name,nyss_inbox_attachments.rejection,nyss_inbox_attachments.size,
	civicrm_contact.display_name
	FROM `nyss_inbox_messages`
	LEFT JOIN nyss_inbox_attachments ON (nyss_inbox_messages.id = nyss_inbox_attachments.email_id)
	LEFT JOIN civicrm_contact ON (nyss_inbox_messages.matcher = civicrm_contact.id)
	WHERE `status` != 99
	LIMIT 0 , 100000";

      $QueryResult = mysql_query($Query, self::db());
      $Output = array();
      $unMatched= 0;
      $Matched =0;
      $Cleared =0;
      $Deleted =0;
      $Errors =0;

      while($row = mysql_fetch_assoc($QueryResult)) {

	$message_id = $row['id'];
	  $Output['successes'][$message_id]['id'] = $message_id;
	  $Output['successes'][$message_id]['sender_name'] = $row['sender_name'];
	  $Output['successes'][$message_id]['sender_email'] = $row['sender_email'];
	  $Output['successes'][$message_id]['subject'] = $row['subject'];
	  $Output['successes'][$message_id]['forwarder'] = $row['forwarder'];
	  $cleanDate = self::cleanDate($row['updated_date']);
	  $Output['successes'][$message_id]['date_short'] = $cleanDate['short'];
	  $Output['successes'][$message_id]['date_u'] = $cleanDate['u'];
	  $Output['successes'][$message_id]['date_long'] = $cleanDate['long'];
	  $Output['successes'][$message_id]['message_status'] = $row['status'];
	  $Output['successes'][$message_id]['matcher'] = $row['matcher'];

	  $emailDate = self::cleanDate($row['email_date']);
	  $Output['successes'][$message_id]['email_date_short'] = $emailDate['short'];
	  $Output['successes'][$message_id]['email_date_u'] = $emailDate['u'];
	  $Output['successes'][$message_id]['email_date_long'] = $emailDate['long'];

	  $Output['successes'][$message_id]['matched_to'] = $row['matched_to'];

	  if(!$row['display_name'] || $row['display_name'] != 0){
	    $Output['successes'][$message_id]['matcher_name'] =  'Automatically Matched';
	  }else{
	    $Output['successes'][$message_id]['matcher_name'] =  $row['display_name'];
	  }
	  $MatchedToQuery = " SELECT contact_type,first_name,last_name,display_name
	    FROM `civicrm_contact`
	    WHERE `id` = ".$row['matched_to']." LIMIT 1";

	    $MatchedToResult = mysql_query($MatchedToQuery, self::db());
	    $MatchedToOutput = array();

	    while($row = mysql_fetch_assoc($MatchedToResult)) {
	      $Output['successes'][$message_id]['contactType'] = $row['contact_type'];
	      $Output['successes'][$message_id]['firstName'] = $row['first_name'];
	      $Output['successes'][$message_id]['lastName'] = $row['last_name'];
	      $Output['successes'][$message_id]['fromName'] = $row['display_name'];
	    }


	  if ( $Output['successes'][$message_id]['contactType'] == '') {
	    $Output['successes'][$message_id]['contactType'] = "Unknown";
	    $Output['successes'][$message_id]['fromName'] = $Output['successes'][$message_id]['sender_name'];
	  }

	  if($row['file_name']){
	    $Output['successes'][$message_id]['attachments'][] =  array('fileName'=>$row['file_name'],'size'=>$row['size'],'rejection'=>$row['rejection'] );
	  }else{
	    $Output['successes'][$message_id]['attachments'] ='';
	  }


	  // get matched_to info
	  $Query="SELECT  contact.id,  email.email FROM civicrm_contact contact
	  LEFT JOIN civicrm_email email ON (contact.id = email.contact_id)
	  WHERE contact.is_deleted=0
	  AND email.email LIKE '".$row['sender_email']."'
	  GROUP BY contact.id
	  ORDER BY contact.id ASC, email.is_primary DESC";
	  $matches = array();
	  $result = mysql_query($Query, self::db());
	  while($row = mysql_fetch_assoc($result)) {
	    $matches[] = $row;
	  }
	  $Output['successes'][$message_id]['matches_count'] = count($matches);

      }
      $returnCode = array('code'      =>  'SUCCESS',
			'total' =>  $unMatched+$Matched+$Cleared+$Errors+$Deleted,
			'unMatched' =>  $unMatched,
			'Matched' =>  $Matched,
			'Cleared' =>  $Cleared,
			'Errors' =>  $Errors,
			'Deleted' =>  $Deleted,
			'Messages'=> $Output
			);
          echo json_encode($returnCode);
      CRM_Utils_System::civiExit();
    }

    public static function fileBug() {
      require_once 'api/api.php';
      require_once 'CRM/Utils/Redmine.php';
      // load from config
      $bbconfig = get_bluebird_instance_config();
      $apiKey = $bbconfig['redmine.api.key'];
      $imapAccounts = explode(',', $bbconfig['imap.accounts']);
      // get session stuff
      $session = CRM_Core_Session::singleton();
      $userId =  $session->get('userID');
      $ContactInfo = self::contactRaw($userId);
      $ContactName = $ContactInfo['values'][$userId]['display_name'];
      $url = $_SERVER["SERVER_NAME"].":".$_SERVER["REQUEST_URI"];
      // _Get vars
      $messageId =  self::get('id');
      $browser =  self::get('browser');
      $description =  self::get('description');

      // var_dump($apiKey);
      // var_dump($messageId);
      // var_dump($imapAccounts);
      // var_dump($url);
      // var_dump($browser);
      // var_dump($userId);
      // var_dump($ContactName);

      $debugQuery = " SELECT *
      FROM `nyss_inbox_messages`
      WHERE `id` = $messageId
      LIMIT 1";

      $debugResult = mysql_query($debugQuery, self::db());
      $debugOutput = array();
      while($row = mysql_fetch_assoc($debugResult)) {
        $debugOutput = $row;
      }
      $debugFormatted ='';
      foreach ($debugOutput as $key => $value) {
        $debugFormatted .= $key.": ".$value.";\n";
      }
      // var_dump($debugFormatted);
      $debugFinal = "Full message:\n".$debugFormatted."\n\nBrowser: ".$browser.";\nContactName: ".$ContactName.";\n url: ".$url."\n\nUser Submitted Description:\n".$description;


      $config['url'] = "http://dev.nysenate.gov/";
      $config['apikey'] = $apiKey;
      $_redmine = new redmine($config);

      $project_id = 62; // blue bird project id
      $category_id = 40; // inbox polling 40
      $assignmentUsernames = 184; // me 184 // dean 14 // jason 22 // scott 29
      $subject = "Automated API: Problem with message #".$messageId;

      $addedIssueDetails = $_redmine->addIssue($subject, $debugFinal, $project_id, $category_id, $assignmentUsernames);
      // var_dump($addedIssueDetails);
      // var_dump($addedIssueDetails);
      $addIssueID = (int)$addedIssueDetails->id;
      // var_dump($addIssueID);

      CRM_Utils_System::civiExit();
    }
    public static function createNewContact() {
        require_once 'api/api.php';

        $debug = self::get('debug');
        // testing url

        $first_name = (strtolower(self::get('first_name')) == 'first name' || trim(self::get('first_name')) =='') ? NULL : self::get('first_name');
        $last_name = (strtolower(self::get('last_name')) == 'last name'|| trim(self::get('last_name')) =='') ? NULL : self::get('last_name');
        $email  = (strtolower(self::get('email_address')) == 'email address')|| trim(self::get('email_address')) =='' ? NULL : self::get('email_address');
        $phone = (strtolower(self::get('phone')) == 'phone number'|| trim(self::get('phone')) =='') ? '' : self::get('phone');
        $street_address = (strtolower(self::get('street_address')) == 'street address'|| trim(self::get('street_address')) =='') ? '' : self::get('street_address');
        $street_address_2 = (strtolower(self::get('street_address_2')) == 'street address'|| trim(self::get('street_address_2')) =='') ? '' : self::get('street_address_2');
        $postal_code = (strtolower(self::get('postal_code')) == 'zip code'|| trim(self::get('postal_code')) =='') ? '' : self::get('postal_code');
        $city = (strtolower(self::get('city')) == 'city'|| trim(self::get('city')) =='') ? '' : self::get('city');
        $dob = (strtolower(self::get('dob')) == 'yyyy-mm-dd'|| trim(self::get('dob')) =='') ? '' : self::get('dob');
        $state = (trim(self::get('state')) =='') ? '' : self::get('state');

        if ($debug){
          echo "<h1>inputs</h1>";
          echo"first_name: ";
          var_dump($first_name);
          echo"last_name: ";
          var_dump($last_name);
          echo"email: ";
          var_dump($email);
          echo"phone: ";
          var_dump($phone);
          echo"street_address: ";
          var_dump($street_address);
          echo"street_address_2: ";
          var_dump($street_address_2);
          echo"postal_code: ";
          var_dump($postal_code);
          echo"city: ";
          var_dump($city);
          echo"dob: ";
          var_dump($dob);
          echo"state: ";
          var_dump($state);

        }

        if((isset($first_name))||(isset($last_name))||(isset($email))){
          // echo "one set";
        }else{
            $returnCode = array('code'      =>  'ERROR',
                                'status'    =>  '1',
                                'message'   =>  'Required: First Name or Last Name or Email');
            echo json_encode($returnCode);
            CRM_Utils_System::civiExit();
        }

        //First, you make the contact
        $params = array(
            'first_name' => $first_name,
            'last_name' => $last_name,
            'contact_type' => 'Individual',
            'birth_date' => $dob,
            'version' => 3,
        );

        $contact = civicrm_api('contact','create', $params);

        if ($debug){
          echo "<h1>Contact Creation</h1>";
          echo "Sent Params<br/>";
          var_dump($params);
          echo "Response <br/>";
          if($contact['id']) echo "<a href='/civicrm/contact/view?reset=1&cid=".$contact['id']."'>View Contact </a><br/>";

          var_dump($contact);
        }

	// add the email
	if($email && $contact['id']){
	  $locationQuery = "SELECT  id FROM `civicrm_location_type` WHERE `name` = 'Other'";
	  $locationResult = mysql_query($locationQuery, self::db());
	  $locationResults = array();
	  while($row = mysql_fetch_assoc($locationResult)) {
	    $locationResults[] = $row['id'];
	  }
	  // Prams to add email to user
	  $emailParams = array(
	    'contact_id' => $contact['id'],
	    'email' => $email,
	    'location_type_id' => $locationResults[0],   // Other
	    'version' => 3,
	  );
	  $email = civicrm_api( 'email','create',$emailParams );
	}
        // add the phone number
        if($phone && $contact['id']){
          $phoneParams = array(
            'contact_id' => $contact['id'],
            'phone' => $phone,
            'version' => 3,
          );
          $phone = civicrm_api( 'phone','create',$phoneParams );
        }

        if(($street_address || $street_address_2 || $city || $postal_code || $state ) && $contact['id']){
          //And then you attach the contact to the Address! which is at $contact['id']
          $address_params = array(
              'contact_id' => $contact['id'],
              'street_address' => $street_address,
              'supplemental_address_1' => $street_address_2,
              'city' => $city,
              'postal_code' => $postal_code,
              'is_primary' => 1,
              'state_province_id' => $state,
              'country_id' => 1228,
              'location_type_id' => 1,
              'version' => 3,
              'debug' => 1
          );

          $address = civicrm_api('address', 'create', $address_params);
        }


        if ($debug){
          echo "<h1>Add address to Contact</h1>";
          echo "Sent Params<br/>";
          var_dump($address_params);
          echo "Response <br/><pre>";
          print_r($address);
        }



        if(($contact['is_error'] == 1) || (!empty($address) && ($address['is_error'] == 1))){
          $returnCode = array('code'      =>  'ERROR',
                                'status'    =>  '1',
                                'message'   =>  'Error adding Contact or Address Details'
                                );
        } else {
          $returnCode = array('code'      =>  'SUCCESS',
                                'status'    =>  '0',
                                'contact' => $contact['id']
                                );
        }
        echo json_encode($returnCode);
        CRM_Utils_System::civiExit();

    }

}

