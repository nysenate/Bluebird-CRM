<?php

class CRM_NYSS_BAO_Mailing {
  static function validateEventQueue($mailingId) {
    $now = time();

    $sql = "
      SELECT mr.*
      FROM civicrm_mailing_recipients mr
      LEFT JOIN (
        SELECT meq.*
        FROM civicrm_mailing_event_queue meq
        JOIN civicrm_mailing_job mj
          ON meq.job_id = mj.id
          AND mj.mailing_id = %1
          AND mj.is_test = 0
          AND mj.job_type = 'child'
      ) queue
        ON mr.email_id = queue.email_id
      WHERE mr.mailing_id = %1
        AND queue.id IS NULL
    ";

    $rows = [];
    $dao = CRM_Core_DAO::executeQuery($sql, [1 => [$mailingId, 'Positive']]);
    //Civi::log()->debug(__FUNCTION__, ['$dao' => $dao]);

    //exit early if we have no recipients to queue
    if (empty($dao->N)) {
      return NULL;
    }

    //get details to create new child job
    $parentJobId = CRM_Core_DAO::singleValueQuery("
      SELECT id
      FROM civicrm_mailing_job
      WHERE mailing_id = %1
        AND is_test = 0
        AND job_type IS NULL
    ", [
      1 => [$mailingId, 'Positive']
    ]);

    $maxOffset = CRM_Core_DAO::singleValueQuery("
      SELECT MAX(job_offset)
      FROM civicrm_mailing_job
      WHERE mailing_id = %1
        AND is_test = 0
        AND job_type = 'child'
    ", [
      1 => [$mailingId, 'Positive']
    ]);

    //create new child job
    try {
      $job = civicrm_api3('MailingJob', 'create', [
        'mailing_id' => $mailingId,
        'is_test' => 0,
        'job_type' => 'child',
        'parent_id' => $parentJobId,
        'job_offset' => $maxOffset + 1000,
        'job_limit' => $dao->N,
      ]);
      //Civi::log()->debug(__FUNCTION__, ['$job' => $job]);

      while ($dao->fetch()) {
        $rows[] = [
          $job['id'],
          $dao->email_id,
          $dao->contact_id,
          empty($dao->phone_id) ? $dao->phone_id : 'null',
        ];
      }
      //Civi::log()->debug(__FUNCTION__, ['rows' => $rows]);

      if (!empty($rows)) {
        CRM_Mailing_Event_BAO_Queue::bulkCreate($rows, $now);

        //notify error report recipients
        CRM_NYSS_BAO_NYSS::notifyErrorReportRecipient('Mailing Queue Cleanup Required', print_r($rows, TRUE));
      }

      return $job['id'];
    }
    catch (CiviCRM_API3_Exception $e) {}

    return NULL;
  }
}
