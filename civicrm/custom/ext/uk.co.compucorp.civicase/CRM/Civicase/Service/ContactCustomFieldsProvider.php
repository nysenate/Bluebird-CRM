<?php

/**
 * Provides contacts custom fields.
 */
class CRM_Civicase_Service_ContactCustomFieldsProvider {

  /**
   * Provides contacts custom fields.
   *
   * @return array
   *   List of custom fields that extends contacts.
   */
  public function get() {
    $fields = [];
    $customFields = $this->getCustomFields();
    if (!empty($customFields['values'])) {
      foreach ($customFields['values'] as $id => $item) {
        $fields['custom_' . $id] = $item['name'];
      }
    }

    return $fields;
  }

  /**
   * Provides contacts custom fields.
   *
   * @return array
   *   List of custom fields that extends contacts.
   */
  public function getCustomFields() {
    $customFields = [];
    try {
      $customFields = civicrm_api3('CustomField', 'get', [
        'custom_group_id.extends' => [
          'IN' => ['Contact', 'Individual', 'Household', 'Organization'],
        ],
        'options' => ['limit' => 0],
      ]);
    }
    catch (Throwable $ex) {
    }

    return $customFields;
  }

}
