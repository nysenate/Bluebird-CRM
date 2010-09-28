<?php
/**
 * $Id: db.php,v 1.1.2.10 2009/09/05 13:03:25 slantview Exp $
 *
 * @file db.php
 *   Database engine file.
 */
class dbCache extends Cache {  
  function page_fast_cache() {
    if ($this->fast_cache === TRUE) {
      require_once './includes/database.inc';
      db_set_active();
    }
    return $this->fast_cache;
  }
  
  function get($key) {
    global $user;
    
    $cache = parent::get($key);
    if ($cache) {
      return $cache;
    }
    
    $cache = db_fetch_object(db_query("SELECT data, created, headers, expire, serialized FROM {". $this->name ."} WHERE cid = '%s'", $key));
    if (isset($cache->data)) {
      $cache->data = db_decode_blob($cache->data);
      if ($cache->serialized) {
        $cache->data = unserialize($cache->data);
      }
	  }
	  parent::set($key, $cache);
	  return $cache;
  }
  
  function set($key, $value, $expire = CACHE_PERMANENT, $headers = NULL) {
    // Create new cache object.
    $cache = new stdClass;
    $cache->cid = $key;
    $cache->created = time();
    $cache->headers = $headers;
    $cache->expire = $expire;

    if (!is_string($value)) {
      $cache->serialized = TRUE;
      $cache->data = serialize($value);
    }
    else { 
      $cache->serialized = FALSE;
      $cache->data = $value;
    }

    db_query("UPDATE {". $this->name ."} SET data = %b, created = %d, expire = %d, headers = '%s', serialized = %d WHERE cid = '%s'", $cache->data, $cache->created, $cache->expire, $cache->headers, $cache->serialized, $key);
    if (!db_affected_rows()) {
      @db_query("INSERT INTO {". $this->name ."} (cid, data, created, expire, headers, serialized) VALUES ('%s', %b, %d, %d, '%s', %d)", $key, $cache->data, $cache->created, $cache->expire, $cache->headers, $cache->serialized);
    }
    parent::set($key, $cache);
  }
  
  function delete($key) {
    parent::flush();
    if (substr($key, -1, 1) == '*') {
      if ($key == '*') {
        db_query("DELETE FROM {". $this->name ."}");
      }
      else {
        $key = substr($key, 0, strlen($key) - 1);
        db_query("DELETE FROM {". $this->name ."} WHERE cid LIKE '%s%%'", $key);
      }
    }
    else {
      db_query("DELETE FROM {". $this->name ."} WHERE cid = '%s'", $key);
    }
  }

  function flush($time = NULL) {
    if (empty($time)) {
      $time = time();
    }
    parent::flush($time);
    db_query("DELETE FROM {". $this->name ."} WHERE expire != %d AND expire < %d", CACHE_PERMANENT, $time);
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
