<?php

require_once 'tokens.civix.php';
use CRM_Tokens_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function tokens_civicrm_config(&$config) {
  _tokens_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function tokens_civicrm_install() {
  _tokens_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function tokens_civicrm_enable() {
  _tokens_civix_civicrm_enable();
}

function tokens_civicrm_tokens(&$tokens) {
  $tokens['nyss'] = [
    'nyss.base_url' => 'Base URL',
    'nyss.senator_formal' => 'Senator Formal Name',
    'nyss.senator_email' => 'Senator Email',
  ];
}

function tokens_civicrm_tokenValues(&$values, $cids, $job, $tokens, $context) {
  /*Civi::log()->debug(__FUNCTION__, [
    'values' => $values,
    'cids' => $cids,
    'tokens' => $tokens,
  ]);*/

  if (!empty($tokens['nyss'])) {
    $bbconfig = get_bluebird_instance_config();
    $bb = [
      'base_url' => "{$bbconfig['db.basename']}.{$bbconfig['base.domain']}",
      'senator_formal' => CRM_Utils_Array::value('senator.name.formal', $bbconfig),
      'senator_email' => CRM_Utils_Array::value('senator.email', $bbconfig),
    ];

    foreach ($cids as $cid) {
      $values[$cid] = [
        'nyss.base_url' => 'http://'.$bb['base_url'],
        'nyss.senator_formal' => $bb['senator_formal'],
        'nyss.senator_email' => $bb['senator_email'],
      ];
    }
  }
}
