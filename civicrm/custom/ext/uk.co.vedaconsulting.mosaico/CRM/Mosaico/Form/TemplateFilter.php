<?php

use CRM_Mosaico_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Mosaico_Form_TemplateFilter extends CRM_Core_Form {

  public function buildQuickForm() {
    $this->add('text', 'title', ts('Template Title'));
    $this->add('select', 'category_id', ts('Category'), \CRM_Core_OptionGroup::values('mailing_template_category'), FALSE, ['class' => 'crm-select2', 'multiple' => TRUE, 'placeholder' => ts('- select one or more category -')]);

    $this->assign('suppressForm', TRUE);
  }

}
