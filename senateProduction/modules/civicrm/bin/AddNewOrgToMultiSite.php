#!/usr/bin/env php
<?php
/* 
 * This scripts adds new orgs to CiviCRM 3.0+ multi-site setups. You need to
 * create the sites first (i.e. put civicrm.settings.php files in
 * sites/www.example.org/ directories for each of your sites).
 *
 * Written by Wes Morgan
 * 8/6/09
 */

$debug = false;

function run( $argc, $argv ) {
    global $debug;

    session_start( );
    require_once '../civicrm.config.php';
    require_once 'api/v2/Domain.php';
    require_once 'api/v2/Group.php';
    require_once 'api/v2/GroupNesting.php';
    require_once 'api/v2/GroupOrganization.php';
    require_once 'api/v2/Contact.php';

    if ($argc < 3 || $argc > 4) {
        #var_dump($argv);
        print_usage( $argv[0] );
        exit(-1);
    }

    $org_name = $argv[1];
    $org_desc = $argv[2];

    $config = CRM_Core_Config::singleton();

    //load bootstrap to call hooks
    require_once 'CRM/Utils/System.php';
    CRM_Utils_System::loadBootStrap(  );
    
    # create the domain
    $existing_domain = civicrm_domain_get( );
    $domain_params = array('name' => $org_name, 'description' => $org_desc,
        'version' => $existing_domain['version']);
    $domain = civicrm_domain_create( $domain_params );
    if ($debug) {
        print "Create domain result: ".var_export($domain)."\n";
    }
    $domain_id = $domain['id'];

    # find the parent group, if necessary
    if (! is_null($argv[3])) {
        $parent_group_name = $argv[3];
        $parent_group_params = array('title' => $parent_group_name);
        $parent_groups = civicrm_group_get( $parent_group_params );
        if ($debug) {
            print "Find parent group result: ".var_export($parent_groups)."\n";
        }
        $parent_group_keys = array_keys($parent_groups);
        $parent_group_id = $parent_group_keys[0];
    }

    # create the group
    $group_params = array('title' => $org_name, 'description' => $org_desc,
        'is_active' => 1);
    $group = civicrm_group_add( $group_params );
    if ($debug) {
        print "Create group result: ".var_export($group)."\n";
    }
    $group_id = $group['result'];

    # create the org nesting if necessary
    if (! is_null($parent_group_id)) {
        $group_nesting_params = array('parent_group_id' => $parent_group_id,
            'child_group_id' => $group_id);
        $group_nesting = civicrm_group_nesting_create( $group_nesting_params );
        if ($debug) {
            print "Create group nesting result: ".var_export($group_nesting)."\n";
        }
    }

    # create the org contact
    $org_params = array('organization_name' => $org_name,
        'contact_type' => 'Organization');
    $org = civicrm_contact_create( $org_params );
    if ($debug) {
        print "Create org contact result: ".var_export($org)."\n";
    }
    $org_id = $org['contact_id'];

    # associate the two
    $group_org_params = array('group_id' => $group_id,
        'organization_id' => $org_id);
    $group_org_id = civicrm_group_organization_create( $group_org_params );
    if ($debug) {
        print "Create group-org association result: ".var_export($group_org_id)."\n";
    }

    print "\n";
    print "Add or modify the following lines in the appropriate ";
    print "civicrm.settings.php file for $org_name:\n";
    print "\tdefine( 'CIVICRM_DOMAIN_ID', $domain_id );\n";
    print "\tdefine( 'CIVICRM_DOMAIN_GROUP_ID', $group_id );\n";
    print "\tdefine( 'CIVICRM_DOMAIN_ORG_ID', $org_id );\n";
    print "\n";
}

function print_usage( $cmd_name ) {
    print "Usage: ".$cmd_name." 'Org Name' 'Org Description' ['Parent Org Name']\n";
}

run( $argc, $argv );

?>
