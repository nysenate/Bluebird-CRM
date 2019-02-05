<?php

function civicrm_api3_nyss_tags_getlist($params) {
  //if input is passed, we are doing a lookup
  //if input is not passed and IDs are passed, we restructure into the appropriate format to pass back
  $result = array();
  if (!empty($params['input'])) {
    $tagLookup = _civicrm_api3_nyss_tags_getLegPositions($params['input']);
    $result = array(
      'is_error' => 0,
      'version' => 3,
      'count' => count($tagLookup),
      'values' => $tagLookup,
      'page_num' => 1,
      'more_results' => 0,
    );
  }
  elseif (!empty($params['id'])) {
    $result = civicrm_api3('tag', 'get', array(
      'parent_id' => 292,
      'sequential' => TRUE,
      'options' => array(
        'limit' => 0,
        'sort' => 'name',
        'offset' => 0,
      ),
      'id' => array(
        'IN' => $params['id'],
      ),
      'return' => array(
        'id',
        'name',
        'color',
        'description',
      ),
      'check_permissions' => FALSE,
    ));

    //default getlist assumes label for text value
    foreach ($result['values'] as &$value) {
      $value['label'] = $value['name'];
      $value['value'] = $value['id'];
    }
  }

  /*Civi::log()->debug('civicrm_api3_nyss_tags_getlist', array(
    'params' => $params,
    '$_REQUEST' => $_REQUEST,
    'result' => $result,
  ));*/
  return $result;
}

function _civicrm_api3_nyss_tags_getlist_spec(&$params) {
}

//this was never hit...
function _civicrm_api3_nyss_tags_getlist_output($result, $request, $entity, $fields) {
  /*Civi::log()->debug('_civicrm_api3_nyss_tags_getlist_output', array(
    '$result' => $result,
    '$request' => $request,
    '$entity' => $entity,
    '$fields' => $fields,
  ));*/
}

function civicrm_api3_nyss_tags_savePosition($params) {
  //Civi::log()->debug('civicrm_api3_nyss_tags_savePosition', array('params' => $params));

  if (strpos($params['value'], ':::') !== false) {
    $label = explode(':::', $params['value'])[0];
    try {
      //do lookup to see if it already exists
      $existingTag = civicrm_api3('tag', 'get', [
        'name' => $label,
        'parent_id' => 292,
        'sequential' => TRUE,
      ]);
      //Civi::log()->debug('civicrm_api3_nyss_tags_savePosition', array('$existingTag' => $existingTag));

      if (!empty($existingTag['values'][0])) {
        return $existingTag['values'][0]['id'];
      }

      //Civi::log()->debug('civicrm_api3_nyss_tags_savePosition', array('label' => $label));
      $tag = civicrm_api3('tag', 'create', array(
        'name' => $label,
        'parent_id' => 292,
        'is_selectable' => true,
        'used_for' => ['civicrm_contact','civicrm_activity','civicrm_case'],
      ));
      //Civi::log()->debug('civicrm_api3_nyss_tags_savePosition', array('tag' => $tag));

      //if this is triggered from contact edit form we don't have a contact ID (if new contact)
      if (!empty($params['contactId'])) {
        if (!is_array($params['contactId'])) {
          $params['contactId'] = array($params['contactId']);
        }
        foreach ($params['contactId'] as $contactId) {
          civicrm_api3('entity_tag', 'create', [
            'tag_id' => $tag['id'],
            'entity_id' => $contactId,
            'entity_table' => 'civicrm_contact',
          ]);
        }
      }

      return $tag['id'];
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

  if ($bills === null) {
    CRM_Core_Error::fatal("Unable to fetch bills from OpenLegislation");
  }

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
