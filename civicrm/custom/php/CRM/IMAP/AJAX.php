<?php
require_once 'CRM/Core/Error.php';
require_once 'CRM/Utils/IMAP.php';
require_once 'CRM/Core/DAO.php';

class CRM_IMAP_AJAX {
    private static $db = null;

    private static $server = "{webmail.senate.state.ny.us/imap/notls}";
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
     * Parameters: email.
     * Returns: An Object message details to map to the output.
     * This function grabs a single messages and cleans it for output.
     */   
    public static function unifiedMessageInfo($email) {

        $details = ($email->plainmsg) ? $email->plainmsg : $email->htmlmsg;
        $format = ($email->plainmsg) ? "plain" : "html";
        if($format =='plain'){
            $tempDetails = preg_replace("/(=|\r\n|\r|\n)/i", "", $details);
            $tempDetails = preg_replace("/>>/i", "", $details);
            $body = preg_replace("/(=|\r\n|\r|\n)/i", "<br>\n", $details);
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
        preg_match("/(Subject:|subject:)\s*([^\r\n]*)/i", $tempDetails, $subjects);
        preg_match("/(From:|from:)\s*([^\r\n]*)/i", $tempDetails, $froms);

        $fromEmail = self::extract_email_address($froms['2']); // removes the email from the name <email> combo

        // check ot see if forwarded 
        $status = (!$froms['2']) ? 'direct' : 'forwarded';

        $header = array(
            'format' => $format,
            'from' => $email->sender[0]->personal.' '.$email->sender[0]->mailbox . '@' . $email->sender[0]->host,
            'from_name' => $email->sender[0]->personal,                      
            'from_email' => $email->sender[0]->mailbox.'@'.$email->sender[0]->host,                      
            'subject' => $email->subject,
            'body' => $body,                      
            'date_clean' => self::cleanDate($email->date),
            'status' => $status,
        );

        // this gets a bit inelegant
        // if the origin details are empty, we have a direct message and populate accordingly 
        $origin = (!$froms['2']) ?  $header['from'] : $froms['2'];
        $origin_name = (!$fromEmail['name']) ?  $header['from_name'] : $fromEmail['name'];
        $origin_email = (!$fromEmail['email']) ?  $header['from_email'] : $fromEmail['email'];

        $forwarded = array(
            'date_clean' => self::cleanDate($tempDetails), 
            'subject' => preg_replace("/(Fwd:|fwd:|Fw:|fw:|Re:|re:) /i", "", $subjects['2']), 
            'origin' => $origin,
            'origin_name' => $origin_name, 
            'origin_email' => $origin_email, 
        );

        $output = array('header'=>$header,'forwarded'=>$forwarded,'attachments'=>$attachmentArray);
        return $output;
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

                    $returnMessage[$header->uid] =  array( 
                        'subject' =>  $output['forwarded']['subject'],
                        'from' =>  $output['forwarded']['origin_name'].' <'.$output['forwarded']['origin_email'].'>',
                        'uid' =>  $header->uid,
                        'date' =>  $output['header']['date_clean'],
                        'format' =>  $output['header']['format'],
                        'from_email' =>  $output['forwarded']['origin_email'],
                        'from_name' =>  $output['forwarded']['origin_name'],
                        'forwarder_email' =>  $output['header']['from_email'],
                        'forwarder_name' =>  $output['header']['from_name'],
                        'forwarder_time' =>  $output['forwarded']['date_clean'],
                        'attachmentfilename'  => $output['attachments'][0]['name'],
                        'attachmentname'  =>  $output['attachments'][0]['name'],
                        'attachment'  => $output['attachments']['overview']['total'],
                        'status' =>$output['header']['status'],
                        'imap_id' =>  $imap_id
                        );
                 }
            }
        }       

        
        // Encode the messages variable and return it to the AJAX call
        echo json_encode($returnMessage);
        CRM_Utils_System::civiExit();
    }

    public function extract_email_address ($string) {
        // some nysenate fixes
        $string = preg_replace('/\/STS\/senate/i', ' internal@nysenate.gov', $string);
        $string = preg_replace('/\/CENTER\/senate/i', ' internal@nysenate.gov', $string);
        $string = preg_replace('/\/senate@senate/i', ' internal@nysenate.gov', $string);
        $string = preg_replace('/&lt;/i', '', $string);
        $string = preg_replace('/&gt;/i', '', $string);
        $string = preg_replace('/</i', '', $string);
        $string = preg_replace('/>/i', '', $string);
        $string = preg_replace('/"/i', '', $string);

        foreach(preg_split('/ /', $string) as $token) {
            $email = filter_var(filter_var($token, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);
            if ($email !== false) {
                $emails[] = $email;
            }
        }

        $name = str_replace($emails[0], '', $string);
        $return = array('name'=>$name,'email'=>$emails[0]);
        return $return;
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
    
        $returnMessage = array('uid'    =>  $id,
                               'imapId' =>  $imap_id,
                               'format' => $output['header']['format'],
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
                               'date'   =>  $output['header']['date_clean']);
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
        $date_string_short = date("m-d-y h:i A", strtotime($date_string_short));
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
        $from = "FROM civicrm_contact as contact\n";
        $where = "WHERE contact.is_deleted=0\n";
        $order = "ORDER BY contact.id ASC";

        $first_name = (strtolower(self::get('first_name')) == 'first name') ? '' : self::get('first_name');
        if($first_name) $where .="  AND (contact.first_name LIKE '$first_name' OR contact.organization_name LIKE '$first_name')\n";

        $last_name = (strtolower(self::get('last_name')) == 'last name') ? '' : self::get('last_name');
        if($last_name) $where .="  AND (contact.last_name LIKE '$last_name' OR contact.household_name LIKE '%$last_name%' )\n";

        $email_address  = (strtolower(self::get('email_address')) == 'email address') ? '' : self::get('email_address');
        if($email_address) {
          $from.="  JOIN civicrm_email as email ON email.contact_id=contact.id\n";
          $where.="  AND email.email LIKE '$email_address'\n";
          $order.=", email.is_primary DESC";
        }

        $dob  = (self::get('dob') == 'yyyy-mm-dd') ? '' : self::get('dob');
        if($dob) $where.="  AND contact.birth_date = '$dob'\n";

        $state_id = self::get('state');
        $street_address = (strtolower(self::get('street_address')) == 'street address') ? '' : self::get('street_address');
        $city = (strtolower(self::get('city')) == 'city') ? '' : self::get('city');

        $from.="  LEFT JOIN civicrm_address as address ON address.contact_id=contact.id\n";

        if($street_address || $city){ // state id is hard coded
          $order.=", address.is_primary DESC";
          if($street_address) {
            $where.="  AND address.street_address LIKE '$street_address'\n";
          }
          if ($city) {
            $where.="  AND address.city LIKE '$city'\n";
          }
          if ($state_id) {
            $from.="  JOIN civicrm_state_province AS state ON address.state_province_id=state.id\n";
            $where.="  AND state.id='$state_id'\n";
          }
        }
        
        $from.="LEFT JOIN civicrm_phone as phone ON phone.contact_id=contact.id\n";
        $phone = (strtolower(self::get('phone')) == 'phone number') ? '' : self::get('phone');
        if ($phone) {
          $where.="  AND phone.phone LIKE '%$phone%'";
        }
        $query = "SELECT * $from\n$where\nGROUP BY contact.id\n$order";
        $result = mysql_query($query, self::db());
        $results = array();
        while($row = mysql_fetch_assoc($result)) {
            $results[] = $row;
        }
        echo json_encode(array_values($results));
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

        $messageUid = self::get('messageId');
        $contactIds = self::get('contactId');
        $imapId = self::get('imapId');
        $imap = new CRM_Utils_IMAP(self::$server, self::$imap_accounts[$imapId]['user'], self::$imap_accounts[$imapId]['pass']);
        $email = $imap->getmsg_uid($messageUid);

        $output = self::unifiedMessageInfo($email);

        // probably could user better names 
        $senderName = $output['header']['from_name'];
        $senderEmailAddress = $output['header']['from_email'];
        $date = $output['header']['date_clean'];
        $subject = $output['forwarded']['subject'];
        $body = $output['header']['body'];
        
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

        // error checking for forwarderId
        if (($result['is_error']==1) || ($result['values']==null )){
          $forwarderId = 1; // bluebird admin
        } else{
          $forwarderId = $result['id'];
        };


        $fromEmail = $output['forwarded']['origin_email'];

        $contactIds = explode(',', $contactIds);
        foreach($contactIds as $contactId) {

          // On match add email to user 
           $params = array( 
            'contact_id' => $contactId,
            'email' => $fromEmail,
            'version' => 3,
          );
          $result = civicrm_api( 'email','create',$params );


          // Submit the activity information and assign it to the right user
          $params = array(
              'activity_type_id' => 12,
              'source_contact_id' => $forwarderId,
              'assignee_contact_id' => $forwarderId,
              'target_contact_id' => $contactId,
              'subject' => $subject,
              'status_id' => 2,
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
            $imap->movemsg_uid($messageUid, 'Archive');
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
                            'date'   =>  $date);
         }
        echo json_encode($returnMessage);
        CRM_Utils_System::civiExit();
    }

    public static function getActivityDetails() {

        $activitId = self::get('id');
        $userId = self::get('contact');

        require_once 'CRM/Core/BAO/Tag.php';
        require_once 'CRM/Core/BAO/EntityTag.php';
        require_once 'CRM/Activity/BAO/ActivityTarget.php';

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
                            'forwardedName' => $forwarder_node['display_name'],
                            'forwardedEmail' => $forwarder_node['email'],
                            'subject'    =>  $activity_node['subject'],
                            'details'  =>  $activity_node['details'],
                            'date'   =>  $date);

        echo json_encode($returnMessage);
        CRM_Utils_System::civiExit();
    }
    
    // delete activit and enttity ref 
    public static function deleteActivity() {
        require_once 'api/api.php';
        $id = self::get('id');
        
        // deleteing a activity
        $params = array( 
            'id' => $id,
            'activity_type_id' => 1,
            'version' => 3,
        );
        $result = civicrm_api( 'activity','delete',$params );

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
        echo json_encode($result);
        mysql_close(self::$db);
        CRM_Utils_System::civiExit();
    }

    // remove the activity tag
    public static function unproccessedActivity() {
        require_once 'api/api.php';
        $id = self::get('id');
        $contact = self::get('contact');
        $tagid = self::getInboxPollingTagId();

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
        echo json_encode($result);
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
WHERE `parent_id` ='296' && `name` LIKE '%$name%'
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
        // testing url 
        //http://skelos/civicrm/imap/ajax/createNewContact?first_name=dan&last_name=pozzi&email=dpozzie@gmail.com&street_address=26%20Riverwalk%20Way&city=Cohoes
        $first_name = self::get("first_name");
        $last_name = self::get("last_name");
        $email = self::get("email_address");
        $phone = self::get("phone");
        $street_address = self::get("street_address");
        $street_address_2 = self::get("street_address_2");
        $postal_code = self::get("postal_code");
        $city = self::get("city");
 
        if(!($first_name) && !($last_name) && !($email))
        {
            $returnCode = array('code'      =>  'ERROR',
                                'status'    =>  '1',
                                'message'   =>  'Required: First Name, Last Name, and Email');
            echo json_encode($returnCode);
            CRM_Utils_System::civiExit();
        }

        require_once 'api/api.php';
        require_once 'CRM/Core/BAO/Address.php';

        //First, you make the contact
        $params = array(
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'contact_type' => 'Individual',
            'version' => 3,
        );

        $contact = civicrm_api('contact','create', $params);


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
        


        if(($contact['is_error'] == 1) || (!empty($address) && ($address['is_error'] == 1))){
          $returnCode = array('code'      =>  'ERROR',
                                'status'    =>  '1',
                                'message'   =>  'Error adding Contact or Address Details'
                                );
          // echo "contact\n";
          // var_dump($contact);
          // echo "address\n";
          // var_dump($address);
          // echo json_encode($returnCode);
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