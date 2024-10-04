<?php

/*
 * Project: BluebirdCRM
 * Authors: Brian Shaughnessy and Ken Zalewski
 * Organization: New York State Senate
 * Date: 2015-04-10
 * Revised: 2024-03-20
 */

class CRM_NYSS_BAO_Integration_Website
{
  /*
   * given a website user Id, conduct a lookup to get the contact Id
   * if none, return empty
   */
  static function getContactId($userId)
  {
    $cid = CRM_Core_DAO::singleValueQuery("
      SELECT id
      FROM civicrm_contact
      WHERE web_user_id = '{$userId}'
    ");

    return $cid;
  } //getContactId()

  /*
   * given a contact ID, conduct a lookup to get the web ID
   * if none, return empty
   */
  static function getWebId($contactId)
  {
    $cid = CRM_Core_DAO::singleValueQuery("
      SELECT web_user_id
      FROM civicrm_contact
      WHERE id = {$contactId}
    ");

    return $cid;
  } //getWebId()

  /*
   * build contact params from row; we now need to look in multiple places:
   * 1. check for table columns (first level row elements)
   * 2. check in msg_info->user_info
   *
   * in each case, we will look for the existence of first/last name
   * @deprecated Use CRM_NYSS_BAO_Integration_WebsiteEventData
   */
  static function getContactParams($row) {
    $contactParams = [];
    $evdata = json_decode($row->event_data);
    $user_info = $evdata->user_info;
    //CRM_Core_Error::debug_var('getContactParams $evdata', $evdata);

    if (!empty($row->first_name) || !empty($row->last_name)) {
      $contactParams = [
        'web_user_id' => $row->user_id,
        'first_name' => $row->first_name,
        'last_name' => $row->last_name,
        'email' => $row->email_address,
        'street_address' => $row->address1,
        'supplemental_addresss_1' => $row->address2,
        'city' => $row->city,
        'state' => $row->state,
        'postal_code' => $row->zip,
      ];
    }
    elseif (!empty($user_info->first_name) || !empty($user_info->last_name)) {
      $contactParams = [
        'web_user_id' => $user_info->id,
        'first_name' => $user_info->first_name,
        'last_name' => $user_info->last_name,
        'email' => $user_info->email,
        'street_address' => $user_info->address,
        'city' => $user_info->city,
        'state' => $user_info->state,
        'postal_code' => $user_info->zipcode,
      ];
    }

    // if we have address fields, pass them through SAGE so we correct
    // any misspellings
    if (!empty($contactParams['state'])) {
      //match params format required by SAGE checkAddress
      $contactParams['state_province'] = $contactParams['state'];
    }
    CRM_Utils_SAGE::checkAddress($contactParams);

    return $contactParams;
  }//getContactParams

  /*
   * attempt to match the record with existing contacts
   */
  static function matchContact($params) {
    //CRM_Core_Error::debug_var('matchContact $params', $params);

    //format params to pass to dedupe tool
    $dedupeParams = [
      'civicrm_contact' => [
        'first_name' => $params['first_name'],
        'last_name' => $params['last_name'],
        'birth_date' => $params['birth_date'],
        'gender_id' => $params['gender_id'],
      ],
      'civicrm_address' => [
        'street_address' => $params['street_address'],
        'city' => $params['city'],
        'postal_code' => $params['postal_code'],
      ],
    ];

    if (!empty($params['email'])) {
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
    $tableQueries = [];
    nyss_dedupe_civicrm_dupeQuery($o, 'table', $tableQueries);
    $sql = $tableQueries['civicrm.custom.5'];
    $sql = "
      SELECT contact.id
      FROM civicrm_contact as contact JOIN ($sql) as dupes
      WHERE dupes.id1 = contact.id AND contact.is_deleted = 0
    ";

    //CRM_Core_Error::debug_var('$sql', $sql);
    $r = CRM_Core_DAO::executeQuery($sql);

    $dupeIDs = [];
    while ($r->fetch()) {
      $dupeIDs[] = $r->id;
    }
    //CRM_Core_Error::debug_var('dupeIDs', $dupeIDs);

    //if dupe found, return id
    if (!empty($dupeIDs)) {
      $cid = $dupeIDs[0];
    }
    else {
      //if not found, create new contact
      $cid = self::createContact($params);
    }

    //set user id
    if (!empty($cid) && !empty($params['web_user_id'])) {
      CRM_Core_DAO::executeQuery("
        UPDATE civicrm_contact
        SET web_user_id = {$params['web_user_id']}
        WHERE id = {$cid}
      ");

      return $cid;
    }
    elseif (!empty($cid)) {
      return $cid;
    }
    else {
      return null;
    }
  } // matchContact()

  /*
   * create a new contact
   */
  static function createContact($params)
  {
    $params['custom_60'] = 'Website Account';
    $params['contact_type'] = 'Individual';
    $params['api.address.create'] = [
      'street_address' => $params['street_address'],
      'supplemental_addresss_1' => $params['supplemental_addresss_1'],
      'city' => $params['city'],
      'state_province' => $params['state'],
      'postal_code' => $params['postal_code'],
      'location_type_id' => 1,
    ];
    self::cleanContactParams($params);
    //CRM_Core_Error::debug_var('createContact params', $params);

    $contact = civicrm_api3('contact', 'create', $params);
    //CRM_Core_Error::debug_var('contact', $contact);

    return $contact['id'];
  } //createContact()

  /*
   * because we're getting data from the web, some of it could be junk
   * initially we will just concern ourselves with field length, but in time
   * this can be a common function used for cleaning data
   */
  static function cleanContactParams(&$params) {
    $contactFields = civicrm_api3('contact', 'getfields', ['sequential' => 1, 'api_action' => 'create']);
    $addressFields = civicrm_api3('address', 'getfields', ['sequential' => 1, 'api_action' => 'create']);

    //strip HTML from name fields
    $params['first_name'] = strip_tags($params['first_name']);
    $params['middle_name'] = strip_tags($params['middle_name']);
    $params['last_name'] = strip_tags($params['last_name']);

    //cycle through contact fields and truncate if necessary
    foreach ($contactFields['values'] as $field) {
      if (array_key_exists($field['name'], $params) && !empty($field['maxlength'])) {
        if (is_string($params[$field['name']]) &&
          strlen(utf8_decode($params[$field['name']])) > $field['maxlength']
        ) {
          $params[$field['name']] = truncate_utf8($params[$field['name']], $field['maxlength']);
        }
      }
    }

    //cycle through address fields and truncate if necessary
    foreach ($addressFields['values'] as $field) {
      if (array_key_exists($field['name'], $params['api.address.create']) && !empty($field['maxlength'])) {
        if (is_string($params['api.address.create'][$field['name']]) &&
          strlen(utf8_decode($params['api.address.create'][$field['name']])) > $field['maxlength']
        ) {
          $params['api.address.create'][$field['name']] =
            truncate_utf8($params['api.address.create'][$field['name']], $field['maxlength']);
        }
      }
    }

    //remove contact elements with empty values
    foreach ($params as $f => $v) {
      if (empty($v) && $v !== 0) {
        unset($params[$f]);
      }
    }

    //remove address elements with empty values
    foreach ($params['api.address.create'] as $f => $v) {
      if (empty($v) && $v !== 0) {
        unset($params['api.address.create'][$f]);
      }
    }

    /*Civi::log()->debug('cleanContactParams', [
      'contactFields' => $contactFields,
      'addressFields' => $addressFields,
      'params' => $params,
    ]);*/
  }//cleanContactParams

  //TODO when a user moves to a different district, need to reset web_user_id

    /**
     * @deprecated Use CRM_NYSS_BAO_Integration_WebsiteEvent_IssueEvent
     */
  static function processIssue($contactId, $action, $params)
  {
    //find out if tag exists
    $parentId = CRM_Core_DAO::singleValueQuery("
      SELECT id
      FROM civicrm_tag
      WHERE name = 'Website Issues'
        AND is_tagset = 1
    ");

    //find tag name
    $tagName = self::getTagName($params, 'issue_name');
    if (empty($tagName)) {
      CRM_Core_Error::debug_var('processIssue: unable to identify tag name in $params', $params, true, true, 'integration');
      return false;
    }

    $tagId = CRM_Core_DAO::singleValueQuery("
      SELECT id
      FROM civicrm_tag
      WHERE name = %1
        AND parent_id = {$parentId}
    ", [1 => [$tagName, 'String']]);
    //CRM_Core_Error::debug_var('tagId', $tagId);

    if (!$tagId) {
      $tag = civicrm_api('tag', 'create', [
        'version' => 3,
        'name' => $tagName,
        'parent_id' => $parentId,
        'is_selectable' => 0,
        'is_reserved' => 1,
        'used_for' => 'civicrm_contact',
        'created_date' => date('Y-m-d H:i:s'),
        'description' => '',//TODO store link back to website
      ]);
      //CRM_Core_Error::debug_var('$tag', $tag);

      if ($tag['is_error']) {
        return $tag;
      }

      $tagId = $tag['id'];
    }

    //clear tag cache; entity_tag sometimes fails because newly created tag isn't recognized by pseudoconstant
    civicrm_api3('Tag', 'getfields', ['cache_clear' => 1]);

    $apiAction = ($action == 'follow') ? 'create' : 'delete';
    $et = self::entityTagAction($contactId, $tagId, $apiAction);

    return $et;
  } //processIssue()

    /**
     * @deprecated Use CRM_NYSS_BAO_Integration_WebsiteEvent_CommitteeEvent
     */
  static function processCommittee($contactId, $action, $params)
  {
    //find out if tag exists
    $parentId = CRM_Core_DAO::singleValueQuery("
      SELECT id
      FROM civicrm_tag
      WHERE name = 'Website Committees'
        AND is_tagset = 1
    ");

    //find tag name
    $tagName = self::getTagName($params, 'committee_name');
    if (empty($tagName)) {
      CRM_Core_Error::debug_var('processCommittee: unable to identify tag name in $params', $params, true, true, 'integration');
      return false;
    }

    $tagId = CRM_Core_DAO::singleValueQuery("
      SELECT id
      FROM civicrm_tag
      WHERE name = %1
        AND parent_id = {$parentId}
    ", [1 => [$tagName, 'String']]);
    //CRM_Core_Error::debug_var('tagId', $tagId);

    if (!$tagId) {
      $tag = civicrm_api('tag', 'create', [
        'version' => 3,
        'name' => $tagName,
        'parent_id' => $parentId,
        'is_selectable' => 0,
        'is_reserved' => 1,
        'used_for' => 'civicrm_contact',
        'created_date' => date('Y-m-d H:i:s'),
        'description' => ''//TODO store link back to website
      ]);
      //CRM_Core_Error::debug_var('$tag', $tag);

      if ($tag['is_error']) {
        return $tag;
      }

      $tagId = $tag['id'];
    }

    //clear tag cache; entity_tag sometimes fails because newly created tag isn't recognized by pseudoconstant
    civicrm_api3('Tag', 'getfields', ['cache_clear' => 1]);

    $apiAction = ($action == 'follow') ? 'create' : 'delete';
    $et = self::entityTagAction($contactId, $tagId, $apiAction);;

    return $et;
  } //processCommittee()

  /**
   * @deprecated Use CRM_NYSS_BAO_Integration_WebsiteEvent_BillEvent instead
   */
  static function processBill($contactId, $action, $params) {
    //CRM_Core_Error::debug_var('processBill $params', $params, true, true, 'integration');

    //find out if tag exists
    $parentId = CRM_Core_DAO::singleValueQuery("
      SELECT id
      FROM civicrm_tag
      WHERE name = 'Website Bills'
        AND is_tagset = 1
    ");

    $tagName = $tagNameBase = self::buildBillName($params);
    $tagNameOpposite = '';

    //construct tag name and determine action
    switch ($action) {
      case 'follow':
        $apiAction = 'create';
        break;
      case 'unfollow':
        $apiAction = 'delete';
        break;
      case 'aye':
        $apiAction = 'create';
        $tagName .= ': SUPPORT';
        $tagNameOpposite = $tagNameBase.': OPPOSE';
        break;
      case 'nay':
        $apiAction = 'create';
        $tagName .= ': OPPOSE';
        $tagNameOpposite = $tagNameBase.': SUPPORT';
        break;
      default:
        return [
          'is_error' => 1,
          'error_message' => 'Unable to determine bill action',
          'contactId' => $contactId,
          'action' => $action,
          'params' => $params,
        ];
    }

    $tagId = CRM_Core_DAO::singleValueQuery("
      SELECT id
      FROM civicrm_tag
      WHERE name = %1
        AND parent_id = $parentId
    ", [1 => [$tagName, 'String']]);
    //CRM_Core_Error::debug_var('tagId', $tagId);

    if (!$tagId) {
      $tag = civicrm_api('tag', 'create', [
        'version' => 3,
        'name' => $tagName,
        'parent_id' => $parentId,
        'is_selectable' => 0,
        'is_reserved' => 1,
        'used_for' => 'civicrm_contact',
        'created_date' => date('Y-m-d H:i:s')
      ]);
      //CRM_Core_Error::debug_var('$tag', $tag);

      if ($tag['is_error']) {
        return $tag;
      }

      $tagId = $tag['id'];
    }

    //clear tag cache; entity_tag sometimes fails because newly created tag isn't recognized by pseudoconstant
    civicrm_api3('Tag', 'getfields', ['cache_clear' => 1]);
    $et = self::entityTagAction($contactId, $tagId, $apiAction);

    //see if the opposite tag exists and if so, remove it
    if (!empty($tagNameOpposite)) {
      $tagIdOpp = CRM_Core_DAO::singleValueQuery("
        SELECT id
        FROM civicrm_tag
        WHERE name = %1
          AND parent_id = $parentId
      ", [1 => [$tagNameOpposite, 'String']]);

      //if the tag doesn't even exist, it's never been used on the site and we can skip the check
      if ($tagIdOpp) {
        $et = self::entityTagAction($contactId, $tagId, 'delete');
      }
    }

    return $et;
  } //processBill()

  /**
   * @deprecated use CRM_NYSS_BAO_Integration_WebsiteEvent_PetitionEvent
   */
  static function processPetition($contactId, $action, $params) {
    //find out if tag exists
    $parentId = CRM_Core_DAO::singleValueQuery("
      SELECT id
      FROM civicrm_tag
      WHERE name = 'Website Petitions'
        AND is_tagset = 1
    ");

    //find tag name
    $tagName = self::getTagName($params, 'petition_name');
    $tagStub = self::getTagName($params, 'petition_name', 'stub');
    if (empty($tagName)) {
      CRM_Core_Error::debug_var('processPetition: unable to identify tag name in $params', $params, true, true, 'integration');
      return false;
    }

    //search by petition name
    $tagId = CRM_Core_DAO::singleValueQuery("
      SELECT id
      FROM civicrm_tag
      WHERE name = %1
        AND parent_id = {$parentId}
    ", [1 => [$tagName, 'String']]);
    //CRM_Core_Error::debug_var('tagId1', $tagId);

    //search by stub if not found by name
    if (!$tagId) {
      $tagId = CRM_Core_DAO::singleValueQuery("
        SELECT id
        FROM civicrm_tag
        WHERE name = %1
          AND parent_id = {$parentId}
      ", [1 => [$tagStub, 'String']]);
    }
    //CRM_Core_Error::debug_var('tagId2', $tagId);

    if (!$tagId) {
      try {
        $tag = civicrm_api3('tag', 'create', [
          'name' => $tagStub,
          'parent_id' => $parentId,
          'is_selectable' => 0,
          'is_reserved' => 1,
          'used_for' => 'civicrm_contact',
          'created_date' => date('Y-m-d H:i:s'),
          'description' => '',//TODO store link back to website
        ]);
        //CRM_Core_Error::debug_var('$tag', $tag);
      }
      catch (CiviCRM_API3_Exception $e) {
        CRM_Core_Error::debug_var('processPetition tag creation', $e);
        return [
          'is_error' => 1,
          'details' => $e,
        ];
      }

      if ($tag['is_error']) {
        return $tag;
      }

      $tagId = $tag['id'];
    }

    //clear tag cache; entity_tag sometimes fails because newly created tag isn't recognized by pseudoconstant
    civicrm_api3('Tag', 'getfields', ['cache_clear' => 1]);

    $apiAction = (in_array($action, ['sign', 'signature update'])) ? 'create' : 'delete';
    try {
      $et = self::entityTagAction($contactId, $tagId, $apiAction);
    }
    catch (CiviCRM_API3_Exception $e) {
      CRM_Core_Error::debug_var('CRM_NYSS_BAO_Integration_Website::processPetition $e', $e);
    }

    return $et;
  } //processPetition()


  /*
   * process account records in the custom nyss_web_account table
   */
  static function processAccount($contactId, $action, $params, $created_date)
  {
    switch ($action) {
      case 'account created':
      case 'created': // new way of saying "account created"
      case 'account deleted':
      case 'login':
      case 'logout':
        $sql = "
          INSERT INTO nyss_web_account
          (contact_id, action, created_date)
          VALUES
          ({$contactId}, '{$action}', '{$created_date}')
        ";
        CRM_Core_DAO::executeQuery($sql);
        break;

      default:
        return [
          'is_error' => 1,
          'error_message' => 'Unable to determine account action',
          'contactId' => $contactId,
          'action' => $action,
          'params' => $params,
        ];
    }

    return ['is_error' => 0, 'version' => 3];
  } //processAccount()


  static function processProfile($contactId, $action, $params, $row)
  {
    //CRM_Core_Error::debug_var('processProfile $row', $row);

    //only available action is account edited
    if ($action != 'account edited') {
      return [
        'is_error' => 1,
        'error_message' => 'Unknown action type for profile: '.$action,
        'params' => $params,
      ];
    }

    $status = ($params->status) ? $params->status : 'edited';

    $profileParams = [
      'entity_id' => $contactId,
      'custom_65' => $row->first_name,
      'custom_66' => $row->last_name,
      'custom_67' => $row->address1,
      'custom_68' => $row->address2,
      'custom_69' => $row->city,
      'custom_70' => $row->state,
      'custom_71' => $row->zip,
      'custom_72' => $row->email_address,
      'custom_73' => ($row->dob) ? date('Ymd', $row->dob) : '',//dob comes as timestamp
      'custom_74' => $row->gender,
      'custom_76' => $row->top_issue,
      'custom_77' => $status,
      'custom_78' => $row->user_is_verified,
      'custom_79' => date('YmdHis', $row->created_at),
    ];
    //CRM_Core_Error::debug_var('profileParams', $profileParams);

    try {
      $result = civicrm_api3('custom_value', 'create', $profileParams);
      //CRM_Core_Error::debug_var('update profile result', $result);
    }
    catch (CiviCRM_API3_Exception $e) {
      // handle error here
      $errorMessage = $e->getMessage();
      $errorCode = $e->getErrorCode();
      $errorData = $e->getExtraParams();

      return [
        'is_error' => 1,
        'error_message' => $errorMessage,
        'error_code' => $errorCode,
        'error_data' => $errorData
      ];
    }

    //9581 update contact record if data missing
    $contact = civicrm_api3('contact', 'getsingle', ['id' => $contactId]);

    $updateParams = [
      'id' => $contactId,
    ];
    $update = false;

    if (empty($contact['email']) && !empty($row->email_address)) {
      $updateParams['api.email.create'] = [
        'email' => $row->email_address,
        'location_type_id' => 1,
      ];
      $update = true;
    }

    if (empty($contact['gender']) && !empty($row->gender)) {
      switch ($row->gender) {
        case 'male':
          $updateParams['gender_id'] = 2;
          break;
        case 'female':
          $updateParams['gender_id'] = 1;
          break;
        case 'other':
          $updateParams['gender_id'] = 4;
          break;
        default:
      }
      $update = true;
    }

    if (empty($contact['birth_date']) && !empty($row->dob)) {
      $updateParams['birth_date'] = date('Ymd', $row->dob);
      $update = true;
    }

    if (empty($contact['street_address']) && !empty($row->address1)) {
      $updateParams['api.address.create'] = [
        'street_address' => $row->address1,
        'supplemental_addresss_1' => $row->address2,
        'city' => $row->city,
        'state_province' => $row->state,
        'postal_code' => $row->zip,
        'location_type_id' => 1,
      ];
      $update = true;
    }

    //CRM_Core_Error::debug_var('$updateParams', $updateParams);
    if ($update) {
      civicrm_api3('contact', 'create', $updateParams);
    }

    return $result;
  } //processProfile()


  /*
   * process communication and contextual messages as notes
   */
  static function processCommunication($contactId, $action, $params, $type, $timestamp = null) {
    if ($type == 'DIRECTMSG') {
      $activityType = 'website_direct_message';

      $subject = 'Direct Message Received';
      if (!empty($params->subject)) {
        $subject = $params->subject;
      }

      $note = $params->message;
      if (empty($note)) {
        $note = '[no message]';
      }
    }
    else {
      $activityType = 'website_contextual_message';

      $subject = 'Contextual Message Received';
      if (!empty($params->subject)) {
        $subject = $params->subject;
      }

      $note = $params->message;
      if (!empty($params->bill_number)) {
        $note = "{$params->message}<br /><br />
          Bill Number: {$params->bill_number}<br />
          Bill Year: {$params->bill_year}
        ";
      }
      if (empty($note)) {
        $note = '[no message]';
      }
    }

    $params = [
      'activity_type_id' => $activityType,
      'source_contact_id' => $contactId,
      'target_id' => $contactId,
      'subject' => $subject,
      'activity_date_time' => date('Y-m-d H:i:s', $timestamp),
      'details' => $note,
      'status_id' => 'Completed',
    ];

    try {
      $result = civicrm_api3('activity', 'create', $params);
      //CRM_Core_Error::debug_var('processCommunication result', $result);
    }
    catch (CiviCRM_API3_Exception $e) {
      // handle error here
      $errorMessage = $e->getMessage();
      $errorCode = $e->getErrorCode();
      $errorData = $e->getExtraParams();

      return [
        'is_error' => 1,
        'error_message' => $errorMessage,
        'error_code' => $errorCode,
        'error_data' => $errorData
      ];
    }

    return $result;
  } // processCommunication()


  /*
   * handle surveys (questionnaire response) with
   */
  static function processSurvey($contactId, $action, $params)
  {
    //check if survey exists; if not, construct fields
    if (!$flds = self::surveyExists($params)) {
      $flds = self::buildSurvey($params);
    }

    if (empty($flds)) {
      return [
        'is_error' => 1,
        'error_message' => 'Unable to build survey'
      ];
    }

    //build array for activity
    $actParams = [
      'subject' => $params->form_title,
      'date' => date('Y-m-d H:i:s'),
      'activity_type_id' => CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_type_id', 'Website Survey'),
      'details' => (!empty($params->detail)) ? $params->detail : '',
      'target_contact_id' => $contactId,
      'source_contact_id' => civicrm_api3('uf_match', 'getvalue', [
        'uf_id' => 1,
        'return' => 'contact_id',
      ]),
    ];
    //CRM_Core_Error::debug_var('actParams', $actParams);

    //wrap activity and custom data in a transaction
    try {
      $transaction = new CRM_Core_Transaction();

      $act = civicrm_api3('activity', 'create', $actParams);
      if ($act['is_error']) {
        return $act;
      }

      $custParams = [
        'entity_id' => $act['id'],
        'custom_80' => $params->form_title,
        'custom_81' => $params->form_id,
      ];

      foreach ($params->form_values as $k => $f) {
        //CRM_Core_Error::debug_var("field $k", $f);

        //some surveys are constructed with duplicate field names, so need to make
        //sure we don't overwrite or skip
        if (isset($flds[$f->field]) && !isset($custParams[$flds[$f->field]])) {
          $custParams[$flds[$f->field]] = $f->value;
        }
        else {
          //try alternate field label (if duplicate)
          $custParams[$flds["{$f->field} ({$k})"]] = $f->value;
        }
      }
      //CRM_Core_Error::debug_var('actParams', $actParams);
      $cf = civicrm_api3('custom_value', 'create', $custParams);

      $transaction->commit();

      if (!empty($cf) && empty($cf['is_error'])) {
        return $cf;
      }
    }
    catch (CiviCRM_API3_Exception $e) {
      CRM_Core_Error::debug_var('Survey Construction Issue', $e, TRUE, TRUE, 'integration');
    }

    return [
      'is_error' => 1,
      'details' => 'Unable to store survey',
      'form_id' => $params->form_id,
      'contact_id' => $contactId,
    ];
  } //processSurvey()


  /*
   * get web account history for a contact
   */
  static function getAccountHistory($cid)
  {
    $sql = "
      SELECT *
      FROM nyss_web_account
      WHERE contact_id = {$cid}
      ORDER BY created_date DESC
      LIMIT 50
    ";
    $r = CRM_Core_DAO::executeQuery($sql);

    $rows = [];
    while ($r->fetch()) {
      $rows[] = [
        'action' => $r->action,
        'created' => date('F jS, Y g:i A', strtotime($r->created_date)),
      ];
    }

    return $rows;
  } // getAccountHistory()


  /*
   * get web messages for a contact
   */
  static function getMessages($cid)
  {
    $sql = "
      SELECT *
      FROM civicrm_note
      WHERE entity_id = {$cid}
        AND entity_table IN ('nyss_contextmsg', 'nyss_directmsg')
      ORDER BY modified_date DESC
      LIMIT 50
    ";
    $r = CRM_Core_DAO::executeQuery($sql);

    $rows = [];
    while ($r->fetch()) {
      $rows[] = [
        'subject' => $r->subject,
        'modified_date' => date('F jS, Y', strtotime($r->modified_date)),
        'note' => nl2br($r->note),
      ];
    }

    return $rows;
  } // getMessages()


  /*
   * check if survey already exists; if so, return fields by label
   * else return false
   */
  static function surveyExists($params)
  {
    if (empty($params->form_id)) {
      return false;
    }

    //see if any activity records exist with the survey id
    $act = CRM_Core_DAO::singleValueQuery("
      SELECT count(id)
      FROM civicrm_value_website_survey_10
      WHERE survey_id_81 = {$params->form_id}
    ");

    //see if custom set exists
    $cs = CRM_Core_DAO::singleValueQuery("
      SELECT *
      FROM civicrm_custom_group
      WHERE name LIKE 'Survey_{$params->form_id}'
    ");

    //CRM_Core_Error::debug_var('act', $act);
    //CRM_Core_Error::debug_var('cs', $cs);

    if (!$act && !$cs) {
      return false;
    }

    //get custom fields for this set
    $cf = civicrm_api3('custom_field', 'get', ['custom_group_id' => $cs]);
    //CRM_Core_Error::debug_var('$cf', $cf);

    //check to see if existing fields count equals params field count
    //if not, need to rebuild fields
    if (count($cf['values']) != count($params->form_values)) {
      $fields = self::buildSurvey($params);
      //CRM_Core_Error::debug_var('$fields', $fields);
    }

    $fields = [];
    foreach ($cf['values'] as $id => $f) {
      $fields[$f['label']] = "custom_{$id}";
    }
    //CRM_Core_Error::debug_var('surveyExists $fields', $fields);

    return $fields;
  } //surveyExists()


  /*
   * create custom data set and fields for survey
   */
  static function buildSurvey($data) {
    if (empty($data->form_id)) {
      return FALSE;
    }

    //create custom group if it doesn't exist
    $csID = CRM_Core_DAO::singleValueQuery("
      SELECT id
      FROM civicrm_custom_group
      WHERE name LIKE 'Survey_{$data->form_id}'
    ");

    if (!$csID) {
      $weight = CRM_Core_DAO::singleValueQuery("
        SELECT max(weight)
        FROM civicrm_custom_group
      ");

      //truncate title; first determine length we can handle, accounting for static text and form_id
      $addedText = strlen((string)$data->form_id) + 12;
      $formTitle = substr($data->form_title, 0, 128 - $addedText);

      $params = [
        'name' => "Survey_{$data->form_id}",
        'title' => "Survey: {$formTitle} [{$data->form_id}]",
        'table_name' => "civicrm_value_surveydata_{$data->form_id}",
        'extends' => ['0' => 'Activity'],
        'extends_entity_column_value' => CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_type_id', 'Website Survey'),
        'collapse_display' => 1,
        'collapse_adv_display' => 1,
        'style' => 'Inline',
        'is_active' => 1,
        'weight' => $weight++
      ];

      try {
        $cg = civicrm_api3('custom_group', 'create', $params);
        $csID = $cg['id'];
      }
      catch (CiviCRM_API3_Exception $e) {
        CRM_Core_Error::debug_var('buildSurvey $e', $e, TRUE, TRUE, 'integration');

        return FALSE;
      }
    }

    //get existing fields for this custom data set
    $existingFieldLabels = [];
    $existingFieldNames = [];
    $existingFields = civicrm_api3('custom_field', 'get', [
      'custom_group_id' => $csID,
      'options' => ['limit' => 0]
    ]);
    //CRM_Core_Error::debug_var('existingFields', $existingFields);
    //CRM_Core_Error::debug_var('$data->form_values', $data->form_values);

    foreach ($existingFields['values'] as $ef) {
      $existingFieldLabels[$ef['id']] = $ef['label'];
      $existingFieldNames[] = $ef['name'];
    }

    $fields = [];
    $weight = 0;
    $fieldCreated = false;
    foreach ($data->form_values as $k => $f) {
      //check to see if field has already been created; if so, set to fields and skip
      if (in_array(trim($f->field), $existingFieldLabels)) {
        $efKey = array_search($f->field, $existingFieldLabels);
        $fields[$f->field] = "custom_{$efKey}";
        continue;
      }

      //make sure label is unique
      $label = substr(trim($f->field), 0, 1018);
      if (array_key_exists($f->field, $fields)) {
        $label = substr($label, 0, 1010);
        $label = "{$label} ({$k})";
      }

      //make sure name is unique -- name is different than label and also
      // needs to be unique
      $field_name = CRM_NYSS_BAO_Integration_WebsiteEvent_SurveyEvent::ensureUnique($f->field, $existingFieldNames,64);

      //CRM_Core_Error::debug_var('buildSurvey $label', $label, TRUE, TRUE, 'integration');

      $params = [
        'custom_group_id' => $csID,
        'label' => $label,
        'name' => $field_name,
        'data_type' => 'String',
        'html_type' => 'Text',
        'is_searchable' => 1,
        'is_active' => 1,
        'is_view' => 1,
        'weight' => $weight++,
      ];
      //CRM_Core_Error::debug_var('fields $params', $params);

      try {
        $cf = civicrm_api3('custom_field', 'create', $params);

        $fields[$f->field] = "custom_{$cf['id']}";
        $existingFieldNames[] = $field_name;
        $fieldCreated = TRUE;
      }
      catch (CiviCRM_API3_Exception $e) {
        $fieldCreated = FALSE;
        CRM_Core_Error::debug_var('buildSurvey $e', $e, TRUE, TRUE, 'integration');
      }
    }
    //CRM_Core_Error::debug_var('final $fields', $fields);
    //CRM_Core_Error::debug_var('$fieldCreated', $fieldCreated);

    if ($fieldCreated) {
      $logging = new CRM_Logging_Schema;
      $logging->fixSchemaDifferencesForAll();
    }

    return $fields;
  } //buildSurvey()

  static function buildBillName($params) {
    //get data pieces from possible locations
    $bill_number = (!empty($params->event_info->bill_number)) ?
      $params->event_info->bill_number : $params->bill_number;
    $bill_year = (!empty($params->event_info->bill_year)) ?
      $params->event_info->bill_year : $params->bill_year;
    $bill_sponsor = (!empty($params->event_info->sponsors)) ?
      $params->event_info->sponsors : $params->bill_sponsor;

    //build bill value text
    $billName = $bill_number.'-'.$bill_year;

    if (!empty($bill_sponsor)) {
      $sponsor = strtoupper($bill_sponsor);
    }
    else {
      require_once 'CRM/NYSS/BAO/Integration/OpenLegislation.php';
      $sponsor = CRM_NYSS_BAO_Integration_OpenLegislation::getBillSponsor($billName);
    }

    return "{$billName} ({$sponsor})";
  }//buildBillName

  /*
   * get the four types of website tagset tags
   * return hierarchal array by tagset
   */
  static function getTags($cid)
  {
    $parentNames = CRM_Core_BAO_Tag::getTagSet('civicrm_contact');
    //CRM_Core_Error::debug_var('$parentNames', $parentNames);

    $tags = [
      'Website Bills' =>
        CRM_Core_BAO_EntityTag::getChildEntityTagDetails(array_search('Website Bills', $parentNames), $cid),
      'Website Committees' =>
        CRM_Core_BAO_EntityTag::getChildEntityTagDetails(array_search('Website Committees', $parentNames), $cid),
      'Website Issues' =>
        CRM_Core_BAO_EntityTag::getChildEntityTagDetails(array_search('Website Issues', $parentNames), $cid),
      'Website Petitions' =>
        CRM_Core_BAO_EntityTag::getChildEntityTagDetails(array_search('Website Petitions', $parentNames), $cid),
    ];

    //CRM_Core_Error::debug_var('$tags', $tags);
    return $tags;
  } //getTags()


  /*
   * get activity stream for contact
   */
  static function getActivityStream()
  {
    //CRM_Core_Error::debug_var('getActivityStream $_REQUEST', $_REQUEST);

    $contactID = CRM_Utils_Type::escape($_REQUEST['cid'], 'Integer', false);
    //CRM_Core_Error::debug_var('getActivityStream $contactID', $contactID);
    $contactIDSql = ($contactID) ? "contact_id = {$contactID}" : '(1)';

    $type = CRM_Utils_Type::escape($_REQUEST['atype'], 'String', false);
    //CRM_Core_Error::debug_var('getActivityStream $type', $type);
    $typeSql = ($type) ? "AND type = '{$type}'" : '';

    $sortMapper = [
      0 => 'sort_name',
      1 => 'type',
      2 => 'created_date',
      3 => 'details',
    ];

    $sEcho = CRM_Utils_Type::escape($_REQUEST['sEcho'], 'Integer');
    $offset = isset($_REQUEST['iDisplayStart']) ? CRM_Utils_Type::escape($_REQUEST['iDisplayStart'], 'Integer') : 0;
    $rowCount = isset($_REQUEST['iDisplayLength']) ? CRM_Utils_Type::escape($_REQUEST['iDisplayLength'], 'Integer') : 25;
    $sort = isset($_REQUEST['iSortCol_0']) ? CRM_Utils_Array::value(CRM_Utils_Type::escape($_REQUEST['iSortCol_0'], 'Integer'), $sortMapper) : NULL;
    $sortOrder = isset($_REQUEST['sSortDir_0']) ? CRM_Utils_Type::escape($_REQUEST['sSortDir_0'], 'String') : 'asc';

    $params = $_REQUEST;
    if ($sort && $sortOrder) {
      $params['sortBy'] = $sort . ' ' . $sortOrder;
    }

    $params['page'] = ($offset / $rowCount) + 1;
    $params['rp'] = $rowCount;

    if ($contactID) {
      $params['contact_id'] = $contactID;
    }

    //CRM_Core_Error::debug_var('getActivityStream $params', $params);

    $orderBy = ($params['sortBy']) ? $params['sortBy'] : 'created_date desc';

    $activity = [];
    $sql = "
      SELECT SQL_CALC_FOUND_ROWS a.*, c.sort_name, c.id as cid
      FROM nyss_web_activity a
      JOIN civicrm_contact c
        ON a.contact_id = c.id
      WHERE $contactIDSql
        {$typeSql}
      ORDER BY {$orderBy}
      LIMIT {$rowCount} OFFSET {$offset}
    ";
    //CRM_Core_Error::debug_var('getActivityStream $sql', $sql);
    $dao = CRM_Core_DAO::executeQuery($sql);
    $totalRows = CRM_Core_DAO::singleValueQuery('SELECT FOUND_ROWS()');
    //CRM_Core_Error::debug_var('getActivityStream $totalRows', $totalRows);

    while ($dao->fetch()) {
      $url = CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid={$dao->cid}");

      $additionalDetails = '';
      if (in_array($dao->type, ['Direct Message', 'Context Message']) &&
        !empty($dao->data)
      ) {
        $data = json_decode($dao->data, true);
        if (!empty($data['note_id'])) {
          $note = CRM_Core_DAO::singleValueQuery("
            SELECT note
            FROM civicrm_note
            WHERE id = {$data['note_id']}
          ");
          $additionalDetails = " <a href='#' onclick='displayNote({$data['note_id']}); return false;'>[view message]</a><div title='Message Text' style='display:none;' id='msg-{$data['note_id']}'>{$note}</div>";
        }
      }

      $activity[$dao->id] = [
        'sort_name' => "<a href='{$url}'>{$dao->sort_name}</a>",
        'type' => $dao->type,
        'created_date' => date('m/d/Y g:i A', strtotime($dao->created_date)),
        'details' => $dao->details.$additionalDetails,
      ];
    }
    //CRM_Core_Error::debug_var('getActivityStream $activity', $activity);

    $iFilteredTotal = $iTotal = $params['total'] = $totalRows;
    $selectorElements = [
      'sort_name', 'type', 'created_date', 'details',
    ];

    echo CRM_Utils_JSON::encodeDataTableSelector($activity, $sEcho, $iTotal, $iFilteredTotal, $selectorElements);
    CRM_Utils_System::civiExit();
  } //getActivityStream()


  /*
   * store basic details about the event in the activity log
   */
  static function storeActivityLog($cid, $type, $date, $details, $data)
  {
    //CRM_Core_Error::debug_var('storeActivityLog', $type);

    $params = [1 => [$details, 'String']];
    $template = "INSERT INTO nyss_web_activity (contact_id, type, created_date, details, data)
            VALUES (:values)";

    $values = [
      $cid,
      $type,
      $date,
      $details,
      $data
    ];

    // Escape and Quote Values
    $data = array_map(function($value) {
      return "'" . CRM_Core_DAO::escapeString($value) . "'";
    }, $values);

    $replacements = [
      ':values' => implode(", ", $data),
    ];

    $sql = strtr($template, $replacements);
    CRM_Core_DAO::executeQuery($sql);
  } //storeActivityLog()


  /*
   * archive the accumulator record and then delete from accumulator
   */
  static function archiveRecord($db, CRM_NYSS_BAO_Integration_WebsiteEventInterface $event_type, $row, $params, $success = true)
  {
    //CRM_Core_Error::debug_var('archiveRecord $event_type', $event_type);
    //CRM_Core_Error::debug_var('archiveRecord $row', $row);
    //CRM_Core_Error::debug_var('archiveRecord $params', $params);

    //set archive date to current timestamp
    $date = date('Y-m-d H:i:s');

    //wrap in a transaction so we store archive and delete from accumulator together
    $transaction = new CRM_Core_Transaction();

    //extra fields by type -- now handled by WebsiteEvent class
    //$extraFields = [
    //  'bill' => ['bill_number', 'bill_year'],
    //  'issue' => ['issue_name'],
    //  'committee' => ['committee_name'],
    //  'contextmsg' => ['bill_number'],
    //  'petition' => ['petition_id'],
    //  'survey' => ['form_id']
    //];

    //setup fields for common archive table insert
    $fields = array_keys(get_object_vars($row));
    //remove object properties
    foreach ($fields as $k => $f) {
      if (strpos($f, '_') === 0 || $f == 'N') {
        unset($fields[$k]);
      }
    }
    $fields[] = 'archive_date';
    $fieldList = implode(', ', $fields);
    //CRM_Core_Error::debug_var('archiveRecord $fields', $fields);

    //setup data
    $data = [];
    foreach ($row as $f => $v) {
      if (in_array($f, $fields)) {
        // BUG: treats all values as strings, but not all values are strings.
        // temporary fix is to handle non-string fields differently.
        if ($f == 'dob' || $f == 'created_at') {
          $data[] = (empty($v)) ? 'NULL' : "'" . $v . "'";
        } else {
          $data[] = "'" . CRM_Core_DAO::escapeString($v) . "'";
        }
      }
    }

    //add date stamp
    $data[] = "'" . $date . "'";

    $dataList = implode(",", $data);
    //CRM_Core_Error::debug_var('archiveRecord $data', $data);

    $mainArchiveTable = ($success) ? 'archive' : 'archive_error';

    $sql = "
      INSERT INTO {$db}.{$mainArchiveTable}
      ({$fieldList})
      VALUES
      ({$dataList})
    ";
    
    //CRM_Core_Error::debug_var('archiveRecord $sql', $sql);
    CRM_Core_DAO::executeQuery($sql);

    // Save to Event Specific Archive Table
    if ($event_type->hasArchiveTable()) {
        $sql = $event_type->getArchiveSQL($row->id, $db);
        CRM_Core_DAO::executeQuery($sql);
    }

    //now delete record from accumulator
    CRM_Core_DAO::executeQuery("
      DELETE FROM {$db}.accumulator
      WHERE id = {$row->id}
    ");

    //if errored, trigger notification email
    if (!$success) {
      self::notifyError($db, $event_type->getEventDescription(), $row, $params, $date);
    }

    $transaction->commit();
  } // archiveRecord()

  /*
   * get recently created contacts
   */
  static function getNewContacts()
  {
    //CRM_Core_Error::debug_var('getNewContacts $_REQUEST', $_REQUEST);

    $sortMapper = [
      0 => 'contact',
      1 => 'date',
      2 => 'email',
      3 => 'address',
      4 => 'city',
      5 => 'source'
    ];

    $sEcho = CRM_Utils_Type::escape($_REQUEST['sEcho'], 'Integer');
    $offset = isset($_REQUEST['iDisplayStart']) ? CRM_Utils_Type::escape($_REQUEST['iDisplayStart'], 'Integer') : 0;
    $rowCount = isset($_REQUEST['iDisplayLength']) ? CRM_Utils_Type::escape($_REQUEST['iDisplayLength'], 'Integer') : 25;
    $sort = isset($_REQUEST['iSortCol_0']) ? CRM_Utils_Array::value(CRM_Utils_Type::escape($_REQUEST['iSortCol_0'], 'Integer'), $sortMapper) : NULL;
    $sortOrder = isset($_REQUEST['sSortDir_0']) ? CRM_Utils_Type::escape($_REQUEST['sSortDir_0'], 'String') : 'asc';

    $params = $_REQUEST;
    if ($sort && $sortOrder) {
      $params['sortBy'] = $sort . ' ' . $sortOrder;
    }

    $params['page'] = ($offset / $rowCount) + 1;
    $params['rp'] = $rowCount;

    //CRM_Core_Error::debug_var('getActivityStream $params', $params);

    //source field sql
    $source = CRM_Utils_Type::escape($_REQUEST['source'], 'String', false);
    $sourceSql = '';
    if ($source == 'Website') {
      $sourceSql = "AND contact_source_60 = 'Website Account'";
    }
    elseif ($source == 'Bluebird') {
      $sourceSql = "AND (contact_source_60 != 'Website Account' OR contact_source_60 IS NULL)";
    }

    //date fields sql
    $dateType = CRM_Utils_Type::escape($_REQUEST['date_relative'], 'String', false);
    //CRM_Core_Error::debug_var('getNewContacts $dateType', $dateType);
    $dateSql = '';

    if (!empty($dateType) && $dateType !== '0') {
      //relative date
      //CRM_Core_Error::debug_log_message('relative date processing...');
      CRM_Contact_BAO_Query::fixDateValues($_REQUEST['date_relative'], $_REQUEST['date_low'], $_REQUEST['date_high']);
      //CRM_Core_Error::debug_var('getNewContacts relative date $_REQUEST', $_REQUEST);
    }

    $date_low = ($_REQUEST['date_low']) ? date('Y-m-d H:i:s', strtotime($_REQUEST['date_low'])) : '';
    $date_high = ($_REQUEST['date_high']) ? date('Y-m-d H:i:s', strtotime($_REQUEST['date_high'])) : '';
    //CRM_Core_Error::debug_var('getNewContacts $date_low', $date_low);
    //CRM_Core_Error::debug_var('getNewContacts $date_high', $date_high);

    if ($date_low) {
      $dateSql .= "AND created_date >= '{$date_low}'";
    }
    if ($date_high) {
      $dateSql .= "AND created_date <= '{$date_high}'";
    }

    $orderBy = 'created_date desc';
    if ($params['sortBy']) {
      //CRM_Core_Error::debug_var('getNewContacts $params[sortBy]', $params['sortBy']);
      //column values don't directly match field names so we must convert
      switch ($params['sortBy']) {
        case 'source asc':
          $orderBy = 'contact_source_60 asc';
          break;
        case 'source desc':
          $orderBy = 'contact_source_60 desc';
          break;
        case 'address asc':
          $orderBy = 'street_address asc';
          break;
        case 'address desc':
          $orderBy = 'street_address desc';
          break;
        case 'date asc':
          $orderBy = 'created_date asc';
          break;
        case 'date desc':
          $orderBy = 'created_date desc';
          break;
        case 'contact asc':
          $orderBy = 'sort_name asc';
          break;
        case 'contact desc':
          $orderBy = 'sort_name desc';
          break;

        default:
          $orderBy = $params['sortBy'];
      }
    }

    $newcontacts = [];
    $sql = "
      SELECT SQL_CALC_FOUND_ROWS c.*, ci.contact_source_60, e.email, a.street_address, a.city
      FROM civicrm_contact c
      LEFT JOIN civicrm_value_constituent_information_1 ci
        ON ci.entity_id = c.id
      LEFT JOIN civicrm_email e
        ON e.contact_id = c.id
        AND e.is_primary = 1
      LEFT JOIN civicrm_address a
        ON a.contact_id = c.id
        AND a.is_primary = 1
      WHERE (1)
        {$sourceSql}
        {$dateSql}
      ORDER BY {$orderBy}
      LIMIT {$rowCount} OFFSET {$offset}
    ";
    //CRM_Core_Error::debug_var('getNewContacts $sql', $sql);
    $dao = CRM_Core_DAO::executeQuery($sql);
    $totalRows = CRM_Core_DAO::singleValueQuery('SELECT FOUND_ROWS()');
    //CRM_Core_Error::debug_var('getNewContacts $totalRows', $totalRows);

    while ($dao->fetch()) {
      $url = CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid={$dao->id}");
      $newcontacts[$dao->id] = [
        'sort_name' => "<a href='{$url}'>{$dao->sort_name}</a>",
        'date' => date('m/d/Y g:i A', strtotime($dao->created_date)),
        'email' => $dao->email,
        'address' => $dao->street_address,
        'city' => $dao->city,
        'source' => ($dao->contact_source_60 == 'Website Account') ? 'Website' : 'Bluebird'
      ];
    }
    //CRM_Core_Error::debug_var('getActivityStream $activity', $activity);

    $iFilteredTotal = $iTotal = $params['total'] = $totalRows;
    $selectorElements = [
      'sort_name', 'date', 'email', 'address', 'city', 'source',
    ];

    echo CRM_Utils_JSON::encodeDataTableSelector($newcontacts, $sEcho, $iTotal, $iFilteredTotal, $selectorElements);
    CRM_Utils_System::civiExit();
  } //getActivityStream()

  /*
   * helper to get tag name as it could be passed in different ways
   * $params the parameter object passed to the record processing function
   * $alternate the alternate column name/param name where we may need to look
   *   for backwards compatibility
   * $primary pass a preferred key to search for instead of the default
   */
  static function getTagName($params, $alternate, $primary = NULL) {
    $tagName = '';
    if (!empty($primary) && !empty($params->event_info->$primary)) {
      $tagName = $params->event_info->$primary;
    }
    elseif (!empty($params->event_info->name)) {
      $tagName = $params->event_info->name;
    }
    elseif (!empty($params->event_info->$alternate)) {
      $tagName = $params->event_info->$alternate;
    }
    elseif (!empty($params->$alternate)) {
      $tagName = $params->$alternate;
    }

    return $tagName;
  }//getTagName


    /**
     * method has been deprecated, but left in place for backward compatibility.
     * @deprecated use createContactEmail() instead
     * @param int $cid
     * @param object $row
     * @return void
     */
    static function updateEmail(int $cid, object $row) : void {
    //email reside in one of three places
    $params = json_decode($row->msg_info);
    $email = null;

    if (!empty($params->user_info->email)) {
      $email = $params->user_info->email;
    }
    elseif (!empty($params->form_info->user_email)) {
      $email = $params->form_info->user_email;
    }
    elseif (!empty($row->email_address)) {
      $email = $row->email_address;
    }

    if (empty($email)) {
      return;
    }

    self::createContactEmail($cid,$email);

  }

    /*
     * we want to make sure we store the email address, regardless of whether we
     * have created the contact or found an existing one.
     * given a contact ID, we determine if the email address already exists;
     * if so, continue with no action. if it does not exist, add it and set it as
     * the primary email for the contact
     */
    /**
     * @throws CRM_Core_Exception
     */
    public static function createContactEmail(int $contact_id, string $email): int
  {

      $count_updated = 0;

      //determine if email already exists for contact
      $exists = CRM_Core_DAO::singleValueQuery("
      SELECT e.id
      FROM civicrm_email e
      JOIN civicrm_contact c
        ON e.contact_id = c.id
        AND c.is_deleted != 1
      WHERE contact_id = %1
        AND email = %2
      LIMIT 1
    ", [
          1 => [$contact_id, 'Integer'],
          2 => [$email, 'String']
      ]);

      if (!$exists) {
          $result = civicrm_api3('email', 'create', [
              'contact_id' => $contact_id,
              'email' => $email,
              'is_primary' => true,
              'location_type_id' => 1,
          ]);

          if ($result['is_error'] === 1) {
            throw new Exception($result['error_message']);
          } else {
            return (int)$result['count'];
          }
      }

      return $count_updated;
  }

  /**
   * @param $contactId
   * @param $tagId
   * @param $action
   *
   * @return array
   *
   * wrapper for entity_tag actions. this let's us determine existence before we
   * act in order to avoid harmless errors from the API
   */
  static function entityTagAction($contactId, $tagId, $action, $entityTable = 'civicrm_contact') {
    //setup common params
    $params = [
      'tag_id' => $tagId,
      'entity_id' => $contactId,
      'entity_table' => $entityTable
    ];

    try {
      //perform a get to see if entity_tag record exists
      $exists = civicrm_api3('entity_tag', 'get', $params);

      if (($exists['count'] && $action == 'create') ||
        (empty($exists['count']) && $action == 'delete')
      ) {
        $results = $exists;
      }
      else {
        $results = civicrm_api3('entity_tag', $action, $params);
      }
    }
    catch (CiviCRM_API3_Exception $e) {
      CRM_Core_Error::debug_var('CRM_NYSS_BAO_Integration_Website::entityTagAction', $e);
    }

    return $results;
  }

  /**
   * @param $db
   * @param $type
   * @param $row
   * @param $params
   * @param $date
   *
   * notify admins when there is an error getting archived
   */
  static function notifyError($db, $type, $row, $params, $date) {
    $toEmails = variable_get('civicrm_error_to');
    //Civi::log()->debug('notifyError', ['$toEmails' => $toEmails]);
    if (empty($toEmails)) {
      return;
    }

    $html = "The website integration script has encountered an error at {$date}. The details are below. <br /><br /><pre>";
    $html .= "db: {$db}\n";
    $html .= "type: {$type}\n";
    $html .= "date: {$date}\n";
    $html .= "row: ".print_r($row, TRUE);
    $html .= "params: ".print_r($params, TRUE);
    $html .= "</pre>";

    $fromEmailAddress = CRM_Core_OptionGroup::values('from_email_address', NULL, NULL, NULL, ' AND is_default = 1');

    foreach (explode(',', $toEmails) as $toEmail) {
      $mailParams = [
        'toEmail' => $toEmail,
        'subject' => "Error processing accumulator data: {$db}",
        'html' => $html,
        'from' => reset($fromEmailAddress),
      ];
      //Civi::log()->debug('notifyError', ['mailParams' => $mailParams]);

      CRM_Utils_Mail::send($mailParams);
    }
  }
}//end class
