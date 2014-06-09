<?php

/**
 * Author:      Brian Shaughnessy
 * Date:        2014-06-09
 * Description: create or update FTS indices
 */

$prog = basename(__FILE__);

require_once 'script_utils.php';
$optList = civicrm_script_init("d", array('debug'), FALSE);

drupal_script_init();
$bbcfg = get_bluebird_instance_config();

require_once 'CRM/Core/Config.php';
$config = CRM_Core_Config::singleton();

echo "creating and updating FTS indices...\n";
require_once 'CRM/Core/InnoDBIndexer.php';
$fts = CRM_Core_InnoDBIndexer::singleton();
//CRM_Core_Error::debug_var('$fts', $fts);

$fts->fixSchemaDifferences();

echo "finished updating FTS indices. \n\n";
