<?php

namespace Civi\Api4\Action\WebIntegration;

use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;
use PHPUnit\Exception;

class UpdateSurveyGroups extends AbstractAction {

  /**
   * Set is_active to this value
   * @var bool|null
   */
  protected ?bool $active = null;

  /**
   * Set is_public to this value
   * @var bool|null
   */
  protected ?bool $public = null;

  /**
   * @inheritDoc
   */
  public function _run(Result $result) {
    \Civi::log()->info('Running NYSS Update Survey Groups', [
      'active' => $this->active,
      'public' => $this->public
    ]);

    // Get all custom "Survey" groups
    $customGroups = \Civi\Api4\CustomGroup::get($this->getCheckPermissions())
      ->addSelect('*')
      ->addWhere('title', 'REGEXP', '^Survey')
      ->addWhere('is_active', '!=', $this->active)
      ->addWhere('is_public', '!=', $this->public)
      //->setLimit(25)
      ->execute();

    // For each survey, get the custom field values (survey submissions) and
    // populate the appropriate Activity Details of the matching entity
    foreach ($customGroups as $customGroup) {
      \Civi::log()->info('Updating Custom Group: ' . $customGroup['title'], [
        'name' => $customGroup['name'],
        'id' => $customGroup['id'],
        'title' => $customGroup['title'],
        'table_name' => $customGroup['table_name'],
        'is_active' => $customGroup['is_active'],
        'is_public' => $customGroup['is_public']
      ]);

      try {
        // Update Custom Group, setting is_active and is_public only
        $update_results = \Civi\Api4\CustomGroup::update($this->getCheckPermissions())
          ->addValue('is_active', $this->active)
          ->addValue('is_public', $this->public)
          ->addWhere('id', '=', $customGroup['id'])
          ->execute();

        $result->append([
          $update_results->single()
        ]);
        \Civi::log()->info('Custom Group Updated: ' . $customGroup['id'], []);
      } catch(Exception $e) {
        \Civi::log()->info('Exception updating Custom Group ('.$customGroup['id'].') details.', [
          'custom_group' => $customGroup,
          'exception' => $e
        ]);
      }
    }
  }

}