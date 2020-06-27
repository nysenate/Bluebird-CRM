<?php

use CRM_Civicase_Helper_CaseCategory as CaseCategoryHelper;
use CRM_Case_BAO_CaseType as CaseType;

/**
 * Class CRM_Civicase_Service_CaseCategorySetting.
 */
class CRM_Civicase_Service_CaseCategorySetting {

  /**
   * Returns the settings set for all case categories.
   *
   * @return array
   *   Array of webform settings.
   */
  public function getForWebform() {
    $caseTypeCategories = CaseType::buildOptions('case_type_category', 'validate');
    $caseCategorySettings = [];
    foreach ($caseTypeCategories as $caseCategoryName) {
      $caseCategorySettings = array_merge($caseCategorySettings, $this->getCaseWebformSetting($caseCategoryName));
    }

    return $caseCategorySettings;
  }

  /**
   * Returns the case category webform settings.
   *
   * @param string $caseCategoryName
   *   Case category name.
   *
   * @return array
   *   Case webform setting for category.
   */
  public function getCaseWebformSetting($caseCategoryName) {
    return [
      str_replace(' ', '', $this->replaceWords('civicaseAllowCaseWebform', $caseCategoryName)) => [
        'group_name' => 'CiviCRM Preferences',
        'group' => 'core',
        'name' => str_replace(' ', '', $this->replaceWords('civicaseAllowCaseWebform', $caseCategoryName)),
        'type' => 'Boolean',
        'quick_form_type' => 'YesNo',
        'html_attributes' => [
          'data-case-category-name' => $caseCategoryName,
          'class' => 'civicase__settings__allow-webform',
        ],
        'default' => FALSE,
        'html_type' => 'radio',
        'add' => '4.7',
        'title' => $this->replaceWords('Trigger webform on Add Case', $caseCategoryName),
        'is_domain' => 1,
        'is_contact' => 0,
        'description' => $this->replaceWords('This setting allows the user to set a webform to be triggered when clicking the "Add Case" button on the Cases tab on the Contact.', $caseCategoryName),
        'help_text' => '',
        'is_webform_url' => FALSE,
        'webform_url_name' => str_replace(' ', '', $this->replaceWords('civicaseWebformUrl', $caseCategoryName)),
      ],
      str_replace(' ', '', $this->replaceWords('civicaseWebformUrl', $caseCategoryName)) => [
        'group_name' => 'CiviCRM Preferences',
        'group' => 'core',
        'name' => str_replace(' ', '', $this->replaceWords('civicaseWebformUrl', $caseCategoryName)),
        'type' => 'String',
        'quick_form_type' => 'Element',
        'html_attributes' => [
          'data-case-category-name' => $caseCategoryName,
          'class' => 'civicase__settings__webform-url',
          'size' => 64,
          'maxlength' => 64,
        ],
        'html_type' => 'text',
        'default' => '',
        'add' => '4.7',
        'title' => ' URL of the Webform',
        'is_domain' => 1,
        'is_contact' => 0,
        'description' => 'A Webform url e.g /node/233',
        'help_text' => '',
        'is_webform_url' => TRUE,
      ],
    ];
  }

  /**
   * Returns the appropriate string after proper word replacements.
   *
   * @param string $stringToReplace
   *   String for word replacements.
   * @param string $caseCategoryName
   *   Case category name.
   *
   * @return string
   *   Word replaced string.
   */
  public function replaceWords($stringToReplace, $caseCategoryName) {
    if ($caseCategoryName == CaseCategoryHelper::CASE_TYPE_CATEGORY_NAME) {
      return $stringToReplace;
    }

    return str_replace(
      ['civicaseAllowCaseWebform', 'civicaseWebformUrl', 'Cases', 'Case'],
      [
        "civi" . ucfirst($caseCategoryName) . "Allow" . ucfirst($caseCategoryName) . "Webform",
        "civi" . ucfirst($caseCategoryName) . "WebformUrl",
        ucfirst($caseCategoryName),
        ucfirst($caseCategoryName),
      ],
      $stringToReplace
    );
  }

}
