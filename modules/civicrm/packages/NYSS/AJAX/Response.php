<?php
/*
  AJAX Response class
  Provides a common interface for returning AJAX/JSON data
*/

define ('NYSS_AJAX_ERR_FATAL', 1);
define ('NYSS_AJAX_ERR_ERROR', 2);
define ('NYSS_AJAX_ERR_WARN',  3);
define ('NYSS_AJAX_ERR_INFO',  4);
define ('NYSS_AJAX_ERR_DEBUG', 5);

class NYSS_AJAX_Response {
  public static $default_content_type = 'application';
  public static $default_content_subtype = 'json';
  public static $default_charset = 'UTF-8';
  public static $error_labels = array(
        NYSS_AJAX_ERR_FATAL => 'FATAL',
        NYSS_AJAX_ERR_ERROR => 'ERROR',
        NYSS_AJAX_ERR_WARN  => 'WARN',
        NYSS_AJAX_ERR_INFO  => 'INFO',
        NYSS_AJAX_ERR_DEBUG => 'DEBUG',
        );
  public static $valid_codes = array(
        100 => "Continue",
				101 => "Switching Protocols",
				200 => "OK",
				201 => "Created",
				202 => "Accepted",
				203 => "Non-Authoritative Information",
				204 => "No Content",
				205 => "Reset Content",
				206 => "Partial Content",
				300 => "Multiple Choices",
				301 => "Moved Permanently",
				302 => "Found",
				303 => "See Other",
				304 => "Not Modified",
				305 => "Use Proxy",
				306 => "(Unused)",
				307 => "Temporary Redirect",
				400 => "Bad Request",
				401 => "Unauthorized",
				402 => "Payment Required",
				403 => "Forbidden",
				404 => "Not Found",
				405 => "Method Not Allowed",
				406 => "Not Acceptable",
				407 => "Proxy Authentication Required",
				408 => "Request Timeout",
				409 => "Conflict",
				410 => "Gone",
				411 => "Length Required",
				412 => "Precondition Failed",
				413 => "Request Entity Too Large",
				414 => "Request-URI Too Long",
				415 => "Unsupported Media Type",
				416 => "Requested Range Not Satisfiable",
				417 => "Expectation Failed",
				500 => "Internal Server Error",
				501 => "Not Implemented",
				502 => "Bad Gateway",
				503 => "Service Unavailable",
				504 => "Gateway Timeout",
				505 => "HTTP Version Not Supported",
				);

  public $data = NULL;
  public $status = 200;
  public $result = 0;
  public $send_as_json = true;
  protected $errors = array();
  protected $headers = array();


  public function __construct($req='') {
    $this->setIdentifiers($req);
    $this->setContentType();
  }

  public function addError($errtype, $value, $key=NULL) {
    if ($key===NULL) {
      $this->errors[] = array('type'=>$errtype, 'msg'=>$value);
    } else {
      $this->errors[$key] = array('type'=>$errtype, 'msg'=>$value);
    }
  }

  public function addErrors($errors) {
    if (is_array($errors)) {
      $this->errors = array_merge($this->errors,$errors);
    }
  }

  public function addHeader($header, $value) {
    $this->headers[$header] = $value;
  }

  public function clearError($key) {
    if (array_key_exists($key,$this->errors)) {
      unset($this->errors[$key]);
    }
  }

  public function clearHeader($header) {
    if (array_key_exists($header,$this->headers)) {
      unset($this->headers[$header]);
    }
  }

  public function getError($key) {
    return array_value($key,$this->errors);
  }

  public function getErrors() {
    return $this->errors;
  }

  public function getHeader($header) {
    return array_value($header,$this->headers);
  }

  public function getHeaders() {
    return $this->headers;
  }

  public function prepResponse() {
    $resp = new stdClass();
    foreach (array('req','result','data','errors') as $v) {
      $resp->$v = $this->$v;
    }
    $resp->errorcount = 0;
    foreach($resp->errors as $k=>&$v) {
      if ($v['type']<NYSS_AJAX_ERR_INFO) { $resp->errorcount++; }
      $v['typelabel']=static::$error_labels[$v['type']];
    }
    return $resp;
  }

  public function send($status=NULL, $with_die=true) {
    $this->setStatus($status);
    echo json_encode($this->prepResponse());
    if ($with_die) { die(); }
  }

  public function sendDBException($e) {
    $x = NYSS_Logger::getInstance();
    $x->log("Dumping exception:\n".var_export($e,1),NYSS_LOG_LEVEL_FATAL);
    if ($e instanceof PDOException) {
      $msg = "SQLSTATE:{$e->errorInfo[0]}, ERROR:{$e->errorInfo[1]}, MSG:{$e->errorInfo[2]}";
      $this->sendMessage($msg,NYSS_AJAX_ERR_FATAL,500);
    } else {
      $this->sendFatal($e->getMessage());
    }
  }

  public function sendFatal($msg,$status=500) {
    $x = NYSS_Logger::getInstance();
    $x->log("Dumping\n".$x->getBackTrace(),NYSS_LOG_LEVEL_ERROR);
    $this->data = NULL;
    $this->sendMessage($msg,NYSS_AJAX_ERR_FATAL,$status);
  }

  public function sendHeaders($extra=NULL) {
    if (!is_array($extra)) { $extra = array(); }
    foreach (array_merge($this->headers,$extra) as $key=>$value) {
      header("$key: $value");
    }
  }

  public function sendMessage($msg,$msgtype,$status=NULL) {
    if ($msg && $msgtype) {
      $this->addError($msgtype,$msg);
    }
    $this->send($status);
  }

  /* sets the content-type header */
  public function setContentType($type=NULL, $subtype=NULL, $charset=NULL) {
    $type = (string)$type;
    $subtype = (string)$subtype;
    $charset = (string)$charset;
    if (!$type) { $type = static::$default_content_type; }
    if (!$subtype) { $subtype = static::$default_content_subtype; }
    if (!$charset) { $charset = static::$default_charset; }
    $head = "{$type}/{$subtype}";
    if ($charset) {
      $head += "; charset={$charset}";
    }
    $this->addHeader('Content-Type',$head);
  }

  public function setIdentifiers($req) {
    $this->req = $req;
  }

  public function setStatus($status) {
    $st = (int)$status;
    if ($st) {
      if (!(array_key_exists($st,static::$valid_codes))) {
        $st = 500;
        $this->addError(NYSS_AJAX_ERR_WARN,"Invalid status '$st', changed to 500");
      }
      $this->status = $st;
    }
    http_response_code($this->status);
  }
}