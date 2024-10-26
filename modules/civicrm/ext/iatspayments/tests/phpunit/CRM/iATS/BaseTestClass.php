<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * FIXME - Add test description.
 *
 * Tips:
 *  - With HookInterface, you may implement CiviCRM hooks directly in the test class.
 *    Simply create corresponding functions (e.g. "hook_civicrm_post(...)" or similar).
 *  - With TransactionalInterface, any data changes made by setUp() or test****() functions will
 *    rollback automatically -- as long as you don't manipulate schema or truncate tables.
 *    If this test needs to manipulate schema or truncate tables, then either:
 *       a. Do all that using setupHeadless() and Civi\Test.
 *       b. Disable TransactionalInterface, and handle all setup/teardown yourself.
 */
abstract class BaseTestClass extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

  use \Civi\Test\Api3TestTrait;

  /**
   * Configure the headless environment.
   */
  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  /**
   * Copied from civicrm core CiviUnitTestCase.
   *
   * Instantiate form object.
   *
   * We need to instantiate the form to run preprocess, which means we have to trick it about the request method.
   *
   * @param string $class
   *   Name of form class.
   *
   * @param array $formValues
   *
   * @param string $pageName
   *
   * @param array $searchFormValues
   *   Values for the search form if the form is a task eg.
   *   for selected ids 6 & 8:
   *   [
   *      'radio_ts' => 'ts_sel',
   *      'task' => CRM_Member_Task::PDF_LETTER,
   *      'mark_x_6' => 1,
   *      'mark_x_8' => 1,
   *   ]
   *
   * @return \CRM_Core_Form
   */
  public function getFormObject($class, $formValues = [], $pageName = '', $searchFormValues = []) {
    $_POST = $formValues;
    /* @var CRM_Core_Form $form */
    $form = new $class();
    $_SERVER['REQUEST_METHOD'] = 'GET';
    switch ($class) {
      case 'CRM_Event_Cart_Form_Checkout_Payment':
      case 'CRM_Event_Cart_Form_Checkout_ParticipantsAndPrices':
        $form->controller = new CRM_Event_Cart_Controller_Checkout();
        break;

      default:
        $form->controller = new CRM_Core_Controller();
    }
    if (!$pageName) {
      $pageName = $form->getName();
    }
    $form->controller->setStateMachine(new CRM_Core_StateMachine($form->controller));
    $_SESSION['_' . $form->controller->_name . '_container']['values'][$pageName] = $formValues;
    if ($searchFormValues) {
      $_SESSION['_' . $form->controller->_name . '_container']['values']['Search'] = $searchFormValues;
    }
    if (isset($formValues['_qf_button_name'])) {
      $_SESSION['_' . $form->controller->_name . '_container']['_qf_button_name'] = $formValues['_qf_button_name'];
    }
    return $form;
  }

}
