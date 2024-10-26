<?php
use CRM_Mosaico_ExtensionUtil as E;

/**
 * Job.mosaico_migrate API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_job_mosaico_migrate_spec(&$spec) {
}

/**
 * Job.mosaico_migrate API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_job_mosaico_migrate($params) {
  $newTpls = [];
  $dao = new CRM_Mosaico_DAO_MessageTemplate();
  $dao->find();
  while ($dao->fetch()) {
    $metadata = json_decode($dao->metadata, TRUE);

    $newTpl = [];
    $newTpl['title'] = $dao->name;
    $newTpl['html'] = $dao->html;
    $newTpl['metadata'] = $dao->metadata;
    $newTpl['content'] = $dao->template;
    $newTpl['msg_tpl_id'] = $dao->msg_tpl_id;

    if ($metadata['template']) {
      if (preg_match(';packages/mosaico/templates/(.*)/.*html;', $metadata['template'], $matches)) {
        $newTpl['base'] = $matches[1];
      }
    }

    if (empty($newTpl['base'])) {
      throw new API_Exception("Migration could not be performed. Template #{$dao->id} has unrecognized base template ({$metadata['template']}).", /*errorCode*/ 1234);
    }

    $newTpls[] = $newTpl;
  }

  $results = [];
  foreach ($newTpls as $newTpl) {
    $result = civicrm_api3('MosaicoTemplate', 'create', $newTpl);
    $results[$result['id']] = $result['values'][$result['id']];
  }

  return civicrm_api3_create_success($results, $params, 'Job', 'mosaico_migrate');
}
