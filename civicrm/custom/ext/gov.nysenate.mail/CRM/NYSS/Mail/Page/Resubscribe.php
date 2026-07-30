<?php

/**
 * NYSS #18424
 * Custom intercept of Resubscribe page. We redirect the user to our custom subscription management page via the
 * public proxy URL.
 */
#[CRM_NYSS_Attribute_IssueRef(18424)]
class CRM_NYSS_Mail_Page_Resubscribe extends CRM_Core_Page {

  public function run(): void {
    // first validate current URL
    $queue_id = CRM_Utils_Request::retrieve('eq', 'Integer');
    // accommodating both intended proxy pattern ('tk') (NYSS #18424) and current proxy pattern ('cs')
    $hash = CRM_Utils_Request::retrieve('tk', 'String') ?: CRM_Utils_Request::retrieve('cs', 'String');

    $q = CRM_Mailing_Event_BAO_MailingEventQueue::verify(NULL, $queue_id, $hash);
    if (!$q) {
      CRM_Utils_System::sendInvalidRequestResponse(ts("Invalid request: bad parameters"));
    }

    // generate fresh checksum and redirect to NYSS custom subscription view page.
    $cs = CRM_Contact_BAO_Contact_Utils::generateChecksum(
      $q->contact_id,
      CRM_Utils_Time::time(),
      Civi::settings()->get('nyssUnsubLinkTimeout')
    );

    $bbcfg = get_bluebird_instance_config();
    $url = "{$bbcfg['public.url.base']}/{$bbcfg['envname']}/{$bbcfg['shortname']}/subscription/manage/{$queue_id}/{$cs}";

    CRM_Utils_System::redirect($url);
  }

}
