<?php
error_reporting(E_ALL ^E_NOTICE);

/*********************************************************************************************************************\
 *
 * AUTHOR
 * =============
 * Kwaku Otchere 
 * ospinto@hotmail.com
 * 
 * Thanks to Andrew Hewitt (rudebwoy@hotmail.com) for the idea and suggestion
 * 
 * All the credit goes to ColdFusion's brilliant cfdump tag
 * Hope the next version of PHP can implement this or have something similar
 * I love PHP, but var_dump BLOWS!!!
 *
 * FOR DOCUMENTATION AND MORE EXAMPLES: VISIT http://dbug.ospinto.com
\*********************************************************************************************************************/
?>
<?php
class dBug {
	
	var $xmlDepth=array();
	var $xmlCData;
	var $xmlSData;
	var $xmlDData;
	var $xmlCount=0;
	var $xmlAttrib;
	var $xmlName;
	var $arrType=array("array","object","resource","boolean");
	
	//constructor
	function dBug($var,$forceType="") {
		$arrAccept=array("array","object","xml"); //array of variable types that can be "forced"
		if(in_array($forceType,$arrAccept))
			$this->{"varIs".ucfirst($forceType)}($var);
		else
			$this->checkType($var);
	}
	
	//create the main table header
	function makeTableHeader($type,$header,$colspan=2) {
		echo "<table cellspacing=2 cellpadding=3 class=\"dBug_".$type."\">
				<tr>
					<td class=\"dBug_".$type."Header\" colspan=".$colspan." style=\"cursor:hand\" onClick='dBug_toggleTable(this)'>".$header."</td>
				</tr>";
	}
	
	//create the table row header
	function makeTDHeader($type,$header) {
		echo "<tr>
				<td valign=\"top\" onClick='dBug_toggleRow(this)' style=\"cursor:hand\" class=\"dBug_".$type."Key\">".$header."</td>
				<td>";
	}
	
	//close table row
	function closeTDRow() {
		return "</td>\n</tr>\n";
	}
	
	//error
	function  error($type) {
		$error="Error: Variable is not a";
		//thought it would be nice to place in some nice grammar techniques :)
		// this just checks if the type starts with a vowel or "x" and displays either "a" or "an"
		if(in_array(substr($type,0,1),array("a","e","i","o","u","x")))
			$error.="n";
		return ($error." ".$type." type");
	}

	//check variable type
	function checkType($var) {
		switch(gettype($var)) {
			case "resource":
				$this->varIsResource($var);
				break;
			case "object":
				$this->varIsObject($var);
				break;
			case "array":
				$this->varIsArray($var);
				break;
			case "boolean":
				$this->varIsBoolean($var);
				break;
			default:
				$var=($var=="") ? "[empty string]" : $var;
				echo "<table cellspacing=0><tr>\n<td>".htmlspecialchars($var)."</td>\n</tr>\n</table>\n";
				break;
		}
	}
	
	//if variable is a boolean type
	function varIsBoolean($var) {
		$var=($var==1) ? "TRUE" : "FALSE";
		echo $var;
	}
			
	//if variable is an array type
	function varIsArray($var) {
		$this->makeTableHeader("array","array");
		if(is_array($var)) {
			foreach($var as $key=>$value) {
				$this->makeTDHeader("array",$key);
				if(in_array(gettype($value),$this->arrType))
					$this->checkType($value);
				else {
					$value=(trim($value)=="") ? "[empty string]" : $value;
					echo htmlspecialchars($value)."</td>\n</tr>\n";
				}
			}
		}
		else echo "<tr><td>".$this->error("array").$this->closeTDRow();
		echo "</table>";
	}
	
	//if variable is an object type
	function varIsObject($var) {
		$this->makeTableHeader("object",get_class($var) == 'stdClass' ? 'object' : get_class($var));
		$arrObjVars=get_object_vars($var);
		if(is_object($var)) {
			foreach($arrObjVars as $key=>$value) {
				$value=(trim($value)=="") ? "[empty string]" : $value;
				$this->makeTDHeader("object",$key);
				if(in_array(gettype($value),$this->arrType))
					$this->checkType($value);
				else echo $value.$this->closeTDRow();
			}
			/*
			$arrObjMethods=get_class_methods(get_class($var));
			foreach($arrObjMethods as $key=>$value) {
				$this->makeTDHeader("object",$value);
				echo "[function]".$this->closeTDRow();
			}*/
		}
		else echo "<tr><td>".$this->error("object").$this->closeTDRow();
		echo "</table>";
	}

	//if variable is a resource type
	function varIsResource($var) {
		$this->makeTableHeader("resourceC","resource",1);
		echo "<tr>\n<td>\n";
		echo get_resource_type($var).$this->closeTDRow();
		echo $this->closeTDRow()."</table>\n";
	}
}
?>