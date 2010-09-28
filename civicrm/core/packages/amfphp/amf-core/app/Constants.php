<?php
/**
 * Defines constants used throughout amfphp package
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage app
 */

/**
 * The Service browser header
 */
define("AMFPHP_SERVICE_BROWSER_HEADER", "DescribeService");
/**
 * The Credentials header string
 */
define("AMFPHP_CREDENTIALS_HEADER", "Credentials");
/**
 * The cleared credentials string
 */
define("AMFPHP_CLEARED_CREDENTIALS", "AMFPHP_CLEARED_CREDENTIALS");
/**
 * The Debugging header string
 */
define("AMFPHP_DEBUG_HEADER", "amf_server_debug");
/**
 * The success method name
 */
define("AMFPHP_CLIENT_SUCCESS_METHOD", "/onResult");
/**
 * The status method name
 */
define("AMFPHP_CLIENT_FAILURE_METHOD", "/onStatus");
/**
 * The rewrite header method name
 */
define("AMFPHP_CLIENT_REWRITE_HEADER", "ReplaceGatewayUrl");
/**
 * The Content Type String
 */
define("AMFPHP_CONTENT_TYPE", "Content-type: application/x-amf");
/**
 * The Content Type String
 */
define("AMFPHP_PHP5", PHP_VERSION >= 5 ? true : false);
/**
 * The Content Type String
 */
$tmp = pack("d", 1); // determine the multi-byte ordering of this machine temporarily pack 1
define("AMFPHP_BIG_ENDIAN", $tmp == "\0\0\0\0\0\0\360\77");

?>
