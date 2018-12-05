<?php
use CRM_Contactlayout_ExtensionUtil as E;

class CRM_Contactlayout_Page_Inline_ProfileBlock extends CRM_Core_Page {

  public function run() {
    $contactId = CRM_Utils_Request::retrieveValue('cid', 'Positive', NULL, TRUE);
    $profileId = CRM_Utils_Request::retrieveValue('gid', 'Positive', NULL, TRUE);

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
      // eliminate all formatting fields
      if (CRM_Utils_Array::value('field_type', $field) == 'Formatting') {
        unset($fields[$name]);
      }
    }
    CRM_Core_BAO_UFGroup::getValues($contactId, $fields, $values, FALSE);
    $result = [];
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
            $org = '<a href="' . CRM_Utils_System::url('civicrm/contact/view', ['reset' => 1, 'cid' => $employer['contact_id']]) . '" title="' . E::ts('view employer') . '">' . $org . '</a>';
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
      $result[] = [
        'name' => $name,
        'value' => CRM_Utils_Array::value($field['title'], $values),
        'label' => $field['title'],
      ];
    }
    return $result;
  }

  /**
   * @param $contactId
   * @param $field
   * @return array
   * @throws \API_Exception
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
          'value' => (empty($note['subject']) ? '' : '<strong>' . $note['subject'] . '</strong><br />') . $note['note'],
          'label' => $field['title'] . ' (' . CRM_Utils_Date::customFormat($note['modified_date'], $dateFormat) . ')',
        ];
      }
    }
    return $result;
  }

}
