<?php

namespace Civi\Api4\Action\WebIntegration;

use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;
use PHPUnit\Exception;
/**
 * NYSS 16799
 * cv api4 WebIntegration.migrateSurveyData
 */
class MigrateSurveyData extends AbstractAction {

  /**
   * When specified in the API call, will limit migration and results to
   * this Activity Record.
   */
  protected $activity_id;


  /**
   * @inheritDoc
   */
  public function _run(Result $result) {
    \Civi::log()->info('Running NYSS Migrate Survey Data', []);

    // Verify that Website_Survey.survey_data field exists
    $data_field = \Civi\Api4\CustomField::get($this->getCheckPermissions())
      ->addWhere('custom_group_id:name', '=', 'Website_Survey')
      ->addWhere('name', '=', 'survey_data')
      ->setLimit(25)
      ->execute();

    if ($data_field->count() != 1) {
      // Throwing Exception because it forces an error in calling shell
      // command ($?)
      throw new \CRM_Core_Exception('Missing required Website_Survey.survey_data field.');
      /*
      return [
        'is_error' => 1,
        'error_message' => 'Missing required Website_Survey.survey_data field.',
      ];
      */
    }

    // Get all custom "Survey" groups
    $customGroups = \Civi\Api4\CustomGroup::get($this->getCheckPermissions())
      ->addSelect('*')
      ->addWhere('title', 'REGEXP', '^Survey')
      //->setLimit(25)
      ->execute();

    // For each survey, get the custom field values (survey submissions) and
    // populate the appropriate Activity Details of the matching entity
    foreach ($customGroups as $customGroup) {
      \Civi::log()->info('Processing Custom Group: ' . $customGroup['title'], [
        'name' => $customGroup['name'],
        'id' => $customGroup['id'],
        'title' => $customGroup['title'],
        'table_name' => $customGroup['table_name']
      ]);
      /*\Civi::log()->debug('Processing Custom Group: ' . $customGroup['title'], [
        'data' => $customGroup
      ]);*/

      // find all activities related to this custom group / survey
      $webform_id = substr($customGroup['name'],7);
      $webform_name = 'Survey_'.$webform_id;
      $query = \Civi\Api4\Activity::get($this->getCheckPermissions())
        ->addSelect($webform_name.'.*', 'Website_Survey.*', '*')
        ->addWhere('activity_type_id:name', '=', 'Website Survey')
        ->addWhere('Website_Survey.Survey_ID', '=', $webform_id);
      if ($this->activity_id) {
        $query->addWhere('id', '=', $this->activity_id);
      }
        //->setLimit(25);
      $activities = $query->execute();

      //process each activity
      foreach ($activities as $activity) {
        \Civi::log()->info('Processing Activity: ' . $activity['id'], [
          //'data' => $activity
        ]);
        // Format data
        $fields_as_text = '';
        $data_fields = [];
        foreach(array_keys($activity) as $activity_key) {
          if (preg_match('/^'.$webform_name.'\.(.+)$/', $activity_key,$matches)) {
            $field_name = strtolower($matches[1]);
            // this format will mimic format used for future accumulator data.
            $data_fields[] = (object)['field'=>$field_name, 'value'=>$activity[$activity_key]];
            $fields_as_text .= \CRM_NYSS_BAO_Integration_WebsiteEvent_SurveyEvent::formatFieldForText($field_name,$activity[$activity_key] ?? '');
          }
        }
        try {
          // Update Activity Details Field and Survey Data
          $update_results = \Civi\Api4\Activity::update($this->getCheckPermissions())
            ->addValue('details', '<pre>'.$fields_as_text.'</pre>')
            ->addValue('Website_Survey.survey_data', json_encode($data_fields))
            ->addWhere('id', '=', $activity['id'])
            ->execute();

          $update_results->single();
          $result->append([
            'activity_id' => $activity['id'],
            'webform_id' => $webform_id,
            'webform_name' => $webform_name,
            'webform_data' => json_encode($data_fields)
          ]);
          \Civi::log()->info('Activity Updated: ' . $activity['id'], []);
        } catch(Exception $e) {
          \Civi::log()->info('Exception updating Activity ('.$activity['id'].') details.', [
            'activity' => $activity,
            'exception' => $e
          ]);
        }
      }
    }
  }
}