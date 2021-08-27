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

    $layout = \Civi\Api4\ContactLayout::get()
      ->setLimit(1)
      ->addSelect('label', 'blocks', 'tabs')
      ->addClause('OR', ['contact_type', 'IS NULL'], ['contact_type', '=', $contact['contact_type']])
      ->addOrderBy('weight');

    // Filter by contact sub-type
    $subClauses = [['contact_sub_type', 'IS NULL']];
    if (!empty($contact['contact_sub_type'])) {
      foreach ($contact['contact_sub_type'] as $subType) {
        $subClauses[] = ['contact_sub_type', 'CONTAINS', $subType];
      }
    }
    $layout->addClause('OR', $subClauses);

    // Filter by user group
    $groupClause = [['groups', 'IS NULL']];
    $groups = \CRM_Contact_BAO_GroupContact::getContactGroup($uid, 'Added', NULL, FALSE, TRUE, FALSE, TRUE, NULL, TRUE);
    if (!empty($groups)) {
      $groups = \Civi\Api4\Group::get()
        ->addSelect('name')
        ->addWhere('id', 'IN', array_column($groups, 'group_id'))
        ->execute();
      foreach ($groups as $group) {
        $groupClause[] = ['groups', 'CONTAINS', $group['name']];
      }
    }
    $layout->addClause('OR', $groupClause);
    $layout = $layout->execute()->first();
    self::loadBlocks($layout, $contact['contact_type']);
    return $layout;
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
    $blockContactType = $blockInfo['contact_type'] ?? NULL;
    if ($blockRelation) {
      try {
        $relationship = self::getRelationshipFromOption($blockRelation);
      }
      catch (Exception $exception) {
        return FALSE;
      }

      return ($relationship['direction'] === 'r' && (
          ($contactType === $relationship['type']['contact_type_a'] &&
            $blockContactType === $relationship['type']['contact_type_b']) ||
          ($contactType === $relationship['type']['contact_type_b'] &&
            $blockContactType === $relationship['type']['contact_type_a']))) ||
        ($relationship['direction'] === 'ab' && (
          $blockContactType === $relationship['type']['contact_type_a'] &&
          $contactType === $relationship['type']['contact_type_b'])) ||
        ($relationship['direction'] === 'ba' && (
          $blockContactType === $relationship['type']['contact_type_b'] &&
          $contactType === $relationship['type']['contact_type_a']));
    }
    else {
      return $blockInfo && (!$contactType || !$blockContactType || $contactType == $blockContactType);
    }
  }

  /**
   * Returns the relationship type and direction for the given parameter.
   *
   * The parameter might come in the format `15_ab` where `15` is the relationship
   * type ID, and `ab` is the direction.
   *
   * @param string $relationshipOption
   * @return array
   */
  protected static function getRelationshipFromOption($relationshipOption) {
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
      'contact_type' => 'Individual',
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
      'contact_type' => 'Individual',
      'system_default' => [3, 1],
    ];

    $profiles = Civi\Api4\UFJoin::get(FALSE)
      ->setSelect(['uf_group_id', 'uf_group.title', 'uf_group.name', 'uf_group.group_type'])
      ->addWhere('module', '=', 'Contact Summary')
      ->addChain('fields', \Civi\Api4\UFField::get()
        ->setSelect(['label', 'field_name'])
        ->addOrderBy('weight')
        ->setWhere([['is_active', '=', 1], ['uf_group_id', '=', '$uf_group_id']]))
      ->execute();
    foreach ($profiles as $profile) {
      $profileType = array_intersect(CRM_Contact_BAO_ContactType::basicTypes(TRUE), $profile['uf_group.group_type']);
      $blocks['profile']['blocks'][$profile['uf_group.name']] = [
        'title' => $profile['uf_group.title'],
        'tpl_file' => 'CRM/Contactlayout/Page/Inline/Profile.tpl',
        'profile_id' => $profile['uf_group_id'],
        'sample' => array_column($profile['fields'], 'label'),
        'collapsible' => TRUE,
        'edit' => TRUE,
        'refresh' => [],
        'selector' => '#crm-profile-content-' . $profile['uf_group.name'],
        'contact_type' => CRM_Utils_Array::first($profileType),
      ];
    }

    $customGroups = \Civi\Api4\CustomGroup::get(FALSE)
      ->addWhere('extends', 'IN', ['Contact', 'Individual', 'Household', 'Organization'])
      ->addWhere('style', '=', 'Inline')
      ->addWhere('is_active', '=', 1)
      ->addOrderBy('weight')
      ->addChain('fields', \Civi\Api4\CustomField::get()
        ->addSelect('label')
        ->addOrderBy('weight')
        ->setWhere([['is_active', '=', 1], ['custom_group_id', '=', '$id']]))
      ->execute();
    foreach ($customGroups as $index => $group) {
      $blocks['custom']['blocks'][$group['name']] = [
        'title' => $group['title'],
        'tpl_file' => 'CRM/Contactlayout/Page/Inline/CustomFieldSet.tpl',
        'custom_group_id' => $group['id'],
        'sample' => array_column($group['fields'], 'label'),
        'multiple' => !empty($group['is_multiple']),
        'collapsible' => TRUE,
        'collapsed' => !empty($group['collapse_display']),
        'edit' => 'civicrm/admin/custom/group/field?reset=1&action=browse&gid=' . $group['id'],
        'selector' => '#custom-set-content-' . $group['id'],
        'contact_type' => $group['extends'] === 'Contact' ? NULL : $group['extends'],
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
      $customFields['#custom-set-content-' . $group['id']] = array_column($group['fields'], 'id');
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
      $block =& $blocks['profile']['blocks'][$profile['uf_group.name']];
      foreach ($profile['fields'] as $field) {
        $fieldName = strtolower($field['field_name']);
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
            if (!in_array($selector, $block['refresh'])) {
              foreach ($fields as $field) {
                if (strpos($fieldName, $field) !== FALSE) {
                  $block['refresh'][] = $selector;
                  break;
                }
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
    $tabs = CRM_Contact_Page_View_Summary::basicTabs();
    foreach (CRM_Core_Component::getEnabledComponents() as $name => $component) {
      $tab = $component->registerTab();
      if ($tab) {
        $tabs[] = $tab + ['id' => $component->getKeyword(), 'icon' => $component->getIcon()];
      }
    }
    $weight = 200;
    $customGroups = \Civi\Api4\CustomGroup::get()
      ->addWhere('style', 'IN', ['Tab', 'Tab with table'])
      ->addWhere('is_active', '=', 1)
      ->addWhere('extends', 'IN', ['Contact', 'Individual', 'Household', 'Organization'])
      ->addOrderBy('weight', 'ASC')
      ->execute();
    foreach ($customGroups as $group) {
      $tabs[] = [
        'id' => "custom_{$group['id']}",
        'title' => $group['title'],
        'weight' => $weight += 10,
        'icon' => 'crm-i ' . ($group['icon'] ?? 'fa-gear'),
        'contact_type' => $group['extends'] == 'Contact' ? NULL : $group['extends'],
      ];
    }
    $context = [
      'contact_id' => CRM_Core_Session::getLoggedInContactID(),
      'caller' => 'ContactLayout',
    ];
    CRM_Utils_Hook::tabset('civicrm/contact/view', $tabs, $context);
    foreach ($tabs as &$tab) {
      // Hack for CiviDiscount
      if ($tab['id'] === 'discounts') {
        $tabs[] = array(
          'id' => 'discounts_assigned',
          'title' => ts('Codes Assigned', ['domain' => 'org.civicrm.module.cividiscount']),
          'weight' => 115,
          'icon' => $tab['icon'],
          'contact_type' => 'Organization',
          'is_active' => TRUE,
        );
      }
      $tab['is_active'] = TRUE;
    }
    usort($tabs, ['CRM_Utils_Sort', 'cmpFunc']);
    return $tabs;
  }

}
