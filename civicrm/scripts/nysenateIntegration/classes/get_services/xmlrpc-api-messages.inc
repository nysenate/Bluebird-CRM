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
class ContactMessages extends drupalService
{

  public function __construct($domain_name, $api_key, $endPoint = 'http://www.nysenate.gov/services/xmlrpc')
  {
    parent::__construct($domain_name, $api_key, $endPoint);
    $this->methodName = 'nyss_contact.messages';
    $this->hash = hash_hmac('sha256', $this->domain_time_stamp .';'.$this->domain_name .';'. $this->nonce .';'. $this->methodName, $this->api_key);
  }

  // method declarations/overrides
  /**
   * Additional parameters for the nyss_contact.messages method:
   * @param $start_date
   *   Number. An optional start date of the date range for which results are desired.
   * @param $end_date
   *   Number. An optional end date of the date range for which results are desired.
   * @param $start_mid
   *   Number. An optional start submission ID number for the range from which results are desired.
   * @param $end_mid
   *   Number. An optional end submission ID number for the range from which results are desired.
   * @param $limit
   *   Number. The maximum number of responses to return (default 100). 
   *   If a limit of "0" is specified, all responses will be returned. Warning: setting no limit could result in timeout or memory errors!
   * @return
   *    a structured array of messages
   */
  public function get($params=NULL)
  {
    parent::get($params);
    $this->key[] = new xmlrpcval($params['start_date'], "int");
    $this->key[] = new xmlrpcval($params['end_date'], "int");
    $this->key[] = new xmlrpcval($params['start_mid'], "int");
    $this->key[] = new xmlrpcval($params['end_mid'], "int");
    $this->key[] = new xmlrpcval($params['source_form'], "string");
    $this->key[] = new xmlrpcval($params['senator_short_name'], "string");
    $this->key[] = new xmlrpcval($params['district_number'], "int");
    $this->key[] = new xmlrpcval($params['limit'], "int");
    $message = new xmlrpcmsg($this->methodName, $this->key);
    $result = $this->client->send($message);
    return xmlrpc_decode($result->value());
  }
}
