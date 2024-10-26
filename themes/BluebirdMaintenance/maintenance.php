<?php
/*
** maintenance.php - Common page for both maintenance and offline states.
**
** Project: BluebirdCRM
** Authors: Vishal Mudi, Ken Zalewski
** Organization: New York State Senate
** Date: 2010-10-04
** Revised: 2010-10-08
**
** Note: The $maintenance_message variable should be set by the script
**       that is including this one.
*/
$theme_path = path_to_theme();
$image_dir = "/$theme_path/images";
?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Bluebird Maintenance</title>
<style type="text/css">
<!--
.blueBackground {
	background-image: url(<?php echo $image_dir?>/bluebird_back.jpg);
	background-repeat: repeat-x;
	background-position: left top;
	text-align: center;
}
.loginImage {
	background-color: transparent;
	background-image: url(<?php echo $image_dir?>/nologin.png);
	background-repeat: no-repeat;
	background-position: top;
	height: 400px;
	width: 550px;
	margin-top: 68px;
	margin-left: auto;
	margin-right: auto;
	text-align: left;
}
.text {
	font-family: arial, sans-serif;
	font-size: 18px;
	font-style: normal;
	font-weight: bold;
	color: #ff7777;
	top: 70px;
	left: 220px;
	width: 200px;
	letter-spacing: normal;
	text-align: center;
	position: relative;
}
-->
</style>
</head>
<body class="blueBackground">
<div class="loginImage">
<div class="text">
<?php echo $maintenance_message?>
</div>
</div>
</body>
</html>
