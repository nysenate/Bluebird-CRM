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

	$config = array();
	$nConfig = 0;
	$domain = array();
	$list = array();

	readSettings("config.cfg");
	//readDBlist("databases.cfg");
	readInstances();
	readHelpFile("help.cfg");
?>

<form method="post" action="index2.php" onsubmit="javascript:givememore();" >

<div id="script-tree">
<h3>Scripts available <span class="small" id="multi-link" style="float:right;margin:3px 15px 0px;"><a href="javascript:multitest();">Multi test</a></span></h3>

<?php

// display the list of scripts

for ($i = 0; $i < $nConfig; $i++) {
   	echo "<p>";
   	echo "<input type='radio' name='testName' value='".$config[$i]->fileName."' onclick=\"javascript:radioclick($i,".$config[$i]->id.");\" />";
   	echo "<input type='checkbox' name='check[]' value='".$config[$i]->fileName."' />";
   	echo $config[$i]->displayName;
   	echo "<div class=\"small-help\" id=\"help_id".$config[$i]->id."\">";
   	echo $config[$i]->help;
   	echo "</div>\n";
   	echo "</p>\n";
}

?>

</div><!-- script tree -->


<div id="settings">
<h3>Last tests</h3>

<?php

// display list of last tests

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

	$host_part = substr($row['host'],0,19);
	if ($row['host'] != $host_part)
		$host_part.="...";
	
	$test_part = substr($row['testname'],0,23);
	if ($row['testname'] != $test_part)
		$test_part.="...";

	echo "<tr><td ".(($i%2==0)?"class=\"even\"":"")."><a href=\"?update=1&link=".$row['tid']."\">".$host_part;
	echo "</a></td>";
	echo "<td ".(($i%2==0)?"class=\"even\"":"").">".$test_part."</td>";

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
if ($update==1) {              // read some patricular settings	
	$tid = $_GET['link'];
	$query = "SELECT * FROM `test` WHERE `tid`='$tid';";
} else {                       // no settings specified - load the last one
	$query = "SELECT * FROM `test` WHERE TRUE ORDER BY `tid` DESC LIMIT 1;";
}
$result = mysql_query($query, $link);
$row = mysql_fetch_array($result);

$host = $row['host'];                    // cut everything except for district name e.g. sd99
if (substr($host,0,7)=="http://") {
	$host_full = substr($host, 7);
	$_host = explode("/", $host_full);
	$_host = explode(".", $_host[0]);	
	$host = $_host[0];
	$envir = substr($host_full, strlen($host));  // current environment
}

?>

<label>Browser:</label>
<select name="browser">
  <option value="*firefox" <?php if ($row['browser']=="*firefox") echo "selected"; ?> >Firefox</option>
  <option value="*googlechrome" <?php if ($row['browser']=="*googlechrome") echo "selected"; ?>>Chrome</option>
  <option value="*iexplore" <?php if ($row['browser']=="*iexplore") echo "selected"; ?>>Internet Explorer</option>
</select> <br />

<label>Environment:</label>
<select id="envir-select" name="domain">
<?php
	foreach ($domain as $d) {
		echo "<option id=\"".$d['id']."\" value=\"".$d['domain']."\">".$d['domain']."</option>";
	}
?>
</select><br/>

<label>Host:</label>
<select id="host-list" name="host">

</select><br />

<h3 id="more-settings-link-h3"><a id="more-settings-link" href="javascript:givememore();">More settings ...</a></h3>

<div id="more-settings" style="display:none;">
	<!--<label>Number of instances:</label>
	<input type="text" name="nins" class="text" value="1" onkeydown="return kd(event)"><br />-->

	<label>Username:</label>
	<input style="width:150px;" type="text" name="username" class="text" value="<?php echo $row['username']; ?>" onkeydown="return kd(event)"><br />

	<label>Password:</label>
	<input style="width:150px;" type="text" name="password" class="text" value="<?php echo $row['password']; ?>" onkeydown="return kd(event)"><br />

	<label>Pause:</label>
	<input type="text" name="sleep" class="text" value="<?php echo $row['sleep']; ?>" onkeydown="return kd(event)"><br />
</div><!-- /more-settings-->


</div><!-- /general settings -->

<div class="new-settings" id="new-settings" style="display:none;">
<h3>Specific settings</h3>

<div id="comment"></div>
<br>
<label id="SearchName_label">Search name:</label>
<input id="SearchName_input" type="text" name="searchname" class="text" value="<?php echo $row['searchname']; ?>" onkeydown="return kd(event)"><br />

<label id="SearchEmail_label">Search email:</label>
<input id="SearchEmail_input" type="text" name="searchemail" class="text" value="<?php echo $row['searchemail']; ?>" onkeydown="return kd(event)"><br />

<label id="SpouseName1_label">Spouse name 1:</label>
<input id="SpouseName1_input" type="text" name="spousename1" class="text" value="<?php echo $row['spousename1']; ?>" onkeydown="return kd(event)"><br />

<label id="SpouseName2_label">Spouse name 2:</label>
<input id="SpouseName2_input" type="text" name="spousename2" class="text" value="<?php echo $row['spousename2']; ?>" onkeydown="return kd(event)"><br />


<input type="hidden" name="save" id="save" value="yes" /> <!-- IGNORE THIS LINE! -->
<input type="hidden" name="multi" id="multi" value="" />


</div><!-- /specific settings -->


<div style="clear:both;" id="clear"></div>

<input type="submit" id="submit" onclick="javascript:actionBar();" value="Start Test" />
<div id="wait">Please wait...</div>

<div style="clear:both;" id="clear"></div>
</form>



<?php 
	mysql_close($link);
?>
</div> <!-- main -->


<script type="text/javascript">

var vis = new Array();
for (i=0;i<4;i++)
	vis[i] = false;

function kd(e) {
    // var intKey = (window.Event) ? e.which : e.keyCode;
    // document.getElementById("save").value = "yes";
    // return true;
}

<?php
	echo "var myFiles = new Array();\n";
	echo "var myComments = new Array();\n";	
	for($i = 0; $i < $nConfig; $i++) {
		echo "myFiles[$i] = \"".$config[$i]->settings."\";\n";
		echo "myComments[$i] = \"".$config[$i]->comment."\";\n";
	}
?>

function radioclick(fn, helpbox) {

	// hide all the help boxes
	<?php
		for ($i=0;$i<$nConfig;$i++)
			echo "document.getElementById(\"help_id\"+".$config[$i]->id.").style.display = \"none\";\n";
	?>

	// display the correct one
	document.getElementById("help_id"+helpbox).style.display = "inline-block";

	document.getElementById("new-settings").style.display = "none";          // just make all the Specific Settings invisible
	document.getElementById("SpouseName1_label").style.display = "none";
	document.getElementById("SpouseName1_input").style.display = "none";
	document.getElementById("SpouseName2_label").style.display = "none";
	document.getElementById("SpouseName2_input").style.display = "none";
	document.getElementById("SearchName_label").style.display = "none";
	document.getElementById("SearchName_input").style.display = "none";
	document.getElementById("SearchEmail_label").style.display = "none";
	document.getElementById("SearchEmail_input").style.display = "none";

	if (myFiles[fn]!="" || myComments[fn]!="") {
		document.getElementById("new-settings").style.display = "inline-block";
		document.getElementById("comment").innerHTML = "<p>"+myComments[fn]+"</p>";
	}

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

function givememore() {
	document.getElementById("more-settings-link").style.display = "none";
	document.getElementById("more-settings").style.height = "auto";
	document.getElementById("more-settings").style.display = "block";
}

function actionBar() {
	document.getElementById("submit").style.display = "none";
	document.getElementById("wait").style.display = "block";
}

</script>



<?php
	include_once "html/footer.php";
	?>