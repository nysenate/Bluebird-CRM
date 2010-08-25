<?php
	header("Content-type: text/html");

	session_start();
	
	error_reporting(E_ALL ^ E_NOTICE);
	
	include_once("config.inc.php");
	define("AMFPHP_BASE", $cfg['AmfphpPath'] . 'amf-core/');

	if($_GET['action'] != null)
	{
		if($_GET['action'] == 'expand')
		{
			$_SESSION['nodesOpen'] = true;
		}
		else
		{
			$_SESSION['nodesOpen'] = false;
		}
	}
	if($_SESSION['nodesOpen'] == null)
	{
		$_SESSION['nodesOpen'] = false;
	}

	include_once( "ServiceBrowser.php" );

	if( !is_dir( $cfg['ServicesPath'] )) 
	{
		$result =  "FATAL ERROR - Invalid ServicesPath<BR><BR>See right pane.";
	}
	else
	{
		$explorer = new ServiceBrowser($cfg['ServicesPath'],$cfg['OmitPath'] );
		$services = $explorer->listServices();
		ksort($services);
		
		$result = "";
		$nodesOpen = $_SESSION['nodesOpen'];
		$icon = !$nodesOpen ? 'treenodeplus.gif' : 'treenodeminus.gif';
		$class = !$nodesOpen ? 'treeSubnodesHidden' : 'treeSubnodes';
		foreach($services as $dirname => $dirvalue)
		{
			if($dirname != "zzz_default")
			{
				$result .= "<div class=\"treeNode\">\n";
				$result .= "<img src=\"images/$icon\" class=\"treeLinkImage\" onclick=\"expandCollapse(this.parentNode)\" />\n";
				$result .= "<a href=\"#\" class=\"treeUnselected\" onclick=\"clickAnchor(this)\">" . $dirname . "</a>\n";
				$result .= "<div class=\"$class\">\n";
			}
			
			foreach($dirvalue as $key => $value)
			{
				$result .= sprintf("<div class=\"treeNode\"><img src=\"images/treenodedot.gif\" class=\"treeNoLinkImage\" /><a href=\"details.php?class=%s\" target=\"details\" class=\"treeUnselected\" onclick=\"clickAnchor(this)\">%s</a> [<a href='methodTable.php?class=%s' target='details'>mt</a>] [<a href='code.php?class=%s' target='details'>code</a>]</div>\n"
					, $value[1] . $value[0], $value[0], $value[1] . $value[0], $value[1] . $value[0]);//,
			}
			
			if($dirname != "__default__")
			{
				$result .= "</div></div>";
			}
		}
	
		if( empty( $result )) 
		{
			$result = "No services found.<BR><BR>" .
			"<a href='details.php?class=__none__found__' target='details'>" .
					"Tell me more...</A>";
		}
	}
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Service browser</title>
<link rel="stylesheet" type="text/css" href="images/tree.css"/>
<style type="text/css">
a.top{
	color:white;
	text-decoration:none;
}
a.top:visited{
	color:white;
}
a.top:hover{
	color:white;
	text-decoration:underline;
}
a.top:active{
	color:white;
}
a{
	color:black;
}
a.top:visited{
	color:black;
}
a.top:hover{
	color:black;
}
a.top:active{
	color:black;
}
</style>
<script src="images/tree.js" language="javascript" type="text/javascript"></script>
</head>
<body id="docBody" style="width:100%; background-color: #BEC2CD; color: White; margin: 0px 0px 0px 0px;" onload="resizeTree();" onresize="resizeTree()" onselectstart="return false;">
<div style="padding:5px;"><div style="float:right; font-family: verdana; font-size: 8pt;"><a href="listing.php" class='top'>Reload</a> | <a href="listing.php?action=expand" class='top'>Expand</a> | <a class='top' href="listing.php?action=close">Collapse</a></div><div style="font-family: verdana; font-size: 8pt; text-align: left">Browser</div></div>
<div id="tree" style="top: 35px; left: 0px;" class="treeDiv">
<div id="treeRoot" onselectstart="return false" ondragstart="return false">
<p>

<?php echo $result ?>

</p>
</body>
</html>

