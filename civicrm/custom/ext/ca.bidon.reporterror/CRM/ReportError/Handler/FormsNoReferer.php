<?php

use CRM_ReportError_ExtensionUtil as E;

class CRM_ReportError_Handler_FormsNoReferer {

  /**
   * Contribution or Event forms: if an error was generated, but there was no HTTP_REFERER,
   * it is most likely a bot, a restored session, or copy-pasted link. Those users should be
   * redirected to a more appropriate page, instead of fatal error.
   *
   * Typical use-case: someone copy-pasted a link to a contribution form, but they did not
   * select the arguments of the URL, so it directs people to /civicrm/contribution/transact,
   * which fatals because there is no 'id' present.
   */
  static public function handler($vars, $options_overrides) {
    $sendreport = TRUE;

    $config = CRM_Core_Config::singleton();
    $urlVar = $config->userFrameworkURLVar;
    $arg = explode('/', $_GET[$urlVar]);

    // Redirect for Contribution pages without a referrer (close / restore browser page)
    if ($arg[0] == 'civicrm' && $arg[1] == 'contribute' && $arg[2] == 'transact' && ! $_SERVER['HTTP_REFERER'] && $_SERVER['REQUEST_METHOD'] != 'HEAD') {
      $handle = reporterror_setting_get('reporterror_noreferer_handle', $options_overrides);
      $pageid = reporterror_setting_get('reporterror_noreferer_pageid', $options_overrides);
      $sendreport = reporterror_setting_get('reporterror_noreferer_sendreport', $options_overrides, 1);

      if ($handle == 1 || ($handle == 2 && ! $pageid)) {
        $vars['redirect_path'] = CRM_Utils_System::baseCMSURL();
      }
      elseif ($handle == 2) {
        $vars['redirect_path'] = CRM_Utils_System::url('civicrm/contribute/transact', 'reset=1&id=' . $pageid);
      }
    }
    elseif ($arg[0] == 'civicrm' && $arg[1] == 'event' && ! $_SERVER['HTTP_REFERER'] && $_SERVER['REQUEST_METHOD'] != 'HEAD') {
      $handle = reporterror_setting_get('reporterror_noreferer_handle_event', $options_overrides);
      $pageid = reporterror_setting_get('reporterror_noreferer_handle_eventid', $options_overrides);
      $sendreport = reporterror_setting_get('reporterror_noreferer_sendreport_event', $options_overrides, 1);

      if ($handle == 1 || ($handle == 2 && ! $pageid)) {
        $vars['redirect_path'] = CRM_Utils_System::baseCMSURL();
      }
      elseif ($handle == 2) {
        $vars['redirect_path'] = CRM_Utils_System::url('civicrm/event/register', 'reset=1&id=' . $pageid);
      }
    }

    if ($sendreport) {
      if (!empty($vars['redirect_path'])) {
        $vars['reporterror_subject'] = E::ts('redirected');
      }

      CRM_ReportError_Utils::sendReport($vars, $options_overrides);
    }

    // A redirection avoids displaying the error to the user.
    if (!empty($vars['redirect_path'])) {
      // 307 = temporary redirect. Assuming it reduces the chances that the browser
      // keeps the redirection in cache.
      CRM_Utils_System::redirect($vars['redirect_path']);
      return TRUE;
    }

    return FALSE;
  }

}
