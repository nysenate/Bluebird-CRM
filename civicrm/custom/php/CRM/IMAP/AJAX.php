<?php
require_once 'CRM/Core/Error.php';
require_once 'CRM/Utils/IMAP.php';
require_once 'CRM/Core/DAO.php';

class CRM_IMAP_AJAX {
    private static $db = null;

    private static $server = "{webmail.senate.state.ny.us/imap/notls}";
    private static $imap_accounts = array();
    private static $bbconfig = null;
    private static $contTime = 6; // time between processMailboxes cron job in mins

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
     * Parameters: email.
     * Returns: An Object message details to map to the output.
     * This function grabs a single messages and cleans it for output.
     */   
    public static function unifiedMessageInfo($email) {
        // add &debug=true to any call to get the raw message details back
        $debug = self::get('debug');
        if ($debug){
          echo "<h1>Full Email RAW DATA</h1><pre>";
          print_r($email);
          echo "</pre>";
        }

        if((count($email->time)!= 1)||(count($email->uid) != 1)){
          $returnCode = array('code'      =>  'ERROR',
              'message'   => 'This email no longer exists');
            echo json_encode($returnCode);
            // CRM_Utils_System::civiExit();
        }

        // if message is less the x mins old, check it to see if it matches a contact,
        // if it does directly match, don't allow it to show up in the unmatches screen
        // we do this because the processing scritp hasn't had a chance to match it yet
        $time = time()-(self::$contTime*60);;
        if( $email->time > $time){
          // email hasn't been processed yet
          $code = 'FAILURE';
        }else{
          $code = 'SUCCESS';
          // email has absolutely been processed by script so return it
          $details = ($email->plainmsg) ? $email->plainmsg : $email->htmlmsg;
          $format = ($email->plainmsg) ? "plain" : "html";

          if($format =='plain'){
              $tempDetails = preg_replace("/>|</i", "", $details);
              $body = preg_replace("/(=|\r\n|\r|\n)/i", "\r\n<br>\n", $tempDetails);
          }else{
              // currently strips content 
              $tempDetails = strip_tags($details,'<br>');
              $tempDetails = preg_replace("/<br>/i", "\r\n<br>\n", $tempDetails);
              $body = $details; // switch us back to the html version
          }

          // grab attachments
          $attachmentCount = 0;
          $attachmentHeader = $email->attachments;
          foreach ($attachmentHeader as $key => $value) {
              $name = quoted_printable_decode($key);
              //mb_convert_encoding
              $name = preg_replace("/(\?utf-8\?Q\?)/i", "", $name);
              $name =  preg_replace('/[^A-Za-z0-9.\s\s+]/', ' ', $name);
              $attachmentArray[$attachmentCount] = array('name' => $name,'content' => $value);
              $attachmentCount++;
          }
          $attachmentArray['overview'] = array('total'=>$attachmentCount); 

          // here we grab the details from the message;
          preg_match("/(Subject:|subject:)([^\r\n]*)/i", $tempDetails, $subjects);
          preg_match("/(From:|from:)\s*([^\r\n]*)/i", $tempDetails, $froms);

          if ($debug){
            echo "<h1>Subjects</h1>";
            var_dump($subjects);
            echo "<h1>From</h1>";
            var_dump($froms);
          }
          $fromEmail = self::extract_email_address($froms['2']); // removes the email from the name <email> combo

          // check ot see if forwarded 
          $status = (!$froms['2']) ? 'direct' : 'forwarded';

          // contains info directly from the email header
          $header = array(
              'format' => $format,
              'uid' => $email->uid,
              'from' => $email->sender[0]->personal.' '.$email->sender[0]->mailbox . '@' . $email->sender[0]->host,
              'from_name' => $email->sender[0]->personal,                      
              'from_email' => $email->sender[0]->mailbox.'@'.$email->sender[0]->host,                      
              'subject' => $email->subject,
              'body' => $body,                      
              'date_clean' => self::cleanDate($email->date),
              'status' => $status,
          );

          // if we have a direct message populate accordingly 
          $origin = ($status == 'direct') ?  $header['from'] : $froms['2'];        
          $origin_name = ($status == 'direct') ?  $header['from_name']  :  $fromEmail['name'];
          $origin_email = ($status == 'direct') ?  $header['from_email'] : $fromEmail['email'];
          $origin_date = (substr(self::cleanDate($tempDetails),0,8) == '12-31-69') ?  $header['date_clean'] : self::cleanDate($tempDetails);

          $origin_subject = ($status == 'direct') ?  preg_replace("/(Fwd:|fwd:|Fw:|fw:|Re:|re:) /i", "", $header['subject']) : preg_replace("/(Fwd:|fwd:|Fw:|fw:|Re:|re:) /i", "", $subjects['2']);

          $origin_subject = preg_replace("/(\(|\))/i", "", $origin_subject);
          if( trim(strip_tags($origin_subject)) =='' || strtolower($origin_subject) == 'no subject' ){ 
            $origin_subject = "No Subject";
          }

          // contains info about the forwarded message in the email body
          $forwarded = array(
              'date_clean' => $origin_date, 
              'subject' => $origin_subject, 
              'origin' => $origin,
              'origin_name' => $origin_name, 
              'origin_email' => $origin_email,
              'origin_lookup' => $fromEmail['type'], 
          );

          $output = array('code'=>$code,'header'=>$header,'forwarded'=>$forwarded,'attachments'=>$attachmentArray);
          if ($debug){
            echo "<h1>Full Email OUTPUT</h1>";
            var_dump($output);
          }
          return $output;
        }
    }

    /* getUnmatchedMessages()
     * Parameters: None.
     * Returns: A JSON Object of messages in all IMAP inboxes.
     * This function grabs all of the messages in each IMAP Inbox,
     * populates and parses the variables to send back, and then
     * encodes it as a JSON object and shoots it back.
     */
    public static function getUnmatchedMessages() {
        require_once 'CRM/Utils/IMAP.php';
        $debug = self::get('debug');


        // Pull all of the IMAP usernames into the $imap_accounts variable
        self::setupImap();
        $messages = array();

        // Loop through the imap accounts and assign an "imap id"
        for($imap_id = 0; $imap_id < count(self::$imap_accounts); $imap_id++) {
            // $imap will be your connection to the IMAP server
            $imap = new CRM_Utils_IMAP(self::$server,
                                    self::$imap_accounts[$imap_id]['user'],
                                    self::$imap_accounts[$imap_id]['pass']);
            // Search for all UIDs that meet the criteria of ""
            // Then get the headers for some basic information.
            // then grab the structure for attachments
            $ids = imap_search($imap->conn(),"",SE_UID);
            $headers = imap_fetch_overview($imap->conn(),implode(',',$ids),FT_UID);

            // Loop through the headers and check to make sure they're valid UIDs
            foreach($headers as $header) {
                if( in_array($header->uid,$ids)) {
                    // Get the message based on the UID of the header.
                    $email = $imap->getmsg_uid($header->uid);
                    $output = self::unifiedMessageInfo($email);
                    if ($output['code'] == "SUCCESS"){
                        $returnMessage[$header->uid] =  array( 
                        'subject' =>  $output['forwarded']['subject'],
                        'from' =>  $output['forwarded']['origin_name'].' '.$output['forwarded']['origin_email'],
                        'uid' =>  $header->uid,
                        'date' =>  $output['header']['date_clean'],
                        'format' =>  $output['header']['format'],
                        'from_email' =>  $output['forwarded']['origin_email'],
                        'from_name' =>  $output['forwarded']['origin_name'],
                        'forwarder_email' =>  $output['header']['from_email'],
                        // 'forwarder_name' =>  $output['header']['from_name'],
                        // 'forwarder_time' =>  $output['forwarded']['date_clean'],
                        'attachmentfilename'  => $output['attachments'][0]['name'],
                        // 'attachmentname'  =>  $output['attachments'][0]['name'],
                        'attachment'  => $output['attachments']['overview']['total'],
                        'status' =>$output['header']['status'],
                        'imap_id' =>  $imap_id,
                        // 'origin_lookup' => $output['forwarded']['origin_lookup']

                        );

                    }else{
                      $returnMessage = array('code' => 'ERROR','message'=>$header->uid." on {$name}");

                    }

             
                 }
            }
        }       
        $returnMessage['count'] = count($returnMessage);
        
        // Encode the messages variable and return it to the AJAX call
        if ($debug) echo "<pre>";
        
        echo (!$debug) ?  json_encode($returnMessage) : print_r($returnMessage);
        if ($debug) echo "</pre>";

        CRM_Utils_System::civiExit();
    }

    public function extract_email_address ($string) {        
        // we have to parse out ldap stuff because sometimes addresses are
        // embedded and, see NYSS #5748 for more details 

        // if o= is appended to the end of the email address remove it 
        $string = preg_replace('/\/senate@senate/i', '/senate', $string);

        // ldap addresses have slashes, so we do an internal lookup
        $internal = preg_match("/\//i", $string, $matches);

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
            $return = array('type'=>'LDAP FAILURE','name'=>'LDAP lookup Failed','email'=>'LDAP lookup Failed');
            return $return;
          }
          
        }else{
          // clean out any anything that wouldn't be a name or email, html or plain-text
          $string = preg_replace('/&lt;|&gt;|&quot;|&amp;/i', '', $string);
          $string = preg_replace('/<|>|"|\'/i', '', $string);
          foreach(preg_split('/ /', $string) as $token) {
              $email = filter_var(filter_var($token, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);
              if ($email !== false) {
                  $emails[] = $email;
              }
          }
          $name = str_replace($emails[0], '', $string);
          $return = array('type'=>'inline','name'=>$name,'email'=>$emails[0]);
          return $return;
        }
    }



    /* getMessageDetails()
     * Parameters: None.
     * Returns: None.
     * This function sets up a connection to the IMAP server with the
     * specified connection ID, and retrieves the message based on UID
     */
    public static function getMessageDetails() {
        // Setup the IMAP variables and connect to the IMAP server
        self::setupImap();
        $id = self::get('id');
        $imap_id = self::get('imapId');
        $imap = new CRM_Utils_IMAP(self::$server,
                                    self::$imap_accounts[$imap_id]['user'],
                                    self::$imap_accounts[$imap_id]['pass']);
        // Pull the message via the UID and output it as plain text if possible
        $email = $imap->getmsg_uid($id);

        $structure = imap_fetchstructure($imap->conn(),$id,SE_UID);

        $matches = array();

        // emails use a standard formatter
        $output = self::unifiedMessageInfo($email);

        // var_dump($output);
        if ($output['code'] == "SUCCESS"){

        $returnMessage = array('uid'    =>  $id,
                               'imapId' =>  $imap_id,
                               'format' => $output['header']['format'],
                               'forwardedFull' => $output['header']['from'],
                               'forwardedName'   =>  mb_convert_encoding($output['header']['from_name'], 'UTF-8'),
                               'forwardedEmail'  =>  $output['header']['from_email'],
                               'fromName'  =>  mb_convert_encoding($output['forwarded']['origin_name'], 'UTF-8'),
                               'fromEmail' =>  $output['forwarded']['origin_email'],
                               'forwardedDate' =>  $output['forwarded']['date_clean'],
                               'subject'    =>  mb_convert_encoding($output['forwarded']['subject'], 'UTF-8'),
                               'details'  =>  mb_convert_encoding($output['header']['body'], 'UTF-8'),
                               'attachmentfilename'  => $output['attachments'][0]['name'],
                               'attachmentname'  =>  $output['attachments'][0]['name'],
                               'attachment'  => $output['attachments']['overview']['total'],
                               'status' =>$output['header']['status'],
                               'email_user' => self::$imap_accounts[$imap_id]['user'],
                               'status' =>$output['header']['status'],
                               'origin_lookup' => $output['forwarded']['origin_lookup'],
                               'header_subject' => $output['header']['subject'],
                               'date'   =>  $output['header']['date_clean'],
                               'forwarder_time'   =>  $output['forwarded']['date_clean']);
          }else{
            $returnMessage = array('code' => 'ERROR','message'=>"It's likely that message #{$id} has not be proccessed by the processMailboxes script, wait a few mins");

          }
        // var_dump($returnMessage);  exit();
        echo json_encode($returnMessage);
        CRM_Utils_System::civiExit();
    }


    /* cleanDate
     * Parameters: $date_string: The date string from the from the forwarded message
     * Returns: The 'm-d-y h:i A' formatted date date .
     * This function will format many types of incoming dates
     */
    public static function cleanDate($date_string){
        $matches = array();

        // search for the word date
        $count = preg_match("/(Date:|date:)\s*([^\r\n]*)/i", $date_string, $matches);
        $date_string_short = ($count == 1 ) ? $matches[2]  : $date_string;

        // sometimes email clients think its fun to add stuff to the date, remove it here.
        $date_string_short = preg_replace("/(at)/i", "", $date_string_short);

        // reformat the date to something standard here.
        $date_string_short = date("m-d-y H:i", strtotime($date_string_short));
        return $date_string_short;
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
        $imap_id = self::get('imapId');
        $imap = new CRM_Utils_IMAP(self::$server,
                                    self::$imap_accounts[$imap_id]['user'],
                                    self::$imap_accounts[$imap_id]['pass']);
        // Delete the message with the specified UID
        $status = $imap->deletemsg_uid($id);
        echo json_encode($status);
        CRM_Utils_System::civiExit();
    }

    /* getContacts
     * Paramters: None.
     * Returns: None.
     * This function will grab the inputs from the GET variable and
     * do a search for contacts and return them as a JSON object.
     * Only returns Records with Primary emails & addresse (so no dupes)
     */
    public static function getContacts() {
        $start = microtime(true);
        $s = self::get('s');
        $debug = self::get('debug');

        $from = "FROM civicrm_contact contact\n";
        $where = "WHERE contact.is_deleted=0\n";
        $order = "ORDER BY contact.id ASC";

        $from.="  LEFT JOIN civicrm_email email ON (contact.id = email.contact_id)\n";
        $from.="  LEFT JOIN civicrm_address address ON (contact.id = address.contact_id)\n";
        $from.="  LEFT JOIN  civicrm_phone phone ON (contact.id = phone.contact_id)\n";
        $from.="  LEFT JOIN civicrm_state_province AS state ON address.state_province_id=state.id\n";

        if(self::get('first_name')) $first_name = (strtolower(self::get('first_name')) == 'first name'  || trim(self::get('first_name')) =='') ? NULL : self::get('first_name');
        if($first_name) $where .="  AND (contact.first_name LIKE '$first_name' OR contact.organization_name LIKE '$first_name')\n";

        if(self::get('last_name')) $last_name = (strtolower(self::get('last_name')) == 'last name'  || trim(self::get('last_name')) =='') ? NULL : self::get('last_name');
        if($last_name) $where .="  AND (contact.last_name LIKE '$last_name' OR contact.household_name LIKE '%$last_name%' )\n";

        if(self::get('email_address')) $email_address  = (strtolower(self::get('email_address')) == 'email address' || trim(self::get('email_address')) =='') ? NULL : self::get('email_address');
        if($email_address) {
          // $from.="  JOIN  civicrm_email email ON (email.email = '$email_address')\n";
          $where.="  AND email.email LIKE '$email_address'\n";
          $order.=", email.is_primary DESC";
        }

        if(self::get('dob')) $dob  = (self::get('dob') == 'yyyy-mm-dd'|| trim(self::get('dob')) =='') ? NULL : date('Y-m-d', strtotime(self::get('dob')));
        // block epoch date
        if ($dob == '1969-12-31') $dob  = NULL ;
        // convert dob to standard format
        if($dob) $where.="  AND contact.birth_date = '$dob'\n";

        $state_id = self::get('state');
        if(self::get('street_address')) $street_address = (strtolower(self::get('street_address')) == 'street address'|| trim(self::get('street_address')) =='') ? NULL : self::get('street_address');
        if(self::get('city')) $city = (strtolower(self::get('city')) == 'city'|| trim(self::get('city')) =='') ? NULL : self::get('city');


        if($street_address || $city){
          $order.=", address.is_primary DESC";
          if($street_address) {
            $where.="  AND address.street_address LIKE '$street_address'\n";
          }
          if ($city) {
            $where.="  AND address.city LIKE '$city'\n";
          }
        }
        // if address info, search in state
        if($street_address || $city) {
          $where.="  AND state.id='$state_id'\n";
        }else{
          $where.="  AND (state.id='$state_id' OR state.id IS NULL)\n";
        }

        if(self::get('phone')) $phone = (strtolower(self::get('phone')) == 'phone number'|| trim(self::get('phone')) =='') ? NULL : self::get('phone');
        if ($phone) {
          $where.="  AND phone.phone LIKE '%$phone%'";
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
          $query = "SELECT  contact.id, contact.display_name, contact.contact_type, contact.birth_date, address.street_address, address.postal_code, address.city, phone.phone, email.email $from\n$where\nGROUP BY contact.id\n$order";
        }else{
          // do nothing if no query
          $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'Please Enter a query.');
          echo json_encode($returnCode);
          mysql_close(self::$db);
          CRM_Utils_System::civiExit();
        }
        if ($debug){
          echo "<h1>Query</h1><pre>";
          print_r($query);
          echo "</pre><h1>Results <small>(".count($results).")</small></h1><pre>";
          print_r($results);
        }

        $result = mysql_query($query, self::db());
        $results = array();
        while($row = mysql_fetch_assoc($result)) {
            $results[] = $row;
        }
        if(count($results) > 0){
          $returnCode = $results;
        }else{
          $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'No Records Found');
        }

        echo json_encode($returnCode);
        $end = microtime(true);
        if(self::get('debug')) echo $end-$start;
        mysql_close(self::$db);
        CRM_Utils_System::civiExit();
    }

    /* assignMessage()
     * Parameters: None.
     * Returns: None.
     * Takes message information and saves it as an activity and assigns it to
     * the selected contact ID.
     */ 
    public static function assignMessage() {
        // testing url 
        // http://skelos/civicrm/imap/ajax/assignMessage?messageId=123&contactId=123&imapId=1
        
        self::setupImap();
        $debug = self::get('debug');

        $messageUid = self::get('messageId');
        $contactIds = self::get('contactId');
        $imapId = self::get('imapId');
        $imap = new CRM_Utils_IMAP(self::$server, self::$imap_accounts[$imapId]['user'], self::$imap_accounts[$imapId]['pass']);
        $email = $imap->getmsg_uid($messageUid);

        $output = self::unifiedMessageInfo($email);

        // probably could user better names 
        $senderName = ($output['header']['from_name']) ?  $output['header']['from_name'] : '' ;
        $senderEmailAddress = ($output['header']['from_email']) ?  $output['header']['from_email'] : '' ;
        $date = ($output['header']['date_clean']) ?  $output['header']['date_clean'] : 'could not find message date' ;
        $subject = ($output['forwarded']['subject']) ?  $output['forwarded']['subject'] : 'could not find message subject' ;
        $body = ($output['header']['body']) ?  $output['header']['body'] : 'could not find message body' ;
        
        if ($debug){
          echo "<h1>inputs</h1>";
          var_dump($senderName);
          var_dump($senderEmailAddress);
          var_dump($date);
          var_dump($subject);
          var_dump($body);
          var_dump($messageUid);
        }

        require_once 'api/api.php';

        // if this email has been moved / assigned already 
        if( $email->sender[0] == null){
          $returnCode = array('code'      =>  'ERROR',
              'message'   => 'This email no longer exists');
            echo json_encode($returnCode);
            CRM_Utils_System::civiExit();
        }

        // Get the user information for the person who forwarded the email, or bluebird admin
        $params = array( 
            'email' => $senderEmailAddress,
            'version' => 3,
        );
        $result = civicrm_api('contact', 'get', $params );

        if ($debug){
          echo "<h1>Get forwarder Contact Record</h1>";
          if (count($result['values']) != 1 ) echo "<p>If there are no results, or multiple contacts we make bluebird admin the owner</p>";
          var_dump($result);

        }

        // error checking for forwarderId
        if (($result['is_error']==1) || ($result['values']==null ) || (count($result['values']) !=  1 )){
          $forwarderId = 1; // bluebird admin
        } else{
          $forwarderId = $result['id'];
        };

        if ($debug){
          echo "<h1>forwarder ID</h1>";
          var_dump($forwarderId);
        }
        $fromEmail = $output['forwarded']['origin_email'];

        if ($debug){
          echo "<h1>Attach activity to</h1>";
          var_dump($fromEmail);
        }

        $contactIds = explode(',', $contactIds);
        foreach($contactIds as $contactId) {

            // Check to see if contact has the email address being assigend to it,
            // if doesn't have email address, add it to contact
            $query = "SELECT email.email FROM civicrm_email email WHERE email.contact_id = $contactId";
            $result = mysql_query($query, self::db());
            $results = array();
            while($row = mysql_fetch_assoc($result)) {
                $results[] = $row;
            }

            if ($debug){
                echo "<h1>Contact ".$contactId." has the following emails </h1>";
                var_dump($results);
            }
            $emailsCount = count($results);

            $matches = 0;
            if ($debug){
              echo "<h1>Contact Non matching results </h1>";
              // if the records don't match, count it, an if the number is > 1 add the record
              foreach($results as $email) {
                  if($email['email'] == $fromEmail){
                      if ($debug) echo "<p>".$email['email'] ." == ".$fromEmail."</p>";
                  }else{
                      $matches++;
                      if ($debug) echo "<p>".$email['email'] ." != ".$fromEmail."</p>";
                  }
              }
            }
            
            // Prams to add email to user
            $params = array(
                'contact_id' => $contactId,
                'email' => $fromEmail,
                'version' => 3,
            );
            if(($emailsCount-$matches) == 0){
                if ($debug) echo "<p> added ".$fromEmail."</p><hr/>";
                $result = civicrm_api( 'email','create',$params );
            }
 


          // Submit the activity information and assign it to the right user
          $params = array(
              'activity_type_id' => 12,
              'source_contact_id' => $forwarderId,
              'assignee_contact_id' => $forwarderId,
              'target_contact_id' => $contactId,
              'subject' => $subject,
              'is_auto' => false, // we manually add it, right ?
              'status_id' => 2,
              'original_id' => $messageUid,
              'details' => $body,
              'version' => 3
          );
          $activity = civicrm_api('activity', 'create', $params);

          // if its an error or doesnt return we need errors 
          if (($activity['is_error']==1)){
            $returnCode = array('code'      =>  'ERROR',
              'message'   =>  $activity['error_message']);
            echo json_encode($returnCode);
            CRM_Utils_System::civiExit();
          } else{
            // Now we need to assign the tag to the activity.
            self::assignTag($activity['id'], 0, self::getInboxPollingTagId());
            if ($debug){
              echo "<h1>Message not archived in debug mode, feel free to try again</h1>";
            }else{
                $imap->movemsg_uid($messageUid, 'Archive');
            }

            // add attachment to activity
          };

        }
        // Move the message to the archive folder!
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



    public static function assignTag($inActivityIds = null, $inContactIds = null, $inTagIds = null) {
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
            //get data about tag
            $data = self::tagRaw($tagId);
            $tagName = $data['values'][$tagId]['name'];
            foreach($contactIds as $contactId) {
                 if($contactId == 0)
                    break;
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
                    $returnCode[$tagId.":".$contactId] = array('code' => 'ERROR','message'=>$result['error_message']." on {$name}");
                }elseif ($result['not_added']==1 ) {
                    $returnCode[$tagId.":".$contactId] = array('code' => 'ERROR','message'=>"Tag '{$tagName}' Already exists on {$name}");
                }else{
                    $returnCode[$tagId.":".$contactId] = array('code' =>'SUCCESS','message'=> "Tag '{$tagName}' Added to {$name}");
                }

            }
            foreach($activityIds as $activityId) {

                  if($activityId == 0)
                    break;
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
                        $returnCode[$tagId.":".$activityId] = array('code'=>'ERROR','message'=> "'$subject' on  '{$tagName}'");
                    }else{
                        $returnCode[$tagId.":".$activityId] = array('code'=>'SUCCESS','message'=>"Tag '{$tagName}' Added to {$subject}");
                    }
                }else{
                    $returnCode[$tagId.":".$activityId] = array('code'=>'ERROR','message'=> "'$subject' on  '{$tagName}'");
                }
             }
        }
        echo json_encode($returnCode);

        //the following causes exit before the loop in assignMessage can complete. commenting it allows multi-match
        //but without it the script returns a full page, a new addition in 1.4
        // CRM_Utils_System::civiExit();
    }

    public static function getMatchedMessages() {
        require_once 'CRM/Core/BAO/Tag.php';
        require_once 'CRM/Core/BAO/EntityTag.php';
        require_once 'CRM/Activity/BAO/ActivityTarget.php';

        // getEntitiesByTag  = get activities id's that are tagged with inbox polling tag
        $tag     = new CRM_Core_BAO_Tag();
        $tag->id = self::getInboxPollingTagId();
        $result = CRM_Core_BAO_EntityTag::getEntitiesByTag($tag);
        $debug = self::get('debug');

        foreach($result as $id) {
            // pull in full activity record 
            $params = array('version'   =>  3,
                            'activity'  =>  'get',
                           'id' => $id,
                            );
            $activity = civicrm_api('activity', 'get', $params);
            $activity_node = $activity['values'][$id];

            // get the user the activity is attached to
            $user_id = CRM_Activity_BAO_ActivityTarget::retrieveTargetIdsByActivityId($id);
            if($user_id){
                $params = array('version'   =>  3,
                            'activity' => 'get',
                            'id' => $user_id[0],
                        );
                $contact = civicrm_api('contact', 'get', $params);
                $contact_node = $contact['values'][$user_id[0]];
            }


            // find out who the forwarder is
            $params = array('version'   =>  3,
                            'id' => $activity_node['source_contact_id'],
            );
            $forwarder = civicrm_api('contact', 'get', $params );
            $forwarder_node = $forwarder['values'][$activity_node['source_contact_id']];

            $date =  date('m-d-y h:i A', strtotime($activity_node['activity_date_time'])); 
            // message to return 
            if ($debug){
              var_dump($activity_node);
            }
            $returnMessage[$id] = array('activitId'    =>  $id,
                            'contactId' =>  $contact_node['contact_id'],
                            'fromName'   =>  $contact_node['display_name'],
                            'contactType'   =>  $contact_node['contact_type'],
                            'firstName'   =>  $contact_node['first_name'],
                            'lastName'   =>  $contact_node['last_name'],
                            'fromEmail'  =>  $contact_node['email'],
                            'forwarderName' => $forwarder_node['display_name'],
                            'forwarder' => $forwarder_node['email'],
                            'activityId' => $activity_node['id'],
                            'subject'    =>  $activity_node['subject'],
                            'details'  =>  $activity_node['details'],
                            'match_type'  =>  $activity_node['is_auto'],
                            'original_id'  =>  $activity_node['original_id'],
                            'date'   =>  $date);
         }
         $returnMessage['count'] = count($returnMessage);
        echo json_encode($returnMessage);
        CRM_Utils_System::civiExit();
    }

    public static function getActivityDetails() {

        $activitId = self::get('id');
        $userId = self::get('contact');

        require_once 'CRM/Core/BAO/Tag.php';
        require_once 'CRM/Core/BAO/EntityTag.php';
        require_once 'CRM/Activity/BAO/ActivityTarget.php';

        //grab the imap user
        self::setupImap();

        $params = array('version'   =>  3,
                        'activity'  =>  'get',
                       'id' => $activitId,
        );
        $activity = civicrm_api('activity', 'get', $params);
        $activity_node = $activity['values'][$activitId];

        $params = array('version'   =>  3,
                    'activity' => 'get',
                    'id' => $userId,
                );
        $contact = civicrm_api('contact', 'get', $params);
        $contact_node = $contact['values'][$userId];


        $params = array('version'   =>  3,
                        'id' => $activity_node['source_contact_id'],
        );
        $forwarder = civicrm_api('contact', 'get', $params );
        $forwarder_node = $forwarder['values'][$activity_node['source_contact_id']];

        $date =  date('m-d-y h:i A', strtotime($activity_node['activity_date_time'])); 

        $returnMessage = array('uid'    =>  $activitId,
                            'fromName'   =>  $contact_node['display_name'],
                            'fromEmail'  =>  $contact_node['email'],
                            'fromId'  =>  $contact_node['id'],
                            'forwardedName' => $forwarder_node['display_name'],
                            'forwardedEmail' => $forwarder_node['email'],
                            'subject'    =>  $activity_node['subject'],
                            'details'  =>  $activity_node['details'],
                            'match_type'  =>  $activity_node['is_auto'],
                            'original_id'  =>  $activity_node['original_id'],
                            'email_user' => self::$imap_accounts[0]['user'], // not ideal for the hardcoded 0 
                            'date'   =>  $date);

        echo json_encode($returnMessage);
        CRM_Utils_System::civiExit();
    }
    
    // delete activit and enttity ref 
    public static function deleteActivity() {
        require_once 'api/api.php';
        $id = self::get('id');
        $tagid = self::getInboxPollingTagId();
        $error = false;

        // deleteing a activity
        $params = array( 
            'id' => $id,
            'activity_type_id' => 1,
            'version' => 3,
        );

        $deleteActivity = civicrm_api('activity','delete',$params );
        if($deleteActivity['is_error'] == 1){
          $error = true;
        }


        // deleteing a entity is hard via api without entity id, time to use sql 
        $query = <<<EOQ
DELETE FROM `civicrm_entity_tag`
WHERE `entity_id` =  $id
AND `tag_id` = $tagid
EOQ;
        $result = mysql_query($query, self::db());
        $results = array();
        while($row = mysql_fetch_assoc($result)) {
            $results[] = $row;
        }

        if(mysql_affected_rows() != 1){
          $error = true;
        }
 
 
        if(!$error){
          $returnCode = array('code'=>'SUCCESS','id'=>$id, 'message'=>'Activity Deleted');
        }else{
          $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'Activity not found');
        }
        echo json_encode($returnCode);

        mysql_close(self::$db);
        CRM_Utils_System::civiExit();
    }

    // remove the activity tag
    public static function unproccessedActivity() {
        require_once 'api/api.php';
        $id = self::get('id');
        $contact = self::get('contact');
        $tagid = self::getInboxPollingTagId();
        $error = false;

        // deleteing a entity is hard via api without entity id, time to use sql 
        $tagid = self::getInboxPollingTagId();
        $query = <<<EOQ
DELETE FROM `civicrm_entity_tag`
WHERE `entity_id` =  $id
AND `tag_id` = $tagid
EOQ;
        $result = mysql_query($query, self::db());
        $results = array();
        while($row = mysql_fetch_assoc($result)) {
            $results[] = $row;
        }

        if(mysql_affected_rows() != 1){
          $error = true;
        }
 
 
        if(!$error){
          $returnCode = array('code'=>'SUCCESS','id'=>$id, 'message'=>'Activity Cleared');
        }else{
          $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'Activity not found');
        }

        echo json_encode($returnCode);
        mysql_close(self::$db);

        CRM_Utils_System::civiExit();

    }

    // reAssignActivity 
    public static function reassignActivity() {
        require_once 'api/api.php';
        $id = self::get('id');
        $contact = self::get('contact');
        $change = self::get('change');
        $results = array();
        $changeData = self::contactRaw($change);
        $changeName = $changeData['values'][$change]['display_name'];
        $firstName = $changeData['values'][$change]['first_name'];
        $LastName = $changeData['values'][$change]['last_name'];
        $contactType = $changeData['values'][$change]['contact_type'];

        // want to update the activity_target, time to use sql 
        // get the the record id please 
        $tagid = self::getInboxPollingTagId();
        $query = <<<EOQ
SELECT id
FROM `civicrm_activity_target`
WHERE `activity_id` = $id
AND `target_contact_id` = $contact
EOQ;

        $activity_id = mysql_query($query, self::db());
        if($row = mysql_fetch_assoc($activity_id)) {
            // the activity id
            $row_id = $row['id']; 
            // change the contact
            $Update = <<<EOQ
UPDATE `civicrm_activity_target`
SET  `target_contact_id`= $change
WHERE `id` =  $row_id
EOQ;

            // change the row           
            $Updated_results = mysql_query($Update, self::db());
            while($row = mysql_fetch_assoc($Updated_results)) {
                 $results[] = $row; 
            }
            $returnCode = array('code'=>'SUCCESS','id'=>$id,'contact_id'=>$change,'contact_type'=>$contactType,'first_name'=>$firstName,'last_name'=>$LastName,'display_name'=>$changeName,'activity_id'=>$row_id,'message'=>'Activity Reassigned to '.$changeName);
        }else{
            $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'Activity not found');

        }

        echo json_encode($returnCode);
        mysql_close(self::$db);
        CRM_Utils_System::civiExit();
    }

    public static function getTags() {
        require_once 'api/api.php';
        $name = self::get('s');
        $i = 0;
        $results = array();

        $query = <<<EOQ
SELECT id, name
FROM `civicrm_tag`
WHERE `parent_id` ='296' && `name` LIKE '$name%'
EOQ;
        $result = mysql_query($query, self::db());
        while($row = mysql_fetch_assoc($result)) {
            array_push( $results,  array("label"=>$row['name'], "value"=>$row['id']));
            $i++;
        }
        $final_results = array('items'=> $results);
        echo json_encode($final_results);
        mysql_close(self::$db);
        CRM_Utils_System::civiExit();
    }




    public static function addTags() {
        require_once 'api/api.php';
        $tag_ids = self::get('tags');
        $activityId = self::get('activityId');
        $contactId = self::get('contactId');
        self::assignTag($activityId, $contactId, $tag_ids);
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
    public static function createNewContact() {
        require_once 'api/api.php';

        $debug = self::get('debug');
        // testing url 
        //http://skelos/civicrm/imap/ajax/createNewContact?first_name=dan&last_name=pozzi&email=dpozzie@gmail.com&street_address=26%20Riverwalk%20Way&city=Cohoes&debug=true
        // http://skelos/civicrm/imap/ajax/createNewContact?messageId=52&imap_id=0&first_name=Fakie&last_name=McTesterson&email_address=Test%40aol.com&phone=5185185555&street_address=1241+fake+street&street_address_2=floor+2&postal_code=12202&city=albany&debug=true

        $first_name = (strtolower(self::get('first_name')) == 'first name' || trim(self::get('first_name')) =='') ? '' : self::get('first_name');
        $last_name = (strtolower(self::get('last_name')) == 'last name'|| trim(self::get('last_name')) =='') ? '' : self::get('last_name');
        $email  = (strtolower(self::get('email_address')) == 'email address')|| trim(self::get('email_address')) =='' ? '' : self::get('email_address');
        $phone = (strtolower(self::get('phone')) == 'phone number'|| trim(self::get('phone')) =='') ? '' : self::get('phone');
        $street_address = (strtolower(self::get('street_address')) == 'street address'|| trim(self::get('street_address')) =='') ? '' : self::get('street_address');
        $street_address_2 = (strtolower(self::get('street_address_2')) == 'street address'|| trim(self::get('street_address_2')) =='') ? '' : self::get('street_address_2');
        $postal_code = (strtolower(self::get('postal_code')) == 'zip code'|| trim(self::get('postal_code')) =='') ? '' : self::get('postal_code');
        $city = (strtolower(self::get('city')) == 'city'|| trim(self::get('city')) =='') ? '' : self::get('city');

        if ($debug){
          echo "<h1>inputs</h1>";
          var_dump($first_name);
          var_dump($last_name);
          var_dump($email);
          var_dump($phone);
          var_dump($street_address);
          var_dump($street_address_2);
          var_dump($postal_code);
          var_dump($city);
        }
  
        if((!$first_name)|| (!$last_name) || (!$email))
        {
            $returnCode = array('code'      =>  'ERROR',
                                'status'    =>  '1',
                                'message'   =>  'Required: First Name, Last Name, and Email');
            echo json_encode($returnCode);
            CRM_Utils_System::civiExit();
        }



        //First, you make the contact
        $params = array(
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'contact_type' => 'Individual',
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
        

        if($street_address && $contact['id']){
          //And then you attach the contact to the Address! which is at $contact['id']
          $address_params = array(
              'contact_id' => $contact['id'],
              'street_address' => $street_address,
              'supplemental_address_1' => $street_address_2,
              'city' => $city,
              'postal_code' => $postal_code,
              'is_primary' => 1,
              'state_province_id' => 1031,
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

          CRM_Utils_System::civiExit();
        } else {
          $returnCode = array('code'      =>  'SUCCESS',
                                'status'    =>  '0',
                                'contact' => $contact['id']
                                );
          echo json_encode($returnCode);
          CRM_Utils_System::civiExit();
        }
    }

}