<?php

/**
 * AddCaseCategoryWordReplacementOptionGroup class.
 */
class CRM_Civicase_Setup_AddCaseCategoryWordReplacementOptionGroup {

  /**
   * Create Case Type Category word replacement option group.
   *
   * Each option value in the option group will provide the word replacement
   * class that can be used to retrieve the key value pairs fof words that
   * need to be replaced and the replacement word.
   */
  public function apply() {
    CRM_Core_BAO_OptionGroup::ensureOptionGroupExists([
      'name' => 'case_type_category_word_replacement_class',
      'title' => ts('Case Type Category Word Replacements'),
      'is_reserved' => 1,
    ]);
  }

}
