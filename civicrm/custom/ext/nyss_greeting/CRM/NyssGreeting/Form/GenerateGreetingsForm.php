<?php
declare(strict_types=1);

use CRM_NyssGreeting_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
#[CRM_NYSS_Attribute_IssueRef('17969')]
class CRM_NyssGreeting_Form_GenerateGreetingsForm extends CRM_Core_Form
{
    /**
     * @throws \CRM_Core_Exception
     */
    public function buildQuickForm(): void
    {
        $this->setTitle(ts('Generate Constituent Greetings'));

        $this->addButtons([
            [
                'type' => 'submit',
                'name' => E::ts('Generate Greetings'),
                'isDefault' => TRUE,
            ],
        ]);

        $contacts = civicrm_api4('Contact', 'get', [
            'select' => [
                'row_count',
            ],
            'where' => [
                ['contact_type', '=', 'Individual'],
                ['OR', [['email_greeting_id', 'IS NULL'], ['postal_greeting_id', 'IS NULL'], ['addressee_id', 'IS NULL']]],
            ],
            'checkPermissions' => TRUE,
        ]);
        $smarty = CRM_Core_Smarty::singleton();
        $smarty->assign('count_individuals', $contacts->count());

        // export form elements
        $this->assign('elementNames', $this->getRenderableElementNames());
        parent::buildQuickForm();
    }

    public function postProcess(): void
    {
        // Process greeting generation in a job queue to avoid page timeouts.
        // Each task processes a small batch so individual AJAX requests stay fast.
        $batch_size = 500;

        // Use total Individual count as a safe upper bound for tasks per greeting type.
        // updateGreeting() returns early if there's nothing left to process, so
        // slightly over-allocating tasks is harmless.
        $total = (int) CRM_Core_DAO::singleValueQuery(
            "SELECT COUNT(*) FROM civicrm_contact WHERE contact_type = 'Individual' AND is_deleted = 0"
        );
        $num_tasks = max(1, (int) ceil($total / $batch_size));

        // Use Sql (sequential) rather than SqlParallel to avoid two tasks for the
        // same greeting type selecting the same unprocessed contacts concurrently.
        $queue = \Civi::queue(CRM_NyssGreeting_Utils::QUEUE_NAME, [
            'type' => 'Sql',
            'reset' => FALSE,
            'error' => 'abort',
        ]);

        foreach (['postal_greeting', 'email_greeting', 'addressee'] as $g) {
            for ($i = 0; $i < $num_tasks; $i++) {
                $queue->createItem(
                    new \CRM_Queue_Task(
                        ['CRM_NyssGreeting_Utils', 'runTask'],
                        [['ct' => 'Individual', 'gt' => $g, 'limit' => $batch_size]],
                        ts("Update %1 (batch %2 of %3)", [1 => $g, 2 => $i + 1, 3 => $num_tasks])
                    )
                );
            }
        }

        $runner = new CRM_Queue_Runner([
            'title' => ts('Generating Greetings'),
            'queue' => $queue,
            'errorMode' => CRM_Queue_Runner::ERROR_ABORT,
            'onEndUrl' => CRM_Utils_System::url('civicrm/nyss/generate-greetings',
                [
                    'reset' => 1,
                ]),
        ]);
        $runner->runAllViaWeb();

        //$values = $this->exportValues();

        parent::postProcess();
    }

    /**
     * Get the fields/elements defined in this form.
     *
     * @return array (string)
     */
    public function getRenderableElementNames(): array
    {
        // The _elements list includes some items which should not be
        // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
        // items don't have labels.  We'll identify renderable by filtering on
        // the 'label'.
        $elementNames = [];
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
