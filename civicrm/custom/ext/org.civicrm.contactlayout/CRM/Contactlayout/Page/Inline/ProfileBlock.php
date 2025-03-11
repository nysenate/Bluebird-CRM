<?php
use CRM_Contactlayout_ExtensionUtil as E;

class CRM_Contactlayout_Page_Inline_ProfileBlock extends CRM_Core_Page {

  public function run() {
    $viewedContactId = CRM_Utils_Request::retrieveValue('cid', 'Positive', NULL, TRUE);
    $profileId = CRM_Utils_Request::retrieveValue('gid', 'Positive', NULL, TRUE);
    $relatedContactId = CRM_Utils_Request::retrieveValue('rel_cid', 'Positive', NULL, FALSE);
    $contactId = $relatedContactId ? $relatedContactId : $viewedContactId;

    $this->assign('contactId', $contactId);
    $this->assign('profileBlock', self::getProfileBlock($profileId, $contactId));

    $allBlocks = CRM_Contactlayout_BAO_ContactLayout::getAllBlocks();
    foreach ($allBlocks['profile']['blocks'] as $block) {
      if ($block['profile_id'] == $profileId) {
        $this->assign('block', $block);
      }
    }

    // Needed to display image
    if ($image_URL = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact', $contactId, 'image_URL')) {
      $this->assign("imageURL", CRM_Utils_File::getImageURL($image_URL));
    }

    // Needed to display tags
    $this->assign('contactTag', CRM_Core_BAO_EntityTag::getContactTags($contactId));
    $this->assign('allTags', CRM_Core_BAO_Tag::getTagsUsedFor('civicrm_contact', FALSE));

    CRM_Contact_Page_View::checkUserPermission($this, $contactId);
    parent::run();
  }

  /**
   * @param int $profileId
   * @param int $contactId
   * @return array
   */
  public static function getProfileBlock($profileId, $contactId) {
    $values = [];
    $fields = CRM_Core_BAO_UFGroup::getFields($profileId, FALSE, CRM_Core_Action::VIEW, NULL, NULL, FALSE, NULL, TRUE);
    foreach ($fields as $name => $field) {
      // Eliminate all formatting fields
      if (($field['field_type'] ?? NULL) === 'Formatting') {
        unset($fields[$name]);
      }
    }
    CRM_Core_BAO_UFGroup::getValues($contactId, $fields, $values, FALSE);
    $result = $details = [];
    // Load details if we need them for special field handling
    if (array_intersect(['birth_date', 'deceased_date', 'is_deceased'], array_keys($fields))) {
      $params = ['id' => $contactId];
      CRM_Contact_BAO_Contact::getValues($params, $details);
    }
    foreach ($fields as $name => $field) {
      // Special handling for group field (profiles only show public groups by default)
      if ($name == 'group') {
        $groups = array_column(CRM_Contact_BAO_GroupContact::getContactGroup($contactId, 'Added'), 'title');
        $values[$field['title']] = implode(', ', $groups);
      }
      // Special handling for employer field - show multiple
      if ($name == 'current_employer') {
        $employers = [];
        foreach (CRM_Contactlayout_Form_Inline_ProfileBlock::getEmployers($contactId) as $employer) {
          $org = $employer['display_name'];
          if (CRM_Contact_BAO_Contact_Permission::allow($employer['contact_id'])) {
            $org = '<a href="' . CRM_Utils_System::url('civicrm/contact/view', ['reset' => 1, 'cid' => $employer['contact_id']]) . '" title="' . htmlspecialchars(E::ts('view employer')) . '">' . $org . '</a>';
          }
          $employers[] = $org;
        }
        $values[$field['title']] = implode(', ', $employers);
      }
      // Special handling for note field - show 3 recent notes
      if ($name == 'note') {
        $notes = self::getNotes($contactId, $field);
        if ($notes) {
          $result = array_merge($result, $notes);
          continue;
        }
      }
      // Hide deceased fields if not applicable
      if ($name == 'deceased_date' && empty($details['deceased_date'])) {
        continue;
      }
      // Hide the pseudo field phone extension that is included in phone
      if (str_starts_with($name, 'phone_ext')) {
        continue;
      }
      // Show Is Deceased message if no deceased date
      if ($name == 'is_deceased') {
        if ((!isset($fields['deceased_date']) || empty($details['deceased_date'])) && !empty($details['is_deceased'])) {
          $result[] = [
            'name' => $name,
            'value' => '<span class="font-red upper">' . htmlspecialchars(ts('Contact is Deceased')) . '</span>',
            'label' => $field['title'],
          ];
        }
        continue;
      }
      $result[] = [
        'name' => $name,
        'value' => $values[$field['title']] ?? NULL,
        'label' => $field['title'],
      ];
      // Birth date - show age
      if ($name == 'birth_date' && !empty($details['age']) && empty($details['is_deceased'])) {
        $result[] = [
          'name' => 'age',
          'label' => ts('Age'),
          'value' => htmlspecialchars($details['age']['y'] ? ts('%count year', ['count' => $details['age']['y'], 'plural' => '%count years']) : ts('%count month', ['count' => $details['age']['m'], 'plural' => '%count months'])),
        ];
      }
    }
    return $result;
  }

  /**
   * @param $contactId
   * @param $field
   * @return array
   * @throws \CRM_Core_Exception
   */
  public static function getNotes($contactId, $field) {
    $result = [];
    $notes = Civi\Api4\Note::get()
      ->addWhere('entity_id', '=', $contactId)
      ->addWhere('entity_table', '=', 'civicrm_contact')
      ->setSelect(['note', 'subject', 'modified_date'])
      ->addOrderBy('modified_date', 'DESC')
      ->addOrderBy('id', 'DESC')
      ->setLimit(3)
      ->setCheckPermissions(FALSE)
      ->execute();
    if (count($notes)) {
      $dateFormat = Civi::Settings()->get('dateformatshortdate');
      foreach ($notes as $i => $note) {
        $result[] = [
          'name' => "note",
          'value' => (empty($note['subject']) ? '' : '<strong>' . htmlspecialchars($note['subject']) . '</strong><br />') . $note['note'],
          'label' => $field['title'] . ' (' . CRM_Utils_Date::customFormat($note['modified_date'], $dateFormat) . ')',
        ];
      }
    }
    return $result;
  }

}
