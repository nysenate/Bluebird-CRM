<?php

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_DoctorWhen_Form_Cleanup extends CRM_Core_Form {
  /**
   * @var CRM_DoctorWhen_Cleanups
   */
  private $cleanups;

  public function __construct($state, $action, $method, $name) {
    parent::__construct($state, $action, $method, $name);
    $this->cleanups = new CRM_DoctorWhen_Cleanups();
  }


  public function buildQuickForm() {
    $taskLabels = array();
    foreach ($this->cleanups->getAllActive() as $id => $cleanup) {
      $taskLabels[$cleanup->getTitle()] = $id;
    }
    $this->addCheckBox('tasks', 'Tasks', $taskLabels);

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function setDefaultValues() {
    $defaults = array();
    $defaults['tasks'] = array();
    //  foreach ($this->cleanups->getAllActive() as $id => $cleanup) {
    //    $defaults['tasks'][$id] = 1;
    //  }
    $defaults['tasks']['SuspendTracking'] = 1;
    $defaults['tasks']['RestoreTracking'] = 1;
    return $defaults;
  }

  public function postProcess() {
    $values = $this->exportValues();
    $options = array('tasks' => array());
    foreach ($values['tasks'] as $id => $bool) {
      if ($bool) {
        $options['tasks'][] = $id;
      }
    }
    $runner = new CRM_Queue_Runner(array(
      'title' => ts('Doctor When: Temporal cleanup agent'),
      'queue' => $this->cleanups->buildQueue($options),
      'onEnd' => array(__CLASS__, 'onEnd'),
      'onEndUrl' => CRM_Utils_System::url('civicrm/doctorwhen', 'reset=1'),
    ));
    $runner->runAllViaWeb(); // does not return
  }

  /**
   * Handle the final step of the queue
   * @param \CRM_Queue_TaskContext $ctx
   */
  public static function onEnd(CRM_Queue_TaskContext $ctx) {
    CRM_Core_Session::setStatus(ts('Executed DoctorWhen.'), '', 'success');
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
