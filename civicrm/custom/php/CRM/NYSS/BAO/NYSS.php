<?php

class CRM_NYSS_BAO_NYSS {

  static function getContactList() {
    // if context is 'customfield'
    if (CRM_Utils_Array::value('context', $_GET) == 'customfield') {
      return self::contactReference();
    }
    $params = array(
      'version' => 3,
      'check_permissions' => TRUE,
      'search_field' => 'sort_name',
    );
    // String params
    // FIXME: param keys don't match input keys, using this array to translate
    $whitelist = array(
      's' => 'name',
      'term' => 'sort_name',
      'fieldName' => 'field_name',
      'tableName' => 'table_name',
      'context' => 'context',
      'rel' => 'rel',
      'contact_sub_type' => 'contact_sub_type'
    );
    foreach ($whitelist as $key => $param) {
      if (!empty($_GET[$key])) {
        $params[$param] = $_GET[$key];
      }
    }
    //CRM-10687: Allow quicksearch by multiple fields
    if (!empty($params['field_name'])) {
      if ($params['field_name'] == 'phone_numeric') {
        $params['name'] = preg_replace('/[^\d]/', '', $params['name']);
      }
      if (!$params['name']) {
        CRM_Utils_System::civiExit();
      }
    }
    // Numeric params
    $whitelist = array(
      'limit',
      'org',
      'employee_id',
      'cid',
      'id',
      'cmsuser',
    );
    foreach ($whitelist as $key) {
      if (!empty($_GET[$key]) && is_numeric($_GET[$key])) {
        $params[$key] = $_GET[$key];
      }
    }
    //CRM_Core_Error::debug_var('$_GET', $_GET);
    //CRM_Core_Error::debug_var('$params', $params);
    $result = civicrm_api('Contact', 'getlist', $params);
    //CRM_Core_Error::debug_var('result', $result);
    if (empty($result['is_error']) && !empty($result['values'])) {
      foreach ($result['values'] as $key => $val) {
        echo "{$val['data']}|{$val['id']}\n";
      }
    }
    CRM_Utils_System::civiExit();
  }

  /**
   * @param $mailingId
   * @param $jobId
   *
   * @return array
   *
   * This is a simplified stripped down version of CRM_Mailing_Event_BAO_Delivered::getRows
   * for our purposes. That method is modified to support our handling of delivered
   * values via a Sendgrid table. But in doing so, we lose the ability to track
   * and "attempted" delivery in real time. This function queries the standard tables
   * in order to verify test mailings immediately.
   */
  static function verifyTestEmail($mailingId, $jobId) {
    $sql = "
      SELECT med.id, c.id contact_id, c.display_name, e.email, med.time_stamp date
      FROM civicrm_mailing_job mj
      JOIN civicrm_mailing_event_queue meq
        ON mj.id = meq.job_id
      JOIN civicrm_mailing_event_delivered med
        ON meq.id = med.event_queue_id
      JOIN civicrm_contact c
        ON meq.contact_id = c.id
      JOIN civicrm_email e
        ON meq.email_id = e.id
      LEFT JOIN civicrm_mailing_event_bounce meb
        ON meq.id = meb.event_queue_id
      WHERE mj.mailing_id = %1
        AND mj.id = %2
        AND meb.id IS NULL
      LIMIT 1
    ";
    $dao = CRM_Core_DAO::executeQuery($sql, [
      1 => [$mailingId, 'Positive'],
      2 => [$jobId, 'Positive'],
    ]);

    $mailDelivered = [];

    while ($dao->fetch()) {
      $url = CRM_Utils_System::url('civicrm/contact/view',
        "reset=1&cid={$dao->contact_id}"
      );
      $mailDelivered[$dao->id] = [
        'contact_id' => $dao->contact_id,
        'name' => "<a href=\"$url\">{$dao->display_name}</a>",
        'email' => $dao->email,
        'date' => CRM_Utils_Date::customFormat($dao->date),
      ];
    }

    return $mailDelivered;
  }

  static function getProtectedProperty($prop, $obj) {
    $reflection = new ReflectionClass($obj);
    $property = $reflection->getProperty($prop);
    $property->setAccessible(true);

    return $property->getValue($obj);
  }

  static function checkUserRole($role) {
    global $user;

    if (in_array($role, $user->roles)) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * @return bool
   *
   * simple helper to determine if the logged in user is considered an admin user
   */
  static function isAdmin() {
    global $user;

    if ($user->uid == 1) {
      return TRUE;
    }

    if (is_array($user->roles) && in_array('Administrator', $user->roles)) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * @return bool
   *
   * determine if the current page is a public page
   */
  static function isPublicUrl() {
    global $theme;

    return ($theme == 'BluebirdPublic');
  }

  /**
   * @param $msg
   *
   * Simple mailing function that piggy-backs on the error reporting extension
   */
  static function notifyErrorReportRecipient($subject, $output) {
    $to = Civi::settings()->get('reporterror_mailto');

    if (!empty($to)) {
      $destinations = explode(REPORTERROR_EMAIL_SEPARATOR, $to);

      $bbconfig = get_bluebird_instance_config();
      $subject = "Bluebird Error [{$bbconfig['shortname']}.{$bbconfig['envname']}] ".$subject;

      foreach ($destinations as $dest) {
        $dest = trim($dest);
        reporterror_civicrm_send_mail($dest, $subject, $output, []);
      }
    }
  }

  /**
   * @param $params
   * @return array
   *
   * Callback for APIWrapper modification to support quicksearch by Case ID
   * 14379
   */
  static function getQuickSearchCaseId($params) {
    //Civi::log()->debug(__FUNCTION__, ['params' => $params]);
    $result = [];

    try {
      $cases = civicrm_api3('Case', 'get', [
        'sequential' => 1,
        'id' => $params['name'],
        'is_deleted' => 0,
        'options' => ['limit' => 0],
      ]);
      //Civi::log()->debug(__FUNCTION__, ['$cases' => $cases]);

      foreach ($cases['values'] as $idx => $case) {
        $contact = civicrm_api3('Contact', 'getsingle', ['id' => $case['client_id'][1]]);
        $result['values'][$idx]['id'] = $case['client_id'][1];
        $result['values'][$idx]['data'] = "{$contact['sort_name']} [Case #{$case['id']} :: {$case['subject']}]";
      }
    }
    catch (CiviCRM_API3_Exception $e) {}

    //Civi::log()->debug(__FUNCTION__, ['$result' => $result]);
    return $result;
  }

  //NYSS 5848
  static function removeBuilderFields($key, &$value) {
    //CRM_Core_Error::debug_var('key', $key);
    //CRM_Core_Error::debug_var('value', $value);

    $flds = [
      'Activity' => [
        'activity_campaign_id',
        'activity_is_test',
        'activity_engagement_level',
        'activity_campaign',
        'is_current_revision',
      ],
    ];

    if (array_key_exists($key, $flds)) {
      foreach ($flds[$key] as $fld) {
        unset($value[$fld]);
      }
    }
  }
}
