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
class SignupGet extends drupalService
{

  public function __construct($domain_name, $api_key, $endPoint = 'http://www.nysenate.gov/services/xmlrpc')
  {
    parent::__construct($domain_name, $api_key, $endPoint);
    $this->methodName = 'nyss_signup.get';
    $this->hash = hash_hmac('sha256', $this->domain_time_stamp .';'.$this->domain_name .';'. $this->nonce .';'. $this->methodName, $this->api_key);
  }

  // method declarations/overrides
  /**
   * Additional parameters for the nyss_signup.get method:
   * @param $start_date
   *   Number. An optional start date of the date range for which results are desired.
   * @param $end_date
   *   Number. An optional end date of the date range for which results are desired.
   * @param $start_sid
   *   Number. An optional start submission ID number for the range from which results are desired.
   * @param $end_sid
   *   Number. An optional end submission ID number for the range from which results are desired.
   * @param $limit
   *   Number. The maximum number of responses to return (default 100). 
   *   If a limit of "0" is specified, all responses will be returned. Warning: setting no limit could result in timeout or memory errors!
   * @return
   *    a SignupData object
   */
  public function get($params=NULL)
  {
    parent::get($params);
    $this->key[] = new xmlrpcval($params['start_date'], "int");
    $this->key[] = new xmlrpcval($params['end_date'], "int");
    $this->key[] = new xmlrpcval($params['start_sid'], "int");
    $this->key[] = new xmlrpcval($params['end_sid'], "int");
    $this->key[] = new xmlrpcval($params['limit'], "int");
    $message = new xmlrpcmsg($this->methodName, $this->key);
    $result = $this->client->send($message);
    return xmlrpc_decode($result->value());
  }
}
