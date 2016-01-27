<?php

/**
 * Job.iATSRecurringContributions API specification
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_job_iatsrecurringcontributions_spec(&$spec) {
  $spec['recur_id'] = array(
    'name' => 'recur_id',
    'title' => 'Recurring payment id',
    'type' => 1,
  );
  $spec['scheduled'] = array(
    'name' => 'scheduled',
    'title' => 'Only scheduled contributions.',
    'type' => 1,
  );
}

/**
 * Job.iATSRecurringContributions API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_job_iatsrecurringcontributions($params) {
  // running this job in parallell could generate bad duplicate contributions
  $lock = new CRM_Core_Lock('civimail.job.IatsRecurringContributions');

  if (! $lock->acquire()) {
    return civicrm_api3_create_success(ts('Failed to acquire lock. No contribution records were processed.'));
  }

  // TODO: what kind of extra security do we want or need here to prevent it from being triggered inappropriately? Or does it matter?

  // the next scheduled contribution date field name is civicrm version dependent
  define('IATS_CIVICRM_NSCD_FID',_iats_civicrm_nscd_fid());
  // $config = &CRM_Core_Config::singleton();
  // $debug  = false;
  // do my calculations based on yyyymmddhhmmss representation of the time
  // not sure about time-zone issues
  $dtCurrentDay    = date("Ymd", mktime(0, 0, 0, date("m") , date("d") , date("Y")));
  $dtCurrentDayStart = $dtCurrentDay."000000";
  $dtCurrentDayEnd   = $dtCurrentDay."235959";
  $expiry_limit = date('ym');
  // restrict this method of recurring contribution processing to only these two payment processors
  $args = array(
    1 => array('Payment_iATSService', 'String'),
    2 => array('Payment_iATSServiceACHEFT', 'String'),
    3 => array('Payment_iATSServiceSWIPE', 'String'),
  );
  // Before triggering payments, we need to do some housekeeping of the civicrm_contribution_recur records.
  // First update the end_date and then the complete/in-progress values.
  // We do this both to fix any failed settings previously, and also
  // to deal with the possibility that the settings for the number of payments (installments) for an existing record has changed.

  // First check for recur end date values on non-open-ended recurring contribution records that are either complete or in-progress
  $select = 'SELECT cr.id, count(c.id) AS installments_done, cr.installments, cr.end_date, NOW() as test_now 
      FROM civicrm_contribution_recur cr 
      INNER JOIN civicrm_contribution c ON cr.id = c.contribution_recur_id 
      INNER JOIN civicrm_payment_processor pp ON cr.payment_processor_id = pp.id 
      WHERE 
        (pp.class_name = %1 OR pp.class_name = %2 OR pp.class_name = %3) 
        AND (cr.installments > 0) 
        AND (cr.contribution_status_id IN (1,5)) 
      GROUP BY c.contribution_recur_id';
  $dao = CRM_Core_DAO::executeQuery($select,$args);
  while ($dao->fetch()) {
    // check for end dates that should be unset because I haven't finished
    if ($dao->installments_done < $dao->installments) { // at least one more installment todo
      if (($dao->end_date > 0) && ($dao->end_date <= $dao->test_now)) { // unset the end_date
        $update = 'UPDATE civicrm_contribution_recur SET end_date = NULL WHERE id = %1';
        CRM_Core_DAO::executeQuery($update,array(1 => array($dao->id,'Int')));
      }
    }
    // otherwise, check if my end date should be set to the past because I have finished
    elseif ($dao->installments_done >= $dao->installments) { // I'm done with installments
      if (empty($dao->end_date) || ($dao->end_date >= $dao->test_now)) { 
        // this interval complete, set the end_date to an hour ago
        $update = 'UPDATE civicrm_contribution_recur SET end_date = DATE_SUB(NOW(),INTERVAL 1 HOUR) WHERE id = %1';
        CRM_Core_DAO::executeQuery($update,array(1 => array($dao->id,'Int')));
      }
    }
  }
  // Second, make sure any open-ended recurring contributions have no end date set
  $update = 'UPDATE civicrm_contribution_recur cr 
      INNER JOIN civicrm_payment_processor pp ON cr.payment_processor_id = pp.id
      SET
        cr.end_date = NULL 
      WHERE
        cr.contribution_status_id IN (1,5) 
        AND NOT(cr.installments > 0)
        AND (pp.class_name = %1 OR pp.class_name = %2 OR pp.class_name = %3)
        AND NOT(ISNULL(cr.end_date))';
  $dao = CRM_Core_DAO::executeQuery($update,$args);
  
  // Third, we update the status_id of the all in-progress or completed recurring contribution records
  // Unexpire uncompleted cycles
  $update = 'UPDATE civicrm_contribution_recur cr 
      INNER JOIN civicrm_payment_processor pp ON cr.payment_processor_id = pp.id
      SET
        cr.contribution_status_id = 5 
      WHERE
        cr.contribution_status_id = 1 
        AND (pp.class_name = %1 OR pp.class_name = %2 OR pp.class_name = %3)
        AND (cr.end_date IS NULL OR cr.end_date > NOW())';
  $dao = CRM_Core_DAO::executeQuery($update,$args);
  // Expire or badly-defined completed cycles
  $update = 'UPDATE civicrm_contribution_recur cr 
      INNER JOIN civicrm_payment_processor pp ON cr.payment_processor_id = pp.id
      SET
        cr.contribution_status_id = 1 
      WHERE
        cr.contribution_status_id = 5 
        AND (pp.class_name = %1 OR pp.class_name = %2 OR pp.class_name = %3)
        AND (
          (NOT(cr.end_date IS NULL) AND cr.end_date <= NOW())
          OR
          ISNULL(cr.frequency_unit)
          OR 
          (frequency_interval = 0) 
        )';
  $dao = CRM_Core_DAO::executeQuery($update,$args);

  // Now we're ready to trigger payments
  // Select the ongoing recurring payments for iATSServices where the next scheduled contribution date (NSCD) is before the end of of the current day
  $select = 'SELECT cr.*, icc.customer_code, icc.expiry as icc_expiry, icc.cid as icc_contact_id, pp.class_name as pp_class_name, pp.url_site as url_site, pp.is_test 
      FROM civicrm_contribution_recur cr 
      INNER JOIN civicrm_payment_processor pp ON cr.payment_processor_id = pp.id
      INNER JOIN civicrm_iats_customer_codes icc ON cr.id = icc.recur_id
      WHERE 
        cr.contribution_status_id = 5
        AND (pp.class_name = %1 OR pp.class_name = %2 OR pp.class_name = %3)';
  //      AND pp.is_test = 0
  if (!empty($params['recur_id'])) { // in case the job was called to execute a specific recurring contribution id -- not yet implemented!
    $select .= ' AND icc.recur_id = %4';
    $args[4] = array($params['recur_id'], 'Int');
  }
  else { // if (!empty($params['scheduled'])) { 
    //normally, process all recurring contributions due today or earlier
    $select .= ' AND cr.'.IATS_CIVICRM_NSCD_FID.' <= %4';
    $args[4] = array($dtCurrentDayEnd, 'String');
    // ' AND cr.next_sched_contribution >= %2 
    // $args[2] = array($dtCurrentDayStart, 'String');
  }
  $dao = CRM_Core_DAO::executeQuery($select,$args);
  $counter = 0;
  $error_count  = 0;
  $output  = array();
  $settings = CRM_Core_BAO_Setting::getItem('iATS Payments Extension', 'iats_settings');
  $receipt_recurring = empty($settings['receipt_recurring']) ? 0 : 1;

  while ($dao->fetch()) {

    // Strategy: create the contribution record with status = 2 (= pending), try the payment, and update the status to 1 if successful
    // Try to get a contribution template for this contribution series - if none matches (e.g. if a donation amount has been changed), we'll just be naive about it.
    $contribution_template = _iats_civicrm_getContributionTemplate(array('contribution_recur_id' => $dao->id, 'total_amount' => $dao->amount));
    $contact_id = $dao->contact_id;
    $total_amount = $dao->amount;
    $hash = md5(uniqid(rand(), true));
    $contribution_recur_id    = $dao->id;
    $subtype = substr($dao->pp_class_name,19);
    $source = "iATS Payments $subtype Recurring Contribution (id=$contribution_recur_id)"; 
    $receive_date = date("YmdHis"); // i.e. now
    // check if we already have an error
    $errors = array();
    if (empty($dao->customer_code)) {
      $errors[] = ts('Recur id %1 is missing a customer code.', array(1 => $contribution_recur_id));
    } 
    else {
      if ($dao->contact_id != $dao->icc_contact_id) {
        $errors[] = ts('Recur id %1 is has a mismatched contact id for the customer code.', array(1 => $contribution_recur_id));
      }
      if (($dao->icc_expiry != '0000') && ($dao->icc_expiry < $expiry_limit)) {
        $errors[] = ts('Recur id %1 is has an expired cc for the customer code.', array(1 => $contribution_recur_id));
      }
    }
    if (count($errors)) {
      $source .= ' Errors: '.implode(' ',$errors);
    }
    $contribution = array(
      'version'        => 3,
      'contact_id'       => $contact_id,
      'receive_date'       => $receive_date,
      'total_amount'       => $total_amount,
      'payment_instrument_id'  => $dao->payment_instrument_id,
      'contribution_recur_id'  => $contribution_recur_id,
      'invoice_id'       => $hash,
      'source'         => $source,
      'contribution_status_id' => 2, /* initialize as pending, so we can run completetransaction after taking the money */
      'currency'  => $dao->currency,
      'payment_processor'   => $dao->payment_processor_id,
      'is_test'        => $dao->is_test, /* propagate the is_test value from the parent contribution */
    );
    $get_from_template = array('contribution_campaign_id','amount_level');
    foreach($get_from_template as $field) {
      if (isset($contribution_template[$field])) {
        $contribution[$field] = is_array($contribution_template[$field]) ?  implode(', ',$contribution_template[$field]) : $contribution_template[$field];
      }
    }
    if (isset($dao->contribution_type_id)) {  // 4.2
       $contribution['contribution_type_id'] = $dao->contribution_type_id;
    }
    else { // 4.3+
       $contribution['financial_type_id'] = $dao->financial_type_id;
    }
    if (!empty($contribution_template['line_items'])) {
      $contribution['skipLineItem'] = 1;
      $contribution[ 'api.line_item.create'] = $contribution_template['line_items'];
    }
    if (count($errors)) {
      ++$error_count;
      ++$counter;
      /* create a failed contribution record, don't bother talking to iats */
      $contribution['contribution_status_id'] = 4;
      $contributionResult = civicrm_api('contribution', 'create', $contribution);
      if ($contributionResult['is_error']) {
        $errors[] = $contributionResult['error_message'];
      }
      continue;
    }
    else { 
      // so far so, good ... create the pending contribution, and save its id
      $contributionResult = civicrm_api('contribution','create', $contribution);
      $contribution_id = CRM_Utils_Array::value('id', $contributionResult);
      // if our template contribution has a membership payment, make this one also
      if (!empty($contribution_template['contribution_id'])) {
        try {
          $membership_payment = civicrm_api('MembershipPayment','getsingle', array('version' => 3, 'contribution_id' => $contribution_template['contribution_id']));
          if (!empty($membership_payment['membership_id'])) {
            civicrm_api('MembershipPayment','create', array('version' => 3, 'contribution_id' => $contribution_id, 'membership_id' => $membership_payment['membership_id']));
          }
        }
        catch (Exception $e) {
          // ignore, if will fail correctly if there is no membership payment
        }
      } 
      // now try to get the money, and then do one of: update the contribution to failed, complete the transaction, or update a pending ach/eft with it's transaction id
      require_once("CRM/iATS/iATSService.php");
      switch($subtype) {
        case 'ACHEFT':
          $method = 'acheft_with_customer_code';
          $contribution_status_id = 2; // will not complete
          break;
        default:
          $method = 'cc_with_customer_code';
          $contribution_status_id = 1;
          break;
      }
      $iats_service_params = array('method' => $method, 'type' => 'process', 'iats_domain' => parse_url($dao->url_site, PHP_URL_HOST));
      $iats = new iATS_Service_Request($iats_service_params);
      // build the request array
      $request = array(
        'customerCode' => $dao->customer_code,
        'invoiceNum' => $hash,
        'total' => $total_amount,
      );
      $request['customerIPAddress'] = (function_exists('ip_address') ? ip_address() : $_SERVER['REMOTE_ADDR']);

      $credentials = iATS_Service_Request::credentials($dao->payment_processor_id, $contribution['is_test']);
      // make the soap request
      $response = $iats->request($credentials,$request);
      // process the soap response into a readable result
      $result = $iats->result($response);
      if (empty($result['status'])) {
        /* update the contribution record in civicrm with the failed status and include the reason in the source field */
        $contribution = array('version' => 3, 'id' => $contribution_id, 'source' => $contribution['source'].' '.$result['reasonMessage'], 'contribution_status_id' => 4);
        $contributionResult = civicrm_api('contribution', 'create', $contribution);
        $output[] = ts('Failed to process recurring contribution id %1: ', array(1 => $contribution_recur_id)).$result['reasonMessage'];
      } 
      elseif ($contribution_status_id == 1) {
        /* success, done */
        $trxn_id = trim($result['remote_id']) . ':' . time();
        $complete = array('version' => 3, 'id' => $contribution_id, 'trxn_id' => $trxn_id, 'receive_date' => $receive_date);
        $complete['is_email_receipt'] = $receipt_recurring; /* do not send receipt by default. TODO: make it configurable */
        try {
          $contributionResult = civicrm_api('contribution', 'completetransaction', $complete);
          // restore my source field that ipn irritatingly overwrites, and make sure that the trxn_id is set also
          civicrm_api('contribution','setvalue', array('version' => 3, 'id' => $contribution_id, 'value' => $source, 'field' => 'source'));
          civicrm_api('contribution','setvalue', array('version' => 3, 'id' => $contribution_id, 'value' => $trxn_id, 'field' => 'trxn_id'));
        }
        catch (Exception $e) {
          throw new API_Exception('Failed to complete transaction: ' . $e->getMessage() . "\n" . $e->getTraceAsString()); 
        }
        $output[] = ts('Successfully processed recurring contribution id %1: ', array(1 => $contribution_recur_id)).$result['auth_result'];
      }
      else { // success, but just update the transaction id, wait for completion
        $contribution = array('version' => 3, 'id' => $contribution_id, 'trxn_id' => trim($result['remote_id']) . ':' . time());
        $contributionResult = civicrm_api('contribution', 'create', $contribution);
        $output[] = ts('Successfully processed pending recurring contribution id %1: ', array(1 => $contribution_recur_id)).$result['auth_result'];
      }
    }

    //$mem_end_date = $member_dao->end_date;
    // $temp_date = strtotime($dao->next_sched_contribution);
    /* calculate the next collection date. You could use the previous line instead if you wanted to catch up with missing contributions instead of just moving forward from the present */
    $temp_date = time();
    $next_collectionDate = strtotime ("+$dao->frequency_interval $dao->frequency_unit", $temp_date);
    $next_collectionDate = date('YmdHis', $next_collectionDate);

    CRM_Core_DAO::executeQuery("
      UPDATE civicrm_contribution_recur 
         SET ".IATS_CIVICRM_NSCD_FID." = %1 
       WHERE id = %2
    ", array(
         1 => array($next_collectionDate, 'String'),
         2 => array($dao->id, 'Int')
       )
    );

    $result = civicrm_api('activity', 'create',
      array(
        'version'       => 3,
        'activity_type_id'  => 6,
        'source_contact_id'   => $contact_id,
        'source_record_id' => CRM_Utils_Array::value('id', $contributionResult),
        'assignee_contact_id' => $contact_id,
        'subject'       => "Attempted iATS Payments $subtype Recurring Contribution for " . $total_amount,
        'status_id'       => 2,
        'activity_date_time'  => date("YmdHis"),
      )
    );
    if ($result['is_error']) {
      $output[] = ts(
        'An error occurred while creating activity record for contact id %1: %2',
        array(
          1 => $contact_id,
          2 => $result['error_message']
        )
      );
      ++$error_count;
    } else {
      $output[] = ts('Created activity record for contact id %1', array(1 => $contact_id));

    }
    ++$counter;
  }

  // now update the end_dates and status for non-open-ended contribution series if they are complete (so that the recurring contribution status will show correctly)
  // This is a simplified version of what we did before the processing
  $select = 'SELECT cr.id, count(c.id) AS installments_done, cr.installments
      FROM civicrm_contribution_recur cr 
      INNER JOIN civicrm_contribution c ON cr.id = c.contribution_recur_id 
      INNER JOIN civicrm_payment_processor pp ON cr.payment_processor_id = pp.id 
      WHERE 
        (pp.class_name = %1 OR pp.class_name = %2 OR pp.class_name = %3) 
        AND (cr.installments > 0) 
        AND (cr.contribution_status_id  = 5) 
      GROUP BY c.contribution_recur_id';
  $dao = CRM_Core_DAO::executeQuery($select,$args);
  while ($dao->fetch()) {
    // check if my end date should be set to now because I have finished
    if ($dao->installments_done >= $dao->installments) { // I'm done with installments
      // set this series complete and the end_date to now
      $update = 'UPDATE civicrm_contribution_recur SET contribution_status_id = 1, end_date = NOW() WHERE id = %1';
      CRM_Core_DAO::executeQuery($update,array(1 => array($dao->id,'Int')));
    }
  }

  $lock->release();
  // If errors ..
  if ($error_count) {
    return civicrm_api3_create_error(
      ts("Completed, but with %1 errors. %2 records processed.",
        array(
          1 => $error_count,
          2 => $counter
        )
      ) . "<br />" . implode("<br />", $output)
    );
  }
  // If no errors and records processed ..
  if ($counter) {
    return civicrm_api3_create_success(
      ts(
        '%1 contribution record(s) were processed.',
        array(
          1 => $counter
        )
      ) . "<br />" . implode("<br />", $output)
    );
  }
  // No records processed
  return civicrm_api3_create_success(ts('No contribution records were processed.'));

}
