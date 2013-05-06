<?php

function nyssInitializeDB()
{
  db_query("CREATE DATABASE nyssLog");
  db_query("CREATE TABLE nyssLog.timer(timestamp DATETIME, host VARCHAR(255), path VARCHAR(255), duration FLOAT, PRIMARY KEY(timestamp,host, path));");
  db_query("CREATE INDEX nyssLog.timerIndex1 ON nyssLog.timer(host, path, timestamp);");
  db_query("CREATE INDEX nyssLog.timerIndex2 ON nyssLog.timer(host, timestamp, path);");
}


//skiprate: if 10, only start timer one in 10 times;
function nyssStartTimer($skipRate=1)
{
  if (mt_rand(1, $skipRate) == 1) {
    $_SESSION['nyssStart'] = microtime(TRUE);
  }
}


function nyssGetElapsed()
{
  return (isset($_SESSION['nyssStart'])) ? microtime(TRUE) - $_SESSION['nyssStart'] : false;
}


function nyssWriteElapsed()
{
  if (isset($_SESSION['nyssStart'])) {
    db_query('INSERT INTO nyssLog.timer(timestamp, host, path, duration) values("'.date("Y-m-d H:i:s").'","'.$_SERVER['HTTP_HOST'].'","'.$_SERVER['REQUEST_URI'].'",'.nyssGetElapsed().');');
  }
}

?>
