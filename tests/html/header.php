<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NY Senate Selenium Testing</title>
	<link rel="stylesheet" type="text/css" href="html/index.css">
	<script type="text/javascript">
		var vis = false;
		function helppage() {
			if (!vis) {
				document.getElementById("helparea").style.display = "none";
			} else {
				document.getElementById("helparea").style.display = "block";
			}
			vis = !vis;
		}
	</script>
</head>

<?php
include_once "html/functions.php";

if (file_exists($tempfile)) {
	dump($tempfile);
}
// check here if $tempfile exists, save it to the db
// and unlink the file
?>

<body>
<div id="wrap">
	<div id="header">
	<h1><a href="index.php">NY Senate</a></h1>
	<h3 class="help-button"><a href="javascript:helppage();">help ?</a></h3>
	<h3 class="help-button"><a href="log.php">log</a></h3>
	<h2>Selenium Testing</h2>

		<div id="helparea">
			<h3>Helpful information</h3>
			<p>Before starting any test, make sure Selenium Server is running (there must be "Selenium Server" application button on the taskbar in the bottom of the screen).</p>
			<p>To start the test:
			<ul>
				<li>Choose the appropriate text script in the left column,</li>
				<li>Make sure General Settings are correct, change them if necessary,</li>
				<li>Some scripts (e.g. Actions - Add Relationship) require additional settings.</li>
			</ul>
			</p>
		</div>
	</div>
