<?php
/**
 * AMFSerializer manages the job of translating PHP objects into
 * the actionscript equivalent via amf.  The main method of the serializer
 * is the serialize method which takes and AMFObject as it's argument
 * and builds the resulting amf body.
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage io
 * @version $Id: AMFSerializer.php,v 1.39 2005/07/22 10:58:11 pmineault Exp $
 */

class AMFSerializer {

	/**
	 * The pagesize of a recordset
	 * 
	 * @access private 
	 * @var int 
	 */
	var $pagesize;
	
	/**
	 * Paging information of a dynamic recordset
	 */
	var $paging = -1;
	
	/**
	 * Classes that are serialized as recordsets
	 */                         
   var $serializedObjects = array();
   
   var $outBuffer;

	/**
	 * AMFSerializer is the constructor function.  You must pass the
	 * method an AMFOutputStream as the single argument.
	 * 
	 * @param object $stream The AMFOutputStream
	 */
	function AMFSerializer() {
		$this->isBigEndian = AMFPHP_BIG_ENDIAN;
		$this->outBuffer = ""; // the buffer
		$this->charsetHandler = new CharsetHandler('phptoflash');
	} 

	/**
	 * serialize is the run method of the class.  When serialize is called
	 * the AMFObject passed in is read and converted into the amf binary
	 * representing the PHP data represented.
	 * 
	 * @param object $d the AMFObject to serialize
	 */
	function serialize(&$d) {
		$this->resourceObjects = $GLOBALS['amfphp']['adapterMappings'];
		$this->amfout = &$d;
		$this->writeInt(0); //  write the version ???
		$count = $this->amfout->numOutgoingHeader();
		$this->writeInt($count); // write header count
		for ($i = 0; $i < $count; $i++) {
			//write headers
			$header = &$this->amfout->getOutgoingHeaderAt($i);
			$this->writeUTF($header->name);
			$this->writeByte(0);
			$this->writeLong(-1);
			$this->writeData($header->value, -1);
		} 
		$count = $this->amfout->numBody();
		$this->writeInt($count); // write the body  count
		for ($i = 0; $i < $count; $i++) {
			//write body
			$body = &$this->amfout->getBodyAt($i);
			//if (!$body->getSpecialHandling()) {
				$this->currentBody = & $body;
				$this->pagesize = $body->getMetadata('pagesize'); // save pagesize
				$this->writeUTF($body->responseURI); // write the responseURI header
				$this->writeUTF($body->responseTarget); //  write null, haven't found another use for this
				$this->writeLong(-1); // always, always there is four bytes of  FF, which is -1 of course
				list($type, $subtype) = $this->sanitizeType($body->getMetadata('type'));
				$this->writeData($body->getResults(), $type, $subtype, $body->getMetadata('type')); // write the data to the output stream
			//}
		} 
		
		return $this->outBuffer;
	} 

	/**
	 * writeBoolean writes the boolean code (0x01) and the data to the output stream
	 * 
	 * @param bool $d The boolean value
	 */
	function writeBoolean($d) {
		$this->writeByte(1); // write the boolean flag
		$this->writeByte($d); // write  the boolean byte
	} 

	/**
	 * writeString writes the string code (0x02) and the UTF8 encoded
	 * string to the output stream.
	 * Note: strings are truncated to 64k max length. Use XML as type 
	 * to send longer strings
	 * 
	 * @param string $d The string data
	 */
	function writeString($d) {
		$count = strlen($d);
		if($count < 65536)
		{
			$this->writeByte(2);
			$this->writeUTF($d);
		}
		else
		{
			$this->writeByte(12);
			$this->writeLongUTF($d);
		}
	}
	
	/**
	 * writeBinary writes the string code (0x02) and the binary string to
	 * the output stream
	 * 
	 * @param string $d The string data
	 */
	function writeBinaryString($d) {
		$this->writeByte(2); // write the string code
		$this->writeBinary($d); //  write the string value
	}
	
	/**
	 * writeRaw writes directly to the output stream.
	 * 
	 * @param string $d The raw data
	 */
	function writeRaw($d) {
		$this->outBuffer .= $d  ;
	}

	/**
	 * writeXML writes the xml code (0x0F) and the XML string to the output stream
	 * Note: strips whitespace
	 * @param string $d The XML string
	 */
	function writeXML($d) {
		$this->writeByte(15);
		$this->writeLongUTF(preg_replace('/\>(\n|\r|\r\n| |\t)*\</','><',trim($d)));
	} 

	/**
	 * writeData writes the date code (0x0B) and the date value to the output stream
	 * 
	 * @param date $d The date value
	 */
	function writeDate($d) {
		$this->writeByte(11); // write  date code
		$this->writeDouble($d); //  write date (milliseconds from 1970)
		/**
		 * write timezone
		 * ?? this is wierd -- put what you like and it pumps it back into flash at the current GMT ??
		 * have a look at the amf it creates...
		 */
		$this->writeInt(0);
	}

	/**
	 * writeNumber writes the number code (0x00) and the numeric data to the output stream
	 * All numbers passed through remoting are floats.
	 * 
	 * @param int $d The numeric data
	 */
	function writeNumber($d) {
		$this->writeByte(0); // write the number code
		$this->writeDouble(floatval($d)); // write  the number as a double
	} 

	/**
	 * writeNull writes the null code (0x05) to the output stream
	 */
	function writeNull() {
		$this->writeByte(5); // null is only a  0x05 flag
	} 

	/**
	 * writeArray first deterines if the PHP array contains all numeric indexes
	 * or a mix of keys.  Then it either writes the array code (0x0A) or the
	 * object code (0x03) and then the associated data.
	 * 
	 * @param array $d The php array
	 */
	function writeArray($d) 
	{
		$numeric = array(); // holder to store the numeric keys
		$string = array(); // holder to store the string keys
		$len = count($d); // get the total number of entries for the array
		foreach($d as $key => $data) { // loop over each element
			if (is_int($key) && ($key >= 0)) { // make sure the keys are numeric
				$numeric[$key] = $data; // The key is an index in an array
			} else {
				$string[$key] = $data; // The key is a property of an object
			} 
		} 
		$num_count = count($numeric); // get the number of numeric keys
		$str_count = count($string); // get the number of string keys

		if ($num_count > 0 && $str_count > 0) { // this is a mixed array
			
			$this->writeByte(8); // write the mixed array code
			$this->writeLong($num_count); // write  the count of items in the array
			$this->writeObject($numeric + $string); // write the numeric and string keys in the mixed array
		} else if ($num_count > 0) { // this is just an array
			
			$this->array_empty_fill($numeric); // null fill the empty slots to preseve sparsity
			$num_count = count($numeric); // get the new count
			
			$this->writeByte(10); // write  the array code
			$this->writeLong($num_count); // write  the count of items in the array
			for($i = 0 ; $i < $num_count ; $i++) { // write all of the array elements
				$this->writeData($numeric[$i]);
			} 
		} else if($str_count > 0) { // this is an object
			$this->writeByte(3); // this is an  object so write the object code
			$this->writeObject($string); // write the object name/value pairs
		} else { //Patch submitted by Jason Justman
			$this->writeByte(10); // make this  an array still
			$this->writeInt(0); //  give it 0 elements
			$this->writeInt(0); //  give it an element pad, this looks like a bug in Flash,
											//but keeps the next alignment proper
		}
	}
	
	/**
	 * Write a plain numeric array without anything fancy
	 */
	function writePlainArray($d, $type = -1)
	{
		$num_count = count($d);
		$this->writeByte(10); // write  the mixed array code
		$this->writeLong($num_count); // write  the count of items in the array
		list($type, $subtype) = $this->sanitizeType($type);
		for($i = 0 ; $i < $num_count ; $i++) { // write all of the array elements
			$this->writeData($d[$i], $type, $subtype);
		} 
	}
	
	/**
	 * array_empty_fill fills in all of the empty numeric slots with null to preserve the
	 * indexes of a sparse array.
	 */
	function array_empty_fill(&$array, $fill = null) {
		$indexmax = -1;
		for (end($array); $key = key($array); prev($array)) { // loop over the array
			if (is_int($key)) { // if the key is an integer
				if ($key > $indexmax) { // is this key greater than the previous max
					$indexmax = $key; // save this key as the high
				} 
			} 
		} 
		for ($i = 0; $i <= $indexmax; $i++) { // loop over all possible indexes from 0 to max
			if (!isset($array[$i])) { // is it set already
				$array[$i] = $fill; // fill it with the $fill value
			} 
		} 
		ksort($array); // resort the keys
		reset($array); // reset the pointer to the beginning
	} 

	/**
	 * writeObject handles writing a php array with string or mixed keys.  It does
	 * not write the object code as that is handled by the writeArray and this method
	 * is shared with the CustomClass writer which doesn't use the object code.
	 * 
	 * @param array $d The php array with string keys
	 */
	function writeObject($d) {
		foreach($d as $key => $data) { // loop over each element
			$this->writeUTF($key);  // write the name of the object
			$this->writeData($data); // write the value of the object
		} 
		$this->writeInt(0); //  write the end object flag 0x00, 0x00, 0x09
		$this->writeByte(9);
	} 
	
	/**
	 * writeObject handles writing a php array with string or mixed keys.  It does
	 * not write the object code as that is handled by the writeArray and this method
	 * is shared with the CustomClass writer which doesn't use the object code.
	 * 
	 * @param array $d The php array with string keys
	 */
	function writeTypedObject($d, $type = -1) {
		list($type, $subtype) = $this->sanitizeType($type);
		foreach($d as $key => $data) { // loop over each element
			$this->writeUTF($key);  // write the name of the object
			$this->writeData($data, $type, $subtype); // write the value of the object
		} 
		$this->writeInt(0); //  write the end object flag 0x00, 0x00, 0x09
		$this->writeByte(9);
	} 
	
	/**
	 * writeObject handles writing a php array with string or mixed keys.  It does
	 * not write the object code as that is handled by the writeArray and this method
	 * is shared with the CustomClass writer which doesn't use the object code.
	 * 
	 * @param array $d The php array with string keys
	 */
	function writeStdClassObject($d) {
		$this->writeByte(3);
		$objVars = (array) $d;
		foreach($d as $key => $data) { // loop over each element
			if($key[0] != "\0")
			{
				$this->writeUTF($key);  // write the name of the object
				$this->writeData($data); // write the value of the object
			}
		} 
		$this->writeInt(0); //  write the end object flag 0x00, 0x00, 0x09
		$this->writeByte(9);
	} 

	/**
	 * writePHPObject takes an instance of a class and writes the variables defined
	 * in it to the output stream.
	 * To accomplish this we just blanket grab all of the object vars with get_object_vars
	 * 
	 * @param object $d The object to serialize the properties
	 */
	function writePHPObject($d) {
		$sr = serialize($d);
		if(in_array($sr, $this->serializedObjects))
		{
			//Circular reference
			$this->writeString("[Circular reference]");
			return;
		}
		$this->serializedObjects[] = $sr;
		$this->writeByte(16); // write  the custom class code
		$classname = get_class($d);
		if(isset($GLOBALS['amfphp']['outgoingClassMappings'][strtolower($classname)]))
		{
			$classname = $GLOBALS['amfphp']['outgoingClassMappings'][strtolower($classname)];
		}
		if(isset($d->_explicitType))
		{
			$classname = $d->_explicitType;
			unset($d->_explicitType);
		}
		
		$this->writeUTF($classname); // write the class name
		$objVars = (array) $d;
		foreach($objVars as $key => $data) { // loop over each element
			if($key[0] != "\0")
			{
				$this->writeUTF($key);  // write the name of the object
				$this->writeData($data); // write the value of the object
			}
		} 
		$this->writeInt(0); //  write the end object flag 0x00, 0x00, 0x09
		$this->writeByte(9);

		array_splice($this->serializedObjects, array_search($sr, $this->serializedObjects), 1);
	} 
	
	/**
	 * writeDynamic RecordSetPage writes the page in the correct format to be sent to the Flash client.
	 *
	 * @param array $d Contains the cursor position and the page data
	 */
	function writeDynamicRecordSetPage(&$d, $length) {
		$this->writeByte(16); // write  the custom class code
		$this->writeUTF("RecordSetPage"); // write  the class name
		
		//Write key
		$this->writeUTF("Cursor");
		$this->writeNumber($this->pagecursor);
		
		//Write pages
		$this->writeUTF("Page");
		
		$this->writeByte(10); // write  the mixed array code
		$this->writeLong($length);  // write the count of items in the array
		$this->outBuffer .= $d->serializedData;     
		$this->writeInt(0); //  write the end object flag 0x00, 0x00, 0x09
		$this->writeByte(9);
	} 

	/**
	 * writeRecordSet is the abstracted method to write a custom class recordset object.
	 * Any recordset from any datasource can be written here, it just needs to be properly formatted
	 * beforehand.
	 *
	 * This was unrolled with at the expense of readability for a 
	 * 10 fold increase in speed in large recordsets
	 * 
	 * @param object $rs The formatted RecordSet object
	 */
	 
	function writeRecordSet(&$rs) 
	{
		//Low-level everything here to make things faster
		//This is the bottleneck of AMFPHP, hence the attention in making things faster
		$ob = "";
		$data = $rs->getRecordSet($this->paging);
		
		if(isset($this->writingPage) && $this->writingPage)
		{
			$this->writeDynamicRecordsetPage($rs, $rs->getRowCount());
			$this->writingPage = false;
			$this->paging = -1;
			return;
		}
		
		$this->writeByte(16); // write  the custom class code
		$this->writeUTF("RecordSet"); // write  the class name
		$this->writeUTF("serverInfo");
		
		//Start writing inner object
		$this->writeByte(3); // this is an  object so write the object code
		
		//Write total count
		$this->writeUTF("totalCount");
		if($this->paging != -1)
		{
			$this->writeNumber($this->paging['count']);
		}
		else
		{
			$this->writeNumber($rs->getRowCount());
		}
		
		//Write initial data
		$this->writeUTF("initialData");
		
		//Inner numeric array
		$colnames = $rs->getColumnNames();
		
		$num_count = $rs->getRowCount();
		$this->writeByte(10); // write  the mixed array code
		$this->writeLong($num_count); // write  the count of items in the array

		//Allow recordsets to create their own serialized data, which is faster
		//since the recordset array is traversed only once
		//If not then write it ourselves (lazy bastards)
		if(!isset($rs->serializedData))
		{
			$numcols = count($colnames);
			
			$ob = "";
			$be = $this->isBigEndian;
			$fc = pack('N', $numcols);
			
			for($i = 0 ; $i < $num_count ; $i++) 
			{ 
				// write all of the array elements
				$ob .= "\12" . $fc;

				for($j = 0; $j < $numcols; $j++) { // write all of the array elements
					
					$d = $data[$i][$j];
					if (is_string($d)) 
					{ // type as string
						$os = $this->charsetHandler->transliterate($d);
						//string flag, string length, and string
						$ob .= "\2" . pack('n', strlen($os)) . $os;
					}
					elseif (is_float($d) || is_int($d)) 
					{ // type as double
						$ob .= "\0";
						$b = pack('d', $d); // pack the bytes
						if ($be) { // if we are a big-endian processor
							$r = strrev($b);
						} else { // add the bytes to the output
							$r = $b;
						} 
						$ob .= $r;
					} 
					elseif (is_bool($d)) 
					{ //type as bool
						$ob .= "\1";
						$ob .= pack('c', $d);
					} 
					elseif (is_null($d)) 
					{ // null
						$ob .= "\5";
					} 
				} 
			}
			$this->outBuffer .= $ob;
		}
		else
		{
			$this->outBuffer .= $rs->serializedData;
		}

		//Write cursor
		$this->writeUTF("cursor");
		$this->writeNumber(1);
		
		//Write service name
		$this->writeUTF("serviceName");
		$this->writeString("PageAbleResult");
		
		//Write column names
		$this->writeUTF("columnNames");
		$this->writePlainArray($colnames, 'string');
		
		//Write version number
		$this->writeUTF("version");
		$this->writeNumber(1);
		
		//Write id
		$this->writeUTF("id");
		$this->writeString($rs->getID());
		
		//End inner serverInfo object
		$this->writeInt(0); //  write the end object flag 0x00, 0x00, 0x09
		$this->writeByte(9);
		
		//End outer recordset object
		$this->writeInt(0); //  write the end object flag 0x00, 0x00, 0x09
		$this->writeByte(9);
		
		$this->paging = -1;
	}

	/**
	 * writeCustomClass promotes the writing of the class name and data for CustomClasses
	 * 
	 * @param string $name The class name
	 * @param object $d The class instance object
	 */
	function writeCustomClass ($name, $d) {
		$this->writeByte(16); // write  the custom class code
		$this->writeUTF($name); //  write the class name
		$this->writeObject($d); // write the classes data
	} 

	/**
	 * throwWrongDataTypeError sends the message back to the user that the
	 * manual data type passed doesn't match the actual data type returned by
	 * the service method. Currently unimplemented.
	 * 
	 * @param string $dt The data type that was expected but not encountered
	 */
	function throwWrongDataTypeError($dt) {
		trigger_error("The returned data was not of the expected type " . $dt, E_USER_ERROR);
	} 

	function sanitizeType($type)
	{
		$subtype = -1;
		$type = strtolower($type);
		if($type == NULL || trim($type) == "")
		{
			$type = -1;
		}
		
		if(strpos($type, ' ') !== false)
		{
			$str = explode(' ', $type);
			
			//result is for most dbs
			//recordset is for flash naming compatibility
			//statement is for oci8
			//resultset is in case somone's dyslexic
			if($str[0] == 'arrayof' || $str[0] == 'structof')
			{
				$type = array_shift($str);
				$subtype = implode(' ', $str);
			}
			elseif(in_array($str[1], array("result", 'resultset', "recordset", "statement")))
			{
				$type = "__RECORDSET__";
				$subtype = $str[0];
			}
		}
		return array($type, $subtype);
	} 

	/**
	 * writeData checks to see if the type was declared and then either
	 * auto negotiates the type or relies on the user defined type to
	 * serialize the data into amf
	 *
	 * Note that autoNegotiateType was eliminated in order to tame the 
	 * call stack which was getting huge and was causing leaks
	 *
	 * manualType allows the developer to explicitly set the type of
	 * the returned data.  The returned data is validated for most of the
	 * cases when possible.  Some datatypes like xml and date have to
	 * be returned this way in order for the Flash client to correctly serialize them
	 * 
	 * recordsets appears top on the list because that will probably be the most
	 * common hit in this method.  Followed by the
	 * datatypes that have to be manually set.  Then the auto negotiatable types last.
	 * The order may be changed for optimization.
	 * 
	 * @param mixed $d The data
	 * @param string $type The optional type
	 */
	function writeData($d, $type = -1, $subtype = -1, $unsanitizedType = -1) {
		if ($type == -1) 
		{
			if (is_int($d) || is_float($d)) 
			{ // double
				$this->writeNumber($d);
				return;
			} 
			elseif (is_string($d)) 
			{ // string
				$this->writeString($d);
				return;
			} 
			elseif (is_bool($d)) 
			{ // boolean
				$this->writeBoolean($d);
				return;
			} 
			elseif (is_array($d)) 
			{ // array
				$this->writeArray($d);
				return;
			} 
			elseif (is_null($d)) 
			{ // null
				$this->writeNull();
				return;
			} 
			elseif (is_resource($d)) 
			{ // resource
				$type = get_resource_type($d);
				list($type, $subtype) = $this->sanitizeType($type);
			} 
			elseif (is_object($d))
			{
				$className = strtolower(get_class($d));
				if(array_key_exists($className, $this->resourceObjects))
				{
					$type = "__RECORDSET__";
					$subtype = $this->resourceObjects[strtolower(get_class($d))];
				}
				else if(AMFPHP_PHP5 && $className == 'domdocument')
				{
					$this->writeXML($d->saveXml());
					return;
				}
				else if(!AMFPHP_PHP5 && $className == 'domdocument')
				{
					$this->writeXML($d->dump_mem());
					return;
				}
				elseif($className == "simplexmlelement")
				{
					$this->writeXML($d->asXML());
					return;
				}
				else if($className == 'stdclass')
				{
					$this->writeStdClassObject($d);
					return;
				}
				else
				{
					$this->writePHPObject($d);
					return;
				}
			}
			else
			{
				$type = gettype($d);
			}
		}
		
		switch ($type) {
			case "__RECORDSET__" :
				$classname = $subtype . "Adapter"; // full class name
				$includeFile = include_once(AMFPHP_BASE . "adapters/" . $classname . ".php"); // try to load the recordset library from the sql folder
				if (!$includeFile) {
					trigger_error("The recordset filter class " . $classname . " was not found");
				} 
				$recordSet = new $classname($d, $this->pagesize, $this->paging); // returns formatted recordset
				$this->writeRecordSet($recordSet); // writes the recordset formatted for Flash
				break;
			case "__dynamic_pageable_resultset__":
				$this->paging = array("class" => $d['class'], "method" => $d['method'], "args" => $d['args'], "count" => $d['count'], "ps" => $this->pagesize);
				$this->writeData($d['data']);
				break;
			case "__dynamic_page__":
				$this->writingPage = true;
				$this->pagecursor = $d['cursor'];
				$this->writeData($d['data']);
				break;
			case "date" :
				if (is_float($d) || is_int($d)) {
					$this->writeDate($d);
				} else {
					$this->throwWrongDataTypeError("date");
				} 
				break;
			case "arrayof" :
				if(is_array($d))
				{
					$this->writePlainArray($d, $subtype);
				}
				else {
					$this->throwWrongDataTypeError("arrayof");
				}
				break;
			case "structof" :
				if(is_array($d))
				{
					$this->writeTypedObject($d, $subtype);
				}
				else {
					$this->throwWrongDataTypeError("structof");
				}
				break;
			case "boolean" :
				if (is_bool($d)) {
					$this->writeBoolean($d);
				} else {
					$this->throwWrongDataTypeError("boolean");
				} 
				break;
			case "string" :
				if (is_string($d)) {
					$this->writeString($d);
				} else {
					$this->throwWrongDataTypeError("string");
				} 
				break;
			case "binary" :
				if (is_string($d)) {
					$this->writeBinaryString($d);
				} else {
					$this->throwWrongDataTypeError("binary");
				} 
				break;
			case "raw" :
				if (is_string($d)) {
					$this->writeRaw($d);
				} else {
					$this->throwWrongDataTypeError("raw");
				} 
				break;
			case "double" :
			case "integer" :
			case "int" : 
				if (is_int($d)) {
					$this->writeNumber($d);
				} else {
					$this->throwWrongDataTypeError("integer");
				} 
				break;
			case "object" :
				//Note that if you get a Client.Data.UnderFlow
				//you need to include the custom class before attempting to send to Flash
				if (is_object($d)) {
					$this->writePHPObject($d);
				} else {
					$this->throwWrongDataTypeError("object");
				} 
				break;
			case "array" :
				if (is_array($d)) {
					$this->writeArray($d);
				} 
				else {
					$this->throwWrongDataTypeError("array");
				} 
				break;
			case "null" :
				if (is_null($d)) {
					$this->writeNull();
				} else {
					$this->throwWrongDataError("null");
				} 
				break;
			case "resultset" :
			case "recordset" :
			case "resource" :
				if (is_resource($d)) {
					$type = get_resource_type($d);
					list($type, $subtype) = $this->sanitizeType($type);
					$this->writeData($d, $type, $subtype);
				} else {
					$this->throwWrongDataTypeError("resource");
				} 
				break;
			case "xml" :
				if (is_string($d)) {
					$this->writeXML($d);
				} else if(is_object($d)){
					
					$className = strtolower(get_class($d));
					if(AMFPHP_PHP5 && $className == 'domdocument')
					{
						$this->writeXML($d->saveXml());
						return;
					}
					else if(!AMFPHP_PHP5 && $className == 'domdocument')
					{
						$this->writeXML($d->dump_mem());
						return;
					}
					elseif($className == "simplexmlelement")
					{
						$this->writeXML($d->asXML());
						return;
					}
					else
					{
						$this->throwWrongDataTypeError("xml");
					}
				}
				else
				{
					 $this->throwWrongDataTypeError("xml");
				}
				break;
			default: 
				// non of the above so lets assume its a Custom Class thats defined in the client
				$this->writeCustomClass($unsanitizedType, $d);
				// trigger_error("Unsupported Datatype");
				break;
		} 
	}
	
	/**********************************************************************************
	 *                      This code used to be in AMFOutputStream
	 ********************************************************************************/

	/**
	 * writeByte writes a singe byte to the output stream
	 * 0-255 range
	 * 
	 * @param int $b An int that can be converted to a byte
	 */
	function writeByte($b) {
		$this->outBuffer .= pack("c", $b); // use pack with the c flag
	} 

	/**
	 * writeInt takes an int and writes it as 2 bytes to the output stream
	 * 0-65535 range
	 * 
	 * @param int $n An integer to convert to a 2 byte binary string
	 */
	function writeInt($n) {
		$this->outBuffer .= pack("n", $n); // use pack with the n flag
	} 

	/**
	 * writeLong takes an int, float or double and converts it to a 4 byte binary string and
	 * adds it to the output buffer
	 * 
	 * @param long $l A long to convert to a 4 byte binary string
	 */
	function writeLong($l) {
		$this->outBuffer .= pack("N", $l); // use pack with the N flag
	} 

	/**
	 * writeUTF takes and input string, writes the length as an int and then
	 * appends the string to the output buffer
	 * 
	 * @param string $s The string less than 65535 characters to add to the stream
	 */
	function writeUTF($s) {
		$os = $this->charsetHandler->transliterate($s);
		$this->writeInt(strlen($os)); // write the string length - max 65535
		$this->outBuffer .= $os; // write the string chars
	} 
	
	/**
	 * writeBinary takes and input string, writes the length as an int and then
	 * appends the string to the output buffer
	 * 
	 * @param string $s The string less than 65535 characters to add to the stream
	 */
	function writeBinary($s) {
		$this->writeInt(strlen($s)); // write the string length - max 65535
		$this->outBuffer .= $s; // write the string chars
	} 

	/**
	 * writeLongUTF will write a string longer than 65535 characters.
	 * It works exactly as writeUTF does except uses a long for the length
	 * flag.
	 * 
	 * @param string $s A string to add to the byte stream
	 */
	function writeLongUTF($s) {
		$os = $this->charsetHandler->transliterate($s);
		$this->writeLong(strlen($os));
		$this->outBuffer .= $os; // write the string chars
	} 

	/**
	 * writeDouble takes a float as the input and writes it to the output stream.
	 * Then if the system is big-endian, it reverses the bytes order because all
	 * doubles passed via remoting are passed little-endian.
	 * 
	 * @param double $d The double to add to the output buffer
	 */
	function writeDouble($d) {
		$b = pack("d", $d); // pack the bytes
		if ($this->isBigEndian) { // if we are a big-endian processor
			$r = strrev($b);
		} else { // add the bytes to the output
			$r = $b;
		} 
		
		$this->outBuffer .= $r;
	} 
}

?>