<?php

/**
 * Provides contact fields.
 */
class CRM_Civicase_Service_ContactFieldsProvider {

  /**
   * Provides contact fields.
   *
   * @return array
   *   List of contact fields.
   */
  public function get() {
    $fields = [];
    try {
      $contactId = CRM_Core_Session::singleton()->getLoggedInContactID();
      $contact = civicrm_api3('contact', 'getsingle', [
        'id' => $contactId,
      ]);
      if ($contact) {
        $fields = array_keys($contact);
      }
    }
    catch (Throwable $ex) {
    }

    return $fields;
  }

}
