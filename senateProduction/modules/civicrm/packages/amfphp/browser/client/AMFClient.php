<?php
/**
 * AMFClient is a class allowing the creating of AMF clients using cURL. Messages
 * can be encoded or decoded accordingly
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage util
 * @author Patrick Mineault
 * @version $Id$
 */

include_once(AMFPHP_BASE . 'app/Globals.php');
include_once(AMFPHP_BASE . 'app/Constants.php');
include_once(AMFPHP_BASE . 'io/AMFDeserializer.php');
include_once(AMFPHP_BASE . 'io/AMFSerializer.php');
include_once(AMFPHP_BASE . 'util/AMFBody.php');
include_once(AMFPHP_BASE . 'util/AMFObject.php');
include_once(AMFPHP_BASE . 'util/AMFHeader.php');
include_once(AMFPHP_BASE . 'util/CharsetHandler.php');
include_once(AMFPHP_BASE . 'util/Headers.php');
 
class AMFClient
{
	var $lastError;
	
	/**
	 * Constructor
	 * @param gatewayUrl The location of the remote gateway
	 */
	function AMFClient($gatewayUrl)
	{
		$this->gatewayUrl = $gatewayUrl;
		if (session_id()) {
		$this->gatewayUrl .= '?' . session_name() . '=' . session_id();
		}
	}
	
	/**
	 * createRequest takes the class, method, args and auth info and creates a ready to
	 * send AMF message
	 * 
	 * @param $class The name of the class
	 * @param $method The name of the remote method
	 * @param $args An array of arguments
	 * @param $username An optional username for Authentication
	 * @param $password An optional password for Authentication
	 * @returns A String representing the AMF data
	 */
	function createRequest($class, $method, $args, $username = "", $password = "")
	{
		$amf = new AMFObject("");
		
		//Create the body of the request
		$body = new AMFBody($class . '.' . $method, '/1', null, null, null, null);
		$body->setResults($args);
		$body->responseURI = $class . '.' . $method;
		$body->responseTarget = '/1';
		$amf->addBody($body);
		
		//Add authentication info
		if($username != "" || $password != "")
		{
			$header = new AMFHeader(AMFPHP_CREDENTIALS_HEADER, false, array('userid' => $username, 'password' => $password));
			$amf->addOutgoingHeader($header);
		}
		
		$serializer = new AMFSerializer(); // Create a serailizer around the output stream
		$contents = $serializer->serialize($amf); // serialize the data
		
		//go in and change the ÿÿÿÿ with actual length of message
		$loc = strpos($contents, 'ÿÿÿÿ');
		
		$serializer->outBuffer = "";
		$serializer->writeLong(strlen($contents) - $loc - 4);
		
		$contents = str_replace('ÿÿÿÿ', $serializer->outBuffer, $contents);
		return $contents;
	}
	
	/**
	 * Takes a chunk of AMF data and sends it to a remote server
	 * @param data A string of AMF data
	 * @returns The data on success, FALSE on error
	 */
	function doRequest($data)
	{
		//Close off session first
		session_write_close();
		if(function_exists('curl_init'))
		{
			return $this->doCurlRequest($data);
		}
		else
		{
			//Do it the PEAR way
			return $this->doPearRequest($data);
		}
		//Restart session
		session_start();
	}
	
	/**
	 * cURL is installed, do a cURL request
	 */
	function doCurlRequest($data)
	{
		//This portion taken from SabreAMF (released under LGPL)
		
		$error = NULL;
		$ch = curl_init($this->gatewayUrl);
		
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_TIMEOUT,20);
		curl_setopt($ch,CURLOPT_HTTPHEADER,array(AMFPHP_CONTENT_TYPE));
		curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
		curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
		
		$result = curl_exec($ch);
		
		if (curl_errno($ch)) {
			$this->lastError = 'CURL error: ' . curl_error($ch);
			return false;
		} else {
			curl_close($ch);
		}
		
		//Is the result valid so far?
		if($result[0] != chr(0))
		{
			//If chr(0) is not the first char, then this result is not good
			//Strip html
			$this->lastError = trim(strip_tags(str_replace('<td', " <td", $result)));
			return false;
		}
		return $result;
	}
	
	/**
	 * Do a PEAR Request instead
	 */
	function doPearRequest($data)
	{
		$oldDir = getcwd();
		chdir(dirname(__FILE__));
		include('HTTP/Request.php');
		chdir($oldDir);
		
		$error = NULL;
		$ch = &new HTTP_Request($this->gatewayUrl);
		
		$ch->setMethod(HTTP_REQUEST_METHOD_POST);
		$header = explode(': ', AMFPHP_CONTENT_TYPE);
		$ch->addHeader($header[0], $header[1]);
		$ch->setBody($data);
		
		$ch->sendRequest();
		
		$result = $ch->getResponseBody();
		
		//Is the result valid so far?
		if($result[0] != chr(0))
		{
			//If chr(0) is not the first char, then this result is not good
			//Strip html
			$this->lastError = trim(strip_tags(str_replace('<td', " <td", $result)));
			return false;
		}
		return $result;
	}
	
	/**
	 * Deserializes an AMF string into a PHP array
	 * @param $data A string containing AMF data
	 * @returns PHP array containing keys 'body' and 'trace'
	 */
	function deserialize($data)
	{
		$amf = new AMFObject($data); // create the amf object
		$deserializer = new AMFDeserializer($data);
		$deserializer->deserialize($amf);
		
		$firstBody = &$amf->getBodyAt(0);
		$traceHeader = $firstBody->getValue();
		
		if(is_array($traceHeader) && 
		   is_array($traceHeader[0]) && 
		   isset($traceHeader[0][0]['_explicitType']) && 
		   strtolower($traceHeader[0][0]['_explicitType']) == 'traceheader')
		{
			$trace = $traceHeader[0][0]['messages'];
			
			$bodies = array();
			for($i = 1; $i < $amf->numBody(); $i++)
			{
				$body = &$amf->getBodyAt($i);
				$bodies[] = &$body->getValue();
			}
		}
		else
		{
			$trace = array("No trace header found.");
			
			$bodies = array();
			for($i = 0; $i < $amf->numBody(); $i++)
			{
				$body = &$amf->getBodyAt($i);
				$bodies[] = &$body->getValue();
			}
		}
		
		return array('bodies' => $bodies, 'trace' => $trace);
	}
	
	/**
	 * Wraps a string into an error AMF message
	 * @param $data the original AMF data (needed to get the response index
	 * @param $error The error to send back
	 * @returns String containing the AMF data
	 */
	function sendError($data, $error)
	{
		//Get the last response index, otherwise the error will not register
		//In the NetConnection debugger 
		$amf = new AMFObject($data); // create the amf object
		$deserializer = new AMFDeserializer($data);
		$deserializer->deserialize($amf);
		
		$lastBody = &$amf->getBodyAt($amf->numBody() - 1);
		$lastIndex = $lastBody->responseIndex;

		// add the error object to the body of the AMFObject
		$amfout = new AMFObject(NULL);
		$amfbody = new AMFBody($lastIndex."/onStatus", $lastIndex);
		
		//Get line number
		preg_match("/in ([A-Za-z0-9\/\.\:]+) on line ([0-9]+)/", str_replace('\\', '/', $error), $matches);
		$file = $matches[1];
		$line = $matches[2];
		$level = substr($error, 0, strpos($error, ': '));
		
		
		$amfbody->setResults(array('description' => $error,
			'line' => $line,
			'file' => $file,
			'level' => $level,
			'code' => 'AMFPHP_DEBUG_ERROR'
			));
		$amfout->addBody($amfbody);  
		
		// create a new serializer
		$serializer = new AMFSerializer();
		
		// serialize the data
		$result = $serializer->serialize($amfout);

		return $result;
	}
	
	/**
	 * Gets the last error when using doRequest
	 */
	function getLastError()
	{
		return $this->lastError;
	}
	
	/**
	 * Prints an AMF string to STDIO with appropriate headers
	 */
	function send($result)
	{
		header(AMFPHP_CONTENT_TYPE);
		header("Content-length: " . strlen($result));
		$dateStr = date("D, j M Y ") . date("H:i:s", strtotime("-2 days"));
		header("Expires: $dateStr GMT");
		header("Pragma: no-store");
		header("Cache-Control: no-store");
		print($result);
	}
	
	/**
	 * Get the signature of the request maker
	 */
	function getRequestSignature()
	{
		if(function_exists('curl_init'))
		{
			$version = curl_version();
			if(is_array($version))
			{
				$version = $version['version'];
			}
			return 'cURL ' . $version;
		}
		else
		{
			return 'PEAR Net::Request 1.3.0';
		}
	}
}