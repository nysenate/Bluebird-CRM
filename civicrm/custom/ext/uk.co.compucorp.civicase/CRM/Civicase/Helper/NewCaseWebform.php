<?php

use CRM_Case_BAO_CaseType as CaseType;
use CRM_Civicase_Hook_Helper_CaseTypeCategory as CaseTypeCategoryHelper;
use CRM_Civicase_Service_CaseCategorySetting as CaseCategorySetting;

/**
 * CRM_Civicase_Helper_NewCaseWebform class.
 */
class CRM_Civicase_Helper_NewCaseWebform {

  /**
   * Adds new case webform URL and client data to the options array.
   *
   * @param array $options
   *   Options array.
   * @param CRM_Civicase_Service_CaseCategorySetting $caseCategorySetting
   *   CaseCategorySetting service.
   */
  public static function addWebformDataToOptions(array &$options, CaseCategorySetting $caseCategorySetting) {
    $caseTypeCategories = CaseType::buildOptions('case_type_category', 'validate');
    $options['caseCategoryWebformSettings'] = [];
    foreach ($caseTypeCategories as $caseTypeCategoryName) {
      $caseTypeCategoryNameLowerCase = strtolower($caseTypeCategoryName);
      $newCaseWebformUrl = CaseTypeCategoryHelper::getNewCaseCategoryWebformUrl($caseTypeCategoryName, $caseCategorySetting);
      $options['caseCategoryWebformSettings'][$caseTypeCategoryNameLowerCase]['newCaseWebformClient'] = 'cid';
      $options['caseCategoryWebformSettings'][$caseTypeCategoryNameLowerCase]['newCaseWebformUrl'] = $newCaseWebformUrl;
      if ($newCaseWebformUrl) {
        $path = explode('/', $newCaseWebformUrl);
        $webformId = array_pop($path);
        $clientId = self::getCaseWebformClientId($webformId);
        if ($clientId) {
          $options['caseCategoryWebformSettings'][$caseTypeCategoryNameLowerCase]['newCaseWebformClient'] = 'cid' . $clientId;
        }
      }
    }
  }

  /**
   * Returns the contact id of the client for given webform id.
   *
   * @param int $webform_id
   *   Webform id.
   *
   * @return int
   *   Contact id.
   */
  public static function getCaseWebformClientId($webform_id) {
    $node = node_load($webform_id);
    $data = $node->webform_civicrm['data'];
    $client = 0;

    if (isset($data['case'][1]['case'][1]['client_id'])) {
      $clients = $data['case'][1]['case'][1]['client_id'];
      $client = reset($clients);
    }

    return $client;
  }

}
