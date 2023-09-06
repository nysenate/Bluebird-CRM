<?php

//https://www.php-imap.com/
use Webklex\PHPIMAP\ClientManager;

class CRM_NYSS_IMAP_Session
{
  public static $force_readonly = false;
  public static $auto_expunge = true;

  private $_config = [];
  private $_serverRef = null;
  private $_conn = null;

  private $_defaults = [
    'host' => 'imap.example.com',
    'port' => 143,
    'flags' => ['imap'],
    'mailbox' => 'INBOX',
    'user' => '',
    'password' => '',
    'log_errors' => false
  ];


  public function __construct($opts = []) {
    // Compute union of provided opts and default opts, with provided opts
    // taking precedence.
    $this->_config = $opts + $this->_defaults;

    // If flags were specified as a slash-delimited string, then convert
    // to an array of flags.
    $flags = $this->_config['flags'];
    if (!is_array($flags)) {
      $this->_config['flags'] = array_filter(explode('/', $flags), 'trim');
    }

    $this->_openConnection();

    if ($this->_conn === null || $this->_conn === false) {
      throw new Exception('Unable to establish connection to IMAP server; serverRef='.$this->_config['user'].'@'.$this->_serverRef);
    }
  }

  public function getConnection() {
    return $this->_conn;
  } // getConnection()


  private function _openConnection() {
    require_once 'php-imap/vendor/autoload.php';

    //refresh and retrieve the access token
    $oAuthSysToken = \Civi\Api4\OAuthSysToken::refresh()
      ->addWhere('grant_type', '=', 'authorization_code')
      ->execute()
      ->first();

    $cm = new ClientManager();
    //Civi::log()->debug(__METHOD__, ['$cm' => $cm]);

    $client = $cm->make(['host' => $this->_config['host'],
      'port' => $this->_config['port'],
      'protocol' => 'imap',
      'encryption' => 'ssl',
      'validate_cert' => TRUE,
      'username' => $this->_config['user'],
      'password' => $oAuthSysToken['access_token'],
      'authentication' => 'oauth',
    ]);
    //Civi::log()->debug(__METHOD__, ['$client' => $client]);

    //Connect to the IMAP Server
    try {
      $client->connect();
      $this->_conn = $client;
      //Civi::log()->debug(__METHOD__, ['$client->connect' => $client]);
    }
    catch (Exception $e) {
      Civi::log()->debug(__METHOD__, ['$e' => $e]);
    }

    return $this->_conn ?? NULL;
  }
}
