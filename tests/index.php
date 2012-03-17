<?php
	include_once "html/header.php";
	?>

<div id="main">

<?php
	$update = $_GET['update'];
?>


<form method="post" action="index2.php" >

<div id="script-tree">
<h3>Scripts available</h3>

<?php
if ($handle = opendir('.')) {
    while (false !== ($entry = readdir($handle))) {
        if (substr($entry,0,4)=='test') {
        	echo "<p>";
        	echo "<input type='radio' name='testName' value='$entry' />";
        	echo substr($entry, 4);
        	echo "</p>\n";
    	}
    }
    closedir($handle);
}
?>

</div><!-- script tree -->


<div id="settings">
<h3>Last Test Runs</h3>

<?php
// display list of settings
$mysql_host = 'localhost';
$mysql_user = 'root';
$mysql_pwd  = 'mysql';
$mysql_db   = 'selenium';
$query = "SELECT * FROM `test` WHERE TRUE ORDER BY `tid` DESC LIMIT 10;";

$link = mysql_connect($mysql_host, $mysql_user, $mysql_pwd);
mysql_select_db($mysql_db);
$result = mysql_query($query, $link);
echo "<table>";
while ($row = mysql_fetch_array($result)) {
	echo "<tr><td><a href=\"?update=1&link=".$row['tid']."\">".$row['host'];
	echo "</a></td>";
	echo "<td>".$row['searchname']."</td>";

	$time = $row['time'];
	$time = date("M,d g:i a",$time);	

	echo "<td>".$time."</td>";
}
echo "</table>";
?>



</div><!-- settings -->

<div class="new-settings">
<h3>General Settings</h3>

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
  <option value="*chrome" <?php if ($row['browser']=="*chrome") echo "selected"; ?>>Chrome</option>
  <option value="*iexplorer" <?php if ($row['browser']=="*iexplorer") echo "selected"; ?>>Internet Explorer</option>
</select> <br />


<label>Host:</label>
<input type="text" name="host" class="text" value="<?php echo $row['host']; ?>" onkeydown="return kd(event)"><br />

<label>Username:</label>
<input type="text" name="username" class="text" value="<?php echo $row['username']; ?>" onkeydown="return kd(event)"><br />

<label>Password:</label>
<input type="text" name="password" class="text" value="<?php echo $row['password']; ?>" onkeydown="return kd(event)"><br />

<label>Pause:</label>
<input type="text" name="sleep" class="text" value="<?php echo $row['sleep']; ?>" onkeydown="return kd(event)"><br />



</div><!-- new settings -->

<div class="new-settings">
<h3>Specific Settings</h3>


<label>Search name:</label>
<input type="text" name="searchname" class="text" value="<?php echo $row['searchname']; ?>" onkeydown="return kd(event)"><br />

<label>Search email:</label>
<input type="text" name="searchemail" class="text" value="<?php echo $row['searchemail']; ?>" onkeydown="return kd(event)"><br />

<label>Spouse name 1:</label>
<input type="text" name="spousename1" class="text" value="<?php echo $row['spousename1']; ?>" onkeydown="return kd(event)"><br />

<label>Spouse name 2:</label>
<input type="text" name="spousename2" class="text" value="<?php echo $row['spousename2']; ?>" onkeydown="return kd(event)"><br />


<input type="hidden" name="save" id="save" value="yes" /> <!-- IGNORE THIS LINE! -->


</div><!-- new settings -->


<input type="submit" id="submit" />

</form>

<div style="clear:both;"></div>

<?php 
	mysql_close($link);
?>
</div> <!-- main -->


<script type="text/javascript">
function kd(e) {
	
    var intKey = (window.Event) ? e.which : e.keyCode;
    document.getElementById("save").value = "yes";
    return true;
}
</script>



<?php
	include_once "html/footer.php";
	?>
