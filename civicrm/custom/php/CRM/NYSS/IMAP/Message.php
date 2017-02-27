<?php

class CRM_NYSS_IMAP_Message
{
  const MAX_SUBJ_LEN = 255;

  private static $_body_type_labels = array(
    TYPETEXT => 'text',
    TYPEMULTIPART => 'multipart',
    TYPEMESSAGE => 'message',
    TYPEAPPLICATION => 'application',
    TYPEAUDIO => 'audio',
    TYPEIMAGE => 'image',
    TYPEVIDEO => 'video',
    TYPEMODEL => 'model',
    TYPEOTHER => 'other');


  /* Credit to http://www.regular-expressions.info/email.html
     See discussion at the above link regarding the effectiveness/thoroughness
     of the pattern.  IT WILL NOT CATCH ALL EMAIL ADDRESSES, but it does match
     99% of RFC5322-compliant addresses.  Also, detections are not
     necessarily VALID.
  */
  private static $_email_address_regex =
    /* mailbox */
    '/([a-z0-9!#$%&\'+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*)' .
    /* at */
    '@' .
    /* host */
    '((?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)/i';

  private static $_html_tags_to_strip = array(
    'ABBR','ACRONYM','ADDRESS','APPLET','AREA','A','BASE','BASEFONT',
    'BDO','BIG','BLOCKQUOTE','BODY','BUTTON','B','CAPTION','CENTER',
    'CITE','CODE','COL','COLGROUP','DD','DEL','DFN','DIR','DL','DT',
    'EM','FIELDSET','FONT','FORM','FRAME','FRAMESET','H\d','HEAD',
    'HTML','IFRAME','INPUT','INS','ISINDEX','I','KBD','LABEL','LEGEND',
    'LI','LINK','MAP','MENU','META','NOFRAMES','NOSCRIPT','OBJECT',
    'OL','OPTGROUP','OPTION','PARAM','PRE','P','Q','SAMP','SCRIPT',
    'SELECT','SMALL','STRIKE','STRONG','STYLE','SUB','SUP','S','SPAN',
    'TEXTAREA','TITLE','TT','U','UL','VAR');

  private $_session = null;
  private $_msgnum = 0;
  private $_uid = 0;
  private $_headers = null;
  private $_metadata = null;
  private $_structure = null;
  private $_content = array('text' => null, 'attachment' => null);
  private $_has_attachments = null;


  public function __construct($imapSession, $msgnum = 0)
  {
    $this->_session = $imapSession;
    $this->_msgnum = $msgnum;
    $this->_uid = imap_uid($this->getConnection(), $msgnum);
    // Pre-populate the _headers, _metadata, and _structure properties.
    $this->_headers = imap_headerinfo($this->getConnection(), $msgnum, 0, self::MAX_SUBJ_LEN);
    $this->_metadata = $this->_genMetaData($this->_headers);
    $this->_structure = imap_fetchstructure($this->getConnection(), $this->_msgnum);
    // Now that headers and structure are cached, continue loading.
    if ($this->isMultipart()) {
      $this->_structure->parts = $this->_flattenParts($this->_structure->parts);
      $this->_has_attachments = $this->_hasAttachments();
    }
    else {
      $this->_has_attachments = false;
    }
    // Finally, pre-load the text content, which is necessary for parsing.
    $this->_loadTextContent();
  } // __construct()


  public function getConnection()
  {
    return $this->_session->getConnection();
  } // getConnection()


  public function isMultipart()
  {
    return isset($this->_structure->parts);
  } // isMultipart()


  public function hasAttachments()
  {
    return $this->_has_attachments;
  } // hasAttachments()


  // Cache the message attachments after retrieving them on the first call.
  // The array key for each attachment is its part number.
  // Each attachment has 'name', 'size', and 'data' attributes.
  public function fetchAttachments()
  {
    if ($this->_content['attachment'] === null) {
      $this->_content['attachment'] = array();
      if ($this->hasAttachments()) {
        foreach ($this->getParts() as $partnum => $part) {
          if ($part->ifdisposition && $part->disposition == 'attachment') {
            $content = $this->fetchPart($partnum);
            $content = $this->_decodeContent($content, $part->encoding, $part->subtype);
            // Extract filename from the "dparameters" field of the part.
            $filename = $this->_getFilename($part->dparameters);
            if (!$filename) {
              // If that didn't work, try the "parameters" field.
              $filename = $this->_getFilename($part->parameters);
            }
            if (!$filename) {
              // Skip any attachment whose filename cannot be determined.
              continue;
            }
            $tempfilename = imap_mime_header_decode($filename);
            for ($i = 0; $i < count($tempfilename); $i++) {
              $filename = $tempfilename[$i]->text;
            }

            $attachment = (object) [ 'name' => $filename,
                                     'type' => $part->type,
                                     'size' => $part->bytes,
                                     'data' => $content ];
            $this->_content['attachment'][$partnum] = $attachment;
          }
        }
      }
    }
    return $this->_content['attachment'];
  } // fetchAttachments()


  public function fetchBody($section = '')
  {
    return imap_fetchbody($this->getConnection(), $this->_msgnum, $section, FT_PEEK);
  } // fetchBody()


  public function fetchPart($section = '1')
  {
    return $this->fetchBody($section);
  } // fetchPart()


  public function getHeaders()
  {
    return $this->_headers;
  } // getHeaders()


  public function getMetaData()
  {
    return $this->_metadata;
  }


  public function getStructure()
  {
    return $this->_structure;
  } // getStructure()


  public function getParts()
  {
    if ($this->isMultipart()) {
      return $this->_structure->parts;
    }
    else {
      return null;
    }
  } // getParts()


  // Returns an array of content elements.  Each element corresponds to
  // a message part that matches the provided type.
  public function getContent($type = null)
  {
    if ($type) {
      if (isset($this->_content[$type])) {
        return $this->_content[$type];
      }
      else {
        return null;
      }
    }
    else {
      return $this->_content;
    }
  } // getContent()


  public function getTextContent()
  {
    return $this->getContent('text');
  } // getTextContent()


  // Return the string representation of the given body type.
  public function getBodyTypeLabel($bodyType)
  {
    if (isset(self::$_body_type_labels[$bodyType])) {
      return self::$_body_type_labels[$bodyType];
    }
    else {
      return 'unknown';
    }
  } // getBodyTypeLabel()


  /**
  ** This function attempts to find various sender addresses in the email.
  ** It returns an array with 3 levels of addresses: primary, secondary, other
  ** The "primary" element contains an array of one element, and that element
  ** is the official sender of the email, based on the headers.
  ** The "secondary" element contains any email addresses in the body of the
  ** message that were extracted from apparent "From:" headers.  This should
  ** include the email address of the original sender if the message is a
  ** forwarded message.
  ** Finally, the "other" element contains an array of all email addresses
  ** that could be found in the body, whether or not they were part of a
  ** "From:" header.
  */
  public function findFromAddresses()
  {
    $addr = array(
      'primary' => array(
        'address'=>$this->_headers->from[0]->mailbox.'@'.$this->_headers->from[0]->host,
        'name'=>isset($this->_headers->from[0]->personal) ? $this->_headers->from[0]->personal : '',
      ),
      'secondary' => array(),
      'other' => array(),
    );

    $parts = $this->getParts();

    foreach ($this->getTextContent() as $content) {
      $matches = array();
      if (preg_match_all('/(^|\n)([\ \t]*([>*][\ \t]*)?)?(From|Reply-To):[\*\s]*(("(\\\"|[^"])*")?[^@]{2,100}@(.*))/i', $content, $matches)) {
        foreach ($matches[5] as $k => $v) {
          $v = str_replace(array("\n","\r"), array(' ',''), $v);
          if (preg_match('#CN=|OU?=|/senate#', $v)) {
            //error_log("resolving -$v- with LDAP");
            $v = $this->_resolveLDAPAddress($v);
            //error_log("resolved to -$v-");
          }
          $ta = imap_rfc822_parse_adrlist($v, '');
          if (count($ta) && $ta[0]->host && $ta[0]->mailbox && $ta[0]->host != '.SYNTAX-ERROR.') {
            $newta = array(
                  'address' => $ta[0]->mailbox.'@'.$ta[0]->host,
                  'name' => isset($ta[0]->personal) ? $ta[0]->personal : null);
            switch (strtoupper($matches[2][$k])) {
              case 'REPLY TO':
                array_unshift($addr['secondary'], $newta);
                break;
              default:
                $addr['secondary'][] = $newta;
                break;
            }
          }
        }
      }

      $matches = array();
      if (preg_match_all(static::$_email_address_regex, $content, $matches)) {
        foreach ($matches[0] as $k => $v) {
          $tv = filter_var(filter_var($v, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);
          if ($tv != $addr['primary']['address']) {
            $addr['other'][] = $tv;
          }
        }
      }
    }
    return $addr;
  } // findFromAddresses()


  public function mangleHTML()
  {
    // get the primary content
    $body = $this->_getPrimaryContent();

    // change newlines to <br/>
    $body = nl2br($body);

    $patterns = array(
      '/<([-\w.]+@[-\w.]+)>/i',       /* standardize email addresses */
      '/\<p(\s*)?\/?\>/i',            /* paragraph elements */
      '/[^(\x20-\x7F)]*/',            /* non-ascii characters */
      '/(style|class|on[a-z]+|title|href)=(["\'])[^\2]*?\2/i', /* HTML attrs */
      '~<\s*\bscript\b[^>]*>(.*?)<\s*\/\s*script\s*>~is',      /* script elem */
    );
    $replace = array ('$1', '<br /><br />', '', '', '');
    $body = trim(preg_replace($patterns, $replace, $body));
    return $this->_stripHTML($body);
  } // mangleHTML()


  /*
  ** This is a heavy-cost email content parser to find groups of headers within
  ** a forwarded email.  It is assumed that all header groups will be in
  ** reverse date order, with the most recent forward found first and the
  ** original message headers found last.  This should only be used if the
  ** "standard" method (see method findFromAddresses()) is unable to locate
  ** an original sender.  This scenario generally arises because the forwarded
  ** headers have been mangled by the sending server (quoted printable
  ** conversions, or other issues).
  */
  public function searchFullHeaders()
  {
    /* Forwarding clients may mangle the headers away from spec.  The pattern
    ** to detect a header line is best-guess based on known mangling patterns.
    ** The tracked_headers array needs to include only the mangled versions of
    ** desired headers - any still adhering to spec will be picked up normally
    */
    $tracked_headers = array('reply to', 'sent by');
    $tracked_headers_regex = '('.implode('|',$tracked_headers).'|[!-9;-~]+)';
    // get the primary content
    $content = $this->_getPrimaryContent();
    // initialize loop variables
    $headers = array();
    $header_block = 0;
    $in_header = false;
    $pattern = '([!-9;-~]+|'.implode('|',$tracked_headers).')';
    // read each line of the content and parse for a header
    foreach (explode("\n", $content) as $k => $v) {
      $trimv = trim($v);
      $matches = array();
      if (!$trimv) {
        $in_header = false;
      }
      elseif (preg_match('/^'.$tracked_headers_regex.':[[:space:]]*(.*)$/i', $trimv, $matches)) {
        if (!$in_header) {
          $in_header = true;
          $headers[++$header_block] = array();
        }
        $headers[$header_block][] = array(1 => $matches[1], 2 => $matches[2]);
      }
      elseif ($in_header) {
        $headers[$header_block][count($headers[$header_block])-1][2].= " $trimv";
      }
    }
    return $headers;
  } // searchFullHeaders()


  // Using the message headers, generate a meta data object.
  private function _genMetaData($headers)
  {
    //deal with various special characters that create problems
    $fsubj = $headers->fetchsubject;
    if (strpos($fsubj, '?UTF-8?') !== false) {
      $fsubj = mb_convert_encoding(mb_decode_mimeheader($fsubj), 'HTML-ENTITIES', 'UTF-8');
      //convert some special characters manually
      $search = array('&rsquo;');
      $replace = array("'");
      $fsubj = str_replace($search, $replace, $fsubj);
    }

    $fl_r = $headers->Recent ? $headers->Recent : '-';
    $fl_u = $headers->Unseen ? $headers->Unseen : '-';
    $fl_f = $headers->Flagged ? $headers->Flagged : '-';
    $fl_a = $headers->Answered ? $headers->Answered : '-';
    $fl_d = $headers->Deleted ? $headers->Deleted : '-';
    $fl_x = $headers->Draft ? $headers->Draft : '-';

    // build return object
    $meta = new stdClass();
    $meta->subject = $fsubj;
    $meta->fromName = isset($headers->from[0]->personal) ? $headers->from[0]->personal : '';
    $meta->fromEmail = $headers->from[0]->mailbox.'@'.$headers->from[0]->host;
    $meta->uid = $this->_uid;
    $meta->msgnum = $this->_msgnum;
    $meta->date = date("Y-m-d H:i:s", strtotime($headers->date));
    $meta->flags = strtr($headers->Recent.$headers->Unseen.$headers->Flagged.$headers->Answered.$headers->Deleted.$headers->Draft, ' ', '-');
    return $meta;
  } // genMetaData()


  /* This returns the first email part marked as text content type.  In simple
  ** messages, this is the same as BODY[1].  In multipart messages, it is the
  ** *first* text content found.  If a message has more than one text section
  ** (e.g., text/plain and text/html), only the first will be returned
  */
  private function _getPrimaryContent()
  {
    if (is_array($this->_content['text'])) {
      reset($this->_content['text']);
      $first_key = key($this->_content['text']);
      return $this->_content['text'][$first_key];
    }
    else {
      return '';
    }
  } // _getPrimaryContent()


  private function _stripHTML($content, $tags = null)
  {
    if (is_null($tags)) {
      $tags = static::$_html_tags_to_strip;
    }
    if (is_array($tags)) {
      $tags = implode('|', $tags);
    }
    return preg_replace('%
        # Match an opening or closing HTML 4.01 tag.
        </?                  # Tag opening "<" delimiter.
        (?:                  # Group for HTML 4.01 tags.
        '.$tags.'
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
        | <!--.*-->          # Or a (non-SGML compliant) HTML comment.
        | <!DOCTYPE[^>]*>    # Or a DOCTYPE.
        %six', '', (string)$content);
  } // _stripHTML()


  private function _resolveLDAPAddress($addr = '')
  {
    /* this is hard-coded for now.  */
    // Parse LDAP info because sometimes addresses are embedded.
    // See NYSS #5748 for more details.

    // if o= is appended to the end of the email address remove it
    $patterns = array(
      '#/senate@senate#i',   /* standardize reference to senate */
      /* SBB DEVCHANGE: This next line was in the original code, but I have found that removing
         the /CENTER part of the name makes the search fail.  Keep as standard, or remove?
         If we remove it, remember to remove the appropriate entry in the $replace array below */
      '#/CENTER/senate#i',   /* standardize reference to senate  */
      '/CN=|O=|OU=/i',       /* remove LDAP-specific addressing */
      '/mailto|\(|\)|:/i',   /* remove link remnants, parenthesis */
      '/"|\'/i',             /* remove quotes */
      '/\[|\]/i',            /* remove square brackets */
    );
    $replace = array('/senate', '/senate');
    $str = preg_replace($patterns, $replace, trim($addr));
    $ret = '';
    if (strpos($str, '/senate') !== false) {
      $search = false;
      $ldapcon = ldap_connect("senmail.senate.state.ny.us", 389);
      if ($ldapcon) {
        $retrieve = array('sn', 'givenname', 'mail');
        $search = ldap_search($ldapcon, 'o=senate', "(displayname=$str)", $retrieve);
      } else {
        error_log("Failed to create connection to LDAP server (testing msg#={$this->_msgnum}, addr=$str)");
      }
      $info = ($search === false) ? array('count'=>0) : ldap_get_entries($ldapcon, $search);
      if (array_key_exists(0,$info)) {
        $name = $info[0]['givenname'][0].' '.$info[0]['sn'][0];
        $ret = "$name <{$info[0]['mail'][0]}>";
      } else {
        error_log("LDAP search returned no results (testing msg#={$this->_msgnum}, addr=$str)");
      }
    }
    return $ret;
  } // _resolveLDAPAddress()


  // Recursive function that flattens out the multipart hierarchy and
  // names the keys using the standard IMAP part number.
  private function _flattenParts($msgParts, $flatParts = array(), $prefix = '',
                                 $index = 1, $fullPrefix = true)
  {
    foreach ($msgParts as $part) {
      $flatParts[$prefix.$index] = $part;
      if (isset($part->parts)) {
        if ($part->type == TYPEMESSAGE) {
          $flatParts = $this->_flattenParts($part->parts, $flatParts, $prefix.$index.'.', 0, false);
        }
        elseif ($fullPrefix) {
          $flatParts = $this->_flattenParts($part->parts, $flatParts, $prefix.$index.'.');
        }
        else {
          $flatParts = $this->_flattenParts($part->parts, $flatParts, $prefix);
        }
        unset($flatParts[$prefix.$index]->parts);
      }
      $index++;
    }
    return $flatParts;
  } // _flattenParts()


  // Decode content based on its encoding and subtype.
  private function _decodeContent($content = '', $enc = ENC7BIT, $subtype = '')
  {
    $ret = (string)$content;
    switch ((int)$enc) {
      case ENCBASE64:
        $ret = base64_decode($content);
        break; /* base-64 encoding */
      case ENCQUOTEDPRINTABLE:
        $ret = quoted_printable_decode($content);
        if (strcasecmp($subtype, 'HTML') == 0) {
          $ret = html_entity_decode($content, ENT_QUOTES);
        }
        break; /* quoted printable encoding */
      default:
        /* covers 7BIT/8BIT/BINARY/OTHER, but is essentially a pass-thru */
        break;
    }
    return $ret;
  } // _decodeContent()


  // For each message part, if the body type of that part matches the provided
  // body type, then load the content, decode it, and add it to the _content
  // array using the appropriate label.
  // By default, only grab TEXT body parts, such as text/plain and text/html.
  private function _loadContent($bodyType = TYPETEXT)
  {
    $label = $this->getBodyTypeLabel($bodyType);
    $this->_content[$label] = array();

    if ($this->isMultipart()) {
      foreach ($this->getParts() as $partnum => $part) {
        if ($part->type === $bodyType) {
          $content = $this->fetchPart($partnum);
          $content = $this->_decodeContent($content, $part->encoding, $part->subtype);
          $this->_content[$label][$partnum] = $content;
        }
      }
    }
    else {
      // fetchPart() with no args calls fetchBody('1'), which is what we want.
      $struct = $this->getStructure();
      $content = $this->fetchPart();
      $content = $this->_decodeContent($content, $struct->encoding, $struct->subtype);
      $this->_content[$label]['1'] = $content;
    }
  } // _loadContent()


  private function _loadTextContent()
  {
    return $this->_loadContent(TYPETEXT);
  } // _loadTextContent()


  // Internal function for interating over the message parts looking for
  // at least one attachment.
  private function _hasAttachments()
  {
    foreach ($this->getParts() as $part) {
      if ($part->ifdisposition && $part->disposition == 'attachment') {
        return true;
      }
    }
    return false;
  } // _hasAttachments()


  // Get the filename attribute from an array of parameters.
  private function _getFilename($params)
  {
    $fname = null;
    if (count($params) > 0) {
      foreach ($params as $param) {
        $attr = strtolower($param->attribute);
        if ($attr == 'name' || $attr == 'filename') {
          $fname = $param->value;
          break;
        }
      }
    }
    return $fname;
  } // _getFilename()
}
