<?php

/**
 * Requires the XML-RPC for PHP library (current version, 3.0.0beta).
 * http://phpxmlrpc.sourceforge.net/
 * http://sourceforge.net/projects/phpxmlrpc/files/phpxmlrpc/3.0.0beta/xmlrpc-3.0.0.beta.zip/download
 */
require_once 'xmlrpc-api.inc';

/**
 * For handling the node.get method.
 */
class SenatorData extends drupalService
{

  public function __construct($domain_name, $api_key, $endPoint = 'http://www.nysenate.gov/services/xmlrpc')
  {
    parent::__construct($domain_name, $api_key, $endPoint);
    $this->methodName = 'nyss_contact.senator_data';
    $this->hash = hash_hmac('sha256', $this->domain_time_stamp .';'.$this->domain_name .';'. $this->nonce .';'. $this->methodName, $this->api_key);
  }

  // method declarations/overrides
  /**
   * @return
   *    a structured array of messages
   */
  public function get($params=NULL)
  {
    parent::get($params);
    $message = new xmlrpcmsg($this->methodName, $this->key);
    $result = $this->client->send($message);
    return xmlrpc_decode($result->value());
  }
}
