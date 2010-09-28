<?php
/**
 * $Id: xcache.php,v 1.1.2.14 2009/09/05 13:03:25 slantview Exp $
 *
 * @file xcache.php
 *   Engine file for XCache.
 */
class xcacheCache extends Cache {
  /**
   * page_fast_cache
   *   This tells CacheRouter to use page_fast_cache.
   *
   *   @return bool TRUE
   */
  function page_fast_cache() {
    return $this->fast_cache;
  }
  
  /**
   * get()
   *   Return item from cache if it is available.
   *
   * @param string $key
   *   The key to fetch.
   * @return mixed object|bool
   *   Returns either the cache object or FALSE on failure
   */
  function get($key) {
    $cache = parent::get($this->key($key));
    if (isset($cache)) {
      return $cache;
    }
    
    // Get item from cache    
    $cache = unserialize(xcache_get($this->key($key)));

    // Update static cache
    parent::set($this->key($key), $cache);

    return $cache;
  }

  /**
   * set()
   *   Add item into cache.
   *
   * @param string $key
   *   The key to set.
   * @param string $value
   *   The data to store in the cache.
   * @param string $expire
   *   The time to expire in seconds.
   * @param string $headers
   *   The page headers.
   * @return bool
   *   Returns TRUE on success or FALSE on failure
   */
  function set($key, $value, $expire = CACHE_PERMANENT, $headers = NULL) {
    // Create new cache object.
    $cache = new stdClass;
    $cache->cid = $key;
    $cache->created = time();
    $cache->expire = $expire;
    $cache->headers = $headers;
    $cache->data = $value;

    if ($expire != CACHE_PERMANENT && $expire != CACHE_TEMPORARY) {
      // Convert Drupal $expire, which is a timestamp, to a TTL
      $ttl = $expire - time();
    }
    else {
      $ttl = 0;
    }

    $return = FALSE;
    if (!empty($key) && $this->lock()) {
      // Get lookup table to be able to keep track of bins
      $lookup = xcache_get($this->lookup);

      // If the lookup table is empty, initialize table
      if (empty($lookup)) {
        $lookup = array();
      }

      $lookup[$this->key($key)] = $expire;

      // Attempt to store full key and value
      if (!xcache_set($this->key($key), serialize($cache), $ttl)) {
        unset($lookup[$this->key($key)]);
        $return = FALSE;
      }
      else {
        // Update static cache
        parent::set($this->key($key), $cache);
        $return = TRUE;
      }
      
      // Resave the lookup table (even on failure)
      xcache_set($this->lookup, $lookup);
      
      // Remove lock.
      $this->unlock();
    }

    return $return;
  }
  
  /**
   * delete()
   *   Remove item from cache.
   *
   * @param string $key
   *   The key to set.
   * @return mixed object|bool
   *   Returns either the cache object or FALSE on failure
   */
  function delete($key) {
    // Remove from static array cache.
    parent::flush();

    if (substr($key, strlen($key) - 1, 1) == '*') {
      $key = $this->key(substr($key, 0, strlen($key) - 1));
      $lookup = xcache_get($this->lookup);
      if (!empty($lookup) && is_array($lookup)) {
        foreach ($lookup as $k => $v) {
          if (substr($k, 0, strlen($key)) == $key) {
            xcache_unset($k);
            unset($lookup[$k]);
          }
        }
      }
      if ($this->lock()) {
        xcache_set($this->lookup, $lookup);
        $this->unlock();
      }
    }
    else {
      if (!empty($key)) {
        if (!xcache_unset($this->key($key))) {
          return FALSE;
        }
      }
    }
    return TRUE;
  }

  /**
   * flush()
   *   Flush the entire cache.
   *
   * @param none
   * @return mixed bool
   *   Returns TRUE
   */
  function flush() {
    parent::flush();
    if ($this->lock()) {
      // Get lookup table to be able to keep track of bins
      $lookup = xcache_get($this->lookup);
    
      // If the lookup table is empty, remove lock and return
      if (empty($lookup) || !is_array($lookup)) {
        $this->unlock();
        return TRUE;
      }

      // Cycle through keys and remove each entry from the cache
      foreach ($lookup as $k => $expire) {
        if ($expire != CACHE_PERMANENT && $expire <= time()) {
          xcache_unset($k);
          unset($lookup[$k]);
        }
      }

      // Resave the lookup table (even on failure)
      xcache_set($this->lookup, $lookup);

      // Remove lock
      $this->unlock();
    }
    
    return TRUE;
  }
  
  function getLookup() {
    return xcache_get($this->lookup);
  }
  
  function setLookup($lookup = array()) {
    xcache_set($this->lookup, $lookup, 0);
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
