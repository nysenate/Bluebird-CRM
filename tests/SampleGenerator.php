<?php

function getFirstName() {
	$a = array ('Andrew','James','John','Mike','Herbie','Johnson','Jake',
	'Timothy','Matthew','Kyle','Jessica','Natalie','Anna','Helena', 'Millie', 'Geogre', 'Sam');
	$count = count($a);	
	return $a[rand(0, $count-1)].'_'.genRandomString(3);
}

function getLastName() {
	return 'Ascher';	
}

function getEmail($fname, $lname, $domain) {
	$r = substr($fname, 0, 1);
	$r .= $lname . genRandomString(3) . $domain;
	return $r;
}

// if flag = 1, return only letters A..Z
function genRandomString($length = 8, $flag = 0) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    if ($flag) $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $string = "";    
    for ($p = 0; $p < $length; $p++) {
        $string .= $characters[rand(0, strlen($characters)-1)];
    }
    return $string;
}

function getStreetAddress() {

	$a = rand(1,999) . genRandomString(1,1);
	$r = '94 Eagle Street, Apt. ' . $a;
	return $r;
}

function getStreetAddress_City() {
	return 'Troy';	
}

function getStreetAddress_Zip() {
	return '12180';	
}

?>