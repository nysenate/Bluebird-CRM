<?php
/**
 * $Id: file.php,v 1.1.2.17 2009/09/05 13:03:25 slantview Exp $
 *
 * @file file.php
 *   Engine file for file based.
 */
class fileCache extends Cache {
  var $content = array();
  var $fspath = '/tmp/filecache';
  
  function page_fast_cache() {
    return TRUE;
  }
  
  function __construct($bin, $options, $default_options) {
    // Assign the path on the following order: bin specific -> default specific -> /tmp/filepath
    if (isset($options['path'])) {
      $this->fspath = $options['path'];
    }
    elseif (isset($default_options['path'])) {
      $this->fspath = $default_options['path'];
    }
    if (substr($this->fspath,-1) == '/') {
      $this->fspath = substr($this->fspath, 0, strlen($this->fspath) - 2);
    }
    parent::__construct($bin, $options, $default_options);
  }
  
  function get($key) {
    global $user, $conf;
    
    if (isset($this->content[$key])) {
      return $this->content[$key];
    }
    
    $cache_lifetime = variable_get('cache_lifetime', 0);

    //make sure fast cache is enabled (see CacheRouter function page_fast_cache)
    if ($this->page_fast_cache()) {
      $cache = NULL;
      $cache_file = $this->key($key);
      if (file_exists($cache_file)) {    
        if ($fp = fopen($cache_file, 'r')) {
          if (flock($fp, LOCK_SH)) {
            $data = @fread($fp, filesize($cache_file));
            flock($fp, LOCK_UN);
            $cache = unserialize($data);
          }
          fclose($fp);
        }
      }
      return $cache;

    }
    else {
      // garbage collection necessary when enforcing a minimum cache lifetime
      $cache_flush = variable_get('cache_flush', 0);
      if ($cache_flush && ($cache_flush + $cache_lifetime <= time())) {
        variable_set('cache_flush', 0);
      }
    }

    if (isset($cache->data)) {
      // If enforcing a minimum cache lifetime, validate that the data is
      // currently valid for this user before we return it by making sure the
      // cache entry was created before the timestamp in the current session's
      // cache timer. The cache variable is loaded into the $user object by
      // sess_read() in session.inc.
      if ($user->cache > $cache->created) {
        // This cache data is too old and thus not valid for us, ignore it.
        return FALSE;
      }
      return $cache;
    }
    return FALSE;
  }
  
  function set($key, $value, $expire = CACHE_PERMANENT, $headers = NULL) {
    static $subdirectories;
    //make sure fast cache is enabled (see CacheRouter function page_fast_cache)
    if ($this->page_fast_cache()) {
      // prepare the cache before grabbing the file lock
      $cache = new stdClass;
      $cache->cid = $key;
      $cache->table = $this->name;
      $cache->created = time();
      $cache->expire = $expire;
      $cache->headers = $headers;
      $cache->data = $value;
      
      $data = serialize($cache);
      
      $file = $this->key($key);
      if ($fp = @fopen($file, 'w')) {
        // only write to the cache file if we can obtain an exclusive lock
        if (flock($fp, LOCK_EX)) {
          fwrite($fp, $data);
          flock($fp, LOCK_UN);
        }
        fclose($fp);
        @chmod(0664, $file); // Necessary for non-webserver users.
      }
      else {
        // t() may not be loaded
        if (function_exists('watchdog')) {
          watchdog('cache', strtr('Cache write error, failed to open file "%file"', array('%file' => $file)), WATCHDOG_ERROR);
        }
      }
    } 
    else {
      if (function_exists('watchdog')) {
        watchdog('cache', 'Cache write error, failed to verify page_cache_fastpath');
      }
    }
  }
  
  function delete($key) {
    // when using wildcard: $key is part-of-key + '*'
    
    $filename = $this->key($key);
    if (is_dir($filename)) {
      $fspath = $filename;
      // Filename: abcdef12345verylongmd5code--content:123456:987654
      $files = file_scan_directory($fspath, ".--.", array('.', '..', 'CVS'));
      foreach ($files as $file) {
        if(is_file($file->filename)){
          if ($fp = fopen($file->filename, 'w')) {
            // only delete the cache file once we obtain an exclusive lock to prevent
            // deleting a cache file that is currently being read.
            if (flock($fp, LOCK_EX)) {
              unlink($file->filename);
            }
          }
        }
      }
    } 
    else if (strrpos($key, '*') !== FALSE) {
      $look_for = explode('*', $key);
      $fspath = $this->fspath;
      // Filename: abcdef12345verylongmd5code--content:123456:987654
      $files = file_scan_directory($fspath, ".--{$look_for[0]}.*", array('.', '..', 'CVS'));
      foreach ($files as $file) {
        if (is_file($file->filename)){
          if ($fp = @fopen($file->filename, 'w')) {
            // only delete the cache file once we obtain an exclusive lock to prevent
            // deleting a cache file that is currently being read.
            if (flock($fp, LOCK_EX)) {
              unlink($file->filename);
            }
          }
        }
      }
    } 
    else {
      if ($fp = @fopen($filename, 'w')) {
        // only delete the cache file once we obtain an exclusive lock to prevent
        // deleting a cache file that is currently being read.
        if (flock($fp, LOCK_EX)) {
          unlink($filename);
        }
      }
    }
  }

  
  function flush() {
    global $user;
    
    $table = $this->name;
    $cache_lifetime = variable_get('cache_lifetime', 0);
    $fspath = $this->fspath;
    
    $ivalfrom = ord("a");
    $ivalto = ord("f");
    
    for ($i = $ivalfrom; $i <= $ivalto; $i++) {
      $this->purge("$fspath/$table/". chr($i));
    }

    for ($i = 0; $i<10; $i++) {
      $this->purge("$fspath/$table/". $i);
    }
  }
  
  function key($key) {
    $table = $this->name;
    $fspath = $this->fspath;
    if ($key != '*') {
      // Make sure we have a good filename when we concatentate $hash with $appendix:
      // * Can't be over 255 bytes (ext2, 3, 4) or 255 characters (NTFS, FAT) as per 
      //  http://en.wikipedia.org/wiki/Comparison_of_file_systems#Limits
      // * Can't include "? * / \ : ; < >" in NTFS and FAT as per 
      //  http://technet.microsoft.com/en-us/library/cc722482.aspx
      $hash = md5($key);
      $appendix = str_replace(array('?','*','/','\\',':',';','<','>'), '-', $key);
      $filename = $hash . '--' . $appendix;
      
      if (function_exists('mb_substr')) {
        $filename = mb_substr($filename, 0, 255, '8bit');
      }
      else {
        // We'll have to assume we're working with ASCII if mb_ extension isn't installed.
        $filename = substr($filename, 0, 255);
      }

      $this->create_directory($fspath, $hash{0});
      return  "$fspath/$table/". $hash{0}. '/'. $filename;
    }
    else {
      return  "$fspath/$table/";
    }

  }
  
  /**
   * Create the necessary $table directory and/or letter/number subdirectory if
   * it doesn't exist.  We store the directories we've created in a static 
   * so we don't bother doing an fstat on that more than one time per page load.
   */
  function create_directory($fspath, $hash) {
    static $dirs = array();
    $table = $this->name;
    
    $create = array($fspath, "$fspath/$table", "$fspath/$table/$hash");

    foreach ($create as $dir) {
      $dir = rtrim($dir, '/\\');
      // Check the static $dirs to avoid excessive fstats
      if (!isset($dirs[$dir])) {
        $dirs[$dir] = 1;
        if (!is_dir($dir)) {
          if (!mkdir($dir)) {
            // t() is not available.
            if (function_exists('watchdog')) {
              watchdog('cache', strtr('Failed to create directory %dir.', array('%dir' => "<em>$dir</em>")), WATCHDOG_ERROR);
            }
          }
          else {
            @chmod($dir, 0775); // Necessary for non-webserver users.
          }
        }
      }
    }
  }

  function purge($dir) {
    $cache_lifetime = variable_get('cache_lifetime', 0);
    $files = file_scan_directory($dir, '.', array('.', '..', 'CVS'));
    foreach ($files as $file) {
      if ((filemtime($file->filename) < (time() - $cache_lifetime)) && $cache_lifetime > 0) {
        if ($fp = fopen($file->filename, 'r')) {
          // We need an exclusive lock, but don't block if we can't get it as
          // we can simply try again next time cron is run.
          if (flock($fp, LOCK_EX|LOCK_NB)) {
            unlink($file->filename);
          }
        }
      }
      else {
        if (file_exists($file->filename)) {    
          if ($fp = @fopen($file->filename, 'r')) {
            if (flock($fp, LOCK_SH)) {
              $data = @fread($fp, filesize($file->filename));
              flock($fp, LOCK_UN);
              $cache = unserialize($data);
            }
            fclose($fp);
            if ($cache->expire != CACHE_PERMANENT && $cache->expire <= time()) {
              unlink($file->filename);
            }
          }
        }
      }
    }
  }
  
  function stats() {
    $stats = array(
      'uptime' => time(),
      'bytes_used' => disk_total_space($this->fspath) - disk_free_space($this->fspath),
      'bytes_total' => disk_total_space($this->fspath),
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
