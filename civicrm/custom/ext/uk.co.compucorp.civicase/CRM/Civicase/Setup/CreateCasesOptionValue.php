<?php

use CRM_Core_BAO_SchemaHandler as SchemaHandler;

class CRM_Civicase_Setup_CreateCasesOptionValue {

  public function apply() {
    CRM_Core_BAO_OptionValue::ensureOptionValueExists([
      'option_group_id' => 'case_type_categories',
      'name' => 'Cases',
      'label' => 'Cases',
      'is_default' => TRUE,
      'is_active' => TRUE,
      'is_reserved' => TRUE,
    ]);

    return TRUE;
  }

}
