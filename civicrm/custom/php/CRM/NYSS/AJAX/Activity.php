<?php

class CRM_NYSS_AJAX_Activity
{
  static function getSubjectList() {
    //CRM_Core_Error::debug_var('GET', $_GET);

    $sql = "
      SELECT id, CONCAT(subject, ' :: ', id) data
      FROM civicrm_activity
      WHERE subject LIKE '{$_GET['s']}%'

    ";
    $sub = CRM_Core_DAO::executeQuery($sql);

    while ( $sub->fetch() ) {
      echo "{$sub->data}|{$sub->id}\n";
    }

    CRM_Utils_System::civiExit();
  }//getSubjectList
}
