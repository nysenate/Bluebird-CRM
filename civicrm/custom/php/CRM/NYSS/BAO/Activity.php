<?php

/*
 * NYSS functions related to activity records
 */
class CRM_NYSS_BAO_Activity {
  /**
   * Pull activity count via ajax
   */
  static function getTabCount( ) {
    $contactId = CRM_Utils_Array::value( 'contactId', $_POST, NULL );
    if( $contactId ) {
      $input = array(
        'contact_id' => $contactId,
        'admin' => FALSE,
        'caseId' => NULL,
        'context' => 'activity',
      );
      echo CRM_Activity_BAO_Activity::getActivitiesCount($input);
    }
    CRM_Utils_System::civiExit( );
  }
}
