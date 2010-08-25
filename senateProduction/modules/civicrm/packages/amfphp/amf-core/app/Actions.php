<?php
/**
 * Actions modify the AMF message PER BODY
 * This allows batching of calls
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage filters
 * @version $Id: Filters.php,v 1.6 2005/04/02   18:37:51 pmineault Exp $
 */

include_once(AMFPHP_BASE . 'util/Authenticate.php');

/**
 * Catches any special request types and classifies as required
 */
function adapterAction (&$amfbody) {
	$baseClassPath = $GLOBALS['amfphp']['classPath'];

	$uriclasspath = "";
	$classname = "";
	$classpath = "";
	$methodname = "";
	$isWebServiceURI = false;

	$target = $amfbody->targetURI;
	
	if (strpos($target, "http://") === false && strpos($target, "https://") === false) { // check for a http link which means web service
		$lpos = strrpos($target, ".");
		if ($lpos === false) {
			// throw an error because there has to be atleast 1
			trigger_error('Malformed target: ' . $target, E_USER_ERROR);
			return false;
		} else {
			$methodname = substr($target, $lpos + 1);
		} 
		$trunced = substr($target, 0, $lpos);
		$lpos = strrpos($trunced, ".");
		if ($lpos === false) {
			$classname = $trunced;
			if ($classname == "PageAbleResult" && $methodname == 'getRecords') {
				$val = $amfbody->getValue();
				$id = $val[0];
				$keys = explode("=", $id);
				$currset = intval($keys[1]);
				
				$set = $_SESSION['amfphp_recordsets'][$currset];
				
				$uriclasspath = $set['class'];
				$classpath = $baseClassPath . $set['class'];
				$methodname = $set['method'];
				
				$classname = substr(strrchr('/' . $set['class'], '/'), 1, -4);
				
				//Now set args for body
				$amfbody->setValue(array_merge($set['args'], array($val[1], $val[2])));
				
				//Tell amfbody that this is a dynamic paged resultset
				$amfbody->setSpecialHandling('pageFetch');
			} 
			else if($classname == "PageAbleResult" && $methodname == 'release')
			{
				$amfbody->setSpecialHandling('pageRelease');
				$amfbody->noExec = true;
			}
			else {
				$uriclasspath = $trunced . ".php";
				$classpath = $baseClassPath . $trunced . ".php";
			} 
		} else {
			$classname = substr($trunced, $lpos + 1);
			$classpath = $baseClassPath . str_replace(".", "/", $trunced) . ".php"; // removed to strip the basecp out of the equation here
			$uriclasspath = str_replace(".", "/", $trunced) . ".php"; // removed to strip the basecp out of the equation here
		} 
	} else { // launch a web service and not a php service
		$amfbody->setSpecialHandling('ws');
		$amfbody->noExec = true;
		$rdot = strrpos($target, ".");
		$classpath = substr($target, 0, $rdot);
		$methodname = substr($target, $rdot + 1);
	} 

	$amfbody->classPath = $classpath;
	$amfbody->uriClassPath = $uriclasspath;
	$amfbody->className = $classname;
	$amfbody->methodName = $methodname;

	return true;
} 

/**
 * Class loader action loads the class from which we will get the remote method
 */
function classLoaderAction (&$amfbody) {
	 
	if(!$amfbody->noExec)
	{ 
		// change to the gateway.php script directory
		// now change to the directory of the classpath.  Possible relative to gateway.php
		$dirname = dirname($amfbody->classPath); 
		if(is_dir($dirname))
		{
			chdir($dirname);
		}
		else
		{
			$ex = new AMFException(E_USER_ERROR, "The classpath folder {" . $amfbody->classPath . "} does not exist. You probably misplaced your service." , __FILE__, __LINE__, "AMFPHP_CLASSPATH_NOT_FOUND");
			AMFException::throwException($amfbody, $ex);
			return false;
		}
	   
		$fileExists = @file_exists(basename($amfbody->classPath)); // see if the file exists
		if(!$fileExists)
		{
				$ex = new AMFException(E_USER_ERROR, "The class {" . $amfbody->className . "} could not be found under the class path {" . $amfbody->classPath . "}" , __FILE__, __LINE__, "AMFPHP_FILE_NOT_FOUND");
				AMFException::throwException($amfbody, $ex);
				return false;
		}
		
		$fileIncluded = Executive::includeClass($amfbody, "./" . basename($amfbody->classPath));
	
		if (!$fileIncluded) 
		{ 
			$ex = new AMFException(E_USER_ERROR, "The class file {" . $amfbody->className . "} exists but could not be included. The file may have syntax errors, or includes at the top of the file cannot be resolved.", __FILE__, __LINE__, "AMFPHP_FILE_NOT_INCLUDED");
			AMFException::throwException($amfbody, $ex);
			return false;
		}
		
		if (!class_exists($amfbody->className))
		{ // Just make sure the class name is the same as the file name
				$ex = new AMFException(E_USER_ERROR, "The file {" . $amfbody->className . ".php} exists and was included correctly but a class by that name could not be found in that file. Perhaps the class is misnamed.", __FILE__, __LINE__, "AMFPHP_CLASS_NOT_FOUND");
				AMFException::throwException($amfbody, $ex);
				return false;
		}

		//Let executive handle building the class
		//The executive can handle making exceptions and all that, that's why
		$classConstruct = Executive::buildClass($amfbody, $amfbody->className);

		if($classConstruct !== '__amfphp_error')
		{
			$amfbody->setClassConstruct($classConstruct);
		}
		else
		{
			return false;
		}
	}
	return true;
} 

/**
 * MetaDataAction loads the required info from the methodTable
 */
function metadataAction (&$amfbody) {
	if(!$amfbody->noExec)
	{
		$classConstruct = &$amfbody->getClassConstruct();
		$methodName = $amfbody->methodName;
		$className = $amfbody->className;
		
		if($methodName !== '_authenticate')
		{
			if (!isset($classConstruct->methodTable)) { // check to see if the methodTable exists
				$ex = new AMFException(E_USER_ERROR, "This class has no methodTable, therefore it cannot be run. Are you using include_once('" . $className . ".methodTable.php') instead of include (without the _once)? This would make the second call to the class fail.", __FILE__, __LINE__, "AMFPHP_NO_METHOD_TABLE");
				AMFException::throwException($amfbody, $ex);
				return false;
			} 
			if (!isset($classConstruct->methodTable[$methodName])) { // check to see if the methodTable exists
				$ex = new AMFException(E_USER_ERROR, "The method  {" . $methodName . "} was not declared in the meta data for class {" . $className . "}.", __FILE__, __LINE__, "AMFPHP_UNDECLARED_METHOD");
				AMFException::throwException($amfbody, $ex);
				return false;
			} 
			
			//Check if there is an alias
			if(isset($classConstruct->methodTable[$methodName]['alias']))
			{
				$alias = $classConstruct->methodTable[$methodName]['alias'];
				$amfbody->methodName = $alias;
				$methodName = $alias;
			}
		}
		
		//Check if method exists
		if (!method_exists($classConstruct, $methodName)) { // check to see if the method exists
			$ex = new AMFException(E_USER_ERROR, "The method  {" . $methodName . "} does not exist in class {" . $className . "}.", __FILE__, __LINE__, "AMFPHP_INEXISTANT_METHOD");
			AMFException::throwException($amfbody, $ex);
			return false;
		} 
	}
	return true;
}

/**
 * Security action checks that the caller has the credentials to run the remote methods
 */
function securityAction (&$amfbody) {
	$check = true;
	if(!$amfbody->noExec)
	{
		$classConstruct = &$amfbody->getClassConstruct();
		$methodName = $amfbody->methodName;
		$className = $amfbody->className;
		
		if ($methodName == "_authenticate") {
			if (method_exists($classConstruct, "_authenticate")) {
				$credentials = $amfbody->getValue();
				
				//Fix for error in _authenticate
				//Pass throught the executive
				$roles = Executive::doMethodCall($amfbody, 
											$classConstruct, 
											'_authenticate', 
											array($credentials['userid'], 
												  $credentials['password']));
				if ($roles !== '__amfphp_error' && $roles !== false && $roles !== "") {
					Authenticate::login($credentials['userid'], $roles);
					return false;
				} else {
					Authenticate::logout();
					return false;
				} 
			} else {
				$ex = new AMFException(E_USER_ERROR, "The _authenticate method was not found in the " . $className . " class", __FILE__, __LINE__, "AMFPHP_AUTHENTICATE_NOT_FOUND");
				AMFException::throwException($amfbody, $ex);
				return false;
			} 
		} 
		
		//else
		//Check for gateway restrictions
		$methodRecord = $classConstruct->methodTable[$methodName]; // create a shortcut for the ugly path
		$instanceName = $GLOBALS['amfphp']['instanceName'];
		if (isset($instanceName) && isset($methodRecord['instance'])) { // see if we have an instance defined
			if ( $instanceName != $methodRecord['instance']) { // if the names don't match die
				$ex = new AMFException(E_USER_ERROR, "The method {" . $methodName . "} instance name does not match this gateway's instance name.", __FILE__, __LINE__, "AMFPHP_INSTANCE_NAME_MISMATCH");
				AMFException::throwException($amfbody, $ex);
				return false;
			} 
		} else if (isset($methodRecord['instance'])) { // see if the method has an instance defined
			if ($instanceName != $methodRecord['instance']) { // if the names don't match die
				$ex = new AMFException(E_USER_ERROR, "The restricted method {" . $methodName . "} is not allowed through a non-restricted gateway.", __FILE__, __LINE__, "AMFPHP_INSTANCE_NAME_RESTRICTION");
				AMFException::throwException($amfbody, $ex);
				return false;
			} 
		} 
		
		if (!isset($methodRecord['access']) || (strtolower($methodRecord['access']) != "remote")) { // make sure we can remotely call it
			$ex = new AMFException(E_USER_ERROR, "ACCESS DENIED: The method {" . $methodName . "} has not been declared a remote method.", __FILE__, __LINE__, "AMFPHP_METHOD_NOT_REMOTE");
			AMFException::throwException($amfbody, $ex);
			return false;
		} 
		
		if (isset($methodRecord['roles']) && !Authenticate::isUserInRole($methodRecord['roles'])) {
				$ex = new AMFException(E_USER_ERROR, "This user is not does not have access to {" . $methodName . "}.", __FILE__, __LINE__, "AMFPHP_AUTH_MISMATCH");
				AMFException::throwException($amfbody, $ex);
				return false;
		} 
	}
	return true;
} 

/**
 * ExecutionAction executes the required methods
 */
function executionAction (&$amfbody) 
{
	$specialHandling = $amfbody->getSpecialHandling();

	if (!$amfbody->isSpecialHandling() || $amfbody->isSpecialHandling(array('describeService', 'pageFetch')))
	{
		$construct = &$amfbody->getClassConstruct();
		$method = $amfbody->methodName;
		$args = $amfbody->getValue();
		
		if(isset($construct->methodTable[$method]['fastArray']) &&
		   $construct->methodTable[$method]['fastArray'] == true)
		{
			$amfbody->setMetaData('fastArray', true);
		}
		
		if($specialHandling == 'describeService')
		{               
			include_once(AMFPHP_BASE . "util/DescribeService.php");
			$ds = new DescribeService();
			$results = $ds->describe($construct, $amfbody->className);
		}
		else if($specialHandling == 'pageFetch')
		{
			$args[count($args) - 2] = $args[count($args) - 2] - 1;
			
			$dataset = Executive::doMethodCall($amfbody, $construct, $method, $args);
			$results = array("cursor" => $args[count($args) - 2] + 1,
							 "data" => $dataset);
			$amfbody->setMetadata('type', '__DYNAMIC_PAGE__');
		}
		else
		{
			if(isset($construct->methodTable[$method]['pagesize']))
			{
				//Check if counting method was overriden
				if(isset($construct->methodTable[$method]['countMethod']))
				{
					$counter = $construct->methodTable[$method]['countMethod'];
				}
				else
				{
					$counter = $method . '_count';
				}
				
				$dataset = Executive::doMethodCall($amfbody, $construct, $method, $args); // do the magic
				$count = Executive::doMethodCall($amfbody, $construct, $counter, $args);
				
				//Include the wrapper
				$results = array('class' => $amfbody->uriClassPath, 
								 'method' => $amfbody->methodName, 
								 'count' => $count, 
								 "args" => $args, 
								 "data" => $dataset);
				$amfbody->setMetadata('type', '__DYNAMIC_PAGEABLE_RESULTSET__');
				$amfbody->setMetadata('pagesize', $construct->methodTable[$method]['pagesize']);
			}
			else
			{
				//The usual
				$results = Executive::doMethodCall($amfbody, $construct, $method, $args); // do the magic
			}
		}

		if($results !== '__amfphp_error')
		{
			$amfbody->setResults($results);
			if(isset($construct->methodTable[$method]['returns'])
				&& !isset($construct->methodTable[$method]['pagesize']))
			{
				$amfbody->setMetadata('type', $construct->methodTable[$method]['returns']);
			}
			$amfbody->responseURI = $amfbody->responseIndex . "/onResult";  
		}
		return false;
	}
	else if($specialHandling == 'pageRelease')
	{
		//Ignore PageAbleResult.release
		$amfbody->setResults(true);
		$amfbody->setMetaData('type', 'boolean');
		$amfbody->responseURI = $amfbody->responseIndex . "/onResult";
		return false;
	}
	return true;
}

/**
 * WebServiceAction calls a remote webservice instead of a regular method
 */
function webServiceAction (&$amfbody) {
	
	$method = $GLOBALS['amfphp']['webServiceMethod'];
	
	if ($amfbody->getSpecialHandling() == 'ws') {
		$args = &$amfbody->getValue();
		$webServiceURI = $amfbody->classPath;
		$webServiceMethod = $amfbody->methodName;
		$phpInternalEncoding = CharsetHandler::getPhpCharset();
		
		$functionName = "webServiceAction_$method";
		
		//Include web service actions
		include_once(AMFPHP_BASE . "app/WebServiceActions.php");
		$results = $functionName($amfbody, $webServiceURI, $webServiceMethod, $args, $phpInternalEncoding);
		if($results != '__amfphp_error')
		{
			$amfbody->setResults($results);
			$amfbody->responseURI = $amfbody->responseIndex . "/onResult";
		}
	}
	return false;
} 
?>