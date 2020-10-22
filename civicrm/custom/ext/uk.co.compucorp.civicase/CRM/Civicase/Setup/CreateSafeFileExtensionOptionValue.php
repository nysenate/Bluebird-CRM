<?php

/**
 * Class CRM_Civicase_Setup_CreateSafeFileExtensionOptionValue.
 */
class CRM_Civicase_Setup_CreateSafeFileExtensionOptionValue {

  /**
   * Installs 'zip', 'msg', 'eml', 'mbox' Safe File Extention Option values.
   */
  public function apply() {
    $fileExtensionsToBeInstalled = ['zip', 'msg', 'eml', 'mbox'];

    foreach ($fileExtensionsToBeInstalled as $fileExtension) {
      CRM_Core_BAO_OptionValue::ensureOptionValueExists([
        'option_group_id' => 'safe_file_extension',
        'name' => $fileExtension,
        'label' => $fileExtension,
        'is_active' => TRUE,
      ]);
    }

    return TRUE;
  }

}
