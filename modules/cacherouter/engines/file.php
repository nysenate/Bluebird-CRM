<?php

/**
 * @file
 *   Engine file for file based.
 */

class fileCacheRouterEngine extends CacheRouterEngine {
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
    if (substr($this->fspath, -1) == '/') {
      $this->fspath = substr($this->fspath, 0, strlen($this->fspath) - 2);
    }
    parent::__construct($bin, $options, $default_options);
  }

  function get($key) {
    global $user, $conf;

    //make sure fast cache is enabled (see CacheRouter function page_fast_cache)
    if ($this->page_fast_cache()) {
      $cache = NULL;
      $cache_file = glob($this->key($key) . '.*', GLOB_NOSORT);
      if (isset($cache_file[0])) {
        if ($fp = @fopen($cache_file[0], 'r')) {
          if (flock($fp, LOCK_SH)) {
            $data = @fread($fp, filesize($cache_file[0]));
            flock($fp, LOCK_UN);
            $cache = unserialize($data);
          }
          fclose($fp);
        }
      }
      return $cache;
    }
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

      // Filename: cache_key-hash.expiration
      if ($expire == CACHE_PERMANENT) {
        $extension = '.perm';
      }
      else {
        $extension = '.0' . ($expire == CACHE_TEMPORARY ? '' : $expire);
      }
      $file = $this->key($key);

      // Grab a lock. Since we're going to (potentially) rename files, we need
      // an additional per-directory lock for writes, to avoid insertion of
      // duplicate entries with different expiration timestamps in concurrent
      // requests. This extra lock is kept on a dummy file, which is outside
      // the actual directory to simplify mass deletion of data files.
      $success = FALSE;
      if ($lockfile = @fopen(dirname($file) . '.lock', 'w')) {
        if (flock($lockfile, LOCK_EX)) {
          // Remove any old entries
          $this->_delete($file . '.*');

          // Write fresh entry
          $file .= $extension;
          if ($fp = @fopen($file, 'w')) {
            // only write to the cache file if we can obtain an exclusive lock.
            // This lock defends against concurrent reading of this one file.
            if (flock($fp, LOCK_EX)) {
              fwrite($fp, $data);
              flock($fp, LOCK_UN);
            }
            fclose($fp);
            $success = TRUE;
          }

          flock($lockfile, LOCK_UN);
          @chmod($file, 0664); // Necessary for non-webserver users.
        }
        fclose($lockfile);
      }

      if (!$success) {
        if (function_exists('watchdog')) {
          watchdog('cache', 'Cache write error, failed to open file "%file"', array('%file' => $file), WATCHDOG_ERROR);
        }
      }
    }
    else {
      if (function_exists('watchdog')) {
        watchdog('cache', 'Cache write error, failed to verify page_cache_fastpath', array(), WATCHDOG_ERROR);
      }
    }
  }

  function delete($key) {
    // when using wildcard: $key is part-of-key + '*'

    $filename = $this->key($key);
    if (is_dir($filename)) {
      // '*' => Delete all
      $this->_delete($filename . '*/*.*');
    }
    elseif (strrpos($key, '*') !== FALSE) {
      // 'part-of-key*' => Delete all matching entries
      $look_for = explode('*', $key);
      $this->_delete($this->key('*') . '*/' . $this->escape_key($look_for[0], '') . '*.*');
    }
    else {
      // 'full-key' => Delete single entry
      $this->_delete($filename . '.*');
    }
  }

  // Helper function to delete a set of files
  function _delete($file_pattern) {
    if ($files = glob($file_pattern, GLOB_NOSORT)) {
      foreach ($files as $file) {
        if ($fp = @fopen($file, 'w')) {
          // Obtain an exclusive lock to ensure a successful removal of the
          // file; unlink() fails on Windows in certain concurrency situations.
          // Because another concurrent unlink() may be executed as we wait for
          // the lock, we need to suppress errors, too.
          if (flock($fp, LOCK_EX)) {
            @unlink($file);
          }
        }
      }
    }
  }

  function flush() {
    $this->purge($this->key('*'));
  }

  function key($key) {
    $table = $this->name;
    $fspath = $this->fspath;
    if ($key != '*') {

      $hash = md5($key);
      $filename = $this->escape_key($key, '-' . $hash);

      $this->create_directory($fspath, $hash{0});
      return "$fspath/$table/" . $hash{0} . '/' . $filename;
    }
    else {
      return "$fspath/$table/";
    }
  }

  /**
   * Escape cache key and limit its size, to obtain a consistent filename
   * for all operations.
   */
  function escape_key($key, $appendix) {
    // Make sure we have a good filename, by custom-escaping the cache key.
    // Add md5 hash to ensure uniqueness on case-insensitive filesystems, and
    // trim to maximum length, keeping space for 9 characters of an extension.
    // * Can't be over 255 bytes (ext2, 3, 4) or 255 characters (NTFS, FAT) as per 
    //  http://en.wikipedia.org/wiki/Comparison_of_file_systems#Limits
    // * Can't include "? * / \ : ; < >" in NTFS and FAT as per 
    //  http://technet.microsoft.com/en-us/library/cc722482.aspx
    // * Can't inlude dots, due to our handling of filename extensions.
    $filename = strtr($key, array('!' => '!!', '.' => '!,',
      '\x00' => '!0', '\x01' => '!1', '\x02' => '!2', '\x03' => '!3', '\x04' => '!4',
      '\x05' => '!5', '\x06' => '!6', '\x07' => '!7', '\x08' => '!8', '\x09' => '!9',
      '\x0A' => '!A', '\x0B' => '!B', '\x0C' => '!C', '\x0D' => '!D', '\x0E' => '!E',
      '\x0F' => '!F', '\x10' => '!G', '\x11' => '!H', '\x12' => '!I', '\x13' => '!J',
      '\x14' => '!K', '\x15' => '!L', '\x16' => '!M', '\x17' => '!N', '\x18' => '!O',
      '\x19' => '!P', '\x1A' => '!Q', '\x1B' => '!R', '\x1C' => '!S', '\x1D' => '!T',
      '\x1E' => '!U', '\x1F' => '!V',
      '?' => '!W', '*' => '!X', '<' => '!Y', '>' => '!Z', ' ' => '!_', '/' => '!=',
      '\\' => '!-', ':' => '!+', ';' => '!#'));
    $filename .= $appendix;

    if (function_exists('mb_substr')) {
      $filename = mb_substr($filename, 0, 246, '8bit');
    }
    else {
      // We'll have to assume we're working with ASCII if mb_ extension isn't installed.
      $filename = substr($filename, 0, 246);
    }

    return $filename;
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
              watchdog('cache', 'Failed to create directory "%dir".', array('%dir' => $dir), WATCHDOG_ERROR);
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
    // Get list of all files for this table, except permanent entries.
    if ($files = glob($dir . '*/*.0*', GLOB_NOSORT)) {
      foreach ($files as $file) {
        // We have expiration timestamp in filename extension (0 for CACHE_TEMPORARY)
        $expire = substr($file, strrpos($file, '.') + 1);
        if ($expire <= time()) {
          // No need to lock the file, because unlink() doesn't harm concurrent
          // processes accessing the same file. If unlink() fails (on Windows,
          // or when another concurrent unlink() happens), we just suppress the
          // error, and try again on next cron run.
          @unlink($file);
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
