<?php

namespace Civi\Test\Api4\Service;

use Civi\Api4\Service\Spec\FieldSpec;
use Civi\Api4\Service\Spec\SpecGatherer;
use \CRM_Utils_String as StringHelper;

class TestCreationParameterProvider {

  /**
   * @var SpecGatherer
   */
  protected $gatherer;

  /**
   * @param SpecGatherer $gatherer
   */
  public function __construct(SpecGatherer $gatherer) {
    $this->gatherer = $gatherer;
  }

  /**
   * @param $entity
   *
   * @return array
   */
  public function getRequired($entity) {
    $createSpec = $this->gatherer->getSpec($entity, 'create', FALSE);
    $requiredFields = $createSpec->getRequiredFields();

    if ($entity === 'Contact') {
      $requiredFields[] = $createSpec->getFieldByName('first_name');
      $requiredFields[] = $createSpec->getFieldByName('last_name');
    }

    $requiredParams = [];
    foreach ($requiredFields as $requiredField) {
      $value = $this->getRequiredValue($requiredField);
      $requiredParams[$requiredField->getName()] = $value;
    }

    unset($requiredParams['id']);

    return $requiredParams;
  }

  /**
   * Attempt to get a value using field option, defaults, FKEntity, or a random
   * value based on the data type.
   *
   * @param FieldSpec $field
   *
   * @return mixed
   * @throws \Exception
   */
  private function getRequiredValue(FieldSpec $field) {

    if ($field->getOptions()) {
      return $this->getOption($field);
    }
    elseif ($field->getDefaultValue()) {
      return $field->getDefaultValue();
    }
    elseif ($field->getFkEntity()) {
      return $this->getFkID($field);
    }

    $randomValue = $this->getRandomValue($field->getDataType());

    if ($randomValue) {
      return $randomValue;
    }

    throw new \Exception('Could not provide default value');
  }

  /**
   * @param FieldSpec $field
   *
   * @return mixed
   */
  private function getOption(FieldSpec $field) {
    $options = $field->getOptions();
    $useKeyNames = ['data_type', 'html_type'];
    $shouldUseKey = in_array($field->getName(), $useKeyNames);
    $isIdField = substr($field->getName(), -3) === '_id';

    if ($isIdField || $shouldUseKey) {
      return array_rand($options); // return key (ID)
    }
    else {
      return $options[array_rand($options)];
    }
  }

  /**
   * @param FieldSpec $field
   *
   * @return mixed
   * @throws \Exception
   */
  private function getFkID(FieldSpec $field) {
    $fkEntity = $field->getFkEntity();
    $params = ['checkPermissions' => FALSE];
    $entityList = civicrm_api4($fkEntity, 'get', $params);
    if ($entityList->count() < 1) {
      $msg = sprintf('At least one %s is required in test', $fkEntity);
      throw new \Exception($msg);
    }

    return $entityList->first()['id'];
  }

  /**
   * @param $dataType
   *
   * @return int|null|string
   */
  private function getRandomValue($dataType) {
    switch ($dataType) {
      case 'Boolean':
        return TRUE;

      case 'Integer':
        return rand(1, 2000);

      case 'String':
        return StringHelper::createRandom(10, implode('', range('a', 'z')));

      case 'Money':
        return sprintf('%d.%2d', rand(0, 2000), rand(1, 99));
    }

    return NULL;
  }

}
