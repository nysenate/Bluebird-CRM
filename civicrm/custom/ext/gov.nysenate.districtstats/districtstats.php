<?php

require_once 'districtstats.civix.php';
use CRM_Districtstats_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function districtstats_civicrm_config(&$config) {
  _districtstats_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function districtstats_civicrm_install() {
  _districtstats_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function districtstats_civicrm_enable() {
  _districtstats_civix_civicrm_enable();
}

function districtstats_civicrm_buildForm($formName, &$form) {
  //Civi::log()->debug('districtstats_civicrm_buildForm', ['formName' => $formName, 'form' => $form]);

  if ($formName == 'CRM_Contact_Form_Search_Advanced' &&
    CRM_Utils_Request::retrieve('context', 'String') == 'districtstats'
  ) {
    //Civi::log()->debug('districtstats_civicrm_buildForm', ['$_GET' => $_GET]);

    $defaults = [];
    $skip = ['reset', 'force', 'context', 'q', 'qfKey'];
    foreach ($_GET as $f => $v) {
      if (!in_array($f, $skip)) {
        $defaults[$f] = $v;
      }
    }

    //Civi::log()->debug('districtstats_civicrm_buildForm', ['$defaults' => $defaults]);
    if (!empty($defaults)) {
      //$form->setDefaults($defaults);
    }
  }
}
