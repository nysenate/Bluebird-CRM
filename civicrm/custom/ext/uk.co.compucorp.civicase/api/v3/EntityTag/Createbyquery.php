<?php

/**
 * @file
 * EntityTag.CreateByQuery file.
 */

/**
 * EntityTag.Createbyquery API specification.
 *
 * @param array $spec
 *   Description of fields supported by this API call.
 */
function _civicrm_api3_entity_tag_createbyquery_spec(array &$spec) {
  $spec['entity_table'] = [
    'title' => 'Entity Table',
    'description' => 'Physical tablename for entity being joined to file, e.g. civicrm_contact',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_STRING,
  ];
  $spec['tag_id'] = [
    'title' => 'Tag Id',
    'description' => 'A single tag Id or array of Tag Ids',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_STRING,
  ];
  $spec['params'] = [
    'title' => 'Params for API Get',
    'description' => 'Array of parameters for Activity.Get API',
    'type' => CRM_Utils_Type::T_STRING,
  ];
  $spec['entity_id'] = [
    'title' => 'Entity ID',
    'type' => CRM_Utils_Type::T_INT,
  ];
}

/**
 * EntityTag.Createbyquery API.
 *
 * The API allows to create entity tags based on the parameters sent.
 * When the params parameter is present, the API gets the entity name from
 * the entity table and uses that to query the Entity get API with the params
 * sent in order to get the entity ids that needs tags to be created for.
 * When Id's are present, these id's are used directly for the
 * entity tag creation.
 *
 * @param array $params
 *   API parameters.
 *
 * @return array
 *   API result descriptor
 */
function civicrm_api3_entity_tag_createbyquery(array $params) {
  $entityTagQueryApiHelper = new CRM_Civicase_APIHelpers_EntityTagQueryApi();
  $entityTagQueryApiHelper->validateParameters($params);
  $genericApiHelper = new CRM_Civicase_APIHelpers_GenericApi();

  if (!empty($params['entity_id'])) {
    $entityValues = $genericApiHelper->getParameterValue($params, 'entity_id');
  }
  else {
    $entityName = $entityTagQueryApiHelper->getEntityNameFromTable($params['entity_table']);
    $entityValues = array_column($genericApiHelper->getEntityValues($entityName, $params['params'], ['id']), 'id');
  }

  $entityIds = [];
  foreach ($entityValues as $entityValue) {
    try {
      civicrm_api3('EntityTag', 'create', [
        'entity_table' => $params['entity_table'],
        'tag_id' => $params['tag_id'],
        'entity_id' => $entityValue,
      ]);
      $entityIds[] = $entityValue;
    } catch (Exception $e) {
    }

  }

  return civicrm_api3_create_success($entityIds, $params, 'EntityTag', 'createbyquery');
}
