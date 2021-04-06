<?php

/**
 * Attaches a new token tree to the form.
 */
class CRM_Civicase_Hook_BuildForm_TokenTree {

  const CASE_ROLE_TOKEN_TEXT = 'Case Roles';

  const CURRENT_USER_TOKEN_TEXT = 'Current User';

  const CASE_TOKEN_TEXT = 'Case';

  const CONTACT_TOKEN_TEXT = 'Contact';

  const RECIPIENT_TOKEN_TEXT = 'Email Recipient';

  const OTHER_TOKEN_TEXT = 'Other';

  const ADDRESS_TOKEN_TEXT = 'Address';

  const CUSTOM_FIELDS_TEXT = 'Custom Fields';

  const CORE_FIELDS_TEXT = 'Core Fields';

  /**
   * All case and contact related custom fields.
   *
   * @var array
   */
  private $customFields = [];

  /**
   * All case roles token name.
   *
   * @var array
   */
  private $caseRolesTokenNames = [];

  /**
   * Is form email.
   *
   * @var bool
   */
  private $isEmailForm = FALSE;

  /**
   * Attaches a new token tree to the form.
   *
   * @param CRM_Core_Form $form
   *   Form object class.
   * @param string $formName
   *   Form name.
   */
  public function run(CRM_Core_Form &$form, $formName) {
    if (!$this->shouldRun($formName)) {
      return;
    }
    $this->isEmailForm = $form instanceof CRM_Contact_Form_Task_Email;
    $this->setAllRelevantCustomFields();
    $this->attachNewTokenTreeToForm($form);
  }

  /**
   * Attaches a new token tree to the form.
   *
   * @param CRM_Core_Form $form
   *   Form object class.
   */
  private function attachNewTokenTreeToForm(CRM_Core_Form &$form) {
    $tokens = $form->get_template_vars('tokens');
    $newTokenTree = [];
    foreach ($tokens as $key => $tokenList) {
      if (empty($tokenList['text'])) {
        continue;
      }
      if ($tokenList['text'] === self::CASE_ROLE_TOKEN_TEXT) {
        $this->addCaseRoleTokens($tokenList['children'], $newTokenTree);
      }
      elseif ($tokenList['text'] === self::CURRENT_USER_TOKEN_TEXT) {
        $this->addCurrentUserTokens($tokenList['children'], $newTokenTree);
      }
      elseif ($tokenList['text'] === self::CASE_TOKEN_TEXT) {
        $this->addCaseTokens($tokenList['children'], $newTokenTree);
      }
      elseif (in_array(
        $tokenList['text'],
        [self::CONTACT_TOKEN_TEXT, self::ADDRESS_TOKEN_TEXT]
        )) {
        if ($this->isEmailForm) {
          $this->addClientTokens($tokenList['children'], $newTokenTree);
        }
      }
      else {
        $this->addOtherTokens($tokenList['children'], $newTokenTree);
      }
    }
    $this->reFormatCustomTokens($newTokenTree);
    $newTokenTree = $this->reOrderTokens($newTokenTree);
    $this->addTokensToJs($newTokenTree);
  }

  /**
   * Reorder the main tokens categories array in a specified order.
   *
   * @param array $newTokenTree
   *   Restructured token tree.
   *
   * @return array
   *   Reordered token tree.
   */
  private function reOrderTokens(array $newTokenTree) {
    $reorderedTree = [];
    $caseClientRole = 'Case Client';
    $tokenTexts = [
      0 => self::CASE_TOKEN_TEXT,
      2 => self::RECIPIENT_TOKEN_TEXT,
      3 => self::CURRENT_USER_TOKEN_TEXT,
    ];
    if (in_array($caseClientRole, $this->caseRolesTokenNames)) {
      $this->caseRolesTokenNames = array_diff(
        $this->caseRolesTokenNames,
        [$caseClientRole]
      );
      $tokenTexts[1] = $caseClientRole;
    }
    ksort($tokenTexts);
    $tokenTexts = array_merge($tokenTexts, $this->caseRolesTokenNames);
    $tokenTexts[] = self::OTHER_TOKEN_TEXT;
    foreach ($tokenTexts as $tokenText) {
      if (!empty($newTokenTree[$tokenText])) {
        $reorderedTree[$tokenText] = $newTokenTree[$tokenText];
      }
    }

    return $reorderedTree;
  }

  /**
   * Reformat the custom fields.
   *
   * @param array $newTokenTree
   *   Restructured token tree.
   */
  private function reFormatCustomTokens(array &$newTokenTree) {
    $tokenTexts = [
      self::CURRENT_USER_TOKEN_TEXT,
      self::CASE_TOKEN_TEXT,
    ];
    if ($this->isEmailForm) {
      $tokenTexts[] = self::RECIPIENT_TOKEN_TEXT;
    }
    foreach ($tokenTexts as $tokenText) {
      for ($i = 0; $i < 2; $i++) {
        if (!empty($newTokenTree[$tokenText]['children'][$i]['children'])) {
          $newTokenTree[$tokenText]['children'][$i]['children']
            = array_values($newTokenTree[$tokenText]['children'][$i]['children']);
          if ($newTokenTree[$tokenText]['children'][$i]['text'] === self::CUSTOM_FIELDS_TEXT) {
            usort(
              $newTokenTree[$tokenText]['children'][$i]['children'],
              [$this, 'sortCustomFields']
            );
          }
        }
      }
    }
  }

  /**
   * Add new token tree to the js.
   *
   * @param array $newTokenTree
   *   Restructured token tree.
   */
  private function addTokensToJs(array &$newTokenTree) {
    CRM_Core_Resources::singleton()
      ->addScriptFile('uk.co.compucorp.civicase', 'js/token-tree.js')
      ->addSetting([
        'civicase-base' => [
          'custom_token_tree' => json_encode(array_values($newTokenTree)),
        ],
      ]);
  }

  /**
   * Fetch all contact and case related custom fields.
   */
  private function setAllRelevantCustomFields() {
    $customFields = [];
    try {
      $customFields = civicrm_api3('CustomField', 'get', [
        'custom_group_id.extends' => [
          'IN' => ['Contact', 'Individual', 'Household', 'Organization', 'Case'],
        ],
        'options' => ['limit' => 0, 'sort' => "custom_group_id.weight ASC"],
        'sequential' => 1,
        'return' => ['id', 'custom_group_id.title'],
      ]);
    }
    catch (Throwable $ex) {
    }
    if (!empty($customFields) && $customFields['is_error'] === 0) {
      foreach ($customFields['values'] as $field) {
        $this->customFields[$field['id']] = $field['custom_group_id.title'];
      }
    }
  }

  /**
   * Add case role tokens to the new token tree.
   *
   * @param array $caseRoleTokens
   *   Array of case role tokens.
   * @param array $newTokenTree
   *   Restructured token tree.
   */
  private function addCaseRoleTokens(array $caseRoleTokens, array &$newTokenTree) {
    $contactRoleTokens = [];
    foreach ($caseRoleTokens as $key => $token) {
      if ($token['id'] === '{case_roles.client}') {
        continue;
      }
      $roleName = explode('-', $token['text']);
      $roleName = $roleName[0];
      $tokenName = trim($roleName);
      if (empty($contactRoleTokens[$tokenName])) {
        $this->caseRolesTokenNames[] = $tokenName;
        $this->initializeTokenType($contactRoleTokens, $tokenName);
      }
      if (strpos($token['id'], '_custom_') !== FALSE) {
        $this->addCustomTokens($contactRoleTokens, $tokenName, $token);
      }
      else {
        if (empty($contactRoleTokens[$tokenName]['children'][0])) {
          $this->initializeTokenTypeCoreField($contactRoleTokens, $tokenName, $token);
        }
        else {
          $contactRoleTokens[$tokenName]['children'][0]['children'][] = $token;
        }
      }
    }
    $this->processAndAddCaseRoleTokens($contactRoleTokens, $newTokenTree);
  }

  /**
   * Sort custom fields.
   *
   * @param array $a
   *   Array of custom fields.
   * @param array $b
   *   Array of custom fields.
   *
   * @return int
   *   sorting direction.
   */
  private function sortCustomFields(array $a, array $b) {
    $firstKey = array_search($a['text'], $this->customFields);
    $secondKey = array_search($b['text'], $this->customFields);

    return $firstKey <=> $secondKey;
  }

  /**
   * Process and add case role tokens to the new token tree.
   *
   * @param array $contactRoleTokens
   *   Array of case contact role tokens.
   * @param array $newTokenTree
   *   Restructured token tree.
   */
  private function processAndAddCaseRoleTokens(array $contactRoleTokens, array &$newTokenTree) {
    ksort($contactRoleTokens, SORT_NATURAL);
    foreach ($contactRoleTokens as $key => $caseRoleToken) {
      if (!empty($caseRoleToken['children'][1]['children'])) {
        usort(
          $caseRoleToken['children'][1]['children'],
          [$this, 'sortCustomFields']
        );
      }
      $caseRoleToken['children'] = array_values($caseRoleToken['children']);
      if (!empty($caseRoleToken['children'][1]['children'])) {
        $caseRoleToken['children'][1]['children']
          = array_values($caseRoleToken['children'][1]['children']);
      }
      if (!empty($caseRoleToken['children'][0]['children'])) {
        $caseRoleToken['children'][0]['children']
          = array_values($caseRoleToken['children'][0]['children']);
      }
      $newTokenTree[$key] = $caseRoleToken;
    }
  }

  /**
   * Add case tokens to the new token tree.
   *
   * @param array $caseTokens
   *   Array of case tokens.
   * @param array $newTokenTree
   *   Restructured token tree.
   */
  private function addCaseTokens(array $caseTokens, array &$newTokenTree) {
    $newTokenTree[self::CASE_TOKEN_TEXT] = [
      'id' => self::CASE_TOKEN_TEXT,
      'text' => self::CASE_TOKEN_TEXT,
      'children' => [
        [
          'id' => 'CoreFields' . uniqid(),
          'text' => self::CORE_FIELDS_TEXT,
          'children' => $caseTokens,
        ],
      ],
    ];
  }

  /**
   * Add client tokens to the new token tree.
   *
   * @param array $clientTokens
   *   Array of client tokens.
   * @param array $newTokenTree
   *   Restructured token tree.
   */
  private function addClientTokens(array $clientTokens, array &$newTokenTree) {
    if (empty($newTokenTree[self::RECIPIENT_TOKEN_TEXT])) {
      $newTokenTree[self::RECIPIENT_TOKEN_TEXT] = [
        'id' => $this->clean(self::RECIPIENT_TOKEN_TEXT) . uniqid(),
        'text' => self::RECIPIENT_TOKEN_TEXT,
        'children' => [
          [
            'id' => 'CoreFields' . uniqid(),
            'text' => self::CORE_FIELDS_TEXT,
            'children' => $clientTokens,
          ],
        ],
      ];
    }
    else {
      $newTokenTree[self::RECIPIENT_TOKEN_TEXT]['children'][0]['children'] =
        array_merge($newTokenTree[self::RECIPIENT_TOKEN_TEXT]['children'][0]['children'], $clientTokens);
    }
  }

  /**
   * Add current user tokens to the new token tree.
   *
   * @param array $currentUserTokens
   *   Array of current user tokens.
   * @param array $newTokenTree
   *   Restructured token tree.
   */
  private function addCurrentUserTokens(array $currentUserTokens, array &$newTokenTree) {
    $this->initializeTokenType($newTokenTree, self::CURRENT_USER_TOKEN_TEXT);
    foreach ($currentUserTokens as $key => $token) {
      if (strpos($token['id'], '_custom_') !== FALSE) {
        $this->addCustomTokens($newTokenTree, self::CURRENT_USER_TOKEN_TEXT, $token);
      }
      else {
        if (empty($newTokenTree[self::CURRENT_USER_TOKEN_TEXT]['children'][0])) {
          $this->initializeTokenTypeCoreField($newTokenTree, self::CURRENT_USER_TOKEN_TEXT, $token);
        }
        else {
          $newTokenTree[self::CURRENT_USER_TOKEN_TEXT]['children'][0]['children'][] = $token;
        }
      }
    }
    $newTokenTree[self::CURRENT_USER_TOKEN_TEXT]['children'] =
      array_values($newTokenTree[self::CURRENT_USER_TOKEN_TEXT]['children']);
  }

  /**
   * Add all remaining tokens to the new token tree.
   *
   * @param array $otherTokens
   *   Array of tokens.
   * @param array $newTokenTree
   *   Restructured token tree.
   */
  private function addOtherTokens(array $otherTokens, array &$newTokenTree) {
    foreach ($otherTokens as $key => $token) {
      if ($this->isEmailForm && strpos($token['id'], 'contact.custom_') !== FALSE) {
        $this->addCustomTokens($newTokenTree, self::RECIPIENT_TOKEN_TEXT, $token);
      }
      elseif (strpos($token['id'], 'case.custom_') !== FALSE) {
        $this->addCustomTokens($newTokenTree, self::CASE_TOKEN_TEXT, $token);
      }
      else {
        if (!empty($newTokenTree[self::OTHER_TOKEN_TEXT])) {
          $newTokenTree[self::OTHER_TOKEN_TEXT]['children'][] = $token;
        }
        else {
          $newTokenTree[self::OTHER_TOKEN_TEXT] = [
            'id' => self::OTHER_TOKEN_TEXT,
            'text' => self::OTHER_TOKEN_TEXT,
            'children' => [$token],
          ];
        }

      }
    }
  }

  /**
   * Add custom tokens to a particular list.
   *
   * @param array $newTokenTree
   *   Restructured token tree.
   * @param string $label
   *   Label for the tokens.
   * @param array $token
   *   Token that is to be added.
   */
  private function addCustomTokens(array &$newTokenTree, $label, array $token) {
    $separatedId = explode('_', $token['id']);
    $customFieldId = $separatedId[1] ? rtrim($separatedId[count($separatedId) - 1], '}') : NULL;
    $customFieldLabel = $this->customFields[$customFieldId] ?? '';
    if (!empty($newTokenTree[$label]['children'][1])) {
      if (!empty($newTokenTree[$label]['children'][1]['children'][$customFieldLabel])) {
        $newTokenTree[$label]['children'][1]['children'][$customFieldLabel]['children'][] = $token;
      }
      else {
        $newTokenTree[$label]['children'][1]['children'][$customFieldLabel] = [
          'id' => $this->clean($customFieldLabel) . uniqid(),
          'text' => $customFieldLabel,
          'children' => [$token],
        ];
      }
    }
    else {
      $newTokenTree[$label]['children'][1] = [
        'id' => 'CustomFields' . uniqid(),
        'text' => self::CUSTOM_FIELDS_TEXT,
        'children' => [
          $customFieldLabel =>
            [
              'id' => $this->clean($customFieldLabel) . uniqid(),
              'text' => $customFieldLabel,
              'children' => [$token],
            ],
        ],
      ];
    }
  }

  /**
   * Determines if the hook will run.
   *
   * This hook is only valid for the email and pdf case forms.
   *
   * @param string $formName
   *   Form name.
   *
   * @return bool
   *   Determines if the hook will run.
   */
  private function shouldRun($formName) {
    return CRM_Utils_Request::retrieve('caseid', 'Integer') &&
      in_array(
        $formName,
        [CRM_Contact_Form_Task_Email::class, CRM_Contact_Form_Task_PDF::class]
      );
  }

  /**
   * Removes special characters from a string.
   *
   * @param string $string
   *   String from which special characters should be removed.
   *
   * @return string
   *   Formatted string.
   */
  private function clean($string) {
    $string = str_replace(' ', '-', $string);

    return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
  }

  /**
   * Initialize a sub token tree.
   *
   * @param array $newTokenTree
   *   Restructured token tree.
   * @param string $tokenName
   *   Token name.
   */
  private function initializeTokenType(array &$newTokenTree, $tokenName) {
    $newTokenTree[$tokenName] = [
      'id' => $this->clean($tokenName) . uniqid(),
      'text' => $tokenName,
      'children' => [],
    ];
  }

  /**
   * Initialize a core field token tree and assign the first token.
   *
   * @param array $newTokenTree
   *   Restructured token tree.
   * @param string $tokenName
   *   Token name.
   * @param array $token
   *   Token to be added.
   */
  private function initializeTokenTypeCoreField(array &$newTokenTree, $tokenName, array $token) {
    $newTokenTree[$tokenName]['children'][0] = [
      'id' => 'CoreFields' . uniqid(),
      'text' => self::CORE_FIELDS_TEXT,
      'children' => [$token],
    ];
  }

}
