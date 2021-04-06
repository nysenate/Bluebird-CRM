<?php

/**
 * Add current user tokens.
 */
class CRM_Civicase_Hook_Tokens_AddContactTokens {

  /**
   * Key for token.
   *
   * @var string
   */
  const TOKEN_KEY = 'current_user';

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
   * Add current user tokens.
   *
   * @param array $tokens
   *   List of tokens.
   */
  public function run(array &$tokens) {
    foreach ($this->contactFieldsService->get() as $field) {
      $tokens[self::TOKEN_KEY]['current_user.contact_' . $field] =
        ts('Current User ' . ucwords(str_replace("_", " ", $field)));
    }
    foreach ($this->contactCustomFieldsService->get() as $key => $field) {
      $tokens[self::TOKEN_KEY]['current_user.contact_' . $key] =
        ts('Current User ' . ucwords(str_replace("_", " ", $field)));
    }
  }

}
