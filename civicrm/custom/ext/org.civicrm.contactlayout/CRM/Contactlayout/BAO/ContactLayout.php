<?php
use CRM_Contactlayout_ExtensionUtil as E;

class CRM_Contactlayout_BAO_ContactLayout extends CRM_Contactlayout_DAO_ContactLayout {

  /**
   * Fetch the first layout for this type of contact visible to this user.
   *
   * @param int $cid
   *   Id of contact being displayed.
   * @param int $uid
   *   Contact id of current user.
   *
   * @return array|null
   */
  public static function getLayout($cid, $uid = NULL) {
    $uid = $uid ?: CRM_Core_Session::getLoggedInContactID();
    $contact = \Civi\Api4\Contact::get()
      ->addWhere('id', '=', $cid)
      ->setSelect(['contact_type', 'contact_sub_type'])
      ->execute()
      ->first();

    $get = \Civi\Api4\ContactLayout::get()
      ->addSelect('label', 'blocks', 'tabs', 'groups')
      ->addClause('OR', ['contact_type', 'IS NULL'], ['contact_type', '=', $contact['contact_type']])
      ->addOrderBy('weight');

    // Filter by contact sub-type
    $subClauses = [['contact_sub_type', 'IS NULL']];
    if (!empty($contact['contact_sub_type'])) {
      foreach ($contact['contact_sub_type'] as $subType) {
        $subClauses[] = ['contact_sub_type', 'CONTAINS', $subType];
      }
    }
    $get->addClause('OR', $subClauses);

    foreach ($get->execute() as $layout) {
      if (self::checkGroupFilter($uid, $layout)) {
        self::loadBlocks($layout, $contact['contact_type']);
        return $layout;
      }
    }
    return NULL;
  }

  /**
   * Check if the user matches the group filter for a layout
   *
   * @param int $uid
   * @param array $layout
   *
   * @return bool
   */
  private static function checkGroupFilter($uid, $layout) {
    // If no group filter, any user matches
    if (empty($layout['groups'])) {
      return TRUE;
    }
    // Convert group names to ids, and verify groups exist
    $groupIds = (array) civicrm_api4('Group', 'get', [
      'checkPermissions' => FALSE,
      'where' => [['name', 'IN', $layout['groups']]],
    ], ['name' => 'id']);

    // In case groups used by this layout have been deleted
    if (count($groupIds) < count($layout['groups'])) {
      Civi::log()->warning(sprintf('ContactLayout "%s" cannot filter on nonexistent group "%s".',
        $layout['label'],
        implode('" and "', array_diff($layout['groups'], array_keys($groupIds)))
      ));
    }
    if (!$groupIds) {
      // Can't filter if the groups don't exist.
      return TRUE;
    }
    return (bool) \Civi\Api4\Contact::get(FALSE)
      ->addSelect('id')
      ->addWhere('id', '=', $uid)
      ->addWhere('groups', 'IN', $groupIds)
      ->execute()->count();
  }

  /**
   * Merge block data with a saved layout.
   *
   * Filters out missing blocks and blocks not applicable to given contact type.
   *
   * @param array $layout
   * @param string $contactType
   */
  public static function loadBlocks(&$layout, $contactType = NULL) {
    if (!empty($layout['blocks'])) {
      foreach ($layout['blocks'] as &$row) {
        foreach ($row as &$column) {
          foreach ($column as &$block) {
            $blockInfo = self::getBlock($block['name']);
            $relatedRel = isset($block['related_rel']) ? $block['related_rel'] : NULL;
            $isValidBlock = self::checkBlockValidity(
              $blockInfo,
              $relatedRel,
              $contactType
            );

            if ($isValidBlock) {
              $block += $blockInfo;
            }
            // Invalid or missing block
            else {
              $block = FALSE;
            }
          }
          // Remove invalid blocks
          $column = array_filter($column);
        }
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
        $group['blocks'] = array_values($group['blocks']);
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
    $group = self::getAllBlocks()[$groupName] ?? [];
    foreach ($group['blocks'] ?? [] as $block) {
      if ($block['name'] == $fullName) {
        return $block;
      }
    }
    return NULL;
  }

  /**
   * Determines if the block can be displayed for the given contact type.
   *
   * If the block is for a contact's relation then we determine if the given
   * contact type and the relation's contact type match.
   *
   * When the block has no relation we match the block's contact type to the
   * given contact type.
   *
   * @param array $blockInfo
   * @param string $blockRelation
   * @param string $contactType
   * @return bool
   */
  protected static function checkBlockValidity($blockInfo, $blockRelation = NULL, $contactType = NULL) {
    $blockContactType = $blockInfo['contact_type'] ?? [];
    if ($blockRelation) {
      try {
        $relationship = self::getRelationshipFromOption($blockRelation);
      }
      catch (Exception $exception) {
        return FALSE;
      }

      return self::checkBlockRelation($relationship, $contactType, $blockContactType);
    }
    else {
      return $blockInfo && (!$contactType || !$blockContactType || in_array($contactType, $blockContactType, TRUE));
    }
  }

  /**
   * @param array $relationship
   * @param $contactType
   * @param $blockContactType
   * @return bool
   */
  private static function checkBlockRelation(array $relationship, $contactType, array $blockContactType) {
    // Reciprocal relationship - check both directions
    if ($relationship['direction'] === 'r') {
      return self::checkBlockRelation(['direction' => 'ab'] + $relationship, $contactType, $blockContactType) ||
        self::checkBlockRelation(['direction' => 'ba'] + $relationship, $contactType, $blockContactType);
    }
    [$a, $b] = str_split($relationship['direction']);
    return $contactType === $relationship['type']["contact_type_$b"] &&
      ((!$blockContactType && !$relationship['type']["contact_type_$a"]) || in_array($relationship['type']["contact_type_$a"], $blockContactType, TRUE));
  }

  /**
   * Returns the relationship type and direction for the given parameter.
   *
   * The parameter might come in the format `15_ab` where `15` is the relationship
   * type ID, and `ab` is the direction.
   *
   * @throws Exception
   * @param string $relationshipOption
   * @return array
   */
  protected static function getRelationshipFromOption($relationshipOption): array {
    $relationship = explode('_', $relationshipOption);
    $relationshipTypeId = $relationship[0];
    $relationshipType = \Civi\Api4\RelationshipType::get(FALSE)
      ->addWhere('id', '=', $relationshipTypeId)
      ->execute()
      ->first();

    if (!$relationshipType) {
      throw new Exception("Relationship Type not found");
    }

    return [
      'type' => $relationshipType,
      'direction' => $relationship[1],
    ];
  }

  /**
   * Fetch raw block info and invoke hook_civicrm_contactSummaryBlocks.
   *
   * @return array
   */
  protected static function loadAllBlocks() {
    $enabledContactTypes = CRM_Contact_BAO_ContactType::basicTypes();
    $allContactTypes = CRM_Contact_BAO_ContactType::basicTypes(TRUE);
    $blocks = [
      'core' => [
        'title' => E::ts('Predefined'),
        'icon' => 'fa-lock',
        'blocks' => [],
      ],
      'custom' => [
        'title' => E::ts('Custom Field'),
        'icon' => 'fa-gear',
        'blocks' => [],
      ],
      'profile' => [
        'title' => E::ts('Profile'),
        'icon' => 'fa-edit',
        'blocks' => [],
      ],
    ];

    // Core blocks are not editable
    $blocks['core']['blocks']['ContactInfo'] = [
      'title' => E::ts('Employer, Nickname, Source'),
      'tpl_file' => 'CRM/Contact/Page/Inline/ContactInfo.tpl',
      'sample' => [E::ts('Employer'), E::ts('Job Title'), E::ts('Nickame'), E::ts('Source')],
      'edit' => FALSE,
      'selector' => '#crm-contactinfo-content',
      'system_default' => [0, 0],
    ];
    $blocks['core']['blocks']['Basic'] = [
      'title' => E::ts('ID, Type, Tags'),
      'tpl_file' => 'CRM/Contactlayout/Page/Inline/BasicPlusImage.tpl',
      'sample' => [E::ts('Tags'), E::ts('Contact Type'), E::ts('Contact ID'), E::ts('External ID')],
      'edit' => FALSE,
      'system_default' => [0, 1],
    ];
    $blocks['core']['blocks']['Email'] = [
      'title' => E::ts('Email'),
      'tpl_file' => 'CRM/Contact/Page/Inline/Email.tpl',
      'sample' => [E::ts('Home Email'), E::ts('Work Email')],
      'edit' => FALSE,
      'selector' => '#crm-email-content',
      'system_default' => [1, 0],
    ];
    $blocks['core']['blocks']['Phone'] = [
      'title' => E::ts('Phone'),
      'tpl_file' => 'CRM/Contact/Page/Inline/Phone.tpl',
      'sample' => [E::ts('Home Phone'), E::ts('Work Phone')],
      'edit' => FALSE,
      'selector' => '#crm-phone-content',
      'system_default' => [1, 1],
    ];
    $blocks['core']['blocks']['Website'] = [
      'title' => E::ts('Website'),
      'tpl_file' => 'CRM/Contact/Page/Inline/Website.tpl',
      'sample' => [E::ts('Facebook'), E::ts('Linkedin')],
      'edit' => FALSE,
      'selector' => '#crm-website-content',
      'system_default' => [1, 0],
    ];
    $blocks['core']['blocks']['IM'] = [
      'title' => E::ts('Instant Messenger'),
      'tpl_file' => 'CRM/Contact/Page/Inline/IM.tpl',
      'sample' => [E::ts('Yahoo'), E::ts('Skype')],
      'edit' => FALSE,
      'selector' => '#crm-im-content',
      'system_default' => [1, 1],
    ];
    $blocks['core']['blocks']['OpenID'] = [
      'title' => E::ts('Open ID'),
      'tpl_file' => 'CRM/Contact/Page/Inline/OpenID.tpl',
      'sample' => [E::ts('User')],
      'edit' => FALSE,
      'selector' => '#crm-openid-content',
      'contact_type' => ['Individual'],
      'system_default' => [1, 1],
    ];
    $blocks['core']['blocks']['Address'] = [
      'title' => E::ts('Address'),
      'tpl_file' => 'CRM/Contactlayout/Page/Inline/AddressBlocks.tpl',
      'sample' => [E::ts('Home Address'), E::ts('City'), E::ts('State/Province'), E::ts('Postal Code')],
      'multiple' => TRUE,
      'edit' => FALSE,
      'selector' => '.crm-inline-edit.address:not(.add-new)',
      'system_default' => [2, 0],
    ];
    $blocks['core']['blocks']['CommunicationPreferences'] = [
      'title' => E::ts('Communication Preferences'),
      'tpl_file' => 'CRM/Contact/Page/Inline/CommunicationPreferences.tpl',
      'sample' => [E::ts('Privacy'), E::ts('Preferred Method(s)'), E::ts('Email Format'), E::ts('Communication Style'), E::ts('Email Greeting'), E::ts('Postal Greeting'), E::ts('Addressee')],
      'edit' => FALSE,
      'selector' => '#crm-communication-pref-content',
      'system_default' => [3, 0],
    ];
    $blocks['core']['blocks']['Demographics'] = [
      'title' => E::ts('Demographics'),
      'tpl_file' => 'CRM/Contact/Page/Inline/Demographics.tpl',
      'sample' => [E::ts('Gender'), E::ts('Date of Birth'), E::ts('Age')],
      'edit' => FALSE,
      'selector' => '#crm-demographic-content',
      'contact_type' => ['Individual'],
      'system_default' => [3, 1],
    ];

    $profiles = Civi\Api4\UFJoin::get(FALSE)
      ->addSelect('uf_group_id', 'uf_group_id.title', 'uf_group_id.name', 'uf_group_id.group_type')
      ->addSelect('GROUP_CONCAT(fields.field_name) AS field_names')
      ->addSelect('GROUP_CONCAT(fields.label ORDER BY fields.weight) AS field_labels')
      ->addWhere('module', '=', 'Contact Summary')
      ->addWhere('uf_group_id.is_active', '=', TRUE)
      ->addJoin('UFField AS fields', 'LEFT',
        ['uf_group_id', '=', 'fields.uf_group_id'],
        ['fields.is_active', '=', TRUE])
      ->addGroupBy('id')
      ->execute();
    foreach ($profiles as $profile) {
      $profileType = array_intersect($allContactTypes, $profile['uf_group_id.group_type'] ?? []);
      $blocks['profile']['blocks'][$profile['uf_group_id.name']] = [
        'title' => $profile['uf_group_id.title'],
        'tpl_file' => 'CRM/Contactlayout/Page/Inline/Profile.tpl',
        'profile_id' => $profile['uf_group_id'],
        'sample' => $profile['field_labels'],
        'collapsible' => TRUE,
        'edit' => TRUE,
        'refresh' => [],
        'selector' => '#crm-profile-content-' . $profile['uf_group_id.name'],
        'contact_type' => $profileType ?: NULL,
      ];
    }

    $customGroups = \Civi\Api4\CustomGroup::get(FALSE)
      ->addSelect('id', 'name', 'title', 'is_multiple', 'collapse_display', 'extends')
      ->addSelect('GROUP_CONCAT(fields.id) AS field_ids')
      ->addSelect('GROUP_CONCAT(fields.label ORDER BY fields.weight) AS field_labels')
      ->addWhere('extends', 'IN', array_merge(['Contact'], $enabledContactTypes))
      ->addWhere('style', '=', 'Inline')
      ->addWhere('is_active', '=', TRUE)
      ->addJoin('CustomField AS fields', 'LEFT',
        ['id', '=', 'fields.custom_group_id'],
        ['fields.is_active', '=', TRUE])
      ->addGroupBy('id')
      ->addOrderBy('weight')
      ->execute();
    foreach ($customGroups as $index => $group) {
      $blocks['custom']['blocks'][$group['name']] = [
        'title' => $group['title'],
        'tpl_file' => 'CRM/Contactlayout/Page/Inline/CustomFieldSet.tpl',
        'custom_group_id' => $group['id'],
        'sample' => $group['field_labels'],
        'multiple' => !empty($group['is_multiple']),
        'collapsible' => TRUE,
        'collapsed' => !empty($group['collapse_display']),
        'edit' => 'civicrm/admin/custom/group/field?reset=1&action=browse&gid=' . $group['id'],
        'selector' => '#custom-set-content-' . $group['id'],
        'contact_type' => $group['extends'] === 'Contact' ? NULL : [$group['extends']],
        'system_default' => [4, $index % 2],
      ];
    }

    self::addBlockRelations($blocks, $profiles, $customGroups);

    $null = NULL;
    CRM_Utils_Hook::singleton()->invoke(['blocks'], $blocks,
      $null, $null, $null, $null, $null, 'civicrm_contactSummaryBlocks'
    );

    return $blocks;
  }

  /**
   * Maps common fields between profiles and other blocks on the summary screen.
   *
   * @param $blocks
   * @param $profiles
   * @param $customGroups
   */
  public static function addBlockRelations(&$blocks, $profiles, $customGroups) {
    $customFields = [];
    foreach ($customGroups as $group) {
      if (!empty($group['field_ids'])) {
        $customFields['#custom-set-content-' . $group['id']] = $group['field_ids'];
      }
    }
    $coreBlocks = [
      '#crm-contactname-content' => [
        'first_name',
        'middle_name',
        'last_name',
        'nick_name',
        'organization_name',
        'household_name',
        'formal_title',
        'individual_prefix',
        'individual_suffix',
        'deceased',
      ],
      '#crm-communication-pref-content' => [
        'do_not',
        'language',
        'is_opt_out',
        'preferred_communication_method',
        'greeting',
        'addressee',
      ],
      '#crm-contactinfo-content' => [
        'employer',
        'job_title',
        'nick_name',
        'source',
      ],
      '#crm-demographic-content' => [
        'gender',
        'deceased',
        'birth_date',
      ],
      '#crm-email-content' => [
        'email',
      ],
      '#crm-phone-content' => [
        'phone',
      ],
      '#crm-website-content' => [
        'url',
      ],
    ];
    foreach ($profiles as $profile) {
      $block =& $blocks['profile']['blocks'][$profile['uf_group_id.name']];
      foreach ($profile['field_names'] as $fieldName) {
        $fieldName = strtolower($fieldName);
        if (strpos($fieldName, 'custom_') === 0) {
          list(, $customId) = explode('_', $fieldName);
          foreach ($customFields as $selector => $fields) {
            if (in_array($customId, $fields) && !in_array($selector, $block['refresh'])) {
              $block['refresh'][] = $selector;
            }
          }
        }
        else {
          foreach ($coreBlocks as $selector => $fields) {
            foreach ($fields as $field) {
              if (!in_array($selector, $block['refresh']) && strpos($fieldName, $field) !== FALSE) {
                $block['refresh'][] = $selector;
                break;
              }
            }
          }
        }
      }
    }
  }

  /**
   * @return array
   */
  public static function getAllTabs() {
    $enabledContactTypes = CRM_Contact_BAO_ContactType::basicTypes();
    $tabs = CRM_Contact_Page_View_Summary::basicTabs();
    foreach (CRM_Core_Component::getEnabledComponents() as $component) {
      $tab = $component->registerTab();
      if ($tab) {
        $tabs[] = $tab + ['id' => $component->getKeyword(), 'icon' => $component->getIcon()];
      }
    }
    $weight = 200;
    $customGroups = \Civi\Api4\CustomGroup::get()
      ->addWhere('style', 'IN', ['Tab', 'Tab with table'])
      ->addWhere('is_active', '=', 1)
      ->addWhere('extends', 'IN', array_merge(['Contact'], $enabledContactTypes))
      ->addOrderBy('weight')
      ->execute();
    foreach ($customGroups as $group) {
      $tabs[] = [
        'id' => "custom_{$group['id']}",
        'title' => $group['title'],
        'weight' => $weight += 10,
        'icon' => 'crm-i ' . ($group['icon'] ?? 'fa-gear'),
        'contact_type' => $group['extends'] == 'Contact' ? NULL : [$group['extends']],
      ];
    }
    // Call the hook for extensions to add tabs
    $context = [
      'contact_id' => 0,
      'contact_type' => NULL,
      'caller' => 'ContactLayout',
    ];
    CRM_Utils_Hook::tabset('civicrm/contact/view', $tabs, $context);

    if (method_exists(\CRM_Core_Smarty::class, 'setRequiredTabTemplateKeys')) {
      // use new core helper to avoid smarty notices
      $tabs = \CRM_Core_Smarty::setRequiredTabTemplateKeys($tabs);
    }

    $keyedTabs = [];

    foreach ($tabs as $key => $tab) {
      // TODO why `is_active` here and `active` elsewhere?
      $tab['is_active'] = $tab['is_active'] ?? TRUE;

      // Ensure array keys match tab.id if provided
      // (The contact template used to use tab.id but now uses the array key)
      $finalKey = $tab['id'] ?? $key;
      $keyedTabs[$finalKey] = $tab;
    }
    uasort($keyedTabs, ['CRM_Utils_Sort', 'cmpFunc']);
    return $keyedTabs;
  }

}
