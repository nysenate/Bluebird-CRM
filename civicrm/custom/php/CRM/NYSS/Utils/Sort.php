<?php

/**
 *
 * NYSS class to extend sort functionality.
 *
 * @package CRM
 * @copyright NYSS (c) 2017
 */
class CRM_NYSS_Utils_Sort {

  //usort functions to sort assoc array by various values
  static function cmpTitle( $a, $b ) {
    return ( $a['title'] <= $b['title'] ) ? -1 : 1;
  }

  static function cmpText( $a, $b ) {
    return ( $a['text'] <= $b['text'] ) ? -1 : 1;
  }

  static function cmpDate( $a, $b ) {
    return ( strtotime($a['date']) >= strtotime($b['date']) ) ? -1 : 1;
  }

  static function cmpName( $a, $b ) {
    return ( $a['name'] <= $b['name'] ) ? -1 : 1;
  }

  static function cmpLogAction( $a, $b ) {
    return ( $a['log_civicrm_entity_log_action'] <= $b['log_civicrm_entity_log_action'] ) ? -1 : 1;
  }
}
