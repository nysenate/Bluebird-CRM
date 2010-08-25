<?php header("Content-type: text/html; charset=UTF-8"); 
if(!function_exists('microtime_float'))
{
	function microtime_float()
	{
	   list($usec, $sec) = explode(" ", microtime());
	   return ((float)$usec + (float)$sec);
	}
}


//Stop service browser from whining
if(!class_exists('NetDebug'))
{
	class NetDebug
	{
		function trace()
		{
			
		}
	}
}

?>
<html>
<head>
	<title>Service <?php echo $_GET['class'] . '.php' ?></title>
	<link rel="stylesheet" type="text/css" href="images/service-browser.css" />
	<link rel="stylesheet" type="text/css" href="images/dBug.css" />
	<script src="images/dBug.js"></script>
	
	<script language="JavaScript1.2">

	function toggleResults(n)
	{
		var i = 1;
		while(document.getElementById('results' + String(i)) != null)
		{
			document.getElementById('results' + String(i)).style.display = 'none';
			i++;
		}
		if(n != -1)
		{
			document.getElementById('results' + String(n)).style.display = 'block';
		}
	}
	
	function testMethod(n)
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
	}
	</script>
</head>
<body>
<?php if(isset($_GET['class'])): ?><h1>Exploring <?php echo $_GET['class'] . '.php' ?></h1><?php endif; ?>
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

function getCfgPath() {

		//  Build the path name of the config file
	$cfgPath = $_SERVER[ 'PATH_TRANSLATED' ];
	$exploded = explode( '/', $cfgPath );
	array_pop( $exploded );
	array_push( $exploded, 'cfg.include.php' );
		$cfgPath = implode( '/', $exploded );
	
	return( $cfgPath );

}

if( !is_dir( $cfg['ServicesPath'] ))
{
	print( "FATAL ERROR - Invalid ServicesPath<BR><BR>\n" .
		   "You must edit the file <b>" . getCfgPath() . "</b> " . 
			   "and change the ServicesPath value to point to " .
			   "the directory where you installed the services " .
			   "directory of you AMFPHP installation. ");
}   

elseif( '__none__found__' == $_GET['class'] ) 
{
	$cfgPath = getCfgPath();

	print "

<font size=6><b>No services found</b></font><BR><BR>

According to settings made in:<BR> 
<b>$cfgPath</b><BR> 
your services should be located in the directory:<BR>
<b>{$cfg['ServicesPath']}</b>

<BR><BR>

<b>Suggestions:</b><BR>

If you haven't installed any services yet, you need to install at least one
service before using this program.  See the INSTALL file, or wiki for instructions
on installing a simple service for testing.

<BR><BR>

If your services are already installed in a different directory than the one 
listed above, you should edit the file cfg.include.php in the directory listed 
above and make the cfgPath point to the directory that contains your services.

<BR><BR>

If your services are installed in the proper directory
listed above you should make sure they have permissions such that your web server
can read them.

<BR><BR>

The name of the class within the service must EXACTLY match the name of the
file, including case.  Any differences will cause the service to be ignored.

<BR><BR>

Don't confuse the gateway with the service.  The gateway is the PHP file that
is executed by the Flash Player to invoke a service.  It must reside within
the DocumentRoot of the web server, and be visible to the world.  Services 
are class files that define things made available and reside in the 
/flashservices/services directory.  The entire /flashservices path can reside
either within DocumentRoot, or outside in a directory specified in the PHP
INCLUDE_PATH.
";

}


elseif(isset($_GET['class']))
{
	ob_start();

	$explorer = new ServiceBrowser($cfg['ServicesPath']);
	
	if($explorer->setService($_GET['class']))
	{
		if($_GET['action'] == 'exec')
		{
			include_once('client/AMFClient.php');
			//Add some custom mappings for RecordSets
			$GLOBALS['amfphp']['customMappingsPath'] = realpath(dirname(__FILE__)) . '/mappings/';

			require_once('JSON.php');
			include_once('dBug.php');
			
			$json = new Services_JSON();
			$arguments = $_POST[$_GET['method'] . '_arguments'];
			
			if(!isset($arguments) || !is_array($arguments))
			{
				$arguments = array();
			}
			foreach($arguments as $key => $value)
			{
				if(!is_numeric($value) && $value != 'false' && $value != 'true' && strpos($value, '[') === FALSE && strpos($value, '{') === FALSE && strpos($value, '"') === FALSE && strpos($value, "'") === FALSE && $value != 'null' && $value != '')
				{
					$value = '"' . $value . '"';
				}
				$arguments[$key] = $json->decode(strip($value));
			}
			
			$class = $_GET['class'];
			$method = $_GET['method'];
			
			// Find path to gateway and encode the string so spaces, etc. do not screw up the gateway URL
			// Create the gateway URL
			
			$sessionName = ini_get('session.name');
			$append = "";
			if(isset($_COOKIE[$sessionName]))
			{
				$append = '?' . $sessionName . '=' . $_COOKIE[$sessionName];
			}
			else if(isset($_GET[$sessionName]))
			{
				$append = '?' . $sessionName . '=' . $_GET[$sessionName];
			}
			
			$startTime = microtime_float();
			$client = new AMFClient($cfg['GatewayPath'] . $append);
			$amf = $client->createRequest(str_replace('/', '.', $class), $method, $arguments, $_POST['username'], $_POST['password']);
			$result = $client->doRequest($amf);
			$deltaTime = (microtime_float() - $startTime)*1000;
			
			if($result !== FALSE)
			{
				echo "<p id='methodList'><a href='javascript:toggleResults(1)'>Results</a> | ";
				echo "<a href='javascript:toggleResults(2)'>Trace headers</a> | ";
				echo "<a href='javascript:toggleResults(3)'>Arguments</a> | ";
				echo "<a href='javascript:toggleResults(4)'>Stats</a></p>";
				
				$results = $client->deserialize($result); 
				echo('<div id="results1">');
				foreach($results['bodies'] as $key => $body)
				{
					echo("<p>");
					new dBug($body); 
					echo("</p>");
				}
				
				echo('</div>');
				echo('<div id="results2" style="display:none">');
				new dBug($results['trace']);
				echo('</div>');
				echo('<div id="results3" style="display:none">');
				new dBug($arguments);
				echo('</div>');
				echo('<div id="results4" style="display:none">');
				printf("<p>Query took: %d ms, %.2f KB received</p>", $deltaTime, strlen($result)/1024);
				printf("<p>Sent using %s</p>", $client->getRequestSignature());
				echo("<p>Note: Query time includes request roundtrip time. Subtract ping time for actual processing time. For localhost queries using cURL subtract 20-30ms, for PEAR subtract 40-50ms.</p>");
				echo('</div>');
			}
			else
			{
				echo "<p class='error'><pre>" . $client->getLastError() . "</pre></p>";
			}
		}
		
		echo '<div id="methodList">'. $explorer->listMethodsShort() . '</div>';
		$i = 1;
		$extra = "";
		foreach($explorer->listMethods() as $name => $method)
		{
			if(isset($_GET['method']))
			{
				$selected = $_GET['method'] == $name;
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
			echo "<div id='tab$i'$extra>" . $method . '</div>';
			$i++;
		}

		ob_end_flush();
	}
	else
	{
		ob_end_clean();
		echo("There is no class named " . str_replace('/', '.', $_GET['class']) . " with a valid methodTable declared in this file.</p></body></html>");
	}
}
?>
