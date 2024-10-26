<?php

use CRM_Civicase_ExtensionUtil as E;
use CRM_Utils_SQL_Select as Select;

class CRM_Civicase_Check extends CRM_Utils_Check_Component {

  public function checkActivityRevisionSetting() {
    $messages = array();
    if (Civi::settings()->get('civicaseActivityRevisions')) {
      $messages[] = new CRM_Utils_Check_Message(
        __FUNCTION__ . '_old',
        E::ts('This system uses embedded activity revisions. This feature should be <em>disabled</em> for CiviCase 5. See <a href="%1">CiviCase Settings</a>.',
          array(
            1 => CRM_Utils_System::url('civicrm/admin/setting/case', 'reset=1'),
          )),
        E::ts('CiviCase: Disable old-style logging'),
        \Psr\Log\LogLevel::WARNING,
        'fa-gears'
      );
    }
    if (!Civi::settings()->get('logging')) {
      $messages[] = new CRM_Utils_Check_Message(
        __FUNCTION__ . '_new',
        E::ts('This system does <em>not</em> use data logging. This feature should be <em>enabled</em> for CiviCase 5. See <a href="%1">Misc Settings</a>.',
          array(
            1 => CRM_Utils_System::url('civicrm/admin/setting/misc', 'reset=1'),
          )),
        E::ts('CiviCase: Enable new-style logging'),
        \Psr\Log\LogLevel::WARNING,
        'fa-gears'
      );
    }
    return $messages;
  }

  public function checkActivityRevisionData() {
    $messages = array();
    $oldRevisions = Select::from('civicrm_activity')
      ->select('count(*)')
      ->where('is_current_revision = 0 OR original_id IS NOT NULL')
      ->execute()
      ->fetchValue();
    if ($oldRevisions > 0) {
      $messages[] = new CRM_Utils_Check_Message(
        __FUNCTION__,
        E::ts('This database includes ~%1 historical activity revisions based on the legacy format. This may not work correctly with some filters or reports. Consider migrating or archiving this data.',
          array(
            1 => $oldRevisions,
          )),
        E::ts('CiviCase: Migrate historical logs'),
        \Psr\Log\LogLevel::WARNING,
        'fa-database'
      );
    }
    return $messages;
  }

}
