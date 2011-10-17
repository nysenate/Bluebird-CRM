<?php

require_once 'api/v3/utils.php';

/**
 *  returns the list of all the entities that you can manipulate via the api. The entity of this API call is the entity, that isn't a real civicrm entity as in something stored in the DB, but an abstract meta object. My head is going to explode. In a meta way.
 */
function civicrm_api3_entity_get ($params) {

     civicrm_api3_verify_mandatory ($params);
     $entities = array ();
     $iterator = new DirectoryIterator(dirname(__FILE__));
     foreach ($iterator as $fileinfo) {
       $file = $fileinfo->getFilename();
       $parts = explode(".", $file);  
       if (end($parts) == "php" &&  $file != "utils.php" ) {
         $entities [] = substr ($file, 0, -4); // without the ".php"
       }
    }
    $entities = array_diff($entities, array('Generic'));
    sort($entities);
    return civicrm_api3_create_success ($entities);

}

/**
 *  Placeholder function. This should never be called, as it doesn't have any meaning
 */
function civicrm_api3_entity_create ($params) {
  return civicrm_api3_create_error ("API (Entity,Create) does not exist Creating a new entity means modifying the source code of civiCRM.");
}

/**
 *  Placeholder function. This should never be called, as it doesn't have any meaning
 */
function civicrm_api3_entity_delete ($params) {
  return civicrm_api3_create_error ("API (Entity,Delete) does not exist Deleting an entity means modifying the source code of civiCRM.");
}

/**
 *  Placeholder function. This should never be called, as it doesn't have any meaning
 */
function civicrm_api3_entity_getfields ($params) {
  return civicrm_api3_create_error ("API (Entity,getfields) does not exist entity_get only returns the list of entities you can access from the API, no fields");
}
