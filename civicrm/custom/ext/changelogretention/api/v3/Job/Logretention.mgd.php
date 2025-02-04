<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 =>
  array (
    'name' => 'Cron:Job.Logretention',
    'entity' => 'Job',
    'params' =>
    array (
      'version' => 3,
      'name' => 'Log Retention',
      'description' => 'Purge changelog records outside of the configured retention window.',
      'run_frequency' => 'Weekly',
      'api_entity' => 'Job',
      'api_action' => 'logretention',
      'parameters' => '',
    ),
    'update' => 'never'
  ),
);
