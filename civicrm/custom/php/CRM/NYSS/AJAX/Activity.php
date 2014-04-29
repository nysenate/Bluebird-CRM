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
}
