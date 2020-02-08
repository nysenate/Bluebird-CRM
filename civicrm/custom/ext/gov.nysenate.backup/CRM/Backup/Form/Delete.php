<?php

use CRM_Backup_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Backup_Form_Delete extends CRM_Core_Form {
  public function buildQuickForm() {
    $fileName = CRM_Utils_Request::retrieve('file', 'String');

    if (empty($fileName)) {
      CRM_Core_Error::statusBounce('You may only access this page by selecting a backup file to delete.',
        CRM_Utils_System::url('civicrm/backup/listing', 'reset=1'));
    }

    $this->add('hidden', 'fileName', $fileName);
    $this->assign('fileName', $fileName);

    $this->addButtons([
      [
        'type' => 'submit',
        'name' => E::ts('Delete Backup'),
        'isDefault' => TRUE,
      ],
      [
        'type' => 'cancel',
        'name' => E::ts('Cancel'),
      ]
    ]);

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();
    //Civi::log()->debug(__FUNCTION__, ['$values' => $values]);

    $response = CRM_Backup_BAO::delete($values['fileName']);

    if ($response) {
      $msg = "File deleted successfully ({$values['fileName']}).";
      $type = 'success';
    }
    else {
      $msg = "Unable to delete file ({$values['fileName']}).";
      $type = 'error';
    }

    CRM_Core_Session::setStatus(E::ts($msg), 'Delete File', $type);

    parent::postProcess();
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
