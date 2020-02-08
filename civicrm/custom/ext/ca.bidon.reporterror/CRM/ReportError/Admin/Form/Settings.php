<?php

use CRM_ReportError_ExtensionUtil as E;

class CRM_ReportError_Admin_Form_Settings extends CRM_Admin_Form_Setting {
  protected $_values;

  protected $_settings = [
    'reporterror_noreferer_handle' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'reporterror_noreferer_pageid' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'reporterror_noreferer_sendreport' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'reporterror_noreferer_handle_event' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'reporterror_noreferer_handle_eventid' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'reporterror_noreferer_sendreport_event' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'reporterror_handle_profile' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'reporterror_sendreport_profile' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'reporterror_mailto' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'reporterror_show_full_backtrace' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'reporterror_show_post_data' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'reporterror_show_session_data' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'reporterror_bots_sendreport' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'reporterror_bots_404' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'reporterror_bots_regexp' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'reporterror_smartgroups_autodisable' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'reporterror_gelf_enable' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'reporterror_gelf_hostname' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'reporterror_gelf_port' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
  ];

  /**
   * Function to build the form
   *
   * @return None
   * @access public
   */
  public function buildQuickForm() {
    $this->applyFilter('__ALL__', 'trim');

    $this->add('text', 'reporterror_mailto',
      E::ts('Error Report Recipient'),
      CRM_Utils_Array::value('mailto', $this->_values),
      FALSE);

    $this->addYesNo('reporterror_show_full_backtrace', E::ts('Display a full backtrace in e-mails?'));
    $this->addYesNo('reporterror_show_post_data', E::ts('Display POST data in e-mails?'));
    $this->addYesNo('reporterror_show_session_data', E::ts('Display session data in e-mails?'));

    // Special handling of Contribution page errors.
    // Get a list of contribution pages
    $results = civicrm_api3('ContributionPage', 'get', [
      'is_active' => 1,
      'option.limit' => 0,
    ]);

    $contribution_pages = [
      0 => ts('- Select -'),
    ];

    foreach ($results['values'] as $val) {
      $contribution_pages[$val['id']] = CRM_Utils_Array::value('title', $val);
    }

    $radio_choices = [
      '0' => E::ts('Do nothing (show the CiviCRM error)'),
      '1' => E::ts('Redirect to front page of CMS'),
      '2' => E::ts('Redirect to a specific contribution page'),
    ];

    $this->addRadio('reporterror_noreferer_handle',
      E::ts('Enable transparent redirection?'),
      $radio_choices,
      array('options_per_line' => 1),
      '<br/>' /* one option per line */
     );

    $this->addYesNo('reporterror_noreferer_sendreport', E::ts('Send error reports for this particular error?'));

    $this->add('select', 'reporterror_noreferer_pageid',
      E::ts('Redirect to Contribution Page'),
      $contribution_pages,
      TRUE);

    // Special handling of Event page errors.
    $results = civicrm_api3('Event', 'get', [
      'is_active' => 1,
      'option.limit' => 0,
    ]);

    $event_pages = [
      0 => ts('- Select -'),
    ];

    foreach ($results['values'] as $val) {
      $event_pages[$val['id']] = CRM_Utils_Array::value('title', $val);
    }

    $radio_choices = [
      '0' => E::ts('Do nothing (show the CiviCRM error)'),
      '1' => E::ts('Redirect to front page of CMS'),
      '2' => E::ts('Redirect to a specific event registration page'),
    ];

    $this->addRadio('reporterror_noreferer_handle_event',
      E::ts('Enable transparent redirection?'),
      $radio_choices,
      array('options_per_line' => 1),
      '<br/>' /* one option per line */
     );

    $this->addYesNo('reporterror_noreferer_sendreport_event', E::ts('Send error reports for this particular error?'));

    $this->add('select', 'reporterror_noreferer_handle_eventid',
      E::ts('Redirect to Event Page'),
      $event_pages,
      TRUE);

    // Special handling of profiles
    $radio_choices = [
      '0' => E::ts('Do nothing (show the CiviCRM error)'),
      '1' => E::ts('Redirect to front page of CMS'),
    ];

    $this->addRadio('reporterror_handle_profile',
      E::ts('Enable transparent redirection?'),
      $radio_choices,
      array('options_per_line' => 1),
      '<br/>' /* one option per line */
     );

    $this->addYesNo('reporterror_sendreport_profile', E::ts('Send error reports for this particular error?'));

    // Special handling of bots
    $this->addYesNo('reporterror_bots_sendreport', E::ts('Send error reports for errors caused by bots?'));
    $this->addYesNo('reporterror_bots_404', E::ts('Respond with a 404 page not found error?'));

    $this->add('text', 'reporterror_bots_regexp', E::ts('Bots to ignore'), TRUE);

    // Smartgroups
    $this->addYesNo('reporterror_smartgroups_autodisable', E::ts('Automatically disable broken smartgroups?'));

    // Remote Logging
    $this->addYesNo('reporterror_gelf_enable', E::ts('Enable remote logging?'));
    $this->add('text', 'reporterror_gelf_hostname', E::ts('Logging server hostname'));
    $this->add('text', 'reporterror_gelf_port', E::ts('Logging server port'));

    $this->addButtons([
      array(
        'type' => 'submit',
        'name' => ts('Save'),
        'isDefault' => TRUE,
      ),
      array(
        'type' => 'cancel',
        'name' => ts('Cancel'),
      ),
    ]);
  }

  /**
   * Function to process the form
   *
   * @access public
   * @return None
   */
  public function postProcess() {
    // store the submitted values in an array
    $values = $this->exportValues();

    foreach ($this->_settings as $setting => $group) {
      $value = $values[$setting];
      Civi::settings()->set($setting, $value);
    }

    // Return back to this form by default
    CRM_Core_Session::setStatus(E::ts('Settings saved.'), '', 'success');
    CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/admin/setting/reporterror', 'reset=1'));
  }
}
