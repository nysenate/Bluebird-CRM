<?php

/**
 * Add current user token values.
 */
class CRM_Civicase_Hook_Tokens_AddContactTokensValues {

  /**
   * Service for fetching contact fields.
   *
   * @var CRM_Civicase_Service_ContactFieldsProvider
   */
  private $contactFieldsService;

  /**
   * Service for fetching contact custom fields.
   *
   * @var CRM_Civicase_Service_ContactCustomFieldsProvider
   */
  private $contactCustomFieldsService;

  /**
   * CRM_Civicase_Hook_Tokens_AddContactTokens constructor.
   *
   * @param CRM_Civicase_Service_ContactFieldsProvider $contactFieldsService
   *   Service for fetching contact fields.
   * @param CRM_Civicase_Service_ContactCustomFieldsProvider $contactCustomFieldsService
   *   Service for fetching contact custom fields.
   */
  public function __construct(
    CRM_Civicase_Service_ContactFieldsProvider $contactFieldsService,
    CRM_Civicase_Service_ContactCustomFieldsProvider $contactCustomFieldsService) {
    $this->contactFieldsService = $contactFieldsService;
    $this->contactCustomFieldsService = $contactCustomFieldsService;
  }

  /**
   * Add current user token values.
   *
   * @param array $values
   *   Token values.
   * @param array $cids
   *   Contact ids.
   * @param int $job
   *   Job id.
   * @param array $tokens
   *   Token names that are used actually.
   * @param string $context
   *   Context name.
   */
  public function run(array &$values, array $cids, $job, array $tokens, $context) {
    if (!$this->shouldRun($tokens)) {
      return;
    }
    $contactFields = $this->contactFieldsService->get();
    $customFields = $this->contactCustomFieldsService->get();
    $allFields = array_merge($contactFields, array_keys($customFields));
    try {
      $contactValues = $this->getContactValues($allFields);
      $currentUsersContact = [];
      foreach ($contactValues as $fieldName => $value) {
        if (strpos($fieldName, 'civicrm_value_') !== FALSE) {
          continue;
        }
        if (in_array($fieldName, $allFields)) {
          $key = 'current_user.contact_' . $fieldName;
          $currentUsersContact[$key] = $value;
        }
      }

      foreach ($cids as $cid) {
        $values[$cid] = empty($values[$cid]) ? $currentUsersContact : array_merge($values[$cid], $currentUsersContact);
      }
    }
    catch (Throwable $ex) {
    }
  }

  /**
   * Returns contact entity and custom field values.
   *
   * @param array $fields
   *   List of fields to fetch.
   *
   * @return array
   *   Contact field values.
   */
  public function getContactValues(array $fields) {
    $contactId = CRM_Core_Session::singleton()->getLoggedInContactID();
    $values = [];
    try {
      $values = civicrm_api3('contact', 'getsingle', [
        'id' => $contactId,
        'return' => $fields,
      ]);
    }
    catch (Throwable $ex) {
    }

    return $values;
  }

  /**
   * Decides whether the hook should run or not.
   *
   * @param array $tokens
   *   List of tokens that are used.
   *
   * @return bool
   *   Whether this hook should run or not.
   */
  private function shouldRun(array $tokens) {
    return !empty($tokens[CRM_Civicase_Hook_Tokens_AddContactTokens::TOKEN_KEY]);
  }

}
