<?php

/**
 * @file Copyright iATS Payments (c) 2020.
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
 * This code provides glue between CiviCRM payment model and the iATS Payment model encapsulated in the CRM_Iats_iATSServiceRequest object
 */

use Civi\Payment\Exception\PaymentProcessorException;

/**
 *
 */
class CRM_Core_Payment_iATSService extends CRM_Core_Payment {

  /**
   * We only need one instance of this object. So we use the singleton
   * pattern and cache the instance in this variable.
   *
   * @var object
   * @static
   */
  static private $_singleton = NULL;

  /**
   * @var array
   * @todo Is the `mode` key is ever used? And it only seems to have one
   * other key `iats_domain`, so maybe should remove mode and rename this.
   * The parent core class doesn't seem to use `_profile`.
   */
  protected $_profile;

  /**
   * Constructor.
   *
   * @param string $mode
   *   the mode of operation: live or test.
   *
   * @param array $paymentProcessor
   */
  public function __construct($mode, &$paymentProcessor, &$paymentForm = NULL, $force = FALSE) {
    $this->_paymentProcessor = $paymentProcessor;

    // Get merchant data from config.
    $config = CRM_Core_Config::singleton();
    // Live or test.
    $this->_profile['mode'] = $mode;
    // We only use the domain of the configured url, which is different for NA vs. UK.
    $this->_profile['iats_domain'] = parse_url($this->_paymentProcessor['url_site'], PHP_URL_HOST);
  }

  /**
   *
   */
  static public function &singleton($mode, &$paymentProcessor, &$paymentForm = NULL, $force = FALSE) {
    $processorName = $paymentProcessor['name'];
    if (self::$_singleton[$processorName] === NULL) {
      self::$_singleton[$processorName] = new CRM_Core_Payment_iATSService($mode, $paymentProcessor);
    }
    return self::$_singleton[$processorName];
  }

  /**
   * Get the iATS configuration values or value.
   *
   * Mangle the days settings to make it easier to test if it is set.
   */
  protected function getSettings($key = '') {
    static $settings = array();
    if (empty($settings)) {
      try {
        $settings = CRM_Iats_Utils::getSettings();
        if (empty($settings['days'])) {
          $settings['days'] = array('-1');
        }
      }
      catch (CRM_Core_Exception $e) {
        // Assume no settings exist, use safest fallback.
        $settings = array('days' => array('-1'));
      }
    }
    return (empty($key) ? $settings : (empty($settings[$key]) ? '' : $settings[$key]));
  }

  /**
   * Override the default way of testing if a method is supported to enable admin configuration of certain
   * functions.
   * Where certain functions currently only means updateSubscriptionBillingInfo, which we'll allow for credit cards.
   *
   * Core says this method is now deprecated, so I might need to change this in the future, but this is how it is used now.
   */
  public function isSupported($method) {
    switch($method) {
      case 'updateSubscriptionBillingInfo':
        if ('CRM_Core_Payment_iATSServiceACHEFT' == CRM_Utils_System::getClassName($this)) {
          return FALSE;
        }
        elseif (!CRM_Core_Permission::check('access CiviContribute')) {
          // disable self-service update of billing info if the admin has not allowed it
          if (FALSE == $this->getSettings('enable_update_subscription_billing_info')) {
            return FALSE;
          }
        }
        break;
    }
    // this is the default method
    return method_exists(CRM_Utils_System::getClassName($this), $method);
  }

  /**
   * Internal function to determine if I'm supporting self-service functions
   *
   * @return bool
   */
  protected function allowSelfService($action) {
    if (!CRM_Core_Permission::check('access CiviContribute')) {
      // disable self-service 'action' if the admin has not allowed it
      if (FALSE == $this->getSettings('enable_'.$action)) {
	return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * The first payment date is configurable when setting up back office recurring payments.
   * For iATSPayments, this is also true for front-end recurring payments.
   *
   * @return bool
   */
  public function supportsFutureRecurStartDate() {
    return TRUE;
  }

  /* We override the default behaviour of allowing self-service editing of recurring contributions
   * to use the allowSelfService() function that tests for the iATS configuration setting
   *
   * @return bool
   */

  public function supportsUpdateSubscriptionBillingInfo() {
    // the CiviCRM internal form for updating billing info for ACHEFT is inadequate
    if ('CRM_Core_Payment_iATSServiceACHEFT' == CRM_Utils_System::getClassName($this)) {
      return FALSE;
    }
    return $this->allowSelfService('update_subscription_billing_info');
  }

  public function supportsEditRecurringContribution() {
    return $this->allowSelfService('change_subscription_amount');
  }

  public function supportsChangeSubscriptionAmount() {
    return $this->allowSelfService('change_subscription_amount');
  }

  public function supportsCancelRecurring() {
    return $this->allowSelfService('cancel_recurring');
  }

  /**
   *
   */
  public function doPayment(&$params, $component = 'contribute') {

    if (empty($params['amount'])) {
      return _iats_payment_status_complete();
    }
    if (!$this->_profile) {
      return self::error('Unexpected error, missing profile');
    }
    // Use the iATSService object for interacting with iATS. Recurring contributions go through a more complex process.
    $isRecur = CRM_Utils_Array::value('is_recur', $params);
    if ($isRecur && empty($params['contributionRecurID'])) {
      return self::error('Invalid call to doPayment with is_recur and no contributionRecurID');
    }
    $methodType = $isRecur ? 'customer' : 'process';
    $method = $isRecur ? 'create_credit_card_customer' : 'cc';
    $iats = new CRM_Iats_iATSServiceRequest(array('type' => $methodType, 'method' => $method, 'iats_domain' => $this->_profile['iats_domain'], 'currency' => $params['currency']));
    $request = $this->convertParams($params, $method);
    $request['customerIPAddress'] = CRM_Iats_Transaction::remote_ip_address();
    $credentials = array(
      'agentCode' => $this->_paymentProcessor['user_name'],
      'password'  => $this->_paymentProcessor['password'],
    );
    // Get the API endpoint URL for the method's transaction mode.
    // TODO: enable override of the default url in the request object
    // $url = $this->_paymentProcessor['url_site'];.
    // Make the soap request.
    $response = $iats->request($credentials, $request);
    if (!$isRecur) {
      // Process the soap response into a readable result, logging any credit card transactions.
      $result = $iats->result($response);
      if ($result['status']) {
        // Success.
        $params['payment_status_id'] = 1;
        $params['trxn_id'] = trim($result['remote_id']) . ':' . time();
        return $params;
      }
      else {
        return self::error($result['reasonMessage']);
      }
    }
    else {
      // Save the customer info in the payment_token table, then (maybe) run the transaction.
      $customer = $iats->result($response, FALSE);
      // print_r($customer);
      if ($customer['status']) {
        $processresult = $response->PROCESSRESULT;
        $customer_code = (string) $processresult->CUSTOMERCODE;
        $expiry_date = sprintf('%04d-%02d-01', $params['year'], $params['month']);
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
        $payment_token_params = [
          'token' => $customer_code,
          'ip_address' => $request['customerIPAddress'],
          'expiry_date' => $expiry_date,
          'contact_id' => $params['contactID'],
          'email' => $email,
          'payment_processor_id' => $this->_paymentProcessor['id'],
        ];
        $token_result = civicrm_api3('PaymentToken', 'create', $payment_token_params);
        // Upon success, save the token table's id back in the recurring record.
        if (!empty($token_result['id'])) {
          civicrm_api3('ContributionRecur', 'create', [
            'id' => $params['contributionRecurID'],
            'payment_token_id' => $token_result['id'],
          ]);
        }
        // Test for admin setting that limits allowable transaction days
        $allow_days = $this->getSettings('days');
        // Test for a specific receive date request and convert to a timestamp, default now
        $receive_date = CRM_Utils_Array::value('receive_date', $params);
        // my front-end addition to will get stripped out of the params, do a
        // work-around
        if (empty($receive_date)) {
          $receive_date = CRM_Utils_Array::value('receive_date', $_POST);
        }
        $receive_ts = empty($receive_date) ? time() : strtotime($receive_date);
        // If the admin setting is in force, ensure it's compatible.
        if (!empty($allow_days) && (max($allow_days) > 0)) {
          $receive_ts = CRM_Iats_Transaction::contributionrecur_next($receive_ts, $allow_days);
        }
        // convert to a reliable format
        $receive_date = date('Ymd', $receive_ts);
        $today = date('Ymd');
        // If the receive_date is NOT today, then
        // create a pending contribution and adjust the next scheduled date.
        if ($receive_date !== $today) {
          // I've got a schedule to adhere to!
          // set the receieve time to 3:00 am for a better admin experience
          $update = array(
            'payment_status_id' => 2,
            'receive_date' => date('Ymd', $receive_ts) . '030000',
          );
          // update the recurring and contribution records with the receive date,
          // i.e. make up for what core doesn't do
          $this->updateRecurring($params, $update);
          $this->updateContribution($params, $update);
          // and now return the updates to core via the params
          $params = array_merge($params, $update);
          return $params;
        }
        else {
          // run the (first) transaction immediately
          $iats = new CRM_Iats_iATSServiceRequest(array('type' => 'process', 'method' => 'cc_with_customer_code', 'iats_domain' => $this->_profile['iats_domain'], 'currency' => $params['currency']));
          $request = array('invoiceNum' => $params['invoiceID']);
          $request['total'] = sprintf('%01.2f', CRM_Utils_Rule::cleanMoney($params['amount']));
          $request['customerCode'] = $customer_code;
          $request['customerIPAddress'] = (function_exists('ip_address') ? ip_address() : $_SERVER['REMOTE_ADDR']);
          $response = $iats->request($credentials, $request);
          $result = $iats->result($response);
          if ($result['status']) {
            // Add a time string to iATS short authentication string to ensure
            // uniqueness and provide helpful referencing.
            $update = array(
              'trxn_id' => trim($result['remote_id']) . ':' . time(),
              'payment_status_id' => 1,
            );
            // do some cleanups to the recurring record in updateRecurring
            $this->updateRecurring($params, $update);
            $params = array_merge($params, $update);
            return $params;
          }
          else {
            return self::error($result['reasonMessage']);
          }
        }
        return self::error('Unexpected error');
      }
      else {
        return self::error($customer['reasonMessage']);
      }
    }
  }

  /**
   * support corresponding CiviCRM method
   */
  public function changeSubscriptionAmount(&$message = '', $params = array()) {
    // $userAlert = ts('You have modified this recurring contribution.');
    // CRM_Core_Session::setStatus($userAlert, ts('Warning'), 'alert');
    return TRUE;
  }

  /**
   * support corresponding CiviCRM method
   */
  public function cancelSubscription(&$message = '', $params = array()) {
    $userAlert = ts('You have cancelled this recurring contribution.');
    CRM_Core_Session::setStatus($userAlert, ts('Warning'), 'alert');
    return TRUE;
  }

  /**
   * Set additional fields when editing the schedule.
   *
   * Note: this doesn't completely replace the form hook, which is still
   * in use for additional changes, and to support 4.6.
   * e.g. the commented out fields below don't work properly here.
   */
  public function getEditableRecurringScheduleFields() {
    return array('amount',
         'installments',
         'next_sched_contribution_date',
//         'contribution_status_id',
//         'start_date',
//         'is_email_receipt',
       );
  }

  /*
   * Set a useful message at the top of the schedule editing form
   */
  public function getRecurringScheduleUpdateHelpText() {
    return 'Use this form to change the amount or number of installments for this recurring contribution. You can not change the contribution frequency.<br />You can also modify the next scheduled contribution date, and whether or not the recipient will get email receipts for each contribution.<br />You have an option to notify the donor of these changes.';
  }

  /**
   *
   */
  public function &error($error = NULL) {
    if (is_object($error)) {
      throw new PaymentProcessorException(ts('Error %1', [1 => $error->getMessage()]));
    }
    elseif ($error && is_numeric($error)) {
      throw new PaymentProcessorException(ts('Error %1', [1 => $this->errorString($error)]));
    }
    elseif (is_string($error)) {
      throw new PaymentProcessorException(ts('Error %1', [1 => $error]));
    }
    else {
      throw new PaymentProcessorException(ts('Unknown System Error.', 9001));
    }
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
    $request = [];
    $convert = [
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
    ];

    // for each of the 'convert' pairs, assign the param key value to the array
    // that we're going to submit to the iATS server with it's new key
    foreach ($convert as $r => $p) {
      if (isset($params[$p])) {
        // The ";" character wouldn't normally be used in these fields, but paysafe
        // will reject the submission if it appears, so we'll strip it
        // out to be safe.
        $request[$r] = str_replace(';', '', $params[$p]);
      }
    }
    // The "&" character is (sometimes?) badly handled by the processor,
    // so we convert it to "and" when it appears in first or last name
    $request['firstName'] = str_replace('&', 'and', $request['firstName']);
    $request['lastName'] = str_replace('&', 'and', $request['lastName']);

    $request['creditCardExpiry'] = sprintf('%02d/%02d', intval($params['month']), (intval($params['year']) % 100));
    $request['total'] = sprintf('%01.2f', CRM_Utils_Rule::cleanMoney($params['amount']));
    // Place for ugly hacks.
    switch ($method) {
      case 'cc_create_customer_code':
        $request['ccNum'] = $request['creditCardNum'];
        unset($request['creditCardNum']);
        $request['ccExp'] = $request['creditCardExpiry'];
        unset($request['creditCardExpiry']);
        break;

      case 'cc_with_customer_code':
        foreach (array('creditCardNum', 'creditCardExpiry', 'mop') as $key) {
          if (isset($request[$key])) {
            unset($request[$key]);
          }
        }
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
    // print_r($request); print_r($params); die();
    return $request;
  }

  /*
   * Implement the ability to update the billing info for recurring contributions,
   * This functionality will apply to back-end and front-end,
   * so it's only enabled when configured as on via the iATS admin settings.
   * The default isSupported method is overridden above to achieve this.
   *
   * Return TRUE on success or an error.
   */
  public function updateSubscriptionBillingInfo(&$message = '', $params = array()) {

    // updatedBillingInfo array has changed a few times, we'll try a few different keys to pull the contribution recurring id
    $crid = !empty($params['contributionRecurID']) ? $params['contributionRecurID'] : (!empty($params['crid']) ? $params['crid'] : $params['recur_id']);
    if (empty($crid)) {
      $alert = ts('This system is unable to perform self-service updates to credit cards. Please contact the administrator of this site.');
      throw new Exception($alert);
    }
    $mop = array(
      'Visa' => 'VISA',
      'MasterCard' => 'MC',
      'Amex' => 'AMX',
      'Discover' => 'DSC',
    );
    $contribution_recur = civicrm_api3('ContributionRecur', 'getsingle', ['id' => $crid]);
    $payment_token = $result = civicrm_api3('PaymentToken', 'getsingle', ['id' => $contribution_recur['payment_token_id']]);
    // construct the array of data that I'll submit to the iATS Payments server.
    $state_province = civicrm_api3('StateProvince', 'getsingle', ['return' => ["abbreviation"], 'id' => $params['state_province_id']]);
    $submit_values = array(
      'cid' => $contribution_recur['contact_id'],
      'customerCode' => $payment_token['token'],
      'creditCardCustomerName' => "{$params['first_name']} " . (!empty($params['middle_name']) ? "{$params['middle_name']} " : '') . $params['last_name'],
      'address' => $params['street_address'],
      'city' => $params['city'],
      'state' => $state_province['abbreviation'],
      'zipCode' => $params['postal_code'],
      'creditCardNum' => $params['credit_card_number'],
      'creditCardExpiry' => sprintf('%02d/%02d', intval($params['month']), intval($params['year']) % 100),
      'mop' => $mop[$params['credit_card_type']],
    );

    // IATS-323
    $submit_values['updateCreditCardNum'] = (0 < strlen($submit_values['creditCardNum']) && (FALSE === strpos($params['creditCardNum'], '*'))) ? 1 : 0;
    if (empty($submit_values['updateCreditCardNum'])) {
      unset($submit_values['creditCardNum']);
      unset($submit_values['updateCreditCardNum']);
    }

    $credentials = CRM_Iats_iATSServiceRequest::credentials($contribution_recur['payment_processor_id'], 0);
    $iats_service_params = array('type' => 'customer', 'iats_domain' => $credentials['domain'], 'method' => 'update_credit_card_customer');
    $iats = new CRM_Iats_iATSServiceRequest($iats_service_params);
    $submit_values['customerIPAddress'] = (function_exists('ip_address') ? ip_address() : $_SERVER['REMOTE_ADDR']);
    // Make the soap request.
    try {
      $response = $iats->request($credentials, $submit_values);
      // note: don't log this to the iats_response table.
      $iats_result = $iats->result($response, TRUE);
      // CRM_Core_Error::debug_var('iats result', $iats_result);
      if ('OK' == $iats_result['AUTHORIZATIONRESULT']) {
        // Update my copy of the expiry date.
        $result = civicrm_api3('PaymentToken', 'get', [
          'return' => ['id'],
          'token' => $values['customerCode'],
        ]);
        if (count($result['values'])) {
          list($month, $year) = explode('/', $values['creditCardExpiry']);
          $expiry_date = sprintf('20%02d-%02d-01', $year, $month);
          foreach(array_keys($result['values']) as $id) {
            civicrm_api3('PaymentToken', 'create', [
              'id' => $id,
              'expiry_date' => $expiry_date,
            ]);
          }
        }
        return TRUE;
      }
      return $this->error('9002','Authorization failed');
    }
    catch (Exception $error) { // what could go wrong?
      $message = $error->getMessage();
      return $this->error('9002', $message);
    }
  }

  /*
   * Update the recurring contribution record.
   *
   * Do some cleanup and implement
   * the ability to set a future start date for recurring contributions.
   * This functionality will apply to back-end and front-end,
   * As enabled when configured via the iATS admin settings.
   *
   * Returns result of api request if a change is made, usually ignored.
   */
  protected function updateRecurring($params, $update) {
    // If the recurring record already exists, let's fix the next contribution and start dates,
    // in case core isn't paying attention.
    // We also set the schedule to 'in-progress' (even for ACH/EFT when the first one hasn't been verified),
    // because we want the recurring job to run for this schedule.
    if (!empty($params['contributionRecurID'])) {
      $recur_id = $params['contributionRecurID'];
      $recur_update = array(
        'id' => $recur_id,
        'contribution_status_id' => 'In Progress',
      );
      // use the receive date to set the next sched contribution date.
      // By default, it's empty, unless we've got a future start date.
      if (empty($update['receive_date'])) {
        $next = strtotime('+' . $params['frequency_interval'] . ' ' . $params['frequency_unit']);
        // handle the special case of monthly contributions made after the 28th
        if ('month' == $params['frequency_unit']) {
          $now = getdate();
          if ($now['mday'] > 28) { // pull it back to the 28th
            $next = $next - (($now['mday'] - 28) * 24 * 60 * 60);
          }
        }
        $recur_update['next_sched_contribution_date'] = date('Ymd', $next) . '030000';
      }
      else {
        $recur_update['start_date'] = $recur_update['next_sched_contribution_date'] = $update['receive_date'];
        // If I've got a monthly schedule, let's set the cycle_day for niceness
        if ('month' == $params['frequency_interval']) {
          $recur_update['cycle_day'] = date('j', strtotime($recur_update['start_date']));
        }
      }
      try {
        $result = civicrm_api3('ContributionRecur', 'create', $recur_update);
        return $result;
      }
      catch (CRM_Core_Exception $e) {
        // Not a critical error, just log and continue.
        $error = $e->getMessage();
        Civi::log()->info('Unexpected error updating the next scheduled contribution date for id {id}: {error}', array('id' => $recur_id, 'error' => $error));
      }
    }
    else {
      Civi::log()->info('Unexpectedly unable to update the next scheduled contribution date, missing id.');
    }
    return FALSE;
  }

  /*
   * Update the contribution record.
   *
   * This function will alter the civi contribution record.
   * Implemented only to update the receive date.
   */
  protected function updateContribution($params, $update = array()) {
    if (!empty($params['contributionID'])  && !empty($update['receive_date'])) {
      $contribution_id = $params['contributionID'];
      $update = array(
        'id' => $contribution_id,
        'receive_date' => $update['receive_date']
      );
      try {
        $result = civicrm_api3('Contribution', 'create', $update);
        return $result;
      }
      catch (CRM_Core_Exception $e) {
        // Not a critical error, just log and continue.
        $error = $e->getMessage();
        Civi::log()->info('Unexpected error updating the contribution date for id {id}: {error}', array('id' => $contribution_id, 'error' => $error));
      }
    }
    return false;
  }

}
