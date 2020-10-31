<?php
use CRM_NYSS_Mail_ExtensionUtil as E;

class CRM_NYSS_Mail_Page_PreviewRecipients extends CRM_Core_Page {

  public function run() {
    $mailingId = CRM_Utils_Request::retrieve('id', 'Positive');

    if (empty($mailingId)) {
      CRM_Core_Error::statusBounce('No mailing ID provided');
    }

    $rows = [];
    $count = 0;

    try {
      $recipients = civicrm_api3('MailingRecipients', 'get', [
        'sequential' => 1,
        'mailing_id' => $mailingId,
        'options' => ['limit' => 50],
      ]);
      //Civi::log()->debug(__FUNCTION__, ['recipients' => $recipients]);

      foreach ($recipients['values'] as $recipient) {
        $contact = civicrm_api3('Contact', 'getvalue', ['id' => $recipient['contact_id'], 'return' => 'display_name']);
        $email = civicrm_api3('Email', 'getvalue', ['id' => $recipient['email_id'], 'return' => 'email']);

        $rows[] = [
          'id' => $recipient['contact_id'],
          'name' => $contact,
          'email' => $email,
        ];
      }
      //Civi::log()->debug(__FUNCTION__, ['$list' => $rows]);

      //get total count
      $count = civicrm_api3('MailingRecipients', 'getcount', [
        'mailing_id' => $mailingId,
      ]);
      //Civi::log()->debug(__FUNCTION__, ['$count' => $count]);
    }
    catch (CiviCRM_API3_Exception $e) {}

    $partialCount = 50;
    if ($recipients['count'] < 50) {
      $partialCount = $recipients['count'];
    }

    $this->assign('rows', $rows);
    $this->assign('recipientCount', $count);
    $this->assign('partialCount', $partialCount);

    parent::run();
  }

}
