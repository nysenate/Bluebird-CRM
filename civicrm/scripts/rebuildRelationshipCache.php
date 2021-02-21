<?php

/**
 * Author:      Brian Shaughnessy
 * Date:        2021-02-21
 * Description: Rebuild relationship cache introduced in 5.29. Simple wrapper for BAO function.
 */

$prog = basename(__FILE__);

require_once 'script_utils.php';
$optList = civicrm_script_init("", array(), False);

drupal_script_init();

CRM_Core_Config::singleton();

echo "Rebuild relationship cache...\n";
CRM_Contact_BAO_RelationshipCache::rebuild();
