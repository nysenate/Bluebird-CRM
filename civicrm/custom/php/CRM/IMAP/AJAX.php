<?php
// AJAX.php
// Project: BluebirdCRM
// Author: Stefan Crain
// Organization: New York State Senate
// Revised: 2013-12-16

require_once 'CRM/Core/Error.php';
require_once 'CRM/Utils/IMAP.php';
require_once 'CRM/Core/DAO.php';

class CRM_IMAP_AJAX {
    private static $db = null;
    private static $server = "{webmail.senate.state.ny.us/imap/ssl/notls}";
    private static $imap_accounts = array();
    private static $bbconfig = null;

    /**
     * This function loads the Bluebird config then parses out the
     * listed IMAP accounts and stores the data in an array
     * so they can be looped through later.
     * @return None
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

    /**
     * Occasionally we'll need the raw database connection to do
     * some processing, this will get the database connection from
     * CiviCRM and set it to a static variable.
     * @return None
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

    /**
     * We want to be able to escape the string so when we use
     * the key in a query, it's already sanitized.
     * @param  [string] $key  The name of the input in the GET message.
     * @return [string]       The escaped string.
     */
    private static function get($key) {
        // Call mysql_real_escape_string using the db() connection object
        return mysql_real_escape_string($_GET[$key], self::db());
    }

    /**
     * Format message details from DB
     * @param  [int] $messageId The ID of the message you are looking for
     * @return [object]         A
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
      while($row_raw = mysql_fetch_assoc($UnprocessedResult)) {
        foreach ($row_raw as $key => $value) {
          switch ($key) {
            case 'body':
              $output = preg_replace('/[^a-zA-Z0-9\s\p{P}<>+]/', '', trim($value));
              $row[$key] = htmlspecialchars_decode(stripslashes($output));
              break;

            default:
              $output = str_replace( chr( 194 ) . chr( 160 ), ' ', $value);
              $output = htmlspecialchars_decode(stripslashes($output));
              $output = preg_replace('/[^a-zA-Z0-9\s\p{P}]/', '', trim($output));
              $row[$key] = substr($output,0,240);
              break;
          }

        }
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
          echo "<pre>";
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

    /**
     * Calculate bytes to different format
     * @param  [int] $bytes What you would like to convert
     * @return [string]      Formatted B=>KB=>MB=>GB=>TB
     */
    public static function decodeSize($bytes){
      $types = array( 'B', 'KB', 'MB', 'GB', 'TB' );
      for( $i = 0; $bytes >= 1024 && $i < ( count( $types ) -1 ); $bytes /= 1024, $i++ );
      return( round( $bytes, 2 ) . " " . $types[$i] );
    }

    /**
     * This function grabs all of the messages previously processed by the
     * process mailbox polling script and formats a JSON object.
     * For Unmatched screen Overview
     * @return [JSON Object]    Messages that have have not been matched
     */
    public static function UnmatchedList() {
      $debug = self::get('debug');
      $range = self::get('range');
      $rangeVal = (is_numeric($range) && $range >= 1) ? "AND (updated_date BETWEEN '".date('Y-m-d H:i:s', strtotime('-'.$range.' days'))."' AND '".date('Y-m-d H:i:s')."') " : '';
      $start = microtime(true);

      $UnprocessedQuery = "SELECT t1.id, UNIX_TIMESTAMP(t1.updated_date) as date_u, DATE_FORMAT(t1.updated_date, '%b %e, %Y %h:%i %p') as date_long,
        CASE
          WHEN DATE_FORMAT(updated_date, '%j') = DATE_FORMAT(NOW(), '%j') THEN DATE_FORMAT(updated_date, 'Today %l:%i %p')
          ELSE CASE
            WHEN DATE_FORMAT(updated_date, '%Y') = DATE_FORMAT(NOW(), '%Y') THEN DATE_FORMAT(updated_date, '%b %e, %l:%i %p')
            ELSE  DATE_FORMAT(updated_date, '%b %e, %Y')
          END
        END AS date_short,
        t1.matched_to, t1.sender_email, t1.subject, t1.forwarder, t1.activity_id, t1.sender_name, count(t2.id) AS email_count,
       IFNULL( count(t3.file_name), '0') as attachments
      FROM `nyss_inbox_messages` AS t1
      LEFT JOIN civicrm_email as t2 ON  t1.sender_email = t2.email
      LEFT JOIN nyss_inbox_attachments AS t3 ON ( t1.id = t3.email_id )
      WHERE t1.status = 0 {$rangeVal}
      GROUP BY t1.id";

      $UnprocessedResult = mysql_query($UnprocessedQuery, self::db());
      $UnprocessedOutput = array();
      while($row = mysql_fetch_assoc($UnprocessedResult)) {
        $id = $row['id'];
        foreach ($row as $key => $value) {
          $output = str_replace( chr( 194 ) . chr( 160 ), ' ', $value);
          $output = preg_replace('/[^a-zA-Z0-9\s\p{P}<>]/', '', trim($output));
          $returnMessage['Unprocessed'][$id][$key] = $output;
        }
      }
      mysql_close(self::$db);
      $returnMessage['stats']['overview']['successes'] = count($returnMessage['Unprocessed']);
      $end = microtime(true);
      $returnMessage['stats']['overview']['time'] = $end-$start;

       // Encode the messages variable and return it to the AJAX call
      if ($debug) echo "<pre>";
      echo (!$debug) ?  json_encode($returnMessage) : print_r($returnMessage);
      if ($debug) echo "</pre>";

      CRM_Utils_System::civiExit();
    }

    /**
     * This function grabs the unassigned message from the db,
     * returns an error if the message is no longer unassigned
     * @return  [JSON Object]    Messages that have have not been matched
     */

    # http://codefool.tumblr.com/post/15288874550/list-of-valid-and-invalid-email-addresses

  // UPDATE `nyss_inbox_messages` SET body = 'should work<br/>+1 (518) 555-0138 x23<br/>(518) 555-0138 x23<br/>518-555-0138 x23<br/>518.555.0138 x23<br/>518 555 0138 x23<br/>555 0138 x23<br/>555-0138 x23<br/><br/>+1(518)-555-0138<br/>+1-518-555-0138<br/>+1 518-555-0138<br/>+1 518 555-0138<br/>+1.518.555.0138<br/>+1 518.555.0138<br/>+1 518 555.0138<br/>+1 518 555 0138<br/>+1 5185550138<br/>+15185550138<br/><br/>(518)-555-0138<br/>(518).555.0138<br/>(518)-555.0138<br/>(518) 555-0138<br/>(518) 555.0138<br/>(518) 5550138<br/>(518)5550138<br/>(5185550138<br/><br/>518-555-0138<br/>518 555-0138<br/>518.555.0138<br/>518 555.0138<br/>518.555-0138<br/>518 555 0138<br/>518 5550138<br/>518-5550138<br/>5185550138<br/><br/>555-0138<br/>555.0138<br/>555 0138<br/>5550138<br/><br/>should fail<br/>2001-2003<br/>1111042 Wallaby Way, Sydney<br/> email@example.com<br/>firstname.lastname@example.com<br/>email@subdomain.example.com<br/>firstname+lastname@example.com<br/>email@123.123.123.123<br/>email@[123.123.123.123]<br/>"email"@example.com<br/>1234567890@example.com<br/>email@example-one.com<br/>_______@example.com<br/>email@example.name<br/>email@example.museum<br/>email@example.co.jp<br/>firstname-lastname@example.com<br/>much.”more\ unusual”@example.com<br/>very.unusual.”@”.unusual.com@example.com<br/>very.”(),:;<>[]”.VERY.”very@\\ "very”.unusual@strange.example.com<br/>plainaddress<br/>#@%^%#$@#$@#.com<br/>@example.com<br/>Joe Smith <email@example.com><br/>email.example.com<br/>email@example@example.com<br/>.email@example.com<br/>email.@example.com<br/>email..email@example.com<br/>あいうえお@example.com<br/>email@example.com (Joe Smith)<br/>email@example<br/>email@-example.com<br/>email@example.web<br/>email@111.222.333.44444<br/>email@example..com<br/>Abc..123@example.com<br/> This part of the expression validates the ‘username’ section of the email address. The hat sign (^) at the beginning of the expression represents the start of the string. If we didn’t include this, then someone could key in anything they wanted before the email address and it would still validate. <br/><br/>Contained in the square brackets are the characters we want to allow in this part of the address. Here, we are allowing the letters a-z, A-Z, the numbers 0-9, and the symbols underscore (_), period (.), and dash (-). As you’ve probably noticed, I’ve included letters both in capitals and lower case. In this instance, this isn’t strictly necessary, as we’re using the eregi (case insensitive) function. But I’ve included them here for completeness, and to show you how the functions work. The order of the character pairs within the brackets doesn’t matter.<br/><br/>The plus (+) sign after the square brackets indicates ‘one or more of the contents of the previous brackets’. So, in this case, we require one or more of any of the characters in the square brackets to be included in the address in order for it to validate. Finally, there is the ‘@‘ sign, which means that we require the presence of one @ sign immediately following the username. Dr. Steven V.R. Crain and<br/>Dr. Joe Sam Smith and Dr. Joe S. Smith and Joe S. Smith and Joe Smith and<br/>Joe-bob O\'Smith and Joe-bob Smith and aa a Mary-Ann I Stupid and<br/>Lisa E. Booth Crain and.Janna H. Belser-Ehrlich and Bjorn O\'Malleydd and<br/>Bin Lindd and Linda Jonesdd and Jason H. Priemdd <br/>Bjorn O\'Malley-Munozdd and Bjorn C. O\'Malleydd and Bjorn "Bill" O\'Malleydd and Bjorn ("Bill") O\'Malleydd and<br/>Bjorn ("Wild Bill") O\'Malleydd and Bjorn (Bill) O\'Malleydd and Bjorn \'Bill\' O\'Malleydd and Bjorn C O\'Malleydd and Bjorn C. R. O\'Malleydd and Bjorn Charles O\'Malleydd and Bjorn Charles R. O\'Malleydd and Bjorn van O\'Malleydd and Bjorn Charles van der O\'Malleydd and Bjorn Charles O\'Malley y Muñozdd and Bjorn O\'Malley, Jr.dd and<br/>Bjorn O\'Malley Jrdd and B O\'Malleydd and William Carlos Williamsdd and<br/>C. Bjorn Roger O\'Malley and B. C. O\'Malleydd and B C O\'Malleydd and B.J. Thomasdd and O\'Malley, Bjorndd and O\'Malley, Bjorn Jrdd and O\'Malley, C. Bjorn dd and O\'Malley, C. Bjorn III dd and O\'Malley y Muñoz, C. Bjorn Roger III and<br/>Doe, John. A. Kenneth III andVelasquez y Garcia, Dr. Juan, Jr. and Dr. Juan Q. Xavier de la Vega, Jr. an Smith and Smith Contractors and 12901,<br/> 5285 KEYES DR  KALAMAZOO MI 49004 2613 .. <br/> PO BOX 35  COLFAX LA 71417 35 .. <br/> 64938 MAGNOLIA LN APT B PINEVILLE LA 71360-9781 486 S SOANGETAHA RD APT 9 GALESBURG IL .. <br/> 450 N CHERRY ST GALESBURG IL 61401.. <br/> 950 REDWOOD SHORES PKWY UNIT K102 REDWOOD CITY CA.. <br/>  123 MAIN ST.. <br/> 123 MAIN ST SAN FRANCISCO.. <br/> MAIN ST & KELLOGG ST.. <br/>  EMBARCADERO ST & MARKET ST SAN FRANCISCO.. <br/> ' where id = 406;



    public static function UnmatchedDetails() {
      $messageId = self::get('id');
      $output = self::unifiedMessageInfo($messageId);
      $status = $output['status'];
      $debug = self::get('debug');

      if($status != ''){
        switch ($status) {
          case '0':
            $time_start = microtime(true);
            $patterns = array('/\r\n|\r|\n/i', '/\<p(\s*)?\/?\>/i', '/\<br(\s*)?\/?\>/i', '/<div[^>]*>/','/<\/div>/' ,'/\//');
            $search = preg_replace('/&lt;|&gt;|&quot;|&amp;/i', '###',   $output['body']);
            $search = preg_replace($patterns, "\n ", $search);

            // Find Possible Email Addresses
            foreach(preg_split('/[, ;]/', $search) as $token) {
              $email = filter_var(filter_var($token, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);
              if ($email !== false) {
                  $emails[] = $email;
              }
            }
            $output['found_emails'] = array_unique($emails, SORT_REGULAR);

            // Find Possible city/states from zipcode with usps-ams service
            // docs here : http://geo.nysenate.gov:8080/usps-ams/docs/
            preg_match_all('/(?<=[\s])\d{5}(-\d{4})?\b/', $search, $zipcodes);
            $url = 'http://geo.nysenate.gov:8080/usps-ams/api/citystate?batch=true';
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($zipcodes[0]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen(json_encode($zipcodes[0]))
            ));
            $addresses = json_decode(curl_exec($ch));
            // turn object into array, the easy way
            $addresses = json_decode(json_encode($addresses), true);
            $output['found_addresses'] = array_unique($addresses['results'], SORT_REGULAR);

            // Find Possible Phone numbers
            preg_match_all('/([\(]{1}[0-9]{3}[\)]{1}[\.| |\-]{0,1}|^[0-9]{3}[\.|\-| ]?)?[0-9]{3}(\.|\-| )?[0-9]{4}/s',$search,$phonecheck1);
            preg_match_all('/(?:\([2-9]\d{2}\)\ ?|(?:[2-9]\d{2}\-))[2-9]\d{2}\-\d{4}/s',$search,$phonecheck2);
            preg_match_all('/1?[-\. ]?(\(\d{3}\)?[-\. ]?|\d{3}?[-\. ]?)?\d{3}?[-\. ]?\d{4}/s',$search,$phonecheck3);

            // preg_match_all('/((((\(\d{3}\))|(\d{3}-))\d{3}-\d{4})|(\+?\d{2}((-| )\d{1,8}){1,5}))(( x| ext)\d{1,5}){0,1}/s',$search,$phonecheck4);
            preg_match_all('/(1?)(-| ?)(\()?([0-9]{3})(\)|-| |\)-|\) )?([0-9]{3})(-| )?([0-9]{4}|[0-9]{4})/s',$search,$phonecheck5);
            preg_match_all('/(1?(?: |\-|\.)?(?:\(\d{3}\)|\d{3})(?: |\-|\.)?\d{3}(?: |\-|\.)?\d{4})/s',$search,$phonecheck6);

            $phonenumbers = array_merge($phonecheck1[0], $phonecheck2[0], $phonecheck3[0], $phonecheck5[0], $phonecheck6[0]);
            $phonenumbers = array_filter(array_map('trim', $phonenumbers));
            $output['found_phones'] = array_unique($phonenumbers, SORT_REGULAR);
            // Find Possible Names
            preg_match_all( "/(?<FirstName>[A-Z]\.?\w*\-?[A-Z]?\w*)\s?(?<MiddleName>[A-Z]\w+|[A-Z]?\.?)\s(?<LastName>(?:[A-Z]\w{1,3}|St\.\s)?[A-Z]\w+\-?[A-Z]?\w*)(?:,\s|)(?<Suffix>Jr\.|Sr\.|PHD\.|MD\.|3RD|2ND|RN\.|III|II|)/",$search,$names);
            foreach ($names[0] as $id => $name) {
              $name = trim($name);

              // separate the prefix and suffix's from the name
              preg_match( "/^((Mr|MR|Ms|Miss|Mrs|Dr|Sir)[.])/",$name, $prefix );
              preg_match( "/(PHD|MD|3RD|2ND|RN|JR|III|II|SR)/",$name, $suffix );
              if (!empty($prefix[0])) {
                $nameOutput[$id]['prefix'] = $prefix[0];
                $name = str_replace($prefix[0],"",$name);
              }
              if (!empty($suffix[0])) {
                $name = str_replace($suffix[0],"",$name);
              }

              // now that we have a name, break it into parts
              $nameArray = explode(' ', trim($name));
              $nameLenght = count($nameArray);
              $nodes = array('first','second','third','fourth','fifth');
              foreach ($nameArray as $key => $value) {
                // is this the last name?
                $count = ($nameLenght != $key+1) ?  $nodes[$key] : 'last';
                $nameOutput[$id][$count] = $value;
              }
              if (!empty($suffix[0])) {
                $nameOutput[$id]['suffix'] = $suffix[0];
              }

              // use dedupe rules to eliminate names not found in the system
              $query = "SELECT COUNT(id) from fn_group where given LIKE '".strtolower($nameOutput[$id]['first'])."';";
              $check_result = mysql_query($query, self::db());
              if($row = mysql_fetch_assoc($check_result)) {
                if($row['COUNT(id)'] < 1){
                  unset($nameOutput[$id]);
                }
              }
            }
            $output['found_names'] =  array_map("unserialize", array_unique(array_map("serialize", $nameOutput)));

            $time_end = microtime(true);
            $time = $time_end - $time_start;

            // colorizing the output
            $time2_start = microtime(true);
            function highlight($text, $search, $type) {
              switch ($type) {
                case 'name':
                  $re = '(' . implode(' ', $search). ')';
                  return preg_replace($re, "<span class='found $type' data-json='".json_encode($search)."' title='Click to use this Name'>".'${0}'."</span>", $text);
                  break;
                case 'addresses':
                  // var_dump($search);
                  $re = '(' . implode(' ', $search). ')';
                  return preg_replace($re, "<span class='found $type' data-json='".json_encode($search)."' title='Click to use this Address'>".'${0}'."</span>", $text);

                  break;
                default:
                  $re = implode('###', $search);
                  $re = preg_quote($re);
                  $re = '(' .preg_replace('/###/', '|', $re). ')';
                  // var_dump($text);
                  return preg_replace($re, "<span class='found $type' data-search='$0' title='Click to use this $type'>".'${0}'."</span>", $text);
                  break;
              }
            }
            $body = $output['body'];
            if (!empty($output['found_emails'])) {
              $body =  highlight($body, $output['found_emails'] ,'email_address');
            }
            if (!empty($output['found_addresses'])) {
              $body =  highlight($body, $output['found_addresses'],'addresses');
            }

            if (!empty($output['found_names'])) {
              foreach ($output['found_names'] as $key => $name) {
                $body =  highlight($body, $name,'name');
              }
            }
            if (!empty($output['found_phones'])) {
              $body =  highlight($body, $output['found_phones'],'phone');
            }
            $output['body'] = $body;
            $time2_end = microtime(true);
            $time2 = $time2_end - $time2_start;
            if ($debug){
              echo "
            <style>
              .found{
                  background: rgba(255, 230, 0, 0.5);
                  padding: 1px 2px;
                  border: 1px solid #C1C1C1;
                  margin: -1px 2px;
                  padding: 1px 4px;
                  border-radius: 3px;
                  display:inline-block;
              }
              .found:hover{
                  background: rgba(255, 230, 0, 0.8);
                  border: 1px solid #A0A0A0;

              }

              .found.name{
                  /* red ffb7b7 */
                  background: rgba(255,183,183, 0.5);
              }
              /*.found.name:hover{
                  background: rgba(255,183,183, 0.8);
              }*/
              .found.zip{
                  /* blue a8d1ff*/
                  background: rgba(168,209,255, 0.5);
              }
              .found.zip:hover{
                  background: rgba(168,209,255, 0.8);
              }
              .found.phone{
                  /* green a8d1ff*/
                  background: rgba(196,255,143, 0.5);
              }
              .found.phone:hover{
                  background: rgba(196,255,143, 0.8);
              }
            </style>";
              // var_dump($search);
              echo $body."<br/><br/><br/>";
              echo $time . " seconds ( Time to Find )\n";
              echo $time2 . " seconds ( Time to colorize )\n";

            }else{
              echo json_encode($output);
            }
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

    /**
     * Calculate bytes to different format
     * @param  [string] $date What you would like to convert
     * @return [array]        3 date formats
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

    /**
     * Switch the message status to deleted
     * For Unmatched & Matched screen Delete
     * @return [JSON Object]    Status message
     */
    public static function UnmatchedDelete() {
      // Set up IMAP variables
      self::setupImap();
      $ids = explode(',', self::get('id'));
      foreach ($ids as $key => $id) {
        $output = self::unifiedMessageInfo($id);
        $imap_id = $output['imap_id'];
        $message_id = $output['message_id'];
        $session = CRM_Core_Session::singleton();
        $userId =  $session->get('userID');
        // Delete the message with the specified UID
        $UPDATEquery = "UPDATE `nyss_inbox_messages`
        SET  `status`= 9, `matcher` = $userId
        WHERE `id` =  {$id}";
        $UPDATEresult = mysql_query($UPDATEquery, self::db());
      }
      $returnCode = array('code'=>'SUCCESS','status'=> '0','id'=>$ids, 'message'=>'Message Deleted');
      echo json_encode($returnCode);
      CRM_Utils_System::civiExit();
    }

    /**
     * Does a search for contacts and return them as a JSON object.
     * Uses BB_NORMALIZE to prevent dupes
     * For Unmatched & Matched screen Search
     * @return [JSON Object]    List of matching contacts
     */
    public static function ContactSearch() {
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



    /**
     * Adds an email address to the contacts
     * @return [JSON Object]    Status message
     */
    public static function ContactAddEmail() {
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

    /**
     * Creates activity from unmatched message
     * Assigns it to contact ID.
     * For Unmatched screen Match
     * @return [JSON Object]    Status message
     */
    public static function UnmatchedAssign() {
      require_once 'api/api.php';
      require_once 'CRM/Utils/File.php';
      require_once 'CRM/Utils/IMAP.php';
      $bbconfig = get_bluebird_instance_config();
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
      $oldActivityId = mysql_real_escape_string($output['activity_id']);
      $senderEmail = mysql_real_escape_string($output['sender_email']);
      $senderName = mysql_real_escape_string($output['sender_name']);
      $forwarder = mysql_real_escape_string($output['forwarder']);
      $date = mysql_real_escape_string($output['updated_date']);
      $FWDdate = mysql_real_escape_string($output['email_date']);
      $subject = mysql_real_escape_string($output['subject']);
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
          $ContactInfo = self::civiRaw('contact',$contactId);
          $ContactName = $ContactInfo['values'][$contactId]['display_name'];

          $aActivityType = CRM_Core_PseudoConstant::activityType();
          $activityType = array_search('Inbound Email', $aActivityType);
          $aActivityStatus = CRM_Core_PseudoConstant::activityStatus();

          $imap_activty_status = $bbconfig['imap.activity.status.default'];
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
            echo "<h1>Prams</h1>";
            print_r($params);
            echo "<h1>Activity Created ?</h1>";
            print_r($activity);
          }

          // if its an error or doesnt return we need errors
          if (($activity['is_error']==1) || ($activity['values']==null ) || (count($activity['values']) !=  1 )){
            $returnCode = array('code'      =>  'ERROR',
              'message'   =>  $activity['error_message']);
            echo json_encode($returnCode);
            CRM_Utils_System::civiExit();
          } else{

            $activity_id =$activity['id'];
            $returnCode['code'] = 'SUCCESS';
            $returnCode['messages'][] = array('code' =>'SUCCESS','message'=> "Message Assigned to ".$ContactName,'key'=>$key,'contact'=>$contactId);

            // if this is not the first contact, add a new row to the table
            if($ContactCount > 0){
              $debug_line = 'Added on assignment to #'.$ContactCount;
              $UPDATEquery = "INSERT INTO `nyss_inbox_messages` (`message_id`, `imap_id`, `sender_name`, `sender_email`, `subject`, `body`, `forwarder`, `status`, `debug`, `updated_date`, `email_date`,`activity_id`,`matched_to`,`matcher`) VALUES ('{$messageId}', '{$imapId}', '{$senderName}', '{$senderEmail}', '{$subject}', '{$body}', '{$forwarder}', '1', '$debug_line', '$date', '{$FWDdate}','{$activity_id}','{$contactId}','{$userId}');";


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
      echo json_encode($returnCode);
      }else{
        $returnCode = array('code'      =>  'ERROR', 'message'   =>  'Message is already matched' );
        echo json_encode($returnCode);
      }
      CRM_Utils_System::civiExit();
    }

    /**
     * Get full details about Contact, Activity, or Tag
     * @param  [string] $type The type of object you are searching for
     * @param  [int]    $id   The id you are searching for
     * @return [array]  CIVI generated array
     */
    public static function civiRaw($type,$id){
        require_once 'api/api.php';
        $params = array('version'   =>  3, 'activity'  =>  'get', 'id' => $id, );
        return civicrm_api($type, 'get', $params);
    }



    /**
     * Retrieves a list of Matched messages that have not been cleared,
     * For Matched screen overview
     * @return [JSON Object]    List overview of messages
     */
    public static function MatchedList() {
        require_once 'api/api.php';
        $debug = self::get('debug');
        $start = microtime(true);
        $range = self::get('range');
        $rangeVal = (is_numeric($range) && $range >= 1) ? "AND (updated_date BETWEEN '".date('Y-m-d H:i:s', strtotime('-'.$range.' days'))."' AND '".date('Y-m-d H:i:s')."') " : '';
        $UnprocessedQuery = " SELECT
        t1.id,
        UNIX_TIMESTAMP(t1.updated_date) as date_u, DATE_FORMAT(t1.updated_date, '%b %e, %Y %h:%i %p') as date_long,
        CASE
          WHEN DATE_FORMAT(updated_date, '%j') = DATE_FORMAT(NOW(), '%j') THEN DATE_FORMAT(updated_date, 'Today %l:%i %p')
          ELSE CASE
            WHEN DATE_FORMAT(updated_date, '%Y') = DATE_FORMAT(NOW(), '%Y') THEN DATE_FORMAT(updated_date, '%b %e, %l:%i %p')
            ELSE  DATE_FORMAT(updated_date, '%b %e, %Y')
          END
        END AS date_short,
        t1.matched_to, t1.sender_email, t1.subject, t1.forwarder, t1.activity_id, t1.matcher,
        IFNULL( count(t4.file_name), '0') as attachments,
        matcher.display_name as matcher_name,
        matched_to.display_name as fromName, matched_to.first_name as firstName, matched_to.last_name as lastName, matched_to.contact_type as contactType
        FROM nyss_inbox_messages as t1
        LEFT JOIN civicrm_contact as matcher ON (t1.matcher = matcher.id)
        LEFT JOIN civicrm_contact as matched_to ON (t1.matched_to = matched_to.id)
        LEFT JOIN nyss_inbox_attachments t4 ON (t1.id = t4.email_id)
        WHERE t1.status = 1 {$rangeVal}
        GROUP BY t1.id";

        $UnprocessedResult = mysql_query($UnprocessedQuery, self::db());
        while($row = mysql_fetch_assoc($UnprocessedResult)) {
          $id = $row['id'];
          foreach ($row as $key => $value) {
            $output = str_replace( chr( 194 ) . chr( 160 ), ' ', $value);
            $output = preg_replace('/[^a-zA-Z0-9\s\p{P}<>]/', '', trim($output));
            $returnMessage['Processed'][$id][$key] = $output;
          }
        }

        mysql_close(self::$db);

        $returnMessage['stats']['overview']['successes'] = count($returnMessage['Processed']);
        $end = microtime(true);
        $returnMessage['stats']['overview']['time'] = $end-$start;

        // $returnMessage['errors'] = $errors;

        if ($debug) echo "<pre>";
        echo (!$debug) ?  json_encode($returnMessage) : print_r($returnMessage);
        if ($debug) echo "</pre>";
        CRM_Utils_System::civiExit();
    }


    /**
     * Retrieve single activity details
     * For Matched screen EDIT or TAG
     * @return [JSON Object]    Encoded message output, OR error codes
     */
    public static function MatchedDetails() {
      $id = self::get('id');
      $debug = self::get('debug');
      $output = self::unifiedMessageInfo($id);
      // overwrite incorrect details
      $changeData = self::civiRaw('contact',$output['matched_to']);
      $output['sender_name'] = $changeData['values'][$output['matched_to']]['display_name'];
      $output['sender_email'] = $changeData['values'][$output['matched_to']]['email'];
      if(!$output){
        $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'Activity not found');#,'clear'=>'true');
      }else{
        $status = $output['status'];
        if($status != ''){
           switch ($status) {
            case '1':
              $time_start = microtime(true);
              $patterns = array('/\r\n|\r|\n/i', '/\<p(\s*)?\/?\>/i', '/\<br(\s*)?\/?\>/i', '/<div[^>]*>/','/<\/div>/' ,'/\//');
              $search = preg_replace('/&lt;|&gt;|&quot;|&amp;/i', '###',   $output['body']);
              $search = preg_replace($patterns, "\n ", $search);

              // Find Possible Email Addresses
              foreach(preg_split('/[, ;]/', $search) as $token) {
                $email = filter_var(filter_var($token, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);
                if ($email !== false) {
                    $emails[] = $email;
                }
              }
              $output['found_emails'] = array_unique($emails, SORT_REGULAR);

              // Find Possible city/states from zipcode with usps-ams service
              // docs here : http://geo.nysenate.gov:8080/usps-ams/docs/
              preg_match_all('/(?<=[\s])\d{5}(-\d{4})?\b/', $search, $zipcodes);
              $url = 'http://geo.nysenate.gov:8080/usps-ams/api/citystate?batch=true';
              $ch = curl_init($url);
              curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
              curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($zipcodes[0]));
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
              curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                  'Content-Type: application/json',
                  'Content-Length: ' . strlen(json_encode($zipcodes[0]))
              ));
              $addresses = json_decode(curl_exec($ch));
              // turn object into array, the easy way
              $addresses = json_decode(json_encode($addresses), true);
              $output['found_addresses'] = array_unique($addresses['results'], SORT_REGULAR);

              // Find Possible Phone numbers
              preg_match_all('/([\(]{1}[0-9]{3}[\)]{1}[\.| |\-]{0,1}|^[0-9]{3}[\.|\-| ]?)?[0-9]{3}(\.|\-| )?[0-9]{4}/s',$search,$phonecheck1);
              preg_match_all('/(?:\([2-9]\d{2}\)\ ?|(?:[2-9]\d{2}\-))[2-9]\d{2}\-\d{4}/s',$search,$phonecheck2);
              preg_match_all('/1?[-\. ]?(\(\d{3}\)?[-\. ]?|\d{3}?[-\. ]?)?\d{3}?[-\. ]?\d{4}/s',$search,$phonecheck3);

              // preg_match_all('/((((\(\d{3}\))|(\d{3}-))\d{3}-\d{4})|(\+?\d{2}((-| )\d{1,8}){1,5}))(( x| ext)\d{1,5}){0,1}/s',$search,$phonecheck4);
              preg_match_all('/(1?)(-| ?)(\()?([0-9]{3})(\)|-| |\)-|\) )?([0-9]{3})(-| )?([0-9]{4}|[0-9]{4})/s',$search,$phonecheck5);
              preg_match_all('/(1?(?: |\-|\.)?(?:\(\d{3}\)|\d{3})(?: |\-|\.)?\d{3}(?: |\-|\.)?\d{4})/s',$search,$phonecheck6);

              $phonenumbers = array_merge($phonecheck1[0], $phonecheck2[0], $phonecheck3[0], $phonecheck5[0], $phonecheck6[0]);
              $phonenumbers = array_filter(array_map('trim', $phonenumbers));
              $output['found_phones'] = array_unique($phonenumbers, SORT_REGULAR);
              // Find Possible Names
              preg_match_all( "/(?<FirstName>[A-Z]\.?\w*\-?[A-Z]?\w*)\s?(?<MiddleName>[A-Z]\w+|[A-Z]?\.?)\s(?<LastName>(?:[A-Z]\w{1,3}|St\.\s)?[A-Z]\w+\-?[A-Z]?\w*)(?:,\s|)(?<Suffix>Jr\.|Sr\.|PHD\.|MD\.|3RD|2ND|RN\.|III|II|)/",$search,$names);
              foreach ($names[0] as $id => $name) {
                $name = trim($name);

                // separate the prefix and suffix's from the name
                preg_match( "/^((Mr|MR|Ms|Miss|Mrs|Dr|Sir)[.])/",$name, $prefix );
                preg_match( "/(PHD|MD|3RD|2ND|RN|JR|III|II|SR)/",$name, $suffix );
                if (!empty($prefix[0])) {
                  $nameOutput[$id]['prefix'] = $prefix[0];
                  $name = str_replace($prefix[0],"",$name);
                }
                if (!empty($suffix[0])) {
                  $name = str_replace($suffix[0],"",$name);
                }

                // now that we have a name, break it into parts
                $nameArray = explode(' ', trim($name));
                $nameLenght = count($nameArray);
                $nodes = array('first','second','third','fourth','fifth');
                foreach ($nameArray as $key => $value) {
                  // is this the last name?
                  $count = ($nameLenght != $key+1) ?  $nodes[$key] : 'last';
                  $nameOutput[$id][$count] = $value;
                }
                if (!empty($suffix[0])) {
                  $nameOutput[$id]['suffix'] = $suffix[0];
                }

                // use dedupe rules to eliminate names not found in the system
                $query = "SELECT COUNT(id) from fn_group where given LIKE '".strtolower($nameOutput[$id]['first'])."';";
                $check_result = mysql_query($query, self::db());
                if($row = mysql_fetch_assoc($check_result)) {
                  if($row['COUNT(id)'] < 1){
                    unset($nameOutput[$id]);
                  }
                }
              }
              $output['found_names'] =  array_map("unserialize", array_unique(array_map("serialize", $nameOutput)));

              $time_end = microtime(true);
              $time = $time_end - $time_start;

              // colorizing the output
              $time2_start = microtime(true);
              function highlight($text, $search, $type) {
                switch ($type) {
                  case 'name':
                    $re = '(' . implode(' ', $search). ')';
                    return preg_replace($re, "<span class='found $type' data-json='".json_encode($search)."' title='Click to use this Name'>".'${0}'."</span>", $text);
                    break;
                  case 'addresses':
                    // var_dump($search);
                    $re = '(' . implode(' ', $search). ')';
                    return preg_replace($re, "<span class='found $type' data-json='".json_encode($search)."' title='Click to use this Address'>".'${0}'."</span>", $text);

                    break;
                  default:
                    $re = implode('###', $search);
                    $re = preg_quote($re);
                    $re = '(' .preg_replace('/###/', '|', $re). ')';
                    // var_dump($text);
                    return preg_replace($re, "<span class='found $type' data-search='$0' title='Click to use this $type'>".'${0}'."</span>", $text);
                    break;
                }
              }
              $body = $output['body'];
              if (!empty($output['found_emails'])) {
                $body =  highlight($body, $output['found_emails'] ,'email_address');
              }
              if (!empty($output['found_addresses'])) {
                $body =  highlight($body, $output['found_addresses'],'addresses');
              }

              if (!empty($output['found_names'])) {
                foreach ($output['found_names'] as $key => $name) {
                  $body =  highlight($body, $name,'name');
                }
              }
              if (!empty($output['found_phones'])) {
                $body =  highlight($body, $output['found_phones'],'phone');
              }
              $output['body'] = $body;
              $time2_end = microtime(true);
              $time2 = $time2_end - $time2_start;
              if ($debug){
                echo "
              <style>
                .found{
                    background: rgba(255, 230, 0, 0.5);
                    padding: 1px 2px;
                    border: 1px solid #C1C1C1;
                    margin: -1px 2px;
                    padding: 1px 4px;
                    border-radius: 3px;
                    display:inline-block;
                }
                .found:hover{
                    background: rgba(255, 230, 0, 0.8);
                    border: 1px solid #A0A0A0;

                }

                .found.name{
                    /* red ffb7b7 */
                    background: rgba(255,183,183, 0.5);
                }
                /*.found.name:hover{
                    background: rgba(255,183,183, 0.8);
                }*/
                .found.zip{
                    /* blue a8d1ff*/
                    background: rgba(168,209,255, 0.5);
                }
                .found.zip:hover{
                    background: rgba(168,209,255, 0.8);
                }
                .found.phone{
                    /* green a8d1ff*/
                    background: rgba(196,255,143, 0.5);
                }
                .found.phone:hover{
                    background: rgba(196,255,143, 0.8);
                }
              </style>";
                // var_dump($search);
                echo $body."<br/><br/><br/>";
                echo $time . " seconds ( Time to Find )\n";
                echo $time2 . " seconds ( Time to colorize )\n";

              }else{
                echo json_encode($output);
              }
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

    /**
     * Removes activity from civi and sets inbound email reference to DELETED
     * For Matched screen DELETE
     * @return [JSON Object]    JSON encoded response, OR error codes
     */
    public static function MatchedDelete() {
      require_once 'api/api.php';
      $ids = explode(',', self::get('id'));
      foreach ($ids as $key => $messageId) {
        $session = CRM_Core_Session::singleton();
        $userId =  $session->get('userID');

        $output = self::unifiedMessageInfo($messageId);
        $activity_id = $output['activity_id'];
        $error = false;
        $debug = self::get('debug');

        // deleteing a activity
        $params = array(
            'id' => $activity_id,
            'activity_type_id' => 1,
            'version' => 3,
        );

        $deleteActivity = civicrm_api('activity','delete',$params );

        // need to add to function to delete entity tags as they are not cleaned up

        if($deleteActivity['is_error'] == 1){
          $error = true;
        }

        if(!$error){
          $UPDATEquery = "UPDATE `nyss_inbox_messages`
          SET  `status`= 9, `matcher` = $userId
          WHERE `id` =  {$messageId}";
          $UPDATEresult = mysql_query($UPDATEquery, self::db());
          $returnCode = array('code'=>'SUCCESS','id'=>$ids, 'message'=>'Activity Deleted');
        }else{
          $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'Activity not found','clear'=>'true');
        }
      }
      echo json_encode($returnCode);

      mysql_close(self::$db);
      CRM_Utils_System::civiExit();
    }

    /**
     * Clears from inbound email Matched screen by setting status to 7
     * For Matched screen CLEAR
     * @return [JSON Object]    JSON encoded response, OR error codes
     */
    public static function Clear() {
      require_once 'api/api.php';
      $session = CRM_Core_Session::singleton();
      $userId =  $session->get('userID');
      $ids = explode(',', self::get('id'));
      $debug = self::get('debug');
      $Error = false;
      foreach ($ids as $key => $messageId) {
        $output = self::unifiedMessageInfo($messageId);
        if(!$output){
          $Error = true;
        }else if($output['status'] != ''){
          $UPDATEquery = "UPDATE `nyss_inbox_messages`
          SET  `status`= 7, `matcher` = $userId
          WHERE `id` =  {$messageId}";
          $UPDATEresult = mysql_query($UPDATEquery, self::db());
        }else{
          $Error = true;
        }
      }
      $count = count($ids);
      $plural = (count($ids) > 1) ? "ies" : "y";
      if($Error){
        $returnCode = array('code' => 'ERROR','message'=> "Could not Clear {$count} Activit{$plural}");
      }else{
        $returnCode = array('code' =>'SUCCESS','message'=> "{$count} Activit{$plural} successfully Cleared",'clear'=>'true');
      }
      mysql_close(self::$db);
      echo json_encode($returnCode);
      CRM_Utils_System::civiExit();
    }

    /**
     * Assign an inbound email activity to a different contact
     * For Matched screen EDIT
     * @return [JSON Object]    JSON encoded response, OR error codes
     */
    public static function MatchedReassign() {
      require_once 'api/api.php';
      $id = self::get('messageId');
      $debug = self::get('debug');
      $contactIds = self::get('contactId');
      $contactIds = explode(',', $contactIds);
      $ContactCount = 0;

      $output = self::unifiedMessageInfo($id);
      $contact = $output['matched_to'];
      $activityId =  $output['activity_id'];
      $date =  $output['updated_date'];


      $results = array();


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
      }

      // we need to check to see if the activity is still assigned to the same contact
      // if not, kill it
      // 1 = assignee, 2 = source, 3 = target
      $query = <<<EOQ
SELECT COUNT(id)
FROM `civicrm_activity_contact`
WHERE `activity_id` = $activityId
AND `contact_id` = $contact
AND `record_type_id` = 3
EOQ;
      $check_result = mysql_query($query, self::db());
      if($row = mysql_fetch_assoc($check_result)) {
        $check = $row['COUNT(id)'];
      }
      if ($debug){
        echo "<h1>check</h1>";
        var_dump($query);
        var_dump($check);
      }
      if($check != '1'){
        $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'Activity is not assigned to this Contact, Please Reload','clear'=>'true');
        echo json_encode($returnCode);
        CRM_Utils_System::civiExit();
      }

      foreach($contactIds as $contactId) {
        $changeData = self::civiRaw('contact',$contactId);
        $changeName = trim($changeData['values'][$contactId]['display_name']);
        $firstName = trim($changeData['values'][$contactId]['first_name']);
        $LastName = trim($changeData['values'][$contactId]['last_name']);
        $contactType = trim($changeData['values'][$contactId]['contact_type']);
        $email = trim($changeData['values'][$contactId]['email']);

        // change the contact
        $Update = <<<EOQ
UPDATE `civicrm_activity_contact`
SET  `contact_id`= $contactId
WHERE `activity_id` =  $activityId
AND `record_type_id` = 3
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
        SET  `matcher` = $userId,  `matched_to` = $contactId, `sender_name` = '$changeName',`sender_email` = '$email', `updated_date` = '$date'
        WHERE `id` =  {$id}";
        $UPDATEresult = mysql_query($UPDATEquery, self::db());
        $returnCode = array('code'=>'SUCCESS','id'=>$id,'contact_id'=>$contactId,'contact_type'=>$contactType,'first_name'=>$firstName,'last_name'=>$LastName,'display_name'=>$changeName,'email'=>$email,'activity_id'=>$id,'message'=>'Activity Reassigned to '.$changeName);
      }

      echo json_encode($returnCode);
      mysql_close(self::$db);
      CRM_Utils_System::civiExit();
    }

    /**
     * Edit the Assignee for a inbound email activity to a different office worker
     * For Matched screen EDIT
     * @return [JSON Object]    JSON encoded response, OR error codes
     */
    public static function MatchedEdit() {
      require_once 'api/api.php';
      $activityid = self::get('activity_id');
      $activityIds = explode(',', $activityid);
      $activty_contact = self::get('activty_contact');
      $activty_status_id = self::get('activty_status_id');
      $activity_date = self::get('activity_date');
      $results = array();
      foreach($activityIds as $activity_id) {

        if (!empty($activty_status_id)) {
          $query_activiy1 = "UPDATE civicrm_activity SET status_id  = ${activty_status_id}  WHERE civicrm_activity.id  = ${activity_id}";
          // var_dump($query_activiy1);
          $Updated_results = mysql_query($query_activiy1, self::db());
          while($row = mysql_fetch_assoc($Updated_results)) {
            $results[] = $row;
          }
        }

        if (!empty($activity_date)) {
          $query_activiy2 = "UPDATE civicrm_activity SET activity_date_time = \"${activity_date}\" WHERE civicrm_activity.id  = ${activity_id}";
          // var_dump($query_activiy2);
          $Updated_results = mysql_query($query_activiy2, self::db());
          while($row = mysql_fetch_assoc($Updated_results)) {
            $results[] = $row;
          }
        }
        // change the contact
        if (!empty($activty_contact)) {
          $query_activiy3 = "INSERT INTO civicrm_activity_contact (activity_id, contact_id, record_type_id) VALUES ('${activity_id}', '${activty_contact}', '1');";
          // var_dump($query_activiy3);
          $Updated_results = mysql_query($query_activiy3, self::db());
          while($row = mysql_fetch_assoc($Updated_results)) {
            $results[] = $row;
          }
          // NYSS - 7929
          $attachments = CRM_Core_BAO_File::getEntityFile('civicrm_activity', $activity_id );
          $assigneeContacts = CRM_Activity_BAO_ActivityAssignment::getAssigneeNames($activity_id, TRUE, FALSE);
          //build an associative array with unique email addresses.
          foreach ($assigneeContacts as $id => $value) {
            $mailToContacts[$assigneeContacts[$id]['email']] = $assigneeContacts[$id];
          }
          CRM_Case_BAO_Case::sendActivityCopy(NULL, $activity_id, $mailToContacts, $attachments, NULL);
        }
      }

      // echo json_encode($results);
      mysql_close(self::$db);
      CRM_Utils_System::civiExit();
    }
    /**
     * Autocomplete Keyword search for tags
     * For Matched screen TAG
     * @return [JSON Object]    JSON encoded response, OR error codes
     */
    public static function KeywordSearch() {
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


    /**
     * Assign Keywords or Positions to a Contact or a Activity
     * For Matched screen TAG
     * @return [JSON Object]   JSON encoded response, OR error codes
     */
    public static function TagAdd() {
        $activityIds = self::get('activityId');
        $contactIds = self::get('contactId');
        $tagIds = self::get('tags');
        $debug = self::get('debug');

        // add new positions to the 292 (positions) table
        $parentId = (self::get('parentId')) ? self::get('parentId') :'296';
        $type = (self::get('parentId') == 292) ? "Position" : "Keyword";

        $tagsIDs = split(',', $tagIds);
        $result = array();

        if ($debug){
          echo "<h1>Prams</h1>";
          var_dump("activityIds",$activityIds);
          var_dump("contactIds",$contactIds);
          var_dump("tagsIDs",$tagsIDs);
          var_dump("parentId",$parentId);
        }
        require_once 'api/api.php';

        if (!empty($contactIds)) {
          $contactIds = split(',', $contactIds);
          $realTagIds = array();
          foreach($contactIds as $contactId)
          {
            $entityTable = 'civicrm_contact';
            $existingTags = CRM_Core_BAO_EntityTag::getTag($contactId, $entityTable);
            $contactTagIds = array();
            foreach ($tagsIDs as $tagId) {

              if (!is_numeric($tagId)) {
                // check if user has selected existing tag or is creating new tag
                // this is done to allow numeric tags etc.
                $tagValue = explode(':::', $tagId);
                if (isset($tagValue[1]) && $tagValue[1] == 'value') {
                  // does tag already exist ?
                  $params = array('version'   =>  3, 'activity'  =>  'get', 'name' => $tagValue[0], 'parent_id' => $parentId  );
                  $check = civicrm_api('tag', 'get', $params);
                  if ($check['count'] != 0) {
                    $tagId =  strval($check['id']);
                  }else{
                    $tagParams = array(
                      'name' => $tagValue[0],
                      'parent_id' => $parentId,
                      'used_for' => 'civicrm_contact,civicrm_activity,civicrm_case',
                    );
                    $tagObject = CRM_Core_BAO_Tag::add($tagParams, CRM_Core_DAO::$_nullArray);
                    $tagId = strval($tagObject->id);
                  }
                }
              }
              $realTagIds[] = $tagId;
              if (!array_key_exists($tagId, $existingTags)) {
                $contactTagIds[] = $tagId;
              }
            }
            if (!empty($contactTagIds)) {
              // New tag ids can be inserted directly into the db table.
              $insertValues = array();
              foreach ($contactTagIds as $tagId) {
                $insertValues[] = "( {$tagId}, {$contactId}, '{$entityTable}' ) ";
              }
              $insertSQL = 'INSERT INTO civicrm_entity_tag ( tag_id, entity_id, entity_table ) VALUES ' . implode(', ', $insertValues) . ';';
              $result = mysql_query($insertSQL, self::db());
              // result = 1 when successful
              $output[$result][] = $insertSQL;
            }else{
              $output[0][] = 'NO NEW TAGS FOUND';
            }
          }
        }
        if (!empty($activityIds)) {
          $activityIds = split(',', $activityIds);
          $realTagIds = array();
          foreach($activityIds as $activityId)
          {
            $entityTable = 'civicrm_activity';
            $existingTags = CRM_Core_BAO_EntityTag::getTag($activityId, $entityTable);
            $activityTagIds = array();
            foreach ($tagsIDs as $tagId) {
              if (!is_numeric($tagId)) {
                // check if user has selected existing tag or is creating new tag
                // this is done to allow numeric tags etc.
                $tagValue = explode(':::', $tagId);
                if (isset($tagValue[1]) && $tagValue[1] == 'value') {
                  // does tag already exist ?
                  $params = array('version'   =>  3, 'activity'  =>  'get', 'name' => $tagValue[0], 'parent_id' => $parentId  );
                  $check = civicrm_api('tag', 'get', $params);
                  if ($check['count'] != 0) {
                    $tagId =  strval($check['id']);
                  }else{
                    $tagParams = array(
                      'name' => $tagValue[0],
                      'parent_id' => $parentId,
                      'used_for' => 'civicrm_contact,civicrm_activity,civicrm_case',
                    );
                    $tagObject = CRM_Core_BAO_Tag::add($tagParams, CRM_Core_DAO::$_nullArray);
                    $tagId = strval($tagObject->id);
                  }
                }
              }
              $realTagIds[] = $tagId;
              if (!array_key_exists($tagId, $existingTags)) {
                $activityTagIds[] = $tagId;
              }
            }
            if (!empty($activityTagIds)) {
              // New tag ids can be inserted directly into the db table.
              $insertValues = array();
              foreach ($activityTagIds as $tagId) {
                $insertValues[] = "( {$tagId}, {$activityId}, '{$entityTable}' ) ";
              }
              $insertSQL = 'INSERT INTO civicrm_entity_tag ( tag_id, entity_id, entity_table ) VALUES ' . implode(', ', $insertValues) . ';';
              $result = mysql_query($insertSQL, self::db());
              // result = 1 when successful
              $output[$result][] = $insertSQL;
            }else{
              $output[0][] = 'NO NEW TAGS FOUND';
            }
          }
        }
        if ($debug){
          echo "<h1>result</h1>";
          var_dump($output);
        }

        if(!$output[1]){
          $returnCode = array('code' => 'ERROR','message'=> "Could not assign {$type}{$plural}");
        }else{
          $plural = (count($output[1]) > 1) ? "s" : "" ;
          $plural2 = (count($output[1]) > 1) ? "were" : "was";
          $returnCode = array('code' =>'SUCCESS','message'=> "{$type}{$plural} {$plural2} successfully assigned");
        }
        echo json_encode($returnCode);
        CRM_Utils_System::civiExit();
    }

    /**
     * Assign tags to a Contact or a Activity
     * For Matched screen TAG
     * @return [JSON Object]    JSON encoded response, OR error codes
     */
    public static function Issuecode() {
      require_once 'api/api.php';
      $tags = self::get('issuecodes');
      $tags = split(',', $tags);
      $contacts = self::get('contacts');
      $contacts = split(',', $contacts);
      $action = self::get('action');
      $prettyAction = ($action === 'create') ? "added to" : "deleted from" ;
      $plural = (count($tags) > 1) ? "s" : "" ;
      $plural2 = (count($tags) > 1) ? "were" : "was";
      $errorMessage ='';
      if(is_null($tags) || $tags == 0) {
        $returnCode = array('code'      =>  'ERROR',
                            'message'   =>  'No valid tags.');
        echo json_encode($returnCode);
        CRM_Utils_System::civiExit();
      }
      $tagString ='';
      $names = array();
      foreach($tags as $tagId) {
        $tag = self::civiRaw('tag',$tagId);
        $tagName = $tag['values'][$tagId]['name'];
        $tagstring .= "'".$tagName."',";
        foreach($contacts as $contactId) {
          if($contactId == 0)  break;
          $params = array(
            'entity_table'  =>  'civicrm_contact',
            'entity_id'     =>  $contactId,
            'tag_id'        =>  $tagId,
            'version'       =>  3,
          );
          $result = civicrm_api('entity_tag', $action, $params );
          $contact = self::civiRaw('contact',$contactId);
          $names[$contactId] = "'".$contact['values'][$contactId]['display_name']."',";
          if($result['is_error']==1){
            $errorMessage = $result['error_message'];
          }
        }
      }
      $tagstring = rtrim($tagstring, ',');
      $nameString = rtrim(implode(",", $names), ',');
      if (!empty($errorMessage)) {
        $returnCode = array('code' => 'ERROR','message'=> $errorMessage." ".$prettyAction." {$tagstring} to {$nameString}");
      }else{
        $returnCode = array('code' =>'SUCCESS','message'=> "Issue code{$plural} {$tagstring} {$plural2} {$prettyAction} {$nameString}");
      }
      echo json_encode($returnCode);
      CRM_Utils_System::civiExit();
    }






    /**
     * Generate Usage Report
     * Current report shows Statistics & every message list
     * For Report screen
     * @return [JSON Object]    JSON encoded response, OR error codes
     */
    public static function getReports() {
      $debug = self::get('debug');
      $range = self::get('range');
      $rangeVal = (is_numeric($range) && $range >= 1) ? "AND (updated_date BETWEEN '".date('Y-m-d H:i:s', strtotime('-'.$range.' days'))."' AND '".date('Y-m-d H:i:s')."') " : '';
      $Query = "SELECT
t1.id,
UNIX_TIMESTAMP(t1.updated_date) as date_u, DATE_FORMAT(t1.updated_date, '%b %e, %Y %h:%i %p') as date_long,
CASE
  WHEN DATE_FORMAT(updated_date, '%j') = DATE_FORMAT(NOW(), '%j') THEN DATE_FORMAT(updated_date, 'Today %l:%i %p')
  ELSE CASE
    WHEN DATE_FORMAT(updated_date, '%Y') = DATE_FORMAT(NOW(), '%Y') THEN DATE_FORMAT(updated_date, '%b %e, %l:%i %p')
    ELSE  DATE_FORMAT(updated_date, '%b %e, %Y')
  END
END AS date_short,

UNIX_TIMESTAMP(t1.updated_date) as email_date_u, DATE_FORMAT(t1.updated_date, '%b %e, %Y %h:%i %p') as email_date_long,
CASE
  WHEN DATE_FORMAT(updated_date, '%j') = DATE_FORMAT(NOW(), '%j') THEN DATE_FORMAT(updated_date, 'Today %l:%i %p')
  ELSE CASE
    WHEN DATE_FORMAT(updated_date, '%Y') = DATE_FORMAT(NOW(), '%Y') THEN DATE_FORMAT(updated_date, '%b %e, %l:%i %p')
    ELSE  DATE_FORMAT(updated_date, '%b %e, %Y')
  END
END AS email_date_short,

CASE
    WHEN t1.status = '0' then 'Unmatched'
    WHEN t1.status = '1' then CONCAT( 'Matched by ', matcher.display_name)
    WHEN t1.status = '7' then 'Cleared'
    WHEN t1.status = '8' then 'Deleted'
    WHEN t1.status = '9' then 'Deleted'
    ELSE -1
END as status_string,

t1.matched_to, t1.sender_email, t1.subject, t1.forwarder, t1.activity_id, t1.matcher, t1.status,
IFNULL( count(t4.file_name), '0') as attachments,
matcher.display_name as matcher_name,

CASE
    WHEN matched_to.display_name IS NULL then t1.sender_email
    WHEN matched_to.display_name IS NOT NULL then matched_to.display_name
    ELSE -1
END as fromName,

matched_to.first_name as firstName, matched_to.last_name as lastName, matched_to.contact_type as contactType
FROM nyss_inbox_messages as t1
LEFT JOIN civicrm_contact as matcher ON (t1.matcher = matcher.id)
LEFT JOIN civicrm_contact as matched_to ON (t1.matched_to = matched_to.id)
LEFT JOIN nyss_inbox_attachments t4 ON (t1.id = t4.email_id)
WHERE t1.status != 99 {$rangeVal}
GROUP BY t1.id
LIMIT 0 , 100000";
      $QueryResult = mysql_query($Query, self::db());



      $total = $unMatched = $Matched = $Cleared = $Errors = $Deleted = 0;
      while($row = mysql_fetch_assoc($QueryResult)) {
       $Output[] = $row;
       $total ++;
       switch ($row['status']) {
         case '0':
           $unMatched ++;
           break;
         case '1':
           $Matched ++;
           break;
         case '7':
           $Cleared ++;
           break;
         case '8':
         case '9':
           $Deleted ++;
           break;
         default:
            $Errors ++;
           break;
       }

      }

      if ($debug){
        echo "<h1>Building Reports</h1>";
        var_dump($Query);
        echo "Response <br/>";
        var_dump($Output);
      }

      $returnCode = array('code'      =>  'SUCCESS',
      'total' =>  $total,
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

    /**
     * Create New contact from message
     * For Matched edit & Unmatched find
     * @return [JSON Object]    JSON encoded response, OR error codes
     */
    public static function ContactAdd() {
        require_once 'api/api.php';

        $debug = self::get('debug');
        // testing url
        $prefix = (strtolower(self::get('prefix')) == 'first name' || trim(self::get('prefix')) =='') ? NULL : self::get('prefix');
        $middle_name = (strtolower(self::get('middle_name')) == 'first name' || trim(self::get('middle_name')) =='') ? NULL : self::get('middle_name');
        $suffix = (strtolower(self::get('suffix')) == 'first name' || trim(self::get('suffix')) =='') ? NULL : self::get('suffix');

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
    if((isset($first_name))||(isset($last_name))){
      $display_name = trim($first_name." ".$last_name);
    }else{
      $display_name = $email;
    }

    if ($debug){
      echo "<h1>inputs</h1>";
      echo "</pre> prefix: <pre>";
      var_dump($prefix);

      echo "</pre> first_name: <pre>";
      var_dump($first_name);
      echo "</pre> middle_name: <pre>";
      var_dump($middle_name);
      echo "</pre> last_name: <pre>";
      var_dump($last_name);
      echo "</pre> suffix: <pre>";
      var_dump($suffix);

      echo "</pre> email: <pre>";
      var_dump($email);
      echo "</pre> phone: <pre>";
      var_dump($phone);
      echo "</pre> street_address: <pre>";
      var_dump($street_address);
      echo "</pre> street_address_2: <pre>";
      var_dump($street_address_2);
      echo "</pre> postal_code: <pre>";
      var_dump($postal_code);
      echo "</pre> city: <pre>";
      var_dump($city);
      echo "</pre> dob: <pre>";
      var_dump($dob);
      echo "</pre> state: <pre>";
      var_dump($state);
      echo "</pre> display name: <pre>";
      var_dump($display_name);
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
      'middle_name'=> $middle_name,
      'last_name' => $last_name,
      'prefix_id' => $prefix,
      'suffix_id' => $suffix,
      'sort_name' => $display_name,
      'display_name' => $display_name,
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
        'location_type_id' => $locationResults[0], // Other
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

