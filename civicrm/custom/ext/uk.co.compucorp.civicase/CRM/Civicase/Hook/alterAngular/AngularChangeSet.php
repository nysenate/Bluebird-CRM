<?php

use CRM_Civicase_ExtensionUtil as ExtensionUtil;
use Civi\Angular\ChangeSet;

/**
 * Angular ChangeSet Helper Class.
 */
class CRM_Civicase_Hook_alterAngular_AngularChangeSet {

  /**
   * Returns ChangeSet for the case type category field.
   *
   * This ChangeSet is needed for the core Case Type create/Edit screen.
   *
   * @return \Civi\Angular\ChangeSet
   *   Angular ChangeSet.
   */
  public static function getForCaseTypeCategoryField() {
    $path = CRM_Core_Resources::singleton()
      ->getPath(ExtensionUtil::LONG_NAME, 'templates/CRM/Civicase/ChangeSet/CaseTypeCategory.html');
    $caseTypeCategoryContent = file_get_contents($path);

    return ChangeSet::create('case-type-category')
      ->alterHtml('~/crmCaseType/caseTypeDetails.html', function (phpQueryObject $doc) use ($caseTypeCategoryContent) {
        $element = $doc->find("div[crm-ui-field*=name: 'caseTypeDetailForm.caseTypeName']");
        if ($element->length) {
          $element->after($caseTypeCategoryContent);
        }
        else {
          $doc->find("[ng-form='caseTypeDetailForm']")->prepend(
            '<p class="error">The case type name selector is invalid, The Instance field will not be available</p>'
          );
        }
      });
  }

  /**
   * Returns ChangeSet for hiding New Case Type button.
   *
   * On the case type listing, we don't want to show the "New Case Type" button
   * because that action is performed specifically on every "Manage Workflows".
   *
   * @return \Civi\Angular\ChangeSet
   *   Angular ChangeSet.
   */
  public static function getForHidingNewCaseTypeButton() {
    return ChangeSet::create('hide-new-case-type-button')
      ->alterHtml('~/crmCaseType/list.html', function (phpQueryObject $doc) {
        $element = $doc->find("a[ng-href*=#/caseType/new");
        if ($element->length) {
          $element->remove();
        }
      });
  }

}
