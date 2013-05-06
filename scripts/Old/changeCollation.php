<?php 
header('Content-type: text/plain;'); 
mysql_connect('localhost or IP','usernameofdatabase','password'); 
mysql_select_db('databasename'); 
$res = mysql_query("SHOW TABLES"); 
while ($r = mysql_fetch_array($res)){ 
  $tablename = $r[0]; 
  echo $sql = "ALTER TABLE $tablename CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci"; 
  echo "\r\n"; 
mysql_query($sql); 
} 
?>