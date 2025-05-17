<?php

require_once 'contactlayout.civix.php';
use CRM_Contactlayout_ExtensionUtil as E;
use CRM_Contactlayout_Helper_ProfileRelatedContact as ProfileRelatedContact;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function contactlayout_civicrm_config(&$config) {
  _contactlayout_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function contactlayout_civicrm_install() {
  _contactlayout_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function contactlayout_civicrm_enable() {
  _contactlayout_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_pageRun().
 *
 * Add layout block data to the contact summary screen.
 */
function contactlayout_civicrm_pageRun(&$page) {
  if (get_class($page) === 'CRM_Contact_Page_View_Summary') {
    $contactID = $page->getVar('_contactId');
    if ($contactID) {
      try {
        $defaultTabs = \Civi\Api4\Setting::get(FALSE)
          ->addSelect('contactlayout_default_tabs')
          ->execute()
          ->first()['value'] ?? NULL;
      }
      catch (CRM_Core_Exception $e) {
        Civi::log()->warning(E::ts("Exception retrieving default tabs setting: %1", ['1' => $e->getMessage()]));
        $defaultTabs = NULL;
      }
      $layout = CRM_Contactlayout_BAO_ContactLayout::getLayout($contactID);
      if ($layout) {
        $profileBlocks = [];
        foreach ($layout['blocks'] as &$row) {
          foreach ($row as &$column) {
            foreach ($column as &$block) {
              if (empty($block['profile_id'])) {
                continue;
              }

              $profileContact = $contactID;
              $block['rel_cid'] = NULL;
              $block['rel_is_missing'] = FALSE;

              if (!empty($block['related_rel'])) {
                $relatedContact = ProfileRelatedContact::get($contactID, $block['related_rel']);
                $profileContact = $relatedContact;
                $block['rel_cid'] = $relatedContact;
                $block['rel_is_missing'] = $relatedContact === NULL;
              }

              // Include the block information only when there is a contact profile to display.
              // This can be empty when there are no results for a particular relationship block.
              if (!empty($profileContact)) {
                $profileBlocks[$block['profile_id']] = CRM_Contactlayout_Page_Inline_ProfileBlock::getProfileBlock(
                  $block['profile_id'],
                  $profileContact
                );
              }
            }
          }
        }
        $page->assign('layoutBlocks', $layout['blocks']);
        $page->assign('profileBlocks', $profileBlocks);
        // Setting these variables will make Summary.tpl replace the contents with SummaryHook.tpl which we override.
        $page->assign('hookContent', 1);
        $page->assign('hookContentPlacement', CRM_Utils_Hook::SUMMARY_REPLACE);
        Civi::resources()
          ->addStyleFile('org.civicrm.contactlayout', 'css/contact-summary-layout.css');
      }
      if (!empty($layout['tabs']) || $defaultTabs) {
        $tabs = array_column($page->get_template_vars('allTabs'), NULL, 'id');
        foreach ($layout['tabs'] ?? $defaultTabs as $weight => $tab) {
          $id = $tab['id'];
          if (empty($tab['is_active'])) {
            unset($tabs[$id]);
          }
          elseif (isset($tabs[$id])) {
            $tabs[$id]['weight'] = $weight;
            $tabs[$id]['title'] = $tab['title'] ?? $tabs[$id]['title'];
            $tabs[$id]['icon'] = $tab['icon'] ?? $tabs[$id]['icon'] ?? NULL;
          }
        }
        uasort($tabs, ['CRM_Utils_Sort', 'cmpFunc']);
        $page->assign('allTabs', $tabs);
      }
      if (CRM_Core_Permission::check('administer CiviCRM')) {
        CRM_Core_Region::instance('contact-actions-ribbon')
          ->add([
            'markup' => '<li class="crm-contact-summary-edit-layout">
              <a class="crm-hover-button" title="' . htmlspecialchars(E::ts('Edit Layout')) . '" href="' . CRM_Utils_System::url('civicrm/admin/contactlayout') . '">
                <i class="crm-i fa-edit"></i> ' . htmlspecialchars(E::ts('Layout: %1', [1 => $layout['label'] ?? E::ts('System Default')])) .
            '</a>
            </li>',
          ]);
      }
    }
  }
}

/**
 * Implements hook_civicrm_postProcess().
 *
 * Refreshes profile blocks when related info is updated.
 */
function contactlayout_civicrm_postProcess($formName, &$form) {
  if (is_a($form, 'CRM_Contact_Form_Inline')) {
    $blocks = CRM_Contactlayout_BAO_ContactLayout::getAllBlocks();
    $selector = NULL;
    if ($formName == 'CRM_Contact_Form_Inline_ContactName') {
      $selector = '#crm-contactname-content';
    }
    else {
      $tpl = str_replace('Form/Inline', 'Page/Inline', $form->getTemplateFileName());
      foreach ($blocks as $group) {
        foreach ($group['blocks'] as $block) {
          if (
            $block['tpl_file'] == $tpl ||
            ($formName == 'CRM_Contact_Form_Inline_CustomData' && $form->_groupID == ($block['custom_group_id'] ?? NULL)) ||
            $block['name'] == 'Address' && $formName == 'CRM_Contact_Form_Inline_Address'
          ) {
            $selector = $block['selector'] ?? NULL;
            break 2;
          }
        }
      }
    }
    if ($selector) {
      foreach ($blocks['profile']['blocks'] as $profileBlock) {
        if (in_array($selector, $profileBlock['refresh'])) {
          $form->ajaxResponse['reloadBlocks'][] = $profileBlock['selector'];
        }
      }
    }
  }
}
