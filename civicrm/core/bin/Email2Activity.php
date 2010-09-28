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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

define( 'EMAIL_ACTIVITY_TYPE_ID', 1 );
define( 'MAIL_DIR_DEFAULT'      , 'PUT YOUR MAIL DIR HERE' );

class bin_Email2Activity {

    protected $_mailDir;

    protected $_processedDir;

    protected $_errorDir;

    protected $_context;

    function __construct( $dir, $context = 'activity' ) {
        $this->_mailDir = $dir;
        $this->_context = strtolower($context);
        // create the error and processed directories
        // sort them by date
        $this->createDir( );
    }

    function createDir( ) {
        require_once 'CRM/Utils/File.php';

        // ensure that $this->_mailDir is a directory and is writable
        if ( ! is_dir( $this->_mailDir ) ||
             ! is_readable( $this->_mailDir ) ) {
            echo "Could not read from {$this->_mailDir}\n";
            exit( );
        }
        
        $config = CRM_Core_Config::singleton( );
        $dir = $config->uploadDir . DIRECTORY_SEPARATOR . 'mail';

        $this->_processedDir = $dir . DIRECTORY_SEPARATOR . 'processed';
        CRM_Utils_File::createDir( $this->_processedDir );

        $this->_errorDir     = $dir . DIRECTORY_SEPARATOR . 'error';
        CRM_Utils_File::createDir( $this->_errorDir );

        // create a date string YYYYMMDD
        require_once 'CRM/Utils/Date.php';
        $date = CRM_Utils_Date::getToday( null, 'Ymd' );

        $this->_processedDir = $this->_processedDir . DIRECTORY_SEPARATOR . $date;
        CRM_Utils_File::createDir( $this->_processedDir );

        $this->_errorDir = $this->_errorDir . DIRECTORY_SEPARATOR . $date;
        CRM_Utils_File::createDir( $this->_errorDir );
    }


    function run( ) {
        $directory = new DirectoryIterator( $this->_mailDir );

        $success = $error = 0;
        foreach ( $directory as $entry ) {
            if ( is_dir( $this->_mailDir . DIRECTORY_SEPARATOR . $entry ) ) {
                continue;
            }

            if ( $this->process( $entry ) ) {
                $success++;
            } else {
                $error++;
            }
        }

        echo "Successfully processed $success emails. Failed processing $error emails.";
        unset( $directory );
    }

    function process( $file ) {
        if ( $this->_context == 'activity' ) {
            require_once 'api/v2/Activity.php';
            $result = civicrm_activity_process_email( $this->_mailDir . DIRECTORY_SEPARATOR . $file,
                                                      EMAIL_ACTIVITY_TYPE_ID );
        } elseif ( $this->_context == 'case' ) {
            require_once 'CRM/Case/BAO/Case.php';
            $result = CRM_Case_BAO_Case::recordActivityViaEmail( $this->_mailDir . DIRECTORY_SEPARATOR . $file );
        } else {
            echo "Context not supported/set.\n";
            exit( );
        }

        if ( $result['is_error'] ) {
            rename( $this->_mailDir  . DIRECTORY_SEPARATOR . $file,
                    $this->_errorDir . DIRECTORY_SEPARATOR . $file );
            echo "Failed Processing: $file. Reason: {$result['error_message']}\n";
            return false;
        } else {
            rename( $this->_mailDir      . DIRECTORY_SEPARATOR . $file,
                    $this->_processedDir . DIRECTORY_SEPARATOR . $file );
            echo "Processed: $file\n";
            return true;
        }
    }

}
    

function run( $supportedArgs, $context ) {
    session_start( );

    require_once '../civicrm.config.php';
    require_once 'CRM/Core/Config.php'; 
    $config = CRM_Core_Config::singleton( );

    // this does not return on failure
    CRM_Utils_System::authenticateScript( true );

    //log the execution of script
    CRM_Core_Error::debug_log_message( 'Email2Activity.php' );
    
    // load bootstrap to call hooks
    require_once 'CRM/Utils/System.php';
    CRM_Utils_System::loadBootStrap(  );

    $mailDir = MAIL_DIR_DEFAULT;
    if ( isset( $_GET['mailDir'] ) ) {
        $mailDir = $_GET['mailDir'];
    }

    if ( $mailDir == 'PUT YOUR MAIL DIR HERE' ) {
        require_once 'CRM/Core/Error.php';
        CRM_Core_Error::fatal( );
    }

    if ( array_key_exists( 'context', $_GET ) && 
         isset($supportedArgs[strtolower($_GET['context'])]) ) {
        $context = $supportedArgs[strtolower($_GET['context'])];
    }

    $email = new bin_Email2Activity( $mailDir, $context );

    $email->run( );
}

// support command line arguements as well
$context       = 'activity';
$supportedArgs = array('case'     => 'case', 
                       '--case'   => 'case', 
                       '--case=1' => 'case');

if ( isset($argv[1]) ) {
    if ( isset( $supportedArgs[strtolower($argv[1])] ) ) {
        $context = $supportedArgs[strtolower($argv[1])];
    } else {
        echo "Context not supported.\n";
        exit( );
    }
}
run( $supportedArgs, $context );
