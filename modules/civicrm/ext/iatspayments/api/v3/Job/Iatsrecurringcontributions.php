<?php
use CRM_Iats_ExtensionUtil as E;

/**
 * Job.Iatsrecurringcontributions API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_job_Iatsrecurringcontributions_spec(&$spec) {
  $spec['recur_id'] = array(
    'name' => 'recur_id',
    'title' => 'Recurring payment id',
    'api.required' => 0,
    'type' => 1,
  );
  $spec['cycle_day'] = array(
    'name' => 'cycle_day',
    'title' => 'Only contributions that match a specific cycle day.',
    'api.required' => 0,
    'type' => 1,
  );
  $spec['failure_count'] = array(
    'name' => 'failure_count',
    'title' => 'Filter by number of failure counts',
    'api.required' => 0,
    'type' => 1,
  );
  $spec['catchup'] = array(
    'title' => 'Process as if in the past to catch up.',
    'api.required' => 0,
  );
  $spec['ignoremembership'] = array(
    'title' => 'Ignore memberships',
    'api.required' => 0,
  );
  $spec['stale_limit'] = array(
    'title' => 'Limit stale schedules, in days',
    'api.required' => 0,
  );
  $spec['failsafe_limit'] = array(
    'title' => 'Number of stale schedules to halt at',
    'api.required' => 0,
  );
}

/**
 * Job.Iatsrecurringcontributions API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws CRM_Core_Exception
 */
function civicrm_api3_job_Iatsrecurringcontributions($params) {
  // Running this job in parallell could generate bad duplicate contributions.
  $lock = new CRM_Core_Lock('civicrm.job.Iatsrecurringcontributions');

  if (!$lock->acquire()) {
    return civicrm_api3_create_success(ts('Failed to acquire lock. No contribution records were processed.'));
  }
  // Restrict this method of recurring contribution processing to only iATS (Faps + Legacy) active payment processors.
  // TODO: exclude test processors?
  $fapsProcessors = _iats_filter_payment_processors('Faps%', array(), array('active' => 1));
  $iatsProcessors = _iats_filter_payment_processors('iATS%', array(), array('active' => 1));
  $paymentProcessors = $fapsProcessors + $iatsProcessors;
  if (empty($paymentProcessors)) {
    return;
  }
  // stale_limit restricts processing of schedules by next_sched_contribution_date no further in the past than this number of days, defaulting to 7.
  $stale_limit = empty($params['stale_limit']) ? 7 : (integer) $params['stale_limit'];
  unset($params['stale_limit']);
  // failsafe_limit restricts all processing of schedules if the number of stale schedules exceeds this number. Defaults to 0 = ignore.
  $failsafe_limit = empty($params['failsafe_limit']) ? 0 : (integer) $params['failsafe_limit'];
  unset($params['failsafe_limit']);
  // use catchup mode to calculate next scheduled contribution based on current value rather than current date
  $catchup = !empty($params['catchup']);
  unset($params['catchup']);
  // do memberships by default, i.e. copy any membership information/relationship from contribution template
  $domemberships = empty($params['ignoremembership']);
  unset($params['ignoremembership']);
  // $config = &CRM_Core_Config::singleton();
  // $debug  = false;
  // do my calculations based on yyyymmddhhmmss representation of the time
  // not sure about time-zone issues.
  $dtCurrentDay    = date("Ymd", mktime(0, 0, 0, date("m"), date("d"), date("Y")));
  $dtCurrentDayEnd   = $dtCurrentDay . "235959";
  $stale_date = date('Y-m-d', strtotime('-'.$stale_limit.' days')). ' 00:00:00';
  $expiry_limit = date('ym');
  // TODO: before triggering payments, do some housekeeping of the civicrm_contribution_recur records?
  // Now we're ready to trigger payments
  // Select the ongoing recurring payments for FAPS where the next scheduled contribution date is before the end of of the current day.
  $get = array(
      'next_sched_contribution_date' => ['<=' => $dtCurrentDayEnd],
      'payment_processor_id' => ['IN' => array_keys($paymentProcessors)],
      'contribution_status_id' => ['IN' => ['In Progress']],
      'payment_token_id' => ['>' => 0],
      'options' => ['limit' => 0],
      'return' => ['id', 'contact_id', 'amount', 'failure_count', 'payment_processor_id', 'next_sched_contribution_date',
        'payment_instrument_id', 'is_test', 'currency', 'financial_type_id','is_email_receipt',
        'frequency_interval', 'frequency_unit', 'payment_token_id'],
  );
  // additional filters that may be passed in as params
  if (!empty($params['recur_id'])) {
    $get['id'] = $params['recur_id'];
  }
  if (!empty($params['cycle_day'])) {
    $get['cycle_day'] = $params['cycle_day'];
  }
  if (isset($params['failure_count'])) {
    $get['failure_count'] = $params['failure_count'];
  }
  $recurringContributions = civicrm_api3('ContributionRecur', 'get',  $get);
  //CRM_Core_Error::debug_var('Recurring contributions get params', $get);
  //CRM_Core_Error::debug_var('Recurring contributions to be generated for', $recurringContributions['values']);
  // Do some initialization before we start processing the schedules, including getting some error handling settings
  $failsafeFlag = FALSE;
  $counter = 0;
  $error_count  = 0;
  $output  = [];
  // flag failsafe behaviour if failsafe_limit is set and we have more than that many stale schedules
  if ($failsafe_limit) {
    $get['next_sched_contribution_date'] = ['<' => $stale_date];
    $staleRecurringContributions = civicrm_api3('ContributionRecur', 'get',  $get);
    $failsafeFlag = ($failsafe_limit < $staleRecurringContributions['count']);
    $output[] = "Failsafe Analysis: stale date $stale_date, limit $failsafe_limit, count ".$staleRecurringContributions['count'];
    $output[] = "Failsafe Decision: ". ($failsafeFlag ? 'Flagged': 'Clear');
  }
  $settings = CRM_Iats_Utils::getSettings();
  $receipt_recurring = $settings['receipt_recurring'] ?? null;
  $email_failure_report = empty($settings['email_recurring_failure_report']) ? '' : $settings['email_recurring_failure_report'];
  $email_failure_contribution_receipt = empty($settings['email_failure_contribution_receipt']) ? FALSE : TRUE;
  list($emailFromName, $emailFromEmail) = CRM_Core_BAO_Domain::getNameAndEmail();
  // By default, after 3 failures move the next scheduled contribution date forward.
  $failure_threshhold = empty($settings['recurring_failure_threshhold']) ? 3 : (int) $settings['recurring_failure_threshhold'];
  $failure_report_text = '';
  if ($failsafeFlag) {
    $error_count = $staleRecurringContributions['count'];
    $failure_report_text = 'Alert: failsafe behaviour triggered due to too many stale schedules';
    $output[] = $failure_report_text;
  }
  else {
    CRM_Core_Error::debug_var('recurring Contributions', $recurringContributions['values']);
    foreach($recurringContributions['values'] as $recurringContribution) {
      // Strategy: create the contribution record with status = 2 (= pending), try the payment, and update the status to 1 if successful
      //           also, advance the next scheduled payment before the payment attempt and pull it back if we know it fails.
      $contribution_recur_id    = $recurringContribution['id'];
      $contact_id = $recurringContribution['contact_id'];
      // But first, check if the next scheduled contribution date is too far in the past, in which case I'll just notify an administrator and skip it.
      if ($recurringContribution['next_sched_contribution_date'] < $stale_date) {
        $failure_text = ts(
          'Stale recurring contribution schedule for contact id %1, recurring schedule id %2, %3',
          array(
            1 => $contact_id,
            2 => $contribution_recur_id,
            3 => $recurringContribution['next_sched_contribution_date']
          )
        );
        $output[] = $failure_text;
        $failure_report_text .= "\n".$failure_text;
        ++$error_count;
        continue;
      }
      $total_amount = $recurringContribution['amount'];
      $is_test = $recurringContribution['is_test'];
      $payment_processor_id = $recurringContribution['payment_processor_id'];
      // Try to get a contribution template for this contribution series - if none matches (e.g. if a donation amount has been changed), we'll just be naive about it.
      $contribution_template = CRM_Iats_Transaction::getContributionTemplate(['contribution_recur_id' => $contribution_recur_id, 'total_amount' => $total_amount, 'is_test' => $is_test]);
      // CRM_Core_Error::debug_var('Contribution Template', $contribution_template);
      // generate my invoice id like CiviCRM does
      $hash = md5(uniqid(rand(), TRUE));
      $paymentProcessor = $paymentProcessors[$payment_processor_id];
      $paymentClass = substr($paymentProcessor['class_name'],8);
      $source = E::ts('iATS Payments (%1) Recurring Contribution ( id = %2 )', [
        1 => $paymentClass,
        2 => $contribution_recur_id,
      ]);
      $receive_ts = $catchup ? strtotime($recurringContribution['next_sched_contribution_date']) : time();
      // i.e. now or whenever it was supposed to run if in catchup mode.
      $receive_date = date("YmdHis", $receive_ts);
      // Check if we already have an error.
      $errors = array();
      if (!empty($recurringContribution['payment_token_id'])) {
        try {
          $payment_token = civicrm_api3('PaymentToken', 'getsingle', array('id' => $recurringContribution['payment_token_id']));
          if (empty($payment_token['token'])) {
            $errors[] = E::ts('Recur id %1 is missing a payment token.', array(1 => $contribution_recur_id));
          }
        }
        catch (Exception $e) {
          $errors[] = E::ts('Unexpected error getting a payment token for recurring schedule id %1', array(1 => $contribution_recur_id));
          CRM_Core_Error::debug_var('Unexpected error getting payment token', $e);
          $payment_token = array();
        }
      }
      else {
        $errors[] = E::ts('Unexpected error, no payment token for recurring schedule id %1', array(1 => $contribution_recur_id));
      }
      if (count($errors)) {
        $source .= ' Errors: ' . implode(' ', $errors);
      }
      $contribution = array(
        'version'        => 3,
        'contact_id'       => $contact_id,
        'receive_date'       => $receive_date,
        'total_amount'       => $total_amount,
        'payment_instrument_id'  => $recurringContribution['payment_instrument_id'],
        'contribution_recur_id'  => $contribution_recur_id,
        'invoice_id'       => $hash,
        'source'         => $source,
        'contribution_status_id' => 'Pending', /* initialize as pending, so we can run completetransaction after taking the money */
        'currency'  => $recurringContribution['currency'],
        'payment_processor'   => $payment_processor_id,
        'is_test'        => $is_test, /* propagate the is_test value from the recurring record */
        'financial_type_id' => $recurringContribution['financial_type_id'],
        'is_email_receipt' => (($receipt_recurring < 2) ? $receipt_recurring : $recurringContribution['is_email_receipt']),
      );
      $get_from_template = ['contribution_campaign_id', 'amount_level', 'original_contribution_id'];
      foreach ($get_from_template as $field) {
        if (isset($contribution_template[$field])) {
          $contribution[$field] = is_array($contribution_template[$field]) ? implode(', ', $contribution_template[$field]) : $contribution_template[$field];
        }
      }
      // if we have a created a pending contribution record due to a future start time, then recycle that CiviCRM contribution record now.
      // Note that the date and amount both could have changed.
      // The key is to only match if we find a single pending contribution, with a NULL transaction id, for this recurring schedule.
      // We'll need to pay attention later that we may or may not already have a contribution id.
      try {
        $pending_contribution = civicrm_api3('Contribution', 'getsingle', array(
          'return' => array('id'),
          'trxn_id' => array('IS NULL' => 1),
          'contribution_recur_id' => $contribution_recur_id,
          'contribution_status_id' => "Pending",
        ));
        if (!empty($pending_contribution['id'])) {
          $contribution['id'] = $pending_contribution['id'];
        }
      }
      catch (Exception $e) {
        // ignore, we'll proceed normally without a contribution id
      }
      // If I'm not recycling a contribution record and my original has line_items, then I'll add them to the contribution creation array.
      // Note: if the amount of a matched pending contribution has changed, then we need to remove the line items from the contribution.
      if (empty($contribution['id']) && !empty($contribution_template['line_items'])) {
        $contribution['skipLineItem'] = 1;
        $contribution['api.line_item.create'] = $contribution_template['line_items'];
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
        if ($email_failure_report) {
          $failure_report_text .= "\n Unexpected Errors: " . implode(' ', $errors);
        }
        continue;
      }
      // Else: no errors in the setup, continue.
      // If the most recent contribution in the sequence is a membership payment, make this one also.
      // Note that we don't use the template_contribution for this purpose, so that
      // we can support changing membership amounts.
      if ($domemberships) {
        // retrieve the most recent previous contribution to check for a membership payment
        $latest_contribution = CRM_Iats_Transaction::getContributionTemplate(['contribution_recur_id' => $contribution_recur_id, 'is_test' => $is_test]);
        try {
          $membership_payment = civicrm_api('MembershipPayment', 'getsingle', array('version' => 3, 'contribution_id' => $latest_contribution['contribution_id']));
          if (!empty($membership_payment['membership_id'])) {
            // a slightly hacky was of passing this information in, membership_id
            // isn't normally a property of a contribution.
            $contribution['membership_id'] = $membership_payment['membership_id'];
          }
        }
        catch (Exception $e) {
          // ignore, if will fail correctly if there is no membership payment.
        }
      }
      // So far so, good ... now use my utility function process_contribution_payment to
      // create the pending contribution and try to get the money, and then do one of:
      // update the contribution to failed, leave as pending for server failure, complete the transaction,
      // or update a pending ach/eft with it's transaction id.
      // Assemble an array of recurring information so that process_contribution_payment can update the recurring record.
      // But first: calculate next collection date now so that in case of server failure on return from a payment request I don't try to take money again.
      // The next collection date is based on receive_ts, "recieve timestamp" (note effect of catchup mode, above)
      $next_collection_date = date('Y-m-d H:i:s', strtotime('+'.$recurringContribution['frequency_interval'].' '.$recurringContribution['frequency_unit'], $receive_ts));
      // Note: keep track of the currently defined "next_sched_contribution_date" as "current_sched_contribution_date" in case of confirmed transient card failures.
      $contribution_recur_update = array('id' => $contribution['contribution_recur_id'], 'next_sched_contribution_date' => $next_collection_date, 'failure_count' => $recurringContribution['failure_count'], 'failure_threshold' => $failure_threshhold, 'current_sched_contribution_date' => $recurringContribution['next_sched_contribution_date']);
      // process the payment and update the contribution and recurring contribution records:
      $result = CRM_Iats_Transaction::process_contribution_payment($contribution, $paymentProcessor, $payment_token, $contribution_recur_update);
      // in case of failure:
      // 1. append result message to report if I'm going to mail out a failures
      // 2. send a failure receipt/confirmation if that's configured
      if (!$result['result']['success']) {
        if ($email_failure_report) {
          $failure_report_text .= "\n".$result['message'];
	}
        // Send receipt with error message if a contribution fails and is set $email_failure_contribution_receipt
        if ($email_failure_contribution_receipt) {
          civicrm_api3('Contribution', 'sendconfirmation', [
            'id' => $contribution['id'],
            'receipt_from_name' => empty($emailFromName) ? ts('Admin') : $emailFromName,
            'receipt_from_email' => $emailFromEmail,
            'receipt_text' => ts('It seems something is not quite right with your recurring contribution payment. Please see details below.') . '<hr><br>' . $result['message'],
            'bcc_receipt' => !empty($email_failure_report)? $email_failure_report: $fromEmail,
          ]);
        }
      }
      $output[] = $result['message'];
      $result = civicrm_api('activity', 'create',
        array(
          'version'       => 3,
          'activity_type_id'  => 6,
          'source_contact_id'   => $contact_id,
          'source_record_id' => $contribution['id'],
          'assignee_contact_id' => $contact_id,
          'subject'       => ts('Attempted iATS Payments (%1) Recurring Contribution for %2', array(1 => $paymentClass, 2 => $total_amount)),
          'status_id'       => 2,
          'activity_date_time'  => date("YmdHis"),
        )
      );
      if ($result['is_error']) {
        $output[] = ts(
          'An error occurred while creating activity record for contact id %1: %2',
          array(
            1 => $contact_id,
            2 => $result['error_message'],
          )
        );
        ++$error_count;
      }
      else {
        $output[] = ts('Created activity record for contact id %1', array(1 => $contact_id));
      }
      ++$counter;
    }
  } // end else failsafeFlag is false

  // Now update the end_dates and status for non-open-ended contribution series if they are complete (so that the recurring contribution status will show correctly)
  // This is a simplified version of what we did before the processing.
  /*
  $select = 'SELECT cr.id, count(c.id) AS installments_done, cr.installments
      FROM civicrm_contribution_recur cr
      INNER JOIN civicrm_contribution c ON cr.id = c.contribution_recur_id
      INNER JOIN civicrm_payment_processor pp ON cr.payment_processor_id = pp.id
      WHERE
        (pp.class_name = %1 OR pp.class_name = %2 OR pp.class_name = %3)
        AND (cr.installments > 0)
        AND (cr.contribution_status_id  = 5)
        AND (c.contribution_status_id IN (1,2))
      GROUP BY c.contribution_recur_id';
  $dao = CRM_Core_DAO::executeQuery($select, $args);
  while ($dao->fetch()) {
    // Check if my end date should be set to now because I have finished
    // I'm done with installments.
    if ($dao->installments_done >= $dao->installments) {
      // Set this series complete and the end_date to now.
      $update = 'UPDATE civicrm_contribution_recur SET contribution_status_id = 1, end_date = NOW() WHERE id = %1';
      CRM_Core_DAO::executeQuery($update, array(1 => array($dao->id, 'Int')));
    }
  }
  */
  $lock->release();
  // If errors, mail a report if so configured
  if ((strlen($failure_report_text) > 0) && $email_failure_report) {
    $mailparams = array(
      'from' => $emailFromName . ' <' . $emailFromEmail . '> ',
      'toName' => empty($emailFromName) ? ts('System Administrator') : $emailFromName,
      'toEmail' => $email_failure_report,
      'bcc' =>  !empty($bcc_email_failure_report) ?  $bcc_email_failure_report : '',
      'subject' => ts('iATS Recurring Payment job failure report: ' . date('c')),
      'text' => $failure_report_text,
    );
    // print_r($mailparams);
    CRM_Utils_Mail::send($mailparams);
  }

  // If errors, return with an error.
  if ($error_count > 0) {
    return civicrm_api3_create_error(
      ts("Completed, but with %1 errors. %2 records processed.",
        array(
          1 => $error_count,
          2 => $counter,
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
          1 => $counter,
        )
      ) . "<br />" . implode("<br />", $output)
    );
  }
  // No records processed.
  return civicrm_api3_create_success(ts('No contribution records were processed.'));
}
