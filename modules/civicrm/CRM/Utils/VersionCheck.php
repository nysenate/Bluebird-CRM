<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id: $
 *
 */
class CRM_Utils_VersionCheck {
  // timeout for when the connection or the server is slow
  CONST LATEST_VERSION_AT = 'http://latest.civicrm.org/stable.php',
  // relative to $civicrm_root
  CHECK_TIMEOUT = 5, LOCALFILE_NAME = 'civicrm-version.php',
  // relative to $config->uploadDir
  CACHEFILE_NAME = 'latest-version-cache.txt',
  // cachefile expiry time (in seconds) - a week
  CACHEFILE_EXPIRE = 604800;

  /**
   * We only need one instance of this object, so we use the
   * singleton pattern and cache the instance in this variable
   *
   * @var object
   * @static
   */
  static private $_singleton = NULL;

  /**
   * The version of the current (local) installation
   *
   * @var string
   */
  var $localVersion = NULL;

  /**
   * The latest version of CiviCRM
   *
   * @var string
   */
  var $latestVersion = NULL;

  /**
   * Class constructor
   *
   * @access private
   */ function __construct() {
    global $civicrm_root;
    $config = CRM_Core_Config::singleton();

    $localfile = $civicrm_root . DIRECTORY_SEPARATOR . self::LOCALFILE_NAME;
    $cachefile = $config->uploadDir . self::CACHEFILE_NAME;

    if ($config->versionCheck &&
      file_exists($localfile)
    ) {
      require_once ($localfile);
      if (function_exists('civicrmVersion')) {
        $info = civicrmVersion();
        $this->localVersion = $info['version'];
      }
      $expiryTime = time() - self::CACHEFILE_EXPIRE;

      // if there's a cachefile and it's not stale use it to
      // read the latestVersion, else read it from the Internet
      if (file_exists($cachefile) and (filemtime($cachefile) > $expiryTime)) {
        $this->latestVersion = file_get_contents($cachefile);
      }
      else {
        // we have to set the error handling to a dummy function, otherwise
        // if the URL is not working (e.g., due to our server being down)
        // the users would be presented with an unsuppressable warning
        ini_set('default_socket_timeout', self::CHECK_TIMEOUT);
        set_error_handler(array('CRM_Utils_VersionCheck', 'downloadError'));
        $hash = md5($config->userFrameworkBaseURL);

        $url = self::LATEST_VERSION_AT . "?version={$this->localVersion}&uf={$config->userFramework}&hash=$hash&lang={$config->lcMessages}&ufv={$config->userFrameworkVersion}";

        // add PHP and MySQL versions
        $dao = new CRM_Core_DAO;
        $dao->query('SELECT VERSION() AS version');
        $dao->fetch();
        $url .= '&MySQL=' . $dao->version . '&PHP=' . phpversion();

        $tables = array(
          'CRM_Activity_DAO_Activity' => 'is_test = 0',
          'CRM_Case_DAO_Case' => NULL,
          'CRM_Contact_DAO_Contact' => NULL,
          'CRM_Contact_DAO_Relationship' => NULL,
          'CRM_Contribute_DAO_Contribution' => 'is_test = 0',
          'CRM_Contribute_DAO_ContributionPage' => 'is_active = 1',
          'CRM_Contribute_DAO_ContributionProduct' => NULL,
          'CRM_Contribute_DAO_Widget' => 'is_active = 1',
          'CRM_Core_DAO_Discount' => NULL,
          'CRM_Price_DAO_SetEntity' => NULL,
          'CRM_Core_DAO_UFGroup' => 'is_active = 1',
          'CRM_Event_DAO_Event' => 'is_active = 1',
          'CRM_Event_DAO_Participant' => 'is_test = 0',
          'CRM_Friend_DAO_Friend' => 'is_active = 1',
          'CRM_Grant_DAO_Grant' => NULL,
          'CRM_Mailing_DAO_Mailing' => 'is_completed = 1',
          'CRM_Member_DAO_Membership' => 'is_test = 0',
          'CRM_Member_DAO_MembershipBlock' => 'is_active = 1',
          'CRM_Pledge_DAO_Pledge' => 'is_test = 0',
          'CRM_Pledge_DAO_PledgeBlock' => NULL,
        );

        // add &key=count pairs to $url, where key is the last part of the DAO
        foreach ($tables as $daoName => $where) {
          require_once str_replace('_', '/', $daoName) . '.php';
          eval("\$dao = new $daoName;");
          if ($where) {
            $dao->whereAdd($where);
          }
          $url .= '&' . array_pop(explode('_', $daoName)) . "={$dao->count()}";
        }

        // get active payment processor types
        $dao = new CRM_Core_DAO_PaymentProcessor;
        $dao->is_active = 1;
        $dao->find();

        $ppTypes = array();
        while ($dao->fetch()) $ppTypes[] = $dao->payment_processor_type;

        // add the .-separated list of the processor types (urlencoded just in case)
        $url .= '&PPTypes=' . urlencode(implode('.', array_unique($ppTypes)));

        // get the latest version using the stats-carrying $url
        $this->latestVersion = file_get_contents($url);
        ini_restore('default_socket_timeout');
        restore_error_handler();

        if (!preg_match('/^\d+\.\d+\.\d+$/', $this->latestVersion)) {
          $this->latestVersion = NULL;
        }
        if (!$this->latestVersion) {
          return;
        }

        $fp = @fopen($cachefile, 'w');
        if (!$fp) {
          $message = ts('Do not have permission to write to file: %1',
            array(1 => $cachefile)
          );
          CRM_Core_Session::setStatus($message);
          return;
        }

        fwrite($fp, $this->latestVersion);
        fclose($fp);
      }
    }
  }

  /**
   * Static instance provider
   *
   * Method providing static instance of CRM_Utils_VersionCheck,
   * as in Singleton pattern
   *
   * @return CRM_Utils_VersionCheck
   */
  static
  function &singleton() {
    if (!isset(self::$_singleton)) {
      self::$_singleton = new CRM_Utils_VersionCheck();
    }
    return self::$_singleton;
  }

  /**
   * Get the latest version number if it's newer than the local one
   *
   * @return string|null  returns the newer version's number or null if the versions are equal
   */
  function newerVersion() {
    $local = explode('.', $this->localVersion);
    $latest = explode('.', $this->latestVersion);
    // compare by version part; this allows us to use trunk.$rev
    // for trunk versions ('trunk' is greater than '1')
    // we only do major / minor version comparison, so stick to 2
    // ignore 3.4 /4.0 comparison
    for ($i = 0; $i < 2; $i++) {
      if (CRM_Utils_Array::value($i, $local) > CRM_Utils_Array::value($i, $latest) OR
        (CRM_Utils_Array::value($i, $local) == 3 && CRM_Utils_Array::value($i + 1, $local) == 4 &&
          CRM_Utils_Array::value($i, $latest) == 4 && CRM_Utils_Array::value($i + 1, $latest) == 0
        )
      ) {
        return NULL;
      }
      elseif (CRM_Utils_Array::value($i, $local) < CRM_Utils_Array::value($i, $latest) and
        preg_match('/^\d+\.\d+\.\d+$/', $this->latestVersion)
      ) {
        return $this->latestVersion;
      }
    }
    return NULL;
  }

  /**
   * A dummy function required for suppressing download errors
   */
  static
  function downloadError($errorNumber, $errorString) {
    return;
  }
}

