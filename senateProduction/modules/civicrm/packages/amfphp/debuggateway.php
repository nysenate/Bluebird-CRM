<?php
/*
 * The debug gateway is a simple gateway that calls the real gateway and then checks if
 * the outgoing message is correctly formatted. If not, it wraps the message into a correctly 
 * formatted message, so that even fatal errors are caught. It is highly recommended
 * the the real gateway be called directly for production use.  
 * 
 * This gateway requires CURL to work properly
 */

//Guess gateway location (you may change this manually)
$path = str_replace('//','/',str_replace("%2F","/",str_replace('%5C', '/', rawurlencode (dirname($_SERVER['PHP_SELF'])))));
$gatewayUrl = 'http://' . $_SERVER['HTTP_HOST'] . $path . '/gateway.php';
$gatewayUrl = str_replace('//gateway', '/gateway', $gatewayUrl);
$sessionName = ini_get('session.name');
if(isset($_GET[$sessionName]))
{
	//Add session id
	$gatewayUrl .= '?' . $sessionName . '=' . $_GET[$sessionName];
}

$data = $GLOBALS['HTTP_RAW_POST_DATA'];

define('AMFPHP_BASE', realpath(dirname(__FILE__)) . "/amf-core/");
define('AMFPHP_CLIENT_BASE', realpath(dirname(__FILE__)) . "/browser/client/");
include_once(AMFPHP_CLIENT_BASE . 'AMFClient.php');
$client = new AMFClient($gatewayUrl);
$result = $client->doRequest($data);

if($data == NULL || $data == "")
{
	echo "<p>cURL and the debug gateway are installed correctly. You may now connect to the debug gateway from Flash.</p><p><a href='http://www.amfphp.org/docs'>View the amfphp documentation</p>";
	die();
}

if($result === FALSE)
{
	$result = $client->sendError($data, $client->getLastError());
}

$client->send($result);
?>
