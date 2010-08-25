<?php

//error_reporting(E_ALL);
//ini_set("display_errors", 1);

//unix
$mysql = 'mysql';
$tmpDir = "/tmp/";
$copy = "cp";

$drupalDB='senate_d_crm99';
$civiDB='senate_c_crm99';

//windows
//$mysql = "\\xampp\\mysql\bin\\mysql.exe";
//$tmpDir = "\\tmp\\";
//$copy = "copy";

$tablePrefix = "civicrm_";
$keyChecks = 0;
$dir = '10000';

$timestart = microtime(1); // note 1

try {

	$dir = $argv[1];
//	if (isset($argv[1])) $fileTable[$argv[1]]=$argv[2];
//	$host = $argv[3];
//	$db = $argv[4];
//	$user = $argv[5];
//	$password = $argv[6];
} catch (Exception $e) {}

if (strlen($host)==0) $host='localhost';
if (strlen($root)==0) $user='root';
if (strlen($password)==0) $password='DGF4dsf@$';

	$sql = "delete from cache;delete from cache_menu;delete from cache_page;delete from cache_filter;";
	$cmd = "$mysql -u$user -p$password $drupalDB -e \"$sql\"";
	exec($cmd,&$aOut);
	print(implode("\n",$aOut));

        $sql = "truncate civicrm_cache;";
        $cmd = "$mysql -u$user -p$password $civiDB -e \"$sql\"";
        exec($cmd,&$aOut);
	print(implode("\n",$aOut));

$elapsed_time = microtime(1)-$timestart; // note 2
echo "elapsed time = $elapsed_time sec\n\n";

?>
