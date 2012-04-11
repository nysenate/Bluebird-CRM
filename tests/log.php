<?php
	include_once "html/header.php";
	?>

<div id="main">
<h3>Log file</h3>
<?php

$query = "SELECT * FROM `log`,`test` WHERE 
			log.tid = test.tid 
			ORDER BY `id` DESC LIMIT 50;";

$link = mysql_connect($mysql_host, $mysql_user, $mysql_pwd);
mysql_select_db($mysql_db);
$result = mysql_query($query, $link);
?>
<table class="log" style="width:100%;">
<thead>
	<th style="width:10%;">Log ID</th>
	<th style="width:32%;">Test Settings</th>
	<th>Log</th>
</thead>
<tbody>
<?php
while ($row = mysql_fetch_array($result)) {
	echo "<tr>";
	echo "<td style=\"vertical-align:top;text-align:center;\">".$row['id']."</td>";
	echo "<td style=\"vertical-align:top;\"><b>Time:</b> ".date("M, j h:i a", $row['time']);
	echo "<br><b>Host:</b> ".$row['host'];
	echo "<br><b>Login:</b> ".$row['username'].":".$row['password'];
	echo "<br><b>Browser:</b> ".$row['browser'];
	echo "<br><b>Test file:</b> ".$row['testname'];
	echo "</td>";
	echo "<td>".$row['text']."</td>";
	echo "</tr>";
}
?>
</tbody>
</table>
<?php
	mysql_close($link);
	?>
</div> <!-- main -->
<?php
	include_once "html/footer.php";
	?>