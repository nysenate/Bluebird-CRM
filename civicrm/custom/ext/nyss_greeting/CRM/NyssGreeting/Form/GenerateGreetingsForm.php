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
        // If we do get timeout problems, then we can think about breaking
        // the tasks up into smaller jobs using the 'limit' parameter
        $queue = \Civi::queue(CRM_NyssGreeting_Utils::QUEUE_NAME, [
            'type' => 'SqlParallel',
            'reset' => FALSE,
            'error' => 'abort',
        ]);

        foreach (['postal_greeting', 'email_greeting', 'addressee'] as $g) {
            $task = $queue->createItem(
                new \CRM_Queue_Task(
                    ['CRM_NyssGreeting_Utils', 'runTask'],
                    // arguments
                    [
                        [
                            'ct' => 'Individual',
                            'gt' => $g,
                        ]
                    ],
                    // title
                    ts("Update Postal Greetings")
                )
            );
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
