<?php
if (isset($GLOBALS['maintenance_message'])) {
  $maintenance_message = $GLOBALS['maintenance_message'];
}
else {
  $maintenance_message = "This Bluebird CRM instance is currently offline.<br/><br/>Please try again later.";
}
include 'maintenance.php';
?>
