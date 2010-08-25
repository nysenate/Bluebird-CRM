<?php
/**
 * $Id$
 *
 * @file none.php
 *   Disable caching altogether.  Useful for debugging and various situations
 *   where caching is undesired.  Please be very careful in using this engine.
 */
class noneCache extends Cache {
  var $content = array();
  
  function page_fast_cache() {
    return FALSE;
  }
  
  function get() {
    return FALSE; // we never find it!
  }
  
  function set() {
    // no op
  }
  
  function delete() {
    // no op
  }

  function flush() {
    // no op
  }
  
  function gc() {
    // no op
  }
  
  function stats() {
    $stats = array(
      'uptime' => time(),
      'bytes_used' => 0,
      'bytes_total' => 0,
      'gets' => 0,
      'sets' => 0,
      'hits' => 0,
      'misses' => 0,
      'req_rate' => 0,
      'hit_rate' => 0,
      'miss_rate' => 0,
      'set_rate' => 0,
    );
    return $stats;
  }
}