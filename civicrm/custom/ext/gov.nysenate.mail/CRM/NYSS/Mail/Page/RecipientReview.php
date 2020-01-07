<?php
use CRM_NYSS_Mail_ExtensionUtil as E;

class CRM_NYSS_Mail_Page_RecipientReview extends CRM_Core_Page {

  public function run() {
    $mailingId = CRM_Utils_Request::retrieve('id', 'Positive');
    $count = CRM_Utils_Request::retrieve('count', 'Integer');
    $recipContacts = [];

    CRM_Utils_System::setTitle(E::ts("Mailing Recipient Review (~{$count} recipients)"));

    if (!$mailingId) {
      $message = '<p>No mailing ID was provided. This page must be viewed within the context of a mailing.</p>';
    }
    else {
      $message = "<p>Based on current data, approximately {$count} contacts will receive a copy of the mailing.</p>
        <p>If individual contacts are separately modified, added, or removed, then the final list may change.</p>";

      try {
        $recipients = civicrm_api3('MailingRecipients', 'get', [
          'mailing_id' => $mailingId,
        ]);

        foreach ($recipients['values'] as $recipient) {
          try {
            $contact = civicrm_api3('Contact', 'getvalue', [
              'id' => $recipient['contact_id'],
              'return' => 'display_name',
            ]);

            $email = civicrm_api3('Email', 'getvalue', [
              'id' => $recipient['email_id'],
              'return' => 'email',
            ]);

            $recipContacts[$recipient['id']] = [
              'contact_id' => $recipient['contact_id'],
              'contact' => $contact,
              'email_id' => $recipient['email_id'],
              'email' => $email,
            ];
          }
          catch (CiviCRM_API3_Exception $e) {
            Civi::log()->debug(__FUNCTION__, ['$e' => $e]);
          }

        }
      }
      catch (CiviCRM_API3_Exception $e) {
        Civi::log()->debug(__FUNCTION__, ['$e' => $e]);
      }
    }

    $this->assign('message', $message);
    $this->assign('recipContacts', $recipContacts);

    parent::run();
  }

}
