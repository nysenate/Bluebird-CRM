<?php
$prog = basename(__FILE__);
require_once 'script_utils.php';
$stdusage = civicrm_script_usage();
$usage = "[--server|-s imap_server] ";
$shortopts = "s";
$longopts = array("server=");
$optlist = civicrm_script_init($shortopts, $longopts);
if ($optlist === null) {
  error_log("Usage: $prog  $stdusage  $usage\n");
  exit(1);
}
require_once 'api/api.php';
require_once 'CRM/Core/DAO.php';

$sender_email = 'dean.hill108@hotmail.com';
// $sender_email = 'gailao777@aol.com';
// $sender_email = 'info@medmaltruth.org';

// check via api 
$apiparams = array('version'=>3, 'activity'=>'get', 'email'=>$sender_email);
$ApiResults = civicrm_api('contact', 'get', $apiparams);

// search via sql 
$nyss_conn = new CRM_Core_DAO();
$nyss_conn = $nyss_conn->getDatabaseConnection();
$dbconn = $nyss_conn->connection;

$Query="SELECT  contact.id,  email.email FROM civicrm_contact contact
          LEFT JOIN civicrm_email email ON (contact.id = email.contact_id)
          WHERE contact.is_deleted=0
          AND email.email LIKE '$sender_email'
          GROUP BY contact.id
          ORDER BY contact.id ASC, email.is_primary DESC";
$SqlResults = array();
$result = mysql_query($Query, $dbconn);
while($row = mysql_fetch_assoc($result)) {
	$SqlResults['values'][] = array('contact_id'=>$row['id'],'email'=> $row['email']);
}

var_dump($ApiResults);
echo "- - - - - - - - - - - - -\n";
var_dump($SqlResults);
echo "- - - - - - - - - - - - -\n";
echo "API RETURNS : ".$ApiResults['count']."\n";
echo "SQL RETURNS : ".count($SqlResults['values'])."\n";


?>