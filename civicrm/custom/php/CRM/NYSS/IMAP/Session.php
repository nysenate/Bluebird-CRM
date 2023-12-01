<?php

//https://www.php-imap.com/
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\IMAP;

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
    CRM_Core_DAO_AllCoreTables::flush();
    CRM_Core_DAO_AllCoreTables::init(TRUE);

    require_once 'php-imap/vendor/autoload.php';

    //retrieve most recent access token
    $oAuthSysToken = \Civi\Api4\OAuthSysToken::get(FALSE)
      ->execute()
      ->last();
    //Civi::log()->debug(__METHOD__, ['$oAuthSysToken' => $oAuthSysToken]);

    //if token expired or expires in next minute, delete and recreate
    if (empty($oAuthSysToken) || $oAuthSysToken['expires'] + 60 < time()) {
      if (!empty($oAuthSysToken['id'])) {
        \Civi\Api4\OAuthSysToken::delete(FALSE)
          ->addWhere('id', '=', $oAuthSysToken['id'])
          ->execute();
      }

      //get an access token via client credential workflow
      $bbcfg = get_bluebird_instance_config();
      $clientCreds = \Civi\Api4\OAuthClient::clientCredential(FALSE)
        ->addWhere('tenant', '=', $bbcfg['oauth.tenant_id'])
        ->addWhere('provider', '=', 'ms-exchange')
        ->addWhere('guid', '=', $bbcfg['oauth.client_id'])
        ->addWhere('secret', '=', $bbcfg['oauth.client_secret']);
      //throws errors if setScopes is chained to main API call, so breaking out
      $clientCreds->setScopes([
        'https://outlook.office.com/.default',
      ]);
      $clientCreds->execute();

      $oAuthSysToken = \Civi\Api4\OAuthSysToken::get(FALSE)
        ->execute()
        ->last();
    }
    //Civi::log()->debug(__METHOD__, ['$oAuthSysToken' => $oAuthSysToken]);

    $cm = new ClientManager();
    //Civi::log()->debug(__METHOD__, ['$cm' => $cm]);

    $username = (str_contains($this->_config['user'], '@nysenate.gov')) ?
      $this->_config['user'] :
      $this->_config['user'].'@nysenate.gov';

    $client = $cm->make([
      'host' => $this->_config['host'],
      'port' => $this->_config['port'],
      'protocol' => 'imap',
      'encryption' => 'ssl',
      'validate_cert' => TRUE,
      'username' => $username,
      'password' => $oAuthSysToken['access_token'],
      'authentication' => 'oauth',
      'flags' => $this->_config['flags']
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

  function setSequenceType($msg, $type) {
    $conn = $this->getConnection();

    //$type = "IMAP::{$type}"

    $msg->setSequence("IMAP::{$type}");
  }
}
