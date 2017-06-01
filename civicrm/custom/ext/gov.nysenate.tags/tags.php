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
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function tags_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
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
function tags_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _tags_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Functions below this ship commented out. Uncomment as required.
 *

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function tags_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function tags_civicrm_navigationMenu(&$menu) {
  _tags_civix_insert_navigation_menu($menu, NULL, array(
    'label' => ts('The Page', array('domain' => 'gov.nysenate.tags')),
    'name' => 'the_page',
    'url' => 'civicrm/the-page',
    'permission' => 'access CiviReport,access CiviContribute',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _tags_civix_navigationMenu($menu);
} // */

function tags_civicrm_merge( $type, &$sqls, $fromId, $toId, $tables ) {
  //insert civicrm_log record for every contact, case or activity affected by a tag merge.
  if ($type == 'sqls' &&
    in_array('civicrm_tag', $tables) &&
    $_GET['q'] == 'civicrm/ajax/mergeTags'
  ) {
    $session = CRM_Core_Session::singleton( );
    $userID = $session->get( 'userID' );

    $sql = "
      INSERT INTO civicrm_log ( entity_table, entity_id, data, modified_id, modified_date )
      SELECT et.entity_table, et.entity_id, CONCAT('Merged tag: ', tag.name, ' (', tag.id, ' with ', {$toId}, ')'), {$userID}, NOW()
      FROM civicrm_entity_tag et
      INNER JOIN civicrm_tag tag
        ON et.tag_id = tag.id
      WHERE tag_id = %2
    ";
    array_unshift( $sqls, $sql );
  }
} //merge

function tags_civicrm_buildForm($formName, &$form) {
  /*Civi::log()->debug('buildForm', array(
    'formName' => $formName,
    'form' => $form,
    //'$form->_elementIndex' => $form->_elementIndex,
    //'$form->_tagsetInfo' => $form->_tagsetInfo,
  ));*/

  if ($formName == 'CRM_Tag_Form_Tag' ||
    $formName == 'CRM_Contact_Form_Task_AddToTag' ||
    $formName == 'CRM_Contact_Form_Contact'
  ) {
    $webSets = array(
      'Website Bills',
      'Website Committees',
      'Website Issues',
      'Website Petitions',
    );
    $webViewOnly = array();
    foreach ($form->_tagsetInfo as $setId => $setDetails) {
      $setName = (!empty($setDetails['parentName'])) ?
        $setDetails['parentName'] :
        civicrm_api3('tag', 'getvalue', array('id' => $setDetails['parentID'], 'return' => 'name'));
      //Civi::log()->debug('buildForm', array('$setName' => $setName));

      if (in_array($setName, $webSets)) {
        $webViewOnly[] = $setDetails['parentID'];
        unset($form->_tagsetInfo["contact_taglistparentId_[{$setDetails['parentID']}]"]);

        //remove the form elements
        if ($form->elementExists("contact_taglist[{$setDetails['parentID']}]")) {
          $form->removeElement("contact_taglist[{$setDetails['parentID']}]");
        }

        //for some reason tagset ID is added twice, so we need to cycle/remove twice
        if ($form->elementExists("contact_taglist[{$setDetails['parentID']}]")) {
          $form->removeElement("contact_taglist[{$setDetails['parentID']}]");
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
    CRM_Core_Resources::singleton()->addScript("
      CRM.$(function($) {
        $('#contact_taglist_292').crmEntityRef('destroy');
        $('#contact_taglist_292').crmEntityRef({
          entity: 'nyss_tags',
          multiple: true,
          create: false,
          api: {
            params: {
              parent_id: 292
            }
          },
          class: 'crm-contact-tagset'
        });
        
        //when a leg position is selected, we may need to add it to the tag table
        $('#contact_taglist_292').on('select2-selecting', function(e) {
          CRM.api3('nyss_tags', 'savePosition', {value:e.val, contactId:{$contactId}}, false);
        });
      });
    ");

    //11072 append list of issue codes and leg positions
    if (!empty($contactIssueCode_list)) {
      CRM_Core_Region::instance('page-body')->add(array(
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

    //11072 auto-open issue code branch
    CRM_Core_Region::instance('page-body')->add(array(
      'jquery' => "$('li#tag_291').removeClass('jstree-closed').addClass('jstree-open').addClass('tuna');",
    ));

  }

  //11082 - force used_for value
  if ($formName == 'CRM_Tag_Form_Edit' && !$form->getVar('_isTagSet')) {
    if ($form->elementExists('used_for')) {
      $form->setDefaults(array(
        'used_for' => 'civicrm_contact',
      ));
      $form->getElement('used_for')->freeze();
    }
  }
}

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
  }
}
