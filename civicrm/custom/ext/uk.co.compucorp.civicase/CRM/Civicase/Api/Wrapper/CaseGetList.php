<?php

use CRM_Case_BAO_CaseType as CaseType;
use CRM_Civicase_Helper_CaseCategory as CaseCategoryHelper;

/**
 * CRM_Civicase_ApiWrapper_CaseGetList.
 */
class CRM_Civicase_Api_Wrapper_CaseGetList implements API_Wrapper {

  /**
   * Makes some changes to API input when entity is Case and action is getlist.
   *
   * The API input is validated when the case type category parameter is passed
   * to make sure that the user is not passing a case category it does not have
   * access to. When this parameter is absent, it is added so that the user is
   * restricted to the case type category it has access to.
   */
  public function fromApiInput($apiRequest) {
    if (!$this->canHandleTheRequest($apiRequest)) {
      return $apiRequest;
    }

    $accessibleCaseCategories = CaseCategoryHelper::getAccessibleCaseTypeCategories();
    $caseCategoryParameterValue = $this->getCaseTypeCategoryParameterValue($apiRequest['params']);

    if (!empty($caseCategoryParameterValue)) {
      $this->validateCaseTypeCategoryParameter($caseCategoryParameterValue, $accessibleCaseCategories);
    }
    else {
      $apiRequest['params']['params']['case_id.case_type_id.case_type_category'] = [
        'IN' => $accessibleCaseCategories,
      ];
    }

    $this->allowSearchByCaseId($apiRequest);

    return $apiRequest;
  }

  /**
   * {@inheritdoc}
   */
  public function toApiOutput($apiRequest, $result) {

    return $result;
  }

  /**
   * Allows search by Case ID in addition to the contact name.
   *
   * This is done by setting the case ID to the input and adding
   * and 'OR' condition between the contact name and case Id field.
   *
   * @param array $apiRequest
   *   API Request.
   */
  private function allowSearchByCaseId(array &$apiRequest) {
    $isInputNumeric = !empty($apiRequest['params']['input']) && is_numeric($apiRequest['params']['input']);
    if (!$isInputNumeric || empty($apiRequest['params']['params']['search_by_case_id'])) {
      return;
    }
    $input = $apiRequest['params']['input'];
    $excludedCaseIds = !empty($apiRequest['params']['params']['case_id']['NOT IN']) ? $apiRequest['params']['params']['case_id']['NOT IN'] : [];
    if (!in_array($input, $excludedCaseIds)) {
      $apiRequest['params']['params']['case_id'] = $input;
      $apiRequest['params']['params']['options'] = ['or' => [['case_id', 'contact_id.sort_name']]];
    }
  }

  /**
   * Handles request coming from Case.getlist API.
   *
   * @param array $apiRequest
   *   API Request.
   *
   * @return bool
   *   If request can be handled.
   */
  private function canHandleTheRequest(array $apiRequest) {
    return $apiRequest['entity'] == 'Case' && $apiRequest['action'] == 'getlist';
  }

  /**
   * Validates the case type category parameter.
   *
   * This checks that the contact has access to the list of case categories
   * If not, an exception is thrown.
   *
   * @param array $caseTypeCategoryParam
   *   Case type category list.
   * @param array $accessibleCaseCategories
   *   Accessible case type categories.
   */
  private function validateCaseTypeCategoryParameter(array $caseTypeCategoryParam, array $accessibleCaseCategories) {
    foreach ($caseTypeCategoryParam as $caseCategory) {
      $caseCategory = $this->getCaseCategoryName($caseCategory);
      if (!array_search($caseCategory, $accessibleCaseCategories)) {
        throw new InvalidArgumentException("User does not have access to this case category {$caseCategory}");
      }
    }
  }

  /**
   * Returns the case type category value from API parameter.
   *
   * @param array $params
   *   API parameter.
   *
   * @return array|mixed
   *   Case type category value.
   */
  private function getCaseTypeCategoryParameterValue(array $params) {
    if (empty($params['params']['case_id.case_type_id.case_type_category'])) {
      return [];
    }

    $caseCategoryParams = $params['params']['case_id.case_type_id.case_type_category'];
    if (!is_array($caseCategoryParams)) {
      return [$caseCategoryParams];
    }

    if (!array_key_exists('IN', $caseCategoryParams)) {
      throw new InvalidArgumentException('The case category parameter only supports the IN operator');
    }

    return $caseCategoryParams['IN'];
  }

  /**
   * Gets the case category name.
   *
   * @param string|int $caseCategory
   *   Case category value.
   *
   * @return mixed|null
   *   Case category name.
   */
  private function getCaseCategoryName($caseCategory) {
    if (is_numeric($caseCategory)) {
      $caseTypeCategories = CaseType::buildOptions('case_type_category', 'validate');

      return isset($caseTypeCategories[$caseCategory]) ? $caseTypeCategories[$caseCategory] : NULL;
    }

    return $caseCategory;
  }

}
