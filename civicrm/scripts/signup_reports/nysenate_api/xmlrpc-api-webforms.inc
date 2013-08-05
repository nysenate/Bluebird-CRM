<?php

/**
 * Requires the XML-RPC for PHP library (current version, 3.0.0beta).
 * http://phpxmlrpc.sourceforge.net/
 * http://sourceforge.net/projects/phpxmlrpc/files/phpxmlrpc/3.0.0beta/xmlrpc-3.0.0.beta.zip/download
 */
require_once 'xmlrpc-api.inc';

/**
 * For handling the webform.responses method.
 */
class WebformResponses extends drupalService
{

  public function __construct($domain_name, $api_key, $endPoint = 'http://www.nysenate.gov/services/xmlrpc')
  {
    parent::__construct($domain_name, $api_key, $endPoint);
    $this->methodName = 'webform.responses';
    $this->hash = hash_hmac('sha256', $this->domain_time_stamp .';'.$this->domain_name .';'. $this->nonce .';'. $this->methodName, $this->api_key);
  }

  // method declarations/overrides
  /**
   * Additional parameters for the webform.responses method:
   * @param $nid
   *   Number. The webform's node ID.
   * @param $start_date
   *   Number. An optional start date of the date range for which results are desired.
   * @param $end_date
   *   Number. An optional end date of the date range for which results are desired.
   * @param $start_sid
   *   Number. An optional start submission ID number for the range from which results are desired.
   * @param $end_sid
   *   Number. An optional end submission ID number for the range from which results are desired.
   * @param $status
   *   Number. The published status of the webform.
   *       0 => return all results
   *       1 => only return submissions from published webforms
   *       2 => only return submissions from unpublished webforms
   * @param $limit
   *   Number. The maximum number of responses to return (default 100). 
   *   If a limit of "0" is specified, all responses will be returned. Warning: setting no limit could result in timeout or memory errors!
   * @return
   *   An array of the form:
   *     Array
   *         (
   *             'start_sid' => the lowest submission id in the returned set
   *             'end_sid' => the highest submission id in the return set
   *             'start_date' => the lowest submission date in the returned set
   *             'end_date' => the highest submission date in the returned set
   *             'nids' => Array(
   *                  [nid1] => [results1]
   *                  [nid2] => [results2]
   *                  ...
   *               )
   *         )
   *   where each instance of results1, results2, etc. is an array of the form:
   *     Array
   *         (
   *             'status' => the published status of the webform (0=unpublished, 1=published)
   *             'fields' => an array giving field definitions for the webform
   *             'sids' => Array(
   *                  [sid1] => [submission1]
   *                  [sid2] => [submission2]
   *                  ...
   *               )
   *         )
   *   and where each instance of submission1, submission2, etc. is an array of values from a single webform submission,
   *   as returned from function webform_service_submission_values().
   *
   * @see webform_service_submission_values() in the webform_service module
   */
  public function get($params=NULL)
  {
    parent::get($params);
    $this->key[] = new xmlrpcval($params['nid'], "int");
    $this->key[] = new xmlrpcval($params['start_date'], "int");
    $this->key[] = new xmlrpcval($params['end_date'], "int");
    $this->key[] = new xmlrpcval($params['start_sid'], "int");
    $this->key[] = new xmlrpcval($params['end_sid'], "int");
    $this->key[] = new xmlrpcval($params['uid'], "int");
    $this->key[] = new xmlrpcval($params['status'], "int");
    $this->key[] = new xmlrpcval($params['limit'], "int");
    $message = new xmlrpcmsg($this->methodName, $this->key);
    $result = $this->client->send($message);
    return xmlrpc_decode($result->value());
  }
}
