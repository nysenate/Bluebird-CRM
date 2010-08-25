<?php
/**
 * Defines globals used throughout amfphp package for config options
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage app
 */

global $amfphp;

$amfphp = array();
$amfphp['error_level'] = E_ALL ^ E_NOTICE;
$amfphp['instanceName'] = NULL;
$amfphp['classPath'] = 'services/';
$amfphp['customMappingsPath'] = 'services/';
$amfphp['adapterMappings'] = array();
$amfphp['incomingClassMappings'] = array();
$amfphp['outgoingClassMappings'] = array();
$amfphp['webServiceMethod'] = 'php5';
$amfphp['disableDescribeService'] = false;
$amfphp['disableTrace'] = false;
$amfphp['disableDebug'] = false;
$amfphp['lastMethodCall'] = '/1';
$amfphp['isFlashComm'] = false;
$amfphp['classInstances'] = array();

?>