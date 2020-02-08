<?php

use CRM_ReportError_ExtensionUtil as E;

class CRM_ReportError_Handler_Profiles {

  /**
   * Profiles generate a lot of errors, either because they were disabled
   * or because they are reserved to admins.
   */
  static public function handler($vars, $options_overrides) {
    $sendreport = TRUE;

    if ($arg[0] == 'civicrm' && $arg[1] == 'profile') {
      $redirect = reporterror_setting_get('reporterror_noreferer_handle_profiles', $options_overrides);
      $sendreport = reporterror_setting_get('reporterror_sendreport_profile', $options_overrides);

      $vars['redirect_path'] = CRM_Utils_System::baseCMSURL();

      $output = reporterror_civicrm_generatereport($site_name, $vars, NULL, $options_overrides);

      if ($sendreport) {
        CRM_ReportError_Utils::sendReport($vars, $options_overrides);
      }

      if ($handle && !empty($vars['redirect_path'])) {
        Civi::log()->warning("Report Error Extension: redirected to home page:\n" . $output);
        CRM_Utils_System::redirect($vars['redirect_path']);
      }

      return TRUE;
    }

    return FALSE;
  }

}
