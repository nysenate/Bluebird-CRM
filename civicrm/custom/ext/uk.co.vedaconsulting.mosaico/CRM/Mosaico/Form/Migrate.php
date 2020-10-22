<?php

use CRM_Mosaico_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Mosaico_Form_Migrate extends CRM_Core_Form {

  public function buildQuickForm() {
    $this->loadTemplateData();

    $migrateComment = E::ts('Are you sure want to copy data from Mosaico 1.x to 2.x?') . '\n' . E::ts('This action cannot be easily undone.');
    $purgeComment = E::ts('Are you sure want to purge Mosaico 1.x data?') . '\n' . E::ts('This action cannot be undone.');

    $buttons = [];
    $buttons[] = [
      'type' => 'submit',
      'name' => ts('Copy'),
      'subName' => 'migrate',
      'isDefault' => TRUE,
      'icon' => 'fa-copy',
      'js' => ['onclick' => 'return confirm(\'' . $migrateComment . '\');'],
    ];
    $buttons[] = [
      'type' => 'submit',
      'name' => ts('Purge'),
      'subName' => 'purge',
      'icon' => 'fa-trash',
      'isDefault' => FALSE,
      'js' => ['onclick' => 'return confirm(\'' . $purgeComment . '\');'],
    ];
    $this->addButtons($buttons);

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();
    if (!empty($values['_qf_Migrate_submit_migrate'])) {
      $apiResult = civicrm_api3('Job', 'mosaico_migrate', [
        'check_permissions' => 1,
      ]);
      CRM_Core_Session::setStatus(E::ts('Copied %1 templates from Mosaico 1.x to 2.x.', [
        1 => $apiResult['count'],
      ]), '', 'success', [
        'expires' => 0,
      ]);
    }
    elseif (!empty($values['_qf_Migrate_submit_purge'])) {
      civicrm_api3('Job', 'mosaico_purge', [
        'check_permissions' => 1,
      ]);
      CRM_Core_Session::setStatus(E::ts('Purged invisible Mosaico 1.x data.', []), '', 'success', [
        'expires' => 0,
      ]);
    }
    else {
      CRM_Core_Session::setStatus(E::ts('Unrecognized action'));
    }

    $this->loadTemplateData();
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
    $elementNames = [];
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

  protected function loadTemplateData() {
    $oldTemplates = CRM_Core_DAO::executeQuery('SELECT id, name, msg_tpl_id FROM civicrm_mosaico_msg_template')
      ->fetchAll();
    $newTemplates = CRM_Core_DAO::executeQuery('SELECT id, title, msg_tpl_id FROM civicrm_mosaico_template')
      ->fetchAll();
    $this->assign('oldTemplates', $oldTemplates);
    $this->assign('newTemplates', $newTemplates);

    $msgTplIds = array_filter(CRM_Utils_Array::collect('msg_tpl_id', $newTemplates), 'is_numeric');
    sort($msgTplIds);
    $uniqueIds = array_unique($msgTplIds);
    $this->assign('msgTplWarning', count($msgTplIds) > count($uniqueIds));
  }

}
