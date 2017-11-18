<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 =>
  array (
    'name' => 'Cron:JobLog.Purge',
    'entity' => 'Job',
    'params' =>
    array (
      'version' => 3,
      'name' => 'Call JobLog.Purge API',
      'description' => 'Call JobLog.Purge API',
      'run_frequency' => 'Daily',
      'api_entity' => 'JobLog',
      'api_action' => 'Purge',
      // Parameter 2: api_call by default takes 'all' value. If you explicitly define it here as api_call=job.version_check or anything else,
      // then only the log records for the above job_type would be deleted based on the day_retained value
      'parameters' => 'days_retained=90
      api_call=all',
    ),
  ),
);