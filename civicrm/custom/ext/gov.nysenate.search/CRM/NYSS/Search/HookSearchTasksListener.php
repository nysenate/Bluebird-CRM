<?php

class CRM_NYSS_Search_HookSearchTasksListener
{
    public static function searchTasks($event) {
        if ($event->objectType === 'contact'){
            $new_tasks = self::remove_create_mailing_task($event->tasks);
            $event->tasks = $new_tasks;
        }
    }

    /** NYSS 6682
      * Remove "Create Mass Email" action from search tasks */
    #[CRM_NYSS_Attribute_IssueRef('6682')]
    private static function remove_create_mailing_task($all_tasks) {
        $remaining_tasks = $all_tasks;
        unset($remaining_tasks[CRM_Core_Task::CREATE_MAILING]);
        return $remaining_tasks;
    }
}