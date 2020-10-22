<?php

use CRM_Civicase_Helper_CaseCategory as CaseCategoryHelper;
use CRM_Case_BAO_CaseType as CaseType;

/**
 * Class CRM_Civicase_BAO_Query_CaseCategory.
 */
class CRM_Civicase_BAO_Query_CaseCategory extends CRM_Contact_BAO_Query_Interface {

  /**
   * {@inheritDoc}
   */
  public function &getFields() {
    $fields = [];

    return $fields;
  }

  /**
   * {@inheritDoc}
   */
  public function from($fieldName, $mode, $side) {
    return '';
  }

  /**
   * Alters where statement to limit results to accessible case categories.
   */
  public function where(&$query) {
    // This query object can be used in other places apart from advanced search page,
    // We need to restrict to this page only.
    if (CRM_Utils_System::currentPath() != 'civicrm/contact/search/advanced') {
      return;
    }

    $accessibleCaseCategories = CaseCategoryHelper::getAccessibleCaseTypeCategories();
    if (!$this->shouldAddCaseTypeCategoryCondition($accessibleCaseCategories)) {
      return;
    }

    $query->_where[0][] = CRM_Contact_BAO_Query::buildClause('civicrm_case_type.case_type_category', 'IN', array_keys($accessibleCaseCategories));
    $query->_element['case_type_category'] = 1;
    $query->_tables['case_type'] = $query->_whereTables['case_type'] = 1;
    $query->_tables['civicrm_case'] = $query->_whereTables['civicrm_case'] = 1;

    list($op, $value) = CRM_Contact_BAO_Query::buildQillForFieldValue('CRM_Case_DAO_CaseType', 'civicrm_case_type.case_type_category', [implode(', ', $accessibleCaseCategories)], 'IN');

    $query->_qill[0][] = ts('%1 %2 %3', [
      1 => 'Case Type Category',
      2 => $op,
      3 => $value,
    ]);
  }

  /**
   * Whether to add case type category condition to query or not.
   *
   * @param array $accessibleCaseCategories
   *   Accessible case categories.
   *
   * @return bool
   *   Whether to add case type category.
   */
  private function shouldAddCaseTypeCategoryCondition(array $accessibleCaseCategories) {
    $caseTypeCategories = CaseType::buildOptions('case_type_category', 'validate');

    return !empty($accessibleCaseCategories) && (count($accessibleCaseCategories) < count($caseTypeCategories));
  }

  /**
   * Implements getPanesMapper, required by getPanesMapper hook.
   *
   * @param array $panes
   *   Panes.
   */
  public function getPanesMapper(array &$panes) {

  }

}
