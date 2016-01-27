<?php

/* Copyright iATS Payments (c) 2014
 * Author: Alan Dixon
 *
 * This file is a part of CiviCRM published extension.
 *
 * This extension is free software; you can copy, modify, and distribute it
 * under the terms of the GNU Affero General Public License
 * Version 3, 19 November 2007.
 *
 * It is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License with this program; if not, see http://www.gnu.org/licenses/
 */

require_once 'iats.civix.php';

/**
 * Implementation of hook_civicrm_config().
 */
function iats_civicrm_config(&$config) {
  _iats_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function iats_civicrm_xmlMenu(&$files) {
  _iats_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function iats_civicrm_install() {
  if (!class_exists('SoapClient')) {
    $session = CRM_Core_Session::singleton();
    $session->setStatus(ts('The PHP SOAP extension is not installed on this server, but is required for this extension'), ts('iATS Payments Installation'), 'error');
  }
  return _iats_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function iats_civicrm_uninstall() {
  return _iats_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function iats_civicrm_enable() {
  if (!class_exists('SoapClient')) {
    $session = CRM_Core_Session::singleton();
    $session->setStatus(ts('The PHP SOAP extension is not installed on this server, but is required for this extension'), ts('iATS Payments Installation'), 'error');
  }
  return _iats_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function iats_civicrm_disable() {
  return _iats_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function iats_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _iats_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function iats_civicrm_managed(&$entities) {
  $entities[] = array(
    'module' => 'com.iatspayments.civicrm',
    'name' => 'iATS Payments',
    'entity' => 'PaymentProcessorType',
    'params' => array(
      'version' => 3,
      'name' => 'iATS Payments Credit Card',
      'title' => 'iATS Payments Credit Card',
      'description' => 'iATS credit card payment processor using the web services interface.',
      'class_name' => 'Payment_iATSService',
      'billing_mode' => 'form',
      'user_name_label' => 'Agent Code',
      'password_label' => 'Password',
      'url_site_default' => 'https://www.iatspayments.com/NetGate/ProcessLink.asmx?WSDL',
      'url_recur_default' => 'https://www.iatspayments.com/NetGate/ProcessLink.asmx?WSDL',
      'url_site_test_default' => 'https://www.iatspayments.com/NetGate/ProcessLink.asmx?WSDL',
      'url_recur_test_default' => 'https://www.iatspayments.com/NetGate/ProcessLink.asmx?WSDL',
      'is_recur' => 1,
      'payment_type' => 1,
    ),
  );
  $entities[] = array(
    'module' => 'com.iatspayments.civicrm',
    'name' => 'iATS Payments ACH/EFT',
    'entity' => 'PaymentProcessorType',
    'params' => array(
      'version' => 3,
      'name' => 'iATS Payments ACH/EFT',
      'title' => 'iATS Payments ACH/EFT',
      'description' => 'iATS ACH/EFT payment processor using the web services interface.',
      'class_name' => 'Payment_iATSServiceACHEFT',
      'billing_mode' => 'form',
      'user_name_label' => 'Agent Code',
      'password_label' => 'Password',
      'url_site_default' => 'https://www.iatspayments.com/NetGate/ProcessLink.asmx?WSDL',
      'url_recur_default' => 'https://www.iatspayments.com/NetGate/ProcessLink.asmx?WSDL',
      'url_site_test_default' => 'https://www.iatspayments.com/NetGate/ProcessLink.asmx?WSDL',
      'url_recur_test_default' => 'https://www.iatspayments.com/NetGate/ProcessLink.asmx?WSDL',
      'is_recur' => 1,
      'payment_type' => 2,
    ),
  );
  $entities[] = array(
    'module' => 'com.iatspayments.civicrm',
    'name' => 'iATS Payments SWIPE',
    'entity' => 'PaymentProcessorType',
    'params' => array(
      'version' => 3,
      'name' => 'iATS Payments SWIPE',
      'title' => 'iATS Payments SWIPE',
      'description' => 'iATS credit card payment processor using the encrypted USB IDTECH card reader.',
      'class_name' => 'Payment_iATSServiceSWIPE',
      'billing_mode' => 'form',
      'user_name_label' => 'Agent Code',
      'password_label' => 'Password',
      'url_site_default' => 'https://www.iatspayments.com/NetGate/ProcessLink.asmx?WSDL',
      'url_recur_default' => 'https://www.iatspayments.com/NetGate/ProcessLink.asmx?WSDL',
      'url_site_test_default' => 'https://www.iatspayments.com/NetGate/ProcessLink.asmx?WSDL',
      'url_recur_test_default' => 'https://www.iatspayments.com/NetGate/ProcessLink.asmx?WSDL',
      'is_recur' => 1,
      'payment_type' => 1,
    ),
  );
  $entities[] = array(
    'module' => 'com.iatspayments.civicrm',
    'name' => 'iATS Payments UK Direct Debit',
    'entity' => 'PaymentProcessorType',
    'params' => array(
      'version' => 3,
      'name' => 'iATS Payments UK Direct Debit',
      'title' => 'iATS Payments UK Direct Debit',
      'description' => 'iATS UK Direct Debit payment processor using the web services interface.',
      'class_name' => 'Payment_iATSServiceUKDD',
      'billing_mode' => 'form',
      'user_name_label' => 'Agent Code',
      'password_label' => 'Password',
      'signature_label' => 'Service User Number',
      'url_site_default' => 'https://www.uk.iatspayments.com/NetGate/ProcessLink.asmx?WSDL',
      'url_recur_default' => 'https://www.uk.iatspayments.com/NetGate/ProcessLink.asmx?WSDL',
      'url_site_test_default' => 'https://www.uk.iatspayments.com/NetGate/ProcessLink.asmx?WSDL',
      'url_recur_test_default' => 'https://www.uk.iatspayments.com/NetGate/ProcessLink.asmx?WSDL',
      'is_recur' => 1,
      'payment_type' => 2,
    ),
  );
  return _iats_civix_civicrm_managed($entities);
}


/**
 * Utility function to get domain info
 *
 * Get values from the civicrm_domain table
 */
function _iats_civicrm_domain_info($key) {
  static $domain;
  if (empty($domain)) {
    $domain = civicrm_api('Domain', 'getsingle', array('version' => 3, 'current_domain' => TRUE));
  }
  switch($key) {
    case 'version':
      return explode('.',$domain['version']);
    default:
      if (!empty($domain[$key])) {
        return $domain[$key];
      }
      $config_backend = unserialize($domain['config_backend']);
      return $config_backend[$key];
  }
}

/* START utility functions to allow this extension to work with different civicrm version */

/**
 * Get the name of the next scheduled contribution date field, (not necessary since 4.4)
 */
function _iats_civicrm_nscd_fid() {
  $version = CRM_Utils_System::version();
  return (version_compare($version, '4.4') < 0) ? 'next_sched_contribution' : 'next_sched_contribution_date';
}

/**
 * Set js config values, version dependent. Access is also version dependent.
 */
function _iats_civicrm_varset($vars) {
  $version = CRM_Utils_System::version();
  if (version_compare($version, '4.5') < 0) { /// support 4.4!
    CRM_Core_Resources::singleton()->addSetting('iatspayments', $vars);
  }
  else {
    CRM_Core_Resources::singleton()->addVars('iatspayments', $vars);
  }
}

/* END functions to allow this extension to work with different civicrm version */

/**
 * Utility to get the next available menu key
 */
function _iats_getMenuKeyMax($menuArray) {
  $max = array(max(array_keys($menuArray)));
  foreach ($menuArray as $v) {
    if (!empty($v['child'])) {
      $max[] = _iats_getMenuKeyMax($v['child']);
    }
  }
  return max($max);
}

function iats_civicrm_navigationMenu(&$navMenu) {
  $pages = array(
    'admin_page' => array(
      'label'      => 'iATS Payments Admin',
      'name'       => 'iATS Payments Admin',
      'url'        => 'civicrm/iATSAdmin',
      'parent' => array('Contributions'),
      'permission' => 'access CiviContribute,administer CiviCRM',
      'operator'   => 'AND',
      'separator'  => NULL,
      'active'     => 1
    ),
    'settings_page' => array(
      'label'      => 'iATS Payments Settings',
      'name'       => 'iATS Payments Settings',
      'url'        => 'civicrm/admin/contribute/iatssettings',
      'parent'    => array('Administer','CiviContribute'),
      'permission' => 'access CiviContribute,administer CiviCRM',
      'operator'   => 'AND',
      'separator'  => NULL,
      'active'     => 1
    ),
  );
  foreach($pages as $item) {
    // Check that our item doesn't already exist
    $menu_item_search = array('url' => $item['url']);
    $menu_items = array();
    CRM_Core_BAO_Navigation::retrieve($menu_item_search, $menu_items);
    if (empty($menu_items)) {
      $path = implode('/',$item['parent']);
      unset($item['parent']);
      _iats_civix_insert_navigation_menu($navMenu, $path, $item);
    }
  }
}

/**
 * hook_civicrm_buildForm.
 * Do a Drupal 7 style thing so we can write smaller functions
 */
function iats_civicrm_buildForm($formName, &$form) {
  // but start by grouping a few forms together for nicer code
  switch ($formName) {
    case 'CRM_Event_Form_Participant':
    case 'CRM_Member_Form_Membership':
    case 'CRM_Contribute_Form_Contribution':
      // override normal convention, deal with all these backend credit card contribution forms the same way
      $fname = 'iats_civicrm_buildForm_CreditCard_Backend';
      break;

    case 'CRM_Contribute_Form_Contribution_Main':
    case 'CRM_Event_Form_Registration_Register':
      // override normal convention, deal with all these front-end contribution forms the same way
      $fname = 'iats_civicrm_buildForm_Contribution_Frontend';
      break;

    case 'CRM_Contribute_Form_Contribution_Confirm':
      // on the confirmation form, we know the processor, so only do processor specific customizations
      $fname = 'iats_civicrm_buildForm_Contribution_Confirm_'.$form->_paymentProcessor['class_name'];
      break;

    case 'CRM_Contribute_Form_Contribution_ThankYou':
      // on the confirmation form, we know the processor, so only do processor specific customizations
      $fname = 'iats_civicrm_buildForm_Contribution_ThankYou_'.$form->_paymentProcessor['class_name'];
      break;

    default:
      $fname = 'iats_civicrm_buildForm_'.$formName;
      break;
  }
  if (function_exists($fname)) {
    $fname($form);
  }
  // else echo $fname;
}

function iats_civicrm_pageRun(&$page) {
  $fname = 'iats_civicrm_pageRun_'.$page->getVar('_name');
  if (function_exists($fname)) {
    $fname($page);
  }
}

function iats_civicrm_pageRun_CRM_Contact_Page_View_Summary(&$page) {
  // because of AJAX loading, I need to load my backend swipe js here
  $swipe = iats_civicrm_processors(NULL,'SWIPE',array('is_default' => 1));
  if (count($swipe) > 0) {
    CRM_Core_Resources::singleton()->addScriptFile('com.iatspayments.civicrm', 'js/swipe.js',10,'html-header');
  }
}

/**
 * hook_civicrm_merge
 * Deal with contact merges - our custom iats customer code table contains contact id's as a check, it might need to be updated
 */
function iats_civicrm_merge($type, &$data, $mainId = NULL, $otherId = NULL, $tables = NULL) {
  if ('cidRefs' == $type) {
    $data['civicrm_iats_customer_codes'] = array('cid');
    $data['civicrm_iats_verify'] = array('cid');
  }
}


/**
 * hook_civicrm_pre
 *
 * Handle special cases of creating contribution (regular and recurring) records when using IATS Payments
 *
 * 1. CiviCRM assumes all recurring contributions need to be confirmed using the IPN mechanism. This is not true for iATS recurring contributions.
 * So when creating a contribution that is part of a recurring series, test for status = 2, and set to status = 1 instead.
 * Do this only for the initial contribution record.
 * The (subsequent) recurring contributions' status id is set explicitly in the job that creates it, this modification breaks that process.
 *
 * 2. For ACH/EFT, we also have the opposite problem - all contributions will need to verified by iATS and only later set to status success or
 * failed via the acheft verify job. We also want to modify the payment instrument from CC to ACH/EFT
 *
 * TODO: update this code with constants for the various id values of 1 and 2.
 * TODO: CiviCRM should have nicer ways to handle this.
 */
function iats_civicrm_pre($op, $objectName, $objectId, &$params) {
  // since this function gets called a lot, quickly determine if I care about the record being created
  if (('create' == $op) && ('Contribution' == $objectName || 'ContributionRecur' == $objectName) && !empty($params['contribution_status_id'])) {
    // watchdog('iats_civicrm','hook_civicrm_pre for Contribution <pre>@params</pre>',array('@params' => print_r($params));
    // figure out the payment processor id, not nice
    $version = CRM_Utils_System::version();
    $payment_processor_id = ('ContributionRecur' == $objectName) ? $params['payment_processor_id'] :
                              (!empty($params['payment_processor']) ? $params['payment_processor'] :
                                (!empty($params['contribution_recur_id']) ? _iats_civicrm_get_payment_processor_id($params['contribution_recur_id']) :
                                 0)
                              );
    if ($type = _iats_civicrm_is_iats($payment_processor_id)) {
      switch ($type.$objectName) {
        case 'iATSServiceContribution': // cc contribution, test if it's been set to status 2 on a recurring contribution
        case 'iATSServiceSWIPEContribution':
          // for civi version before 4.6.6, we had to force the status to 1
          if ((2 == $params['contribution_status_id'])
            && !empty($params['contribution_recur_id'])
            && (version_compare($version, '4.6.6') < 0)
          ) {
            // but only for the first one 
            $count = civicrm_api('Contribution', 'getcount', array('version' => 3, 'contribution_recur_id' => $params['contribution_recur_id']));
            if (
              (is_array($count) && empty($count['result']))
              || empty($count)
            ) { 
              // watchdog('iats_civicrm','hook_civicrm_pre updating status_id for objectName @id, count <pre>!count</pre>, params <pre>!params</pre>, ',array('@id' => $objectName, '!count' => print_r($count,TRUE),'!params' => print_r($params,TRUE)));
              $params['contribution_status_id'] = 1;
            }
          }
          break;
        case 'iATSServiceContributionRecur': // cc/swipe/ACHEFT recurring contribution record
        case 'iATSServiceSWIPEContributionRecur':
        case 'iATSServiceACHEFTContributionRecur':
          // the next scheduled contribution date field name is civicrm version dependent
          $field_name = _iats_civicrm_nscd_fid();
          // when creating a recurring contribution record via a civicrm contribution form
          // we've already taken the first payment, so calculate the next one (core assumes the intial contribution is pending)
          // we set this to 'in-progress' even for ACH/EFT if the first one hasn't been verified, because we still want to be attempting later ones
          // this condition helps avoid mangling records being imported from a csv file
          if (5 != $params['contribution_status_id'] && empty($params[$field_name])) {
            $params['contribution_status_id'] = 5;
            $params['trxn_id'] = NULL;
            $next = strtotime('+'.$params['frequency_interval'].' '.$params['frequency_unit']);
            $params[$field_name] = date('YmdHis',$next);
          }
          if ($type == 'iATSServiceACHEFT') { // fix the payment type for ACH/EFT
            $params['payment_instrument_id'] = 2;
          } 
          break;
        case 'iATSServiceACHEFTContribution': 
          // ach/eft contribution: update the payment instrument 
          $params['payment_instrument_id'] = 2;
          // and push the status to 2 if civicrm thinks it's 1, i.e. for one-time contributions
          // in other words, never create ach/eft contributions as complete, always push back to pending and verify
          if ($params['contribution_status_id'] == 1) {
            $params['contribution_status_id'] = 2;
          }
          break;
        case 'iATSServiceUKDDContribution': // UK DD contribution: update the payment instrument, fix the receive date
          $params['payment_instrument_id'] = 2;
          if ($start_date = strtotime($_POST['payer_validate_start_date'])) {
            $params['receive_date'] = date('Ymd',$start_date).'120000';
          }
          break;
        case 'iATSServiceUKDDContributionRecur': // UK DD recurring contribution record: update the payment instrument, fix the start_date
          $params['payment_instrument_id'] = 2;
          if ($start_date = strtotime($_POST['payer_validate_start_date'])) {
            $params['start_date'] = date('Ymd',$start_date).'120000';
          }
          break;
      }
    }
    // watchdog('iats_civicrm','ignoring hook_civicrm_pre for objectName @id',array('@id' => $objectName));
  }
}

/**
 * The contribution itself doesn't tell you which payment processor it came from
 * So we have to dig back via the contribution_recur_id that it is associated with.
 */
function _iats_civicrm_get_payment_processor_id($contribution_recur_id) {
  $params = array(
    'version' => 3,
    'sequential' => 1,
    'id' => $contribution_recur_id,
  );
  $result = civicrm_api('ContributionRecur', 'getsingle', $params);
  if (empty($result['payment_processor_id'])) {
    return FALSE;
    // TODO: log error
  }
  return $result['payment_processor_id'];
}

/*
 * Utility function to see if a payment processor id is using one of the iATS payment processors
 *
 * This function relies on our naming convention for the iats payment processor classes, staring with the string Payment_iATSService
 */
function _iats_civicrm_is_iats($payment_processor_id) {
  $params = array(
    'version' => 3,
    'sequential' => 1,
    'id' => $payment_processor_id,
  );
  $result = civicrm_api('PaymentProcessor', 'getsingle', $params);
  if (empty($result['class_name'])) {
    return FALSE;
    // TODO: log error
  }
  $type = substr($result['class_name'],0,19);
  $subtype = substr($result['class_name'],19);
  return ('Payment_iATSService' == $type) ? 'iATSService'.$subtype  : FALSE;
}

/**
 * Internal utility function: return the id's of any iATS processors matching various conditions
 *
 * processors: an array of payment processors indexed by id to filter by,
 *             or if NULL, it searches through all
 * subtype: the iats service class name subtype
 * params: an array of additional params to pass to the api call
 */
function iats_civicrm_processors($processors, $subtype = '', $params = array()) {
  $list = array();
  $class_name = 'Payment_iATSService'.$subtype;
  $params = $params + array('version' => 3, 'sequential' => 1, 'class_name' => $class_name);
  $result = civicrm_api('PaymentProcessor', 'get', $params);
  if (0 == $result['is_error'] && count($result['values']) > 0) {
    foreach($result['values'] as $paymentProcessor) {
      $id = $paymentProcessor['id'];
      if ((is_null($processors)) || !empty($processors[$id])) {
        $list[$id] = $paymentProcessor;
      }
    }
  }
  return $list;
}

/*
 * Customize direct debit billing blocks, per currency
 *
 * Each country has different rules about direct debit, so only currencies that we explicitly handle will be
 * customized, others will get a warning.
 *
 * The currency-specific functions will do things like modify labels, add exta fields,
 * add legal requirement notice and perhaps checkbox acceptance for electronic acceptance of ACH/EFT, and
 * make this form nicer by include a sample check with instructions for getting the various numbers
 *
 * Each one also includes some javascript to move the new fields around on the DOM
 */

function iats_acheft_form_customize($form) {
  // $fname = 'iats_acheft_form_customize_'.$form->_values['currency'];
  // currency is in a funny place for the Event registration form
  $currency = isset($form->_values['event']['currency']) ? $form->_values['event']['currency'] : $form->_values['currency'];
  $fname = 'iats_acheft_form_customize_'.$currency;
  /* we always want these three fields to be required, in all currencies */
  $form->addRule('account_holder', ts('%1 is a required field.', array(1 => ts('Name of Account Holder'))), 'required');
  $form->addRule('bank_account_number', ts('%1 is a required field.', array(1 => ts('Account Number'))), 'required');
  $form->addRule('bank_name', ts('%1 is a required field.', array(1 => ts('Bank Name'))), 'required');
  if (function_exists($fname)) {
    $fname($form);
  }
  else { // I'm handling an unexpected currency
    CRM_Core_Region::instance('billing-block')->add(array(
      'template' => 'CRM/iATS/BillingBlockDirectDebitExtra_Other.tpl'
    ));
  }
}

/*
 * Customization for USD ACH-EFT billing block
 */
function iats_acheft_form_customize_USD($form) {
  $form->addElement('select', 'bank_account_type', ts('Account type'), array('CHECKING' => 'Checking', 'SAVING' => 'Saving'));
  $form->addRule('bank_account_type', ts('%1 is a required field.', array(1 => ts('Account type'))), 'required');
  $element = $form->getElement('account_holder');
  $element->setLabel(ts('Name of Account Holder'));
  $element = $form->getElement('bank_account_number');
  $element->setLabel(ts('Bank Account Number'));
  $element = $form->getElement('bank_identification_number');
  $element->setLabel(ts('Bank Routing Number'));
  $form->addRule('bank_identification_number', ts('%1 is a required field.', array(1 => ts('Bank Routing Number'))), 'required');
  CRM_Core_Region::instance('billing-block')->add(array(
    'template' => 'CRM/iATS/BillingBlockDirectDebitExtra_USD.tpl'
  ));
}

/*
 * Customization for CAD ACH-EFT billing block
 */
function iats_acheft_form_customize_CAD($form) {
  $form->addElement('text', 'cad_bank_number', ts('Bank Number (3 digits)'));
  $form->addRule('cad_bank_number', ts('%1 is a required field.', array(1 => ts('Bank Number'))), 'required');
  $form->addRule('cad_bank_number', ts('%1 must contain only digits.', array(1 => ts('Bank Number'))), 'numeric');
  $form->addRule('cad_bank_number', ts('%1 must be of length 3.', array(1 => ts('Bank Number'))), 'rangelength', array(3,3));
  $form->addElement('text', 'cad_transit_number', ts('Transit Number (5 digits)'));
  $form->addRule('cad_transit_number', ts('%1 is a required field.', array(1 => ts('Transit Number'))), 'required');
  $form->addRule('cad_transit_number', ts('%1 must contain only digits.', array(1 => ts('Transit Number'))), 'numeric');
  $form->addRule('cad_transit_number', ts('%1 must be of length 5.', array(1 => ts('Transit Number'))), 'rangelength', array(5,5));
  $form->addElement('select', 'bank_account_type', ts('Account type'), array('CHECKING' => 'Chequing', 'SAVING' => 'Savings'));
  $form->addRule('bank_account_type', ts('%1 is a required field.', array(1 => ts('Account type'))), 'required');
  /* minor customization of labels + make them required */
  $element = $form->getElement('account_holder');
  $element->setLabel(ts('Name of Account Holder'));
  $element = $form->getElement('bank_account_number');
  $element->setLabel(ts('Account Number'));
  $form->addRule('bank_account_number', ts('%1 must contain only digits.', array(1 => ts('Bank Account Number'))), 'numeric');
  /* the bank_identification_number is hidden and then populated using jquery, in the custom template */
  $element = $form->getElement('bank_identification_number');
  $element->setLabel(ts('Bank Number + Transit Number'));
  CRM_Core_Region::instance('billing-block')->add(array(
    'template' => 'CRM/iATS/BillingBlockDirectDebitExtra_CAD.tpl'
  ));
}

/*
 * Contribution form customization for iATS secure swipe
 */
function iats_swipe_form_customize($form) {
 // remove two fields that are replaced by the swipe code data
 // we need to remove them from the _paymentFields as well or they'll sneak back in!
 $form->removeElement('credit_card_type',TRUE);
 $form->removeElement('cvv2',TRUE);
 unset($form->_paymentFields['credit_card_type']);
 unset($form->_paymentFields['cvv2']);
 // add a single text area to store/display the encrypted cc number that the swipe device will fill
 $form->addElement('textarea','encrypted_credit_card_number',ts('Encrypted'), array('cols' => '80', 'rows' => '8'));
 $form->addRule('encrypted_credit_card_number', ts('%1 is a required field.', array(1 => ts('Encrypted'))), 'required');
 CRM_Core_Region::instance('billing-block')->add(array(
   'template' => 'CRM/iATS/BillingBlockSwipe.tpl'
 ));
}

/*
 * Customize direct debit billing block for UK Direct Debit
 *
 * This could be handled by iats_acheft_form_customize, except there's some tricky multi-page stuff for the payer validate step
 */

function iats_ukdd_form_customize($form) {
  /* uk direct debits have to start 16 days after the initial request is made */
  if (!$form->elementExists('is_recur')) {
    // todo generate an error on the page
    return;
  }
  define('IATS_UKDD_START_DELAY',16 * 24 * 60 * 60);
  /* for batch efficiencies, restrict to a specific set of days of the month, less than 28 */
  $start_days = array('1', '15'); // you can change these if you're sensible and careful
  $start_dates = array(); // actual date options
  $start_date = time() + IATS_UKDD_START_DELAY;
  for ($j = 0; $j < count($start_days); $j++) {
    $i = 0;  // so I don't get into an infinite loop somehow ..
    while(($i++ < 60) && !in_array($dp['mday'],$start_days)) {
      $start_date += (24 * 60 * 60);
      $dp = getdate($start_date);
    }
    $start_dates[date('Y-m-d',$start_date)] = strftime('%B %e, %Y',$start_date);
    $start_date += (24 * 60 * 60);
    $dp = getdate($start_date);
  }
  $service_user_number = $form->_paymentProcessor['signature'];
  $payee = _iats_civicrm_domain_info('name');
  $phone = _iats_civicrm_domain_info('domain_phone');
  $email = _iats_civicrm_domain_info('domain_email');
  $form->addRule('is_recur', ts('You can only use this form to make recurring contributions.'), 'required');
  /* declaration checkbox at the top */
  $form->addElement('checkbox', 'payer_validate_declaration', ts('I wish to start a Direct Debit'));
  $form->addElement('static', 'payer_validate_contact', ts(''), ts('Organization: %1, Phone: %2, Email: %3', array('%1' => $payee, '%2' => $phone['phone'], '%3' => $email)));
  $form->addElement('select', 'payer_validate_start_date', ts('Date of first collection'), $start_dates);
  $form->addRule('payer_validate_declaration', ts('%1 is a required field.', array(1 => ts('The Declaration'))), 'required');
  $form->addRule('installments', ts('%1 is a required field.', array(1 => ts('Number of installments'))), 'required');
  /* customization of existing elements */
  $element = $form->getElement('account_holder');
  $element->setLabel(ts('Account Holder Name'));
  $form->addRule('account_holder', ts('%1 is a required field.', array(1 => ts('Name of Account Holder'))), 'required');
  $element = $form->getElement('bank_account_number');
  $element->setLabel(ts('Account Number'));
  $form->addRule('bank_account_number', ts('%1 is a required field.', array(1 => ts('Account Number'))), 'required');
  $element = $form->getElement('bank_identification_number');
  $element->setLabel(ts('Sort Code'));
  $form->addRule('bank_identification_number', ts('%1 is a required field.', array(1 => ts('Sort Code'))), 'required');
  /* new payer validation elements */
  $form->addElement('textarea', 'payer_validate_address', ts('Name and full postal address of your Bank or Building Society'), array('rows' => '6', 'columns' => '30'));
  $form->addElement('text', 'payer_validate_service_user_number', ts('Service User Number'));
  $form->addElement('text', 'payer_validate_reference', ts('Reference'), array());
  $form->addElement('text', 'payer_validate_date', ts('Today\'s Date'), array()); // date on which the validation happens, reference
  $form->addElement('static', 'payer_validate_instruction', ts('Instruction to your Bank or Building Society'), ts('Please pay %1 Direct Debits from the account detailed in this instruction subject to the safeguards assured by the Direct Debit Guarantee. I understand that this instruction may remain with TestingTest and, if so, details will be passed electronically to my Bank / Building Society.',array('%1' => "<strong>$payee</strong>")));
  // $form->addRule('bank_name', ts('%1 is a required field.', array(1 => ts('Bank Name'))), 'required');
  //$form->addRule('bank_account_type', ts('%1 is a required field.', array(1 => ts('Account type'))), 'required');
  /* only allow recurring contributions, set date */
  $form->setDefaults(array(
    'is_recur' => 1,
    'payer_validate_date' => date('F j, Y'),
    'payer_validate_start_date' => current(array_keys($start_dates)),
    'payer_validate_service_user_number' => $service_user_number,
  )); // make recurring contrib default to true
  CRM_Core_Region::instance('billing-block')->add(array(
    'template' => 'CRM/iATS/BillingBlockDirectDebitExtra_GBP.tpl'
  ));
}

/* Modifications to a (public/frontend) contribution forms if iATS ACH/EFT or SWIPE is enabled
 *  1. set recurring to be the default, if enabled (ACH/EFT) [previously forced recurring, removed in 1.2.4]
 *  2. add extra fields/modify labels
 */
function iats_civicrm_buildForm_Contribution_Frontend(&$form) {
  if (empty($form->_paymentProcessors)) {
    return;
  }
  $acheft = iats_civicrm_processors($form->_paymentProcessors,'ACHEFT');
  $swipe = iats_civicrm_processors($form->_paymentProcessors,'SWIPE');
  $ukdd = iats_civicrm_processors($form->_paymentProcessors,'UKDD');

  // include the required javascripts for available customized selections
  // TODO: skip this if we're just loading a fragment of the page via ajax
  // If a form allows ACH/EFT and enables recurring, set recurring to the default
  if (0 < count($acheft)) {
    CRM_Core_Resources::singleton()->addScriptFile('com.iatspayments.civicrm', 'js/dd_acheft.js',10,'html-header');
    // country specific js needs to be loaded here so it's available later
    CRM_Core_Resources::singleton()->addScriptFile('com.iatspayments.civicrm', 'js/dd_cad.js',10,'html-header');
    if (isset($form->_elementIndex['is_recur'])) {
      $form->setDefaults(array('is_recur' => 1)); // make recurring contrib default to true
    }
  }
  if (0 < count($swipe)) {
    CRM_Core_Resources::singleton()->addScriptFile('com.iatspayments.civicrm', 'js/swipe.js',10,'html-header');
  }
  if (0 < count($ukdd)) {
    CRM_Core_Resources::singleton()->addScriptFile('com.iatspayments.civicrm', 'js/dd_uk.js',10,'html-header');
    if (isset($form->_elementIndex['is_recur'])) {
      $form->setDefaults(array('is_recur' => 1)); // make recurring contrib default to true
    }
  }
  /* Mangle (in a currency-dependent way) the ajax-bit of the form if I've just selected an ach/eft option */
  if (!empty($acheft[$form->_paymentProcessor['id']])){
    iats_acheft_form_customize($form);
    // watchdog('iats_acheft',kprint_r($form,TRUE));
  }

  /* now something similar for swipe */
  if (!empty($swipe[$form->_paymentProcessor['id']]) && !empty($form->_elementIndex['credit_card_exp_date'])) {
    iats_swipe_form_customize($form);
  }

  /* UK Direct debit option */
  if (!empty($ukdd[$form->_paymentProcessor['id']])){
    iats_ukdd_form_customize($form);
    // watchdog('iats_acheft',kprint_r($form,TRUE));
  }

  /* and finally, for most frontend forms, use the dpm.js script to use the DirectPost Method override
   * TODO: provide an admin way to prevent this
   * TODO: use it for swipe, never for ukdd?
   */
  require_once("CRM/iATS/iATSService.php");
  if (iATS_Service_Request::isDPM($form->_paymentProcessor)) {
    CRM_Core_Region::instance('billing-block')->add(array(
      'template' => 'CRM/iATS/BillingBlockDPM.tpl'
    ));
    $iats_domain = parse_url($form->_paymentProcessor['url_site'],PHP_URL_HOST);
    $dpm_url = iATS_Service_Request::dpm_url($iats_domain);
    _iats_civicrm_varset(array('dpmURL' => $dpm_url));
    CRM_Core_Resources::singleton()->addScriptFile('com.iatspayments.civicrm', 'js/dpm.js');
  }
}

/*  Fix the backend credit card contribution forms
 *  Includes CRM_Contribute_Form_Contribution, CRM_Event_Form_Participant, CRM_Member_Form_Membership
 *  1. Remove my ACH/EFT processors
 *     Now fixed in core for contribution forms: https://issues.civicrm.org/jira/browse/CRM-14442
 *  2. Force SWIPE (i.e. remove all others) if it's the default, and mangle the form accordingly.
 *     For now, this form doesn't refresh when you change payment processors, so I can't use swipe if it's not the default, so i have to remove it.
 */
function iats_civicrm_buildForm_CreditCard_Backend(&$form) {
  // skip if i don't have any processors
  if (empty($form->_processors)) {
    return;
  }
  // get all my swipe processors
  $swipe = iats_civicrm_processors($form->_processors,'SWIPE');
  // get all my ACH/EFT processors (should be 0, but I'm fixing old core bugs)
  $acheft = iats_civicrm_processors($form->_processors,'ACHEFT');
  // if an iATS SWIPE payment processor is enabled and default remove all other payment processors
  $swipe_id_default = 0;
  if (0 < count($swipe)) {
    foreach($swipe as $id => $pp) {
      if ($pp['is_default']) {
        $swipe_id_default = $id;
        break;
      }
    }
  }
  // find the available pp options form element (update this if we ever switch from quickform, uses a quickform internals)
  // not all invocations of the form include this, so check for non-empty value first
  if (!empty($form->_elementIndex['payment_processor_id'])) {
    $pp_form_id = $form->_elementIndex['payment_processor_id'];
    // now cycle through them, either removing everything except the default swipe or just removing the ach/eft
    $element = $form->_elements[$pp_form_id]->_options;
    foreach($element as $option_id => $option) {
      $pp_id = $option['attr']['value']; // key is set to payment processor id
      if ($swipe_id_default) {
        // remove any that are not my swipe default pp
        if ($pp_id != $swipe_id_default) {
          unset($form->_elements[$pp_form_id]->_options[$option_id]);
          unset($form->_processors[$pp_id]);
          if (!empty($form->_recurPaymentProcessors[$pp_id])) {
            unset($form->_recurPaymentProcessors[$pp_id]);
          }
        }
      }
      elseif (!empty($acheft[$pp_id]) || !empty($swipe[$pp_id])) {
        // remove my ach/eft and swipe, which both require form changes
        unset($form->_elements[$pp_form_id]->_options[$option_id]);
        unset($form->_processors[$pp_id]);
        if (!empty($form->_recurPaymentProcessors[$pp_id])) {
          unset($form->_recurPaymentProcessors[$pp_id]);
        }
      }
    }
  }

  // if i'm using swipe as default and I've got a billing section, then customize it
  if ($swipe_id_default) {
    CRM_Core_Resources::singleton()->addScriptFile('com.iatspayments.civicrm', 'js/swipe.js',10,'html-header');
    if (!empty($form->_elementIndex['credit_card_exp_date'])) {
      iats_swipe_form_customize($form);
    }
  }
}

/*
 *  Provide helpful links to backend-ish payment pages for ACH/EFT, since the backend credit card pages don't work/apply
 *  Could do the same for swipe?
 */
function iats_civicrm_buildForm_CRM_Contribute_Form_Search(&$form) {
  // ignore invocations that aren't for a specific contact, e.g. the civicontribute dashboard
  if (empty($form->_defaultValues['contact_id'])) {
    return;
  }
  $contactID = $form->_defaultValues['contact_id'];
  $acheft = iats_civicrm_processors(NULL,'ACHEFT',array('is_active' => 1, 'is_test' => 0));
  $acheft_backoffice_links = array();
  // for each ACH/EFT payment processor, try to provide a different mechanism for 'backoffice' type contributions
  // note: only offer payment pages that provide iATS ACH/EFT exclusively
  foreach(array_keys($acheft) as $pp_id) {
    $params = array('version' => 3, 'sequential' => 1, 'is_active' => 1, 'payment_processor' => $pp_id);
    $result = civicrm_api('ContributionPage', 'get', $params);
    if (0 == $result['is_error'] && count($result['values']) > 0) {
      foreach($result['values'] as $page) {
        $url = CRM_Utils_System::url('civicrm/contribute/transact','reset=1&cid='.$contactID.'&id='.$page['id']);
        $acheft_backoffice_links[] = array('url' => $url, 'title' => $page['title']);
      }
    }
  }
  if (count($acheft_backoffice_links)) {
    _iats_civicrm_varset(array('backofficeLinks' => $acheft_backoffice_links));
    CRM_Core_Resources::singleton()->addScriptFile('com.iatspayments.civicrm', 'js/contribute_form_search.js');
  }
}

/*
 * Modify the recurring contribution cancelation form to exclude the confusing message about sending the request to the backend
 */
function iats_civicrm_buildForm_CRM_Contribute_Form_CancelSubscription(&$form) {
  if ($form->elementExists('send_cancel_request')) {
    $form->removeElement('send_cancel_request');
  }
}

/*
 * Modify the contribution Confirm screen for iATS UK DD
 *  1. display extra field data injected earlier for payer validation
 */
function iats_civicrm_buildForm_Contribution_Confirm_Payment_iATSServiceUKDD(&$form) {
  $form->addElement('textarea', 'payer_validate_address', ts('Name and full postal address of your Bank or Building Society'), array('rows' => '6', 'columns' => '30'));
  $form->addElement('text', 'payer_validate_service_user_number', ts('Service User Number'));
  $form->addElement('text', 'payer_validate_reference', ts('Reference'));
  $form->addElement('text', 'payer_validate_date', ts('Today\'s Date'), array()); // date on which the validation happens, reference
  $form->addElement('text', 'payer_validate_start_date', ts('Date of first collection'));
  $form->addElement('static', 'payer_validate_instruction', ts('Instruction to your Bank or Building Society'), ts('Please pay %1 Direct Debits from the account detailed in this instruction subject to the safeguards assured by the Direct Debit Guarantee. I understand that this instruction may remain with TestingTest and, if so, details will be passed electronically to my Bank / Building Society.',array('%1' => "<strong>$payee</strong>")));
  $defaults = array(
    'payer_validate_date' => date('F j, Y'),
  );
  foreach(array('address','service_user_number','reference','date','start_date') as $k) {
    $key = 'payer_validate_'.$k;
    $defaults[$key] = $form->_params[$key];
  };
  $form->setDefaults($defaults);
  CRM_Core_Region::instance('contribution-confirm-billing-block')->add(array(
    'template' => 'CRM/iATS/ContributeConfirmExtra_UKDD.tpl'
  ));
}

/*
 * Modify the contribution Thank You screen for iATS UK DD
 */
function iats_civicrm_buildForm_Contribution_ThankYou_Payment_iATSServiceUKDD(&$form) {
  foreach(array('address','service_user_number','reference','date','start_date') as $k) {
    $key = 'payer_validate_'.$k;
    $form->addElement('static', $key, $key, $form->_params[$key]);
  };
  CRM_Core_Region::instance('contribution-thankyou-billing-block')->add(array(
    'template' => 'CRM/iATS/ContributeThankYouExtra_UKDD.tpl'
  ));
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function iats_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _iats_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * For a recurring contribution, find a reasonable candidate for a template, where possible
 */
function _iats_civicrm_getContributionTemplate($contribution) {
  // Get the first contribution in this series that matches the same total_amount, if present
  $template = array();
  $get = array('version'  => 3, 'contribution_recur_id' => $contribution['contribution_recur_id'], 'options'  => array('sort'  => ' id' , 'limit'  => 1));
  if (!empty($contribution['total_amount'])) {
    $get['total_amount'] = $contribution['total_amount'];
  }
  $result = civicrm_api('contribution', 'get', $get);
  if (!empty($result['values'])) {
    $contribution_ids = array_keys($result['values']);
    $template = $result['values'][$contribution_ids[0]];
    $template['line_items'] = array();
    $get = array('version'  => 3, 'entity_table' => 'civicrm_contribution', 'entity_id' => $contribution_ids[0]);
    $result = civicrm_api('LineItem', 'get', $get);
    if (!empty($result['values'])) {
      foreach($result['values'] as $initial_line_item) {
        $line_item = array();
        foreach(array('price_field_id','qty','line_total','unit_price','label','price_field_value_id','financial_type_id') as $key) {
          $line_item[$key] = $initial_line_item[$key];
        }
        $template['line_items'] = $line_item;
      }
    }
  }
  return $template;
}
