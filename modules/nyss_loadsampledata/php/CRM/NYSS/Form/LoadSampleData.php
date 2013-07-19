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

    $setStart = CRM_Utils_Array::value('setStart', $_GET, 0);
    $setEnd = CRM_Utils_Array::value('setEnd', $_GET, 10);
    //CRM_Core_Error::debug_var('setStart',$setStart);
    //CRM_Core_Error::debug_var('setEnd',$setEnd);

    if ( $setStart == 0 ) {
      //CRM_Core_Error::debug_var('setStart sleep', $setStart);
      sleep(8);
    }

    $i = 0;
    $output = '';

    //process file
    $fhandle = fopen($logFile, "r");
    while (!feof($fhandle)) {
      $line = fgets($fhandle);
      $i++;
      //CRM_Core_Error::debug_var('i',$i);

      //skip ahead (inefficient)
      if ( $i < $setStart ) {
        continue;
      }

      $output .= str_replace("\n", '<br />', $line);
      //CRM_Core_Error::debug_var('output',$output);

      if ( $i > $setEnd ) {
        bbscript_log('info', "{$setEnd} contacts imported...");
        echo $output;
        sleep(3);

        fclose($fhandle);
        CRM_Utils_System::civiExit();
      }

      //safety to avoid runaway script
      if ( $i > 10000 ) {
        break;
      }
    }

    //print final output from partial set
    if ( !empty($output) ) {
      echo $output;
    }

    //final return to finish process
    bbscript_log('info', "Complete.");

    fclose($fhandle);
    CRM_Utils_System::civiExit();
  }//_getOutput
}
