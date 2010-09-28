<?php

// global exception handler

function reportExceptions ($code, $descr, $filename, $line)
{
	// obey error_level set by system/user
	if (!($code & error_reporting())) {
		return;
	}

	// build a new AMFObject
	$amfout = new AMFObject("");
	// init a new error info object
	$error = array();
	// pass the code
	$error["code"] = "AMFPHP_RUNTIME_ERROR";
	// pass the description
	$error["description"] = $descr;
	// pass the details
	$error["details"] = $filename;
	// pass the level
	$error["level"] = AMFException::getFriendlyError($code);
	// pass the line number
	$error["line"] = $line;
	
	// add the error object to the body of the AMFObject
	$amfbody = new AMFBody(NULL, $GLOBALS['amfphp']['lastMethodCall']);
	$amfbody->setResults($error);
	$amfout->addBody($amfbody);  
	
	// Add the trace headers we have so far while we're at it
	debugFilter($amfout);
	
	// create a new serializer
	$serializer = new AMFSerializer();
	
	// serialize the data
	$data = $serializer->serialize($amfout);

	// send the correct header
	header('Content-type: application/x-amf');
	// flush the amf data to the client.
	print($data);
	
	// kill the system after we find a single error
	exit;
}

set_error_handler("reportExceptions");

?>