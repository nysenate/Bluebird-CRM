<?php

class civicrm_api3_result {
  //
}

class civicrm_api3  {

  function __construct ($config = null) {
    $this->input = array ();
    $this->lastResult = array ();
    if (isset ($config) &&isset($config ['conf_path'] )) {
      require_once ($config ['conf_path'] .'/civicrm.settings.php');
      require_once 'CRM/Core/Config.php';
      require_once 'api/api.php';
      require_once "api/v3/utils.php";
      $this->cfg= CRM_Core_Config::singleton();
      $this->init();
      $this->ping();
    } else {
      $this->cfg= CRM_Core_Config::singleton();
    }
  }

  public function __get($entity) {
    //TODO check if it's a valid entity
    $this->currentEntity = $entity;
    return $this;
  }

  public function __call($action, $params) {
    // TODO : check if its a valid action
    if (isset($params[0])) {
      return $this->call ($this->currentEntity,$action,$params[0]);
    }else {
      return $this->call ($this->currentEntity,$action,$this->input);
    }
  }


  /**  As of PHP 5.3.0  */
  public static function __callStatic($name, $arguments) {
    // Should we implement it ?
    echo "Calling static method '$name' "
      . implode(', ', $arguments). "\n";
  }

  function call ($entity,$action='Get',$params = array()) {
    $this->ping ();// necessary only when the caller runs a long time (eg a bot)
    if (is_int($params)) {
      $params = array ('id'=> $params);
    }
    if (!isset ($params['version']))
      $params['version'] = 3;
    if (!isset ($params['sequential']))
      $params['sequential'] = 1;
    $this->lastResult= (object) civicrm_api ($entity,$action,$params);
    if ($this->lastResult->count == 1 && count($this->lastResult->values)== 1) {
      $this->lastResult->values = array_shift($this->lastResult->values);
    }
    $this->input=array();//reset the input to be ready for a new call
    return (!$this->lastResult->is_error);
  }

  function ping () {
    global $_DB_DATAOBJECT;
    foreach ($_DB_DATAOBJECT['CONNECTIONS'] as &$c) {
      if (!$c->connection->ping()) {
        $c->connect($this->cfg->dsn);
        if (!$c->connection->ping()) {
          die ("we couldn't connect");
        }
      }

    }
  }

  function errorMsg () {
    return $this->lastResult->error_message;
  }
  function init () {
    CRM_Core_DAO::init( $this->cfg->dsn );
  }

  /*
   * $api->attr ('id'); // return the id
   * or
   * $api->attr ('id',42) //set the id
   */

  public function attr ($name,$value == null) {
    if ($value === null) {
      return $this->lastResult->values[$name];
    } else {
      $this->input[$name] = $value;
    }
  }
}
