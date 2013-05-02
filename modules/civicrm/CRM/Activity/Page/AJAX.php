<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2012
 *
 */

/**
 * This class contains all the function that are called using AJAX (jQuery)
 */
class CRM_Activity_Page_AJAX {
  static
  function getCaseActivity() {
    $caseID    = CRM_Utils_Type::escape($_GET['caseID'], 'Integer');
    $contactID = CRM_Utils_Type::escape($_GET['cid'], 'Integer');
    $userID    = CRM_Utils_Type::escape($_GET['userID'], 'Integer');
    $context   = CRM_Utils_Type::escape(CRM_Utils_Array::value('context', $_GET), 'String');

    $sortMapper = array(
      0 => 'display_date', 1 => 'ca.subject', 2 => 'ca.activity_type_id',
      3 => 'acc.sort_name', 4 => 'cc.sort_name', 5 => 'ca.status_id',
    );

    $sEcho     = CRM_Utils_Type::escape($_REQUEST['sEcho'], 'Integer');
    $offset    = isset($_REQUEST['iDisplayStart']) ? CRM_Utils_Type::escape($_REQUEST['iDisplayStart'], 'Integer') : 0;
    $rowCount  = isset($_REQUEST['iDisplayLength']) ? CRM_Utils_Type::escape($_REQUEST['iDisplayLength'], 'Integer') : 25;
    $sort      = isset($_REQUEST['iSortCol_0']) ? CRM_Utils_Array::value(CRM_Utils_Type::escape($_REQUEST['iSortCol_0'], 'Integer'), $sortMapper) : NULL;
    $sortOrder = isset($_REQUEST['sSortDir_0']) ? CRM_Utils_Type::escape($_REQUEST['sSortDir_0'], 'String') : 'asc';

    $params = $_POST;
    if ($sort && $sortOrder) {
      $params['sortname'] = $sort;
      $params['sortorder'] = $sortOrder;
    }
    $params['page'] = ($offset / $rowCount) + 1;
    $params['rp'] = $rowCount;

    // get the activities related to given case
    $activities = CRM_Case_BAO_Case::getCaseActivity($caseID, $params, $contactID, $context, $userID);

    $iFilteredTotal = $iTotal = $params['total'];
    $selectorElements = array('display_date', 'subject', 'type', 'with_contacts', 'reporter', 'status', 'links', 'class');

    echo CRM_Utils_JSON::encodeDataTableSelector($activities, $sEcho, $iTotal, $iFilteredTotal, $selectorElements);
    CRM_Utils_System::civiExit();
  }

  static
  function convertToCaseActivity() {
    $params = array('caseID', 'activityID', 'contactID', 'newSubject', 'targetContactIds', 'mode');
    foreach ($params as $param) {
      $vals[$param] = CRM_Utils_Array::value($param, $_POST);
    }

    $retval = self::_convertToCaseActivity($vals);

    echo json_encode($retval);
    CRM_Utils_System::civiExit();
  }

  static
  function _convertToCaseActivity($params) {
    if (!$params['activityID'] || !$params['caseID']) {
      return (array('error_msg' => 'required params missing.'));
    }

    $otherActivity = new CRM_Activity_DAO_Activity();
    $otherActivity->id = $params['activityID'];
    if (!$otherActivity->find(TRUE)) {
      return (array('error_msg' => 'activity record is missing.'));
    }
    $actDateTime = CRM_Utils_Date::isoToMysql($otherActivity->activity_date_time);

    //create new activity record.
    $mainActivity = new CRM_Activity_DAO_Activity();
    $mainActVals = array();
    CRM_Core_DAO::storeValues($otherActivity, $mainActVals);

    //get new activity subject.
    if (!empty($params['newSubject'])) {
      $mainActVals['subject'] = $params['newSubject'];
    }

    $mainActivity->copyValues($mainActVals);
    $mainActivity->id = NULL;
    $mainActivity->activity_date_time = $actDateTime;
    //make sure this is current revision.
    $mainActivity->is_current_revision = TRUE;
    //drop all relations.
    $mainActivity->parent_id = $mainActivity->original_id = NULL;

    $mainActivity->save();
    $mainActivityId = $mainActivity->id;
    CRM_Activity_BAO_Activity::logActivityAction($mainActivity);
    $mainActivity->free();

    /* Mark previous activity as deleted. If it was a non-case activity
         * then just change the subject.
         */

    if (in_array($params['mode'], array(
      'move', 'file'))) {
      $caseActivity = new CRM_Case_DAO_CaseActivity();
      $caseActivity->case_id = $params['caseID'];
      $caseActivity->activity_id = $otherActivity->id;
      if ($params['mode'] == 'move' || $caseActivity->find(TRUE)) {
        $otherActivity->is_deleted = 1;
      }
      else {
        $otherActivity->subject = ts('(Filed on case %1)', array(
          1 => $params['caseID'])) . ' ' . $otherActivity->subject;
      }
      $otherActivity->activity_date_time = $actDateTime;
      $otherActivity->save();

      $caseActivity->free();
    }
    $otherActivity->free();

    $targetContacts = array();
    if (!empty($params['targetContactIds'])) {
      $targetContacts = array_unique(explode(',', $params['targetContactIds']));
    }
    foreach ($targetContacts as $key => $value) {
      $targ_params = array(
        'activity_id' => $mainActivityId,
        'target_contact_id' => $value,
      );
      CRM_Activity_BAO_Activity::createActivityTarget($targ_params);
    }

    // typically this will be empty, since assignees on another case may be completely different
    $assigneeContacts = array();
    if (!empty($params['assigneeContactIds'])) {
      $assigneeContacts = array_unique(explode(',', $params['assigneeContactIds']));
    }
    foreach ($assigneeContacts as $key => $value) {
      $assigneeParams = array(
        'activity_id' => $mainActivityId,
        'assignee_contact_id' => $value,
      );
      CRM_Activity_BAO_Activity::createActivityAssignment($assigneeParams);
    }

    //attach newly created activity to case.
    $caseActivity = new CRM_Case_DAO_CaseActivity();
    $caseActivity->case_id = $params['caseID'];
    $caseActivity->activity_id = $mainActivityId;
    $caseActivity->save();
    $error_msg = $caseActivity->_lastError;
    $caseActivity->free();

    $params['mainActivityId'] = $mainActivityId;
    CRM_Activity_BAO_Activity::copyExtendedActivityData($params);

    return (array('error_msg' => $error_msg, 'newId' => $mainActivity->id));
  }

  static
  function getContactActivity() {
    $contactID = CRM_Utils_Type::escape($_POST['contact_id'], 'Integer');
    $context = CRM_Utils_Type::escape(CRM_Utils_Array::value('context', $_GET), 'String');

    $sortMapper = array(
      0 => 'activity_type', 1 => 'subject', 2 => 'source_contact_name',
      3 => '', 4 => '', 5 => 'activity_date_time', 6 => 'status_id',
    );

    $sEcho     = CRM_Utils_Type::escape($_REQUEST['sEcho'], 'Integer');
    $offset    = isset($_REQUEST['iDisplayStart']) ? CRM_Utils_Type::escape($_REQUEST['iDisplayStart'], 'Integer') : 0;
    $rowCount  = isset($_REQUEST['iDisplayLength']) ? CRM_Utils_Type::escape($_REQUEST['iDisplayLength'], 'Integer') : 25;
    $sort      = isset($_REQUEST['iSortCol_0']) ? CRM_Utils_Array::value(CRM_Utils_Type::escape($_REQUEST['iSortCol_0'], 'Integer'), $sortMapper) : NULL;
    $sortOrder = isset($_REQUEST['sSortDir_0']) ? CRM_Utils_Type::escape($_REQUEST['sSortDir_0'], 'String') : 'asc';

    $params = $_POST;
    if ($sort && $sortOrder) {
      $params['sortBy'] = $sort . ' ' . $sortOrder;
    }

    $params['page'] = ($offset / $rowCount) + 1;
    $params['rp'] = $rowCount;

    $params['contact_id'] = $contactID;
    $params['context'] = $context;

    // get the contact activities
    $activities = CRM_Activity_BAO_Activity::getContactActivitySelector($params);

    $iFilteredTotal = $iTotal = $params['total'];
    $selectorElements = array(
      'activity_type', 'subject', 'source_contact',
      'target_contact', 'assignee_contact',
      'activity_date', 'status', 'links', 'class',
    );

    echo CRM_Utils_JSON::encodeDataTableSelector($activities, $sEcho, $iTotal, $iFilteredTotal, $selectorElements);
    CRM_Utils_System::civiExit();
  }
}

