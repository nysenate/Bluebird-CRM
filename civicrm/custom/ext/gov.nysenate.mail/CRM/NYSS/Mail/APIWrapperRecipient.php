<?php

class CRM_NYSS_Mail_APIWrapperRecipient {
  public static function autocompleteDefault($event) {
    $hookValues = $event->getHookValues();
    //Civi::log()->debug(__METHOD__, ['$hookValues' => $hookValues]);

    //16724 - adjust how we retrieve recipient list
    if (in_array($hookValues[2], ['Mailing.recipients_include', 'Mailing.recipients_exclude'])) {
      $hookValues[0]['api_params']['sets'][0][3]['where'][1] = [
        'OR',
        [
          ['group_type:name', 'CONTAINS', 'Mailing List'],
          ['group_type:name', 'CONTAINS', 'Email List'],
        ],
      ];
    }
  }
}
