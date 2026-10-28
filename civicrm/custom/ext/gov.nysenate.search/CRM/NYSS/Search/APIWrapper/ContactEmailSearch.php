<?php
require_once 'api/Wrapper.php';

/**
 * Lets a contact EntityRef field search by email address, without turning on
 * the site-wide includeEmailInName setting (which is deliberately off because
 * email matches swamp ordinary name searches).
 * @see NYSS #4271
 *
 * Opt a field in by adding ['api' => [self::PARAM_ENABLED => 1]] to addEntityRef().
 * When the typed input contains "@", the name search is replaced by a prefix
 * match against all of a contact's email addresses (primary or not). Input
 * without "@" is left alone, so name searches behave exactly as before.
 *
 * Each result also lists the address(es) that matched, unless the description
 * already shows them (e.g. a primary email match).
 */
#[CRM_NYSS_Attribute_IssueRef('17663')]
class CRM_NYSS_Search_APIWrapper_ContactEmailSearch implements API_Wrapper {

  const PARAM_ENABLED = 'nyss_email_search';

  /**
   * Request param carrying matched emails (keyed by contact ID) from
   * fromApiInput() to toApiOutput(). It can't live on the wrapper object:
   * core re-runs hook_civicrm_apiWrappers for the output phase, so each
   * phase gets a fresh instance.
   */
  const PARAM_MATCHES = 'nyss_email_search_matches';

  public static function applies($apiRequest) {
    return $apiRequest['entity'] == 'Contact' &&
      $apiRequest['action'] == 'getlist' &&
      !empty($apiRequest['params'][self::PARAM_ENABLED]);
  }

  public function fromApiInput($apiRequest) {
    // Only we may set this; never trust it from the caller.
    unset($apiRequest['params'][self::PARAM_MATCHES]);

    $input = trim($apiRequest['params']['input'] ?? '');
    if (!str_contains($input, '@')) {
      return $apiRequest;
    }

    // Escape LIKE wildcards so "john_smith@" doesn't match "johnXsmith@".
    $like = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $input) . '%';
    $emails = \Civi\Api4\Email::get(FALSE)
      ->addSelect('contact_id', 'email')
      ->addWhere('email', 'LIKE', $like)
      // Contact.get skips its trash filter when given explicit IDs
      ->addWhere('contact_id.is_deleted', '=', FALSE)
      ->execute();

    $matches = [];
    foreach ($emails as $email) {
      $matches[$email['contact_id']][] = $email['email'];
    }
    $apiRequest['params'][self::PARAM_MATCHES] = $matches;

    // Contact.get still applies ACLs, paging and any "refine search"
    // filters; we only swap the name match for an ID list.
    $apiRequest['params']['params']['id'] = ['IN' => array_keys($matches) ?: [0]];
    $apiRequest['params']['input'] = '';
    return $apiRequest;
  }

  public function toApiOutput($apiRequest, $result) {
    $matches = $apiRequest['params'][self::PARAM_MATCHES] ?? [];
    if (empty($matches) || empty($result['values'])) {
      return $result;
    }

    foreach ($result['values'] as &$row) {
      $description = $row['description'] ?? [];
      $unshown = array_diff(array_unique($matches[$row['id']] ?? []), $description);
      $row['description'] = array_merge($unshown, $description);
    }
    unset($row);

    return $result;
  }

}
