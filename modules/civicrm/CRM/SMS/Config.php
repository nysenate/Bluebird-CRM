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
 * Config handles all the run time configuration changes that the system needs to deal with.
 * Typically we'll have different values for a user's sandbox, a qa sandbox and a production area.
 * The default values in general, should reflect production values (minimizes chances of screwing up)
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'CRM/Core/Config.php';

class CRM_SMS_Config {

    /**
     * Username for the sms provider
     *
     * @var string
     */
    protected $smsUsername;

    /**
     * Password for the sms provider
     *
     * @var string
     */
    protected $smsPassword;

    /**
     * API ID for the sms provider
     *
     * @var string
     */
    protected $smsAPIID;

    /**
     * API Server for the sms provider
     *
     * @var string
     */
    protected $smsAPIServer;

    /** 
     * Class implementing the server protocol
     * 
     * @var string 
     */ 
    protected $smsClass;

    /**
     * Function to add additional config paramters to the core Config class
     * if CiviSMS is enabled
     *
     * Note that this config class prevent code bloat in the Core Config class,
     * however we declare all the variables assigned here, more for documentation
     * than anything else, at some point, we'll figure out how to extend a class
     * and properties dynamically in PHP (like Ruby)
     *
     * @param CRM_Core_Config (reference ) the system config object
     *
     * @return void
     * @static
     * @access public
     */
    static function add( &$config ) {

        $config->smsUsername  = null;
        $config->smsPassword  = null;
        $config->smsAPIID     = null;
        $config->smsAPIServer = null;
        $config->smsClass     = null;

        if ( defined( 'CIVICRM_SMS_USERNAME' ) ) {
            $config->smsUsername = CIVICRM_SMS_USERNAME;
        }

        if ( defined( 'CIVICRM_SMS_PASSWORD' ) ) {
            $config->smsPassword = CIVICRM_SMS_PASSWORD;
        }

        if ( defined( 'CIVICRM_SMS_APIID' ) ) {
            $config->smsAPIID = CIVICRM_SMS_APIID;
        }

        if ( defined( 'CIVICRM_SMS_APISERVER' ) ) {
            $config->smsAPIServer = CIVICRM_SMS_APISERVER;
        }

        if ( defined( 'CIVICRM_SMS_CLASS' ) ) {
            $config->smsClass = CIVICRM_SMS_CLASS;
        }
    }

    /**
     * verify that the needed parameters have been set of SMS to work
     *
     * @param CRM_Core_Config (reference ) the system config object
     *
     * @return boolean
     * @static
     * @access public
     */
    static function check( &$config ) {
        $requiredParameters = array( 'smsUsername', 'smsPassword', 'smsAPIID', 'smsAPIServer', 'smsClass' );
        return CRM_Core_Config::check( $config, $requiredParameters );
    }

}


