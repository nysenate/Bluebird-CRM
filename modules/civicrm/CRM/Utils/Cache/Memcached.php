<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */
class CRM_Utils_Cache_Memcached {

  /**
   * The host name of the memcached server
   *
   * @var string
   */
  protected $_host;

  /**
   * The port on which to connect on
   *
   * @var int
   */
  protected $_port;

  /**
   * The default timeout to use
   *
   * @var int
   */
  protected $_timeout;

  /**
   * The actual memcache object
   *
   * @var resource
   */
  protected $_cache;

  /**
   * The prefix prepended to cache keys.
   *
   * If we are using the same memcache instance for multiple CiviCRM
   * installs, we must have a unique prefix for each install to prevent
   * the keys from clobbering each other.
   *
   * @var string
   */
  protected $_prefix;

  /**
   * Constructor
   *
   * @param string  $host      the memcached server host
   * @param int     $port      the memcached server port
   * @param int     $timeout   the default timeout
   * @param string  $prefix    the prefix prepended to a cache key
   *
   * @return void
   */
  function __construct($host = 'localhost',
    $port    = 11211,
    $timeout = 3600,
    $prefix  = ''
  ) {
    $this->_host    = $host;
    $this->_port    = $port;
    $this->_timeout = $timeout;
    $this->_prefix  = $prefix;

    $this->_cache = new Memcached();

    if (!$this->_cache->addServer($this->_host, $this->_port)) {
      // dont use fatal here since we can go in an infinite loop
      echo 'Could not connect to Memcached server';
      CRM_Utils_System::civiExit();
    }
  }

  function set($key, &$value) {
    $key = preg_replace('/\s+|\W+/', '_', $this->_prefix . $key);
    if (!$this->_cache->set($key, $value, $this->_timeout)) {
      CRM_Core_Error::debug( 'Result Code: ', $this->_cache->getResultMessage());
      CRM_Core_Error::fatal("memcached set failed, wondering why?, $key", $value );
      return FALSE;
    }
    return TRUE;
  }

  function &get($key) {
    $key = preg_replace('/\s+|\W+/', '_', $this->_prefix . $key);
    $result = $this->_cache->get($key);
    return $result;
  }

  function delete($key) {
    $key = preg_replace('/\s+|\W+/', '_', $this->_prefix . $key);
    return $this->_cache->delete($key);
  }

  function flush() {
    return $this->_cache->flush();
  }
}
