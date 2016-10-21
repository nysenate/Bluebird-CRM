<?php

class CRM_NYSS_IMAP_Message
{
  public static $body_type_labels = array(
    0 => 'text',
    1 => 'multipart',
    2 => 'message',
    3 => 'application',
    4 => 'audio',
    5 => 'image',
    6 => 'video',
    7 => 'other');

  /* Credit to http://www.regular-expressions.info/email.html
     See discussion at the above link regarding the effectiveness/thoroughness
     of the pattern.  IT WILL NOT CATCH ALL EMAIL ADDRESSES, but it does match
     99% of RFC5322-compliant addresses.  Also, detections are not
     necessarily VALID.
  */
  private static $_email_address_regex =
    /* mailbox */
    '/([a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*)' .
    /* at */
    '@' .
    /* host */
    '((?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)/i';

  private static $_encoding_type_labels = array(
    0 => '7BIT',
    1 => '8BIT',
    2 => 'BINARY',
    3 => 'BASE64',
    4 => 'QUOTED-PRINTABLE',
    5 => 'OTHER');

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

  /* legacy item from MessageBodyParser.  Probably is not needed anymore */
  private static $_html_additional_tags = array(
    'BR','DIV','HR','IMG','TABLE','TD','TBODY','TFOOT','TH','THEAD','TR');

  private $_session = null;
  private $_msgnum = 0;
  private $_uid = 0;
  private $_headers = null;
  private $_structure = null;
  private $_metadata = null;
  private $_parts = null;
  private $_attachments = null;
  private $_from_limit = 0;
  private $_subj_limit = 50;
  private $_is_multipart = false;


  public function __construct($imapSession, $msgnum = 0)
  {
    $this->_session = $imapSession;
    $this->_msgnum = $msgnum;
    $this->_uid = imap_uid($this->getConnection(), $msgnum);
    // Pre-populate the _headers and _structure properties.
    $this->fetchHeaders();
    $this->fetchStructure();
  } // __construct()


  public function getConnection()
  {
    return $this->_session->getConnection();
  } // getConnection()


  // Cache the message attachments after retrieving them on the first call.
  public function fetchAttachments()
  {
    if ($this->_attachments == null) {
      // Make sure that _structure is populated.
      if ($this->_structure == null) {
        $this->fetchStructure();
      }
      $this->_attachments = array();
      if ($this->_is_multipart) {
        foreach ($this->_structure->parts as $k => $v) {
          $partno = (int)$k + 1;
          if ($v->ifdisposition && $v->disposition == 'attachment') {
            $this->_attachments[] = $this->fetchPart($partno);
          }
        }
      }
    }
    return $this->_attachments;
  } // fetchAttachments()


  public function fetchBody($section = '')
  {
    return imap_fetchbody($this->getConnection(), $this->_msgnum, $section);
  } // fetchBody()


  // Cache the message headers after retrieving them on the first call.
  public function fetchHeaders()
  {
    //TODO why do we truncate to 50 characters?
    if ($this->_headers == null) {
      $this->_headers = imap_headerinfo($this->getConnection(), $this->_msgnum, $this->_from_limit, $this->_subj_limit);
    }

    //deal with various special characters that create problems
    if (strpos($this->_headers->subject, '?UTF-8?') !== false) {
      $cleanSubject = mb_convert_encoding(mb_decode_mimeheader($this->_headers->subject), "HTML-ENTITIES", "UTF-8");
      //TODO why do we truncate to 50 characters?
      $cleanSubject = substr($cleanSubject, 0, 50);

      //address some special characters manually (not ideal, but no easy workaround)
      $search = array('&rsquo;');
      $replace = array("'");
      $this->_headers->fetchsubject = str_replace($search, $replace, $cleanSubject);
    }

    return $this->_headers;
  } // fetchHeaders()


  // Cache the message meta data after retrieving it on the first call.
  public function fetchMetaData()
  {
    if ($this->_metadata == null) {
      // fetch info
      $headers = $this->fetchHeaders();

      // build return object
      $meta = new stdClass();
      $meta->subject = $headers->fetchsubject;
      $meta->fromName = isset($headers->from[0]->personal) ? $headers->from[0]->personal : '';
      $meta->fromEmail = $headers->from[0]->mailbox.'@'.$headers->from[0]->host;
      $meta->uid = $this->_uid;
      $meta->msgnum = $this->_msgnum;
      $meta->date = date("Y-m-d H:i:s", strtotime($headers->date));
      $this->_metadata = $meta;
    }
    return $this->_metadata;
  } // fetchMetaData()


  public function fetchPart($section = '1')
  {
    return $this->fetchBody($section);
  } // fetchPart()


  // Cache the message structure after retrieving it on the first call.
  // Also sets the is_multipart flag accordingly.
  public function fetchStructure()
  {
    if ($this->_structure == null) {
      $this->_structure = imap_fetchstructure($this->getConnection(), $this->_msgnum);
      $this->_is_multipart = isset($this->_structure->parts);
    }
    return $this->_structure;
  } // fetchStructure()


  public function getStructure()
  {
    return $this->_structure;
  } // getStructure()


  public function findFromAddresses()
  {
    $addr = array(
              'primary'=>array(
                  'address'=>$this->_headers->from[0]->mailbox.'@'.$this->_headers->from[0]->host,
                  'name'=>isset($this->_headers->from[0]->personal) ? $this->_headers->from[0]->personal : '',
                  ),
              'secondary'=>array(),
              'other'=>array(),
            );
    $other = array();
    if (!(is_array($this->_parts) && count($this->_parts))) {
      $this->populateParts();
    }

    foreach ($this->_parts as $k => $v) {
      if (!$v->ifdisposition && $v->content) {
        $matches = array();
        $tc = $this->_decodeContent($v->content, $v->encoding, $v->subtype);
        if (preg_match_all('/(From|Reply To):\s*(.*)/i', $tc, $matches)) {
          //error_log("Parsing msg#={$this->_msgnum}");
          foreach ($matches[2] as $kk => $vv) {
            if (preg_match('#CN=|OU?=|/senate#', $vv)) {
              //error_log("resolving -$vv- with LDAP");
              $vv = $this->_resolveLDAPAddress($vv);
              //error_log("resolved to -$vv-");
            }
            $ta = imap_rfc822_parse_adrlist($vv, '');
            if (count($ta) && $ta[0]->host && $ta[0]->mailbox && $ta[0]->host != '.SYNTAX-ERROR.') {
              $newta = array(
                    'address' => $ta[0]->mailbox.'@'.$ta[0]->host,
                    'name' => isset($ta[0]->personal) ? $ta[0]->personal : null);
              switch (strtoupper($matches[1][$kk])) {
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
        if (preg_match_all(static::$_email_address_regex, $tc, $matches)) {
          foreach ($matches[0] as $kk=>$vv) {
            $tvv = filter_var(filter_var($vv, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);
            if ($tvv != $addr['primary']['address']) {
              $addr['other'][] = $tvv;
            }
          }
        }
      }
    }
    return $addr;
  } // findFromAddresses()


  public function hasAttachments()
  {
    if (isset($this->_structure->parts)) {
      foreach ($this->_structure->parts as $k => $v) {
        if ($v->ifdisposition && $v->disposition == 'attachment') {
          return true;
        }
      }
    }
    return false;
  } // hasAttachments()


  public function populateParts($include_attach = false)
  {
    $parts = array();
    if ($this->_is_multipart) {
      foreach ($this->_structure->parts as $k => $v) {
        $partno = (int)$k + 1;
        $parts[$partno] = $v;
        if (!$v->ifdisposition || $include_attach) {
          $parts[$partno]->content = $this->fetchPart($partno);
        }
      }
    }
    else {
      $parts[1] = clone $this->_structure;
      $parts[1]->content = $this->fetchPart();
    }
    $this->_parts = $parts;
  } // populateParts()


  public function mangleHTML() {
    // get the primary content, decoded
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
    foreach (explode("\n",$content) as $k => $v) {
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


  /* This returns the first email part marked as text content type.  In simple
  ** messages, this is the same as BODY[1].  In multipart messages, it is the
  ** *first* text content found.  If a message has more than one text section
  ** (e.g., text/plain and text/html), only the first will be returned
  */
  private function _getPrimaryContent($decoded = true)
  {
    if (!($this->_parts)) {
      $this->populateParts();
    }
    $content = '';
    foreach ($this->_parts as $k => $v) {
      if ($v->type == 0) {
        $content = $v->content;
        if ($decoded) {
          $content = $this->_decodeContent($v->content, $v->encoding);
        }
        break;
      }
    }
    return $content;
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
        | <!--.*?-->         # Or a (non-SGML compliant) HTML comment.
        | <!DOCTYPE[^>]*>    # Or a DOCTYPE.
        %six', '', (string)$content);
  } // _stripHTML()


  // kz: This seems to be the long way to get UID.  imap_uid() is much easier.
  private function _fetchUid()
  {
    $t = imap_fetch_overview($this->getConnection(), $this->_msgnum);
    return (is_array($t) && isset($t[0]) && is_object($t[0])) ? $t[0]->uid : 0;
  } // _fetchUid()


  private function _decodeContent($content = '', $encoding = 0, $subtype = '')
  {
    $ret = (string)$content;
    switch ((int)$encoding) {
      case 3:
        $ret = base64_decode($content);
        break; /* base-64 encoding */
      case 4:
        $ret = quoted_printable_decode($content);
        if ($subtype == 'HTML') {
          $ret = html_entity_decode($content,ENT_QUOTES);
        }
        break; /* quoted printable encoding */
      default:
        /* covers 7BIT/8BIT/BINARY/OTHER, but is essentially a pass-thru */
        break;
    }
    return $ret;
  } // _decodeContent()


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


  private function _resolveMessageNumber($msgnum = 0, $use_existing = true)
  {
    return $mn;
  } // _resolveMessageNumber()
}
