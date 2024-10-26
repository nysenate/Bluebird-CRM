<?php

use CRM_Civicase_Helper_CaseCategory as CaseCategoryHelper;
use CRM_Civicase_ExtensionUtil as ExtensionUtil;
use CRM_Case_BAO_CaseType as CaseType;

/**
 * Class CRM_Civicase_Service_CaseCategoryFromUrl.
 *
 * Service for detecting the category name from a Url.
 */
class CRM_Civicase_Service_CaseCategoryFromUrl {

  /**
   * URL's that the case category can be gotten from the activity param.
   */
  const ACTIVITY_TYPE_URL = 'activity_type';

  /**
   * URL's that the case category can be gotten from the case param.
   */
  const CASE_TYPE_URL = 'case_type';

  /**
   * URL that is of type ajax.
   */
  const AJAX_TYPE_URL = 'ajax';

  /**
   * URL's that the case category can be gotten from the case category param.
   */
  const CASE_CATEGORY_TYPE_URL = 'case_category';

  /**
   * Whether the Ajax call is for entity Case.
   *
   * @var bool
   */
  private $isCaseEntity;

  /**
   * Returns the URL's array configurations.
   *
   * @return array
   *   The URLs config array.
   */
  private function getUrlsConfig() {
    $configFile = CRM_Core_Resources::singleton()
      ->getPath(ExtensionUtil::LONG_NAME, 'config/urls/case_category_url.php');

    return include $configFile;
  }

  /**
   * Gets the case type category name from the URL if it exists.
   *
   * @param string $url
   *   URL string.
   *
   * @return string|null
   *   The case category name.
   */
  public function get($url) {
    $urlConfigs = $this->getUrlsConfig();

    if (!isset($urlConfigs[$url])) {
      return;
    }

    $urlConfig = $urlConfigs[$url];

    if ($urlConfig['url_type'] == self::CASE_TYPE_URL) {
      return $this->getCaseCategoryNameFromCaseIdInUrl($urlConfig['param']);
    }

    if ($urlConfig['url_type'] == self::CASE_CATEGORY_TYPE_URL) {
      return $this->getCaseCategoryFromUrl($urlConfig['param']);
    }

    if ($urlConfig['url_type'] == self::ACTIVITY_TYPE_URL) {
      return $this->getCaseCategoryFromActivityIdInUrl($urlConfig['param']);
    }

    if ($urlConfig['url_type'] == self::AJAX_TYPE_URL) {
      return $this->getCaseCategoryForAjaxRequest($urlConfig['param']);
    }
  }

  /**
   * Gets the case category type from the URL.
   *
   * @param string $caseTypeCategoryParam
   *   Case category Param name.
   *
   * @return mixed|null
   *   Category URL.
   */
  private function getCaseCategoryFromUrl($caseTypeCategoryParam) {
    $caseCategory = CRM_Utils_Request::retrieve($caseTypeCategoryParam, 'String');
    if ($caseCategory) {
      if (is_numeric($caseCategory)) {
        $caseTypeCategories = CaseType::buildOptions($caseTypeCategoryParam, 'validate');

        return isset($caseTypeCategories[$caseCategory]) ? $caseTypeCategories[$caseCategory] : NULL;
      }

      return $caseCategory;
    }

    return $this->getParamValueFromEntryUrl($caseTypeCategoryParam);
  }

  /**
   * Returns the parameter value from the Entry URL.
   *
   * @param string $param
   *   Parameter Name.
   *
   * @return mixed|null
   *   Parameter value.
   */
  private function getParamValueFromEntryUrl($param) {
    $entryURL = CRM_Utils_Request::retrieve('entryURL', 'String');

    $urlParams = parse_url(htmlspecialchars_decode($entryURL), PHP_URL_QUERY);
    parse_str($urlParams, $urlParams);

    if (!empty($urlParams[$param])) {
      return $urlParams[$param];
    }

    return NULL;
  }

  /**
   * Returns the case category name from activity id.
   *
   * @param string $activityIdParamName
   *   Case ID Param name.
   *
   * @return string
   *   Case category name.
   */
  private function getCaseCategoryFromActivityIdInUrl($activityIdParamName) {
    $activityId = CRM_Utils_Request::retrieve($activityIdParamName, 'Integer');

    if (!$activityId) {
      $activityId = $this->getActivityIdFromArrayInUrl($activityIdParamName);
    }

    if ($activityId) {
      $result = civicrm_api3('Activity', 'get', [
        'sequential' => 1,
        'return' => ['case_id'],
        'id' => $activityId,
      ]);

      $caseId = !empty($result['values'][0]['case_id'][0]) ? $result['values'][0]['case_id'][0] : NULL;

      if ($caseId) {
        return CaseCategoryHelper::getCategoryName($caseId);
      }

      return NULL;
    }

    return NULL;
  }

  /**
   * Return an Activity ID from an array received on the query string.
   *
   * @param string $activityIdParamName
   *   Activity ID param name.
   *
   * @return int|null
   *   The first activity ID found, or null.
   */
  private function getActivityIdFromArrayInUrl($activityIdParamName) {
    $activityIds = CRM_Utils_Array::value($activityIdParamName, $_GET);
    if ($activityIds) {
      return array_shift($activityIds);
    }

    return NULL;
  }

  /**
   * Returns the case category name when case Id is known.
   *
   * @param string $caseIdParamName
   *   Case ID Param name.
   *
   * @return string|null
   *   Case category name.
   */
  private function getCaseCategoryNameFromCaseIdInUrl($caseIdParamName) {
    $caseId = CRM_Utils_Request::retrieve($caseIdParamName, 'Integer');

    if (!$caseId) {
      $caseId = $this->getParamValueFromEntryUrl($caseIdParamName);
    }

    return CaseCategoryHelper::getCategoryName($caseId);
  }

  /**
   * Get case category name for Ajax case API requests.
   *
   * @param string $caseTypeCategoryParam
   *   Case category Param name.
   *
   * @return string
   *   Case category name.
   */
  private function getCaseCategoryForAjaxRequest($caseTypeCategoryParam) {
    $entity = CRM_Utils_Request::retrieve('entity', 'String');
    $json = CRM_Utils_Request::retrieve('json', 'String');
    $json = $json ? json_decode($json, TRUE) : [];

    if ($entity && strtolower($entity) == 'case') {
      $this->isCaseEntity = TRUE;
      if (isset($json[$caseTypeCategoryParam])) {
        return $this->getCaseTypeCategoryNameFromOptions($json[$caseTypeCategoryParam]);
      }
    }

    if (strtolower($entity) == 'api3') {
      foreach ($json as $entityParam) {
        [$entityName, $action, $params] = $entityParam;

        if (strtolower($entityName) == 'case') {
          $this->isCaseEntity = TRUE;
          if (isset($params[$caseTypeCategoryParam])) {
            return $this->getCaseTypeCategoryNameFromOptions($params[$caseTypeCategoryParam]);
          }
        }
      }
    }

    return NULL;
  }

  /**
   * Returns value of the isCaseEntity variable.
   *
   * The isCaseentity variable is usually modified by AJAX requests and is
   * useful for the CaseCategory permission hook class to make a decision on
   * whether to check for equivalent case category permission in some secenario.
   *
   * @return bool
   *   Bool value.
   */
  public function getIsCaseEntity() {
    return $this->isCaseEntity;
  }

  /**
   * Returns the case category name from case type id or name.
   *
   * @param mixed $caseTypeCategory
   *   Case category name.
   *
   * @return string
   *   Case category name.
   */
  private function getCaseTypeCategoryNameFromOptions($caseTypeCategory) {
    if (!is_numeric($caseTypeCategory)) {
      return $caseTypeCategory;
    }

    return CaseCategoryHelper::getCaseCategoryNameFromOptionValue($caseTypeCategory);
  }

}
