<?php
#[CRM_NYSS_Attribute_IssueRef('17969')]
class CRM_NyssGreeting_Utils
{
    const QUEUE_NAME = 'generate-greetings';

    /**
     * This is a queue runner task that generates contact greetings based what's passed to it in $params
     * Example Usage taken from CRM_NyssGreeting_Form_GenerateGreetingsForm:
     * $task = $queue->createItem(
     *      new \CRM_Queue_Task(
     *          ['CRM_NyssGreeting_Utils', 'run'],
     *          // arguments to updateGreeting()
     *          [
     *              [
     *                  'ct' => 'Individual',
     *                  'gt' => 'email_greeting',
     *              ]
     *          ],
     *          // task title
     *          ts("Update Postal Greetings")
     *      )
     * );
     * @see CRM_Contact_BAO_Contact_Utils::updateGreeting($params)
     * @link https://docs.civicrm.org/dev/en/latest/framework/queues/
     */
    public static function runTask($context, $params) {
        try {
            CRM_Contact_BAO_Contact_Utils::updateGreeting($params);
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }
}