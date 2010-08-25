<?php
/**
 * Creates the methodTable for a service class.
 *
 * @usage $this->methodTable = MethodTable::create($this);
 * @author Christophe Herreman
 * @since 05/01/2005
 * @version $id$
 * 
 * Special contributions by Allessandro Crugnola and Ted Milker
 */

if (!defined('T_ML_COMMENT')) {
   define('T_ML_COMMENT', T_COMMENT);
} else {
   define('T_DOC_COMMENT', T_ML_COMMENT);
}

function strrstr($haystack, $needle)
{
	return substr($haystack, 0, strpos($haystack.$needle,$needle));
}

function strstrafter($haystack, $needle)
{
	return substr(strstr($haystack, $needle), strlen($needle));
}

class MethodTable
{
	/**
	 * Constructor.
	 *
	 * Since this class should only be accessed through the static create() method
	 * this constructor should be made private. Unfortunately, this is not possible
	 * in PHP4.
	 *
	 * @access private
	 */
	function MethodTable(){
	}


	/**
	 * Creates the methodTable for a passed class.
	 *
	 * @static
	 * @access public
	 * @param $className(String) The name of the service class.
	 *        May also simply be __FILE__
	 * @param $servicePath(String) The location of the classes (optional)
	 */
	function create($className, $servicePath = NULL){
		
		$methodTable = array();
		if(file_exists($className))
		{
			//The new __FILE__ way of doing things was used
			$sourcePath = $className;
		}
		else
		{
			$className = str_replace('.php', '', $className);
			$fullPath = str_replace('.', '/', $className);
			$className = $fullPath;
			if(strpos($fullPath, '/') !== FALSE)
			{
				$className = substr(strrchr($fullPath, '/'), 1);
			}
			
			if($servicePath == NULL)
			{
				if(isset($GLOBALS['amfphp']['classPath']))
				{
					$servicePath = $GLOBALS['amfphp']['classPath'];
				}
				else
				{
					$servicePath = "../services/";
				}
			}
			$sourcePath = $servicePath . $fullPath . ".php";
		}
		
		if(!file_exists($sourcePath))
		{
			trigger_error("The MethodTable class could not find {" . 
				$sourcePath . "}", 
				E_USER_ERROR);
		}
		
		$source = file_get_contents($sourcePath);
		$tokens = token_get_all($source);
		
		$waitingForOpenParenthesis = false;
		$waitingForFunction = false;
		$waitingForClassName = false;
		$bufferingArgs = false;
		$argBuffer = "";
		$lastFunction = "";
		$lastFunctionComment = "";
		$lastComment = "";
		$classMethods = array();
		$realClassName = "";

		$openBraces = -10000;

		$waitingForEndEncapsedString = false;
		foreach($tokens as $token)
		{
		   if (is_string($token)) {
				if($token == '{')
				{
					$openBraces++;
				}
				if($token == '}')
				{
					if($waitingForEndEncapsedString)
					{
						$waitingForEndEncapsedString = false;
					}
					else
					{
						$lastComment = '';
						$openBraces--;
						
						if($openBraces == 0)
						{
							break;
						}
					}
				}
				elseif($waitingForOpenParenthesis && $token == '(')
				{
					$bufferingArgs = true;
					$argBuffer = "";
					$waitingForOpenParenthesis = false;
				}
				elseif($bufferingArgs)
				{
					if($token != ')')
					{
						$argBuffer .= $token;
					}
					else
					{
						if($lastFunction != $realClassName)
						{
							$classMethods[] = array("name" => $lastFunction,
											   "comment" => $lastFunctionComment,
											   "args" => $argBuffer);
							
							$bufferingArgs = false;
							$argBuffer = "";
							$lastFunction = "";
							$lastFunctionComment = "";
						}
					}
					
				}
		   } else {
			   // token array
			   list($id, $text) = $token;
				
				if($bufferingArgs)
				{
					$argBuffer .= $text;                    
				}
			   switch ($id) 
			   {
					
				   case T_COMMENT:
				   case T_ML_COMMENT: // we've defined this
				   case T_DOC_COMMENT: // and this
				   // no action on comments
						$lastComment = $text;
						break;
				   case T_FUNCTION:
						if($openBraces >= 1)
						{
							$waitingForFunction = true;
						}
						break;
					case T_STRING:
						if($waitingForFunction)
						{
							$waitingForFunction = false;
							$waitingForOpenParenthesis = true;
							$lastFunction = $text;
							$lastFunctionComment = $lastComment;
							$lastComment = "";              
						}
						if($waitingForClassName)
						{
							$waitingForClassName = false;
							$realClassName = $text;
						}
						break;
					case T_CLASS:
						$openBraces = 0;
						$waitingForClassName = true;
						break;
					case T_CURLY_OPEN:
					case T_DOLLAR_OPEN_CURLY_BRACES:
						$waitingForEndEncapsedString = true;
						break;
				}
			}
		}
		
		foreach ($classMethods as $key => $value) {
			$methodSignature = $value['args'];
			$methodName = $value['name'];
			$methodComment = $value['comment'];
			
			$description = MethodTable::getMethodDescription($methodComment) . " " . MethodTable::getMethodCommentAttribute($methodComment, "desc");
			$description = trim($description);
			$access = MethodTable::getMethodCommentAttributeFirstWord($methodComment, "access");
			$roles = MethodTable::getMethodCommentAttributeFirstWord($methodComment, "roles");
			$instance = MethodTable::getMethodCommentAttributeFirstWord($methodComment, "instance");
			$returns = MethodTable::getMethodCommentAttributeFirstLine($methodComment, "returns");
			$pagesize = MethodTable::getMethodCommentAttributeFirstWord($methodComment, "pagesize");
			$params = MethodTable::getMethodCommentArguments($methodComment);
						
			//description, arguments, access, [roles, [instance, [returns, [pagesize]]]]
			$methodTable[$methodName] = array();
			//$methodTable[$methodName]["signature"] = $methodSignature; //debug purposes
			$methodTable[$methodName]["description"] = ($description == "") ? "No description given." : $description;
			$methodTable[$methodName]["arguments"] = MethodTable::getMethodArguments($methodSignature, $params);
			$methodTable[$methodName]["access"] = ($access == "") ? "private" : $access;
			
			if($roles != "") $methodTable[$methodName]["roles"] = $roles;
			if($instance != "") $methodTable[$methodName]["instance"] = $instance;
			if($returns != "") $methodTable[$methodName]["returns"] = $returns;
			if($pagesize != "") $methodTable[$methodName]["pagesize"] = $pagesize;
		}
		
		return $methodTable;
		
	}
	
	/**
	 * 
	 */
	function getMethodCommentArguments($comment)
	{
		$pieces = explode('@param', $comment);
		$args = array();
		if(is_array($pieces) && count($pieces) > 1)
		{
			for($i = 0; $i < count($pieces) - 1; $i++)
			{
				$ps = strrstr($pieces[$i + 1], '@');
				$ps = strrstr($ps, '*/');
				$args[] = MethodTable::cleanComment($ps);
			}
		}
		return $args;
	}
	
	
	/**
	 * Returns the description from the comment.
	 * The description is(are) the first line(s) in the comment.
	 *
	 * @static
	 * @private
	 * @param $comment(String) The method's comment.
	 */
	function getMethodDescription($comment){
		$comment = MethodTable::cleanComment(strrstr($comment, "@"));
		return trim($comment);
	}
	
	
	/**
	 * Returns the value of a comment attribute.
	 *
	 * @static
	 * @private
	 * @param $comment(String) The method's comment.
	 * @param $attribute(String) The name of the attribute to get its value from.
	 */
	function getMethodCommentAttribute($comment, $attribute){
		$pieces = strstrafter($comment, '@' . $attribute);
		if($pieces !== FALSE)
		{
			$pieces = strrstr($pieces, '@');
			$pieces = strrstr($pieces, '*/');
			return MethodTable::cleanComment($pieces);
		}
		return "";
	}
	
	/**
	 * Returns the value of a comment attribute.
	 *
	 * @static
	 * @private
	 * @param $comment(String) The method's comment.
	 * @param $attribute(String) The name of the attribute to get its value from.
	 */
	function getMethodCommentAttributeFirstLine($comment, $attribute){
		$pieces = strstrafter($comment, '@' . $attribute);
		if($pieces !== FALSE)
		{
			$pieces = strrstr($pieces, '@');
			$pieces = strrstr($pieces, "*");
			$pieces = strrstr($pieces, "/");
			$pieces = strrstr($pieces, "-");
			$pieces = strrstr($pieces, "\n");
			$pieces = strrstr($pieces, "\r");
			$pieces = strrstr($pieces, '*/');
			return MethodTable::cleanComment($pieces);
		}
		return "";
	}
	
	function getMethodCommentAttributeFirstWord($comment, $attribute){
		$pieces = strstrafter($comment, '@' . $attribute);
		if($pieces !== FALSE)
		{
			$val = MethodTable::cleanComment($pieces);
			return trim(strrstr($val, ' '));
		}
		return "";
	}
	
	/**
	 * Returns an array with the arguments of a method.
	 *
	 * @static
	 * @access private
	 * @param $methodSignature (String)The method's signatureg;
	 */
	function getMethodArguments($methodSignature, $commentParams){
		if(strlen($methodSignature) == 0){
			//no arguments, return an empty array
			$result = array();
		}else{
			//clean the arguments before returning them
			$result = MethodTable::cleanArguments(explode(",", $methodSignature), $commentParams);
		}
		
		return $result;
	}
	
	
	/**
	 * Cleans the arguments array.
	 * This method removes all whitespaces and the leading "$" sign from each argument
	 * in the array.
	 *
	 * @static
	 * @access private
	 * @param $args(Array) The "dirty" array with arguments.
	 */
	function cleanArguments($args, $commentParams){
		$result = array();
		
		foreach($args as $index => $arg){
			$arg = strrstr(substr(trim($arg), 1), '=');
			if(!isset($commentParams[$index]))
			{
				$result[] = trim($arg);
			}
			else
			{
				$start = trim($arg);
				$end = trim(str_replace('$', '', $commentParams[$index]));
				if(strpos(strtolower($end), strtolower($start)) == 0)
				{
					$end = substr($end, strlen($start));
				}
				$result[] = $start . ' - ' . trim($end);
			}
		}
		
		return $result;
	}
	
	
	/**
	 * Cleans the comment string by removing all comment start and end characters.
	 *
	 * @static
	 * @private
	 * @param $comment(String) The method's comment.
	 */
	function cleanComment($comment){
		$comment = str_replace("/**", "", $comment);
		$comment = str_replace("*/", "", $comment);
		$comment = str_replace("*", "", $comment);
		$comment = str_replace("\n", "\\n", trim($comment));
		$comment = eregi_replace("[\r\t\n ]+", " ", trim($comment));
		$comment = str_replace("\"", "\\\"", $comment);
		return $comment;
	}

	/**
	 *
	 */
	function showCode($methodTable){
		

		foreach($methodTable as $methodName=>$methodProps){
			$result .= "\n\t\"" . $methodName . "\" => array(";
			
			foreach($methodProps as $key=>$value){
				$result .= "\n\t\t\"" . $key . "\" => ";

				if($key=="arguments"){
					$result .= "array(";
					for($i=0; $i<count($value); $i++){
						$result .= "\"" . addslashes($value[$i]) . "\"";
						if($i<count($value)-1){
							$result .= ", ";
						}
					}
					$result .= ")";
				}else{
					$result .= "\"" . $value . "\"";
				}

				$result .= ",";
			}
			
			$result = substr($result, 0, -1);
			$result .= "\n\t),";
		}
		
		$result = substr($result, 0, -1);
		$result = "\$this->methodTable = array(" . $result;
		$result .= "\n);";
			
		return $result;
	}
}
?>