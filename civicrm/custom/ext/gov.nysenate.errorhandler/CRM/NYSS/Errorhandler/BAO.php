<?php

class CRM_NYSS_Errorhandler_BAO {
  static function generateReport($vars, $msg = '') {
    $bbcfg = get_bluebird_instance_config();
    $output = "There was a CiviCRM error at {$bbcfg['servername']}. \n";
    $date = date('c');
    $output .= "Date: {$date}\n\n";

    // Error details
    $output .= "\n\n*** ERROR ***\n";
    $output .= self::parseArray($vars);

    // The "last error" can sometimes help, but it can also mislead
    // (ex: PHP notice during the error).
    if (function_exists('error_get_last')) {
      $output .= "*** LAST ERROR ***\n";
      $output .= print_r(error_get_last(), TRUE);
    }

    // User information and the session variable
    $output .= self::getSessionInfo();

    // Backtrace
    $output .= "\n\n*** BACKTRACE ***\n";
    $backtrace = debug_backtrace();
    $output .= CRM_Core_Error::formatBacktrace($backtrace, TRUE, 120);

    // $_POST
    $output .= "\n\n*** POST ***\n";
    $output .= self::parseArray($_POST);

    /*$output .= "\n\n*** FULL BACKTRACE ***\n";
    foreach ($backtrace as $call) {
      $output .= "** next call **\n";
      $output .= self::parseArray($call);
    }*/

    //determine how we will notify regarding the error
    $endpoints = Civi::settings()->get('errorhandler_endpoints');
    $endpoints = array_map('trim', explode(',', $endpoints));
    //Civi::log()->debug(__FUNCTION__, ['endpoints' => $endpoints]);

    $subject = "Bluebird Error [{$bbcfg['shortname']}.{$bbcfg['envname']}]";
    $subject .= ' (' . substr($vars['message'], 0, 100) . ')';

    if (in_array('email', $endpoints)) {
      self::notifyEmail($output, $subject);
    }

    if (in_array('slack', $endpoints)) {
      self::notifySlack($output, $subject);
    }

    //redirect to homepage (with message?)
    CRM_Utils_System::redirect(CRM_Utils_System::baseCMSURL());
  }

  static function handle($vars) {
    //Civi::log()->debug(__FUNCTION__, ['_REQUEST' => $_REQUEST, 'vars' => $vars]);

    $config = CRM_Core_Config::singleton();
    $urlVar = $config->userFrameworkURLVar;
    $arg = explode('/', $_GET[$urlVar]);
    $msg = '';
    $redirectUrl = CRM_Utils_System::baseCMSURL();

    //profile error
    if ($arg[0] == 'civicrm' && $arg[1] == 'profile') {
      $msg = 'There was a problem accessing the page. Please try again.';
      self::generateReport($vars, $msg);
    }
    elseif (isset($vars['exception']) && isset($vars['pearError'])) {
      //smart group error
      if ($msg = self::handleSmartGroups($vars)) {
        self::generateReport($vars, $msg);
      }
    }
    elseif (strpos($vars['message'], 'This page requires cookies to be enabled in your browser settings.') !== FALSE) {
      $msg = 'There was a session error accessing this page. This is likely because you left the page open and inactive and the security token expired. Please revisit the page you were using and try again.';
      self::generateReport($vars, $msg);
    }
    //NYSS 14116
    elseif (strpos($vars['message'], 'There is a validation error with your HTML input.') !== FALSE) {
      $redirectUrl = CRM_Utils_Request::retrieve('entryURL', 'String').'?qfKey='.CRM_Utils_Request::retrieve('qfKey', 'String');
      $msg = 'The content you entered into this form contains potentially harmful code. Please try pasting as plain text using [Ctrl+Shift+V].';
    }

    if (!empty($msg && !empty($redirectUrl))) {
      CRM_Core_Error::statusBounce($msg, $redirectUrl);

      return TRUE;
    }

    //let standard CiviCRM behavior proceed; also log
    self::log(__FUNCTION__, $vars);

    return FALSE;
  }

  static function handleSmartGroups($vars) {
    $sql = NULL;

    if (method_exists($vars['exception'], 'getExtraParams')) {
      $extra_params = $vars['exception']->getExtraParams();

      if (isset($extra_params['sql'])) {
        $sql = $extra_params['sql'];
      }

      $error_message = $vars['exception']->getMessage();
    }

    if (empty($sql)) {
      if (!empty($vars['pearError']->userinfo)) {
        $sql = $vars['pearError']->userinfo;
      }

      $error_message = $vars['pearError']->message;
    }

    if (preg_match('/^CREATE TEMPORARY TABLE civicrm_temp_group_contact_cache\d+ \(SELECT (\d+) as group_id/', $sql, $matches)) {
      $broken_group_id = $matches[1];

      $output = [
        'data' => [],
      ];

      $result = civicrm_api3('Group', 'getsingle', [
        'group_id' => $broken_group_id,
      ]);

      $description = ($result['description'] ? $result['description'] . ' -- ' : '') . 'Disabled automatically by error handler: ' . $error_message;

      try {
        civicrm_api3('Group', 'create', [
          'group_id' => $broken_group_id,
          'description' => $description,
          'is_active' => 0,
        ]);
      }
      catch (CiviCRM_API3_Exception $e) {}

      if (CRM_Utils_Array::value('update_smart_groups', $_REQUEST) == 1) {
        CRM_Core_Session::setStatus(E::ts('ERROR: Group ID %1 could not be loaded and has been disabled.',
          [1 => $broken_group_id]), '', 'error');
        CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/group', 'reset=1'));

        return TRUE;
      }
      else {
        // Assumes this is while the main database was being loaded on /civicrm/group?reset=1
        $output['data'][] = [
          'id' => 99999,
          'count' => 1,
          'title' => E::ts('ERROR: Group ID %1 could not be loaded and has been disabled. This may be the result of a deleted custom field or a bug in a custom search.', [1 => $broken_group_id]),
          'description' => '',
          'group_type' => '',
          'visibility' => '',
          'links' => '',
          'created_by' => '',
          'DT_RowId' => 'row_99999',
          'DT_RowClass' => 'crm-group-parent',
          'DT_RowAttr' => [
            'data-id' => 99999,
            'data-entity' => 'group',
          ],
        ];

        echo json_encode($output);
      }

      return "There was a problem loading smart group ID {$broken_group_id}.";
    }

    return FALSE;
  }

  /**
   * @param $message
   * @param null $title
   * @param array $fields
   * @param null $channel
   * @param string $attachment_color
   *
   * Adapted from:
   * https://github.com/nysenate/NYSenate.gov-Website-2015/blob/2.x/sites/all/modules/custom/nys_utils/nys_utils.module#L1973
   */
  static function notifySlack($message, $title = NULL, $fields = [], $channel = NULL, $attachment_color = 'danger') {
    $slack_url = Civi::settings()->get('errorhandler_slack_url');

    // Proceed only if the slack URL is set.
    if (!empty($slack_url)) {
      // Get default channel if none is passed in.
      if ($channel == NULL) {
        $channel = Civi::settings()->get('errorhandler_slack_channel');
      }

      // Get default title if none is passed in.
      if (empty($title)) {
        $title = Civi::settings()->get('errorhandler_slack_title');
      }

      //append site url
      $bbcfg = get_bluebird_instance_config();
      $title .= ' (' . $bbcfg['servername'] . ')';

      // Set a default for attachment color, which can be any of the following:
      // - good (green)
      // - warning (yellow)
      // - danger (red)
      $slack_attachment_colors = ['good', 'warning', 'danger'];
      if (!in_array($attachment_color, $slack_attachment_colors)) {
        $attachment_color = 'danger';
      }

      $payload = [
        'text' => $title,
        'channel' => $channel,
        'attachments' => [[
          'color' => $attachment_color,
          'text' => $message,
          'fallback' => $message,
          'pretext' => '',
        ],
        ],
      ];

      // Merge in fields if the $fields array is not empty.
      if (is_array($fields) && count($fields)) {
        $payload['attachments'][0]['fields'] = $fields;
      }
      //Civi::log()->debug(__FUNCTION__, ['payload' => $payload]);

      // Convert $payload array to recommended json string.
      $data_string = ['payload' => json_encode($payload)];

      $ch = curl_init($slack_url);
      curl_setopt($ch, CURLOPT_POST, TRUE);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
      $response = curl_exec($ch);
      //Civi::log()->debug(__FUNCTION__, ['$response' => $response]);

      curl_close($ch);

      if ($response != 'ok') {
        self::log(__FUNCTION__.' Could not send mail', $response);
        watchdog('bluebird', 'Slack call failed with response: %response', ['%response'=>$response], WATCHDOG_ERROR);
      }
    }
    else {
      self::log(__FUNCTION__, 'Errorhandler: Slack URL is not set!');
      watchdog('bluebird', 'Slack URL is not set!', [], WATCHDOG_WARNING);
    }
  }

  static function notifyEmail($message, $subject) {
    $emails = Civi::settings()->get('errorhandler_emailrecipients');
    $from = Civi::settings()->get('errorhandler_fromemail');

    if (empty($emails)) {
      return FALSE;
    }

    $emails = array_map('trim', explode(',', $emails));

    foreach ($emails as $email) {
      $params = [
        'from' => $from,
        'toName' => 'Site Administrator',
        'toEmail' => $email,
        'subject' => $subject,
        'text' => $message,
      ];

      $result = CRM_Utils_Mail::send($params);

      if (!$result) {
        self::log(__FUNCTION__, 'Errorhandler: Could not send mail');
        watchdog('bluebird', 'Errorhandler: Could not send mail', [], WATCHDOG_WARNING);

        return FALSE;
      }
    }

    return TRUE;
  }

  static function log($label, $var) {
    CRM_Core_Error::debug_var($label, $var, TRUE, TRUE, 'errorhandler');
  }

  static function getSessionInfo() {
    $output = '';

    // User info
    $session = CRM_Core_Session::singleton();
    $userId = $session->get('userID');

    if ($userId) {
      $output .= "\n\n*** LOGGED IN USER ***\n";

      try {
        $contact = civicrm_api3('Contact', 'getsingle', [
          'id' => $userId,
          'return' => 'id,display_name,email',
        ]);
        $output .= self::parseArray($contact);
      }
      catch (Exception $e) {
        $output .= "Failed to fetch user info using the API:\n";
      }
    }
    else {
      // Show the remote IP and user-agent of anon users, to facilitate
      // identification of bots and other source of false positives.
      $output .= "\n\n*** ANONYMOUS USER ***\n";
    }

    $output .= "REMOTE_ADDR: " . $_SERVER['REMOTE_ADDR'] . "\n";
    $output .= "HTTP_USER_AGENT: " . $_SERVER['HTTP_USER_AGENT'] . "\n";

    // $_SERVER
    $output .= "\n\n*** SERVER ***\n";
    $output .= self::parseArray($_SERVER);

    return $output;
  }

  static function parseArray($array) {
    $output = '';

    $array = (array) $array;

    foreach ($array as $key => $value) {
      if (is_array($value) || is_object($value)) {
        $value = print_r($value, TRUE);
      }

      $key = str_pad($key . ':', 20, ' ');
      $output .= $key . self::checkLength($value) . "\n";
    }

    // Remove sensitive data.
    // We do this hackishly this way, because:
    // - doing a search/replace in the $array can cause changes in the $_SESSION, for example, because of references.
    // - re-writing print_r() seemed a bit ambitious, and likely to introduce bugs.
    $output = preg_replace('/\[credit_card_number\] => (\d{4})\d+/', '[credit_card_number] => \1[removed]', $output);
    $output = preg_replace('/\[cvv2\] => \d+/', '[cvv2] => [removed]', $output);
    $output = preg_replace('/\[password\] => .*$/', '[password] => [removed]', $output);

    // This is for the POST data
    $output = preg_replace('/credit_card_number:\s+(\d{4})\d+/', 'credit_card_number: \1[removed]', $output);
    $output = preg_replace('/cvv2:\s+\d+/', 'cvv2: [removed]', $output);
    $output = preg_replace('/password: .*$/', 'password: [removed]', $output);

    return $output . "\n";
  }

  /**
   *  Helper function to add ellipses and return spaces if null
   *
   *  @param string $item
   *    String to check.
   *  @return string
   *    The truncated string.
   */
  static function checkLength($item) {
    if (is_null($item)) {
      return ' ';
    }

    if (strlen($item) > 2000) {
      $item = substr($item, 0, 2000) .'...';
    }

    return (string) $item;
  }
}