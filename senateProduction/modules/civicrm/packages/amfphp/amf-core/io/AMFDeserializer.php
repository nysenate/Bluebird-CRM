<?php
/**
 * AMFDeserializer takes the raw amf input stream and converts it PHP objects
 * representing the data.
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage io
 * @version $Id$
 */

/**
 * Required classes
 */ 
require_once(AMFPHP_BASE . "util/AMFBody.php");
require_once(AMFPHP_BASE . "util/AMFHeader.php");
require_once(AMFPHP_BASE . "util/DateWrapper.php");

class AMFDeserializer {
	/**
	 * The raw data input
	 * 
	 * @access private 
	 * @var string 
	 */
	var $raw_data;

	/**
	 * The current seek cursor of the stream
	 * 
	 * @access private 
	 * @var int 
	 */
	var $current_byte;

	/**
	 * The length of the stream.  Since this class is not actually using a stream
	 * the entire content of the stream is passed in as the initial argument so the
	 * length can be determined.
	 * 
	 * @access private 
	 * @var int 
	 */
	var $content_length;
	
	/**
	 * The number of headers in the packet.
	 * 
	 * @access private 
	 * @var int 
	 */
	var $header_count;

	/**
	 * The content of the packet headers
	 * 
	 * @access private 
	 * @var string 
	 */
	var $headers;

	/**
	 * The number of bodys in the packet.
	 * 
	 * @access private 
	 * @var int 
	 */
	var $body_count;

	/**
	 * The content of the body elements
	 * 
	 * @access private 
	 * @var string 
	 */
	var $body;

	/**
	 * The object to store the amf data.
	 * 
	 * @access private 
	 * @var object 
	 */
	var $amfdata;

	/**
	 * The instance of the amfinput stream object
	 * 
	 * @access private 
	 * @var object 
	 */
	var $inputStream;
	
	/**
	 * metaInfo
	 */
	var $meta;

	/**
	 * Constructor method for the deserializer.  Constructing the deserializer converts the input stream
	 * content to a AMFObject.
	 * 
	 * @param object $is The referenced input stream
	 */
	function AMFDeserializer($rd) {
		$this->isBigEndian = AMFPHP_BIG_ENDIAN;
		$this->current_byte = 0;
		$this->raw_data = $rd;  // store the stream in this object
		$this->content_length = strlen($this->raw_data); // grab the total length of this stream
		$this->charsetHandler = new CharsetHandler('flashtophp');
	} 

	/**
	 * deserialize invokes this class to transform the raw data into valid object
	 * 
	 * @param object $amfdata The object to put the deserialized data in
	 */
	function deserialize (&$amfdata) {
		$this->amfdata = &$amfdata;
		$this->readHeader(); // read the binary header
		$this->readBody(); // read the binary body
	} 
	/**
	 * returns the built AMFObject from the deserialization operation
	 * 
	 * @return object The deserialized AMFObject
	 */
	function getAMFObject() {
		return $this->amfdata;
	} 

	/**
	 * readHeader converts that header section of the amf message into php obects.
	 * Header information typically contains meta data about the message.
	 */
	function readHeader() {

		$topByte = $this->readByte(); // ignore the first two bytes --  version or something
		$secondByte = $this->readByte(); //0 for Flash,
														//1 for FlashComm                   
		//Disable debug events for FlashComm
		$GLOBALS['amfphp']['isFlashComm'] = $secondByte == 1;
		
		//If firstByte != 0, then the AMF data is corrupted, for example the transmission 
		//
		if(!($topByte == 0 || $topByte == 3))
		{
			trigger_error("Malformed AMF message, connection may have dropped");
			exit();
		}
		$this->header_count = $this->readInt(); //  find the total number of header elements
		while ($this->header_count--) { // loop over all of the header elements
			$name = $this->readUTF();
			$required = $this->readByte() == 1; // find the must understand flag
			//$length   = $this->readLong(); // grab the length of  the header element
			$this->current_byte += 4; // grab the length of the header element
			$type = $this->readByte();  // grab the type of the element
			$content = $this->readData($type); // turn the element into real data
			$this->amfdata->addHeader(new AMFHeader($name, $required, $content)); // save the name/value into the headers array
		}

	} 

	/**
	 * readBody converts the payload of the message into php objects.
	 */
	function readBody() {
		$this->body_count = $this->readInt(); // find the total number  of body elements
		while ($this->body_count--) { // loop over all of the body elements
			
			$target = $this->readUTF();
			$response = $this->readUTF(); //    the response that the client understands
			
			//$length = $this->readLong(); // grab the length of    the body element
			$this->current_byte += 4;
			$type = $this->readByte();  // grab the type of the element
			$data = $this->readData($type); // turn the argument elements into real data
			$this->amfdata->addBody(new AMFBody($target, $response, $data)); // add the body element to the body object
			
		} 
	} 

	/**
	 * readObject reads the name/value properties of the amf message and converts them into
	 * their equivilent php representation
	 * 
	 * @return array The php array with the object data
	 */
	function readObject() {
		$ret = array(); // init the array
		$key = $this->readUTF(); // grab the key
		for ($type = $this->readByte(); $type != 9; $type = $this->readByte()) {
			$val = $this->readData($type); // grab the value
			$ret[$key] = $val; // save the name/value pair in the array
			$key = $this->readUTF(); // get the next name
		}
		return $ret; // return the array
	} 
	
	/**
	 * readMixedObject reads the name/value properties of the amf message and converts
	 * numeric looking keys to numeric keys
	 * 
	 * @return array The php array with the object data
	 */
	function readMixedObject() {
		$ret = array(); // init the array
		$key = $this->readUTF(); // grab the key
		for ($type = $this->readByte(); $type != 9; $type = $this->readByte()) {
			$val = $this->readData($type); // grab the value
			if(is_numeric($key))
			{
				$key = (float) $key;
			}
			$ret[$key] = $val; // save the name/value pair in the array
			$key = $this->readUTF(); // get the next name
		}
		return $ret; // return the array
	} 

	/**
	 * readArray turns an all numeric keyed actionscript array into a php array.
	 * 
	 * @return array The php array
	 */
	function readArray() {
		$ret = array(); // init the array object
		$length = $this->readLong(); // get the length  of the array
		for ($i = 0; $i < $length; $i++) { // loop over all of the elements in the data
			$type = $this->readByte();  // grab the type for each element
			$ret[] = $this->readData($type); // grab each element
		} 
		return $ret; // return the data
		
	} 

	/**
	 * readMixedArray turns an array with numeric and string indexes into a php array
	 * 
	 * @return array The php array with mixed indexes
	 */
	function readMixedArray() {
		//$length   = $this->readLong(); // get the length  property set by flash
		$this->current_byte += 4;
		return $this->readMixedObject(); // return the body of mixed array
	} 

	/**
	 * readCustomClass reads the amf content associated with a class instance which was registered
	 * with Object.registerClass.  In order to preserve the class name an additional property is assigned
	 * to the object "_explicitType".  This property will be overwritten if it existed within the class already.
	 * 
	 * @return object The php representation of the object
	 */
	function readCustomClass() {
		$typeIdentifier = str_replace('..', '', $this->readUTF());
		
		$value = $this->readObject(); // the rest of the bytes are an object without the 0x03 header
		$value["_explicitType"] = $typeIdentifier; // save that type because we may need it if we can find a way to add debugging features
		
		//Check out if class exists
		$mappedClass = str_replace('.', '/', $typeIdentifier);
		if(isset($GLOBALS['amfphp']['incomingClassMappings'][$typeIdentifier]))
		{
			$mappedClass = str_replace('.', '/', $GLOBALS['amfphp']['incomingClassMappings'][$typeIdentifier]);
		}
		
		$include = FALSE;
		if(file_exists($GLOBALS['amfphp']['customMappingsPath'] . $mappedClass . '.php'))
		{
			$include = $GLOBALS['amfphp']['customMappingsPath'] . $mappedClass . '.php';
		}
		elseif(file_exists($GLOBALS['amfphp']['customMappingsPath'] . $mappedClass . '.class.php'))
		{
			$include = $GLOBALS['amfphp']['customMappingsPath'] . $mappedClass . '.class.php';
		}
		
		if($include !== FALSE)
		{
			include_once($include);
			$lastPlace = strrpos('/' . $mappedClass, '/');
			$classname = substr($mappedClass, $lastPlace);
			if(class_exists($classname))
			{
				$clazz = new $classname;
				//Map this baby
				foreach($value as $key => $contents)
				{
					if($key != '_explicitType')
					{   
						//Input values
						$clazz->$key = $contents;
					}
				}
				
				if(method_exists ( $clazz, 'init' ))
				{
					$clazz->init();
				}
				$value = $clazz;
			}
		}
		
		return $value; // return the object
	} 

	/**
	 * readDate reads a date from the amf message and returns the time in ms.
	 * This method is still under development.
	 * 
	 * @return long The date in ms.
	 */
	function readDate() {
		$ms = $this->readDouble();  // date in milliseconds from 01/01/1970
		$int = $this->readInt(); // nasty way to get timezone
		if ($int > 720) {
			$int = - (65536 - $int);
		} 
		$int *= -60;
		//$int *= 1000;
		//$min = $int % 60;
		//$timezone = "GMT " . - $hr . ":" . abs($min);
		// end nastiness
		
		//We store the last timezone found in date fields in the request
		//FOr most purposes, it's expected that the timezones
		//don't change from one date object to the other (they change per client though)
		DateWrapper::setTimezone($int);
		return $ms; 
	}

	/**
	 * readReference replaces the old readFlushedSO. It treats where there
	 * are references to other objects. Currently it does not resolve the
	 * object as this would involve a serious amount of overhead, unless
	 * you have a genius idea
	 * 
	 * @return String 
	 */
	function readReference() {
		$reference = $this->readInt();
		return "(unresolved object #$reference)";
	} 

	/**
	 * readData is the main switch for mapping a type code to an actual
	 * implementation for deciphering it.
	 * 
	 * @param mixed $type The $type integer
	 * @return mixed The php version of the data in the message block
	 */
	function readData($type) {
		switch ($type) {
			case 0: // number
				$data = $this->readDouble();
				break;
			case 1: // boolean
				$data = $this->readByte() == 1;
				break;
			case 2: // string
				$data = $this->readUTF();
				break;
			case 3: // object Object
				$data = $this->readObject();
				break;
			case 5: // null
				$data = null;
				break;
			case 6: // undefined
				$data = null;
				break;
			case 7: // Circular references are returned here
				$data = $this->readReference();
				break;
			case 8: // mixed array with numeric and string keys
				$data = $this->readMixedArray();
				break;
			case 10: // array
				$data = $this->readArray();
				break;
			case 11: // date
				$data = $this->readDate();
				break;
			case 12: // string, strlen(string) > 2^16
				$data = $this->readLongUTF();
				break;
			case 13: // mainly internal AS objects
				$data = null;
				break;
			case 15: // XML
				$data = $this->readLongUTF();
				break;
			case 16: // Custom Class
				$data = $this->readCustomClass();
				break;
			default: // unknown case
				trigger_error("Found unhandled type with code: $type");
				exit();
				break;
		} 
		return $data;
	} 
	
	/********************************************************************************
	 *                       This used to be in AmfInputStream
	 ********************************************************************************

	/**
	 * readByte grabs the next byte from the data stream and returns it.
	 * 
	 * @return int The next byte converted into an integer
	 */
	function readByte() {
		return ord($this->raw_data[$this->current_byte++]); // return the next byte
	}

	/**
	 * readInt grabs the next 2 bytes and returns the next two bytes, shifted and combined
	 * to produce the resulting integer
	 * 
	 * @return int The resulting integer from the next 2 bytes
	 */
	function readInt() {
		return ((ord($this->raw_data[$this->current_byte++]) << 8) |
			ord($this->raw_data[$this->current_byte++])); // read the next 2 bytes, shift and add
	} 

	/**
	 * readLong grabs the next 4 bytes shifts and combines them to produce an integer
	 * 
	 * @return int The resulting integer from the next 4 bytes
	 */
	function readLong() {
		return ((ord($this->raw_data[$this->current_byte++]) << 24) |
			(ord($this->raw_data[$this->current_byte++]) << 16) |
			(ord($this->raw_data[$this->current_byte++]) << 8) |
			ord($this->raw_data[$this->current_byte++])); // read the next 4 bytes, shift and add
	} 

	/**
	 * readDouble reads the floating point value from the bytes stream and properly orders
	 * the bytes depending on the system architecture.
	 * 
	 * @return float The floating point value of the next 8 bytes
	 */
	function readDouble() {
		$bytes = substr($this->raw_data, $this->current_byte, 8);
		$this->current_byte += 8;
		if ($this->isBigEndian) {
			 $bytes = strrev($bytes);
		} 
		$zz = unpack("dflt", $bytes); // unpack the bytes
		return $zz['flt']; // return the number from the associative array
	} 

	/**
	 * readUTF first grabs the next 2 bytes which represent the string length.
	 * Then it grabs the next (len) bytes of the resulting string.
	 * 
	 * @return string The utf8 decoded string
	 */
	function readUTF() {
		$length = $this->readInt(); // get the length of the string (1st 2 bytes)
		//BUg fix:: if string is empty skip ahead
		if($length == 0)
		{
			return "";
		}
		else
		{
			$val = substr($this->raw_data, $this->current_byte, $length); // grab the string
			$this->current_byte += $length; // move the seek head to the end of the string
			return $this->charsetHandler->transliterate($val); // return the string
		}
	} 

	/**
	 * readLongUTF first grabs the next 4 bytes which represent the string length.
	 * Then it grabs the next (len) bytes of the resulting in the string
	 * 
	 * @return string The utf8 decoded string
	 */
	function readLongUTF() {
		$length = $this->readLong(); // get the length of the string (1st 4 bytes)
		$val = substr($this->raw_data, $this->current_byte, $length); // grab the string
		$this->current_byte += $length; // move the seek head to the end of the string
		return $this->charsetHandler->transliterate($val); // return the string
	} 
} 

?>