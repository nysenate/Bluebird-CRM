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

    public static function strip_HTML_tags($text){ // Strips HTML 4.01 start and end tags. Preserves contents.
        return preg_replace('%
            # Match an opening or closing HTML 4.01 tag.
            </?                  # Tag opening "<" delimiter.
            (?:                  # Group for HTML 4.01 tags.
              ABBR|ACRONYM|ADDRESS|APPLET|AREA|A|BASE|BASEFONT|BDO|BIG|
              BLOCKQUOTE|BODY|BUTTON|B|CAPTION|CENTER|CITE|CODE|COL|
              COLGROUP|DD|DEL|DFN|DIR|DIV|DL|DT|EM|FIELDSET|FONT|FORM|
              FRAME|FRAMESET|H\d|HEAD|HR|HTML|IFRAME|IMG|INPUT|INS|
              ISINDEX|I|KBD|LABEL|LEGEND|LI|LINK|MAP|MENU|META|NOFRAMES|
              NOSCRIPT|OBJECT|OL|OPTGROUP|OPTION|PARAM|PRE|P|Q|SAMP|
              SCRIPT|SELECT|SMALL|SPAN|STRIKE|STRONG|STYLE|SUB|SUP|S|
              TABLE|TD|TBODY|TEXTAREA|TFOOT|TH|THEAD|TITLE|TR|TT|U|UL|VAR
            )\b                  # End group of tag name alternative.
            (?:                  # Non-capture group for optional attribute(s).
              \s+                # Attributes must be separated by whitespace.
              [\w\-.:]+          # Attribute name is required for attr=value pair.
              (?:                # Non-capture group for optional attribute value.
                \s*=\s*          # Name and value separated by "=" and optional ws.
                (?:              # Non-capture group for attrib value alternatives.
                  "[^"]*"        # Double quoted string.
                | \'[^\']*\'     # Single quoted string.
                | [\w\-.:]+      # Non-quoted attrib value can be A-Z0-9-._:
                )                # End of attribute value alternatives.
              )?                 # Attribute value is optional.
            )*                   # Allow zero or more attribute=value pairs
            \s*                  # Whitespace is allowed before closing delimiter.
            /?                   # Tag may be empty (with self-closing "/>" sequence.
            >                    # Opening tag closing ">" delimiter.
            | <!--.*?-->         # Or a (non-SGML compliant) HTML comment.
            | <!DOCTYPE[^>]*>    # Or a DOCTYPE.
            %six', '', $text);
    }

    // http://www.electrictoolbox.com/function-extract-email-attachments-php-imap/
    //http://www.electrictoolbox.com/php-imap-message-body-attachments/
    public static function flattenParts($messageParts, $flattenedParts = array(), $prefix = '', $index = 1, $fullPrefix = true) {
      // if(isset($structure->parts)) $flattenedParts = self::flattenParts($structure->parts);else $flattenedParts['1'] = $structure;
      foreach($messageParts as $part) {
        $flattenedParts[$prefix.$index] = $part;
        if(isset($part->parts)) {
          if($part->type == 2) {
            $flattenedParts = flattenParts($part->parts, $flattenedParts, $prefix.$index.'.', 0, false);
          }
          elseif($fullPrefix) {
            $flattenedParts = flattenParts($part->parts, $flattenedParts, $prefix.$index.'.');
          }
          else {
            $flattenedParts = flattenParts($part->parts, $flattenedParts, $prefix);
          }
          unset($flattenedParts[$prefix.$index]->parts);
        }
        $index++;
      }

      return $flattenedParts;

    }

    public static function getPart($connection, $messageNumber, $partNumber, $encoding) {

      $data = imap_fetchbody($connection, $messageNumber, $partNumber);
      switch($encoding) {
        case 0: return $data; // 7BIT
        case 1: return $data; // 8BIT
        case 2: return $data; // BINARY
        case 3: return base64_decode($data); // BASE64
        case 4: return quoted_printable_decode($data); // QUOTED_PRINTABLE
        case 5: return $data; // OTHER
      }


    }

    public static function getFilenameFromPart($part) {

      $filename = '';

      if($part->ifdparameters) {
        foreach($part->dparameters as $object) {
          // var_dump($object);
          $text = iconv($charset, 'UTF-8', $text);
          $input = str_replace($encoded, $text, $input);
          if(strtolower($object->attribute) == 'filename') {
            $filename = quoted_printable_decode($object->value);
          }
        }
      }

      if(!$filename && $part->ifparameters) {
        foreach($part->parameters as $object) {
          if(strtolower($object->attribute) == 'name') {
            $filename = $object->value;
          }
        }
      }

      return $filename;

    }


    /* unifiedMessageInfo()
     * Parameters: imap = object of inbox, id = messageid. imap_id = imap mailbox
     * Returns: An Object message details to map to the output.
     * This function grabs a single messages and cleans it for output.
     */
    public static function unifiedMessageInfo($imap,$id,$imap_id) {

        $debug = self::get('debug');
        $uniStart = microtime(true);
        // check to see if we are connected
        $connection = imap_check($imap->conn());
        if(!$connection ){
          $output = array('code'=>'ERROR','status'=>'0','message'=>'Imap Connection failed');
        }else{

          // Pull the message via the UID and output it as plain text if possible
          $email = $imap->getmsg_uid($id);
          if ($debug){
            echo "<h1>Imap Errors</h1>";
            var_dump(imap_errors());;
            echo "<h1>Input</h1>";
            var_dump($id);
            var_dump($imap_id);
            echo "<h1>Full Email RAW DATA</h1><pre>";
            var_dump($email);
            echo "</pre>";
          }
          // if message is less the x mins old, check it to see if it matches a contact,
          // if it does directly match, don't allow it to show up in the unmatches screen
          // we do this because the processing scritp hasn't had a chance to match it yet

          if( $email->uid == '' || $email->time =='' ){

            $code = 'ERROR';
            $output = array('code'=>$code,'status'=>'0','message'=>'This message does not exist','clear'=>'true','debug'=> $email->uid.':'.$email->time.':'.$email->time.':'. $time);
          }else{
            $code = 'SUCCESS';

            // email has absolutely been processed by script so return it
            $details = ($email->plainmsg) ? $email->plainmsg : $email->htmlmsg;
            $format = ($email->plainmsg) ? "plain" : "html";

            // print_r($details);
            // var_dump($format);

            // check for fake html
            // we don't care if the body only has <br/> tags
            if(strip_tags($details) != strip_tags($details,"<br>")){
              $format = 'html';
            }
            // var_dump($format);

            if($format =='plain'){
                $tempDetails = preg_replace("/>|</i", "", $details);
                $body = preg_replace("/(=|\r\n|\r|\n)/i", "\r\n<br>\n", $tempDetails);
            }else{
                // currently strips content
                $tempDetails = strip_tags($details,'<br>');
                $tempDetails = preg_replace("/<br>/i", "\r\n<br>\n", $tempDetails);
                // $tempDetails = self::strip_HTML_tags($tempDetails);

                $body = $details; // switch us back to the html version
            }

            // print_r($tempDetails);
            // exit();

            $messageid = $email->msgno;
            $structure = imap_fetchstructure($imap->conn(), $messageid);
            if ($debug){
              echo "<h1>Attachemts</h1>";
              var_dump($messageid);
              var_dump($structure->parts);
            }
            // $flattenedParts = self::flattenParts($structure->parts);
            // var_dump($flattenedParts);
            // exit();
            // foreach($structure->parts as $partNumber => $part) {
            //   echo "$part->type \n";
            //   switch($part->type) {

            //     case 0:
            //       // the HTML or plain text part of the email
            //       $message = getPart($connection, $messageNumber, $partNumber, $part->encoding);
            //       // now do something with the message, e.g. render it
            //     break;
            //     case 1:
            //       // multi-part headers, can ignore
            //     break;
            //     case 2:
            //       // attached message headers, can ignore
            //     break;

            //     case 3: // application
            //     case 4: // audio
            //     case 5: // image
            //     var_dump($part);
            //       $filename = self::getFilenameFromPart($part);
            //       $filename2 = utf8_decode(imap_utf8($filename));
            //       $filename3 = imap_mime_header_decode($filename);
            //       $filename4 =  iconv_mime_decode($filename, 0, "ISO-8859-1");
            //       for ($i=0; $i<count($filename3); $i++) {
            //           echo "Charset: {$filename4[$i]->charset}\n";
            //           echo "Text: {$filename4[$i]->text}\n\n";
            //       }
            //       var_dump($filename2);
            //       $attachmentArray[]  = array(
            //       'type' => $part->type,
            //       'subtype' => $part->subtype,
            //       'filename' => $filename.$filename2.$filename3.$filename4,
            //       'inline' => false,
            //     );

            //     case 6: // video
            //     case 7: // other
            //       $filename = self::getFilenameFromPart($part);
            //       if($filename) {
            //          // it's an attachment
            //         $attachment = self::getPart($connection, $messageNumber, $partNumber, $part->encoding);
            //         // now do something with the attachment, e.g. save it somewhere
            //       }
            //       else {
            //         // don't know what it is
            //       }
            //     break;

            //   }

            // }


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

            // check to see if forwarded
          if(!$froms['2'] || !$subjects['2']){
            $status =  'direct';
          }else{
            $status ='forwarded';
          }
          $cleanDate = self::cleanDate($email->date);
          $date_short = $cleanDate['short'];
          $date_long =  $cleanDate['long'];
          $date_u =  $cleanDate['u'];

          $body = self::strip_HTML_tags($body);
          // maybe im a type nerd, but proper quotes are important
          $body = preg_replace('/\'/', '&#8217;', $body);
          $body = preg_replace('/ "/', ' &#8220;', $body);
          $body = preg_replace('/" |"$/', '&#8221; ', $body);
          $body = preg_replace('/"\\n|"\\r/', '&#8221;<br/>', $body);

          //  other normalizing work
          $body = preg_replace('/ +/', ' ', $body);
          $body = preg_replace('/\r\r|\n\n/', '<br/>', $body);
          $body = preg_replace('/\r|\n/', '', $body);
          $body = mysql_real_escape_string($body);

            // contains info directly from the email header
            $header = array(
                'messageId'=>$id,
                'format' => $format,
                'uid' => $email->uid,
                'from' => $email->sender[0]->personal.' '.$email->sender[0]->mailbox . '@' . $email->sender[0]->host,
                'from_name' => $email->sender[0]->personal,
                'from_email' => $email->sender[0]->mailbox.'@'.$email->sender[0]->host,
                'subject' => trim(substr(strip_tags($email->subject),0,255)),
                'body' =>    $body,
                'date_short' => $date_short,
                'date_long' => $date_long,
                'date_u' => $date_u,
                'status' => $status,
            );

            // if we have a direct message populate accordingly
            $origin = ($status == 'direct') ?  $header['from'] : $froms['2'];
            $origin_name = ($status == 'direct') ?  $header['from_name']  :  $fromEmail['name'];
            $origin_email = ($status == 'direct') ?  $header['from_email'] : $fromEmail['email'];



            $fwdcleanDate = self::cleanDate($tempDetails);
            $fwddate_short = $fwdcleanDate['short'];
            $fwddate_u = $fwdcleanDate['u'];
            $fwddate_long = $fwdcleanDate['long'];

            $origin_date_short = ($fwddate_u < 10000) ?  $date_short : $fwddate_short ;
            $origin_date_long = ($fwddate_u < 10000) ?  $date_long : $fwddate_long ;
            $origin_date_u = ($fwddate_u < 10000) ?  $date_u : $fwddate_u ;

            $origin_subject = ($status == 'direct') ?  preg_replace("/(Fwd:|fwd:|Fw:|fw:|Re:|re:) /i", "", $header['subject']) : preg_replace("/(Fwd:|fwd:|Fw:|fw:|Re:|re:) /i", "", $subjects['2']);

            $origin_subject = preg_replace("/(\(|\))/i", "", $origin_subject);
            if( trim(strip_tags($origin_subject)) == "" | trim($origin_subject) == "no subject"){
              $origin_subject = "No Subject";
            }

            // contains info about the forwarded message in the email body
            $forwarded = array(
                'date_short' => $origin_date_short,
                'date_long' => $origin_date_long,
                'date_u' => $origin_date_u,
                'subject' => trim(substr(strip_tags($origin_subject),0,255)),
                'origin' => $origin,
                'origin_name' => $origin_name,
                'origin_email' => $origin_email,
                'origin_lookup' => $fromEmail['type'],
            );
            $output = array('code'=>$code,'header'=>$header,'forwarded'=>$forwarded,'attachments'=>$attachmentArray);
          }

          $uniEnd = microtime(true);
          $output['stats']['overview']['time'] = $uniEnd-$uniStart;

          if ($debug){
            echo "<h1>Full Email OUTPUT</h1>";
            var_dump($output);
          }
        }
        return $output;
        // imap_close($imap->conn());
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
        require_once 'CRM/Utils/IMAP.php';
        $debug = self::get('debug');
        $start = microtime(true);

        $UnprocessedQuery = " SELECT *
        FROM `nyss_inbox_messages`
        WHERE `status` = 0
        LIMIT 0 , 100000";

        $UnprocessedResult = mysql_query($UnprocessedQuery, self::db());
        $UnprocessedOutput = array();
        while($row = mysql_fetch_assoc($UnprocessedResult)) {
            $UnprocessedOutput = $row;
            $message_id = $row['id'];
            $returnMessage['Unprocessed'][$message_id] = $UnprocessedOutput;
            $cleanDate = self::cleanDate($row['email_date']);
            $date_short = $cleanDate['short'];
            $date_long =  $cleanDate['long'];
            $date_u =  $cleanDate['u'];
            $returnMessage['Unprocessed'][$message_id]['date_short'] = $date_short;
            $returnMessage['Unprocessed'][$message_id]['date_u'] = $date_u;
            $returnMessage['Unprocessed'][$message_id]['date_long'] = $date_long;
            // usefully when setting status for other unmatched messages
            $returnMessage['Unprocessed'][$message_id]['key'] = $row['sender_email'];

            // find matches
            // lets reuse the search function
            $sender_email = $row['sender_email'];
            $Query="SELECT  contact.id,  email.email FROM civicrm_contact contact
            LEFT JOIN civicrm_email email ON (contact.id = email.contact_id)
            WHERE contact.is_deleted=0
            AND email.email LIKE '$sender_email'
            GROUP BY contact.id
            ORDER BY contact.id ASC, email.is_primary DESC";
            $matches = array();
            $result = mysql_query($Query, self::db());
            while($row = mysql_fetch_assoc($result)) {
              $matches[] = $row;
            }
            $returnMessage['Unprocessed'][$message_id]['matches_count'] = count($matches);
            // attachments
            $attachments= array();
            $AttachmentsQuery ="SELECT * FROM nyss_inbox_attachments WHERE `email_id` = $message_id";
            $AttachmentResult = mysql_query($AttachmentsQuery, self::db());
            while($row = mysql_fetch_assoc($AttachmentResult)) {
              $attachments[$row['id']] = array('filename'=>$row['filename'],'size'=>$row['size'],'ext'=>$row['ext'] );
            }
            $returnMessage['Unprocessed'][$message_id]['attachments']=$attachments;
        }

        $ProcessedQuery = " SELECT count(id)
        FROM `nyss_inbox_messages`
        WHERE `status` = 1";

        $ProcessedResult = mysql_query($ProcessedQuery, self::db());
        while($row = mysql_fetch_assoc($ProcessedResult)) {
            $returnMessage['stats']['overview']['Processed'] = $row['count(id)'];
        }

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

    public function extract_email_address ($string) {
        $debug = self::get('debug');

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
          if ($debug) {

            echo "<h1> extract_email_address Output</h1>";
            echo "Input : ".$string;
            var_dump($return);
          }
          return $return;
        }
    }



    /* getMessageDetails()
     * Parameters: None.
     * Returns: None.
     * This function sets up a connection to the IMAP server with the
     * specified connection ID, and retrieves the message based on UID
     */
    public static function getMessageDetails($id_passed = null, $imap_passed = 0, $internal= null) {
        // Setup the IMAP variables and connect to the IMAP server
        require_once 'CRM/Utils/IMAP.php';
        self::setupImap();

        if(is_int($id_passed)){
          $messageId = ($id_passed) ? $id_passed : self::get('id');
        }else{
          $messageId = self::get('id');
        }

        $imap_id = ($imap_passed != null) ? $imap_passed : self::get('imapId');
        $debug = self::get('debug');

        $imap = new CRM_Utils_IMAP(self::$server,
                                    self::$imap_accounts[$imap_id]['user'],
                                    self::$imap_accounts[$imap_id]['pass']);
        // // Pull the message via the UID and output it as plain text if possible
        // $email = $imap->getmsg_uid($id);

        // $structure = imap_fetchstructure($imap->conn(),$id,SE_UID);

        // $matches = array();

        // emails use a standard formatter
        $output = self::unifiedMessageInfo($imap,$messageId,$imap_id);
        if($debug){
          echo "<h1>Message inputs</h1>";
          var_dump($messageId);
          var_dump($imap_id);
          var_dump($email);
          var_dump($output);
        }

        if($output['code'] != "ERROR" ){
        $returnMessage = array('uid'    =>  $messageId,
                               'imapId' =>  $imap_id,
                               'format' => $output['header']['format'],
                               'forwardedFull' => $output['header']['from'],
                               'forwardedName'   =>  mb_convert_encoding($output['header']['from_name'], 'UTF-8'),
                               'forwardedEmail'  =>  $output['header']['from_email'],
                               'fromName'  =>  mb_convert_encoding($output['forwarded']['origin_name'], 'UTF-8'),
                               'fromEmail' =>  $output['forwarded']['origin_email'],
                               // 'forwardedDate' =>  $output['forwarded']['date_short'],
                               'subject'    =>  mb_convert_encoding($output['forwarded']['subject'], 'UTF-8'),
                               'attachmentfilename'  => $output['attachments'][0]['name'].','.$output['attachments'][1]['name'].','.$output['attachments'][2]['name'].','.$output['attachments'][3]['name'].','.$output['attachments'][4]['name'],
                               'attachmentname'  =>  $output['attachments'][0]['name'].','.$output['attachments'][1]['name'].','.$output['attachments'][2]['name'].','.$output['attachments'][3]['name'].','.$output['attachments'][4]['name'],
                               'attachment'  => $output['attachments']['overview']['total'],
                               'status' =>$output['header']['status'],
                               'email_user' => self::$imap_accounts[$imap_id]['user'],
                               'status' =>$output['header']['status'],
                               'origin_lookup' => $output['forwarded']['origin_lookup'],
                               'header_subject' => $output['header']['subject'],
                               'date_short'   =>  $output['header']['date_short'],
                               'date_long'   =>  $output['header']['date_long'],
                               'forwarder_date_short'   =>  $output['forwarded']['date_short'],
                               'forwarder_date_long'   =>  $output['forwarded']['date_long'],
                               'time' => $output['stats']['overview']['time'],
                               'details'  =>  mb_convert_encoding($output['header']['body'], 'UTF-8'),
                              );

          }else if($output['code'] == "ERROR" ){
            $returnMessage = $output;
          }
        imap_close($imap->conn());

        if($internal){
          return $returnMessage;
        }else{
          echo json_encode($returnMessage);
        }
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
    $date ='';
    // in some emails line breaks don't exist, so parse the string after Date:
    if($count == 1){
      foreach(preg_split('/ /', $matches[2]) as $token) {
        $check = strtolower($token);
        $date .= $check." ";
        if($check == 'am'||$check == 'pm'||$check == 'subject:'||$check == 'from:'||$check == 'to:'){
          break;
        }
      }
    }

    $date_string_short = ($count == 1 ) ? $date  : $date_string;

    // somestrs email clients think its fun to add stuff to the date, remove it here.
    $date_string_short = preg_replace("/ (at) /i", "", $date_string_short);

    if(date('Ymd') == date('Ymd', strtotime($date_string_short))){ $today = true; };

    $yearsago = floor((time() - strtotime($date_string_short))/86400);

    // check if the message is from last year
    if ( (date("Y", strtotime($date_string_short)) - date("Y")) < 0 ){
      $formatted = date("M d, Y", strtotime($date_string_short));
    }else{
      // if the message is from this year, see if the message is from today
      if ($today){
        $formatted = 'Today '.date("h:i A", strtotime($date_string_short));
      }else{
        $formatted = date("M d h:i A", strtotime($date_string_short));
      }
    }
    return array('date_debug'=>$date_string_short,
              'long'=> date("M d, Y h:i A", strtotime($date_string_short)),
              'u'=>date("U", strtotime($date_string_short)),
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
        $imap_id = self::get('imapId');
        $imap = new CRM_Utils_IMAP(self::$server,
                                    self::$imap_accounts[$imap_id]['user'],
                                    self::$imap_accounts[$imap_id]['pass']);
        // Delete the message with the specified UID
        // return standard response
        // check to see if this message exists
        // $email = $imap->getmsg_uid($id);
        $headers = imap_fetch_overview($imap->conn(),$id,FT_UID);
        if(sizeof($headers) != 1 ){
          $returnCode = array('code'      =>  'ERROR',
              'message'   => 'This Message no longer exists', 'clear'=>'true');
        }else{
          $status = $imap->deletemsg_uid($id);
          if($status == true){
            $returnCode = array('code'=>'SUCCESS','status'=> '0','message'=>'Message Deleted');
          }else{
            $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'Could not Delete Message');
          }
        }
        echo json_encode($returnCode);
        CRM_Utils_System::civiExit();
    }

    /* searchContacts
     * Paramters: None.
     * Returns: None.
     * This function will grab the inputs from the GET variable and
     * do a search for contacts and return them as a JSON object.
     * Only returns Records with Primary emails & addresse (so no dupes)
     */
    public static function searchContacts() {
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
        require_once 'CRM/Utils/IMAP.php';
        self::setupImap();
        $debug = self::get('debug');

        $messageUid = self::get('messageId');
        $contactIds = self::get('contactId');
        $imapId = self::get('imapId');
        $imap = new CRM_Utils_IMAP(self::$server, self::$imap_accounts[$imapId]['user'], self::$imap_accounts[$imapId]['pass']);
        // $email = $imap->getmsg_uid($messageUid);

        $output = self::unifiedMessageInfo($imap,$messageUid,$imapId);
 

        // probably could user better names 
        $senderName = ($output['header']['from_name']) ?  $output['header']['from_name'] : '' ;
        $senderEmailAddress = ($output['header']['from_email']) ?  $output['header']['from_email'] : '' ;
        $date = ($output['forwarded']['date_long']) ?  $output['forwarded']['date_long'] : null ;
        $subject = ($output['forwarded']['subject']) ?  $output['forwarded']['subject'] : 'could not find message subject' ;
        $body = ($output['header']['body']) ?  $output['header']['body'] : 'could not find message body' ;
        
        $senderName = mysql_real_escape_string($senderName);
        $senderEmailAddress = mysql_real_escape_string($senderEmailAddress);
        $date = mysql_real_escape_string($date);
        $subject = mysql_real_escape_string($subject);
        $body = mysql_real_escape_string($body);

        if ($debug){
          var_dump($messageUid);
          var_dump($imapId);
          var_dump($imap);
          var_dump($output);
          echo "<h1>inputs</h1>";
          var_dump($senderName);
          var_dump($senderEmailAddress);
          var_dump($date);
          var_dump($subject);
          var_dump($body);
          var_dump($messageUid);
          echo "<h1>Attachments</h1>";
          var_dump($attachments);
        }

        require_once 'api/api.php';

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
WHERE g.title='Authorized Forwarders'
  AND e.email='".$senderEmailAddress."'
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
          echo "<h1>Get forwarder Contact Record for {$senderEmailAddress}</h1>";
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
            }
            // if the records don't match, count it, an if the number is > 1 add the record
            foreach($results as $email) {
                if(strtolower($email['email']) == strtolower($fromEmail)){
                    if ($debug) echo "<p>".$email['email'] ." == ".strtolower($fromEmail)."</p>";
                }else{
                    $matches++;
                    if ($debug) echo "<p>".$email['email'] ." != ".strtolower($fromEmail)."</p>";
                }
            }

            // get contact info for return message
            $ContactInfo = self::contactRaw($contactId);
            $ContactName = $ContactInfo['values'][$contactId]['display_name'];
            if ($debug){
              echo "<h1>Contact Info</h1>";
              var_dump($ContactInfo['values'][$contactId]);
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
              'is_auto' => 0, // we manually add it, right ?
              'status_id' => 2,
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
              var_dump($assignTag);
              $returnCode = array('code'      =>  'ERROR',
              'message'   =>  $assignTag['message']);
              echo json_encode($returnCode);
              CRM_Utils_System::civiExit();
            }else{
                if ($debug){
                  echo "<h1>Message not archived in debug mode, feel free to try again</h1>";
                }else{
                  // Move the message to the archive folder!
                  $imap->movemsg_uid($messageUid, 'Archive');
                  $key =  $output['forwarded']['origin_email'];
                  // check to see it it was deleted
                  $delete_check = self::unifiedMessageInfo($imap,$messageUid,$imapId);
                  if($delete_check['code']=="ERROR"){ // ERROR is what we expect here
                    $returnCode = array('code' =>'SUCCESS','message'=> "Message Assigned to ".$ContactName." ".$output['forwarded']['origin_email'],'key'=>$key);
                    $activity_id =$activity['id'];

                        $UPDATEquery = "UPDATE `nyss_inbox_messages`
                        SET  `status`= 1, `matcher` = $currentUserId, `activity_id` = $activity_id, `matched_to` = $contactId
                        WHERE `message_id` =  {$messageUid} && `imap_id`= {$imapId}";
                        $UPDATEresult = mysql_query($UPDATEquery, self::db());

                        // var_dump($result);
                  }else{
                    $returnCode = array('code' =>  'ERROR','message' =>  "Message was not deleted ");
                  }
                  echo json_encode($returnCode);
                }
            }

            // add attachment to activity
          };
        }
        imap_close($imap->conn());
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
            foreach($activityIds as $activityId) {

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


        $UnprocessedQuery = " SELECT *
        FROM `nyss_inbox_messages`
        WHERE `status` = 1
        LIMIT 0 , 100000";


        $UnprocessedResult = mysql_query($UnprocessedQuery, self::db());
        $UnprocessedOutput = array();
        while($row = mysql_fetch_assoc($UnprocessedResult)) {
            $UnprocessedOutput = $row;
            $message_id = $row['id'];
            $returnMessage['successes'][$message_id] = $UnprocessedOutput;
            $cleanDate = self::cleanDate($row['email_date']);
            $date_short = $cleanDate['short'];
            $date_long =  $cleanDate['long'];
            $date_u =  $cleanDate['u'];
            $returnMessage['successes'][$message_id]['date_short'] = $date_short;
            $returnMessage['successes'][$message_id]['date_u'] = $date_u;
            $returnMessage['successes'][$message_id]['date_long'] = $date_long;
            $returnMessage['successes'][$message_id]['fromEmail'] = $row['sender_email'];

            // getting contact details
            $contactId = $row['matched_to'];
            $contact_info = self::contactRaw($contactId);
            $returnMessage['successes'][$message_id]['contactType'] = $contact_info['values'][$contactId]['contact_type'];
            $returnMessage['successes'][$message_id]['firstName'] = $contact_info['values'][$contactId]['first_name'];
            $returnMessage['successes'][$message_id]['lastName'] = $contact_info['values'][$contactId]['last_name'];
            $returnMessage['successes'][$message_id]['fromName'] = $contact_info['values'][$contactId]['display_name'];
            $returnMessage['successes'][$message_id]['fromdob'] = $contact_info['values'][$contactId]['birth_date'];
            $returnMessage['successes'][$message_id]['fromphone'] = $contact_info['values'][$contactId]['phone'];
            $returnMessage['successes'][$message_id]['fromstreet'] = $contact_info['values'][$contactId]['street_address'];
            $returnMessage['successes'][$message_id]['fromcity'] = $contact_info['values'][$contactId]['city'];

        }

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

    public static function getActivityDetails($id_passed = null, $userId_passed  = null, $internal = null) {

      if(is_int($id_passed)){
        $id = ($id_passed) ? $id_passed : self::get('id');
      }else{
        $id = self::get('id');
      }
      $userId = ($userId_passed) ? $userId_passed : self::get('contact');

      $debug = self::get('debug');
      $tagid = self::getInboxPollingTagId();

      if($debug){
        echo "<h1>Activity Details</h1>";
        var_dump($id);
        var_dump($userId);
      }
       require_once 'CRM/Core/BAO/Tag.php';
      require_once 'CRM/Core/BAO/EntityTag.php';
      require_once 'CRM/Activity/BAO/ActivityTarget.php';
      require_once 'api/api.php';

      //grab the imap user
      self::setupImap();

      // we need to check to see if:
      // the inbox polling tag,
      // assigned to the same contact,
      // exists,
      // contact still exists
      $query = <<<EOQ
SELECT COUNT(id)
FROM `civicrm_entity_tag`
WHERE `entity_id` =  $id
AND `tag_id` = $tagid
EOQ;
      $check_tag = mysql_query($query, self::db());
      if($row = mysql_fetch_assoc($check_tag)) {
        $result_tag = $row['COUNT(id)'];
      }


      if($result_tag != '1' ){
        $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'Activity was Cleared or Deleted, Please Reload','clear'=>'true');
        echo json_encode($returnCode);
        CRM_Utils_System::civiExit();
      }


      $query = <<<EOQ
SELECT COUNT(id)
FROM `civicrm_activity_target`
WHERE `activity_id` = $id
AND `target_contact_id` = $userId
EOQ;
      $check_result = mysql_query($query, self::db());
      if($row = mysql_fetch_assoc($check_result)) {
        $result_target = $row['COUNT(id)'];
      }

      // message to return
      if ($debug){
        var_dump($query);
        var_dump($result_target);
      }

      if($result_target != '1' ){
        $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'Activity is not assigned to this Contact, Please Reload','clear'=>'true');
        echo json_encode($returnCode);
        CRM_Utils_System::civiExit();
      }

      $params = array('version'   =>  3,
                      'activity'  =>  'get',
                      'id' => $id,
      );
      $activity = civicrm_api('activity', 'get', $params);
      $activity_node = $activity['values'][$id];

      if(($activity['is_error']==1) || ($activity['values']==null ) || (count($activity['values']) !=  1 )){
        $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'Activity not found','clear'=>'true');
        echo json_encode($returnMessage);
        CRM_Utils_System::civiExit();
      }

      if ($debug){
        echo "<h1>Activity</h1>";
        var_dump($activity);
       }

      $params = array('version'   =>  3,
                  'activity' => 'get',
                  'id' => $userId,
              );
      $contact = civicrm_api('contact', 'get', $params);
      $contact_node = $contact['values'][$userId];

      if(($contact['is_error']==1) || ($contact['values']==null ) || (count($contact['values']) !=  1 )){
        $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'Activity\'s Contact not found');
        echo json_encode($returnMessage);
        CRM_Utils_System::civiExit();
      }

      if ($debug){
        echo "<h1>Contact</h1>";
        var_dump($contact);
       }

      $params = array('version'   =>  3,
                      'id' => $activity_node['source_contact_id'],
      );

      $forwarder = civicrm_api('contact', 'get', $params );
      $forwarder_node = $forwarder['values'][$activity_node['source_contact_id']];

      $cleanDate = self::cleanDate($activity_node['activity_date_time']);
      $date_short = $cleanDate['short'];
      $date_long =  $cleanDate['long'];
      $date_u =  $cleanDate['u'];

      $returnMessage = array('code'=>'SUCCESS','message'=>'SUCCESS',
                          'uid'    =>  $id,
                          'contactId' =>  $contact_node['contact_id'],
                          'contactType'   =>  $contact_node['contact_type'],
                          'fromName'   =>  $contact_node['display_name'],
                          'fromEmail'  =>  $contact_node['email'],
                          'fromId'  =>  $contact_node['id'],
                          'forwardedName' => $forwarder_node['display_name'],
                          'forwardedEmail' => $forwarder_node['email'],
                          'subject'    =>  $activity_node['subject'],
                          'match_type'  =>  $activity_node['is_auto'],
                          'original_id'  =>  $activity_node['original_id'],
                          'email_user' => self::$imap_accounts[0]['user'], // not ideal for the hardcoded 0
                          'date_short'   =>  $date_short,
                          'date_long' =>$date_long,
                          'details'  =>  $activity_node['details']
                          );
        if($internal){
          return $returnMessage;
        }else{
          echo json_encode($returnMessage);
        }
      CRM_Utils_System::civiExit();
    }
    // delete activit and enttity ref
    public static function deleteActivity() {
        require_once 'api/api.php';
        $id = self::get('id');
        $tagid = self::getInboxPollingTagId();
        $error = false;
        $debug = self::get('debug');

        if($debug){
          sleep(2);
          $returnCode = array('code'=>'SUCCESS','id'=>$id, 'message'=>'Activity Deleted');
          echo json_encode($returnCode);
          exit();
        }

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
          $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'Activity not found','clear'=>'true');
        }
        echo json_encode($returnCode);

        mysql_close(self::$db);
        CRM_Utils_System::civiExit();
    }

    // remove the activity tag
    public static function untagActivity() {
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
          $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'Activity not found','clear'=>'true');
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
      $email = $changeData['values'][$change]['email'];
      $tagid = self::getInboxPollingTagId();

      // we need to check to see if the activity is still assigned to the same contact
      // if not, kill it

      $query = <<<EOQ
SELECT COUNT(id)
FROM `civicrm_entity_tag`
WHERE `entity_id` =  $id
AND `tag_id` = $tagid
EOQ;
      $check_tag = mysql_query($query, self::db());
      if($row = mysql_fetch_assoc($check_tag)) {
        $result_tag = $row['COUNT(id)'];
      }

      if($result_tag != '1' ){
        $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'Activity was cleared, Please Reload','clear'=>'true');
        echo json_encode($returnCode);
        CRM_Utils_System::civiExit();
      }

      $query = <<<EOQ
SELECT COUNT(id)
FROM `civicrm_activity_target`
WHERE `activity_id` = $id
AND `target_contact_id` = $contact
EOQ;
      $check_result = mysql_query($query, self::db());
      if($row = mysql_fetch_assoc($check_result)) {
      $check = $row['COUNT(id)'];
      }

      if($check != '1'){
        $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'Activity is not assigned to this Contact, Please Reload','clear'=>'true');
        echo json_encode($returnCode);
        CRM_Utils_System::civiExit();
      }

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

                        $Source_update = <<<EOQ
UPDATE `civicrm_activity`
SET  `is_auto`= 0
WHERE `id` =  $id
EOQ;

            // change the row
            $Source_results = mysql_query($Source_update, self::db());
            while($row = mysql_fetch_assoc($Source_results)) {
                 $results[] = $row;
            }

            $returnCode = array('code'=>'SUCCESS','id'=>$id,'contact_id'=>$change,'contact_type'=>$contactType,'first_name'=>$firstName,'last_name'=>$LastName,'display_name'=>$changeName,'email'=>$email,'activity_id'=>$row_id,'message'=>'Activity Reassigned to '.$changeName);
        }else{
            $returnCode = array('code'=>'ERROR','status'=> '1','message'=>'Activity not found');

        }

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

    public static function fileBug() {
      require_once 'api/api.php';
      require_once 'CRM/Utils/IMAP.php';
      require_once 'CRM/Utils/Redmine.php';

      self::setupImap();

      $id =  self::get('id');
      $imap =  self::get('imap');
      $url =  self::get('url');
      $type =  self::get('type');
      $browser =  self::get('browser');

      $instance = self::$imap_accounts[0]['user'];
      $session = CRM_Core_Session::singleton();
      $userId =  $session->get('userID');
      $ContactInfo = self::contactRaw($userId);
      $ContactName = $ContactInfo['values'][$userId]['display_name'];

      // var_dump($id);
      // var_dump($imap);
      // var_dump($url);
      // var_dump($browser);
      // var_dump($type);
      // var_dump($instance);
      // var_dump($userId);
      // var_dump($ContactName);

      if($type == "Activity"){
        $body_raw = self::getActivityDetails($id,$imap,true);
      }elseif($type == "Message"){
        $body_raw = self::getMessageDetails($id,$imap,true);
      }
      // build body with all the data
      $body = '';
      foreach ($body_raw as $key => $value) {
        $body.= $key.' : '.$value."\n";
      }

      // echo('<pre>'.$body.'</pre>');

      $project_id = 62; // blue bird project id
      $category_id = 40; // inbox polling 40
      $assigned_to_id = 184; // me 184 // dean 14 // jason 22 // scott 29
      $subject = "Automatic Issue created by ".$ContactName." in ".$instance;
      $open_date = date("U");


      $config['url'] = "http://dev.nysenate.gov/";
      $config['apikey'] = '5c253defa3935717d9cd8a8c9ea9996efee3e8ed';
      $_redmine = new redmine($config);

      // // List all Projects
      $projects = $_redmine->getProjects();
      foreach($projects->project as $project) var_dump($project);

      // // Get userId
      $userId = $_redmine->getUserId('username');
       $assigned_to_id = array('184' =>'184');
      // // Add an Issue
      // // ($subject, $description, $project_id, $category_id, $assignmentUsernames, $due_date, $priority_id) {
      $addedIssueDetails = $_redmine->addIssue('API tests', 'body', '41', $assigned_to_id, '1', '', '1');
      $addIssueID = (int)$addedIssueDetails->id;

      var_dump($addIssueID);
      // // Add an note to the issue
      // $_redmine->addNoteToIssue($addIssueID, "this is a new message");

      // // Close the issue
      // $_redmine->setIssueStatus(true, $addIssueID);

      // // Finnaly get the Link
      print $_redmine->getTrackerItemLink($addIssueID);


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
          var_dump($dob);
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
            'email' => $email,
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

