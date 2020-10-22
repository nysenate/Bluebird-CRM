<?php

class CRM_Civicase_BAO_CaseContactLock extends CRM_Civicase_DAO_CaseContactLock {

  /**
   * Create a new CaseContactLock based on array-data
   *
   * @param array $params
   *   key-value pairs
   *
   * @return CRM_Civicase_DAO_CaseContactLock|NULL
   */
  public static function create($params) {
    $className = CRM_Civicase_DAO_CaseContactLock::class;
    $entityName = 'CaseContactLock';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);

    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();

    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Create locks for the given contact for each case.
   *
   * @param array $cases
   * @param array $contacts
   *
   * @return array
   * @throws \API_Exception
   */
  public static function createLocks($cases, $contacts) {
    $result = array();

    if (!is_array($cases)) {
      throw new API_Exception('Cases input parameter has to be an array.');
    }

    if (!is_array($contacts)) {
      throw new API_Exception('Contacts input parameter has to be an array.');
    }

    foreach ($cases as $caseID) {
      $locksBAO = new CRM_Civicase_BAO_CaseContactLock();
      $locksBAO->whereAdd("case_id = $caseID");
      $locksBAO->delete(TRUE);

      foreach ($contacts as $contactID) {
        $lockDAO = self::create(array(
          'case_id' => $caseID,
          'contact_id' => $contactID,
        ));

        $result[] = $lockDAO->toArray();
      }
    }

    return $result;
  }

}
