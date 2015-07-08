<?php
/* ********************************
   Utility library for IMAP Library
*/

define('IMAP_DISABLEAUTH_NONE',0);
define('IMAP_DISABLEAUTH_GSSAPI',1);
define('IMAP_DISABLEAUTH_NTLM',2);

class NYSS_Utils {

  public static function array_ifelse($key, $list, $default = NULL) {
    if (is_array($list)) {
      return array_key_exists($key, $list) ? $list[$key] : $default;
    }
    return $default;
  }

  public static function object_ifelse($key, $obj, $default = NULL) {
    return self::array_ifelse($key, (array)$obj, $default);
  }

  public static function clean_string($input) {
    return preg_replace('/[^-a-zA-Z0-9: _,.]/', '', $input);
  }
}