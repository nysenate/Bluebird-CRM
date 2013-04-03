<?php
// A class Needed for the processMailboxes.php script,
//
class parseMessageBody {

  /* unifiedMessageInfo()
  * Parameters: $body, the body text of an email (html or plaintex)
  * Returns: An Object of the headers in message in tree format
  */
  public static function unifiedMessageInfo($body) {
    $uniStart = microtime(true);

    // check for html
    if($body != self::strip_HTML_tags($body)){
      $format = 'html';
    }else{
      $format = 'plain';
    }

    $body = quoted_printable_decode($body);
    $body = preg_replace("/\\t/i", " ", $body);

    if($format =='plain'){
      $tempbody = preg_replace("/>|</i", "", $body);
    }else{
      $tempbody = preg_replace("/<br>/i", "\r\n<br>\n", $body);
      $tempbody = preg_replace("/\\r|\\n|\\t/i", "\r\n<br>\n", $tempbody);
    }
    $tempbody = self::strip_HTML_tags($tempbody);

    // searching message for tree of embedded headers
    $bodyArray = explode("\r\n", $tempbody);
    $possibleHeaders = "subject|from|to|sent|date|cc|bcc";
    $count = 0; // count of embedded message headers
    foreach ($bodyArray as $key => $line) {
      $line = trim($line);
      if($line != ''){
        $line = preg_replace("/mailto/i", "", $line); // this matched /to/
        $line = preg_replace("/Reply To|reply to|Replyto/i", "", $line); // this matched /to/

        if (preg_match('/('.$possibleHeaders.'):([^\r\n]*)/i', $line, $matches)){
          $header = strtolower($matches[1]);
          $value = trim($matches[2]);

          $dateValue = preg_replace("/ at |,/i", "", $value); // Remove errors caused by at
          $parseDate= date("Y-m-d H:i:s", strtotime($dateValue));

          switch ($header) {
            case 'subject':
                $m['Subject'][] = trim($value);
              break;
            case 'from':
                $parseEmail= self::cleanEmail($value);
                $m['From'][] = $parseEmail;
              break;
            case 'to':
              // $m[$count]['To'] = $value;
            break;
            case 'sent':
            case 'date':
                $m['Date'][] = $parseDate;
              break;
            case 'cc':
                $m['Cc'][] = trim($value);
              break;
            case 'bcc':
                $m['Bcc'][] = trim($value);
              break;
            default:
              break;
          }
        }
      }
    }

    // at this point, $m returns full tree, but we don't need it

    // // maybe useful at some point
    // // search the message for other emails,
    // // SO 3901070
    // $pattern = '/[a-z0-9_\-\+]+@[a-z0-9\-]+\.([a-z]{2,20})(?:\.[a-z]{3})?/i';
    // preg_match_all($pattern, $tempbody, $matches);
    // $mentionsCount = array();
    // // look for a previous mention, add them up.
    // foreach ($matches[0] as $key => $value) {
    //   if(trim($value) != ''){
    //     if($mentions[$value]){
    //       $mentions[$value] = $mentions[$value]+1;
    //     }else{
    //       $mentions[$value] = 1;
    //     }
    //   }
    // }

    $fwdDate = $m['Date'][0];
    $fwdSubject = $m['Subject'][0];
    $fwdName = $m['From'][0]['name'];
    $fwdEmail = $m['From'][0]['email'];
    $fwdEmailLookup = $m['From'][0]['lookupType'];
    $fwdSubject = trim(preg_replace("/(\(|\))/i", "", $fwdSubject));

    // contains info about the forwarded message in the email body
    $forwarded = array(
        'fwd_date' => mysql_real_escape_string($fwdDate),
        'fwd_subject' => mysql_real_escape_string($fwdSubject),
        'fwd_name' => mysql_real_escape_string($fwdName),
        'fwd_email' => mysql_real_escape_string($fwdEmail),
        'fwd_lookup' => mysql_real_escape_string($fwdEmailLookup),
        'fwd_mentions' =>mysql_real_escape_string($mentions),
    );


    // custom body parsing for mysql entry,
    // $body is perserved as much as possible for viewing
    $body = self::strip_HTML_tags($body);

    // use a placeholder to mark linebreaks / br tags
    $body = preg_replace('/\r\n|\r|\n/i', '#####---', $body);
    $body = preg_replace('/(<br[^>]*>\s*){1,}/', '#####---', $body);

    $body = preg_replace('/> /i', '', $body);
    $body = preg_replace('/ <|>/i', ' ', $body);
    $body = preg_replace('/#####---/i', '<br/>', $body);
    // find more then 3 br tags in a row
    $body = preg_replace('/(<br[^>]*>\s*){3,}/', "<br/>", $body);

    // maybe im a type nerd, but proper quotes are important
    $body = preg_replace('/\'/', '&#8217;', $body);
    $body = preg_replace('/ "/', ' &#8220;', $body);
    $body = preg_replace('/" |"$/', '&#8221; ', $body);
    $body = preg_replace('/"\\n|"\\r/', '&#8221;<br/>', $body);

    // final cleanup
    $body = mysql_real_escape_string($body);
    $body = mb_convert_encoding($body, 'ISO-8859-1');
    // var_dump()
    if($forwarded['fwd_email'] == '' || $forwarded['fwd_email'] == NULL){
      $status =  'direct';
    }else{
      $status ='forwarded';
      $output['fwd_headers'] = $forwarded;
    }
    $uniEnd = microtime(true);
    $output['message_action'] = $status;
    $output['format'] = $format;
    $output['time'] = $uniEnd-$uniStart;
    $output['body'] = $body;
    return $output;
  }

  // Parse and find LDAP & standard format emails
  public static function cleanEmail ($string) {
    // we have to parse out ldap stuff because sometimes addresses are
    // embedded and, see NYSS #5748 for more details

    // if o= is appended to the end of the email address remove it
    $string = preg_replace('/\/senate@senate/i', '/senate', $string);
    $string = preg_replace('/\/CENTER\/senate/i', '/senate', $string);

    $string = preg_replace('/mailto|\(|\)|:/i', '', $string);
    $string = preg_replace('/"|\'/i', '', $string);
    $string = preg_replace('/\[|\]/i', '', $string);

    // ldap addresses have slashes, so we do an internal lookup
    $internal = preg_match("/\/senate/i", $string, $matches);

    if($internal == 1){
      $ldapcon = ldap_connect("ldap://webmail.senate.state.ny.us", 389);
        $retrieve = array("sn","givenname", "mail");
        $search = ldap_search($ldapcon, "o=senate", "(displayname=$string)", $retrieve);
        $info = ldap_get_entries($ldapcon, $search);
      if($info[0]){
        $name = $info[0]['givenname'][0].' '.$info[0]['sn'][0];
        $return = array('lookupType'=>'LDAP','name'=>$name,'email'=>$info[0]['mail'][0]);
        return $return;
      }else{
        $return = array('lookupType'=>'LDAP FAILURE','name'=>'LDAP lookup Failed','email'=>'LDAP lookup Failed on string '.$string);
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
      $return = array('lookupType'=>'inline','name'=>$name,'email'=>$emails[0]);
      return $return;
    }
  }

  // Strips HTML 4.01 start and end tags. Preserves contents.
  // modified to not strip br tags
  public static function strip_HTML_tags($text){
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



}

