<?php
/**
 * $Id: memcache.php,v 1.1.2.13 2009/09/05 13:03:25 slantview Exp $
 *
 * @file memcache.php
 *   Engine file for memcache.
 */
class memcacheCache extends Cache {
  var $settings = array();
  var $memcache;
  
  function page_fast_cache() {
    return $this->fast_cache;
  }
  
  function __construct($bin, $options, $default_options) {
    // Assign the servers on the following order: bin specific -> default specific -> localhost port 11211
    if (isset($options['servers'])) {
    	$this->settings['servers'] = $options['servers'];
    	$this->settings['compress'] = isset($options['compress']) ? MEMCACHE_COMPRESSED : 0;
      $this->settings['shared'] = isset($options['shared']) ? $options['shared'] : TRUE;
    }
    else {
      if (isset($default_options['servers'])) {
        $this->settings['servers'] = $default_options['servers'];
        $this->settings['compress'] = isset($default_options['compress']) ? MEMCACHE_COMPRESSED : 0;
        $this->settings['shared'] = isset($default_options['shared']) ? $default_options['shared'] : TRUE;
      }
      else {
        $this->settings['servers'] = array('localhost:11211');
        $this->settings['compress'] = 0;
        $this->settings['shared'] = TRUE;
      }
    }
                                
    parent::__construct($bin, $options, $default_options);
    
    $this->connect();
  }
  
  function get($key) {
    // Attempt to pull from static cache.
    $cache = parent::get($this->key($key));
    if (isset($cache)) {
      return $cache;
    }
    
    // Get from memcache
    $cache = $this->memcache->get($this->key($key));
    
    // Update static cache 
    parent::set($this->key($key), $cache);
    
    return $cache;
  }
  
  function set($key, $value, $expire = CACHE_PERMANENT, $headers = NULL) {    
    // Create new cache object.
    $cache = new stdClass;
    $cache->cid = $key;
    $cache->created = time();
    $cache->expire = $expire;
    $cache->headers = $headers;
    $cache->data = $value;
    
    if ($expire == CACHE_TEMPORARY || $expire == CACHE_PERMENANT) {
      $set_expire = 0;  
    }
    
    if (!empty($key)) {
      if ($this->settings['shared']) {
        if ($this->lock()) {
          // Get lookup table to be able to keep track of bins
          $lookup = $this->memcache->get($this->lookup);

          // If the lookup table is empty, initialize table
          if (empty($lookup)) {
            $lookup = array();
          }

          // Set key to 1 so we can keep track of the bin
          $lookup[$this->key($key)] = $expire;

          // Attempt to store full key and value
          if (!$this->memcache->set($this->key($key), $cache, $this->settings['compress'], $set_expire)) {
            unset($lookup[$this->key($key)]);
            $return = FALSE;
          }
          else {
            // Update static cache
            parent::set($this->key($key), $cache);
            $return = TRUE;
          }

          // Resave the lookup table (even on failure)
          $this->memcache->set($this->lookup, $lookup, FALSE, 0);  

          // Remove lock.
          $this->unlock();
        }
      }
      else {
        // Update memcache
        return $this->memcache->set($this->key($key), $cache, $this->settings['compress'], $set_expire);
      }
    }
  }
  
  function delete($key) {
    // Delete from static cache
    parent::flush();
    
    if (substr($key, strlen($key) - 1, 1) == '*') {
      $key = $this->key(substr($key, 0, strlen($key) - 1));
      if ($this->settings['shared']) {
        $lookup = $this->memcache->get($this->lookup);
        if (!empty($lookup)) {
          foreach ($lookup as $k => $v) {
            if (substr($k, 0, strlen($key)) == $key) {
              $this->memcache->delete($k);
              unset($lookup[$k]);
            }
          }
        }
        if ($this->lock()) {
          $this->memcache->set($this->lookup, $lookup, FALSE, 0); 
          $this->unlock();
        }
      }
      else {
        return $this->flush();
      }
    }
    else {
      if (!empty($key)) {
        return $this->memcache->delete($this->key($key));
      }
    }
  }
  
  function flush() {
    // Flush static cache
    parent::flush();
    
    // If this is a shared cache, we need to cycle through the lookup table and remove individual
    // items directly
    if ($this->settings['shared']) {
      if ($this->lock()) {
        // Get lookup table to be able to keep track of bins
        $lookup = $this->memcache->get($this->lookup);

        // If the lookup table is empty, remove lock and return
        if (empty($lookup)) {
          $this->unlock();
          return TRUE;
        }

        // Cycle through keys and remove each entry from the cache
        foreach ($lookup as $k => $expire) {
          if ($expire != CACHE_PERMANENT && $expire < time()) {
            $this->memcache->delete($k);
            unset($lookup[$k]);
          }
        }

        // Resave the lookup table (even on failure)
        $this->memcache->set($this->lookup, $lookup, FALSE, 0);

        // Remove lock
        $this->unlock();
      }
    }
    else {
      // Flush memcache
      return $this->memcache->flush();
    }
  }
  
  function lock() {
    // Lock once by trying to add lock file, if we can't get the lock, we will loop
    // for 3 seconds attempting to get lock.  If we still can't get it at that point,
    // then we give up and return FALSE.
    if ($this->memcache->add($this->lock, $this->settings['compress'], 0) === FALSE) {
      $time = time();
      while ($this->memcache->add($this->lock, $this->settings['compress'], 0) === FALSE) {
        if (time() - $time >= 3) {
          return FALSE;
        }
      }
    }
    return TRUE;
  }
  
  function unlock() {
    return $this->memcache->delete($this->lock);
  }
  
  function connect() {
    $this->memcache =& new Memcache;
    foreach ($this->settings['servers'] as $server) {
      list($host, $port) = explode(':', $server);
      if (!$this->memcache->addServer($host, $port)) {
        watchdog('cache', "Unable to connect to memcache server $host:$port", WATCHDOG_ERROR);
      }
    }
  }
  
  function close() {
    $this->memcache->close();
  }
  
  function stats() {
    $memcache_stats = $this->memcache->getStats();
    $stats = array(
      'uptime' => $memcache_stats['uptime'],
      'bytes_used' => $memcache_stats['bytes'],
      'bytes_total' => $memcache_stats['limit_maxbytes'],
      'gets' => $memcache_stats['cmd_get'],
      'sets' => $memcache_stats['cmd_set'],
      'hits' => $memcache_stats['get_hits'],
      'misses' => $memcache_stats['get_misses'],
      'req_rate' => (($memcache_stats['cmd_get'] + $memcache_stats['cmd_set']) / $memcache_stats['uptime']),
      'hit_rate' => ($memcache_stats['get_hits'] / $memcache_stats['uptime']),
      'miss_rate' => ($memcache_stats['get_misses'] / $memcache_stats['uptime']),
      'set_rate' => ($memcache_stats['cmd_set'] / $memcache_stats['uptime']),
    );
    return $stats;
  }
}