<?php
/**
 * $Id: cacherouter.inc,v 1.2.2.12 2009/09/05 13:03:25 slantview Exp $
 *
 * @file cacherouter.inc
 *   Defines the cacherouter module
 */
require dirname(__FILE__) .'/CacheRouter.php';

/**
 * Return data from the persistent cache. Data may be stored as either plain text or as serialized data.
 * cache_get will automatically return unserialized objects and arrays.
 *
 * @param $key
 *   The cache ID of the data to retrieve.
 * @param $table
 *   The table $table to store the data in. Valid core values are 'cache_filter',
 *   'cache_menu', 'cache_page', or 'cache' for the default cache.
 */
function cache_get($key, $table = 'cache') {
  global $cache, $user;
  if (!isset($cache)) {
    $cache = new CacheRouter();
  }
  
  $cache_object = $cache->get($key, $table);  
  if (isset($cache_object->data)) {
    if ($cache_object->expire == CACHE_PERMANENT || !variable_get('cache_lifetime', 0)) {
      return $cache_object;
    }
    else {
      if ($user->cache > $cache_object->created) {
        return 0;
      }
      else {
        return $cache_object;
      }
    }
  }
  
  return 0;
}

/**
 * Store data in the persistent cache.
 *
 * The persistent cache is split up into four database
 * tables. Contributed modules can add additional tables.
 *
 * 'cache_page': This table stores generated pages for anonymous
 * users. This is the only table affected by the page cache setting on
 * the administrator panel.
 *
 * 'cache_menu': Stores the cachable part of the users' menus.
 *
 * 'cache_filter': Stores filtered pieces of content. This table is
 * periodically cleared of stale entries by cron.
 *
 * 'cache': Generic cache storage table.
 *
 * The reasons for having several tables are as follows:
 *
 * - smaller tables allow for faster selects and inserts
 * - we try to put fast changing cache items and rather static
 *   ones into different tables. The effect is that only the fast
 *   changing tables will need a lot of writes to disk. The more
 *   static tables will also be better cachable with MySQL's query cache
 *
 * @param $key
 *   The cache ID of the data to store.
 * @param $data
 *   The data to store in the cache. Complex data types will be automatically serialized before insertion.
 *   Strings will be stored as plain text and not serialized.
 * @param $table
 *   The table $table to store the data in. Valid core values are 'cache_filter',
 *   'cache_menu', 'cache_page', or 'cache'.
 * @param $expire
 *   One of the following values:
 *   - CACHE_PERMANENT: Indicates that the item should never be removed unless
 *     explicitly told to using cache_clear_all() with a cache ID.
 *   - CACHE_TEMPORARY: Indicates that the item should be removed at the next
 *     general cache wipe.
 *   - A Unix timestamp: Indicates that the item should be kept at least until
 *     the given time, after which it behaves like CACHE_TEMPORARY.
 * @param $headers
 *   A string containing HTTP header information for cached pages.
 */
function cache_set($key, $value, $table = 'cache', $expire = CACHE_PERMANENT, $headers = NULL) {
  global $cache;
  if (!isset($cache)) {
    $cache = new CacheRouter();
  }
  return $cache->set($key, $value, $expire, $headers, $table);
}

/**
 *
 * Expire data from the cache. If called without arguments, expirable
 * entries will be cleared from the cache_page and cache_block tables.
 *
 * @param $key
 *   If set, the cache ID to delete. Otherwise, all cache entries that can
 *   expire are deleted.
 *
 * @param $table
 *   If set, the table $table to delete from. Mandatory
 *   argument if $key is set.
 *
 * @param $wildcard
 *   If set to TRUE, the $key is treated as a substring
 *   to match rather than a complete ID. The match is a right hand
 *   match. If '*' is given as $key, the table $table will be emptied.
 */
function cache_clear_all($key = NULL, $table = NULL, $wildcard = FALSE) {
  global $cache;
  global $user;

  if (!isset($cache)) {
    $cache = new CacheRouter();
  }

  if (!isset($key) && !isset($table)) {
    // Clear the block cache first, so stale data will
    // not end up in the page cache.
    $cache->flush('cache_block');
    $cache->flush('cache_page');
    return;
  }

  if (empty($key)) {
    if (variable_get('cache_lifetime', 0)) {
      // We store the time in the current user's $user->cache variable which
      // will be saved into the sessions table by sess_write(). We then
      // simulate that the cache was flushed for this user by not returning
      // cached data that was cached before the timestamp.
      $user->cache = time();

      $cache_flush = variable_get('cache_flush', 0);
      if ($cache_flush == 0) {
        // This is the first request to clear the cache, start a timer.
        variable_set('cache_flush', time());
      }
      else if (time() > ($cache_flush + variable_get('cache_lifetime', 0))) {
        // Clear the cache for everyone, cache_flush_delay seconds have
        // passed since the first request to clear the cache.
        $cache->flush($table);
        variable_set('cache_flush', 0);
      }
    }
    else {
      // No minimum cache lifetime, flush all temporary cache entries now.
      $cache->flush($table);
    }
  }
  else {
    if ($wildcard) {
      if ($key == '*' || empty($key)) {
        $cache->delete('*', $table);
      }
      else {
        $cache->delete($key .'*', $table);
      }
    }
    else {
      $key = str_replace('*', '', $key);
      $cache->delete($key, $table);
    }
  }
}

/**
 * Main callback from DRUPAL_BOOTSTRAP_EARLY_PAGE_CACHE phase
 */
if (!function_exists('page_cache_fastpath')) {
  function page_cache_fastpath() {
    global $base_root;
    global $cache;
  
    if (empty($_POST) && !isset($_COOKIE['DRUPAL_UID'])) {
      if (!isset($cache)) {
        $cache = new CacheRouter();
      }
      if ($cache->page_fast_cache('cache_page')) {
        $page = $cache->get($base_root . request_uri(), 'cache_page');
        if (!empty($page)) {
          drupal_page_header();
        
           //checking if first chars are compressed (always the same pattern for every header)
          if (substr($page->data, 0,3) == "\x1f\x8b\x08") {
          
            // Determine if the browser accepts gzipped data.
            if (@strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') === FALSE && function_exists('gzencode')) {
              // Strip the gzip header and run uncompress.
              $page->data = gzinflate(substr(substr($page->data, 10), 0, -8));
            }
            elseif (function_exists('gzencode')) {
              //send gzip header to the browser
              header('Content-Encoding: gzip');
            }
          }
        
          print $page->data;
        
          return TRUE;
        }
      }
    }
    return FALSE;
  }
}