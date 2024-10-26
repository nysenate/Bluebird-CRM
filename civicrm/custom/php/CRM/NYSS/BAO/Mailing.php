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
        AND mr.email_id IS NOT NULL
        AND mr.email_id != ''
        AND mr.contact_id IS NOT NULL
        AND mr.contact_id != ''
    ";

    $rows = [];
    $dao = CRM_Core_DAO::executeQuery($sql, [1 => [$mailingId, 'Positive']]);

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
      //CRM_Core_Error::debug_var('job', $job, TRUE, TRUE, 'veq');

      while ($dao->fetch()) {
        $rows[] = [
          $job['id'],
          $dao->email_id,
          $dao->contact_id,
          'null',
        ];
      }
      //CRM_Core_Error::debug_var('rows', $rows, TRUE, TRUE, 'veq');

      if (!empty($rows)) {
        CRM_Mailing_Event_BAO_Queue::bulkCreate($rows, $now);

        //notify error report recipients
        $bbcfg = get_bluebird_instance_config();
        CRM_NYSS_Errorhandler_BAO::notifyEmail(print_r($rows, TRUE), "Mailing Queue Cleanup Required [{$bbcfg['shortname']}.{$bbcfg['envname']}]");
      }

      return $job['id'];
    }
    catch (CiviCRM_API3_Exception $e) {}

    return NULL;
  }

  /**
   * @param $mailingId
   * @param $role
   * @return void
   *
   * $roles = [created, approver, scheduled]
   */
  static function notify($mailingId, $roles = ['created', 'approver']) {
    $select = ['name'];
    foreach ($roles as $role) {
      $select[] = "{$role}_id";
      $select[] = "{$role}_id.display_name";
    }

    //using this format so we can pass select fields as array
    $mailings = civicrm_api4('Mailing', 'get', [
      'select' => $select,
      'where' => [
        ['id', '=', $mailingId],
      ],
      'checkPermissions' => FALSE,
    ]);

    $fromEmailAddress = CRM_Core_OptionGroup::values('from_email_address', NULL, NULL, NULL, ' AND is_default = 1');

    /*Civi::log()->debug(__METHOD__, [
      'select' => $select,
      'mailings' => $mailings,
      'fromEmailAddress' => $fromEmailAddress,
    ]);*/

    foreach ($mailings as $mailing) {
      foreach ($roles as $role) {
        $email = \Civi\Api4\Email::get(FALSE)
          ->addSelect('email')
          ->addWhere('on_hold', 'IS EMPTY')
          ->addWhere('contact_id', '=', $mailing["{$role}_id"])
          ->addOrderBy('is_primary', 'DESC')
          ->execute()
          ->first();

        $params = [
          'toEmail' => $email['email'],
          'toName' => $mailing["{$role}_id.display_name"],
          'subject' => "Bluebird mailing has been paused: {$mailing['name']} (ID: {$mailingId})",
          'html' => "
          <p>A mailing you helped prepare has been paused. This generally indicates a temporary connection issue with the email delivery service. Please review the mailing from within Bluebird and select resume if you wish to continue delivery.</p>
          <p>Mailing Name: {$mailing['name']}</p>
        ",
          'from' => reset($fromEmailAddress),
        ];

        //Civi::log()->debug(__METHOD__, ['$params' => $params]);

        CRM_Utils_Mail::send($params);
      }
    }
  }
}
