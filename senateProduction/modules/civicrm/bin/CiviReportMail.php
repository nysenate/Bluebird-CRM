<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
 * A PHP cron script to mail the result set of specified report to the  
 * recipients mentioned for that report  
 */
class CiviReportMail { 
    function processReport( ) {
        $sendmail     = CRM_Utils_Request::retrieve( 'sendmail', 'Boolean', 
                                                     CRM_Core_DAO::$_nullObject, true, null, 'REQUEST' );
        $instanceId   = CRM_Utils_Request::retrieve( 'instanceId', 'Positive', 
                                                     CRM_Core_DAO::$_nullObject, true, null, 'REQUEST' );
        $resetVal     = CRM_Utils_Request::retrieve( 'reset', 'Positive',
                                                     CRM_Core_DAO::$_nullObject, true, null, 'REQUEST' );
              
        $optionVal    = CRM_Report_Utils_Report::getValueFromUrl( $instanceId );
        
        echo "Report Mail Triggered...<br />";
        require_once 'CRM/Core/OptionGroup.php';
        $templateInfo = CRM_Core_OptionGroup::getRowValues( 'report_template', $optionVal, 'value' );
        $obj = new CRM_Report_Page_Instance();            
        if ( strstr($templateInfo['name'], '_Form') ) {
            $instanceInfo = array( );
            CRM_Report_BAO_Instance::retrieve( array('id' => $instanceId), $instanceInfo );
            
            if ( ! empty($instanceInfo['title']) ) {
                $obj->assign( 'reportTitle', $instanceInfo['title'] );
            } else {
                $obj->assign( 'reportTitle', $templateInfo['label'] );
            }
            
            $wrapper = new CRM_Utils_Wrapper( );
            $arguments['urlToSession'] = array( array( 'urlVar'     => 'instanceId',
                                                       'type'       => 'Positive',
                                                       'sessionVar' => 'instanceId',
                                                       'default'    => 'null' ) );
            return $wrapper->run( $templateInfo['name'], null, $arguments );
        }
    }
  }

session_start();
require_once '../civicrm.config.php';
require_once 'CRM/Core/Config.php';
require_once 'CRM/Report/Page/Instance.php';
require_once 'CRM/Utils/Wrapper.php';

$config = CRM_Core_Config::singleton();

CRM_Utils_System::authenticateScript(true);

// load bootstrap to call hooks
require_once 'CRM/Utils/System.php';
CRM_Utils_System::loadBootStrap(  );

require_once 'CRM/Core/Lock.php';
$lock = new CRM_Core_Lock('CiviReportMail');

if ($lock->isAcquired()) {
    // try to unset any time limits
    if (!ini_get('safe_mode')) set_time_limit(0);

    //log the execution of script
    CRM_Core_Error::debug_log_message( 'CiviReportMail.php' );
    // if there are named sets of settings, use them - otherwise use the default (null)
    CiviReportMail::processReport();
    
 } else {
    throw new Exception('Could not acquire lock, another CiviReportMail process is running');
 }

$lock->release();

