<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
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

require_once 'CRM/Core/Form.php';

/**
 * This class generates form components for DedupeRules
 *
 */
class CRM_NYSS_Form_LoadSampleData extends CRM_Core_Form
{

  /**
   * Function to pre processing
   *
   * @return None
   * @access public
   */
  function preProcess() {
    $bbcfg = get_bluebird_instance_config();
    //CRM_Core_Error::debug_var('bbcfg',$bbcfg);

    //TODO allowable instances should be retrieved from bluebird.cfg
    $allowedInstances = array(
      'demo',
      'sample',
      'sd99',
      'training1',
      'training2',
      'training3',
      'training4',
    );

    if ( !in_array($bbcfg['shortname'], $allowedInstances) ) {
      $url = CRM_Utils_System::url( 'civicrm','reset=1' );
      CRM_Core_Error::statusBounce( 'This instance is locked and may not have contacts purged and sample data loaded.', $url );
    }
  }

  /**
   * Function to build the form
   *
   * @return None
   * @access public
   */
  public
  function buildQuickForm() {
  }

  /**
   * Function to process the form
   *
   * @access public
   * @param output_status Determines if the status will be output.  This should
   *                      be set to false when running from the CLI.
   * @return None
   */
  static
  function loadData() {
    $sTime = microtime(TRUE);

    //get script
    $bbcfg = get_bluebird_instance_config();
    $script = $bbcfg['app.rootdir'].'/civicrm/scripts/importSampleData.php';

    $config = CRM_Core_Config::singleton();
    $logFile = $config->configAndLogDir.'loadSample_output.log';

    //truncate the log file before running the script
    $f = fopen($logFile, 'w');
    fclose($f);

    //run script
    exec("php $script -S {$bbcfg['shortname']} --system --purge --log=info 1>{$logFile}");

    $eTime = microtime(TRUE);
    $diffTime = ($eTime - $sTime)/60;

    //return processing time
    echo $diffTime;
    CRM_Utils_System::civiExit();
  }

  static
  function getOutput() {
    $config = CRM_Core_Config::singleton();
    $logFile = $config->configAndLogDir.'loadSample_output.log';

    //$i = 0;
    $output = '';
    $finished = FALSE;

    //process file
    $fhandle = fopen($logFile, "r");
    while (!feof($fhandle)) {
      $line = fgets($fhandle);
      $i++;
      //CRM_Core_Error::debug_var('i',$i);

      $output .= str_replace("\n", '</p><p>', $line);
      //CRM_Core_Error::debug_var('output',$output);

      if ( strpos($line, 'Completed instance cleanup') !== FALSE ) {
        $finished = TRUE;
      }

      sleep(5);
    }

    //print final output from partial set
    if ( !empty($output) ) {
      echo $output;
    }

    if ( $finished ) {
      echo 'SCRIPTCOMPLETE';
    }

    fclose($fhandle);
    CRM_Utils_System::civiExit();
  }//_getOutput
}
