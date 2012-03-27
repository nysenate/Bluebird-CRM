<?php
	include_once "html/header.php";
	?>

<div id="main">

<?php
	if (isset($_GET['update'])) {
		$update = $_GET['update'];	
	} else {
		$update = 0;
	}	

	class configClass {
		public $displayName;
		public $fileName;
		public $settings;
	}

	$config = array();
	$nConfig = 0;

	// read configuration file
	function readSettings($filename) {
		global $config;
		global $nConfig;
		$sArray = array();
		
		$handle = @fopen($filename, "r");
		if ($handle) {
		    while (($buffer = fgets($handle, 4096)) != false ) {
		    	$buffer = trim($buffer);
		    	if (strlen($buffer)>0 && $buffer[0]!='#') {
		    		$sArray = explode("\t", $buffer);
			    	$cfg = new configClass();
			    	$cfg->displayName = $sArray[0];
			    	$cfg->fileName = trim($sArray[1]);
			    	$cfg->settings = trim($sArray[2]);
			    	$config[$nConfig++] = $cfg;
			    }		    
		    }
		}
		fclose($handle);
	}

	readSettings("config.cfg");
?>


<form method="post" action="index2.php" >

<div id="script-tree">
<h3>Scripts available</h3>

<?php

// display the list of scripts

for ($i = 0; $i < $nConfig; $i++) {
   	echo "<p>";
   	echo "<input type='radio' name='testName' value='".$config[$i]->fileName."' onclick=\"javascript:radioclick($i);\" />";
   	echo $config[$i]->displayName;
   	echo "</p>\n";
}

?>

</div><!-- script tree -->


<div id="settings">
<h3>Last tests</h3>

<?php

// display list of settings

$mysql_host = 'localhost';
$mysql_user = 'root';
$mysql_pwd  = 'mysql';
$mysql_db   = 'selenium';
$query = "SELECT * FROM `test` WHERE TRUE ORDER BY `tid` DESC LIMIT 9;";

$link = mysql_connect($mysql_host, $mysql_user, $mysql_pwd);
mysql_select_db($mysql_db);
$result = mysql_query($query, $link);

if (!mysql_num_rows($result)) {
	// empty database?
	$tmpQuery = "INSERT INTO `test`(`browser) VALUES ('*firefox');";
	$result_2 = mysql_query($tmpQuery, $link);
	$result = mysql_query($query, $link);
}

echo "<table>";
$i = 1;
while ($row = mysql_fetch_array($result)) {
	echo "<tr><td ".(($i%2==0)?"class=\"even\"":"")."><a href=\"?update=1&link=".$row['tid']."\">".$row['host'];
	echo "</a></td>";
	echo "<td ".(($i%2==0)?"class=\"even\"":"").">".$row['testname']."</td>";

	$time = $row['time'];
	$time = date("M,d g:i a",$time);	

	echo "<td ".(($i%2==0)?"class=\"even\"":"").">".$time."</td>";
	$i++;
}
echo "</table>";
?>



</div><!-- settings -->

<div class="new-settings">
<h3>General settings</h3>

<?php
if ($update==1) {
	
	$tid = $_GET['link'];
	$query = "SELECT * FROM `test` WHERE `tid`='$tid';";
} else {
	$query = "SELECT * FROM `test` WHERE TRUE ORDER BY `tid` DESC LIMIT 1;";
}
$result = mysql_query($query, $link);
$row = mysql_fetch_array($result);

?>

<label>Browser:</label>
<select name="browser">
  <option value="*firefox" <?php if ($row['browser']=="*firefox") echo "selected"; ?> >Firefox</option>
  <option value="*googlechrome" <?php if ($row['browser']=="*googlechrome") echo "selected"; ?>>Chrome</option>
  <option value="*iexplore" <?php if ($row['browser']=="*iexplore") echo "selected"; ?>>Internet Explorer</option>
</select> <br />


<label>Number of instances:</label>
<input type="text" name="nins" class="text" value="1" onkeydown="return kd(event)"><br />

<label>Host:</label>
<input type="text" name="host" class="text" value="<?php echo $row['host']; ?>" onkeydown="return kd(event)"><br />

<label>Username:</label>
<input type="text" name="username" class="text" value="<?php echo $row['username']; ?>" onkeydown="return kd(event)"><br />

<label>Password:</label>
<input type="text" name="password" class="text" value="<?php echo $row['password']; ?>" onkeydown="return kd(event)"><br />

<label>Pause:</label>
<input type="text" name="sleep" class="text" value="<?php echo $row['sleep']; ?>" onkeydown="return kd(event)"><br />



</div><!-- /general settings -->

<div class="new-settings" id="new-settings" style="display:none;">
<h3>Specific settings</h3>


<label id="SearchName_label">Search name:</label>
<input id="SearchName_input" type="text" name="searchname" class="text" value="<?php echo $row['searchname']; ?>" onkeydown="return kd(event)"><br />

<label id="SearchEmail_label">Search email:</label>
<input id="SearchEmail_input" type="text" name="searchemail" class="text" value="<?php echo $row['searchemail']; ?>" onkeydown="return kd(event)"><br />

<label id="SpouseName1_label">Spouse name 1:</label>
<input id="SpouseName1_input" type="text" name="spousename1" class="text" value="<?php echo $row['spousename1']; ?>" onkeydown="return kd(event)"><br />

<label id="SpouseName2_label">Spouse name 2:</label>
<input id="SpouseName2_input" type="text" name="spousename2" class="text" value="<?php echo $row['spousename2']; ?>" onkeydown="return kd(event)"><br />


<input type="hidden" name="save" id="save" value="yes" /> <!-- IGNORE THIS LINE! -->


</div><!-- /specific settings -->


<div style="clear:both;" id="clear"></div>

<input type="submit" id="submit" value="Start Test" />

<div style="clear:both;" id="clear"></div>
</form>



<?php 
	mysql_close($link);
?>
</div> <!-- main -->


<script type="text/javascript">
function kd(e) {
    // var intKey = (window.Event) ? e.which : e.keyCode;
    // document.getElementById("save").value = "yes";
    // return true;
}

<?php
	echo "var myFiles = new Array();\n";
	for($i = 0; $i < $nConfig; $i++) {
		echo "myFiles[$i] = \"".$config[$i]->settings."\";\n";
	}
?>

function radioclick(fn) {
	if (myFiles[fn]!="") 
		document.getElementById("new-settings").style.display = "inline-block";

	if (myFiles[fn]=="SpouseName") {
		document.getElementById(myFiles[fn]+"1_label").style.display = "inline-block";
		document.getElementById(myFiles[fn]+"1_input").style.display = "inline-block";
		document.getElementById(myFiles[fn]+"2_label").style.display = "inline-block";
		document.getElementById(myFiles[fn]+"2_input").style.display = "inline-block";

	} else if (myFiles[fn]!="") {
		document.getElementById(myFiles[fn]+"_label").style.display = "inline-block";
		document.getElementById(myFiles[fn]+"_input").style.display = "inline-block";
	}

}

</script>



<?php
	include_once "html/footer.php";
	?>