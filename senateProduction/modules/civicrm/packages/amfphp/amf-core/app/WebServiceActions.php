<?php
/**
 * The nuSoap client implementation
 * 
 * @return mixed The web service results
 */
function webServiceAction_nusoap(&$amfbody, $webServiceURI, $webServiceMethod, $args, $phpInternalEncoding) {
	$installed = @include_once(AMFPHP_BASE . "lib/nusoap.php");
	if ($installed) {
		$soapclient = new soapclient($webServiceURI, 'wsdl'); // create a instance of the SOAP client object
		$soapclient->soap_defencoding = $phpInternalEncoding;
		if (count($args) == 1 && is_array($args)) {
			$result = $soapclient->call($webServiceMethod, $args[0]); // execute without the proxy
		} else {
			$proxy = $soapclient->getProxy();
			//
			$result = call_user_func_array(array($proxy, $webServiceMethod), $args);
		} 
		//echo $soapclient->getDebug();
		return $result;
	} else {
		trigger_error("nuSOAP is not installed correctly, it should be in lib/nusoap.php", E_USER_ERROR);
	}
} 

/**
 * The PEAR::SOAP client implementation
 * 
 * @return mixed The web service results
 */
function webServiceAction_pear(&$amfbody, $webServiceURI, $webServiceMethod, $args, $phpInternalEncoding) {
	$installed = @include_once "SOAP/Client.php"; // load the PEAR::SOAP implementation
	if ($installed) {
		$client = new SOAP_Client($webServiceURI);
		$response = $client->call($webServiceMethod, $args[0]);
		return $response;
	} else {
		trigger_error("PEAR::SOAP is not installed correctly", E_USER_ERROR);
	} 
} 

/**
 * PHP5 SOAP implementation
 */
function webServiceAction_php5(&$amfbody, $webServiceURI, $webServiceMethod, $args, $phpInternalEncoding)
{
	//Note that encoding is set to php internal encoding,
	//As SoapClient always sends and receives stuff in UTF-8 anyway
	if(class_exists('SoapClient'))
	{
		$client = new SoapClient($webServiceURI, array("exceptions" => 0, "trace" => 1, "encoding" => $phpInternalEncoding));
		$response = $client->__soapCall($webServiceMethod, $args[0]);
		if(is_soap_fault($response))
		{
			$ex = new AMFException(E_USER_ERROR, "SOAP error: " . $client->__getLastResponse(), __FILE__, __LINE__, "AMFPHP_SOAP_ERROR");
			AMFException::throwException($amfbody, $ex);
		}
		return $response;
	}
	else
	{
		$ex = new AMFException(E_USER_ERROR, "PHP5 SoapClient is not installed", __FILE__, __LINE__, "AMFPHP_SOAP_NOT_INSTALLED_ERROR");
		AMFException::throwException($amfbody, $ex);
	}
}
?>