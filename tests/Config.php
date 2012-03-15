<?php

function getMainURL() {
	return 'http://sd99/';
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
	return "Mike Gordo";
}

/// testActionsAddRelationship3_7.php
function getSSName($i) {
	$result = array ('Ascher','Mike Gordo');
	return $result[$i];
}

/// testAdvSearch_Email2_27.php
function getEmailToSearch() {
	return "mgordo@live.com";
}





?>