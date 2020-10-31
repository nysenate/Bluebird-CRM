<?php

use CRM_Civicase_Helper_CaseCategory as CaseCategoryHelper;

/**
 * Class CRM_Civicase_XMLProcessor_Report.
 */
class CRM_Civicase_XMLProcessor_Report extends CRM_Case_XMLProcessor_Report {

  /**
   * Get Activities which match the sent filters.
   *
   * @param int $clientID
   *   Client ID.
   * @param int $caseID
   *   Case Id.
   * @param array $activityTypes
   *   Activity Types.
   * @param array $activities
   *   Activities.
   * @param array $selectedActivities
   *   Selected activities.
   */
  public function getActivities($clientID, $caseID, array $activityTypes, array &$activities, array $selectedActivities = NULL) {
    // Get all activities for this case that in this activityTypes set.
    foreach ($activityTypes as $aType) {
      $activityTypesMap[$aType['id']] = $aType;
    }

    // Get all core activities.
    $coreActivityTypes = CRM_Case_PseudoConstant::caseActivityType(FALSE, TRUE);

    foreach ($coreActivityTypes as $aType) {
      $activityTypesMap[$aType['id']] = $aType;
    }

    $activityTypeIDs = implode(',', array_keys($activityTypesMap));
    $query = "
SELECT a.*, c.id as caseID
FROM   civicrm_activity a,
       civicrm_case     c,
       civicrm_case_activity ac
WHERE  a.is_current_revision = 1
AND    a.is_deleted =0
AND    a.activity_type_id IN ( $activityTypeIDs )
AND    c.id = ac.case_id
AND    a.id = ac.activity_id
AND    ac.case_id = %1
";

    $params = [1 => [$caseID, 'Integer']];
    $dao = CRM_Core_DAO::executeQuery($query, $params);
    while ($dao->fetch()) {
      $activityTypeInfo = $activityTypesMap[$dao->activity_type_id];
      if (count($selectedActivities) === 0 || in_array($dao->id, $selectedActivities)) {
        $activities[] = $this->getActivity($clientID,
          $dao,
          $activityTypeInfo
        );
      }
    }
  }

  /**
   * Get the Case Report for the sent filter.
   *
   * @param int $clientID
   *   Client ID.
   * @param int $caseID
   *   Case Id.
   * @param string $activitySetName
   *   The activity set name.
   * @param array $params
   *   Parameters.
   * @param CRM_Civicase_XMLProcessor_Report $report
   *   CRM_Civicase_XMLProcessor_Report object.
   * @param array $selectedActivities
   *   Selected activities.
   *
   * @return mixed
   *   Report contents.
   */
  public static function getCaseReport($clientID, $caseID, $activitySetName, array $params, CRM_Civicase_XMLProcessor_Report $report, array $selectedActivities = NULL) {

    $template = CRM_Core_Smarty::singleton();

    $template->assign('caseId', $caseID);
    $template->assign('clientID', $clientID);
    $template->assign('activitySetName', $activitySetName);

    if (!empty($params['is_redact'])) {
      $report->_isRedact = TRUE;
      $template->assign('_isRedact', 'true');
    }
    else {
      $report->_isRedact = FALSE;
      $template->assign('_isRedact', 'false');
    }

    // First get all case information.
    $case = $report->caseInfo($clientID, $caseID);
    $template->assign_by_ref('case', $case);

    if ($params['include_activities'] == 1) {
      $template->assign('includeActivities', 'All');
    }
    else {
      $template->assign('includeActivities', 'Missing activities only');
    }

    $xml = $report->retrieve($case['caseTypeName']);

    $activitySetNames = CRM_Case_XMLProcessor_Process::activitySets($xml->ActivitySets);
    $pageTitle = CRM_Utils_Array::value($activitySetName, $activitySetNames);
    $template->assign('pageTitle', $pageTitle);

    if ($activitySetName && !empty($activitySetNames[$activitySetName])) {
      $timelineActivities = $report->getActivityTypes($xml, $activitySetName);
      $activityTypes = !empty($timelineActivities) ? $timelineActivities : CRM_Case_XMLProcessor::allActivityTypes();
    }
    else {
      $activityTypes = CRM_Case_XMLProcessor::allActivityTypes();
    }

    if (!$activityTypes) {
      return FALSE;
    }

    // Next get activity set Information.
    $activitySet = [
      'label' => $report->getActivitySetLabel($xml, $activitySetName),
      'includeActivities' => 'All',
      'redact' => 'false',
    ];
    $template->assign_by_ref('activitySet', $activitySet);

    // Now collect all the information about activities.
    $activities = [];
    $report->getActivities($clientID, $caseID, $activityTypes, $activities, $selectedActivities);
    $template->assign_by_ref('activities', $activities);
    // Now run the template.
    $contents = $template->fetch('CRM/Case/XMLProcessor/Report.tpl');

    return $contents;
  }

  /**
   * Check if its Readct.
   *
   * @param string $string
   *   String.
   * @param bool $printReport
   *   Print Report or not.
   * @param array $replaceString
   *   Replace string.
   *
   * @return mixed
   *   Returns report content.
   */
  private function redact($string, $printReport = FALSE, array $replaceString = []) {
    if ($printReport) {
      return CRM_Utils_String::redaction($string, $replaceString);
    }
    elseif ($this->_isRedact) {
      $regexToReplaceString = CRM_Utils_String::regex($string, $this->_redactionRegexRules);
      return CRM_Utils_String::redaction($string, array_merge($this->_redactionStringRules, $regexToReplaceString));
    }

    return $string;
  }

  /**
   * Process Case Relationship Fields.
   *
   * @param CRM_Civicase_XMLProcessor_Report $report
   *   CRM_Civicase_XMLProcessor_Report object.
   * @param array $caseRoles
   *   Case roles array.
   * @param array $caseRelationships
   *   Case relationships.
   * @param bool $isRedact
   *   If Isredact.
   */
  private static function processCaseRelationshipFields(CRM_Civicase_XMLProcessor_Report &$report, array &$caseRoles, array &$caseRelationships, $isRedact) {
    foreach ($caseRelationships as $key => & $value) {
      if (!empty($caseRoles[$value['relation_type'] . '_' . $value['relationship_direction']])) {
        unset($caseRoles[$value['relation_type'] . '_' . $value['relationship_direction']]);
      }

      if (!$isRedact) {
        continue;
      }

      if (!array_key_exists($value['name'], $report->_redactionStringRules)) {
        $report->_redactionStringRules = CRM_Utils_Array::crmArrayMerge($report->_redactionStringRules,
          [$value['name'] => 'name_' . rand(10000, 100000)]
        );
      }

      $value['name'] = $report->redact($value['name'], TRUE, $report->_redactionStringRules);

      if (!empty($value['email']) &&
        !array_key_exists($value['email'], $report->_redactionStringRules)
      ) {
        $report->_redactionStringRules = CRM_Utils_Array::crmArrayMerge($report->_redactionStringRules,
          [$value['email'] => 'email_' . rand(10000, 100000)]
        );
      }

      $value['email'] = $report->redact($value['email'], TRUE, $report->_redactionStringRules);

      if (!empty($value['phone']) &&
        !array_key_exists($value['phone'], $report->_redactionStringRules)
      ) {
        $report->_redactionStringRules = CRM_Utils_Array::crmArrayMerge($report->_redactionStringRules,
          [$value['phone'] => 'phone_' . rand(10000, 100000)]
        );
      }
      $value['phone'] = $report->redact($value['phone'], TRUE, $report->_redactionStringRules);
    }
  }

  /**
   * Redact Case Client Fields.
   *
   * @param CRM_Civicase_XMLProcessor_Report $report
   *   CRM_Civicase_XMLProcessor_Report object.
   * @param array $caseRoles
   *   Case roles.
   */
  private static function redactCaseClientFields(CRM_Civicase_XMLProcessor_Report &$report, array &$caseRoles) {
    foreach ($caseRoles['client'] as &$client) {
      if (!array_key_exists(CRM_Utils_Array::value('sort_name', $client), $report->_redactionStringRules)) {

        $report->_redactionStringRules = CRM_Utils_Array::crmArrayMerge($report->_redactionStringRules,
          [
            CRM_Utils_Array::value('sort_name', $client) => 'name_' . rand(10000, 100000),
          ]
        );
      }
      if (!array_key_exists(CRM_Utils_Array::value('display_name', $client), $report->_redactionStringRules)) {
        $report->_redactionStringRules[CRM_Utils_Array::value('display_name', $client)] = $report->_redactionStringRules[CRM_Utils_Array::value('sort_name', $client)];
      }
      $client['sort_name'] = $report->redact(CRM_Utils_Array::value('sort_name', $client), TRUE, $report->_redactionStringRules);
      if (!empty($client['email']) &&
        !array_key_exists($client['email'], $report->_redactionStringRules)
      ) {
        $report->_redactionStringRules = CRM_Utils_Array::crmArrayMerge($report->_redactionStringRules,
          [$client['email'] => 'email_' . rand(10000, 100000)]
        );
      }
      $client['email'] = $report->redact(CRM_Utils_Array::value('email', $client), TRUE, $report->_redactionStringRules);

      if (!empty($client['phone']) &&
        !array_key_exists($client['phone'], $report->_redactionStringRules)
      ) {
        $report->_redactionStringRules = CRM_Utils_Array::crmArrayMerge($report->_redactionStringRules,
          [$client['phone'] => 'phone_' . rand(10000, 100000)]
        );
      }
      $client['phone'] = $report->redact(CRM_Utils_Array::value('phone', $client), TRUE, $report->_redactionStringRules);
    }
  }

  /**
   * Process Client Relationship Fields.
   *
   * @param CRM_Civicase_XMLProcessor_Report $report
   *   CRM_Civicase_XMLProcessor_Report object.
   * @param array $relClient
   *   Relationship client.
   * @param array $caseRelationships
   *   Case relationships.
   * @param array $otherRelationships
   *   Other relationships.
   * @param bool $isRedact
   *   Is redact.
   */
  private static function processClientRelationshipFields(CRM_Civicase_XMLProcessor_Report &$report, array &$relClient, array $caseRelationships, array &$otherRelationships, $isRedact) {
    foreach ($relClient as $r) {
      if ($isRedact) {
        if (!array_key_exists($r['name'], $report->_redactionStringRules)) {
          $report->_redactionStringRules = CRM_Utils_Array::crmArrayMerge($report->_redactionStringRules,
            [$r['name'] => 'name_' . rand(10000, 100000)]
          );
        }
        if (!array_key_exists($r['display_name'], $report->_redactionStringRules)) {
          $report->_redactionStringRules[$r['display_name']] = $report->_redactionStringRules[$r['name']];
        }
        $r['name'] = $report->redact($r['name'], TRUE, $report->_redactionStringRules);

        if (!empty($r['phone']) &&
          !array_key_exists($r['phone'], $report->_redactionStringRules)
        ) {
          $report->_redactionStringRules = CRM_Utils_Array::crmArrayMerge($report->_redactionStringRules,
            [$r['phone'] => 'phone_' . rand(10000, 100000)]
          );
        }
        $r['phone'] = $report->redact($r['phone'], TRUE, $report->_redactionStringRules);

        if (!empty($r['email']) &&
          !array_key_exists($r['email'], $report->_redactionStringRules)
        ) {
          $report->_redactionStringRules = CRM_Utils_Array::crmArrayMerge($report->_redactionStringRules,
            [$r['email'] => 'email_' . rand(10000, 100000)]
          );
        }
        $r['email'] = $report->redact($r['email'], TRUE, $report->_redactionStringRules);
      }
      if (!array_key_exists($r['id'], $caseRelationships)) {
        $otherRelationships[] = $r;
      }
    }
  }

  /**
   * Redact Global Relationship Fields.
   *
   * @param CRM_Civicase_XMLProcessor_Report $report
   *   CRM_Civicase_XMLProcessor_Report object.
   * @param array $relGlobal
   *   Relationship.
   */
  private static function redactGlobalRelationshipFields(CRM_Civicase_XMLProcessor_Report &$report, array &$relGlobal) {
    foreach ($relGlobal as & $r) {
      if (!array_key_exists($r['sort_name'], $report->_redactionStringRules)) {
        $report->_redactionStringRules = CRM_Utils_Array::crmArrayMerge($report->_redactionStringRules,
          [$r['sort_name'] => 'name_' . rand(10000, 100000)]
        );
      }
      if (!array_key_exists($r['display_name'], $report->_redactionStringRules)) {
        $report->_redactionStringRules[$r['display_name']] = $report->_redactionStringRules[$r['sort_name']];
      }

      $r['sort_name'] = $report->redact($r['sort_name'], TRUE, $report->_redactionStringRules);
      if (!empty($r['phone']) &&
        !array_key_exists($r['phone'], $report->_redactionStringRules)
      ) {
        $report->_redactionStringRules = CRM_Utils_Array::crmArrayMerge($report->_redactionStringRules,
          [$r['phone'] => 'phone_' . rand(10000, 100000)]
        );
      }
      $r['phone'] = $report->redact($r['phone'], TRUE, $report->_redactionStringRules);

      if (!empty($r['email']) &&
        !array_key_exists($r['email'], $report->_redactionStringRules)
      ) {
        $report->_redactionStringRules = CRM_Utils_Array::crmArrayMerge($report->_redactionStringRules,
          [$r['email'] => 'email_' . rand(10000, 100000)]
        );
      }
      $r['email'] = $report->redact($r['email'], TRUE, $report->_redactionStringRules);
    }
  }

  /**
   * Print report of a specific case.
   */
  public static function printCaseReport() {
    $xmlProcessor = new CRM_Case_XMLProcessor_Process();

    $caseID = CRM_Utils_Request::retrieve('caseID', 'Positive');
    $clientID = CRM_Utils_Request::retrieve('cid', 'Positive');
    $activitySetName = CRM_Utils_Request::retrieve('asn', 'String');
    $selectedActivities = array_filter(explode(",", CRM_Utils_Request::retrieve('sact', 'CommaSeparatedIntegers')));
    $isRedact = CRM_Utils_Request::retrieve('redact', 'Boolean');
    $includeActivities = CRM_Utils_Request::retrieve('all', 'Positive');
    $params = $otherRelationships = $globalGroupInfo = [];
    $report = new CRM_Civicase_XMLProcessor_Report($isRedact);

    if ($includeActivities) {
      $params['include_activities'] = 1;
    }

    if ($isRedact) {
      $params['is_redact'] = 1;
      $report->_redactionStringRules = [];
    }

    $template = CRM_Core_Smarty::singleton();

    // Get case related relationships (Case Role).
    $caseRelationships = CRM_Case_BAO_Case::getCaseRoles($clientID, $caseID);
    $caseType = CRM_Case_BAO_Case::getCaseType($caseID, 'name');

    $caseRoles = $xmlProcessor->get($caseType, 'CaseRoles');
    $caseRoles['client'] = CRM_Case_BAO_Case::getContactNames($caseID);
    // Retrieve ALL client relationships.
    $relClient = CRM_Contact_BAO_Relationship::getRelationship($clientID,
      CRM_Contact_BAO_Relationship::CURRENT,
      0, 0, 0, NULL, NULL, FALSE
    );
    // Now global contact list that appears on all cases.
    $relGlobal = CRM_Case_BAO_Case::getGlobalContacts($globalGroupInfo);

    if ($isRedact) {
      CRM_Civicase_XMLProcessor_Report::redactCaseClientFields($report, $caseRoles);
      CRM_Civicase_XMLProcessor_Report::redactGlobalRelationshipFields($report, $relGlobal);
    }

    CRM_Civicase_XMLProcessor_Report::processCaseRelationshipFields($report, $caseRoles, $caseRelationships, $isRedact);
    CRM_Civicase_XMLProcessor_Report::processClientRelationshipFields($report, $relClient, $caseRelationships, $otherRelationships, $isRedact);

    // Retrieve custom values for cases.
    $customValues = self::getCaseCustomValues($caseID);
    $extends = self::getEntityToExtend($caseID);
    $groupTree = CRM_Core_BAO_CustomGroup::getGroupDetail(NULL, NULL, $extends);
    $caseCustomFields = [];
    foreach ($groupTree as $gid => $group_values) {
      foreach ($group_values['fields'] as $id => $field_values) {
        if (array_key_exists($id, $customValues)) {
          $caseCustomFields[$gid]['title'] = $group_values['title'];
          $caseCustomFields[$gid]['values'][$id] = [
            'label' => $field_values['label'],
            'value' => $customValues[$id],
          ];
        }
      }
    }
    $template->assign('caseRelationships', $caseRelationships);
    $template->assign('caseRoles', $caseRoles);
    $template->assign('otherRelationships', $otherRelationships);
    $template->assign('globalRelationships', $relGlobal);
    $template->assign('globalGroupInfo', $globalGroupInfo);
    $template->assign('caseCustomFields', $caseCustomFields);
    $contents = self::getCaseReport($clientID,
      $caseID,
      $activitySetName,
      $params,
      $report,
      $selectedActivities
    );

    $caseCategoryName = CRM_Civicase_Helper_CaseCategory::getCategoryName($caseID);
    CRM_Civicase_Hook_Helper_CaseTypeCategory::addWordReplacements($caseCategoryName);
    $printReport = CRM_Case_Audit_Audit::run($contents, $clientID, $caseID, TRUE);
    echo $printReport;
    CRM_Utils_System::civiExit();
  }

  /**
   * Returns the case custom values for the given case.
   *
   * The format returned is the case custom field id as array key
   * and the custom value for that case as the value.
   *
   * @param int $caseId
   *   Case ID.
   *
   * @return array
   *   Case custom values.
   */
  private static function getCaseCustomValues($caseId) {
    $customValues = [];
    $result = civicrm_api3('CustomValue', 'gettreevalues', [
      'entity_id' => $caseId,
      'entity_type' => 'Case',
    ]);

    foreach ($result['values'] as $caseCustomGroup) {
      if (empty($caseCustomGroup['fields'])) {
        continue;
      }
      foreach ($caseCustomGroup['fields'] as $customField) {
        if (isset($customField['value']['id'])) {
          $displayField = $customField['value']['display'];
          if ($customField['data_type'] === 'Money' && in_array($customField['html_type'], ['Radio', 'Select'])) {
            $displayField = $customField['value']['data'];
          }
          if ($customField['data_type'] === 'File') {
            $displayField = self::getFileLink($customField);
          }

          $customValues[$customField['id']] = $displayField;
        }
      }
    }

    return $customValues;
  }

  /**
   * Returns the formatted file link.
   *
   * @param string $customData
   *   Custom data.
   *
   * @return string
   *   The file link.
   */
  private static function getFileLink($customData) {
    return CRM_Utils_System::href($customData['value']['fileName'], $customData['value']['fileURL']);
  }

  /**
   * Returns the entity to fetch custom fields for.
   *
   * @param int $caseId
   *   Case ID.
   *
   * @return array
   *   Entity Name.
   */
  private static function getEntityToExtend($caseId) {
    $entityToExtend = ['Case'];
    $caseCategoryName = CaseCategoryHelper::getCategoryName($caseId);
    if ($caseCategoryName && $caseCategoryName != 'Cases') {
      $entityToExtend = [$caseCategoryName];
    }

    return $entityToExtend;
  }

}
