<?php

/**
 * Requires the XML-RPC for PHP library (current version, 3.0.0beta).
 * http://phpxmlrpc.sourceforge.net/
 * http://sourceforge.net/projects/phpxmlrpc/files/phpxmlrpc/3.0.0beta/xmlrpc-3.0.0.beta.zip/download
 */
  require_once(dirname(__FILE__).'./../../../../../modules/civicrm/libs/xmlrpc/lib/xmlrpc.inc');

/**
 * Superclass for all Service classes.
 */
abstract class drupalService
{
  // Property declarations
  protected $domain_name = NULL;
  protected $api_key = NULL;
  protected $endPoint = 'http://www.nysenate.gov/services/xmlrpc';
  protected $methodName = NULL; 
  protected $domain_time_stamp = NULL;
  protected $nonce = NULL;
  protected $hash = NULL;
  protected $client = NULL;
  protected $key = NULL;
  
  // Method declarations

  public function __construct($domain_name, $api_key, $endPoint = 'http://www.nysenate.gov/services/xmlrpc')
  {
    $this->domain_name = $domain_name;
    $this->api_key = $api_key;
    $this->endPoint = $endPoint;
    $this->domain_time_stamp = (string)time();
    $code = md5(uniqid(rand(), true));
    $this->nonce = substr($code, 0, 20);
  }
  
  /**
   *  Parameters for all Services methods:
   *  @param params (required array)
   *    An array of all parameters to be passed into the method.
   */
  public function get($params=NULL)
  {
    $this->client = new xmlrpc_client($this->endPoint);
    $this->client->return_type = 'xml';            
    $this->key = array(
      new xmlrpcval($this->hash, "string"),
      new xmlrpcval($this->domain_name, "string"),
      new xmlrpcval($this->domain_time_stamp, "string"),
      new xmlrpcval($this->nonce, "string"),
    );
    return NULL;
  }
}
