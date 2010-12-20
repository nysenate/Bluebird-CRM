<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
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
 * Start of the Error framework. We should check out and inherit from
 * PEAR_ErrorStack and use that framework
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'PEAR/ErrorStack.php';
require_once 'PEAR/Exception.php';

require_once 'CRM/Core/Config.php';
require_once 'CRM/Core/Smarty.php';

require_once 'Log.php';

class CRM_Exception extends PEAR_Exception {
    // Redefine the exception so message isn't optional
    public function __construct($message = null, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}


class CRM_Core_Error extends PEAR_ErrorStack {

    /**
     * status code of various types of errors
     * @var const
     */
    const
        FATAL_ERROR = 2,
        DUPLICATE_CONTACT = 8001,
        DUPLICATE_CONTRIBUTION = 8002,
        DUPLICATE_PARTICIPANT = 8003;


    /**
     * We only need one instance of this object. So we use the singleton
     * pattern and cache the instance in this variable
     * @var object
     * @static
     */
    private static $_singleton = null;

    /**
     * The logger object for this application
     * @var object
     * @static
     */
    private static $_log       = null;

    /**
     * If modeException == true, errors are raised as exception instead of returning civicrm_errors
     * @static
     */
    public static $modeException = null;
    
    /**
     * singleton function used to manage this object. This function is not
     * explicity declared static to be compatible with PEAR_ErrorStack
     *  
     * @return object
     */
    function &singleton()
    {
        if (self::$_singleton === null ) {
            self::$_singleton = new CRM_Core_Error('CiviCRM');
        }
        return self::$_singleton;
    }
  
    /**
     * construcor
     */
    function __construct()
    {
        parent::__construct('CiviCRM');

        $log = CRM_Core_Config::getLog();
        $this->setLogger( $log );

        // set up error handling for Pear Error Stack
        $this->setDefaultCallback(array($this, 'handlePES'));
    }

    function getMessages( &$error, $separator = '<br />' ) {
        if ( is_a( $error, 'CRM_Core_Error' ) ) { 
            $errors = $error->getErrors( ); 
            $message = array( ); 
            foreach ( $errors as $e ) { 
                $message[] = $e['code'] . ':' . $e['message']; 
            } 
            $message = implode( $separator, $message ); 
            return $message;
        }
        return null;
    }

    function displaySessionError( &$error, $separator = '<br />' ) {
        $message = self::getMessages( $error, $separator );
        if ( $message ) {
            $status = ts( "Payment Processor Error message" ) . "{$separator}: $message"; 
            $session = CRM_Core_Session::singleton( ); 
            $session->setStatus( $status ); 
        }
    }

    /**
     * create the main callback method. this method centralizes error processing.
     *
     * the errors we expect are from the pear modules DB, DB_DataObject
     * which currently use PEAR::raiseError to notify of error messages.
     *
     * @param object PEAR_Error
     *
     * @return void
     * @access public
     */
    public static function handle( $pearError )
    {

        // setup smarty with config, session and template location.
        $template = CRM_Core_Smarty::singleton( );
        $config   = CRM_Core_Config::singleton( );

        if ( $config->backtrace ) {
            self::backtrace( );
        }

        // create the error array
        $error = array();
        $error['callback']    = $pearError->getCallback();
        $error['code']        = $pearError->getCode();
        $error['message']     = $pearError->getMessage();
        $error['mode']        = $pearError->getMode();
        $error['debug_info']  = $pearError->getDebugInfo();
        $error['type']        = $pearError->getType();
        $error['user_info']   = $pearError->getUserInfo();
        $error['to_string']   = $pearError->toString();
        if ( function_exists( 'mysql_error' ) &&
             mysql_error( ) ) {
            $mysql_error = mysql_error( ) . ', ' . mysql_errno( );
            $template->assign_by_ref( 'mysql_code', $mysql_error );

            // execute a dummy query to clear error stack
            mysql_query( 'select 1' );
        } else if ( function_exists( 'mysqli_error' ) ) {
            $dao  = new CRM_Core_DAO( );

            // we do it this way, since calling the function
            // getDatabaseConnection could potentially result
            // in an infinite loop
            global $_DB_DATAOBJECT;
            if ( isset( $_DB_DATAOBJECT['CONNECTIONS'][$dao->_database_dsn_md5] ) ) {
                $conn = $_DB_DATAOBJECT['CONNECTIONS'][$dao->_database_dsn_md5];
                $link = $conn->connection;

                if ( mysqli_error( $link ) ) {
                    $mysql_error = mysqli_error( $link ) . ', ' . mysqli_errno( $link );
                    $template->assign_by_ref( 'mysql_code', $mysql_error );
                    
                    // execute a dummy query to clear error stack
                    mysqli_query( $link, 'select 1' );
                }
            }
        }

        $template->assign_by_ref('error', $error);
        $errorDetails = CRM_Core_Error::debug( '', $error, false );
        $template->assign_by_ref('errorDetails', $errorDetails);
        
        CRM_Core_Error::debug_var( 'Fatal Error Details', $error );
        CRM_Core_Error::backtrace( 'backTrace', true );

        if ( $config->initialized ) {
            $content  = $template->fetch( 'CRM/common/fatal.tpl' );
            echo CRM_Utils_System::theme( 'page', $content, true );
        } else {
            echo "Sorry. A non-recoverable error has occurred. The error trace below might help to resolve the issue<p>";
            CRM_Core_Error::debug( null, $error );
        }

        self::abend(1);
    }

    /**
     * Handle errors raised using the PEAR Error Stack.
     *
     * currently the handler just requests the PES framework
     * to push the error to the stack (return value PEAR_ERRORSTACK_PUSH).
     *
     * Note: we can do our own error handling here and return PEAR_ERRORSTACK_IGNORE.
     *
     * Also, if we do not return any value the PEAR_ErrorStack::push() then does the
     * action of PEAR_ERRORSTACK_PUSHANDLOG which displays the errors on the screen,
     * since the logger set for this error stack is 'display' - see CRM_Core_Config::getLog();
     *
     */
    public static function handlePES($pearError)
    {
        return PEAR_ERRORSTACK_PUSH;
    }


    /**
     * display an error page with an error message describing what happened
     *
     * @param string message  the error message
     * @param string code     the error code if any
     * @param string email    the email address to notify of this situation
     *
     * @return void
     * @static
     * @acess public
     */
    static function fatal($message = null, $code = null, $email = null) {
        if ( ! $message ) {
            $message = ts('We experienced an unexpected error. Please post a detailed description and the backtrace on the CiviCRM forums: %1', array(1 => 'http://forum.civicrm.org/')); 
        }

        $vars = array( 'message' => $message,
                       'code'    => $code );

        $config = CRM_Core_Config::singleton( );

        if ( $config->fatalErrorHandler &&
             function_exists( $config->fatalErrorHandler ) ) {
            $name = $config->fatalErrorHandler;
            $ret = $name( $vars );
            if ( $ret ) {
                // the call has been successfully handled
                // so we just exit
                self::abend( CRM_Core_Error::FATAL_ERROR );
            }
        }

        if ( $config->backtrace ) {
            self::backtrace( );
        }

        $template = CRM_Core_Smarty::singleton( );
        $template->assign( $vars );

        CRM_Core_Error::debug_var( 'Fatal Error Details', $vars );
        CRM_Core_Error::backtrace( 'backTrace', true );
        $content = $template->fetch( $config->fatalErrorTemplate );
        echo CRM_Utils_System::theme( 'page', $content );
        // print $content;
        self::abend( CRM_Core_Error::FATAL_ERROR );
    }

    /**
     * outputs pre-formatted debug information. Flushes the buffers
     * so we can interrupt a potential POST/redirect
     *
     * @param  string name of debug section
     * @param  mixed  reference to variables that we need a trace of
     * @param  bool   should we log or return the output
     * @param  bool   whether to generate a HTML-escaped output
     *
     * @return string the generated output
     * @access public
     * @static
     */
    static function debug( $name, $variable = null, $log = true, $html = true ) {
        $error =& self::singleton( );

        if ( $variable === null ) {
            $variable = $name;
            $name = null;
        }
        
        $out = print_r( $variable, true );
        $prefix = null;
        if ($html) {
            $out = htmlspecialchars( $out );
            if ( $name ) {
                $prefix = "<p>$name</p>";
            }
            $out = "{$prefix}<p><pre>$out</pre></p><p></p>";
        } else {
            if ( $name ) {
                $prefix = "$name:\n";
            }
            $out = "{$prefix}$out\n";
        }
        if ( $log ) {
            echo $out;
        }

        return $out;
    }


    /**
     * Similar to the function debug. Only difference is 
     * in the formatting of the output.
     *
     * @param  string variable name
     * @param  mixed  reference to variables that we need a trace of
     * @param  bool   should we use print_r ? (else we use var_dump)
     * @param  bool   should we log or return the output
     *
     * @return string the generated output
     *
     * @access public
     *
     * @static
     *
     * @see CRM_Core_Error::debug()
     * @see CRM_Core_Error::debug_log_message()
     */
    static function debug_var($variable_name,
                              $variable,
                              $print = true,
                              $log   = true,
                              $comp  = '' )
    {
        // check if variable is set
        if(!isset($variable)) {
            $out = "\$$variable_name is not set";
        } else {
            if ( $print ) {
                $out = print_r($variable, true);
                $out = "\$$variable_name = $out";
            } else {
                // use var_dump
                ob_start();
                var_dump($variable);
                $dump = ob_get_contents();
                ob_end_clean();
                $out = "\n\$$variable_name = $dump";
            }
            // reset if it is an array
            if(is_array($variable)) {
                reset($variable);
            }
        }
        return self::debug_log_message($out, false, $comp );
    }
    
    /**
     * display the error message on terminal
     *
     * @param  string message to be output
     * @param  bool   should we log or return the output
     *
     * @return string format of the backtrace
     *
     * @access public
     *
     * @static
     */
    static function debug_log_message( $message, $out = false , $comp = '' )
    {
        $config = CRM_Core_Config::singleton( );

        if ( $comp ) {
            $comp = $comp . '.';
        }

        $fileName =
            "{$config->configAndLogDir}CiviCRM." . 
            $comp .
            md5( $config->dsn . $config->userFrameworkResourceURL ) .
            '.log';

        // Roll log file monthly or if greater than 256M
        // note that PHP file functions have a limit of 2G and hence
        // the alternative was introduce :)
        if ( file_exists( $fileName ) ) {
            $fileTime = date ("Ym", filemtime($fileName));
            $fileSize = filesize($fileName);
            if ( ( $fileTime < date('Ym') ) ||
                 ( $fileSize > 256 * 1024 * 1024 ) ||
                 ( $fileSize < 0 ) ) {
                rename( $fileName,
                        $fileName . '.' . date('Ymdhs', mktime(0, 0, 0, date("m")-1, date("d"), date("Y")) ) );
            }
        }

        $file_log = Log::singleton( 'file',$fileName );
        $file_log->log("$message\n");
        $str = "<p/><code>$message</code>";
        if ( $out ) {
            echo $str;
        }
        $file_log->close( );

        if ($config->userFrameworkLogging) {
            if ($config->userFramework == 'Drupal' and function_exists('watchdog')) {
                watchdog('civicrm', $message, NULL, WATCHDOG_DEBUG);
            }
        }

        return $str;
    }

    static function backtrace( $msg = 'backTrace', $log = false ) {
        $backTrace = debug_backtrace( );
        
        $msgs = array( );
        require_once 'CRM/Utils/Array.php';
        foreach ( $backTrace as $trace ) {
            $msgs[] = implode( ', ',
                               array( CRM_Utils_Array::value('file'    , $trace ),
                                      CRM_Utils_Array::value('function', $trace ),
                                      CRM_Utils_Array::value('line'    , $trace ) ) );
        }

        $message = implode( "\n", $msgs );
        if ( ! $log ) {
            CRM_Core_Error::debug( $msg, $message );
        } else {
            CRM_Core_Error::debug_var( $msg, $message );
        }
    }

    static function createError( $message, $code = 8000, $level = 'Fatal', $params = null ) {
        $error =& CRM_Core_Error::singleton( );
        $error->push( $code, $level, array( $params ), $message );
        return $error;
    }

    /**
     * Set a status message in the session, then bounce back to the referrer.
     *
     * @param string $status        The status message to set
     * @return void
     * @access public
     * @static
     */
    public static function statusBounce( $status, $redirect = null ) {
        $session = CRM_Core_Session::singleton();
        if ( ! $redirect ) {
            $redirect = $session->readUserContext();
        }
        $session->setStatus($status);
        CRM_Utils_System::redirect($redirect);
    }

    /**
     * Function to reset the error stack
     *
     * @access public
     * @static
     */
    public static function reset( ) 
    {
        $error =& self::singleton( );
        $error->_errors = array( ) ;
        $error->_errorsByLevel = array( ) ;
    }

    /* used for the API, rise the exception instead of catching/fatal it */
    public static function setRaiseException( ) {
        //deprecated        PEAR::setErrorHandling( PEAR_ERROR_EXCEPTION);
        PEAR::setErrorHandling( PEAR_ERROR_CALLBACK,  array( 'CRM_Core_Error', 'exceptionHandler' ) );
    }

    public static function ignoreException( $callback = null ) {
        if ( ! $callback ) {
            $callback = array( 'CRM_Core_Error', 'nullHandler' );
        }

        PEAR::setErrorHandling( PEAR_ERROR_CALLBACK,
                                $callback );
    }
    
    public static function exceptionHandler ($pearError) {
        $error = array();
        $error['code']        = $pearError->getCode();
        $error['message']     = $pearError->getMessage();
        $error['mode']        = $pearError->getMode();
        $error['debug_info']  = $pearError->getDebugInfo();
        $error['type']        = $pearError->getType();
        $error['user_info']   = $pearError->getUserInfo();
        $error['to_string']   = $pearError->toString();

        throw new PEAR_Exception($pearError->getMessage(),$pearError);   
    }
    
    
    /**
     * Error handler to quietly catch otherwise fatal smtp transport errors.
     *
     * @param object $obj       The PEAR_ERROR object
     * @return object $obj
     * @access public
     * @static
     */
    public static function nullHandler( $obj ) {
        CRM_Core_Error::debug_var( 'Ignoring exception thrown here', $obj );
        CRM_Core_Error::backtrace( 'backTrace', true );
        return $obj;
    }

    /**
     * (Re)set the default callback method
     *
     * @return void
     * @access public
     * @static
     */
    public static function setCallback( $callback = null ) {
        if ( ! $callback ) {
            $callback = array( 'CRM_Core_Error', 'handle' );
        }
        PEAR::setErrorHandling( PEAR_ERROR_CALLBACK, 
                                $callback );
    }

    public static function &createAPIError( $msg, $data = null ) {
        if (self::$modeException) {
          throw CRM_Exception ($msg,$data);
        }

        $values = array( );
        
        $values['is_error']      = 1;
        $values['error_message'] = $msg;
        if ( $data ) {
            $values['error_data']    = $data;
        }
        return $values;
    }

    public static function &createAPISuccess( $result = 1 ) {
        $values = array( );
        
        $values['is_error'] = 0;
        $values['result'  ] = $result;
        return $values;
    }
 
    public static function movedSiteError( $file ) {
        $url = CRM_Utils_System::url( 'civicrm/admin/setting/updateConfigBackend',
                                      'reset=1',
                                      true );
        echo "We could not write $file. Have you moved your site directory or server?<p>";
        echo "Please fix the setting by running the <a href=\"$url\">update config script</a>";
        exit( );
    }
    
    /**
     * Terminate execution abnormally
     */
    protected static function abend( $code ) {
        // do a hard rollback of any pending transactions
        // if we've come here, its because of some unexpected PEAR errors
        require_once 'CRM/Core/Transaction.php';
        CRM_Core_Transaction::forceRollbackIfEnabled( );
        CRM_Utils_System::civiExit( $code );
    }
}

PEAR_ErrorStack::singleton('CRM', false, null, 'CRM_Core_Error');


