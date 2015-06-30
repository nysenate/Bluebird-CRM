<?php
set_time_limit(300);
$docroot = array_key_exists('DOCUMENT_ROOT',$_SERVER) ? $_SERVER['DOCUMENT_ROOT'] : '';
if (php_sapi_name() != "apache2handler" || !$docroot) {
  echo "This file should not be called from command line.";
  exit(1);
}

// bootstrap Civi
require_once $_SERVER['DOCUMENT_ROOT'].'/../civicrm/scripts/script_utils.php';
require_once SCRIPT_UTILS_CIVIROOT.'/civicrm.config.php';
$config = &CRM_Core_Config::singleton();
$session = &CRM_Core_Session::singleton();

// get the bluebird config
add_scripts_to_include_path();
require_once 'bluebird_config.php';

// add local resources to include path
set_include_path(dirname(__FILE__).PATH_SEPARATOR.get_include_path());
require_once 'NYSS/Utils.php';
require_once 'NYSS/AJAX/Session.php';
require_once 'NYSS/IMAP/Session.php';
require_once 'NYSS/IMAP/Message.php';

$isession = NYSS_AJAX_Session::getInstance();
/* for debugging */
$isession->logger->setLevel(NYSS_LOG_LEVEL_DEBUG);
$ret =& $isession->response->data;

/* set up references for application config and IMAP session */
$this_instance = $isession->configBBInstanceCredentials();
if ($this_instance) {
  $this_session = new NYSS_IMAP_Session(array(
          'user'=>$this_instance[0],
          'password'=>$this_instance[1],
          'mailbox'=>$isession->req('folder_request'),
     ));
}

// application routing goes here
$cmd = NYSS_Utils::array_ifelse('req',$_REQUEST,'');

switch($isession->req('req')) {
  case 'loadInstances':
    $r = array();
    foreach ($isession->bbconfig as $k=>$v) {
      $matches = array();
      if (preg_match('/^instance:(.*)$/',$k,$matches)) {
        $r[] = $matches[1];
      }
    }
    $isession->response->data=$r;
    break;
  case 'listFolders': $ret = $this_session->listFolders(); break;
  case 'getFolderStatus': $ret = $this_session->getFolderStatus($isession->req('folder_request')); break;
  case 'showMsg':
    $this_session->selectFolder($isession->req('folder_request'));
    $mn = $isession->req('showMsg_number');
    $msg = new NYSS_IMAP_Message($this_session->conn, $mn);
    $msg->populateParts();

    $ret = new stdClass;
    $ret->overview = $msg->fetchStructure();
    $ret->rfc822 = $msg->fetchPart('');
    $ret->headers = $msg->fetchHeaders();
    $ret->meta = $msg->fetchMetaData();

    $ret->message = $msg;
    $ret->senders = $msg->findFromAddresses();
    break;
  case 'searchHeader':
    $this_session->selectFolder($isession->req('folder_request'));
    $mn = $isession->req('showMsg_number');
    $r = new NYSS_IMAP_Message($this_session->conn, $mn);
    echo "<pre>".print_r($r->searchFullHeaders(),1)."</pre>";
    echo "<pre style=\"border:2px solid black;padding:10px;\">".$r->mangleHTML()."</pre>";
    break;
  case 'searchMulti':
    $this_session->selectFolder('Archive');
    for ($i=1;$i<6000;$i++) {
      $r = $this_session->fetchMessage($i);
      error_log("Message $i type=".$r->type."(".NYSS_IMAP_Message::$body_type_labels[$r->type]."), subtype=".$r->subtype);
      if (isset($r->parts)) {
        foreach($r->parts as $k=>$v) {
          error_log("  --> PART $k, type=".$v->type."(".NYSS_IMAP_Message::$body_type_labels[$v->type]."), subtype=".$v->subtype);
        }
      }
    }
  default:
    $ret = array('result'=>'ERROR', 'resultcode'=>1, 'message'=>'Unknown command', 'data'=>NULL);
    break;
}
$isession->response->send();
die();
