<?php

function civicrm_api3_tag_save_position($params) {
  Civi::log()->debug('civicrm_api3_tag_savePosition', array('params' => $params));

  if (strpos($params['value'], ':::') !== false) {
    $label = explode(':::', $params['value'])[0];
    try {
      //do lookup to see if it already exists
      $existingTag = civicrm_api3('tag', 'get', [
        'name' => $label,
        'parent_id' => 292,
        'sequential' => TRUE,
      ]);
      Civi::log()->debug('civicrm_api3_tag_saveposition', array('$existingTag' => $existingTag));

      if (!empty($existingTag['values'][0])) {
        return $existingTag;
        //return $existingTag['values'][0]['id'];
      }

      Civi::log()->debug('civicrm_api3_tag_savePosition', array('label' => $label));
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
    catch (CRM_Core_Exception $e) {
      Civi::log()->error('civicrm_api3_nyss_tags_savePosition', array('e' => $e));
    }
  } else {
    return civicrm_api3_create_success();
  }
}