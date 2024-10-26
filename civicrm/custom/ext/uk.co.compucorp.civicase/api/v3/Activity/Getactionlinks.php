<?php

/**
 * @file
 * Activity.getactionlinks file.
 */

const ACTIONS_DEFINED_BY_CIVICASE = ['Delete', 'File on Case', 'Edit'];

/**
 * Activity.getactionlinks API specification (optional).
 *
 * @param array $spec
 *   description of fields supported by this API call.
 */
function _civicrm_api3_activity_getactionlinks_spec(array &$spec) {
  $spec['activity_type_id']['api.required'] = 1;
  $spec['source_record_id']['api.required'] = 1;
  $spec['activity_id']['api.required'] = 1;
  $spec['case_id']['api.required'] = 0;
}

/**
 * Activity.getactionlinks API.
 *
 * This API returns the activity action links for an activity record.
 * Basically it adds the action links added by core plus the action links
 * added by various extensions via hooks. Action links differ per activity
 * as some logic may determine which links are available for a particular
 * activity.
 *
 * @param array $params
 *   Params.
 *
 * @return array
 *   Links
 */
function civicrm_api3_activity_getactionlinks(array $params) {
  return _civicrm_api3_activity_getActivityActionLinks($params);
}

/**
 * Returns activity links for the activity ID in the params.
 *
 * @param array $params
 *   Params.
 *
 * @return array
 *   Links.
 */
function _civicrm_api3_activity_getActivityActionLinks(array $params) {
  try {
    $actionLinks = CRM_Activity_Selector_Activity::actionLinks(
      CRM_Utils_Array::value('activity_type_id', $params),
      CRM_Utils_Array::value('source_record_id', $params),
      FALSE,
      CRM_Utils_Array::value('activity_id', $params)
    );
  }
  catch (Exception $e) {
    return [];
  }

  $actionMask = array_sum(array_keys($actionLinks));

  $seqLinks = [];
  foreach ($actionLinks as $bit => $link) {
    $link['bit'] = $bit;
    $seqLinks[] = $link;
  }

  $values = [
    'id' => $params['activity_id'],
    'cid' => CRM_Core_Session::getLoggedInContactID(),
    'cxt' => '',
    'caseid' => CRM_Utils_Array::value('case_id', $params),
  ];

  // Invoke hook links for activity tab rows.
  CRM_Utils_Hook::links(
    'activity.tab.row',
    'Activity',
    $params['activity_id'],
    $seqLinks,
    $actionMask,
    $values
  );

  return _civicrm_api3_activity_GetActionLinks_processLinks($seqLinks);
}

/**
 * Process activity links.
 *
 * @param array $activityActionLinks
 *   Activity Action Links.
 *
 * @return array
 *   Activity Action Links.
 */
function _civicrm_api3_activity_GetActionLinks_processLinks(array $activityActionLinks) {
  foreach ($activityActionLinks as $id => $link) {
    // Remove action links already added by civicase.
    if (in_array($link['name'], ACTIONS_DEFINED_BY_CIVICASE)) {
      unset($activityActionLinks[$id]);
      continue;
    }

    // Format link URL.
    if (isset($link['qs']) && !CRM_Utils_System::isNull($link['qs'])) {
      $urlPath = CRM_Utils_System::url(CRM_Core_Action::replace($link['url'], $values),
        CRM_Core_Action::replace($link['qs'], $values), FALSE, NULL, TRUE
      );
    }
    else {
      $urlPath = CRM_Utils_Array::value('url', $link, '#');
    }

    $activityActionLinks[$id]['url'] = $urlPath;

    // Add link classes.
    $classes = 'action-item';
    if (isset($link['ref'])) {
      $classes .= ' ' . strtolower($link['ref']);
    }

    if (isset($link['class'])) {
      $className = is_array($link['class']) ? implode(' ', $link['class']) : $link['class'];
      $classes .= ' ' . strtolower($className);
    }

    $activityActionLinks[$id]['class'] = $classes;
  }

  return $activityActionLinks;
}
