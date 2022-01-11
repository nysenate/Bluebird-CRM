<?php
class CRM_RecalculateRecipients_Run{
  static function run(){

    $mailings = $jobs = [];
    $currentTime = date('YmdHis');
    try {
      $jobs = civicrm_api3('MailingJob', 'get', [
        'status' =>  'scheduled',
        'scheduled_date' => ['<=' => $currentTime],
        'option.limit' => 0
      ]);
    }
    catch (CiviCRM_API3_Exception $e) {
      CRM_Core_Error::debug_log_message("Couldn\'t retrieve list of mailings.", false, 'civicrm-relculate-recipients');
    }
    foreach($jobs['values'] as $job){
      $mailings[] = $job['mailing_id'];
    }
    $mailings = array_unique($mailings);
    foreach($mailings as $mailingId){
      try{
        CRM_Mailing_BAO_Mailing::getRecipients($mailingId);
      }
      catch (CiviCRM_API3_Exception $e) {
        CRM_Core_Error::debug_log_message("Couldn't retrieve mailing id: {$mailingId}", false, 'civicrm-relculate-recipients');
      }
    }
  }
}
