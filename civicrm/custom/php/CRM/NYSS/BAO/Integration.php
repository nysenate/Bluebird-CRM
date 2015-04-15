<?php

/*
 * Project: BluebirdCRM
 * Authors: Brian Shaughnessy
 * Organization: New York State Senate
 * Date: 2015-04-10
 */

class CRM_NYSS_BAO_Integration {

  /*
   * given a website user Id, conduct a lookup to get the contact Id
   * if none, return empty
   */
  static function getContact($userId) {
    $cid = CRM_Core_DAO::singleValueQuery("
      SELECT id
      FROM civicrm_contact
      WHERE web_user_id = {$userId}
    ");

    return $cid;
  }//getContact

  /*
   * attempt to match the record with existing contacts
   */
  static function matchContact($params) {
    //format params to pass to dedupe tool
    $dedupeParams = array(
      'civicrm_contact' => array(
        'first_name' => $params['first_name'],
        'last_name' => $params['last_name'],
        'postal_code' => $params['postal_code'],
        'birth_date' => $params['birth_date'],
        'gender_id' => $params['gender_id'],
      ),
      'civicrm_address' => array(
        'street_address' => $params['street_address'],
        'city' => $params['city'],
        'postal_code' => $params['postal_code'],
      ),
    );

    if ( !empty($params['email']) ) {
      $dedupeParams['civicrm_email']['email'] = $params['email'];
    }

    $dedupeParams = CRM_Dedupe_Finder::formatParams($dedupeParams, 'Individual');
    $dedupeParams['check_permission'] = 0;

    //get indiv unsupervised rule
    $ruleTitle = CRM_Core_DAO::singleValueQuery("
      SELECT title
      FROM civicrm_dedupe_rule_group
      WHERE id = 1
    ");

    $o = new stdClass();
    $o->title = $ruleTitle;
    $o->params = $dedupeParams;
    $o->noRules = FALSE;
    $tableQueries = array();
    nyss_dedupe_civicrm_dupeQuery($o, 'table', $tableQueries);
    $sql = $tableQueries['civicrm.custom.5'];
    $sql = "
      SELECT contact.id
      FROM civicrm_contact as contact JOIN ($sql) as dupes
      WHERE dupes.id1 = contact.id AND contact.is_deleted = 0
    ";
    $r = CRM_Core_DAO::executeQuery($sql);

    $dupeIDs = array();
    while($r->fetch()) {
      $dupeIDs[] = $r->id;
    }

    //if dupe found, return id
    if ( !empty( $dupeIDs ) ) {
      $cid = $dupeIDs[0];
    }
    else {
      //if not found, create new contact
      $cid = self::createContact($params);
    }

    //set user id
    if (!empty($cid)) {
      CRM_Core_DAO::executeQuery("
        UPDATE civicrm_contact
        SET web_user_id = {$params['web_user_id']}
        WHERE id = {$cid}
      ");

      return $cid;
    }
    else {
      return array(
        'is_error' => 'Unable to match or create contact',
        'params' => $params,
      );
    }
  }

  /*
   * create a new contact
   */
  static function createContact($params) {
    $contact = civicrm_api('contact', 'create', array('version' => 3, 'contact_type' => 'Individual') + $params);
    //CRM_Core_Error::debug_var('contact', $contact);

    return $contact['id'];
  }//createContact

  //TODO when a user moves to a different district, need to reset web_user_id

  static function processIssue($contactId, $action, $params) {
    //bbscript_log('trace', '$contactId', $contactId);
    //bbscript_log('trace', '$action', $action);
    //bbscript_log('trace', '$params', $params);

    //find out if tag exists
    $parentId = CRM_Core_DAO::singleValueQuery("
      SELECT id
      FROM civicrm_tag
      WHERE name = 'Website Issues'
        AND is_tagset = 1
    ");
    $tagId = CRM_Core_DAO::singleValueQuery("
      SELECT id
      FROM civicrm_tag
      WHERE name = '{$params->issue_name}'
        AND parent_id = {$parentId}
    ");
    //CRM_Core_Error::debug_var('tagId', $tagId);

    if (!$tagId) {
      $tag = civicrm_api('tag', 'create', array(
        'version' => 3,
        'name' => $params->issue_name,
        'parent_id' => $parentId,
        'is_selectable' => 0,
        'is_reserved' => 1,
        'used_for' => 'civicrm_contact',
        'created_date' => date('Y-m-d H:i:s'),
      ));
      //CRM_Core_Error::debug_var('$tag', $tag);

      $tagId = $tag['id'];
    }

    $apiAction = ($action == 'follow') ? 'create' : 'delete';

    $et = civicrm_api('entity_tag', $apiAction, array(
      'version' => 3,
      'entity_table' => 'civicrm_contact',
      'entity_id' => $contactId,
      'tag_id' => $tagId,
    ));

    //TODO also store is_website flag
  }//processIssue
}//end class
