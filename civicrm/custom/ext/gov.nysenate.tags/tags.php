<?php

require_once 'tags.civix.php';


/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function tags_civicrm_config(&$config) {
  _tags_civix_civicrm_config($config);
}


/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function tags_civicrm_xmlMenu(&$files) {
  _tags_civix_civicrm_xmlMenu($files);
}


/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function tags_civicrm_install() {
  _tags_civix_civicrm_install();
}


/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function tags_civicrm_uninstall() {
  _tags_civix_civicrm_uninstall();
}


/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function tags_civicrm_enable() {
  _tags_civix_civicrm_enable();
}


/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function tags_civicrm_disable() {
  _tags_civix_civicrm_disable();
}


/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of
 *               pending upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (true if pending upgrades)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function tags_civicrm_upgrade($op, CRM_Queue_Queue $queue = null) {
  return _tags_civix_civicrm_upgrade($op, $queue);
}


/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function tags_civicrm_managed(&$entities) {
  _tags_civix_civicrm_managed($entities);
}


/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function tags_civicrm_caseTypes(&$caseTypes) {
  _tags_civix_civicrm_caseTypes($caseTypes);
}


/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function tags_civicrm_angularModules(&$angularModules) {
  _tags_civix_civicrm_angularModules($angularModules);
}


/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function tags_civicrm_alterSettingsFolders(&$metaDataFolders = null) {
  _tags_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

function tags_civicrm_merge($type, &$sqls, $fromId, $toId, $tables) {
  //insert civicrm_log record for every contact, case or activity affected
  //by a tag merge.
  if ($type == 'sqls' && is_array($tables) && in_array('civicrm_tag', $tables)
      && $_GET['q'] == 'civicrm/ajax/mergeTags') {
    $session = CRM_Core_Session::singleton();
    $userID = $session->get('userID');

    $sql = "
      INSERT INTO civicrm_log ( entity_table, entity_id, data, modified_id, modified_date )
      SELECT et.entity_table, et.entity_id, CONCAT('Merged tag: ', tag.name, ' (', tag.id, ' with ', {$toId}, ')'), {$userID}, NOW()
      FROM civicrm_entity_tag et
      INNER JOIN civicrm_tag tag
        ON et.tag_id = tag.id
      WHERE tag_id = %2
    ";
    array_unshift($sqls, $sql);
  }
} //tags_civicrm_merge()

function tags_civicrm_buildForm($formName, &$form) {
  /*Civi::log()->debug('buildForm', array(
    'formName' => $formName,
    'form' => $form,
  ));*/

  if (in_array($formName, array('CRM_Tag_Form_Tag', 'CRM_Contact_Form_Task_AddToTag',
    'CRM_Contact_Form_Contact', 'CRM_Contact_Form_Task_RemoveFromTag'))
  ) {
    $webSets = array(
      'Website Bills',
      'Website Committees',
      'Website Issues',
      'Website Petitions',
    );
    $webViewOnly = array();
    foreach ($form->_tagsetInfo as $setId => $setDetails) {
      //Civi::log()->debug('buildForm', array('$setDetails' => $setDetails));

      try {
        $setName = (!empty($setDetails['parentName'])) ?
          $setDetails['parentName'] :
          civicrm_api3('tag', 'getvalue', [
            'id' => $setDetails['parentID'],
            'return' => 'name'
          ]);
        //Civi::log()->debug('buildForm', array('$setName' => $setName));
      }
      catch (CiviCRM_API3_Exception $e) {}

      if (in_array($setName, $webSets)) {
        $webViewOnly[] = $setDetails['parentID'];
        unset($form->_tagsetInfo["contact_taglistparentId_[{$setDetails['parentID']}]"]);

        //on contact edit form, set to read-only
        if ($formName == 'CRM_Contact_Form_Contact') {
          if ($form->elementExists("contact_taglist[{$setDetails['parentID']}]")) {
            $ele =& $form->getElement("contact_taglist[{$setDetails['parentID']}]");
            $ele->freeze();
          }
        }
        //for all other forms, remove the tagset elements
        else {
          if ($form->elementExists("contact_taglist[{$setDetails['parentID']}]")) {
            $form->removeElement("contact_taglist[{$setDetails['parentID']}]");
          }

          //for some reason tagset ID is added twice, so we need to cycle/remove twice
          if ($form->elementExists("contact_taglist[{$setDetails['parentID']}]")) {
            $form->removeElement("contact_taglist[{$setDetails['parentID']}]");
          }
        }
      }
    }
    $form->assign('webViewOnly', $webViewOnly);
    //CRM_Core_Error::debug_var('$webViewOnly', $webViewOnly);

    //10659 - leg positions can't create tag
    if ($form->elementExists('contact_taglist[292]')) {
      $legPosField =& $form->getElement('contact_taglist[292]');
      $legPosField->_attributes['data-create-links'] = false;
    }

    //11111
    if ($form->elementExists('tag')) {
      $issueCodes =& $form->getElement('tag');
      $issueCodes->_label = 'Issue Codes';
    }
  }

  //Construct some arrays and values to be passed to the tag tab
  if ($formName == 'CRM_Tag_Form_Tag') {
    $contactId = $form->getVar('_entityID');

    //Construct list of issue codes, comma-separated; work with the full list and subtract tagsets
    $contactIssueCode_item = array();
    $contactTags = CRM_Core_BAO_EntityTag::getTag($contactId);
    $tagsNotTagset = CRM_Core_BAO_Tag::getTagsNotInTagset();

    foreach ($tagsNotTagset as $key => $issueCode) {
      if (in_array($key, $contactTags)) {
        $contactIssueCode_item[] = $issueCode;
      }
    }
    sort($contactIssueCode_item);
    $contactIssueCode_list = stripslashes(implode(' &#8226; ', $contactIssueCode_item));

    //Construct list of Legislative Positions
    $legpositions = CRM_Core_BAO_EntityTag::getChildEntityTagDetails(292, $contactId);

    $bbcfg = get_bluebird_instance_config();
    if (isset($bbcfg['openleg.url.template'])) {
      $url_template = $bbcfg['openleg.url.template'];
    }
    else {
      $url_template = '/bill/{year}/{billno}';
    }

    if (!empty($legpositions)) {
      $legpositionsHTML = '<ul>';
      foreach ($legpositions as &$legposition) {
        $name = $legposition['name'];
        $bill_id = substr($name, 0, strcspn($name, ' :'));
        $id_parts = explode('-', $bill_id);  // [0]=billno, [1]=year
        $search = array('{billno}', '{year}');
        $bill_url = str_replace($search, $id_parts, $url_template);
        $bill_html = "<a href=\"$bill_url\" target=\"_blank\">$bill_url</a>";
        $legposition['description'] = $bill_html;

        if (!empty($legposition['description']) && $legposition['description'] != 'No description available.') {
          $legpositionsHTML .= "<li><strong>{$name}</strong> :: {$legposition['description']}</li>";
        }
      }
      $legpositionsHTML .= '</ul>';
    }

    //10658 rebuild leg positions entity ref using custom API and disabling create
    CRM_Core_Resources::singleton()->addVars('NYSS', array('contactId' => $contactId));
    CRM_Core_Resources::singleton()->addScriptFile('gov.nysenate.tags', 'js/form_tagset_legpos.js');

    //11072/11167 append list of issue codes and leg positions
    if (!empty($contactIssueCode_list)) {
      CRM_Core_Region::instance('page-header')->add(array(
        'markup' => "
          <div class='contactTagsList help'>
            <strong>Issue Codes: </strong>
            <span>{$contactIssueCode_list}</span>
          </div>
    	    <div class='clear'></div>
    	  ",
      ));
    }

    if (!empty($legpositions)) {
      CRM_Core_Region::instance('page-body')->add(array(
        'markup' => "
          <div class='contactTagsList help'>
            <strong>Legislative Position Details</strong>
            {$legpositionsHTML}
          </div>
    	    <div class='clear'></div>
    	  ",
      ));
    }

    //11072/11450 auto-expand issue code tree
    CRM_Core_Region::instance('form-bottom')->add(array(
      'jquery' => "
        var interval_id = setInterval(function(){
          if ($('li#j1_1').length != 0){
            clearInterval(interval_id)
            $('#tagtree').jstree('open_node', $('li#j1_1'))
          }
        }, 5);
      ",
    ));
  }

  //11334 (extension of 10658): rebuild leg positions entity ref using custom API and disabling create
  if ($formName === 'CRM_Contact_Form_Contact') {
    //DISABLED: handled in validateForm below
    //Civi::log()->debug('CRM_Contact_Form_Contact', array('form' => $form,));
    //Note: intentionally don't pass contact_id as js param as we don't want entity_tag
    //saved via AJAX -- only during form submission
    //CRM_Core_Resources::singleton()->addScriptFile('gov.nysenate.tags', 'js/form_tagset_legpos.js');
  }

  //11082 - force used_for value
  if ($formName == 'CRM_Tag_Form_Edit' && !$form->getVar('_isTagSet')) {
    if ($form->elementExists('used_for')) {
      $form->setDefaults(array(
        'used_for' => 'civicrm_contact,civicrm_activity,civicrm_case',
      ));
      $form->getElement('used_for')->freeze();
    }
  }
} //tags_civicrm_buildForm()

function tags_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  /*Civi::log()->debug('tags_civicrm_postProcess', array(
    'formName' => $formName,
    '$fields' => $fields,
    '$errors' => $errors,
  ));*/

  //11334 (extension of 10658): process leg positions from contact edit form
  //we need to take the submitted value, create the tag, and replace the
  //submitted value with the newly created tag id
  if (($formName == 'CRM_Contact_Form_Contact' && !empty($fields['contact_taglist'][292])) ||
    ($formName == 'CRM_Activity_Form_Activity' && !empty($fields['activity_taglist'][292])) ||
    ($formName == 'CRM_Case_Form_Case' && !empty($fields['case_taglist'][292]))
  ) {
    if (isset($fields['contact_taglist'])) {
      $legPosTagFld = 'contact_taglist';
      $recordType = 'Contact';
    }
    elseif (isset($fields['activity_taglist'])) {
      $legPosTagFld = 'activity_taglist';
      $recordType = 'Activity';
    }
    elseif (isset($fields['case_taglist'])) {
      $legPosTagFld = 'case_taglist';
      $recordType = 'Case';
    }

    $tags = array();
    foreach (explode(',', $fields[$legPosTagFld][292]) as $tag) {
      if (strpos($tag, ':::') !== false) {
        try {
          $tags[] = civicrm_api3('nyss_tags', 'savePosition', array(
            'value' => $tag,
          ));
        }
        catch (CiviCRM_API3_Exception $e) {}
      }
      else {
        $tags[] = $tag;
      }
    }

    $data = &$form->controller->container();
    $data['values'][$recordType][$legPosTagFld][292] = implode(',', $tags);
    //Civi::log()->debug('tags_civicrm_postProcess', array('$tags' => $tags, '$data' => $data));
  }
} //tags_civicrm_validateForm()

function tags_civicrm_pageRun(&$page) {
  //Civi::log()->debug('tags_civicrm_pageRun', array('$page' => $page));

  if (is_a($page, 'CRM_Tag_Page_Tag')) {
    //hide some tagsets
    $tagSets = $page->get_template_vars('tagsets');
    $remove = array('Positions', 'Website Issues', 'Website Committees', 'Website Bills', 'Website Petitions');
    foreach ($tagSets as $setID => $tagSet) {
      if (in_array($tagSet['name'], $remove)) {
        unset($tagSets[$setID]);
      }
    }
    $page->assign('tagsets', $tagSets);

    //load resources
    Civi::resources()->addScriptFile('gov.nysenate.tags', 'js/page_manage_tags.js');
    Civi::resources()->addStyleFile('gov.nysenate.tags', 'css/page_manage_tags.css');
  }
} //tags_civicrm_pageRun()

function tags_civicrm_alterEntityRefParams(&$params, $formName) {
  /*Civi::log()->debug('tags_civicrm_alterEntityRefParams', array(
    'params' => $params,
    'formName' => $formName,
  ));*/

  //use custom api for legislative positions; exclude search forms
  if ($params['entity'] == 'tag' &&
    !empty($params['api']['params']['parent_id']) &&
    $params['api']['params']['parent_id'] == 292 &&
    strpos($formName, 'Search') === FALSE &&
    $formName != 'CRM_Logging_Form_ProofingReport'
  ) {
    //Civi::log()->debug('tags_civicrm_alterEntityRefParams', array('params' => $params));
    $params['entity'] = 'nyss_tags';
    $params['create'] = FALSE;
    $params['search_field'] = 'name';
    $params['label_field'] = 'name';
  }
} //tags_civicrm_alterEntityRefParams()

function tags_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  /*Civi::log()->debug('tags_civicrm_alterAPIPermissions', [
    '$entity' => $entity,
    '$action' => $action,
    '$params' => $params,
    '$permissions' => $permissions,
  ]);*/

  //11459
  if ($entity == 'nyss_tags' && in_array($action, array('getlist', 'saveposition'))) {
    $params['check_permissions'] = false;
  }
} //tags_civicrm_alterAPIPermissions()
