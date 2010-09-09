<?php
$hostname = $_SERVER['HTTP_HOST'];

if (stristr($hostname, ".crm.") || stristr($hostname, ".crm2.")) {
  header("location: /nyss/");
}
else if (stristr($hostname,".crmdev.") || stristr($hostname,".dev.")) {
  header("location: /nyssdev/");
}
else {
  echo "Bluebird CRM";
}

?>
