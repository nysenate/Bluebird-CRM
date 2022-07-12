<?php

use CRM_NYSS_Contact_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_NYSS_Contact_Form_DeleteTrashed extends CRM_Core_Form {
  public function buildQuickForm() {
    Civi::resources()->addScriptFile(E::LONG_NAME, 'js/DeleteTrashed.js');
    Civi::resources()->addStyleFile(E::LONG_NAME, 'css/DeleteTrashed.css');
    Civi::resources()->addVars('NYSS', [
      'processUrl' => CRM_Utils_System::url('civicrm/nyss/processtrashed', 'reset=1'),
    ]);

    $sql = "
      SELECT COUNT(id)
      FROM civicrm_contact
      WHERE is_deleted = 1;
    ";
    $count = CRM_Core_DAO::singleValueQuery($sql);

    $this->assign('trashCount', $count);
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
