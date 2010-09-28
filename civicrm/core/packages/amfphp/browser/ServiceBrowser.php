<?php
/**
 * The ServiceBrowser class can generate a listing of class in the services folder 
 * and generate actionscript along with a listing of methods. 
 * It is therefore more complete than 
 * Flash's service browser (although the latter is still supported)
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @access private 
 * @package flashservices
 * @subpackage util
 * @author John Cowen
 * @version $Id: ServiceBrowser.php,v 1.23 2005/07/25 01:33:19 pmineault Exp $
 */
 
include_once(AMFPHP_BASE . 'util/Compat.php');

class ServiceBrowser {
	/**
	 * The location of the class to be browsed.
	 * 
	 * @access private 
	 * @var string 
	 */
	var $_classpath;
	/**
	 * The name of the class to be browsed.
	 * 
	 * @access private 
	 * @var string 
	 */
	var $_classname;
	/**
	 * The method to be tested.
	 * 
	 * @access private 
	 * @var string 
	 */
	var $_methodname;
	/**
	 * An instance of the class being browsed.
	 * 
	 * @access private 
	 * @var object 
	 */
	var $_classConstruct;
	/**
	 * Arguments used when tesing a method.
	 * 
	 * @access private 
	 * @var array 
	 */
	var $_arguments;
	/**
	 * Internal array of loaded classes
	 * This needed because of issues with loading multiple files in different folders with
	 * the same class name
	 *
	 * @access private
	 * @var string
	 */
	var $_classes;
	/**
	 * Path to the classes
	 *
	 * @access private
	 * @var string
	 */
	var $_path;
	
	var $phpToAsTypes = array(
		"string" => "String",
		"int" => "Number",
		"float" => "Number",
		"double" => "Number",
		"number" => "Number",
		"boolean" => "Boolean",
		"null" => "null",
		"void" => "Void",
		"undefined" => "null",
		"date" => "Date",
		"array" => "Array",
		"object" => "Object",
		"xml" => "XML");

	/**
	 * Constructor method for the Service Browser class.
	 * We do not use a unified constructor for PHP4 compatibility. (CH)
	 * 
	 * @param $path The path to the services folder.
	 */
	function ServiceBrowser($path, $omit = array()) {
		define("PRODUCTION_SERVER", false);
		$this->_path = $path;
		$this->_omit = $omit;

		if (isset($_GET['methodname'])) {
			$this->_methodname = $_GET['methodname'];
		}
		if (isset($_POST['arguments'])) {
			$this->_arguments = $_POST['arguments'];
		}
		$this->_basedir = getcwd();
	}


	/**
	 * Retrieves the list of services in a folder
	 *
	 * @param string $location The location of the folder containing services
	 *
	 */
	function listServices($dir = "", $suffix = "")
	{
		
		if($dir == "")
		{
			$dir = $this->_path;
		}
		$services = array();
		if(in_array($suffix, $this->_omit)){ return; }
		if ($handle = opendir($dir . $suffix))
		{
			while (false !== ($file = readdir($handle))) 
			{
				chdir(dirname(__FILE__));
				if ($file != "." && $file != "..") 
				{
					if(is_file($dir . $suffix . $file))
					{
						if(strpos($file, '.methodTable') !== FALSE)
						{
							continue;
						}
						$index = strrpos($file, '.');
						$before = substr($file, 0, $index);
						$after = substr($file, $index + 1);
						
						if($after == 'php')
						{
							$loc = "zzz_default";
							if($suffix != "")
							{
								$loc = str_replace('/','.', substr($suffix, 0, -1));
							}
							
							if($services[$loc] == NULL)
							{
								$services[$loc] = array();
							}
							$services[$loc][] = array($before, $suffix);
							//array_push($this->_classes, $before);
						}
						
					}
					elseif(is_dir($dir . $suffix . $file))
					{
						$insideDir = $this->listServices($dir, $suffix . $file . "/");
						if(is_array($insideDir))
						{
							$services = $services + $insideDir;
						}
					}
				}
			}
		}else{
			echo("error");
		}
		closedir($handle);
		return $services;
	}
	 
	/**
	 * Sets the service to be browsed.
	 * 
	 * The classname can be passed or the class filename i.e MyClass or MyClass.php
	 * 
	 * @param string $class The location of the service class file
	 */
	function setService($class) {
		
		$this->_classpath = $class;
		// get classname
		$dot = strrpos($this->_classpath, ".");
		if ($dot === false) {
			// class name was passed
			$trunced = $this->_classpath;
			$this->_classpath .= ".php";
		} else {
			// class filename was passed
			$trunced = substr($this->_classpath, 0, $dot);
		} 
		//echo($trunced);
		$this->_classname = substr(strrchr('/' . $trunced, "/"), 1);
		$path = substr('/' . $trunced, 1 ,strrpos('/' . $trunced, "/"));
		chdir($this->_path . $path);
		ob_start();
		include_once($this->_classname . '.php');
		ob_end_clean();
		
		if (class_exists($this->_classname)) {
			$this->_classConstruct = new $this->_classname(NULL, NULL, NULL, NULL, NULL, NULL, NULL);
			if(isset($this->_classConstruct->methodTable))
			{
				
				return true;
			}
			else
			{
				return false;
			}
		} else {
			return false;
		}
	} 
	function setMethodName($methodName)
	{
		$this->_methodname = $methodName;
	}
	
	/**
	 * Prints out the headers and footers of the method list page and 
	 * prints out the properties of each method through _printMethod.
	 */
	function listMethods() {
		$methods = array();
		$i = 0;
		if(is_array($this->_classConstruct->methodTable))
		{
			foreach ($this->_classConstruct->methodTable as $name => $methodproperties) {
				$methods[$name] = $this->_printMethod($name, $methodproperties, $i);
				$i++;
			}
		}
		return $methods;
	}
	
	/**
	 * Prints out the headers and footers of the method list page and 
	 * prints out the properties of each method through _printMethod.
	 */
	function listMethodsShort() {
		$remoteMethods = array();
		$privateMethods = array();
		if(is_array($this->_classConstruct->methodTable))
		{
			$i = 1;
			$j = count($this->_classConstruct->methodTable);
			foreach ($this->_classConstruct->methodTable as $name => $methodproperties) {
				if($methodproperties['access'] != 'remote')
				{
					$privateMethods[].= "<a href='javascript:testMethod($i,$j)' title='" . $methodproperties['description'] . "'>" . $name. "</a>";
				}
				else
				{
					$remoteMethods[].= "<a href='javascript:testMethod($i,$j)' title='" . $methodproperties['description'] . "'>" . $name. "</a>";
				}
				$i++;
			}
		}
		$ret = "";
		if(count($remoteMethods) > 0)
		{
			$ret .= "<p><b>Remote</b>: " . implode($remoteMethods, ' | ') . "</p>";
		}
		if(count($privateMethods) > 0)
		{
			$ret .= "<p><b>Private</b>: " . implode($privateMethods, ' | ') . "</p>";
		}
		return $ret;
	} 
	
	function generateCode()
	{
		$info = $this->gatherInfo();
		chdir($this->_basedir);
		
		$templates = array();
		if ($handle = opendir('templates'))
		{
			while (false !== ($file = readdir($handle))) 
			{
				chdir(dirname(__FILE__));
				if ($file != "." && $file != "..") 
				{
					if(is_file($this->_basedir . '/templates/' . $file))
					{
						$index = strrpos($file, '.');
						$before = substr($file, 0, $index);
						$after = substr($file, $index + 1);
						
						if($after == 'php')
						{
							include_once($this->_basedir . '/templates/' . $file);
							$templates[$before] = new $before;
						}
					}
				}
			}
		}
		
		function cmp ($a, $b)
		{ 
			 if ($a->priority == $b->priority) return 0;
			 return ($a->priority < $b->priority) ? 1 : -1;
		}
		# the index is the second element of
		# each row
		
		usort($templates, "cmp");
		
		
		
		$section = array();
		foreach($templates as $key => $tpl)
		{
			$key = get_class($tpl);
			$code = $tpl->format($info);
			$text = ('<p>' . "<span style='float:right'><a href='javascript:selectCode(\"$key\");'>Select text</a></span>" . $tpl->description . "</p>");
			$text .= "<textarea class='codex' id='ascode_$key' name='ascode_$key'>" . $code . '</textarea>';
			$section[$key] = array('code' => $text, 'description' => $tpl->description);
		}
		
		return $section;
	}
	
	function gatherInfo()
	{
		if(!isset($this->info))
		{
			$info = array();
			$info['package'] = str_replace("/" , "." , dirname($this->_classpath));
			if($info['package'] == '.')
			{
				$info['package'] = "";
			}
			else
			{
				$info['package'] .= ".";
			}
			$info['class'] = $this->_classname;
			$info['server'] = $_SERVER['SERVER_NAME'];
			
			$auth = false;
			$methods = array();
			
			require_once(AMFPHP_BASE . 'util/MethodTable.php');
			$mt = MethodTable::create(substr($_GET['class'], strrpos('/' . $_GET['class'], '/')) . '.php');
			
			foreach ($this->_classConstruct->methodTable as $name => $props) 
			{
				if($props['access'] == 'remote')
				{
					$typedArgs = "";
					$untypedArgs = "";
					
					if(!isset($props['arguments']))
					{
						$props['arguments'] = $mt[$name]['arguments'];
					}
					foreach($props['arguments'] as $key => $value) {
						if(!is_array($value))
						{
							$arg = ', ' . substr($value . ' -', 0, strpos($value . ' -','-') - 1);
							$typedArgs .= $arg;
							$untypedArgs .= $arg;
						}
						else
						{
							$untypedArgs .= ', ' . $key ;
							if(isset($value['type']))
							{
								if(!isset($this->phpToAsTypes[strtolower($value['type'])]))
								{
									$typedArgs .= ', ' . $key . ':' . $value['type'];
								}
								else
								{
									$typedArgs .= ', ' . $key . ':' . $this->phpToAsTypes[strtolower($value['type'])];
								}
							}
							else
							{
								$typedArgs .= ', ' . $value;
							}
							
						}
					} 

					$typedArgs = substr($typedArgs, 2);
					$untypedArgs = substr($untypedArgs, 2);
					
					$methods[] = array(
						"description" => $props['description'],
						"methodName" => $name,
						"args" => $untypedArgs,
						"typedArgs" => $typedArgs,
					);
					
					if(isset($props['roles']))
					{
						//At least one method requires auth
						$auth = true;
					}
				}
			}
			$info['methods'] = $methods;
			$info['auth'] = $auth;
			$info['gatewayUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . str_replace('//', '/', str_replace("%2F","/",rawurlencode (str_replace('\\', '/', dirname(dirname($_SERVER['PHP_SELF']))))) . '/gateway.php');
			
			$this->info = $info;
			return $info;
		}
		else
		{
			return $this->info;
		}
	}
	
	/**
	 * Prints out the headers and footers of the method testing page and
	 * either prints a form (through _printForm) for the user to enter arguments or, if arguments
	 * are provided, prints the result of the method (through printResult).
	 */
	function testMethod($name) {
		if(isset($this->_classConstruct->methodTable[$name]['arguments']))
		{
			return $this->_classConstruct->methodTable[$name]['arguments'];
		}
		else
		{
			require_once(AMFPHP_BASE . 'util/MethodTable.php');
			$mt = MethodTable::create(substr($_GET['class'], strrpos('/' . $_GET['class'], '/')) . '.php');
			return $mt[$name]['arguments'];
		}
	} 

	/**
	 * Prints out a table containing the method name and its
	 * properties.
	 * 
	 * @param string $name The name of the method.
	 * @param array $methodproperties The method properties, the information in the method table.
	 */
	function _printMethod($name, $methodproperties, $selected) {
		// header - Method name and link
		$method = '<div id="methods">';
		$method .= '<table class="methodTable" style="width:100%" cellspacing="2" cellpadding="3">';
		$method .= '<tr class="header"><td class="header" colspan=2>';
		$method .= $name . ' - ' . substr($methodproperties['description'], 0, 80) . '</td>';
		$method .= '</td></tr>';
		foreach($methodproperties as $property => $value) {
			if (!is_array($value)) {
				$method .= $this->_printMethodProp($property, $value);
			} else {
				$method .= $this->_printArgs($property, $value);
			}
		}
		
		$methodName = $name;
		
		if($methodproperties['access'] == 'remote')
		{
			$arguments = $this->testMethod($name);
			
			$method .= '<form action="' . $_SERVER['PHP_SELF'] . '?class=' . substr($this->_classpath, 0, -4) . '&method=' . $name . '&action=exec" method="POST" id="form">' ."\n";
			$method .= '<tr><td class="formcaption" colspan="2">Arguments</td></tr>'."\n";
			$key = 0;
			foreach($arguments as $key => $name) {
				$method .= '<tr class="caption"><td class="key">' . $name . '</td><td><input class="inputbox" type="text" name="' . $methodName . '_arguments[]" maxlength="65535" value="' . htmlspecialchars(strip($_POST[$methodName . "_arguments"][$key])) . '"/></td></tr>'."\n";
			}
			//$method .= '<tr><td class="formcaption" colspan="2">Optional arguments</td></tr>';
			//for($i = 0; $i < 5; $i++) {
			//    $method .= '<tr class="inputrow"><td class="key">arg' . ($i + 1) . '</td><td><input class="inputbox" type="text" name="' . $methodName . '_arguments[]" maxlength="65535" value="' . htmlspecialchars(strip($_POST[$methodName . "_arguments"][$i + $key + 1])) . '"/></td></tr>'."\n";
			//}
			$method .= '<tr><td class="formcaption" colspan="2">Authentication</td></tr>'."\n";
			$method .='<tr class="inputrow"><td class="key">Username</td><td><input class="inputbox" type="text" name="username" maxlength="65535" value="' . htmlspecialchars(strip($_POST['username'])) . '"/></td></tr>'."\n";
			$method .='<tr class="inputrow"><td class="key">Password</td><td><input class="inputbox" type="text" name="password" maxlength="65535" value="' . htmlspecialchars(strip($_POST['password'])) . '"/></td></tr>'."\n";
			$method .= '<tr class="inputrow"><td colspan="2" class="submitbtn"><input type="submit">'."\n";
			$method .= '</td></tr></form>'."\n";
			
			
		}
		$method .= '</table></div>';
		return $method;
	}
	/**
	 * Prints a row of a table containing the a property of the method table
	 * This method copes with the description, access, roles, instance and
	 * alias entries in the method table. Arguments are printed by _printArgs.
	 * 
	 * @param string $property The name of the property.
	 * @param string $value The value of the property.
	 */
	function _printMethodProp($property, $value) {
		return '<tr class="methodrow"><td class="key">' . nl2br($property) . '</td><td class="value">' . nl2br($value) . '</td></tr>';
	} 
	/**
	 * Prints a row of a table containing the an meta information of
	 * an argument (taken from the method table).
	 * The meta information is printed out in a similar format to the
	 * Flash MX Service Browser.
	 * 
	 * @param string $property The name of the property.
	 * @param string $value The value of the property.
	 */
	function _printArgs($property, $value) {
		$args = '<tr class="argrow"><td class="key">' . $property . '</td><td class="value">';
		if (count($value) == 0) {
			$args .= '<span>[none]</span>';
		} else {
			foreach($value as $subproperty => $subvalue) {
				if(!is_array($subvalue))
				{
					$args .= $subvalue . '<br />';
				}
				else
				{
					$args .= '<pre>' . $subproperty . " => ";
					ob_start();
					print_r($subvalue);
					$args .= ob_get_clean();
					$args .= '</pre>';
				}
			} 
		}
		return $args . '</td></tr>';
	} 
	
	/**
	 * Prints the final result of a tested method
	 *
	 * @param mixed $result The result of the execution of the method.
	 */
	function _printResult($result) {
		echo '<div id="results">';
		echo '<table class="resultstable">';
		echo '<caption class="resultscaption">Output of: ' . $this->_classpath . '</caption>';
		echo '<tr class="resultsrow">';
		if (is_object($result) || is_array($result) || is_resource($result)) {
			echo '<td>';
			echo '<code>';
			print_r($result);
			echo '</code>';
			echo '</td>';
		} else {
			echo '<td><code class="resultstext">' . $result . '</code></td>';
		} 
		echo '</tr>';
		echo '</table>';
		echo '</div>';
	}
	
	function saveCode($type, $location, $overwrite)
	{
		$info = $this->gatherInfo();
		$result = include_once($this->_basedir . '/templates/' . str_replace('..', '', $type) . '.php');
		if(!$result)
		{
			die('<p>Could not include template type</p>');
		}
		
		global $cfg;
		
		$template = new $type;
		$result = $template->save($info, $cfg['CodePath'] . '/' . str_replace('..', '', $location), $overwrite);
		return $result;
	}
}

function makeDirs($strPath) //creates directory tree recursively
{
   return is_dir($strPath) or ( makeDirs(dirname($strPath)) and mkdir($strPath) );
}

function strip($val)
{
	if(get_magic_quotes_gpc())
	{
		return stripslashes($val);
	}
	else
	{
		return $val;
	}
}

?>
