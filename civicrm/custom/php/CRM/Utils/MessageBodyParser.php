<?php

define('BODY_TAGS_TO_SKIP', '
        ABBR|ACRONYM|ADDRESS|APPLET|AREA|A|BASE|BASEFONT|BDO|BIG|
        BLOCKQUOTE|BODY|BUTTON|B|CAPTION|CENTER|CITE|CODE|COL|
        COLGROUP|DD|DEL|DFN|DIR|DL|DT|EM|FIELDSET|FONT|FORM|
        FRAME|FRAMESET|H\d|HEAD|HTML|IFRAME|INPUT|INS|
        ISINDEX|I|KBD|LABEL|LEGEND|LI|LINK|MAP|MENU|META|NOFRAMES|
        NOSCRIPT|OBJECT|OL|OPTGROUP|OPTION|PARAM|PRE|P|Q|SAMP|
        SCRIPT|SELECT|SMALL|STRIKE|STRONG|STYLE|SUB|SUP|S|SPAN|
        TEXTAREA|TITLE|TT|U|UL|VAR');

define('HEAD_TAGS_TO_SKIP', BODY_TAGS_TO_SKIP.'|
	BR|DIV|HR|IMG|TABLE|TD|TBODY|TFOOT|TH|THEAD|TR');


class MessageBodyParser
{
  /* unifiedMessageInfo()
  * Parameters: $origin, the body text of an email (HTML or PlainText)
  * Returns: An object of the headers in message in tree format
  */
  public static function unifiedMessageInfo($origin)
  {
    $timeStart = microtime(true);

    // prefer html, because its not broken into lines
    if (isset($origin['HTML']['body'])) {
      $start = $origin['HTML']['body'];
      $format = 'html';
      $encoding = $origin['HTML']['encoding'];
    }
    elseif (isset($origin['PLAIN']['body'])) {
      $start = $origin['PLAIN']['body'];
      $format = 'plain';
      $encoding = $origin['PLAIN']['encoding'];
    }

    if ($encoding == 0) {
      //$start = imap_7bit($start);
    }
    elseif ($encoding == 1) {
      $start = quoted_printable_decode(imap_8bit($start));
    }
    elseif ($encoding == 2) {
      $start = imap_base64(imap_binary($start));
    }
    elseif ($encoding == 3) {
      $start = imap_base64($start);
    }
    elseif ($encoding == 4) {
      $start = quoted_printable_decode($start);
    }

    $headerCheck = substr($start, 0, 2500);
    $headerCheck = preg_replace("/\\t/i", " ", $headerCheck);
    $patterns = array('/\r\n|\r|\n/i', '/(<(br)[^>]*>\s*)+/i');
    $headerCheck = preg_replace($patterns, '#####---', $headerCheck);
    $headerCheck = self::stripTagsForHeader($headerCheck);
    $headerCheck = html_entity_decode($headerCheck);
    $headerCheck = self::stripTagsForHeader($headerCheck);
    $headerCheck = preg_replace('/#####---/', "\r\n", $headerCheck);
    $bodyArray = explode("\r\n", $headerCheck);

    $possibleHeaders = "subject|from|to|sent|date|cc|bcc|sent by";

    $Line = array();
    $BlockLines = array();
    $currentHeader=0;
    $HeaderBlocks = array();

    // search body line by line for headers 
    foreach ($bodyArray as $key => $line) {
      if (preg_match('/('.$possibleHeaders.'):([^\r\n]*)/i', $line, $matches)) {
        // if we find a header, we start the header section
        if (!isset($BlockLines[$currentHeader]['start'])) {
          $BlockLines[$currentHeader]['start']=$key;
        }
      }else{
	if (strip_tags(trim($line) == '')){
          // if there is an open header, and we have an empty line, thats the end of a deader
          if ((!isset($BlockLines[$currentHeader]['stop'])) && (isset($BlockLines[$currentHeader]['start']))) {
            $BlockLines[$currentHeader]['stop']=$key;
            $currentHeader++;
          }
        }
      }
    }
    $LastHeader = '';

    // loop through header blocks, combine multi line headers
    foreach ($BlockLines as $id => $payload) {
      foreach ($bodyArray as $key => $line) {
        if($key >= $payload['start'] && $key < $payload['stop']){
          if (preg_match('/('.$possibleHeaders.'):([^\r\n]*)/i', $line, $matches)) {
            $LastHeader = strtolower($matches[1]);
            $value = trim($matches[2]);
            $HeaderBlocks[$id][$LastHeader] = $value;
          }else{
            $HeaderBlocks[$id][$LastHeader] = $HeaderBlocks[$id][$LastHeader]." ".$line;
          }

        }
      }
    }

    //var_dump($HeaderBlocks);

    // So we have single lines now, lets output it !
    $m=array();
    foreach ($HeaderBlocks as $id => $block) {
      foreach ($block as $header => $value) {
        switch ($header) {
          case 'subject':
            $m[$id]['Subject'] = trim($value);
            break;
          case 'from':
            $parseEmail = self::parseFromHeader(trim($value));
            $m[$id]['From'] = $parseEmail;
            break;
          case 'to':
            $m[$id]['To'] = trim($value);
            break;
          case 'sent': case 'date':
            // Remove errors caused by "at" or ","
            $dateValue = preg_replace('/ at |,/i', '',  trim($value));
            $parseDate = date("Y-m-d H:i:s", strtotime($dateValue));
            $m[$id]['Date'] = $parseDate;
            break;
          case 'cc':
            $m[$id]['Cc'] = trim($value);
            break;
          case 'bcc':
            $m[$id]['Bcc'] = trim($value);
            break;
          default:
            break;
        } 
      }
    }
    // only grab the details from the first header in the message
    $fwdDate = $m[0]['Date'];
    $fwdName = trim($m[0]['From']['name']);
    $fwdEmail = $m[0]['From']['email'];
    $fwdEmailLookup = $m[0]['From']['lookupType'];
    $fwdSubject = trim($m[0]['Subject']);


    // Remove all parentheses from the subject
    $fwdSubject = trim(preg_replace('/[()]/i', '', $fwdSubject));

    // contains info about the forwarded message in the email body
    $forwarded = array(
        'fwd_date' => mysql_real_escape_string($fwdDate),
        'fwd_subject' => mysql_real_escape_string($fwdSubject),
        'fwd_name' => mysql_real_escape_string($fwdName),
        'fwd_email' => mysql_real_escape_string($fwdEmail),
        'fwd_lookup' => mysql_real_escape_string($fwdEmailLookup),
    );

    // custom body parsing for mysql entry,
    $body = $start;
    // strip out non-ascii characters
    $body = nl2br($body);
    $body = preg_replace('/[^(\x20-\x7F)]*/', '', $body);
    $body = preg_replace('/<([\w.]+@[\w.]+)>/i', '$1', $body);

    // remove classes & styles 
    $body = preg_replace( '/style=(["\'])[^\1]*?\1/i', '', $body);
    $body = preg_replace( '/class=(["\'])[^\1]*?\1/i', '', $body);
    $body = preg_replace( '/onclick=(["\'])[^\1]*?\1/i', '', $body);
    $body = preg_replace( '/title=(["\'])[^\1]*?\1/i', '', $body);

    // final cleanup
    $body = html_entity_decode($body); 
    $body = self::stripBodyTags($body);
    $body = preg_replace('~<\s*\bscript\b[^>]*>(.*?)<\s*\/\s*script\s*>~is', '', $body);
    $body = addslashes($body);

    if (trim($body) == '') {
      $body = "No Message Content Found";
    }

    if ($forwarded['fwd_email'] == '' || $forwarded['fwd_email'] == null) {
      $status = 'direct';
    }
    else {
      $status = 'forwarded';
      $res['fwd_headers'] = $forwarded;
    }

    $timeEnd = microtime(true);

    $res['message_action'] = $status;
    $res['format'] = $format;
    $res['time'] = $timeEnd - $timeStart;
    $res['body'] = $body;
    return $res;
  } // unifiedMessageInfo()



  // Parse and find LDAP & standard format emails
  public static function parseFromHeader($str)
  {
    // Parse LDAP info because sometimes addresses are embedded.
    // See NYSS #5748 for more details.

    // if o= is appended to the end of the email address remove it
    $patterns = array('#/senate@senate#i', '#/CENTER/senate#i');
    $str = preg_replace($patterns, '/senate', $str);

    // CN=Jason Biernacki/OU=STS11thFloor/O=senate
    // CN=Personnel Mail/O=senate
    $str = preg_replace('/CN=|O=|OU=/i', '', $str);
    $str = preg_replace('/mailto|\(|\)|:/i', '', $str);
    $str = preg_replace('/"|\'/i', '', $str);
    $str = preg_replace('/\[|\]/i', '', $str);

    $internal = strpos($str, '/senate');

    if ($internal !== false) {
      // LDAP addresses have slashes, so we do an internal lookup
      $ldapcon = ldap_connect("ldap://webmail.senate.state.ny.us", 389);
      $retrieve = array('sn', 'givenname', 'mail');
      $search = ldap_search($ldapcon, 'o=senate', "(displayname=$str)", $retrieve);
      $info = ldap_get_entries($ldapcon, $search);
      if ($info[0]) {
        $name = $info[0]['givenname'][0].' '.$info[0]['sn'][0];
        $res = array('lookupType'=>'LDAP', 'name'=>$name, 'email'=>$info[0]['mail'][0]);
        return $res;
      }
      else {
        $res = array('lookupType'=>'LDAP FAILURE', 'name'=>'LDAP lookup failed', 'email'=>"LDAP lookup failed on string $str");
        return $res;
      }
    }
    else {
      // clean out any anything that wouldn't be a name or email, html or plain-text
      $str = preg_replace('/&lt;|&gt;|&quot;|&amp;/i', '', $str);
      $str = preg_replace('/<|>|"|\'/i', '', $str);
      foreach (preg_split('/ /', $str) as $token) {
        $name .= $token." ";
        $email = filter_var(filter_var($token, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);
        if ($email !== false) {
          $emails[] = $email;
          break; // only want one match
        }
      }
      $name = trim(str_replace($emails[0], '', $name));
      $res = array('lookupType'=>'inline', 'name'=>$name, 'email'=>$emails[0]);
      return $res;
    }
  } // parseFromHeader()



  public static function stripTags($text, $tagNames)
  {
    return preg_replace('%
        # Match an opening or closing HTML 4.01 tag.
        </?                  # Tag opening "<" delimiter.
        (?:                  # Group for HTML 4.01 tags.
        '.$tagNames.'
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
        /?                   # Tag may be empty (with self-closing "/>" sequence
        >                    # Opening tag closing ">" delimiter.
        | <!--.*?-->         # Or a (non-SGML compliant) HTML comment.
        | <!DOCTYPE[^>]*>    # Or a DOCTYPE.
        %six', '', $text);
  } // stripTags()



  // modified to not strip tags needed to display html message
  public static function stripBodyTags($text)
  {
    return self::stripTags($text, BODY_TAGS_TO_SKIP);
  } // stripBodyTags()



  // header doesn't need any of these, so blow them away.
  public static function stripTagsForHeader($text)
  {
    return self::stripTags($text, HEAD_TAGS_TO_SKIP);
  } // stripTagsForHeader()
}
