<?php
use CRM_Contactsummary_ExtensionUtil as E;

class CRM_Contactsummary_BAO_ContactSummary extends CRM_Contactsummary_DAO_ContactSummary {

  /**
   * Fetch the right layout
   *
   * @param int $cid
   *   Id of contact being displayed.
   * @param int $uid
   *   Contact id of current user.
   *
   * @return array|null
   */
  public static function getLayout($cid, $uid = NULL) {
    $uid = $uid ?: \CRM_Core_Session::getLoggedInContactID();
    $contact = \Civi\Api4\Contact::get()
      ->addWhere('id', '=', $cid)
      ->setSelect(['contact_type', 'contact_sub_type'])
      ->execute()
      ->first();
    $groups = \CRM_Contact_BAO_GroupContact::getContactGroup($uid, 'Added', NULL, FALSE, TRUE, FALSE, TRUE, NULL, TRUE);
    $groupIds = array_column($groups, 'group_id');
    $layout = \Civi\Api4\ContactSummary::get()
      ->setLimit(1)
      ->addSelect('blocks')
      ->addClause('OR', ['contact_type', 'IS NULL'], ['contact_type', '=', $contact['contact_type']])
      ->addOrderBy('weight');
    if (!empty($contact['contact_sub_type'])) {
      $subClauses = [['contact_sub_type', 'IS NULL']];
      foreach ($contact['contact_sub_type'] as $subType) {
        $subClauses[] = ['contact_sub_type', 'LIKE', '%' . CRM_Core_DAO::VALUE_SEPARATOR . $subType . CRM_Core_DAO::VALUE_SEPARATOR . '%'];
      }
      $layout->addClause('OR', $subClauses);
    }
    if (!empty($groups)) {
      $groups = \Civi\Api4\Group::get()
        ->addSelect('name')
        ->addWhere('id', 'IN', $groupIds)
        ->execute();
      $subClauses = [['groups', 'IS NULL']];
      foreach ($groups as $group) {
        $subClauses[] = ['groups', 'LIKE', '%' . CRM_Core_DAO::VALUE_SEPARATOR . $group['name'] . CRM_Core_DAO::VALUE_SEPARATOR . '%'];
      }
      $layout->addClause('OR', $subClauses);
    }
    $layout = CRM_Utils_Array::value('blocks', $layout->execute()->first());
    self::loadLayout($layout);
    return $layout;
  }

  /**
   * Merge block data with a saved layout.
   *
   * @param $layout
   */
  public static function loadLayout(&$layout) {
    if ($layout) {
      foreach ($layout as &$column) {
        foreach ($column as &$block) {
          $blockInfo = self::getBlock($block['name']);
          if ($blockInfo) {
            $block += $blockInfo;
          }
          // If this block is missing, invalidate it
          else {
            $block = FALSE;
          }
        }
        // Remove invalid blocks
        $column = array_filter($column);
      }
    }
  }

  /**
   * @return array
   */
  public static function getAllBlocks() {
    if (!isset(\Civi::$statics[__CLASS__]['blocks'])) {
      \Civi::$statics[__CLASS__]['blocks'] = self::loadAllBlocks();
      foreach (\Civi::$statics[__CLASS__]['blocks'] as $groupName => &$group) {
        $group['name'] = $groupName;
        foreach ($group['blocks'] as $blockName => &$block) {
          $block['name'] = "$groupName.$blockName";
        }
      }
    }
    return \Civi::$statics[__CLASS__]['blocks'];
  }

  /**
   * Fetches a block based on its full name.
   *
   * @param string $fullName
   *
   * @return null|array
   */
  public static function getBlock($fullName) {
    list($groupName, $blockName) = explode('.', $fullName, 2);
    $group = CRM_Utils_Array::value($groupName, self::getAllBlocks());
    return isset($group['blocks'][$blockName]) ? $group['blocks'][$blockName] : NULL;
  }

  /**
   * Fetch raw block info and invoke hook_civicrm_contactSummaryBlocks.
   *
   * @return array
   */
  protected static function loadAllBlocks() {
    $blocks = [
      'core' => [
        'title' => ts('Predefined Blocks'),
        'icon' => 'fa-lock',
        'blocks' => [],
      ],
      'custom' => [
        'title' => ts('Custom Field Sets'),
        'icon' => 'fa-gear',
        'blocks' => [],
      ],
      'profile' => [
        'title' => ts('Profiles'),
        'icon' => 'fa-edit',
        'blocks' => [],
      ],
    ];

    // Core blocks are not editable
    $blocks['core']['blocks']['ContactInfo'] = [
      'title' => ts('Contact Info'),
      'tpl_file' => 'CRM/Contact/Page/Inline/ContactInfo.tpl',
      'sample' => [ts('Employer'), ts('Job Title'), ts('Nickame'), ts('Source')],
      'edit' => FALSE,
    ];
    $blocks['core']['blocks']['Demographics'] = [
      'title' => ts('Demographics'),
      'tpl_file' => 'CRM/Contact/Page/Inline/Demographics.tpl',
      'sample' => [ts('Gender'), ts('Date of Birth'), ts('Age')],
      'edit' => FALSE,
    ];
    $blocks['core']['blocks']['CommunicationPreferences'] = [
      'title' => ts('Communication Preferences'),
      'tpl_file' => 'CRM/Contact/Page/Inline/CommunicationPreferences.tpl',
      'sample' => [ts('Privacy'), ts('Preferred Method(s)'), ts('Email Format'), ts('Communication Style'), ts('Email Greeting'), ts('Postal Greeting'), ts('Addressee')],
      'edit' => FALSE,
    ];
    $blocks['core']['blocks']['Address'] = [
      'title' => ts('Address'),
      'tpl_file' => 'CRM/Contactsummary/Page/Inline/AddressBlocks.tpl',
      'sample' => [ts('Home Address'), ts('City'), ts('State/Province'), ts('Postal Code')],
      'multiple' => TRUE,
      'edit' => FALSE,
    ];
    $blocks['core']['blocks']['Phone'] = [
      'title' => ts('Phone'),
      'tpl_file' => 'CRM/Contact/Page/Inline/Phone.tpl',
      'sample' => [ts('Home Phone'), ts('Work Phone')],
      'edit' => FALSE,
    ];
    $blocks['core']['blocks']['Email'] = [
      'title' => ts('Email'),
      'tpl_file' => 'CRM/Contact/Page/Inline/Email.tpl',
      'sample' => [ts('Home Email'), ts('Work Email')],
      'edit' => FALSE,
    ];
    $blocks['core']['blocks']['IM'] = [
      'title' => ts('Instant Messenger'),
      'tpl_file' => 'CRM/Contact/Page/Inline/IM.tpl',
      'sample' => [ts('Yahoo'), ts('Skype')],
      'edit' => FALSE,
    ];
    $blocks['core']['blocks']['OpenID'] = [
      'title' => ts('Open ID'),
      'tpl_file' => 'CRM/Contact/Page/Inline/OpenID.tpl',
      'sample' => [ts('User')],
      'edit' => FALSE,
    ];
    $blocks['core']['blocks']['Website'] = [
      'title' => ts('Website'),
      'tpl_file' => 'CRM/Contact/Page/Inline/Website.tpl',
      'sample' => [ts('Facebook'), ts('Linkedin')],
      'edit' => FALSE,
    ];

    $profiles = civicrm_api3('UFJoin', 'get', [
      'return' => ['uf_group_id.title', 'uf_group_id.name', 'uf_group_id'],
      'options' => ['limit' => 0],
      'module' => 'Contact Summary',
      'api.UFField.get' => [
        'return' => 'label',
        'is_active' => 1,
        'uf_group_id' => '$value.uf_group_id',
        'options' => ['limit' => 10, 'sort' => 'weight'],
      ],
    ]);
    foreach ($profiles['values'] as $profile) {
      $blocks['profile']['blocks'][$profile['uf_group_id.name']] = [
        'title' => $profile['uf_group_id.title'],
        'tpl_file' => 'CRM/Contactsummary/Page/Inline/Profile.tpl',
        'profile_id' => $profile['uf_group_id'],
        'sample' => CRM_Utils_Array::collect('label', $profile['api.UFField.get']['values']),
        'edit' => TRUE,
      ];
    }

    $customGroups = civicrm_api3('CustomGroup', 'get', [
      'extends' => ['IN' => ['Contact', 'Individual', 'Household', 'Organization']],
      'style' => 'Inline',
      'is_active' => 1,
      'options' => ['limit' => 0, 'sort' => 'weight'],
      'api.CustomField.get' => [
        'return' => ['label'],
        'is_active' => 1,
        'options' => ['limit' => 10, 'sort' => 'weight'],
      ],
    ]);
    foreach ($customGroups['values'] as $groupId => $group) {
      $blocks['custom']['blocks'][$group['name']] = [
        'title' => $group['title'],
        'tpl_file' => 'CRM/Contactsummary/Page/Inline/CustomFieldSet.tpl',
        'custom_group_id' => $groupId,
        'sample' => CRM_Utils_Array::collect('label', $group['api.CustomField.get']['values']),
        'multiple' => !empty($group['is_multiple']),
        'edit' => 'civicrm/admin/custom/group/field?reset=1&action=browse&gid=' . $groupId,
      ];
    }

    $null = NULL;
    CRM_Utils_Hook::singleton()->invoke(['blocks'], $blocks,
      $null, $null, $null, $null, $null, 'civicrm_contactSummaryBlocks'
    );

    return $blocks;
  }

}
