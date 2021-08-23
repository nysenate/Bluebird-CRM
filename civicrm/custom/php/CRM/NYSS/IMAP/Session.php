<?php

require_once 'CRM/Utils/Array.php';


class CRM_NYSS_IMAP_Session
{
  public static $force_readonly = false;
  public static $auto_expunge = true;

  private $_config = array();
  private $_serverRef = null;
  private $_conn = null;

  private $_defaults = array(
    'host'       => 'imap.example.com',
    'port'       => 143,
    'flags'      => array('imap'),
    'mailbox'    => 'INBOX',
    'user'       => '',
    'password'   => '',
    'log_errors' => false
  );


  public function __construct($opts = array())
  {
    // Compute union of provided opts and default opts, with provided opts
    // taking precedence.
    $this->_config = $opts + $this->_defaults;

    // If flags were specified as a slash-delimited string, then convert
    // to an array of flags.
    $flags = $this->_config['flags'];
    if (!is_array($flags)) {
      $this->_config['flags'] = array_filter(explode('/', $flags), 'trim');
    }

    $this->_serverRef = $this->_buildServerRef();
    $this->_openConnection();
    if ($this->_conn === null || $this->_conn === false) {
      throw new Exception('Unable to establish connection to IMAP server; serverRef='.$this->_config['user'].'@'.$this->_serverRef);
    }
  } // __construct()


  public function __destruct()
  {
    $this->_closeConnection();
  } // __destruct()


  public function getConnection()
  {
    return $this->_conn;
  } // getConnection()


  public function getServerRef()
  {
    return $this->_serverRef;
  } // getServerRef()


  public function fetchBody($msgnum, $section = '')
  {
    return imap_fetchbody($this->_conn, (int)$msgnum, $section);
  } // fetchBody()


  public function fetchHeaders($msgnum)
  {
    return imap_fetchheader($this->_conn, (int)$msgnum);
  } // fetchHeaders()


  public function fetchMessage($msgnum)
  {
    return imap_fetchstructure($this->_conn, (int)$msgnum);
  } // fetchMessage()


  public function fetchMessageCount()
  {
    return imap_num_msg($this->_conn);
  } // fetchMessage()


  public function getFolderStatus($folder = 'INBOX', $options = SA_ALL)
  {
    return imap_status($this->_conn, $this->_buildMailboxRef($folder), $options);
  } // getFolderStatus()


  public function listFolders($pattern = '*', $removeServerRef = true)
  {
    $boxes = imap_list($this->_conn, $this->_serverRef, $pattern);
    if ($removeServerRef) {
      return str_replace($this->_serverRef, '', $boxes);
    }
    else {
      return $boxes;
    }
  } // listFolders()


  public function selectFolder($folder = 'INBOX')
  {
    return imap_reopen($this->_conn, $this->_buildMailboxRef($folder));
  } // selectFolder()


  private function _buildMailboxRef($mailbox = null)
  {
    if (!$mailbox) {
      $mailbox = $this->_config['mailbox'];
    }
    return $this->_serverRef.$mailbox;
  } // _buildMailboxRef()


  private function _buildServerRef()
  {
    $serverRef = $this->_config['host'].':'.$this->_config['port'];
    $flags = $this->_config['flags'];
    if (self::$force_readonly && !in_array('readonly', $flags)) {
      $flags[] = 'readonly';
    }
    if (count($flags)) {
      $serverRef .= '/'.implode('/', $flags);
    }
    return '{'.$serverRef.'}';
  } // _buildServerRef()


  private function _closeConnection()
  {
    // Changes (moves and deletions) to the IMAP mailbox will not be made
    // unless CL_EXPUNGE is used when the connection is closed.
    if ($this->_conn) {
      $errors = imap_errors();
      if ($errors && $this->_config['log_errors']) {
        error_log("IMAP SESSION REPORTED ERRORS:\n".print_r($errors, true));
      }
      imap_close($this->_conn, (static::$auto_expunge ? CL_EXPUNGE : null));
    }
    $this->_conn = null;
  } // _closeConnection()


  private function _openConnection()
  {
    $this->_conn = imap_open($this->_buildMailboxRef(), $this->_config['user'], $this->_config['password']);
  } // _openConnection()
}
