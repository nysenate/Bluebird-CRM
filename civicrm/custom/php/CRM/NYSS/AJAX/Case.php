<?php

class CRM_NYSS_AJAX_Case
{

  //5340
  static function getAllCases() {
    $sortMapper = array(
      0 => 'expand',
      1 => 'sort_name',
      2 => 'case_subject',
      3 => 'case_status',
      4 => 'case_type',
      5 => 'case_role',
      6 => 'casemanager',
      7 => NULL,
      8 => 'actions',
      9 => 'case_id',
      10 => 'contact_id',
    );

    $sEcho = CRM_Utils_Type::escape($_REQUEST['sEcho'], 'Integer');
    $offset = isset($_REQUEST['iDisplayStart']) ? CRM_Utils_Type::escape($_REQUEST['iDisplayStart'], 'Integer') : 0;
    $rowCount = isset($_REQUEST['iDisplayLength']) ? CRM_Utils_Type::escape($_REQUEST['iDisplayLength'], 'Integer') : 10;
    $sort = isset($_REQUEST['iSortCol_0']) ? CRM_Utils_Array::value(CRM_Utils_Type::escape($_REQUEST['iSortCol_0'], 'Integer'), $sortMapper) : NULL;
    $sortOrder = isset($_REQUEST['sSortDir_0']) ? CRM_Utils_Type::escape($_REQUEST['sSortDir_0'], 'String') : 'asc';

    $params = $_POST;
    CRM_Core_Error::debug_var('params $_POST', $params);
    $sortSQL = NULL;
    if ($sort && $sortOrder) {
      $params['sortname'] = $sort;
      $params['sortorder'] = $sortOrder;
    }
    $params['page'] = ($offset / $rowCount) + 1;
    $params['rp'] = $rowCount;

    $session = CRM_Core_Session::singleton();
    $userID = $session->get('userID');
    $cases = CRM_Case_BAO_Case::getCases(TRUE, $userID, 'any', 'dashboard', $params);
    //CRM_Core_Error::debug_var('upcoming', $cases);
    CRM_Core_Error::debug_var('params', $params);
    CRM_Core_Error::debug_var('count $cases', count($cases));

    $config = CRM_Core_Config::singleton();

    foreach($cases as $key => $value) {
      $cases[$key]['expand'] = '<div id="treeIcon-'.$cases[$key]['case_id'].'"><a><img src="'.$config->resourceBase.'i/TreePlus.gif" class="action-icon" alt="open section"/></a></div>';
      $cases[$key]['actions'] = $cases[$key]['action'].' '. $cases[$key]['moreActions'];

      $cases[$key]['sort_name'] = CRM_Utils_System::href($cases[$key]['sort_name'], 'civicrm/contact/view', "reset=1&cid={$cases[$key]['contact_id']}");
      $cases[$key]['casemanager'] = CRM_Utils_System::href($cases[$key]['casemanager'], 'civicrm/contact/view', "reset=1&cid={$cases[$key]['casemanager_id']}");
    }

    $iFilteredTotal = $iTotal = $params['total'];
    $selectorElements = array(
      'expand',
      'sort_name',
      'case_subject',
      'case_status',
      'case_type',
      'case_role',
      'casemanager',
      NULL,
      'actions',
      'case_id',
      'contact_id'
    );

    echo CRM_Utils_JSON::encodeDataTableSelector($cases, $sEcho, $iTotal, $iFilteredTotal, $selectorElements);
    CRM_Utils_System::civiExit();
  }

}
