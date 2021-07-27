<?php

require_once 'reporterror.civix.php';

define('REPORTERROR_CIVICRM_SUBJECT_LEN', 100);
define('REPORTERROR_EMAIL_SEPARATOR', ',');

require_once(__DIR__ . '/vendor/autoload.php');

use CRM_ReportError_ExtensionUtil as E;

/**
 * Implementation of hook_civicrm_config
 */
function reporterror_civicrm_config(&$config) {
  _reporterror_civix_civicrm_config($config);

  // override the error handler
  $config = CRM_Core_Config::singleton();
  $config->fatalErrorHandler = 'reporterror_civicrm_handler';
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function reporterror_civicrm_xmlMenu(&$files) {
  _reporterror_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function reporterror_civicrm_install() {
  _reporterror_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function reporterror_civicrm_postInstall() {
  _civirules_civix_civicrm_postInstall();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function reporterror_civicrm_uninstall() {
  // Send final email
  $subject = E::ts('CiviCRM Error Report was uninstalled');
  $output = $subject . _reporterror_civicrm_get_session_info();
  $to = Civi::settings()->get('reporterror_mailto');

  if (!empty($to)) {
    $destinations = explode(REPORTERROR_EMAIL_SEPARATOR, $to);
    foreach ($destinations as $dest) {
      $dest = trim($dest);
      reporterror_civicrm_send_mail($dest, $subject, $output);
    }
  }
  else {
    Civi::log()->warning('Report Error Extension could not send since no email address was set.');
  }

  // Delete our settings
  // FIXME: Maybe settings metadata helps? This is redundant.
  $settings = [ 'reporterror_show_full_backtrace', 'reporterror_show_post_data', 'reporterror_show_session_data', 'reporterror_noreferer_sendreport', 'reporterror_noreferer_sendreport_event', 'reporterror_bots_sendreport', 'reporterror_bots_404', 'reporterror_bots_regexp' ];

  foreach ($settings as $name) {
    CRM_Core_DAO::executeQuery('DELETE FROM civicrm_setting WHERE name = %1', [
      1 => [$name, 'String'],
    ]);
  }

  _reporterror_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function reporterror_civicrm_enable() {
  _reporterror_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function reporterror_civicrm_disable() {
  return _reporterror_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function reporterror_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _reporterror_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function reporterror_civicrm_managed(&$entities) {
  _reporterror_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_navigationMenu
 */
function reporterror_civicrm_navigationMenu(&$params) {
  _reporterror_civix_insert_navigation_menu($params, 'Administer/System Settings', [
    'label' => E::ts('Report Error Settings'),
    'name' => 'Report Error Settings',
    'url' => 'civicrm/admin/setting/reporterror',
    'permission' => 'administer CiviCRM',
    'operator' => 'OR',
    'separator' => 0,
  ]);
  _reporterror_civix_navigationMenu($params);
}

/**
 * Custom error handler.
 * This is registered as a callback in hook_civicrm_config().
 *
 * @param array $vars Array with the 'message' and 'code' of the error.
 * @param array $options_overrides
 */
function reporterror_civicrm_handler($vars, $options_overrides = []) {
  $handers = [
    'IgnoreBots',
    'FormsNoReferer',
    'SmartGroupRefresh',
    'Profiles',
  ];

  foreach ($handers as $h) {
    $success = call_user_func_array('CRM_ReportError_Handler_' . $h . '::handler', [$vars, $options_overrides]);

    if ($success) {
      return TRUE;
    }
  }

  // We let CiviCRM display the regular fatal error
  return FALSE;
}

/**
 * Returns a plain text output for the e-mail report.
 *
 * FIXME: the redirect_path should be included in 'vars'
 * This should be rewritten under CRM_ReportError_Utils,
 * with backwards-compat wrapper.
 *
 * @param string $site_name
 * @param array $vars
 * @param string $redirect_path
 * @param array $options_overrides
 *
 * @return string
 */
function reporterror_civicrm_generatereport($site_name, $vars, $redirect_path, $options_overrides = []) {
  $show_full_backtrace = reporterror_setting_get('reporterror_show_full_backtrace', $options_overrides);
  $show_post_data = reporterror_setting_get('reporterror_show_post_data', $options_overrides);
  $show_session_data = reporterror_setting_get('reporterror_show_session_data', $options_overrides);

  $output = E::ts('There was a CiviCRM error at %1.', [1 => $site_name]) . "\n";
  $output .= E::ts('Date: %1', [1 => date('c')]) . "\n\n";

  // Backwards compatibility
  if ($redirect_path && empty($vars['redirect_path'])) {
    $vars['redirect_path'] = $redirect_path;
  }

  if (!empty($vars['redirect_path'])) {
    $output .= E::ts("Error handling rules redirected the user to:") . "\n";
    $output .= $vars['redirect_path'] . "\n\n";
  }

  // Error details
  $output .= "\n\n***ERROR***\n";
  $output .= _reporterror_civicrm_parse_array($vars);

  // The "last error" can sometimes help, but it can also mislead
  // (ex: PHP notice during the error).
  if (function_exists('error_get_last')) {
    $output .= "***LAST ERROR***\n";
    $output .= print_r(error_get_last(), TRUE);
  }

  // User information and the session variable
  $output .= _reporterror_civicrm_get_session_info($show_session_data);

  // Backtrace
  $output .= "\n\n***BACKTRACE***\n";

  $backtrace = debug_backtrace();
  $output .= CRM_Core_Error::formatBacktrace($backtrace, TRUE, 120);

  // $_POST
  if ($show_post_data) {
    $output .= "\n\n***POST***\n";
    $output .= _reporterror_civicrm_parse_array($_POST);
  }

  if ($show_full_backtrace) {
    $output .= "\n\n***FULL BACKTRACE***\n";

    foreach ($backtrace as $call) {
      $output .= "**next call**\n";
      $output .= _reporterror_civicrm_parse_array($call);
    }
  }

  return $output;
}

/**
 * Send the e-mail using CRM_Utils_Mail::send()
 *
 * @param string $to
 * @param string $subject
 * @param string $output
 * @param array|null $options_overrides
 */
function reporterror_civicrm_send_mail($to, $subject, $output, $options_overrides = NULL) {
  $email = reporterror_setting_get('reporterror_fromemail', $options_overrides);

  //if email is not in the settings, use system default
  if (empty($email)) {
    $result = civicrm_api('OptionValue', 'get', [
      'option_group_name' => 'from_email_address',
      'is_default' => TRUE,
      'version' => 3,
    ]);

    if ($result['is_error']) {
      CRM_Core_Error::debug_log_message('Report Error Extension: failed to get the default from email address');
      return;
    }
    $val = array_pop($result['values']);
    $email = $val['label'];
  }

  if (!$email) {

    return;
  }

  $params = [
    'from' => $email,
    'toName' => 'Site Administrator',
    'toEmail' => $to,
    'subject' => $subject,
    'text' => $output,
  ];

  $mail_sent = CRM_Utils_Mail::send($params);

  if (! $mail_sent) {
    CRM_Core_Error::debug_log_message('Report Error Extension: Could not send mail');
  }
}

/**
 *  Helper function to return a pretty print of the given array
 *
 *  @param array $array
 *    The array to print out.
 *  @return string
 *    The printed array.
 */
function _reporterror_civicrm_parse_array($array) {
  $output = '';

  $array = (array) $array;

  foreach ($array as $key => $value) {
    if (is_array($value) || is_object($value)) {
      $value = print_r($value, TRUE);
    }

    $key = str_pad($key . ':', 20, ' ');
    $output .= $key . _reporterror_civicrm_check_length($value) . "\n";
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
 *  Helper function to add elipses and return spaces if null
 *
 *  @param string $item
 *    String to check.
 *  @return string
 *    The truncated string.
 */
function _reporterror_civicrm_check_length($item) {
  if (is_null($item)) {
    return ' ';
  }

  if (strlen($item) > 2000) {
    $item = substr($item, 0, 2000) .'...';
  }

  return (string) $item;
}

/**
 *  Helper function to get user session info for email body.
 *
 *  @return string
 *    Partial email body string with user session info.
 */
function _reporterror_civicrm_get_session_info($show_session_data = FALSE) {
  $output = '';

  // User info
  $session = CRM_Core_Session::singleton();
  $userId = $session->get('userID');

  if ($userId) {
    $output .= "\n\n***LOGGED IN USER***\n";

    try {
      $contact = civicrm_api3('Contact', 'getsingle', [
        'id' => $userId,
        'return' => 'id,display_name,email',
      ]);
      $output .= _reporterror_civicrm_parse_array($contact);
    }
    catch (Exception $e) {
      $output .= "Failed to fetch user info using the API:\n";
    }
  }
  else {
    // Show the remote IP and user-agent of anon users, to facilitate
    // identification of bots and other source of false positives.
    $output .= "\n\n***ANONYMOUS USER***\n";
  }

  $output .= "REMOTE_ADDR: " . $_SERVER['REMOTE_ADDR'] . "\n";
  $output .= "HTTP_USER_AGENT: " . $_SERVER['HTTP_USER_AGENT'] . "\n";

  if ($show_session_data) {
    $output .= "\n\n***SESSION***\n";
    $output .= _reporterror_civicrm_parse_array($_SESSION);
  }

  // $_SERVER
  $output .= "\n\n***SERVER***\n";
  $output .= _reporterror_civicrm_parse_array($_SERVER);
  return $output;
}

/**
 * Helper function to get a specific setting of the extension,
 * or lookup an override option.
 *
 * Option overrides is an array of settings that the calling function
 * can set to override the behavior of the report. For example, if a
 * payment processor caught an exception doing a curl/soap request, it
 * will probably want to disable the full backtrace and session info.
 */
function reporterror_setting_get($name, $options_overrides, $default = NULL) {
  if (isset($options_overrides[$name])) {
    return $options_overrides[$name];
  }
  return Civi::settings()->get($name);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 */
function reporterror_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _reporterror_civix_civicrm_alterSettingsFolders($metaDataFolders);
}
