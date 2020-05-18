<?php

require_once 'ode.civix.php';

/**
 * Implements hook_civicrm_config().
 */
function ode_civicrm_config(&$config) {
  _ode_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 */
function ode_civicrm_xmlMenu(&$files) {
  _ode_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 */
function ode_civicrm_install() {
  checkValidEmails();
  return _ode_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 */
function ode_civicrm_uninstall() {
  return _ode_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 */
function ode_civicrm_enable() {
  checkValidEmails();
  return _ode_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 */
function ode_civicrm_disable() {
  return _ode_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 */
function ode_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _ode_civix_civicrm_upgrade($op, $queue);
}

/**
*  Implements hook_civicrm_validateForm().
*/
function ode_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  switch ($formName) {
    case 'CRM_Contribute_Form_Contribution':
    case 'CRM_Event_Form_Participant':
    case 'CRM_Member_Form_Membership':
    case 'CRM_Pledge_Form_Pledge':

      $isReceiptField = array(
        'CRM_Contribute_Form_Contribution' => 'is_email_receipt',
        'CRM_Event_Form_Participant' => 'send_receipt',
        'CRM_Member_Form_Membership' => 'send_receipt',
        'CRM_Pledge_Form_Pledge' => 'is_acknowledge',
      );

      if (CRM_Utils_Array::value($isReceiptField[$formName], $fields) && !CRM_Utils_Array::value('from_email_address', $fields)) {
        $errors['from_email_address'] = ts('Receipt From is a required field.');
      }
      break;

    case 'CRM_Contribute_Form_ContributionPage_ThankYou':
    case 'CRM_Event_Form_ManageEvent_Registration':
    case 'CRM_Grant_Form_GrantPage_ThankYou':
      $isReceiptField = array(
        'CRM_Contribute_Form_ContributionPage_ThankYou' => array('is_email_receipt', 'receipt_from_email'),
        'CRM_Grant_Form_GrantPage_ThankYou' => array('is_email_receipt', 'receipt_from_email'),
        'CRM_Event_Form_ManageEvent_Registration' => array('is_email_confirm', 'confirm_from_email'),
      );

      if (CRM_Utils_Array::value($isReceiptField[$formName][0], $fields)) {
        $errors += toCheckEmail(CRM_Utils_Array::value($isReceiptField[$formName][1], $fields), $isReceiptField[$formName][1]);
        if (!empty($errors)) {
          $errors[$isReceiptField[$formName][1]] = ts('The Outbound Domain Enforcement extension has prevented this From Email Address from being used as it uses a different domain.');
        }
      }
      break;

    case 'CRM_Admin_Form_ScheduleReminders':
      $email = CRM_Utils_Array::value('from_email', $fields);
      if (!$email) {
        list($ignore, $email) = CRM_Core_BAO_Domain::getNameAndEmail();
      }
      $errors += toCheckEmail($email, 'from_email');
      break;

    case 'CRM_UF_Form_Group':
      if (CRM_Utils_Array::value('notify', $fields)) {
        list($ignore, $email) = CRM_Core_BAO_Domain::getNameAndEmail();
        $errors += toCheckEmail($email, 'notify');
      }
      break;

    case 'CRM_Batch_Form_Entry':
      foreach ($fields['field'] as $key => $value) {
        if (CRM_Utils_Array::value('send_receipt', $value)) {
          list($ignore, $email) = CRM_Core_BAO_Domain::getNameAndEmail();
          $errors += toCheckEmail($email, "field[$key][send_receipt]");
          break;
        }
      }
      break;

    case 'CRM_Contact_Form_Domain':
      $errors += toCheckEmail(CRM_Utils_Array::value('email_address', $fields), 'email_address');
      break;

    case (substr($formName, 0, 16) == 'CRM_Report_Form_' ? TRUE : FALSE):
      if (CRM_Utils_Array::value('email_to', $fields) || CRM_Utils_Array::value('email_cc', $fields)) {
        list($ignore, $email) = CRM_Core_BAO_Domain::getNameAndEmail();
        $errors += toCheckEmail($email, 'email_to');
      }
      break;
  }
}

/**
* Function to check email address with domain.
*
* @param string $email
* @param string $field
* @param bool $returnHostName
*
* @return array|bool
*/
function toCheckEmail($email, $field, $returnHostName = FALSE) {
  $error = array();
  if (!$email) {
    return $error;
  }
  $config = CRM_Core_Config::singleton();
  $domain = get_domain($config->userFrameworkBaseURL);

  $isSSL = CRM_Core_BAO_Setting::getItem('CiviCRM Preferences', 'enableSSL');
  if ($isSSL) {
    preg_match('@^(?:https://)?([^/]+)@i', $domain, $matches);
  }
  else {
    preg_match('@^(?:http://)?([^/]+)@i', $domain, $matches);
  }

  // for testing purpose on local
  // $matches[1] = 'jmaconsulting.biz';

  $host = '@' . $matches[1];
  $host = '@nysenate.gov';//NYSS force this host as we don't set a value in the system org record
  if ($returnHostName) {
    return $host;
  }

  $isError = FALSE;
  $odeSettings = Civi::settings()->get('ode_settings');
  if (CRM_Utils_Array::value('ode_from_allowed', $odeSettings)) {
    if (!isFromEmail($email)) {
      $isError = TRUE;
    }
  }
  else {
    $hostLength = strlen($host);
    if (substr($email, -$hostLength) != $host) {
      $isError = TRUE;
    }
  }
  if ($isError) {
    $error[$field] = ts('The Outbound Domain Enforcement extension has prevented this From Email Address from being used as it uses a different domain than the System-generated Mail Settings From Email Address configured at Administer > Communications > Organization Address and Contact Info.');
  }
  return $error;
}

/**
 * @param $email
 *
 * @return bool
 *
 * check if passed email is in list of configured FROM email addresses
 * return TRUE if so
 */
function isFromEmail($email) {
  $domainEmails = CRM_Core_BAO_Email::domainEmails();
  $params = ['id' => CRM_Core_Config::domainID()];
  CRM_Core_BAO_Domain::retrieve($params, $domainDefaults);
  $locParams = array('contact_id' => $domainDefaults['contact_id']);
  $locationDefaults = CRM_Core_BAO_Location::getValues($locParams);
  if (!empty($locationDefaults['email'])) {
    $domainEmails['<' . $locationDefaults['email'][CRM_Core_Config::domainID()]['email'] . '>'] = 1;
  }

  foreach ($domainEmails as $domainEmail => $dontCare) {
    if (strpos($domainEmail, "<{$email}>") !== FALSE) {
      return TRUE;
    }
  }

  return FALSE;
}

/**
*  Implements hook_civicrm_buildForm().
*/
function ode_civicrm_buildForm($formName, &$form) {
  if (in_array($formName,
    array(
      'CRM_Mailing_Form_Upload',
      'CRM_Contact_Form_Task_Email',
      'CRM_Contribute_Form_Contribution',
      'CRM_Event_Form_Participant',
      'CRM_Member_Form_Membership',
      'CRM_Pledge_Form_Pledge',
      'CRM_Contribute_Form_Task_Email',
      'CRM_Event_Form_Task_Email',
      'CRM_Member_Form_Task_Email',
    ))) {

    $fromField = 'from_email_address';
    if (in_array($formName,
      array(
        'CRM_Contribute_Form_Task_Email',
        'CRM_Event_Form_Task_Email',
        'CRM_Member_Form_Task_Email',
      ))) {
      $fromField = 'fromEmailAddress';
    }

    if (!$form->elementExists($fromField)) {
      return NULL;
    }

    $showNotice = TRUE;
    if ($form->_flagSubmitted) {
      $showNotice = FALSE;
    }

    $elements = & $form->getElement($fromField);
    $options = & $elements->_options;
    ode_suppressEmails($options, $showNotice);

    if (empty($options)) {
      $options = array(
        array(
          'text' => ts('- Select -'),
          'attr' => array('value' => ''),
        ),
      );
    }
    $options = array_values($options);
  }
}

/**
* Function to supress email address.
*
* @param array $fromEmailAddress
* @param bool $showNotice
*
* @return array|NULL
*/
function ode_suppressEmails(&$fromEmailAddress, $showNotice) {
  $config = CRM_Core_Config::singleton();
  $domain = get_domain($config->userFrameworkBaseURL);
  $isSSL = CRM_Core_BAO_Setting::getItem('CiviCRM Preferences', 'enableSSL');
  if ($isSSL) {
    preg_match('@^(?:https://)?([^/]+)@i', $domain, $matches);
  }
  else {
    preg_match('@^(?:http://)?([^/]+)@i', $domain, $matches);
  }

  // for testing purpose on local
  // $matches[1] = 'jmaconsulting.biz';

  $domainEmails = $invalidEmails = array();
  if (ode_get_settings_value()) {
    // Allow domains configured in 'From' admin settings.
    // The main objective of this setting is to bypass emails that have been whitelisted and have SPF in place.
    $fromAdminEmails = CRM_Core_OptionGroup::values('from_email_address');

    foreach ($fromAdminEmails as $key => $val) {
      if (preg_match('/<([^>]+)>/', $val, $matches)) {
        $domainEmails[] = $matches[1];
      }
    }
  }

  $host = '@' . $matches[1];
  $host = '@nysenate.gov';//NYSS force this host as we don't set a value in the system org record
  $hostLength = strlen($host);
  foreach ($fromEmailAddress as $keys => $headers) {
    $email = pluckEmailFromHeader(html_entity_decode($headers['text']));
    if (empty($domainEmails)) {
      if (substr($email, -$hostLength) != $host) {
        $invalidEmails[] = $email;
        unset($fromEmailAddress[$keys]);
      }
    }
    else {
      if ((!in_array($email, $domainEmails)) && (substr($email, -$hostLength) != $host)) {
        $invalidEmails[] = $email;
        unset($fromEmailAddress[$keys]);
      }
    }
  }

  if (!empty($invalidEmails) && $showNotice) {
    //redirect user to enter from email address.
    $session = CRM_Core_Session::singleton();
    $message = "";
    $url = NULL;
    if (empty($fromEmailAddress)) {
      $message = " You can add another one <a href='%2'>here.</a>";
      $url = CRM_Utils_System::url('civicrm/admin/options/from_email_address', 'group=from_email_address&action=add&reset=1');
    }
    $status = ts('The Outbound Domain Enforcement extension has prevented the following From Email Address option(s) from being used as it uses a different domain than the System-generated Mail Settings From Email Address configured at Administer > Communications > Organization Address and Contact Info: %1' . $message, array(1 => implode(', ', $invalidEmails), 2 => $url));
    if ($showNotice === 'returnMessage') {
      return array('msg' => $status);
    }
    //NYSS disable all messages
    //$session->setStatus($status, ts('Notice'));
  }
  return NULL;
}

/**
* Function to pluck email address from header.
*
* @param array $header
*
* @return string|NULL
*/
function pluckEmailFromHeader($header) {
  preg_match('/<([^<]*)>/', $header, $matches);

  if (isset($matches[1])) {
    return $matches[1];
  }
  return NULL;
}

/**
* Function to get domain.
*
* @param string $domain
* @param bool $debug
*
* @return string|bool
*/
function get_domain($domain, $debug = FALSE) {
  $original = $domain = strtolower($domain);

  if (filter_var($domain, FILTER_VALIDATE_IP)) {
    return $domain;
  }

  $debug ? print ('<strong style="color:green">&raquo;</strong> Parsing: ' . $original) : FALSE;

  $arr = array_slice(array_filter(explode('.', $domain, 4), function($value) {
    return $value !== 'www';
  }), 0); //rebuild array indexes

  if (count($arr) > 2) {
    $count = count($arr);
    $_sub = explode('.', $count === 4 ? $arr[3] : $arr[2]);

    $debug ? print (" (parts count: {$count})") : FALSE;

    if (count($_sub) === 2) {// two level TLD
      $removed = array_shift($arr);
      if ($count === 4) {// got a subdomain acting as a domain
        $removed = array_shift($arr);
      }
      $debug ? print ("<br>\n" . '[*] Two level TLD: <strong>' . implode('.', $_sub) . '</strong> ') : FALSE;
    }
    elseif (count($_sub) === 1) {// one level TLD
      $removed = array_shift($arr); //remove the subdomain

      if (strlen($_sub[0]) === 2 && $count === 3) {// TLD domain must be 2 letters
        array_unshift($arr, $removed);
      }
      else {
        // non country TLD according to IANA
        $tlds = array(
          'aero',
          'arpa',
          'asia',
          'biz',
          'cat',
          'com',
          'coop',
          'edu',
          'gov',
          'info',
          'jobs',
          'mil',
          'mobi',
          'museum',
          'name',
          'net',
          'org',
          'post',
          'pro',
          'tel',
          'travel',
          'xxx',
        );

        if (count($arr) > 2 && in_array($_sub[0], $tlds) !== FALSE) {//special TLD don't have a country
          array_shift($arr);
        }
      }
      $debug ? print ("<br>\n" . '[*] One level TLD: <strong>' . implode('.', $_sub) . '</strong> ') : FALSE;
    }
    else {// more than 3 levels, something is wrong
      for ($i = count($_sub); $i > 1; $i--) {
        $removed = array_shift($arr);
      }
      $debug ? print ("<br>\n" . '[*] Three level TLD: <strong>' . implode('.', $_sub) . '</strong> ') : FALSE;
    }
  }
  elseif (count($arr) === 2) {
    $arr0 = array_shift($arr);

    if (strpos(implode('.', $arr), '.') === FALSE
      && in_array($arr[0], array('localhost', 'test', 'invalid')) === FALSE
    ) {// not a reserved domain
      $debug ? print ("<br>\n" . 'Seems invalid domain: <strong>' . implode('.', $arr) . '</strong> re-adding: <strong>' . $arr0 . '</strong> ') : FALSE;
      // seems invalid domain, restore it
      array_unshift($arr, $arr0);
    }
  }

  $debug ? print ("<br>\n" . '<strong style="color:gray">&laquo;</strong> Done parsing: <span style="color:red">' . $original . '</span> as <span style="color:blue">' . implode('.', $arr) . "</span><br>\n") : FALSE;

  return implode('.', $arr);
}


/**
 * Implements hook_civicrm_navigationMenu().
 *
 */
function ode_civicrm_navigationMenu(&$menu) {
  _ode_civix_insert_navigation_menu($menu, 'Administer/Communications', array(
    'label' => ts('ODE Preferences', array('domain' => 'biz.jmaconsulting.ode')),
    'name' => 'ode_settings',
    'url' => 'civicrm/ode/settings?reset=1',
    'permission' => 'administer CiviCRM',
    'operator' => 'AND',
    'separator' => 1,
  ));
  _ode_civix_navigationMenu($menu);
}

/**
 * Function to check from email address are configured correctlly for
 * 1. Contribution Page
 * 2. Event Page
 * 3. Schedule Reminders
 * 4. Organization Address and Contact Info
 * 5. If grant application is installed then application page
 */
function checkValidEmails() {
  $getHostName = toCheckEmail('dummy@dummy.com', NULL, TRUE);
  $config = CRM_Core_Config::singleton();
  if (property_exists($config, 'civiVersion')) {
    $civiVersion = $config->civiVersion;
  }
  else {
    $civiVersion = CRM_Core_BAO_Domain::version();
  }

  $error = array();

  $links = array(
    'Contribution Page(s)' => 'civicrm/admin/contribute/thankyou',
    'Event(s)' => 'civicrm/event/manage/registration',
    'Schedule Reminder(s)' => 'civicrm/admin/scheduleReminders',
  );

  // Contribution Pages.
  $contributionPageParams = array(
    'is_email_receipt' => 1,
    'receipt_from_email' => array("NOT LIKE" => "'%{$getHostName}'"),
    'return' => array('id', 'title'),
  );
  $result = civicrm_api3('contribution_page', 'get', $contributionPageParams);
  if ($result['count'] > 0) {
    foreach ($result['values'] as $values) {
      $error['Contribution Page(s)'][] = "<a target='_blank' href='" . CRM_Utils_System::url($links['Contribution Page(s)'], "reset=1&action=update&id={$values['id']}") . "'>{$values['title']}</a>";
    }
  }

  // Events.
  $eventParams = array(
    'is_email_confirm' => 1,
    'is_template' => array("<>" => 1),
    'confirm_from_email' => array("NOT LIKE" => "'%{$getHostName}'"),
    'return' => array('id', 'title'),
  );
  $result = civicrm_api3('event', 'get', $eventParams);
  if ($result['count'] > 0) {
    foreach ($result['values'] as $values) {
      $error['Event(s)'][] = "<a target='_blank' href='" . CRM_Utils_System::url($links['Event(s)'], "reset=1&action=update&id={$values['id']}") . "'>{$values['title']}</a>";
    }
  }

  // Schedule Reminders.
  if (version_compare('4.5.0', $civiVersion) <= 0) {
    $dao = CRM_Core_DAO::executeQuery("SELECT id, title FROM civicrm_action_schedule WHERE `from_email` NOT LIKE '%{$getHostName}'");
    while ($dao->fetch()) {
      $error['Schedule Reminder(s)'][] = "<a target='_blank' href='" . CRM_Utils_System::url($links['Schedule Reminder(s)'], "reset=1&action=update&id={$dao->id}") . "'>{$dao->title}</a>";
    }
  }

  // Grant Application Pages.
  $query = "SELECT id FROM `civicrm_extension` WHERE full_name = 'biz.jmaconsulting.grantapplications' AND is_active = 1;";
  $dao = CRM_Core_DAO::executeQuery($query);
  if ($dao->N) {
    $links['Grant Application Page(s)'] = 'civicrm/admin/grant/thankyou';
    $grantAppParams = array(
      'is_email_receipt' => 1,
      'receipt_from_email' => array("NOT LIKE" => "'%{$getHostName}'"),
      'return' => array('id', 'title'),
    );
    $result = civicrm_api3('grant_application_page', 'get', $grantAppParams);
    if ($result['count'] > 0) {
      foreach ($result['values'] as $values) {
        $error['Grant Application Page(s)'][] = "<a target='_blank' href='" . CRM_Utils_System::url($links['Grant Application Page(s)'], "reset=1&action=update&id={$values['id']}") . "'>{$values['title']}</a>";
      }
    }
  }

  list($ignore, $email) = CRM_Core_BAO_Domain::getNameAndEmail();
  $hostLength = strlen($getHostName);
  if (substr($email, -$hostLength) != $getHostName) {
    $error['Organization Address and Contact Info'][] = "<a target='_blank' href='" . CRM_Utils_System::url('civicrm/admin/domain', 'action=update&reset=1') . "'>Click Here</a>";
  }

  if (!empty($error)) {
    $errorMessage = 'Please check the following configurations for emails that have an invalid domain name.<ul>';
    foreach ($error as $title => $links) {
      $errorMessage .= "<li>$title<ul>";
      foreach ($links as $link) {
        $errorMessage .= "<li>$link</li>";
      }
      $errorMessage .= '</ul></li>';
    }
    $errorMessage .= '</ul>';
    //NYSS disable all messages
    //CRM_Core_Session::singleton()->setStatus($errorMessage, ts('Notice'));
  }
}

/**
 * Function to get settings value for ode_from_allowed.
 *
 * @return string
 */
function ode_get_settings_value() {
  $config = CRM_Core_Config::singleton();
  if (property_exists($config, 'civiVersion')) {
    $civiVersion = $config->civiVersion;
  }
  else {
    $civiVersion = CRM_Core_BAO_Domain::version();
  }
  if (version_compare('4.7alpha1', $civiVersion) > 0) {
    $value = CRM_Core_BAO_Setting::getItem(NULL, 'ode_from_allowed');
  }
  else {
    $value = Civi::settings()->get('ode_from_allowed');
  }
  return $value;
}

/**
 * Function to set settings value for ode_from_allowed.
 *
 * @param string $settingValue
 *
 */
function ode_set_settings_value($settingValue) {
  $config = CRM_Core_Config::singleton();
  if (property_exists($config, 'civiVersion')) {
    $civiVersion = $config->civiVersion;
  }
  else {
    $civiVersion = CRM_Core_BAO_Domain::version();
  }
  if (version_compare('4.7alpha1', $civiVersion) > 0) {
    CRM_Core_BAO_Setting::setItem($settingValue,
      NULL,
      'ode_from_allowed'
    );
  }
  else {
    $value = Civi::settings()->set('ode_from_allowed', $settingValue);
  }
}

/**
 * Implements hook_civicrm_apiWrappers().
 *
 */
function ode_civicrm_apiWrappers(&$wrappers, $apiRequest) {
  if ($apiRequest['entity'] == 'OptionValue' && $apiRequest['action'] == 'get') {
    $optionGroupId = civicrm_api3('OptionGroup', 'getvalue', array(
      'return' => "id",
      'name' => "from_email_address",
    ));
    $optionGroup = CRM_Utils_Array::value(
      'option_group_id',
      CRM_Utils_Array::value('params', $apiRequest)
    );
    if (in_array($optionGroup, array($optionGroupId, 'from_email_address'))) {
      $wrappers[] = new CRM_Ode_OdeAPIWrapper();
    }
  }
}
