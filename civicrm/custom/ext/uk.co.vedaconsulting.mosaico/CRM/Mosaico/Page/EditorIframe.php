<?php

class CRM_Mosaico_Page_EditorIframe extends CRM_Mosaico_Page_Editor {

  /**
   * Modify return value of parent:: method.
   */
  protected function getScriptUrls() {
    $scriptUrls = parent::getScriptUrls();

    CRM_Utils_Hook::singleton()->invoke(['scriptUrls'], $scriptUrls, $null, $null,
      $null, $null, $null,
      'civicrm_mosaicoScriptUrlsAlter'
    );
    return $scriptUrls;
  }

  /**
   * Modify return value of parent:: method.
   */
  protected function getStyleUrls() {
    $styleUrls = parent::getStyleUrls();

    CRM_Utils_Hook::singleton()->invoke(['styleUrls'], $styleUrls, $null, $null,
      $null, $null, $null,
      'civicrm_mosaicoStyleUrlsAlter'
    );
    return $styleUrls;
  }
}
