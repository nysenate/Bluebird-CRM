<?php

class CRM_Tags_NYSS {

  /**
   * @param $positions
   *
   * @return array
   *
   * given a comma-separated list of position tags, parse and create tags
   * where a value was passed (instead of an existing ID)
   */
  static function processPositionsList($positions) {
    $tags = [];
    foreach (explode(',', $positions) as $tag) {
      if (strpos($tag, ':::') !== false) {
        try {
          $tags[] = civicrm_api3('nyss_tags', 'savePosition', array(
            'value' => $tag,
          ));
        }
        catch (CiviCRM_API3_Exception $e) {}
      }
      else {
        $tags[] = $tag;
      }
    }

    //Civi::log()->debug('processPositionsList', ['$tags' => $tags]);
    return $tags;
  }
}
