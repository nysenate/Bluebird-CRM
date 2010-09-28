<html>
<head>
<link rel="stylesheet" type="text/css" href="images/service-browser.css" />
<link rel="stylesheet" type="text/css" href="images/dBug.css" />
<script>
function selectCode(num)
{
	var a=document.getElementById("ascode_" + num);
	if(document.createTextRange) { var range = a.createTextRange(); a.select();  } else { 
	a.select(); }
}

function toggleTab(n, key)
{
	var i = 1;
	while(document.getElementById('tab' + String(i)) != null)
	{
		document.getElementById('tab' + String(i)).style.display = 'none';
		i++;
	}
	if(n != -1)
	{
		document.getElementById('tab' + String(n)).style.display = 'block';
	}
	document.getElementById('codeType').value = key;
}
</script>
</head>

<body>
<h1>Code generation for <?php echo $_GET['class'] . '.php' ?></h1>
<?php
include_once('config.inc.php');

$serviceBrowserName = "ServiceBrowser.php";
if( file_exists( $serviceBrowserName )) 
{           //  Get it relative to the current file name
	define("AMFPHP_BASE", $cfg['AmfphpPath'] . 'amf-core/');
	include_once( $serviceBrowserName );
} else {    //  Get it from the path
	die("AMFPHP path not set properly");
}
error_reporting(E_ALL ^ E_NOTICE);

$explorer = new ServiceBrowser($cfg['ServicesPath']);
if($explorer->setService($_GET['class']))
{
	if($_GET['action'] == 'save')
	{
		$result = $explorer->saveCode($_GET['codeType'], $_GET['location'], $_GET['overwrite']);
		if($result !== TRUE)
		{
			echo "<p class='error'>" . $result . "</p>";
		}
		else
		{
			echo "<p class='feedback'>Files saved succesfully</p>";
		}
	}
	
	$menu = array();
	$divs = "";
	$i = 1;
	foreach($explorer->generateCode() as $key => $val)
	{
		if(isset($_GET['codeType']))
		{
			$selected = $_GET['codeType'] == $key;
		}
		else
		{
			$selected = $i == 1;
		}
		
		if(!$selected)
		{
			$extra = " style='display:none'";
		}
		else
		{
			$extra = " style='display:block'";
		}
		
		$menu[] = "<a href='javascript:toggleTab($i,\"$key\")'>" . $val['description'] . "</a>";
		$divs .= "<div id='tab$i'$extra>" . $val['code'] . "</div>";
		$i++;
	}
	
	echo( "<p>Code type: " . implode($menu, ' | ') . "</p>");
	echo($divs);
	
	if(!isset($_GET['codeType']))
	{
		$_GET['codeType'] = 'as2';
	}
}
else
{
	ob_end_clean();
	echo("There is no class named " . str_replace('/', '.', $_GET['class']) . " with a valid methodTable declared in this file.</p>");
	exit();
}
?>
<div id='saveCode'>
<form method='get'>
	<input type='hidden' name='action' value='save'>
	<input type='hidden' name='codeType' id='codeType' value='<?php echo $_GET['codeType']; ?>'>
	<input type='hidden' name='class' value='<?php echo $_GET['class']; ?>'>
	<p>Save to <?php echo $cfg['CodePath'] ?> <input type='text' style='width:150px' name='location'>
		<input type='checkbox' name='overwrite' id='overwrite'><label for='overwrite'>Overwrite files</label>
	</p>
	<p><input type='submit' value='Save to disk' style='float:right'></p>
</form> 
</div>
</body>