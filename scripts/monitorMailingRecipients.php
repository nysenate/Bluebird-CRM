<?php

/**
 * Project: BluebirdCRM
 * Author: Nathan Frank
 * Organization: New York State Senate
 * Date: 2025-03-27
 * Revised: ----
 *
 * This script is intended to be temporary. It is a responsive measure to
 * ticket #17075. A hopeful fix was put into place. Though, the nature of the
 * fix makes it hard to test. This is meant to check if the fix worked or not.
 *
 * Its purpose is to compare intended mailing recipients against the actual
 * mail queue and look for duplicate recipients in the mail queue.
 * If duplicates are found, then it will be reported.
 *
 * The intention is for this to run from cron.
 * Args are -S <Site> and -s <Since>. <Since> must be compatible with
 * PHP strtotime(), and must be in the past. eg. 30 days ago or -1 week
 */
$prog = basename(__FILE__);

require_once dirname(__FILE__).'/../civicrm/scripts/script_utils.php';
$optlist = civicrm_script_init("s", array('since='), FALSE);

if ($optlist === null) {
  $stdusage = civicrm_script_usage();
  error_log("Usage: ".basename(__FILE__)."  -S <SITE> [--since]\n");
  exit(1);
}

drupal_script_init();

require_once 'CRM/Core/Config.php';
$config = CRM_Core_Config::singleton();

// load cli args
$since = (!empty($optlist['since'])) ? strtotime($optlist['since']): strtotime('30 days ago');
// get instance settings
$bbcfg = get_bluebird_instance_config($optlist['site']);

// Get list of mailings within the given timeframe
$mailings = \Civi\Api4\Mailing::get(FALSE)
  ->addSelect('id', 'name')
  ->addWhere('scheduled_date', '>=', date('Y-m-d',$since))
  ->addWhere('is_completed', '=', TRUE)
  ->execute();

// check each mailing for duplicate recipients in event queue
foreach ($mailings as $mailing) {
  $sql = "SELECT c FROM 
             (SELECT COUNT(r.contact_id) c, r.contact_id, r.email_id  
              FROM civicrm_mailing_recipients r 
                  JOIN civicrm_mailing_job j ON r.mailing_id = j.mailing_id 
                  JOIN civicrm_mailing_event_queue q ON j.id=q.job_id 
              WHERE r.contact_id = q.contact_id 
                AND r.email_id = q.email_id 
                AND r.mailing_id = %1 
                AND q.is_test = 0
             GROUP BY r.contact_id, r.email_id) a 
         WHERE c > 1";
  $params = [1 => [$mailing['id'], 'Integer']];
  $result = CRM_Core_DAO::executeQuery($sql, $params);
  if ($result->N) {
    $message = "Mailing ".$mailing['name']." (ID: ". $mailing['id'] .") has duplicated recipients";
    bbscript_log(LL::WARN, $message);
    CRM_NYSS_Errorhandler_BAO::notifySlack($message);
    CRM_NYSS_Errorhandler_BAO::notifyEmail($message, 'Report of Duplicate Mail Recipients');
  }
}
