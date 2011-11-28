<?php
/**
 * This class allows to consume the API, either from within a module that knows civicrm already:
 $api = new civicrm_api3();
or from any code on the same server as civicrm
  $api = new civicrm_api3 (array('conf_path'=> '/your/path/to/your/civicrm/or/joomla/site)); //the path to civicrm.settings.php
or to query a remote server via the rest api
  $api = new civicrm_api3 (array ('server' => 'http://example.org','api_key'=>'theusersecretkey','key'=>'thesitesecretkey'));

no matter how initialised and if civicrm is local or remote, you use the class the same way

  $api->{entity}->{action}($params);

so to get the individual contacts
  if ($api->Contact->Get(array('contact_type'=>'Individual','return'=>'sort_name,current_employer')) {
    echo "\n contacts found " . $api->attr('count');
    foreach ($api->values() as $c) {
       echo "\n".$c->sort_name. " working for ". $c->current_employer;
    }


  }else { // in theory, doesn't append
    echo $api->errorMsg();
  }

or to create an event
  if ($api->Event->Create(array('title'=>'Test','event_type_id' => 1,'is_public' => 1,'start_date' => 19430429))) {
    echo "created event id:". $api->attr('id');
  } else {
    echo $api->errorMsg();
  }

To make it easier, the get method can either take an associative array $params, or simply an id
$api->Activity->Get (42);

being the same as:

$api->Activity->Get (array('id'=>42));


 */


class civicrm_api3  {

  function __construct ($config = null) {
    $this->local=true;
    $this->input = array ();
    $this->lastResult = array ();
    if (isset ($config) &&isset($config ['server'])) {
      // we are calling a remote server via REST
      $this->local=false;
      $this->uri = $config ['server'];
      if (isset($config ['path']))
        $this->uri .= "/".$config ['path'];
      else
        $this->uri .= '/sites/all/modules/civicrm/extern/rest.php';
      $this->uri .='?json=1';
      if (isset($config ['key'])) {
        $this->key = $config['key'];
      }else {
        die ("\nFATAL:param['key] missing\n");
      }  
      if (isset($config ['api_key'])) {
        $this->api_key = $config['api_key'];
      }else {
        die ("\nFATAL:param['api_key] missing\n");
      }  

      return;
    }
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
    // TODO : check if it's a valid action
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

  function remoteCall ($entity,$action,$params = array()) {
    $fields= "key={$this->key}&api_key={$this->api_key}";
    $query = $this->uri."&entity=$entity&action=$action";
    foreach ($params as $k => $v) {
      $fields .= "&$k=".urlencode($v);
    }
    if (function_exists('curl_init')) {
      //set the url, number of POST vars, POST data
      $ch = curl_init();
      curl_setopt($ch,CURLOPT_URL,$query);
      curl_setopt($ch,CURLOPT_POST,count($params)+2);
      curl_setopt($ch,CURLOPT_POSTFIELDS,$fields);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      //execute post
      $result = curl_exec($ch);
      curl_close($ch);
      return json_decode ($result);
    } else { // not good, all in get when should be post
      $result = file_get_contents($query.'&'.$fields);
      return json_decode ($result);
    }
  }

  function call ($entity,$action='Get',$params = array()) {
    if (is_int($params)) 
      $params = array ('id'=> $params);
    if (!isset ($params['version']))
      $params['version'] = 3;
    if (!isset ($params['sequential']))
      $params['sequential'] = 1;

    if (!$this->local) {
      $this->lastResult= $this->remoteCall ($entity,$action,$params);
    } else {
      $this->ping ();// necessary only when the caller runs a long time (eg a bot)
      $this->lastResult= civicrm_api ($entity,$action,$params);
    }
/*    if ($this->lastResult->count == 1 && count($this->lastResult->values)== 1) {
      $this->lastResult->values = array_shift($this->lastResult->values);
}
 */
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

  public function attr ($name,$value = null) {
    if ($value === null) {
      return $this->lastResult->$name;
    } else {
      $this->input[$name] = $value;
    }
  }

  public function values () {
    if (is_array ($this->lastResult))
      return $this->lastResult['values'];
    else 
      return $this->lastResult->values;
  }

  public function result () {
    return $this->lastResult;
  }
}
