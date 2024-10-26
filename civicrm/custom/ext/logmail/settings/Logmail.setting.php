<?php

return [
  'logmail_enable' => [
    'group_name' => 'Logmail',
    'group' => 'logmail',
    'name' => 'logmail_enable',
    'title' => 'Enable email logging?',
    'type' => 'Boolean',
    'quick_form_type' => 'YesNo',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => '',
    'help_text' => 'Prevent email delivery and optionally send to a log file.',
    'settings_pages' => ['logmail' => ['weight' => 1]],
  ],
  'logmail_file' => [
    'group_name' => 'Logmail',
    'group' => 'logmail',
    'name' => 'logmail_file',
    'title' => 'Send emails to a log file?',
    'type' => 'Boolean',
    'quick_form_type' => 'YesNo',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => '',
    'help_text' => 'If yes, emails will be written to /ConfigAndLog/Logmail.php. If no, emails will be discarded.',
    'settings_pages' => ['logmail' => ['weight' => 1]],
  ],
];
