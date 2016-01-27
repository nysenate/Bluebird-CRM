<?php
/* Copyright iATS Payments (c) 2014
 * @author Alan Dixon
 *
 * This file is a part of CiviCRM published extension.
 *
 * This extension is free software; you can copy, modify, and distribute it
 * under the terms of the GNU Affero General Public License
 * Version 3, 19 November 2007.
 *
 * It is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License with this program; if not, see http://www.gnu.org/licenses/
 *
 * This code provides glue between CiviCRM payment model and the iATS Payment model encapsulated in the iATS_Service_Request object
 *
 */
class CRM_Core_Payment_iATSService extends CRM_Core_Payment {

  /**
   * We only need one instance of this object. So we use the singleton
   * pattern and cache the instance in this variable
   *
   * @var object
   * @static
   */
  static private $_singleton = NULL;

  /**
   * Constructor.
   *
   * @param string $mode the mode of operation: live or test
   *
   * @param array $paymentProcessor
   */
  public function __construct($mode, &$paymentProcessor, &$paymentForm = NULL, $force = FALSE) {
    $this->_paymentProcessor = $paymentProcessor;
    $this->_processorName = ts('iATS Payments');

    // get merchant data from config
    $config = CRM_Core_Config::singleton();
    // live or test
    $this->_profile['mode'] = $mode;
    // we only use the domain of the configured url, which is different for NA vs. UK
    $this->_profile['iats_domain'] = parse_url($this->_paymentProcessor['url_site'], PHP_URL_HOST);
  }

  static function &singleton($mode, &$paymentProcessor, &$paymentForm = NULL, $force = FALSE) {
    $processorName = $paymentProcessor['name'];
    if (self::$_singleton[$processorName] === NULL) {
      self::$_singleton[$processorName] = new CRM_Core_Payment_iATSService($mode, $paymentProcessor);
    }
    return self::$_singleton[$processorName];
  }

  function doDirectPayment(&$params) {

    if (!$this->_profile) {
      return self::error('Unexpected error, missing profile');
    }
    // use the iATSService object for interacting with iATS, mostly the same for recurring contributions
    require_once("CRM/iATS/iATSService.php");
    $isRecur = CRM_Utils_Array::value('is_recur', $params) && $params['contributionRecurID'];
    $method = $isRecur ? 'cc_create_customer_code' : 'cc';
    $iats = new iATS_Service_Request(array('type' => 'process', 'method' => $method, 'iats_domain' => $this->_profile['iats_domain'], 'currencyID' => $params['currencyID']));
    $request = $this->convertParams($params, $method);
    $request['customerIPAddress'] = (function_exists('ip_address') ? ip_address() : $_SERVER['REMOTE_ADDR']);
    $credentials = array(
      'agentCode' => $this->_paymentProcessor['user_name'],
      'password'  => $this->_paymentProcessor['password'],
    );
    // Get the API endpoint URL for the method's transaction mode.
    // TODO: enable override of the default url in the request object
    // $url = $this->_paymentProcessor['url_site'];

    // make the soap request
    $response = $iats->request($credentials, $request);
    // process the soap response into a readable result
    $result = $iats->result($response);
    if ($result['status']) {
      $params['contribution_status_id'] = 1; // success
      $params['payment_status_id'] = 1; // for future versions, the proper key
      $params['trxn_id'] = trim($result['remote_id']) . ':' . time();
      $params['gross_amount'] = $params['amount'];
      if ($isRecur) {
        // save the client info in my custom table
        // Allow further manipulation of the arguments via custom hooks,
        // before initiating processCreditCard()
        // CRM_Utils_Hook::alterPaymentProcessorParams($this, $params, $iatslink1);
        $processresult = $response->PROCESSRESULT;
        $customer_code = (string) $processresult->CUSTOMERCODE;
        $exp = sprintf('%02d%02d', ($params['year'] % 100), $params['month']);
        $email = '';
        if (isset($params['email'])) {
          $email = $params['email'];
        }
        elseif (isset($params['email-5'])) {
          $email = $params['email-5'];
        }
        elseif (isset($params['email-Primary'])) {
          $email = $params['email-Primary'];
        }
        $query_params = array(
          1 => array($customer_code, 'String'),
          2 => array($request['customerIPAddress'], 'String'),
          3 => array($exp, 'String'),
          4 => array($params['contactID'], 'Integer'),
          5 => array($email, 'String'),
          6 => array($params['contributionRecurID'], 'Integer'),
        );
        CRM_Core_DAO::executeQuery("INSERT INTO civicrm_iats_customer_codes
          (customer_code, ip, expiry, cid, email, recur_id) VALUES (%1, %2, %3, %4, %5, %6)", $query_params);
        $params['contribution_status_id'] = 1;
        // also set next_sched_contribution
        $params['next_sched_contribution'] = strtotime('+' . $params['frequency_interval'] . ' ' . $params['frequency_unit']);
      }
      return $params;
    }
    else {
      return self::error($result['reasonMessage']);
    }
  }

  function changeSubscriptionAmount(&$message = '', $params = array()) {
    $userAlert = ts('You have updated the amount of this recurring contribution.');
    CRM_Core_Session::setStatus($userAlert, ts('Warning'), 'alert');
    return TRUE;
  }

  function cancelSubscription(&$message = '', $params = array()) {
    $userAlert = ts('You have cancelled this recurring contribution.');
    CRM_Core_Session::setStatus($userAlert, ts('Warning'), 'alert');
    return TRUE;
  }

  function &error($error = NULL) {
    $e = CRM_Core_Error::singleton();
    if (is_object($error)) {
      $e->push($error->getResponseCode(),
        0, NULL,
        $error->getMessage()
      );
    }
    elseif ($error && is_numeric($error)) {
      $e->push($error,
        0, NULL,
        $this->errorString($error)
      );
    }
    elseif (is_string($error)) {
      $e->push(9002,
        0, NULL,
        $error
      );
    }
    else {
      $e->push(9001, 0, NULL, "Unknown System Error.");
    }
    return $e;
  }

  /**
   * This function checks to see if we have the right config values.
   *
   * @return string
   *   The error message if any
   */
  public function checkConfig() {
    $error = array();

    if (empty($this->_paymentProcessor['user_name'])) {
      $error[] = ts('Agent Code is not set in the Administer CiviCRM &raquo; System Settings &raquo; Payment Processors.');
    }

    if (empty($this->_paymentProcessor['password'])) {
      $error[] = ts('Password is not set in the Administer CiviCRM &raquo; System Settings &raquo; Payment Processors.');
    }

    if (!empty($error)) {
      return implode('<p>', $error);
    }
    else {
      return NULL;
    }
  }

  /**
   * Convert the values in the civicrm params to the request array with keys as expected by iATS.
   *
   * @param array $params
   * @param string $method
   *
   * @return array
   */
  protected function convertParams($params, $method) {
    $request = array();
    $convert = array(
      'firstName' => 'billing_first_name',
      'lastName' => 'billing_last_name',
      'address' => 'street_address',
      'city' => 'city',
      'state' => 'state_province',
      'zipCode' => 'postal_code',
      'country' => 'country',
      'invoiceNum' => 'invoiceID',
      'creditCardNum' => 'credit_card_number',
      'cvv2' => 'cvv2',
    );

    foreach ($convert as $r => $p) {
      if (isset($params[$p])) {
        $request[$r] = htmlspecialchars($params[$p]);
      }
    }
    $request['creditCardExpiry'] = sprintf('%02d/%02d', $params['month'], ($params['year'] % 100));
    $request['total'] = sprintf('%01.2f', CRM_Utils_Rule::cleanMoney($params['amount']));
    // place for ugly hacks
    switch ($method) {
      case 'cc_create_customer_code':
        $request['ccNum'] = $request['creditCardNum'];
        unset($request['creditCardNum']);
        $request['ccExp'] = $request['creditCardExpiry'];
        unset($request['creditCardExpiry']);
        break;
    }
    if (!empty($params['credit_card_type'])) {
      $mop = array(
        'Visa' => 'VISA',
        'MasterCard' => 'MC',
        'Amex' => 'AMX',
        'Discover' => 'DSC',
      );
      $request['mop'] = $mop[$params['credit_card_type']];
    }
    return $request;
  }

}

