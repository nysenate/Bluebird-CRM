<?php

function civicrm_api3_nyss_tags_getList($params) {
  $result = (!empty($params['input'])) ?
    array('values' => _civicrm_api3_nyss_tags_getLegPositions($params['input'])) :
    array();

  /*Civi::log()->debug('civicrm_api3_nyss_tags_getList', array(
    'params' => $params,
    '$_REQUEST' => $_REQUEST,
    'result' => $result,
  ));*/
  return $result;
}

function _civicrm_api3_nyss_tags_getList_spec(&$params) {
}

function civicrm_api3_nyss_tags_savePosition($params) {
  //Civi::log()->debug('civicrm_api3_nyss_tags_savePosition', array('params' => $params));

  if (strpos($params['value'], ':::') !== false) {
    $label = explode(':::', $params['value'])[0];
    try {
      //Civi::log()->debug('civicrm_api3_nyss_tags_savePosition', array('label' => $label));
      $tag = civicrm_api3('tag', 'create', array(
        'name' => $label,
        'parent_id' => 292,
        'is_selectable' => true,
        'used_for' => 'civicrm_contact',
      ));
      //Civi::log()->debug('civicrm_api3_nyss_tags_savePosition', array('tag' => $tag));

      civicrm_api3('entity_tag', 'create', array(
        'tag_id' => $tag['id'],
        'entity_id' => $params['contactId'],
        'entity_table' => 'civicrm_contact',
      ));
    }
    catch (CiviCRM_API3_Exception $e) {
      Civi::log()->error('civicrm_api3_nyss_tags_savePosition', array('e' => $e));
    }
  }
}

/*
 * helper function to get leg positions
 * IDs are hardcoded as this is a very unique requirement and not
 * likely reusable in other contexts
 */
function _civicrm_api3_nyss_tags_getLegPositions($input) {
  get_bluebird_instance_config();
  $input = CRM_Utils_Type::escape($input, 'String');
  $tags = array();

  /*
   * NYSS leg positions should retrieve list from OpenLegislation
   * and create value in tag table.
   */
  require_once 'CRM/NYSS/BAO/Integration/OpenLegislation.php';
  $bills = CRM_NYSS_BAO_Integration_OpenLegislation::getBills($input);
  $billcnt = count($bills);

  /*Civi::log()->debug('_civicrm_api3_nyss_tags_getLegPositions', array(
    '$bills' => $bills,
    '$billcnt' => $billcnt,
  ));*/

  for ($j = 0; $j < $billcnt; $j++) {
    $billName = $bills[$j]['id'];
    $billSponsor = '';
    if (isset($bills[$j]['sponsor'])) {
      $billSponsor = $bills[$j]['sponsor'];
      $billName .= " ($billSponsor)";
    }

    //construct positions
    $billTags = array($billName, "$billName: SUPPORT", "$billName: OPPOSE");

    //construct tags array
    foreach ($billTags as $billTag) {
      // Do lookup to see if tag exists in system already,
      // else construct using standard format
      // NYSS 4315 - escape position tag name
      $query = "
        SELECT id, name FROM civicrm_tag
        WHERE parent_id = 292
          AND name = '".str_replace("'", "''", $billTag)."'";

      $dao = CRM_Core_DAO::executeQuery($query);
      if ($dao->fetch()) {
        $tagID = $dao->id;
      }
      else {
        $tagID = $billTag.':::value';
      }

      $tags[] = array(
        'label' => $billTag,
        'id' => $tagID,
        'sponsor' => $billSponsor
      );
    }//end foreach
  }

  /*Civi::log()->debug('_civicrm_api3_nyss_tags_getLegPositions', array(
    '$tags' => $tags,
  ));*/

  return $tags;

  //echo json_encode($tags);
  //CRM_Utils_System::civiExit();
}
