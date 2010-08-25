<?php
/**
 * $Id: CacheRouter.php,v 1.1.2.7 2009/09/05 13:03:25 slantview Exp $
 *
 * @file CacheRouter.php
 *   The main class for routing to the cache engines.
 */
require dirname(__FILE__) .'/Cache.php';

class CacheRouter {
  var $map = array();
  var $settings = array();
  var $type = array();
  
  function __construct() {
    global $conf;
    $conf['page_cache_fastpath'] = TRUE;
  }
  
  private function __init($bin) {
    global $conf;
    
    if (isset($conf['cacherouter'][$bin]['engine']) && !isset($this->map[$bin])) {
      $type = strtolower($conf['cacherouter'][$bin]['engine']);
    }
    else {
      $type = isset($conf['cacherouter']['default']['engine']) ? $conf['cacherouter']['default']['engine'] : 'db';
    }
    
    $this->type[$bin] = $type;
    
    if (!class_exists($type . 'Cache')) {
      if (!require(dirname(__FILE__) .'/engines/' . $type . '.php')) {
        return FALSE;
      }
    }
    $cache_engine = $type . 'Cache';

    $options = array();    
    if (isset($conf['cacherouter'][$bin])) {
      $options = $conf['cacherouter'][$bin];
    }
    
    $default_options = array();
    if (isset($conf['cacherouter']['default'])) {
      $default_options = $conf['cacherouter']['default'];
    }
    
    $this->map[$bin] = new $cache_engine($bin, $options, $default_options);
  }
  
  public function get($key, $bin) {
    if (!isset($this->map[$bin])) {
      $this->__init($bin);
    }
    return $this->map[$bin]->get($key);
  }

  public function set($key, $value, $expire, $headers, $bin) {
    if (!isset($this->map[$bin])) {
      $this->__init($bin);
    }
    return $this->map[$bin]->set($key, $value, $expire, $headers);
  }

  public function delete($key, $bin) {
    if (!isset($this->map[$bin])) {
      $this->__init($bin);
    }
    return $this->map[$bin]->delete($key);
  }

  public function flush($bin) {
    if (!isset($this->map[$bin])) {
      $this->__init($bin);
    }
    return $this->map[$bin]->flush();
  }
  
  public function page_fast_cache($bin) {
    if (!isset($this->map[$bin])) {
      $this->__init($bin);
    }
    return $this->map[$bin]->page_fast_cache();
  }
  
  public function getStats($bin) {
    $bin = ($bin == 'default') ? 'cache' : $bin;
    
    if (!isset($this->map[$bin])) {
      $this->__init($bin);
    }

    return $this->map[$bin]->stats();
  }
  
  public function getBins() {
    global $conf;
    return array_keys($conf['cacherouter']);
  }
  
  public function getType($bin) {
    $bin = ($bin == 'default') ? 'cache' : $bin;
    return $this->type[$bin];
  }
}