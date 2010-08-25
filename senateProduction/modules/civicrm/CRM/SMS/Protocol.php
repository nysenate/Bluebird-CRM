<?php

/* +----------------------------------------------------------------------+
 * | SMS_Clickatell                                                       |
 * +----------------------------------------------------------------------+
 * | Copyright (c) 2002-2005 Jacques Marneweck                            |
 * +----------------------------------------------------------------------+
 * | This source file is subject to version 3.0 of the PHP license,       |
 * | that is bundled with this package in the file LICENSE, and is        |
 * | available at through the world-wide-web at                           |
 * | http://www.php.net/license/3_0.txt.                                  |
 * | If you did not receive a copy of the PHP license and are unable to   |
 * | obtain it through the world-wide-web, please send a note to          |
 * | license@php.net so we can mail you a copy immediately.               |
 * +----------------------------------------------------------------------+
 * | Authors: Jacques Marneweck <jacques@php.net>                         |
 * | Authors: Donald A. Lobo <lobo@civicrm.org>
 * +----------------------------------------------------------------------+
 */

require_once 'PEAR.php';

/**
 * PHP Interface into the Clickatell API
 *
 * Made some pretty major changes. rewrote using our standards and also functionalized the code a bit
 *
 * @author	Jacques Marneweck <jacques@php.net>
 * @copyright	2002-2005 Jacques Marneweck
 * @license	http://www.php.net/license/3_0.txt	PHP License
 * @version	$Id: Clickatell.php,v 1.31 2005/12/18 14:24:08 jacques Exp $
 * @access	public
 * @package	SMS
 */

abstract class CRM_SMS_Protocol {

    /**
     * We only need one instance of this object. So we use the singleton
     * pattern and cache the instance in this variable
     *
     * @var object
     * @static
     */
    static private $_singleton = null;

    /**  
     * singleton function used to manage this object  
     *  
     * @return object  
     * @static  
     *  
     */  
    static function &singleton( ) {
        if (self::$_singleton === null ) {
            $config   = CRM_Core_Config::singleton( );
            
            $classPath = str_replace( '_', '/', $config->smsClass ) . '.php';
            require_once($classPath);
            self::$_singleton = eval( 'return ' . $config->smsClass . '::singleton( $mode );' );
        }
        return self::$_singleton;
    }

	/**
	 * Delete message queued by  which has not been passed
	 * onto the SMSC.
	 *
	 * @param	string	$id
	 * @access	public
	 */
	abstract function deleteMessage ( $id );

	/**
	 * Query balance of remaining SMS credits
	 *
	 * @access	public
	 */
	abstract function getBalance ();

	/**
	 * Determine the cost of the message which was sent
	 *
	 * @param	string	$id
     *
	 * @access  public
	 */
	abstract function getMessageCharge( $id );

	/**
	 * Keep our session to the API Server valid.
	 *
	 * @access public
	 */
	abstract function ping ();

	/**
	 * Query message status
	 *
	 * @access public
	 */
	abstract function queryMessage ($id);

	/**
	 * Send an SMS Message via the API Server
	 *
	 * @param array the message with a to/from/text
	 *
	 * @access public
	 */
	abstract function sendMessage ( &$message );

	/**
	 * Spend a voucher which can be used for topping up of
	 * sub user accounts.
	 *
	 * @param	string	voucher number
	 * @access	public
	 */
	abstract function pay ($voucher);
}
