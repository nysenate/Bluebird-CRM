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
    $queue_id = CRM_Utils_Request::retrieve('eq', 'Integer');
    // accommodating both intended proxy pattern ('tk') (NYSS #18424) and current proxy pattern ('cs')
    $hash = CRM_Utils_Request::retrieve('tk', 'String') ?: CRM_Utils_Request::retrieve('cs', 'String');

    $q = CRM_Mailing_Event_BAO_MailingEventQueue::verify(NULL, $queue_id, $hash);
    if (!$q) {
      Civi::log()->warning('CRM_NYSS_Mail_Page_Unsubscribe: failed to verify queue_id and event queue hash', [
        '$queue_id' => $queue_id, '$hash' => $hash,
      ]);
      CRM_Utils_System::sendInvalidRequestResponse(ts("Invalid request: bad parameters"));
      return;
    }

    $contact_id = $q->contact_id;

    // Resolve mailing ID and the specific email address that received this mailing.
    $row = CRM_Core_DAO::executeQuery(
      "SELECT mj.mailing_id, mj.id AS job_id, em.id AS email_id
         FROM civicrm_mailing_event_queue meq
         INNER JOIN civicrm_mailing_job mj ON mj.id = meq.job_id
         INNER JOIN civicrm_email em ON em.id = meq.email_id
         WHERE meq.id = %1 AND meq.hash = %2",
      [1 => [$queue_id, 'Positive'], 2 => [$hash, 'String']]
    );
    if (!$row->fetch()) {
      Civi::log()->warning('CRM_NYSS_Mail_Page_Unsubscribe: could not resolve mailing/email from queue', [
        '$queue_id' => $queue_id, 'contactId' => $contact_id,
      ]);
      CRM_Utils_System::sendOkRequestResponse();
      return;
    }
    $mailing_id = (int) $row->mailing_id;
    $email_id = (int) $row->email_id;
    $job_id = (int) $row->job_id;

    $category = CRM_Core_DAO::singleValueQuery(
      "SELECT category FROM civicrm_mailing WHERE id = %1",
      [1 => [$mailing_id, 'Positive']]
    );

    if (empty($category)) {
      Civi::log()->warning('CRM_NYSS_Mail_Page_Unsubscribe: mailing has no category, one-click unsubscribe ignored', [
        'mailingId' => $mailing_id, 'contactId' => $contact_id,
      ]);
      CRM_Utils_System::sendOkRequestResponse();
      return;
    }

    $current_categories = CRM_Core_DAO::singleValueQuery(
      "SELECT mailing_categories FROM civicrm_email WHERE id = %1",
      [1 => [$email_id, 'Positive']]
    );
    $category_list = array_values(array_filter(explode(',', $current_categories ?? '')));

    if (!in_array($category, $category_list)) {
      $category_list[] = $category;
      civicrm_api3('Email', 'create', [
        'id' => $email_id,
        'mailing_categories' => implode(',', $category_list),
      ]);
    }

    // Track the unsubscribe event for reporting (mirrors what unsub_from_mailing does).
    $ue = new CRM_Mailing_Event_BAO_MailingEventUnsubscribe();
    $ue->event_queue_id = $queue_id;
    $ue->org_unsubscribe = 0;
    $ue->time_stamp = date('YmdHis');
    $ue->save();

    // Look up the category label so the confirmation email reads sensibly.
    $category_label = CRM_Core_DAO::singleValueQuery(
      "SELECT ov.label
         FROM civicrm_option_value ov
         INNER JOIN civicrm_option_group og ON og.id = ov.option_group_id
         WHERE og.name = 'mailing_categories' AND ov.value = %1",
      [1 => [$category, 'String']]
    ) ?? $category;

    CRM_Mailing_Event_BAO_MailingEventUnsubscribe::send_unsub_response(
      $queue_id, [$category => $category_label], FALSE, $job_id
    );

    Civi::log()->info('CRM_NYSS_Mail_Page_Unsubscribe: one-click category opt-out recorded', [
      'mailingId' => $mailing_id, 'contactId' => $contact_id, 'emailId' => $email_id,
      'category' => $category
    ]);

    CRM_Utils_System::sendOkRequestResponse();
  }

}
