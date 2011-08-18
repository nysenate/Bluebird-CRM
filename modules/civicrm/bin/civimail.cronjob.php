<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
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
 * A PHP cron script to run the outstanding and scheduled CiviMail jobs
 * initiated by Owen Barton from a mailing sent by Lobo to crm-mail
 *
 * The structure of the file is set to mimiic soap.php which is a stand-alone
 * script and hence does not have any UF issues. You should be able to run
 * this script using a web url or from the command line
 */

function processQueue( &$config ) {
  require_once 'CRM/Core/BAO/MailSettings.php';
  if (CRM_Core_BAO_MailSettings::defaultDomain() == "FIXME.ORG") {
    CRM_Core_Error::fatal( ts( 'The <a href="%1">default mailbox</a> has not been configured. You will find <a href="%2">more info in our book</a>', array( 1 => CRM_Utils_System::url('civicrm/admin/mailSettings', 'reset=1'), 2=> "http://en.flossmanuals.net/civicrm/ch042_system-configuration/")));
  }

  //log the execution of script
  require_once 'CRM/Core/Error.php';
  CRM_Core_Error::debug_log_message( 'civimail.cronjob.php');
    
  // check if we are enforcing number of parallel cron jobs
  // CRM-8460
  $gotCronLock  = false;
  if ( $config->mailerJobsMax &&
       $config->mailerJobsMax > 1 ) {
      require_once 'CRM/Core/Lock.php';

      $lockArray = range( 1, $config->mailerJobsMax );
      shuffle( $lockArray );
      foreach ( $lockArray as $lockID ) {
          $cronLock = new CRM_Core_Lock( "civimail.cronjob.{$lockID}" );
          if ( $cronLock->isAcquired( ) ) {
              $gotCronLock = true;
              break;
          }
      }

      // exit here since we have enuf cronjobs running
      if ( ! $gotCronLock ) {
          CRM_Core_Error::debug_log_message( 'Returning early, since max number of cronjobs running' );
          return;
      }
  }

  // load bootstrap to call hooks
  require_once 'CRM/Mailing/BAO/Job.php';

  // Split up the parent jobs into multiple child jobs
  CRM_Mailing_BAO_Job::runJobs_pre($config->mailerJobSize);
  CRM_Mailing_BAO_Job::runJobs();
  CRM_Mailing_BAO_Job::runJobs_post();

  // lets release the global cron lock if we do have one
  if ( $gotCronLock ) {
      $cronLock->release( );
  }

}

function run( ) {
    session_start( );                               
                                            
    if (! function_exists( 'drush_get_context' ) ) {
        require_once '../civicrm.config.php'; 
    }

    require_once 'CRM/Core/Config.php'; 
    $config =& CRM_Core_Config::singleton(); 

    // this does not return on failure
    CRM_Utils_System::authenticateScript( true );

    require_once 'CRM/Utils/System.php';
    CRM_Utils_System::loadBootStrap(  );

    // we now use DB locks on a per job basis
    processQueue( $config );
}

// you can run this program either from an apache command, or from the cli
if ( php_sapi_name() == "cli" ) {
  require_once ("bin/cli.php");
  $cli=new civicrm_cli ();

  //if it doesn't die, it's authenticated 
  require_once 'CRM/Core/Config.php';
  $config =& CRM_Core_Config::singleton();

  processQueue( $config );

} else  { //from the webserver
  run( );
}

