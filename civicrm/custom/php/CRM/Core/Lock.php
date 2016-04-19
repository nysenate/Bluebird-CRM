<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
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
 * @copyright CiviCRM LLC (c) 2004-2013
 * $Id$
 *
 */
//NYSS 8629
class CRM_Core_Lock implements \Civi\Core\Lock\LockInterface
{
  static $jobLog = false; //NYSS 8629

  // lets have a 3 second timeout for now
  const TIMEOUT = 3;

  protected $_hasLock = false;
  protected $_name;
  protected $_id;

  //NYSS 8629
  /**
   * Use MySQL's GET_LOCK(). Locks are shared across all Civi instances
   * on the same MySQL server.
   *
   * @param string $name
   *   Symbolic name for the lock. Names generally look like
   *   "worker.mailing.EmailProcessor" ("{category}.{component}.{AdhocName}").
   *
   *   Categories: worker|data|cache|...
   *   Component: core|mailing|member|contribute|...
   * @return \Civi\Core\Lock\LockInterface
   */
  public static function createGlobalLock($name) {
    return new static($name, null, true);
  }

  /**
   * Use MySQL's GET_LOCK(), but apply prefixes to the lock names.
   * Locks are unique to each instance of Civi.
   *
   * @param string $name
   *   Symbolic name for the lock. Names generally look like
   *   "worker.mailing.EmailProcessor" ("{category}.{component}.{AdhocName}").
   *
   *   Categories: worker|data|cache|...
   *   Component: core|mailing|member|contribute|...
   * @return \Civi\Core\Lock\LockInterface
   */
  public static function createScopedLock($name) {
    return new static($name);
  }

  /**
   * Use MySQL's GET_LOCK(), but conditionally apply prefixes to the lock names
   * (if civimail_server_wide_lock is disabled).
   *
   * @param string $name
   *   Symbolic name for the lock. Names generally look like
   *   "worker.mailing.EmailProcessor" ("{category}.{component}.{AdhocName}").
   *
   *   Categories: worker|data|cache|...
   *   Component: core|mailing|member|contribute|...
   * @return \Civi\Core\Lock\LockInterface
   * @deprecated
   */
  public static function createCivimailLock($name) {
    $serverWideLock = \CRM_Core_BAO_Setting::getItem(
      \CRM_Core_BAO_Setting::MAILING_PREFERENCES_NAME,
      'civimail_server_wide_lock'
    );
    return new static($name, null, $serverWideLock);
  }

  /**
   * Initialize the constants used during lock acquire / release
   *
   * @param string  $name name of the lock. Please prefix with component / functionality
   *                      e.g. civimail.cronjob.JOB_ID
   * @param int     $timeout the number of seconds to wait to get the lock. 1 if not set
   * @param boolean $serverWideLock should this lock be applicable across your entire mysql server
   *                                this is useful if you have mutliple sites running on the same
   *                                mysql server and you want to limit the number of parallel cron
   *                                jobs - CRM-91XX
   *
   * @return object the lock object
   *
   */
  function __construct($name, $timeout = null, $serverWideLock = false) {
    $config = CRM_Core_Config::singleton();
    $dsnArray = DB::parseDSN($config->dsn);
    $database = $dsnArray['database'];
    $domainID = CRM_Core_Config::domainID();
    if ($serverWideLock) {
      $this->_name = $name;
    }
    else {
      $this->_name = $database . '.' . $domainID . '.' . $name;
    }

    // MySQL 5.7 restricts lock names to 64 characters.  The sha1() function
    // returns a 40-character hexadecimal string representation of the input.
    $this->_id = sha1($this->_name);
    if (defined('CIVICRM_LOCK_DEBUG')) {
      CRM_Core_Error::debug_log_message('trying to construct lock for '.$this->_name.' ('.$this->_id.')');
    }
    //NYSS 8629
    //static $jobLog = false;
    //if ($jobLog && CRM_Core_DAO::singleValueQuery("SELECT IS_USED_LOCK( '{$jobLog}')")) {
    //  return $this->hackyHandleBrokenCode($jobLog);
    //if (self::$jobLog && CRM_Core_DAO::singleValueQuery("SELECT IS_USED_LOCK( '" . self::$jobLog . "')")) {
    //  return $this->hackyHandleBrokenCode(self::$jobLog);
    //}
    //if (stristr($name, 'civimail.job.')) {
    //  $jobLog = $this->_name;
    //if (stristr($name, 'data.mailing.job.')) {
    //  self::$jobLog = $this->_name;
    //}
    //if (defined('CIVICRM_LOCK_DEBUG')) {
    //CRM_Core_Error::debug_var('backtrace', debug_backtrace());
    //}
    $this->_timeout = $timeout !== null ? $timeout : self::TIMEOUT;

    //$this->acquire();
  }

  function __destruct() {
    $this->release();
  }

  //NYSS 8629
  public function acquire($timeout = null) {
    /*if (defined('CIVICRM_LOCK_DEBUG')) {
      CRM_Core_Error::debug_log_message('acquire lock for ' . $this->_name);
    }*/
    if (!$this->_hasLock) {
      if (self::$jobLog && CRM_Core_DAO::singleValueQuery("SELECT IS_USED_LOCK('".$this->_id."')")) {
        return $this->hackyHandleBrokenCode(self::$jobLog);
      }

      $query = "SELECT GET_LOCK(%1, %2)";
      $params = array(
        1 => array($this->_id, 'String'),
        2 => array($timeout ? $timeout : $this->_timeout, 'Integer'),//NYSS 8629
      );
      $res = CRM_Core_DAO::singleValueQuery($query, $params);
      if ($res) {
        //NYSS 8629
        if (defined('CIVICRM_LOCK_DEBUG')) {
          CRM_Core_Error::debug_log_message('acquire lock for '.$this->_name.'('.$this->_id.')');
        }
        $this->_hasLock = true;
        if (stristr($this->_name, 'data.mailing.job.')) {
          self::$jobLog = $this->_name;
        }
      }
      //NYSS 8629
      else {
        if (defined('CIVICRM_LOCK_DEBUG')) {
          CRM_Core_Error::debug_log_message('failed to acquire lock for '.$this->_name.'('.$this->_id.')');
        }
      }
    }
    return $this->_hasLock;
  }

  //NYSS 8629
  public function release() {
    if ($this->_hasLock) {
      //NYSS 8629
      if (defined('CIVICRM_LOCK_DEBUG')) {
        CRM_Core_Error::debug_log_message('release lock for '.$this->_name.'('.$this->_id.')');
      }
      $this->_hasLock = false;

      if (self::$jobLog == $this->_name) {
        self::$jobLog = false;
      }

      $query = "SELECT RELEASE_LOCK(%1)";
      $params = array(1 => array($this->_id, 'String'));
      return CRM_Core_DAO::singleValueQuery($query, $params);
    }
  }

  function isFree() {
    $query = "SELECT IS_FREE_LOCK(%1)";
    $params = array(1 => array($this->_id, 'String'));
    return CRM_Core_DAO::singleValueQuery($query, $params);
  }

  function isAcquired() {
    return $this->_hasLock;
  }

  /**
   * CRM-12856 locks were originally set up for jobs, but the concept was extended to caching & groups without
   * understanding that would undermine the job locks (because grabbing a lock implicitly releases existing ones)
   * this is all a big hack to mitigate the impact of that - but should not be seen as a fix. Not sure correct fix
   * but maybe locks should be used more selectively? Or else we need to handle is some cool way that Tim is yet to write :-)
   * if we are running in the context of the cron log then we would rather die (or at least let our process die)
   * than release that lock - so if the attempt is being made by setCache or something relatively trivial
   * we'll just return TRUE, but if it's another job then we will crash as that seems 'safer'
   *
   * @param string $jobLog
   * @throws CRM_Core_Exception
   * @return boolean
   */
  function hackyHandleBrokenCode($jobLog) {
    if (stristr($this->_name, 'job')) {
      //NYSS 8629
      //throw new CRM_Core_Exception('lock aquisition for ' . $this->_name . 'attempted when ' . $jobLog . 'is not released');
      CRM_Core_Error::debug_log_message('lock acquisition for '.$this->_name.'('.$this->_id.')'.' attempted when '.$jobLog.' is not released');
      throw new CRM_Core_Exception('lock acquisition for '.$this->_name.'('.$this->_id.')'.' attempted when '.$jobLog.' is not released');
    }
    if (defined('CIVICRM_LOCK_DEBUG')) {
      CRM_Core_Error::debug_log_message('(CRM-12856) faking lock for '.$this->_name.'('.$this->_id.')');
    }
    $this->_hasLock = true;
    return true;
  }
}

