<?php

/**
 * One-click unsubscribe page (RFC 8058).
 *
 * Overrides the core page so that one-click POSTs act against
 * mailing's category rather than CiviCRM groups.
 */
#[CRM_NYSS_Attribute_IssueRef(18424)]
class CRM_NYSS_Mail_Page_Unsubscribe extends CRM_Mailing_Page_Unsubscribe {

  public function handleOneClick(): void {
    $jobId = CRM_Utils_Request::retrieve('jid', 'Integer');
    $queueId = CRM_Utils_Request::retrieve('qid', 'Integer');
    $hash = CRM_Utils_Request::retrieve('h', 'String');

    $q = CRM_Mailing_Event_BAO_MailingEventQueue::verify(NULL, $queueId, $hash);
    if (!$q) {
      CRM_Utils_System::sendInvalidRequestResponse(ts("Invalid request: bad parameters"));
    }

    $contactId = $q->contact_id;

    // Resolve mailing ID and the specific email address that received this mailing.
    $row = CRM_Core_DAO::executeQuery(
      "SELECT mj.mailing_id, em.id AS email_id
         FROM civicrm_mailing_event_queue meq
         INNER JOIN civicrm_mailing_job mj ON mj.id = meq.job_id
         INNER JOIN civicrm_email em ON em.id = meq.email_id
         WHERE meq.id = %1",
      [1 => [$queueId, 'Positive']]
    );
    if (!$row->fetch()) {
      Civi::log()->warning('CRM_NYSS_Mail_Page_Unsubscribe: could not resolve mailing/email from queue', [
        'queueId' => $queueId, 'contactId' => $contactId,
      ]);
      CRM_Utils_System::sendOkRequestResponse();
      return;
    }
    $mailingId = (int) $row->mailing_id;
    $emailId = (int) $row->email_id;

    $category = CRM_Core_DAO::singleValueQuery(
      "SELECT category FROM civicrm_mailing WHERE id = %1",
      [1 => [$mailingId, 'Positive']]
    );

    if (empty($category)) {
      Civi::log()->warning('CRM_NYSS_Mail_Page_Unsubscribe: mailing has no category, one-click unsubscribe ignored', [
        'mailingId' => $mailingId, 'contactId' => $contactId, 'jobId' => $jobId,
      ]);
      CRM_Utils_System::sendOkRequestResponse();
      return;
    }

    $currentCategories = CRM_Core_DAO::singleValueQuery(
      "SELECT mailing_categories FROM civicrm_email WHERE id = %1",
      [1 => [$emailId, 'Positive']]
    );
    $categoryList = array_values(array_filter(explode(',', $currentCategories ?? '')));

    if (!in_array($category, $categoryList)) {
      $categoryList[] = $category;
      civicrm_api3('Email', 'create', [
        'id' => $emailId,
        'mailing_categories' => implode(',', $categoryList),
      ]);
    }

    // Track the unsubscribe event for reporting (mirrors what unsub_from_mailing does).
    $ue = new CRM_Mailing_Event_BAO_MailingEventUnsubscribe();
    $ue->event_queue_id = $queueId;
    $ue->org_unsubscribe = 0;
    $ue->time_stamp = date('YmdHis');
    $ue->save();

    // Look up the category label so the confirmation email reads sensibly.
    $categoryLabel = CRM_Core_DAO::singleValueQuery(
      "SELECT ov.label
         FROM civicrm_option_value ov
         INNER JOIN civicrm_option_group og ON og.id = ov.option_group_id
         WHERE og.name = 'mailing_categories' AND ov.value = %1",
      [1 => [$category, 'String']]
    ) ?? $category;

    CRM_Mailing_Event_BAO_MailingEventUnsubscribe::send_unsub_response(
      $queueId, [$category => $categoryLabel], FALSE, $jobId
    );

    Civi::log()->info('CRM_NYSS_Mail_Page_Unsubscribe: one-click category opt-out recorded', [
      'mailingId' => $mailingId, 'contactId' => $contactId, 'emailId' => $emailId,
      'category' => $category, 'jobId' => $jobId,
    ]);

    CRM_Utils_System::sendOkRequestResponse();
  }

}
