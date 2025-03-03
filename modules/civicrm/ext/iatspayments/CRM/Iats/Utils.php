<?php

class CRM_Iats_Utils {

  public static function getSettings(): array {
    $settingValues = [];
    $settings = ['email_recurring_failure_report', 'bcc_email_recurring_failure_report', 'receipt_recurring', 'recurring_failure_threshhold', 'email_failure_contribution_receipt', 'disable_cryptogram', 'ach_category_text', 'no_edit_extra', 'enable_update_subscription_billing_info', 'enable_change_subscription_amount', 'enable_cancel_recurring', 'enable_cancel_recurring', 'days'];
    $hasNotbeenMigrated = CRM_Core_DAO::singleValueQuery("SELECT id FROM civicrm_setting WHERE name = 'iats_settings'");
    if ($hasNotbeenMigrated) {
      return Civi::settings()->get('iats_settings');
    }
    else {
      foreach ($settings as $setting) {
        $settingValue = Civi::settings()->get('iats_' . $setting);
        if ($setting === 'days') {
          // the days setting value is unpredictable, fix that here
          // convert value to an array if it's a serialized string
          if (is_string($settingValue)) {
            // if setting value is empty or blank set it to be the "disabled" serialized array of [-1].
            if (empty($settingValue) || trim($settingValue, CRM_Core_DAO::VALUE_SEPARATOR) === '') {
              $settingValue = ['-1'];
            }
            else {
              $settingValue = CRM_Core_DAO::unSerializeField($settingValue, CRM_Core_DAO::SERIALIZE_SEPARATOR_BOOKEND);
            }
          }
          // in case we end up with something wacky, set it to the "disabled" option
          if (empty($settingValue) || !is_array($settingValue)) {
            $settingValue = ['-1'];
          }
        }
        $settingValues[$setting] = $settingValue;
      }
    }
    return $settingValues;
  }


  public static function migrateSettings(): void {
    $settings = ['email_recurring_failure_report', 'bcc_email_recurring_failure_report', 'receipt_recurring', 'recurring_failure_threshhold', 'email_failure_contribution_receipt', 'disable_cryptogram', 'ach_category_text', 'no_edit_extra', 'enable_update_subscription_billing_info', 'enable_change_subscription_amount', 'enable_cancel_recurring', 'enable_cancel_recurring', 'days'];
    $currentSettingsValues = Civi::settings()->get('iats_settings');
    foreach ($settings as $setting) {
      if (isset($currentSettingsValues[$setting])) {
        Civi::settings()->set('iats_' . $setting, $currentSettingsValues[$setting]);
      }
    }
    CRM_Core_DAO::executeQuery("DELETE FROM civicrm_setting WHERE name = 'iats_settings'");
  }

  public static function settingDateOptions(): array {
    $days = array('-1' => 'disabled');
    for ($i = 1; $i <= 28; $i++) {
      $days["$i"] = "$i";
    }
    return $days;
  }

}
