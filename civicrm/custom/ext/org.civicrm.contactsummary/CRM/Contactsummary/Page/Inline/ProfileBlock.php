<?php
use CRM_Contactsummary_ExtensionUtil as E;

class CRM_Contactsummary_Page_Inline_ProfileBlock extends CRM_Core_Page {

  public function run() {
    $contactId = CRM_Utils_Request::retrieveValue('cid', 'Positive', NULL, TRUE);
    $profileId = CRM_Utils_Request::retrieveValue('gid', 'Positive', NULL, TRUE);

    $this->assign('contactId', $contactId);
    $this->assign('profileBlock', self::getProfileBlock($profileId, $contactId));
    $this->assign('block', ['profile_id' => $profileId]);

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
    $fields = CRM_Core_BAO_UFGroup::getFields($profileId, FALSE, CRM_Core_Action::VIEW);
    foreach ($fields as $name => $field) {
      // eliminate all formatting fields
      if (CRM_Utils_Array::value('field_type', $field) == 'Formatting') {
        unset($fields[$name]);
      }
    }
    CRM_Core_BAO_UFGroup::getValues($contactId, $fields, $values, FALSE);
    $result = [];
    foreach ($fields as $fieldName => $field) {
      $result[] = [
        'name' => $fieldName,
        'value' => CRM_Utils_Array::value($field['title'], $values),
        'label' => $field['title'],
      ];
    }
    return $result;
  }

}
