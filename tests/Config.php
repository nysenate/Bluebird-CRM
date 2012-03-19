<?php

$query = "SELECT * FROM `test` WHERE TRUE ORDER BY `tid` DESC LIMIT 1; ";

function getMainURL() {
	global $query;
	$db = new db();
	$db->connect();

	$row = $db->query($query);

	return $row['host'];
}

function getMainURLTitle() {
	return 'Bluebird';
}


// additional settings for different test cases

/// testActionsAddCase3_5.php
/// testActionsMeeting3_5.php
/// testAddTag3_5.php
/// testAdd_Suffix-Remove_Suffix2_29.php
/// testCommunication2_29.php
function getSearchName() {
	global $query;
	$db = new db();
	$db->connect();

	$row = $db->query($query);

	return $row['searchname'];

}

/// testActionsAddRelationship3_7.php
function getSSName($i) {
	global $query;
	$db = new db();
	$db->connect();

	$row = $db->query($query);

	return $row["spousename".($i+1)];
}

/// testAdvSearch_Email2_27.php
function getEmailToSearch() {
	global $query;
	$db = new db();
	$db->connect();

	$row = $db->query($query);

	return $row['searchemail'];
}




class db {
	var $link;
	var $mysql_host = 'localhost';
	var $mysql_user = 'root';
	var $mysql_pwd  = 'mysql';
	var $mysql_db   = 'selenium';
	
	function connect() {
		$this->link = mysql_connect($this->mysql_host, $this->mysql_user, $this->mysql_pwd);
		mysql_select_db($this->mysql_db);
	}

	function query($query) {
		$result = mysql_query($query, $this->link);
		$row = mysql_fetch_array($result);
		return $row;
	}

	function disconnect() {
		mysql_close($this->link);		
	}
}

class BluebirdSeleniumSettings {

	var $publicSandbox  = false;
	var $browser = '';
	var $sandboxURL = '';
	var $sandboxPATH = '';
	var $username = 'demo';
	var $password = 'demo';
	var $adminUsername = '';
	var $adminPassword = '';
    var $UFemail = 'noreply@civicrm.org';
    var $sleepTime = 0;
    
	function __construct() {
		global $query;
		$db = new db();
		$db->connect();
		$row = $db->query($query);

		$this->browser =  $row['browser'];
		$this->sandboxURL =  $row['host'];
		$this->adminUsername =  $row['username'];
		$this->adminPassword =  $row['password'];
		$this->sleepTime = intval($row['sleep']);


		$db->disconnect();

		$this->fullSandboxPath = $this->sandboxURL . $this->sandboxPATH;
	}
}





/*

create database selenium;
use selenium;
create table `test` (tid int primary key AUTO_INCREMENT, host varchar(255) not null default 'http://sd99/',
	username varchar(255) not null default 'senateroot', password varchar(255) not null default 'mysql',
	searchname varchar(255) not null default 'Mike Gordo', searchemail varchar(255) not null default 'mgordo@live.com',
	spousename1 varchar(100) not null default 'Ascher', spousename2 varchar(100) not null default 'Mike Gordo',
	time bigint not null, browser varchar(20) not null default '*firefox', sleep int not null default 0,
	testname varchar(255));

*/


?>