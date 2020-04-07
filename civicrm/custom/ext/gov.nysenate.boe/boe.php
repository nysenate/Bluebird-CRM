<?php

require_once 'boe.civix.php';
use CRM_Boe_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function boe_civicrm_config(&$config) {
  _boe_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function boe_civicrm_xmlMenu(&$files) {
  _boe_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function boe_civicrm_install() {
  _boe_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function boe_civicrm_postInstall() {
  _boe_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function boe_civicrm_uninstall() {
  _boe_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function boe_civicrm_enable() {
  _boe_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function boe_civicrm_disable() {
  _boe_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function boe_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _boe_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function boe_civicrm_managed(&$entities) {
  _boe_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function boe_civicrm_caseTypes(&$caseTypes) {
  _boe_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function boe_civicrm_angularModules(&$angularModules) {
  _boe_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function boe_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _boe_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function boe_civicrm_entityTypes(&$entityTypes) {
  _boe_civix_civicrm_entityTypes($entityTypes);
}

function boe_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Contact_Form_Contact') {
    //don't allow editing for BOE location type; don't allow setting 'Board of Election' loc type
    CRM_Core_Resources::singleton()->addScriptFile(E::LONG_NAME, 'js/ContactFormAddressBOE.js');

    $blocks = [
      'Email' => 'email',
      'Phone' => 'phone',
      'IM' => 'im',
      'OpenID' => 'openid',
      'Address' => 'address'
    ];

    $values = $form->_values;
    if (!empty($_POST)) $values = $_POST;
    Civi::log()->debug(__FUNCTION__, ['$values' => $values]);

    $locationTypes = CRM_Core_PseudoConstant::get('CRM_Core_DAO_Address', 'location_type_id');
    $boardOfElectionId = array_search('BOE', $locationTypes);
    //Civi::log()->debug(__FUNCTION__, ['$boardOfElectionId' => $boardOfElectionId]);

    $addressOptions = $form->get('addressOptions');
    if (!isset($addressOptions)) {
      $addressOptions = CRM_Core_BAO_Setting::valueOptions(CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
        'address_options', TRUE, NULL, TRUE
      );
      $form->set('addressOptions', $addressOptions);
    }
    //Civi::log()->debug(__FUNCTION__, ['addressOptions' => $addressOptions]);

    foreach ($blocks as $label => $name) {
      foreach ($values[$name] as $blockId => $block) {
        //process BOE locking, etc.
        if ($boardOfElectionId == CRM_Utils_Array::value('location_type_id', $block)) {
          if ($name == 'address') {
            CRM_Core_Resources::singleton()->addVars('NYSS', [
              'boeAddressBlockId' => $blockId,
            ]);

            foreach ($addressOptions as $key => $value) {
              //empty means the option is not enabled and can be skipped
              if (empty($value)) {
                continue;
              }

              if (in_array($key, ['country', 'state_province'])) {
                $key .= '_id';

                //we might register duplicate elements
                if ($key == 'state_province_id') {
                  if (array_key_exists("address[$blockId][$key]", $form->_duplicateIndex)) {
                    $duplicateIndexes = $form->_duplicateIndex["address[$blockId][$key]"];
                    foreach ($duplicateIndexes as $index) {
                      $element = $form->_elements[$index];
                      $element->freeze();
                    }
                  }
                }
              }

              //process address data element.
              _boe_lockElement($form, "address[$blockId][$key]");
            }

            //hide 'Use Shared Address' for BOE
            if ($form->elementExists("address[{$blockId}][use_shared_address]")) {
              //$form->removeElement("address[{$blockId}][use_shared_address]");
              //$form->removeElement("address[{$blockId}][master_contact_id]");
            }
          }
          else {
            $dataElementName = "{$name}[{$blockId}][{$name}]";

            //special field handling
            if ($name == 'im') {
              $dataElementName = "{$name}[{$blockId}][name]";
              _boe_lockElement($form, "{$name}[{$blockId}][provider_id]");
            }
            elseif ($name == 'phone') {
              _boe_lockElement($form, "{$name}[{$blockId}][phone_type_id]");
            }

            //process data element.
            _boe_lockElement($form, $dataElementName);
          }

          //lock location type - this hides street address and removes share fields for some weird reason
          //_boe_lockElement($form, "{$name}[{$blockId}][location_type_id]");
        }
        else {
          //if not BOE, limit list options
          _boe_unsetLocTypeOptions($form, $name, $blockId);
        }
      }

      //if block is empty, run unsetLocTypeOptions for blockId 1 (new row; no existing values)
      if (empty($values[$name])) {
        _boe_unsetLocTypeOptions($form, $name, 1);
      }
    }
  }

  $inlineBlockBOE = [
    'CRM_Contact_Form_Inline_Email',
    'CRM_Contact_Form_Inline_Phone',
    'CRM_Contact_Form_Inline_Address',
  ];

  if (in_array($formName, $inlineBlockBOE)) {
    $locationTypes = CRM_Core_PseudoConstant::get('CRM_Core_DAO_Address', 'location_type_id');
    $boardOfElectionId = array_search('BOE', $locationTypes);

    //determine name and object
    switch ($formName) {
      case 'CRM_Contact_Form_Inline_Email':
        $name    = 'email';
        $objName = '_emails';
        break;
      case 'CRM_Contact_Form_Inline_Phone':
        $name    = 'phone';
        $objName = '_phones';
        break;
      case 'CRM_Contact_Form_Inline_Address':
        $name    = 'address';
        $objName = '_addresses';
        break;
    }

    $excludeFlds = [
      'is_primary',
    ];

    $removeFlds = [
      'master_id',
      'use_shared_address',
      'master_contact_id',
      'update_current_employer',
    ];

    $b = 0;
    foreach ($form->_defaultValues[$name] as $blockId => $block) {
      //CRM_Core_Error::debug_var('block', $block);
      //CRM_Core_Error::debug_var('elements', $form->_elementIndex);
      //CRM_Core_Error::debug_var('form->_defaultValues', $form->_defaultValues);

      if ($block['location_type_id'] == $boardOfElectionId) {
        $elementLoc = "{$name}[$blockId][location_type_id]";
        $elementName = "{$name}[$blockId]";

        if (!$form->elementExists($elementLoc)) {
          continue;
        }

        CRM_Core_Resources::singleton()->addScriptFile(E::LONG_NAME, 'js/InlineAddressBOE.js');

        //get parsed values from DB and set to js vars
        if ($name == 'address' && !empty($addrId = $form->_defaultValues['address'][$blockId]['id'])) {
          try {
            $addrVals = civicrm_api3('Address', 'getsingle', [
              'id' => $addrId,
              'return' => [
                'street_number',
                'street_name',
                'street_unit',
              ],
            ]);
            //CRM_Core_Error::debug_var('$addrVals', $addrVals);

            CRM_Core_Resources::singleton()->addVars('NYSS', [
              "address_{$blockId}_street_number" => CRM_Utils_Array::value('street_number', $addrVals),
              "address_{$blockId}_street_name" => CRM_Utils_Array::value('street_name', $addrVals),
              "address_{$blockId}_street_unit" => CRM_Utils_Array::value('street_unit', $addrVals),
            ]);
          }
          catch (CiviCRM_API3_Exception $e) {}
        }

        foreach ($form->_elementIndex as $ele => $dontcare) {
          if (strpos($ele, $elementName) === 0) {
            $fld = trim(str_replace($elementName, '', $ele),'[]');
            //CRM_Core_Error::debug_var('$fld', $fld);

            if (!in_array($fld, $excludeFlds) &&
              !in_array($fld, $removeFlds) &&
              strpos($fld, 'custom_') === FALSE
            ) {
              $element = $form->getElement($ele);
              $element->freeze();
            }
            elseif (in_array($fld, $removeFlds)) {
              $form->removeElement($ele);
            }
          }
        }
      }
      $b++;
    }
    $b = ($b < 5) ? 5 : $b;

    //suppress special location type options
    for ($i = 1; $i <= $b; $i++) {
      $elementLoc = "{$name}[$i][location_type_id]";
      if (!$form->elementExists($elementLoc)) {
        continue;
      }
      $eleLoc =& $form->getElement($elementLoc);
      $specialTypes = ['BOE', 'Billing', 'NCOA'];
      foreach ($eleLoc->_options as $index => $options) {
        if (in_array($options['text'], $specialTypes) &&
          !$eleLoc->_flagFrozen
        ) {
          unset($eleLoc->_options[$index]);
        }
      }
      //reset array keys or we have issues with the display
      $eleLoc->_options = array_values($eleLoc->_options);
    }
    //CRM_Core_Error::debug_var('form',$form);
  }
}

/**
 * process the elements on the form
 *
 * @return void
 * @access public
 */
function _boe_lockElement(&$form, $elementName) {
  //Civi::log()->debug(__FUNCTION__, ['$elementName' => $elementName]);

  if (!$form->elementExists($elementName)) {
    return;
  }

  $element = $form->getElement($elementName);
  $element->freeze();
}

function _boe_unsetLocTypeOptions(&$form, $name, $blockId) {
  Civi::log()->debug(__FUNCTION__, ['$name' => $name]);
  Civi::log()->debug(__FUNCTION__, ['$blockId' => $blockId]);

  $newOptions = [];
  $elementName = "{$name}[{$blockId}][location_type_id]";

  if (!$form->elementExists($elementName)) {
    return;
  }

  $element =& $form->getElement($elementName);
  //Civi::log()->debug(__FUNCTION__, ['element' => $element]);

  foreach ($element->_options as $index => $options) {
    if (in_array($options['text'], ['BOE', 'Billing', 'NCOA'])) {
      unset($element->_options[$index]);
    }
  }
}
