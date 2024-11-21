<?php

class CRM_NYSS_QuickSearchAPIWrapper {

  public static function RESPOND($event) {
    $request = $event->getApiRequestSig();
    //Civi::log()->debug(__FUNCTION__, ['$request' => $request]);

    switch ($request) {
      case '4.contact.autocomplete':
        $apiRequest = $event->getApiRequest();
        $result = $event->getResponse();

        /*Civi::log()->debug(__FUNCTION__, [
          //'apiRequest' => $apiRequest,
          'result' => $result,
        ]);*/

        if (!empty($caseId = $apiRequest->filters['case_id'])) {
          //Civi::log()->debug(__FUNCTION__, ['$caseId' => $caseId]);

          $cases = \Civi\Api4\CiviCase::get(FALSE)
            ->addSelect('id', 'subject', 'contact.id', 'contact.sort_name', 'address.street_address', 'address.city', 'address.postal_code', 'email.email', 'phone.phone')
            ->addJoin('CaseContact AS case_contact', 'INNER')
            ->addJoin('Contact AS contact', 'INNER', 'CaseContact')
            ->addJoin('Address AS address', 'LEFT', ['contact.id', '=', 'address.contact_id'], ['address.is_primary', '=', TRUE])
            ->addJoin('Email AS email', 'LEFT', ['contact.id', '=', 'email.contact_id'], ['email.is_primary', '=', TRUE])
            ->addJoin('Phone AS phone', 'LEFT', ['contact.id', '=', 'phone.contact_id'], ['phone.is_primary', '=', TRUE])
            ->addWhere('id', '=', $caseId)
            ->setLimit(15)
            ->addGroupBy('id')
            ->addGroupBy('contact.id')
            ->addGroupBy('address.id')
            ->addGroupBy('email.id')
            ->addGroupBy('phone.id')
            ->execute();

          foreach ($cases as &$case) {
            $case['label'] = "{$case['contact.sort_name']} [Case #{$case['id']}: {$case['subject']}]";
            $case['id'] = $case['contact.id'].'|'.$case['id'];
            $case['description'] = [
              $case['email.email'],
              $case['phone.phone'],
              $case['address.street_address'],
              $case['address.city'],
              $case['address.postal_code'],
            ];
          }

          $event->setResponse($cases);
        }

        break;
    }
  }
}
