<?php
class NYSS_IMAP_Session {
  public static $force_readonly = false;
  public static $auto_expunge = true;
  public $config = array();
  public $conn = NULL;
  public $defaults = array('server'  => 'webmail.nysenate.gov',
                           'port'    => 143,
                           'mailbox' => 'INBOX',
                           'flags'   => array('imap','readonly'),
                           );


  public function __construct($opts=array()) {
    $this->_populateOptions($opts);
    $this->_establishConnection();
  }

  public function __destruct() {
    $this->_closeConnection();
  }

  protected function _buildMailboxRef($mailbox = NULL) {
    if (!$mailbox) { $mailbox = $this->_getConfig('mailbox'); }
    return $this->buildServerRef() . $mailbox;
  }

  public function buildServerRef() {
    $ret = '{' . $this->_getConfig('server') . ':' . $this->_getConfig('port');
    $flags = $this->_getConfig('flags');
    if (self::$force_readonly && !in_array('readonly', $flags)) {
      $flags[]='readonly';
    }
    if (count($flags)) {
      $ret .= '/' . implode('/',$flags);
    }
    $ret .= '}';
    return $ret;
  }

  protected function _closeConnection() {
    if ($this->conn) {
      imap_close($this->conn, (static::$auto_expunge ? CL_EXPUNGE : NULL) );
    }
  }

  protected function _establishConnection() {
    $this->conn = imap_open($this->_buildMailboxRef(), $this->_getConfig('user'), $this->_getConfig('password'));
    return (bool)$this->conn;
  }

  protected function _getConfig($type) {
    return NYSS_Utils::array_ifelse($type,
                                    $this->config,
                                    NYSS_Utils::array_ifelse($type, $this->defaults, NULL)
           );
  }

  protected function _populateOptions(Array $opts = array()) {
    foreach ($opts as $k=>$v) {
      $this->config[$k] = $v;
    }
    $this->_standardizeFlags();
  }

  protected function _standardizeFlags() {
    $f = $this->_getConfig('flags');
    if (!is_array($f)) {
      $newf = array();
      $f = explode('/',(string)$f);
      foreach ($f as $v) {
        $tv = trim($v);
        if ($tv) { $newf[] = $tv; }
      }
      $this->config['flags'] = $newf;
    }
  }

  public function fetchBody($msgnum, $section='') {
    return imap_fetchbody($this->conn, (int)$msgnum, $section);
  }

  public function fetchHeaders($msgnum) {
    return imap_fetchheader($this->conn, (int)$msgnum);
  }

  public function fetchMessage($msgnum) {
    return imap_fetchstructure($this->conn, (int)$msgnum);
  }

  public function fetchMessageCount() {
    return imap_num_msg($this->conn);
  }

  public function getFolderStatus($folder = NULL, $options = SA_ALL) {
    return imap_status($this->conn, $this->_buildMailboxRef($folder), $options);
  }

  public function listFolders($pattern='*') {
    $ref = $this->buildServerRef();
    $ret = array();
    foreach (imap_list($this->conn, $ref, $pattern) as $k=>$v) {
      $ret[] = str_replace($ref, '', $v);
    }
    return $ret;
  }

  public function selectFolder($folder) {
    $this->config['mailbox'] = $folder ? $folder : "INBOX";
    return imap_reopen($this->conn, $this->_buildMailboxRef());
  }
}