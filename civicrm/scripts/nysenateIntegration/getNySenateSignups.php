<?php

require_once '../script_utils.php';

require_once 'classes/NySenateContact.php';
require_once 'classes/SignupForm.php';
require_once 'classes/WebformForm.php';
require_once 'classes/ContactForm.php';

add_packages_to_include_path();

$usage = <<<OPTS
[--site|-S site] [--api-key|-a api_key] 
[--domain-name|-d domain_name] 
[--start-time-stamp|-s start_time_stamp] 
[--end-time-stamp|-e end_time_stamp] 
[--work-directory|-w work_directory] [--help|-h help]
OPTS;

$shortopts = "S:a:d:s:e:w:h";

$longopts = array("site=", "api-key=", "domain-name=", 
		"start-time-stamp=", "end-time-stamp=", 
		"work-directory=", "help");

$optlist = process_cli_args($shortopts, $longopts);

if ($optlist === null || $optlist["help"]) {
	echo("Usage: $prog $usage\n");
	exit(1);
}

define('DEFAULT_SITE',
	FormHelper::get_default($optlist, 'site', "sd99")
);			
define(
	'WORK_DIRECTORY',
	FormHelper::get_default(
		$optlist, 
		'work-directory', 
		'/tmp/'
	)
);

$bbconfig = FormHelper::get_bb_config(DEFAULT_SITE);

$api_key        = FormHelper::get_default($optlist, 'api-key', $bbconfig['nysenate.services.key']);
$domain_name    = FormHelper::get_default($optlist, 'domain-name', 'civicrm.nysenate.gov');
$start_date		= FormHelper::get_default($optlist, 'start-time-stamp', NULL);
$end_date 		= FormHelper::get_default($optlist, 'end-time-stamp', NULL);

$forms = array(
	new SignupForm($api_key, $domain_name),
	new WebformForm($api_key, $domain_name),
	new ContactForm($api_key, $domain_name)
);

foreach($forms as $form) {
	$contacts = $form->getformcontacts(
		$start_date,
		$end_date,
		NULL,
		NULL,
		1
	);
	
	foreach($contacts as $contact) {
		write($contact);
	}
}

/**
 * 
 * Write contact data to instance file, done individually
 * to keep large contact lists out of memory
 * @param $file_name
 * @param $contact
 */
function write(NySenateContact $contact) {
	$file_name = WORK_DIRECTORY.$contact->senator_short_name;
	
	$file_exists = file_exists($file_name);
	
	if($file_exists) {
		$data = file_get_contents($file_name);
		
		$contacts = unserialize($data);
	}
	else {
		$contacts = array();
	}
	
	$contacts[] = $contact;
	
	if($file_exists) {
		unlink($file_name);
	}
	
	if($handle = fopen($file_name, 'w')) {
		fwrite($handle, serialize($contacts));
		fclose($handle);
	}
}