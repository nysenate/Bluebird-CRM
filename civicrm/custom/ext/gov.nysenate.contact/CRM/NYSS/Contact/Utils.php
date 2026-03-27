<?php

class CRM_NYSS_Contact_Utils {

  // NYSS #17730
  public static function showNickName(&$name, $nick_name = NULL) {
    if ($nick_name) {
      $name .= ' "' . $nick_name . '"';
    }
  }

}
