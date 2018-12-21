<?php

class CRM_NYSS_AJAX_Activity
{
  static function getSubjectList($print = TRUE) {
    //CRM_Core_Error::debug_var('GET', $_GET);
    //CRM_Core_Error::debug_var('POST', $_POST);

    $allRecords = array();

    if ( $_GET['getrows'] ) {
      $print = FALSE;
    }

    $actTypeSql = '';
    if ( !empty($_GET['activity_type_id']) ) {
      $actTypeSql = "AND activity_type_id = {$_GET['activity_type_id']}";
    }

    $sql = "
      SELECT GROUP_CONCAT(id) ids, subject data
      FROM civicrm_activity
      WHERE subject LIKE '%{$_GET['s']}%'
        {$actTypeSql}
      GROUP BY subject
    ";
    $sub = CRM_Core_DAO::executeQuery($sql);

    while ( $sub->fetch() ) {
      if ( $_GET['getrows'] ) {
        //construction options list
        echo "<option value='{$sub->ids}'>{$sub->data}</option>";
      }
      elseif ( $print ) {
        //print as json friendly
        echo "{$sub->data}|({$sub->ids})\n";
      }
      else {
        //create array
        $allRecords[$sub->ids] = $sub->data;
      }
    }

    if ( $print || $_GET['getrows'] ) {
      CRM_Utils_System::civiExit();
    }
    else {
      //CRM_Core_Error::debug_var('allRecords', $allRecords);
      return $allRecords;
    }
  }//getSubjectList

  /**
   * //NYSS 11385
   * Get all activities
   * copied from CRM_Activity_Page_AJAX::getContactActivity()
   * but without the contact ID filter
   *
   * @return array
   */
  public static function getDashletActivities() {
    $requiredParameters = array();
    $optionalParameters = array(
      'context' => 'String',
      'activity_type_id' => 'Integer',
      'activity_type_exclude_id' => 'Integer',
      'activity_status_id' => 'String',
      'activity_date_relative' => 'String',
      'activity_date_low' => 'String',
      'activity_date_high' => 'String',
    );

    $params = CRM_Core_Page_AJAX::defaultSortAndPagerParams();
    $params += CRM_Core_Page_AJAX::validateParams($requiredParameters, $optionalParameters);
    //Civi::log()->debug('getDashletActivities', array('$params' => $params));

    // get the activities
    $activities = self::getContactActivitySelector($params);
    //Civi::log()->debug('getDashletActivities', array('activities' => $activities));

    foreach ($activities['data'] as $key => $value) {
      // Check if recurring activity.
      if (!empty($value['is_recurring_activity'])) {
        $repeat = $value['is_recurring_activity'];
        $activities['data'][$key]['activity_type'] .= '<br/><span class="bold">' . ts('Repeating (%1 of %2)', array(1 => $repeat[0], 2 => $repeat[1])) . '</span>';
      }
    }

    // store the activity filter preference CRM-11761
    if (Civi::settings()->get('preserve_activity_tab_filter') && ($userID = CRM_Core_Session::getLoggedInContactID())) {
      unset($optionalParameters['context']);
      foreach ($optionalParameters as $searchField => $dataType) {
        $formSearchField = $searchField;
        if ($searchField == 'activity_type_id') {
          $formSearchField = 'activity_type_filter_id';
        }
        elseif ($searchField == 'activity_type_exclude_id') {
          $formSearchField = 'activity_type_exclude_filter_id';
        }
        if (!empty($params[$searchField])) {
          $activityFilter[$formSearchField] = CRM_Utils_Type::escape($params[$searchField], $dataType);
          if (in_array($searchField, array('activity_date_low', 'activity_date_high'))) {
            $activityFilter['activity_date_relative'] = 0;
          }
          elseif ($searchField == 'activity_status_id') {
            $activityFilter['status_id'] = explode(',', $activityFilter[$searchField]);
          }
        }
        elseif (in_array($searchField, array('activity_type_id', 'activity_type_exclude_id'))) {
          $activityFilter[$formSearchField] = '';
        }
      }

      Civi::contactSettings()->set('activity_tab_filter', $activityFilter);
    }
    if (!empty($_GET['is_unit_test'])) {
      return array($activities, $activityFilter);
    }

    CRM_Utils_JSON::output($activities);
  }

  /**
   * //NYSS 11385
   * copied from CRM_Activity_BAO_Activity::getContactActivitySelector
   *
   * Wrapper for ajax activity selector.
   *
   * @param array $params
   *   Associated array for params record id.
   *
   * @return array
   *   Associated array of contact activities
   */
  public static function getContactActivitySelector(&$params) {
    // Format the params.
    $params['offset'] = ($params['page'] - 1) * $params['rp'];
    $params['rowCount'] = $params['rp'];
    $params['sort'] = CRM_Utils_Array::value('sortBy', $params);
    $params['caseId'] = NULL;
    $context = CRM_Utils_Array::value('context', $params);
    $showContactOverlay = !CRM_Utils_String::startsWith($context, "dashlet");
    $activityTypeInfo = civicrm_api3('OptionValue', 'get', array(
      'option_group_id' => "activity_type",
      'options' => array('limit' => 0),
    ));
    $activityIcons = array();
    foreach ($activityTypeInfo['values'] as $type) {
      if (!empty($type['icon'])) {
        $activityIcons[$type['value']] = $type['icon'];
      }
    }
    //Civi::log()->debug('getContactActivitySelector', array('$params' => $params));

    // Get contact activities.
    $params['admin'] = TRUE;
    $activities = CRM_Activity_BAO_Activity::deprecatedGetActivities($params);
    //Civi::log()->debug('getContactActivitySelector', array('activities' => $activities));

    // Add total.
    $params['total'] = CRM_Activity_BAO_Activity::deprecatedGetActivitiesCount($params);
    //Civi::log()->debug('getContactActivitySelector', array('$params' => $params));

    // Format params and add links.
    $contactActivities = array();

    if (!empty($activities)) {
      $activityStatus = CRM_Core_PseudoConstant::activityStatus();

      // Check logged in user for permission.
      $page = new CRM_Core_Page();
      $contactId = CRM_Core_Session::getLoggedInContactID();
      CRM_Contact_Page_View::checkUserPermission($page, $contactId);
      $permissions = array($page->_permission);

      //NYSS 5507 remove edit if not permissioned
      if (!CRM_Core_Permission::check('access all cases and activities')) {
        $permissions[] = CRM_Core_Permission::VIEW;
        unset($permissions[array_search(CRM_Core_Permission::EDIT, $permissions)]);
      }

      if (CRM_Core_Permission::check('delete activities')) {
        $permissions[] = CRM_Core_Permission::DELETE;
      }

      $mask = CRM_Core_Action::mask($permissions);

      foreach ($activities as $activityId => $values) {
        $activity = array();
        $activity['DT_RowId'] = $activityId;
        // Add class to this row if overdue.
        $activity['DT_RowClass'] = "crm-entity status-id-{$values['status_id']}";
        if (CRM_Activity_BAO_Activity::isOverdue($values)) {
          $activity['DT_RowClass'] .= ' status-overdue';
        }
        else {
          $activity['DT_RowClass'] .= ' status-ontime';
        }

        $activity['DT_RowAttr'] = array();
        $activity['DT_RowAttr']['data-entity'] = 'activity';
        $activity['DT_RowAttr']['data-id'] = $activityId;

        $activity['activity_type'] = (!empty($activityIcons[$values['activity_type_id']]) ? '<span class="crm-i ' . $activityIcons[$values['activity_type_id']] . '"></span> ' : '') . $values['activity_type'];
        $activity['subject'] = $values['subject'];

        $activity['source_contact_name'] = '';
        if ($contactId == $values['source_contact_id']) {
          $activity['source_contact_name'] = $values['source_contact_name'];
        }
        elseif ($values['source_contact_id']) {
          $srcTypeImage = "";
          if ($showContactOverlay) {
            $srcTypeImage = CRM_Contact_BAO_Contact_Utils::getImage(
              CRM_Contact_BAO_Contact::getContactType($values['source_contact_id']),
              FALSE,
              $values['source_contact_id']);
          }
          $activity['source_contact_name'] = $srcTypeImage . CRM_Utils_System::href($values['source_contact_name'],
              'civicrm/contact/view', "reset=1&cid={$values['source_contact_id']}");
        }
        else {
          $activity['source_contact_name'] = '<em>n/a</em>';
        }

        $activity['target_contact_name'] = '';
        if (isset($values['target_contact_counter']) && $values['target_contact_counter']) {
          //grab the first contact
          foreach ($values['target_contact_name'] as $tcID => $tcName) {
            $targetLink = CRM_Utils_System::href($tcName, 'civicrm/contact/view', "reset=1&cid={$tcID}");
            $activity['target_contact_name'] = $targetLink;
            break;
          }

          if ($extraCount = $values['target_contact_counter'] - 1) {
            $activity['target_contact_name'] .= ";<br />" . "(" . ts('%1 more', array(1 => $extraCount)) . ")";
          }
        }
        elseif (!$values['target_contact_name']) {
          $activity['target_contact_name'] = '<em>n/a</em>';
        }

        $activity['assignee_contact_name'] = '';
        if (empty($values['assignee_contact_name'])) {
          $activity['assignee_contact_name'] = '<em>n/a</em>';
        }
        elseif (!empty($values['assignee_contact_name'])) {
          $count = 0;
          $activity['assignee_contact_name'] = '';
          foreach ($values['assignee_contact_name'] as $acID => $acName) {
            if ($acID && $count < 5) {
              $assigneeTypeImage = "";
              $assigneeLink = CRM_Utils_System::href($acName, 'civicrm/contact/view', "reset=1&cid={$acID}");
              if ($showContactOverlay) {
                $assigneeTypeImage = CRM_Contact_BAO_Contact_Utils::getImage(
                  CRM_Contact_BAO_Contact::getContactType($acID),
                  FALSE,
                  $acID);
                $activity['assignee_contact_name'] .= "<div>$assigneeTypeImage $assigneeLink";
              }
              else {
                $activity['assignee_contact_name'] .= $assigneeLink;
              }

              $count++;
              if ($count) {
                $activity['assignee_contact_name'] .= ";&nbsp;";
              }
              if ($showContactOverlay) {
                $activity['assignee_contact_name'] .= "</div> ";
              }

              if ($count == 4) {
                $activity['assignee_contact_name'] .= "(" . ts('more') . ")";
                break;
              }
            }
          }
        }

        $activity['activity_date_time'] = CRM_Utils_Date::customFormat($values['activity_date_time']);
        $activity['status_id'] = $activityStatus[$values['status_id']];

        // build links
        $activity['links'] = '';
        $accessMailingReport = FALSE;
        if (!empty($values['mailingId'])) {
          $accessMailingReport = TRUE;
        }

        $actionLinks = CRM_Activity_Selector_Activity::actionLinks(
          CRM_Utils_Array::value('activity_type_id', $values),
          CRM_Utils_Array::value('source_record_id', $values),
          $accessMailingReport,
          CRM_Utils_Array::value('activity_id', $values)
        );

        $actionMask = array_sum(array_keys($actionLinks)) & $mask;

        $activity['links'] = CRM_Core_Action::formLink($actionLinks,
          $actionMask,
          array(
            'id' => $values['activity_id'],
            'cid' => $contactId,
            'cxt' => $context,
            'caseid' => CRM_Utils_Array::value('case_id', $values),
          ),
          ts('more'),
          FALSE,
          'activity.tab.row',
          'Activity',
          $values['activity_id']
        );

        if ($values['is_recurring_activity']) {
          $activity['is_recurring_activity'] = CRM_Core_BAO_RecurringEntity::getPositionAndCount($values['activity_id'], 'civicrm_activity');
        }

        array_push($contactActivities, $activity);
      }
    }

    $activitiesDT = array();
    $activitiesDT['data'] = $contactActivities;
    $activitiesDT['recordsTotal'] = $params['total'];
    $activitiesDT['recordsFiltered'] = $params['total'];

    //Civi::log()->debug('getContactActivitySelector', array('$activitiesDT' => $activitiesDT));
    return $activitiesDT;
  }
}
