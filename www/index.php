<? 

if 	(stristr($_SERVER['HTTP_HOST'],".crm.")) header("location: /nyss/");
else if (stristr($_SERVER['HTTP_HOST'],".crmdev.")) header("location: /nyssdev/");
else if (stristr($_SERVER['HTTP_HOST'],".dev.")) header("location: /nyssdev/");
else {
  echo "Bluebird CRM";
}

?>
