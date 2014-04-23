<?php

class CRM_NYSS_AJAX_Activity
{
  static function getSubjectList() {
    //CRM_Core_Error::debug_var('GET', $_GET);

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
      echo "{$sub->data}|({$sub->ids})\n";
    }

    CRM_Utils_System::civiExit();
  }//getSubjectList
}
