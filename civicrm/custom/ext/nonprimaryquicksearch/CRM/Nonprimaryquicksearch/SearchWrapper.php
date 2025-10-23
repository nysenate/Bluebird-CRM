<?php

/**
 * Class CRM_Nonprimaryquicksearch_SearchWrapper
 */
class CRM_Nonprimaryquicksearch_SearchWrapper implements API_Wrapper {

  /**
   * @inheritDoc
   */
  public function fromApiInput($apiRequest) {
    if ($apiRequest['entity'] === 'SearchDisplay') {
      $apiRequest = $this->searchOnNonPrimary($apiRequest);
    }
    return $apiRequest;
  }

  /**
   * @inheritDoc
   */
  public function toApiOutput($apiRequest, $result) {
    if ($apiRequest['entity'] === 'Contact') {
      $result = $this->showAllContactInfo($result);
    }

    return $result;
  }

  private function searchOnNonPrimary($apiRequest) {
    // We have to set the whole saved search, so we pull a copy here.
    $newSearch = $apiRequest->getSavedSearch();
    $newParams = $newSearch['api_params'];
    // Add joins to the params.
    $newParams['join'] = [
      ['Email AS email', 'LEFT'],
      ['Address AS address', 'LEFT'],
      ['Phone AS phone', 'LEFT'],
    ];
    // NYSS #17544
    $newParams['groupBy'] = [
      'id', 'email.email', 'phone.phone_numeric', 'address.street_address',
      'address.city', 'address.postal_code'
    ];
    // Change the SELECT fields from primary to non-primary versions.
    foreach ($newParams['select'] as $key => $selected) {
      if (str_contains($selected, '_primary.')) {
        $newParams['select'][$key] = str_replace('_primary', '', $selected);
      }
    }
    $newSearch['api_params'] = $newParams;

    // Add search by email if on name/email
    $newFilters = $apiRequest->getFilters();
    if (count($newFilters) === 1 && isset($newFilters['sort_name']) && \Civi::settings()->get('includeEmailInName')) {
      $newFilters['email.email,sort_name'] = $newFilters['sort_name'];
      unset($newFilters['sort_name']);
    }
    // Change the search's filters from primary to non-primary versions.
    foreach ($newFilters as $field => $filterTerm) {
      if (str_contains($field, '_primary.')) {
        unset($newFilters[$field]);
        $newField = str_replace('_primary', '', $field);
        $newFilters[$newField] = $filterTerm;
      }
    }
    $apiRequest->setFilters($newFilters);
    $apiRequest->setSavedSearch($newSearch);
    return $apiRequest;
  }

  private function showAllContactInfo($result) {
    // Create a map of results to contact IDs so we can add more info in.
    $contactMap = array_combine(array_column((array) $result, 'id'), array_keys((array) $result));
    $allContactsInfo = \Civi\Api4\Contact::get(TRUE)
      ->addSelect('id', 'email.email', 'phone.phone', 'address.street_address', 'address.city', 'address.state_province_id:abbr', 'address.country_id:abbr')
      ->addJoin('Address AS address', 'LEFT')
      ->addJoin('Email AS email', 'LEFT')
      ->addJoin('Phone AS phone', 'LEFT')
      ->addWhere('id', 'IN', array_keys($contactMap))
      ->execute();
    $collectedContactInfo = [];
    // The left joins create a bunch of duplicate contact info, so we flip the array key/value to auto-dedupe.  Then flip it back at the end.
    foreach ($allContactsInfo as $contactInfo) {
      // The array index of this contact in the search results.
      $arrayIndex = $contactMap[$contactInfo['id']];
      $collectedContactInfo[$arrayIndex]['email'][] = $contactInfo['email.email'];
      $collectedContactInfo[$arrayIndex]['address'][] = $this->buildAddress($contactInfo);
      $collectedContactInfo[$arrayIndex]['phone'][] = $contactInfo['phone.phone'];
    }
    // remove duplicates
    foreach ($collectedContactInfo as $arrayIndex => $undeduped) {
      $collectedContactInfo[$arrayIndex]['email'] = array_unique($undeduped['email']);
      $collectedContactInfo[$arrayIndex]['address'] = array_unique($undeduped['address']);
      $collectedContactInfo[$arrayIndex]['phone'] = array_unique($undeduped['phone']);
    }
    // Now add the collected contact info onto the contact description.
    foreach ($collectedContactInfo as $key => $replacementInfo) {
      foreach ($replacementInfo as $replacementKey => $finalContactInfo) {
        $replacementInfo[$replacementKey] = implode(' ~ ', array_filter($finalContactInfo));
      }
      // Put everything together.
      $result[$key]['description'][] = implode(' :: ', array_filter($replacementInfo));
    }
    return $result;
  }

  private function buildAddress($contactInfo) : string {
    $address = '';
    if ($contactInfo['address.street_address']) {
      $address .= $contactInfo['address.street_address'] . ', ';
    }
    if ($contactInfo['address.city']) {
      $address .= $contactInfo['address.city'] . ', ';
    }
    if ($contactInfo['address.state_province_id:abbr']) {
      $address .= $contactInfo['address.state_province_id:abbr'] . ', ';
    }
    if ($contactInfo['address.country_id:abbr']) {
      $address .= $contactInfo['address.country_id:abbr'] . ', ';
    }
    // remove trailing comma
    $address = substr($address, 0, strrpos($address, ', '));
    return $address;
  }

}
