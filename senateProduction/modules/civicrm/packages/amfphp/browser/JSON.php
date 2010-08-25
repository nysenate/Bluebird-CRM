<?php
/** 
 * LICENSE: Redistribution and use in source and binary forms, with or
 * without modification, are permitted provided that the following
 * conditions are met: Redistributions of source code must retain the
 * above copyright notice, this list of conditions and the following
 * disclaimer. Redistributions in binary form must reproduce the above
 * copyright notice, this list of conditions and the following disclaimer
 * in the documentation and/or other materials provided with the
 * distribution.
 * 
 * THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN
 * NO EVENT SHALL CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS
 * OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
 * TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE
 * USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 * 
 * @category   
 * @package     Services_JSON
 * @author      Michal Migurski <mike-json@teczno.com>
 * @author      Matt Knapp <mdknapp[at]gmail[dot]com>
 * @author      Brett Stimmerman <brettstimmerman[at]gmail[dot]com>
 * @copyright   2005 Michal Migurski
 * @license     http://www.opensource.org/licenses/bsd-license.php
 * @link        http://pear.php.net/pepr/pepr-proposal-show.php?id=198
 *
 * Note: this is a stripped version of JSON.php to remove pear dependencies and
 * encoding capability
 */

/**
 * Marker constant for Services_JSON::decode(), used to flag stack state
 */
define('SERVICES_JSON_SLICE',   1);

/**
 * Marker constant for Services_JSON::decode(), used to flag stack state
 */
define('SERVICES_JSON_IN_STR',  2);

/**
 * Marker constant for Services_JSON::decode(), used to flag stack state
 */
define('SERVICES_JSON_IN_ARR',  3);

/**
 * Marker constant for Services_JSON::decode(), used to flag stack state
 */
define('SERVICES_JSON_IN_OBJ',  4);

/**
 * Marker constant for Services_JSON::decode(), used to flag stack state
 */
define('SERVICES_JSON_IN_CMT', 5);

/**
 * Behavior switch for Services_JSON::decode()
 */
define('SERVICES_JSON_LOOSE_TYPE', 16);

/**
 * Behavior switch for Services_JSON::decode()
 */
define('SERVICES_JSON_SUPPRESS_ERRORS', 32);

class Services_JSON
{
	function Services_JSON($use = 0)
	{
		$this->use = $use;
	}

	function utf162utf8($utf16)
	{
		// oh please oh please oh please oh please oh please
		if(function_exists('mb_convert_encoding'))
			return mb_convert_encoding($utf16, 'UTF-8', 'UTF-16');
		
		$bytes = (ord($utf16{0}) << 8) | ord($utf16{1});

		switch(true) {
			case ((0x7F & $bytes) == $bytes):
				// this case should never be reached, because we are in ASCII range
				// see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
				return chr(0x7F & $bytes);

			case (0x07FF & $bytes) == $bytes:
				// return a 2-byte UTF-8 character
				// see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
				return chr(0xC0 | (($bytes >> 6) & 0x1F))
					 . chr(0x80 | ($bytes & 0x3F));

			case (0xFFFF & $bytes) == $bytes:
				// return a 3-byte UTF-8 character
				// see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
				return chr(0xE0 | (($bytes >> 12) & 0x0F))
					 . chr(0x80 | (($bytes >> 6) & 0x3F))
					 . chr(0x80 | ($bytes & 0x3F));
		}

		// ignoring UTF-32 for now, sorry
		return '';
	}        

	function utf82utf16($utf8)
	{
		// oh please oh please oh please oh please oh please
		if(function_exists('mb_convert_encoding'))
			return mb_convert_encoding($utf8, 'UTF-16', 'UTF-8');
		
		switch(strlen($utf8)) {
			case 1:
				// this case should never be reached, because we are in ASCII range
				// see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
				return $ut8;

			case 2:
				// return a UTF-16 character from a 2-byte UTF-8 char
				// see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
				return chr(0x07 & (ord($utf8{0}) >> 2))
					 . chr((0xC0 & (ord($utf8{0}) << 6))
						 | (0x3F & ord($utf8{1})));
				
			case 3:
				// return a UTF-16 character from a 3-byte UTF-8 char
				// see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
				return chr((0xF0 & (ord($utf8{0}) << 4))
						 | (0x0F & (ord($utf8{1}) >> 2)))
					 . chr((0xC0 & (ord($utf8{1}) << 6))
						 | (0x7F & ord($utf8{2})));
		}

		// ignoring UTF-32 for now, sorry
		return '';
	}        
	
	function name_value($name, $value)
	{
		$encoded_value = $this->encode($value);
		
		if(Services_JSON::isError($encoded_value))
			return $encoded_value;
	
		return $this->encode(strval($name)) . ':' . $encoded_value;
	}        

	function reduce_string($str)
	{
		$str = preg_replace(array(
		
				// eliminate single line comments in '// ...' form
				'#^\s*//(.+)$#m',
	
				// eliminate multi-line comments in '/* ... */' form, at start of string
				'#^\s*/\*(.+)\*/#Us',
	
				// eliminate multi-line comments in '/* ... */' form, at end of string
				'#/\*(.+)\*/\s*$#Us'
	
			), '', $str);
		
		// eliminate extraneous space
		return trim($str);
	}

	function decode($str)
	{
		$str = $this->reduce_string($str);
	
		switch (strtolower($str)) {
			case 'true':
				return true;

			case 'false':
				return false;
			
			case 'null':
				return null;
			
			default:
				if (is_numeric($str)) {
					// Lookie-loo, it's a number

					// Return float or int, as appropriate
					return ((float)$str == (integer)$str)
						? (integer)$str
						: (float)$str;
					
				} 
				elseif (preg_match('/^("|\').*(\1)$/s', $str,   $m) && $m[1] == $m[2]) {
					// STRINGS RETURNED IN UTF-8 FORMAT
					$delim = substr($str, 0, 1);
					$chrs = substr($str, 1, -1);
					$utf8 = '';
					$strlen_chrs = strlen($chrs);
					
					for ($c = 0; $c < $strlen_chrs; ++$c) {
					
						$substr_chrs_c_2 = substr($chrs, $c, 2);
						$ord_chrs_c = ord($chrs{$c});
						
						switch (true) {
							case $substr_chrs_c_2 == '\b':
								$utf8 .= chr(0x08);
								++$c;
								break;
							case $substr_chrs_c_2 == '\t':
								$utf8 .= chr(0x09);
								++$c;
								break;
							case $substr_chrs_c_2 == '\n':
								$utf8 .= chr(0x0A);
								++$c;
								break;
							case $substr_chrs_c_2 == '\f':
								$utf8 .= chr(0x0C);
								++$c;
								break;
							case $substr_chrs_c_2 == '\r':
								$utf8 .= chr(0x0D);
								++$c;
								break;

							case $substr_chrs_c_2 == '\\"':
							case $substr_chrs_c_2 == '\\\'':
							case $substr_chrs_c_2 == '\\\\':
							case $substr_chrs_c_2 == '\\/':
								if (($delim == '"' && $substr_chrs_c_2 != '\\\'') ||
								   ($delim == "'" && $substr_chrs_c_2 != '\\"')) {
									$utf8 .= $chrs{++$c};
								}
								break;
								
							case preg_match('/\\\u[0-9A-F]{4}/i', substr($chrs, $c, 6)):
								// single, escaped unicode character
								$utf16 = chr(hexdec(substr($chrs, ($c + 2), 2)))
									   . chr(hexdec(substr($chrs, ($c + 4), 2)));
								$utf8 .= $this->utf162utf8($utf16);
								$c += 5;
								break;
		
							case ($ord_chrs_c >= 0x20) && ($ord_chrs_c <= 0x7F):
								$utf8 .= $chrs{$c};
								break;
		
							case ($ord_chrs_c & 0xE0) == 0xC0:
								// characters U-00000080 - U-000007FF, mask 110XXXXX
								//see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
								$utf8 .= substr($chrs, $c, 2);
								++$c;
								break;
	
							case ($ord_chrs_c & 0xF0) == 0xE0:
								// characters U-00000800 - U-0000FFFF, mask 1110XXXX
								// see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
								$utf8 .= substr($chrs, $c, 3);
								$c += 2;
								break;
	
							case ($ord_chrs_c & 0xF8) == 0xF0:
								// characters U-00010000 - U-001FFFFF, mask 11110XXX
								// see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
								$utf8 .= substr($chrs, $c, 4);
								$c += 3;
								break;
	
							case ($ord_chrs_c & 0xFC) == 0xF8:
								// characters U-00200000 - U-03FFFFFF, mask 111110XX
								// see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
								$utf8 .= substr($chrs, $c, 5);
								$c += 4;
								break;
	
							case ($ord_chrs_c & 0xFE) == 0xFC:
								// characters U-04000000 - U-7FFFFFFF, mask 1111110X
								// see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
								$utf8 .= substr($chrs, $c, 6);
								$c += 5;
								break;

						}

					}
					
					return $utf8;
				
				} elseif (preg_match('/^\[.*\]$/s', $str) || preg_match('/^\{.*\}$/s', $str)) {
					// array, or object notation

					if ($str{0} == '[') {
						$stk = array(SERVICES_JSON_IN_ARR);
						$arr = array();
					} else {
						if ($this->use & SERVICES_JSON_LOOSE_TYPE ) {
							$stk = array(SERVICES_JSON_IN_OBJ);
							$obj = array();
						} else {
							$stk = array(SERVICES_JSON_IN_OBJ);
							$obj = new stdClass();
						}
					}
					
					array_push($stk, array('what'  => SERVICES_JSON_SLICE,
										   'where' => 0,
										   'delim' => false));

					$chrs = substr($str, 1, -1);
					$chrs = $this->reduce_string($chrs);
					
					if ($chrs == '') {
						if (reset($stk) == SERVICES_JSON_IN_ARR) {
							return $arr;

						} else {
							return $obj;

						}
					}

					//print("\nparsing {$chrs}\n");
					
					$strlen_chrs = strlen($chrs);
					
					for ($c = 0; $c <= $strlen_chrs; ++$c) {
					
						$top = end($stk);
						$substr_chrs_c_2 = substr($chrs, $c, 2);
					
						if (($c == $strlen_chrs) || (($chrs{$c} == ',') && ($top['what'] == SERVICES_JSON_SLICE))) {
							// found a comma that is not inside a string, array, etc.,
							// OR we've reached the end of the character list
							$slice = substr($chrs, $top['where'], ($c - $top['where']));
							array_push($stk, array('what' => SERVICES_JSON_SLICE, 'where' => ($c + 1), 'delim' => false));
							//print("Found split at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");

							if (reset($stk) == SERVICES_JSON_IN_ARR) {
								// we are in an array, so just push an element onto the stack
								array_push($arr, $this->decode($slice));

							} elseif (reset($stk) == SERVICES_JSON_IN_OBJ) {
								// we are in an object, so figure
								// out the property name and set an
								// element in an associative array,
								// for now
								if (preg_match('/^\s*(["\'].*[^\\\]["\'])\s*:\s*(\S.*),?$/Uis', $slice, $parts)) {
									// "name":value pair
									$key = $this->decode($parts[1]);
									$val = $this->decode($parts[2]);

									if ($this->use & SERVICES_JSON_LOOSE_TYPE) {
										$obj[$key] = $val;
									} else {
										$obj->$key = $val;
									}
								} elseif (preg_match('/^\s*(\w+)\s*:\s*(\S.*),?$/Uis', $slice, $parts)) {
									// name:value pair, where name is unquoted
									$key = $parts[1];
									$val = $this->decode($parts[2]);

									if ($this->use & SERVICES_JSON_LOOSE_TYPE) {
										$obj[$key] = $val;
									} else {
										$obj->$key = $val;
									}
								}

							}

						} elseif ((($chrs{$c} == '"') || ($chrs{$c} == "'")) && ($top['what'] != SERVICES_JSON_IN_STR)) {
							// found a quote, and we are not inside a string
							array_push($stk, array('what' => SERVICES_JSON_IN_STR, 'where' => $c, 'delim' => $chrs{$c}));
							//print("Found start of string at {$c}\n");

						} elseif (($chrs{$c} == $top['delim']) &&
								 ($top['what'] == SERVICES_JSON_IN_STR) &&
								 (($chrs{$c - 1} != '\\') ||
								 ($chrs{$c - 1} == '\\' && $chrs{$c - 2} == '\\'))) {
							// found a quote, we're in a string, and it's not escaped
							array_pop($stk);
							//print("Found end of string at {$c}: ".substr($chrs, $top['where'], (1 + 1 + $c - $top['where']))."\n");

						} elseif (($chrs{$c} == '[') &&
								 in_array($top['what'], array(SERVICES_JSON_SLICE, SERVICES_JSON_IN_ARR, SERVICES_JSON_IN_OBJ))) {
							// found a left-bracket, and we are in an array, object, or slice
							array_push($stk, array('what' => SERVICES_JSON_IN_ARR, 'where' => $c, 'delim' => false));
							//print("Found start of array at {$c}\n");

						} elseif (($chrs{$c} == ']') && ($top['what'] == SERVICES_JSON_IN_ARR)) {
							// found a right-bracket, and we're in an array
							array_pop($stk);
							//print("Found end of array at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");

						} elseif (($chrs{$c} == '{') &&
								 in_array($top['what'], array(SERVICES_JSON_SLICE, SERVICES_JSON_IN_ARR, SERVICES_JSON_IN_OBJ))) {
							// found a left-brace, and we are in an array, object, or slice
							array_push($stk, array('what' => SERVICES_JSON_IN_OBJ, 'where' => $c, 'delim' => false));
							//print("Found start of object at {$c}\n");

						} elseif (($chrs{$c} == '}') && ($top['what'] == SERVICES_JSON_IN_OBJ)) {
							// found a right-brace, and we're in an object
							array_pop($stk);
							//print("Found end of object at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");

						} elseif (($substr_chrs_c_2 == '/*') &&
								 in_array($top['what'], array(SERVICES_JSON_SLICE, SERVICES_JSON_IN_ARR, SERVICES_JSON_IN_OBJ))) {
							// found a comment start, and we are in an array, object, or slice
							array_push($stk, array('what' => SERVICES_JSON_IN_CMT, 'where' => $c, 'delim' => false));
							$c++;
							//print("Found start of comment at {$c}\n");

						} elseif (($substr_chrs_c_2 == '*/') && ($top['what'] == SERVICES_JSON_IN_CMT)) {
							// found a comment end, and we're in one now
							array_pop($stk);
							$c++;
							
							for ($i = $top['where']; $i <= $c; ++$i)
								$chrs = substr_replace($chrs, ' ', $i, 1);
							
							//print("Found end of comment at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");

						}
					
					}
					

					
					if (reset($stk) == SERVICES_JSON_IN_ARR) {
						return $arr;

					} elseif (reset($stk) == SERVICES_JSON_IN_OBJ) {
						return $obj;

					}
				
				}
		}
	}
}
?>