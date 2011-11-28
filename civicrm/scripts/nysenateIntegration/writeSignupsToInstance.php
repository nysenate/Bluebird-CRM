<?php

require_once '../script_utils.php';
require_once 'classes/NySenateContact.php';

$stdusage = civicrm_script_usage();

$usage = "[--file|-f file] [--dedupe-level|-d dedupe_level] [--user-id|-u user_id] [--activity-name|-a activity_name]";
$shortopts = "f:d:u:a";
$longopts = array("file=", "dedupe-level=", "user-id=", "acitivity-name=");

$optlist = civicrm_script_init($shortopts, $longopts);
if ($optlist === null || !$optlist['file']) {
	echo("Usage: $stdusage  $usage\n");
	exit(1);
}

//userid 1 is senateroot
define('EXECUTING_USER_ID', $optlist['user-id']
				? $optlist['user-id']
				: 1);
				
//Level 3 (street + lname + fname + city + suffix) - Fuzzy
//Level 4 (fname + lname + email) - Fuzzy
define('DEDUPE_LEVEL', $optlist['dedupe-level']
				? $optlist['dedupe-level']
				: "Level 3,Level 4");

require_once 'CRM/Core/Error.php';
require_once 'CRM/Core/Config.php';
$config =& CRM_Core_Config::singleton();
$session =& CRM_Core_Session::singleton();
$session->set('userID', EXECUTING_USER_ID);

//dedupe levels to work with

$data = file_get_contents($optlist['file']);

if($data) {
	$contacts = unserialize($data);
	
	foreach($contacts as $contact) {
		write_contact_to_instance($contact);
	}
}

function write_contact_to_instance($contact) {
	$levels = explode(",", DEDUPE_LEVEL);
	
	foreach($levels as $level) {
		//run dedupe rules on contact
		$dupes = is_dupe($contact->civicrm_contact_params, $level);
		
		if($dupes) {
			break;
		}
	}
	
	if($dupes && count($dupes) == 1) {
		//one match found, use this contact
		
		$cid = $dupes[0];
	}
	else {
		//found no duplicates OR more than one, so create record
		
		require_once "CRM/Contact/DAO/Contact.php";
		$contact_fileds =& CRM_Contact_DAO_Contact::import();
		
		$civi_contact = CRM_Contact_BAO_Contact::create($contact->civicrm_contact_params);
		
		$cid = $civi_contact->id;
	}
	
	if($contact->activity) {
		//assign activity and keywords to activity
		
		$activity = create_activity($cid, $contact->activity);
		
		if(array_key_exists('keywords', $contact->activity)) {
			foreach($contact->activity['keywords'] as $keyword) {
				assign_activity_keyword($keyword, $activity[0]['id']);
			}
		}
	}
	
	if($contact->issues) {
		//assign issue codes or keywords
		
		foreach($contact->issues as $issue) {
			assign_contact_tag_best_fit($issue, $cid);
		}
	}
	
	if($contact->initiative) {
		//assign initiative issue code
		
		$issue_codes = get_tag("Issue Codes");
		$issue_code = $issue_codes[0];
		
		$website_initiative = create_tag_if_not_exists(
										"Website - Initiative",  
										$issue_code['id']);
		
		assign_entity_tag(
			$contact->initiative['title'], 
			$website_initiative['id'], 
			$cid, 
			'civicrm_contact');
	}
}

/**
 * find the absolute root of a given tag
 * as well as its distance from that root
 * @param tag civicrm params
 * @param distance
 */
function get_tag_root($tag, $distance = 0) {
	if(!$tag || !array_key_exists('parent_id', $tag)) {
		return NULL;
	}
	
	if($tag['parent_id']) {
		$tags = get_tag(NULL, $tag['parent_id']);
		return get_tag_root($tags[0], $distance + 1);
	}
	
	return array(
		'tag'	=>	$tag, 
		'distance'	=>	$distance
	);
}

/**
 * for issues workflow see:
 * 		http://dev.nysenate.gov/wiki/bluebird/NYSenategov_Integration#Issues
 * 
 * given a tag name decides to assign to an issue code or keyword
 * 
 * @param tag_name
 * @param contact_id
 * @param super_parent defines which root would have precedence
 */
function assign_contact_tag_best_fit($tag_name, $contact_id, $super_parent = "Issue Codes") {
	$tags = get_tag($tag_name);
	$tag = null;
	
	//if only one tag exists with the name then use it
	if(count($tags) == 1) {
		$tag = $tags[0];
	}
	//if more than one exists choose issue code
	//or issue code nearest to top level
	else if(count($tags) > 1) {
		$parent_tag = NULL;
		$distance_from_root = NULL;
		
		foreach($tags as $temp_tag) {
			$root = get_tag_root($temp_tag);
			
			if($root['tag']['name'] == $super_parent) {
				if(!$parent_tag || $distance_from_root < $root['distance']) {
					$parent_tag = $temp_tag;
					$distance_from_root = $root['distance'];
				}
				//if more than one issue code exists at a given level
				//default to keyword
				else if($distance_from_root == $root['distance']) {
					break;
				}
			}
		}
		
		$tag = $parent_tag;
	}
	
	if($tag) {
		create_entity_tag($contact_id, $tag['id'], 'civicrm_contact');
	}
	else {
		assign_contact_keyword($tag_name, $contact_id);	
	}

}

function assign_contact_keyword($tag_name, $contact_id) {
	assign_entity_keyword($tag_name, $activity_id, 'civicrm_contact');
}

function assign_activity_keyword($tag_name, $activity_id) {
	assign_entity_keyword($tag_name, $activity_id, 'civicrm_activity');
}

function assign_entity_keyword($tag_name, $entity_id, $entity_table) {
	$parent_keyword_tags = get_tag('Keywords');
	$parent_keyword_tag = $parent_keyword_tags[0];
	
	assign_entity_tag($tag_name, $parent_keyword_tag['id'], $entity_id, $entity_table);
}

/**
 * find tag with given name and parent, if it doesn't exist
 * create it
 * @param tag_name
 * @param tag_parent_id
 */
function create_tag_if_not_exists($tag_name, $tag_parent_id) {
	$tags = get_tag($tag_name, NULL, $tag_parent_id);
	$tag = NULL;
	
	if(count($tags) > 0) {
		$tag = $tags[0];
	}
	else {
		$tags = create_tag($tag_name, $tag_parent_id);
		$tag = $tags[0];
	}
	
	return $tag;
}

/**
 * generic function for creating entity tags
 * for any kind of entity + tag
 * @param tag_name
 * @param tag_parent_id
 * @param entity_id
 * @param entity_table
 */
function assign_entity_tag($tag_name, $tag_parent_id, $entity_id, $entity_table) {
	$tag = create_tag_if_not_exists($tag_name, $tag_parent_id);
	
	create_entity_tag($entity_id, $tag['id'], $entity_table);
}

/**
 * 
 * Given a contact determine if it is a duplicate
 * @param $params formatted array with contact data
 * @param $rule_id
 * @return array of contact ids if dupes found, else null
 */
function is_dupe($params, $rule_id) {
	require_once "api/v2/Contact.php";

	$dupe_error = civicrm_contact_check_params($params, true, false, true, $rule_id);
	
	//found duplicates
	if($dupe_error) {
		return split(",", $dupe_error[0]);
	}
	else {
		return NULL;
	}
}

function create_tag($name, $parent_id = NULL, $is_selectable = 0) {
	if($name) {
		$tag = api(
			"Tag", 
			"create", 
			array(
				'name' => $name,
				'parent_id' => $parent_id,
				'is_selectable' => $is_selectable
			)
		);
		
		return api_unwrap($tag);
	}
	return NULL;
}

function get_tag($name = NULL, $id = NULL, $parent_id = NULL) {
	//maintains a cache of queries that have been made
	//if the query returns a valid response
	static $cache = array();
	
	if(array_key_exists($name.$id.$parent_id, $cache)) {
		return $cache[$name.$id.$parent_id];
	}
	
	if($name || $id || parent_id) {
		$raw_tags = api(
			"Tag", 
			"get", 
			array(
				'name' 		=> $name,
				'id'		=> $id,
				'parent_id'	=> $parent_id
			)
		);
		
		$tags = api_unwrap($raw_tags);
		
		if(count($tags) > 0) {
			$cache[$name.$id.$parent_id] = $tags;
		}
		
		return $tags;
	}
	
	return NULL;
}


/**
 * 
 * create relationship between contact and tag
 * @param $cid contact-id
 * @param $tid tag-id
 */
function create_entity_tag($cid, $tid, $entity_table) {
	if($cid && $tid) {
		$entity_tag = api(
			"EntityTag", 
			"create", 
			array(
				'contact_id' 	=> $cid,
				'tag_id' 		=> $tid,
				'entity_table' 	=> $entity_table
			)
		);
		
		return api_unwrap($entity_tag);
	}
	return NULL;
}

function create_activity($contact_id, $activity) {
	if($contact_id && $activity) {
		
		//set source id, default is SenateRoot
		$activity['source_contact_id'] = EXECUTING_USER_ID;
		$activity['target_contact_id'] = $contact_id;
		
		$new_activity = api_unwrap(api("Activity", "create", $activity));
		
		return $new_activity;
	}
	return NULL;
}

/**
 * wrapper for civi v3 api
 * @param type civi entity that we're working with
 * @param command 
 * @param params
 * @param version
 */
function api($type, $command, $params, $version = 3) {
	require_once 'api/api.php';
	
	if(!array_key_exists('version', $params)) {
		$params['version'] = $version;
	}
	
	return civicrm_api($type, $command, $params);
}

function api_unwrap($response) {
	if($response && array_key_exists('values', $response)) {
		
		$values = array();
		
		foreach($response['values'] as $id => $value) {
			$values[] = $value;
		}
		
		return $values;
	}
	else {
		return NULL;
	}
}