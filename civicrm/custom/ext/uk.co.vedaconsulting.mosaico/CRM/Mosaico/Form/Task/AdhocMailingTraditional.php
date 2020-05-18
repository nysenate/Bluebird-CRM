<?php

/**
 * Given the selected contacts, prepare a *traditional* mailing with a hidden group.
 */
class CRM_Mosaico_Form_Task_AdhocMailingTraditional extends CRM_Contact_Form_Task {

  public function preProcess() {
    parent::preProcess();
    $templateTypes = \CRM_Mailing_BAO_Mailing::getTemplateTypes();
    foreach ($templateTypes as $key => $templateType) {
      if ($templateType['name'] == 'traditional') {
        $tradTemplateType = $key;
      }
    }
    list ($groupId) = $this->createHiddenGroup();
    $mailing = civicrm_api3('Mailing', 'create', [
      'name' => "",
      'campaign_id' => NULL,
      'replyto_email' => "",
      'template_type' => $templateTypes[$tradTemplateType]['name'],
      'template_options' => ['nonce' => 1],
      'subject' => "",
      'body_html' => "",
      'body_text' => "",
      'groups' => [
        'include' => [$groupId],
        'exclude' => [],
        'base' => [],
      ],
      'mailings' => [
        'include' => [],
        'exclude' => [],
      ],
    ]);

    CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/a/', NULL, TRUE, '/mailing/' . $mailing['id']));
  }
}
