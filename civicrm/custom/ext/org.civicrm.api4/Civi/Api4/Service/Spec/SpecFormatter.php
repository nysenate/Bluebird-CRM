<?php

namespace Civi\Api4\Service\Spec;

use CRM_Utils_Array as ArrayHelper;
use CRM_Core_DAO_AllCoreTables as TableHelper;

class SpecFormatter {
  /**
   * @param RequestSpec $spec
   * @param bool $includeFieldOptions
   *
   * @return array
   */
  public static function specToArray(RequestSpec $spec, $includeFieldOptions = FALSE) {
    $specArray = [];
    $specArray['entity'] = $spec->getEntity();
    $specArray['action'] = $spec->getAction();
    $specArray['fields'] = [];

    foreach ($spec->getFields() as $field) {
      if ($includeFieldOptions) {
        $field->getOptions();
      }
      $specArray['fields'][$field->getName()] = $field->toArray();
    }
    if (!$includeFieldOptions) {
      foreach ($specArray['fields'] as &$field) {
        unset($field['options']);
      }
    }

    return $specArray;
  }

  /**
   * @param array $data
   * @param string $entity
   *
   * @return FieldSpec
   */
  public static function arrayToField(array $data, $entity) {
    $dataTypeName = self::getDataType($data);

    if (!empty($data['custom_group_id'])) {
      $name = $data['custom_group']['name'] . '.' . $data['name'];
      $field = new CustomFieldSpec($name, $entity, $dataTypeName);
      $field->setCustomFieldId(ArrayHelper::value('id', $data));
      $field->setCustomGroupId($data['custom_group_id']);
      $field->setRequired((bool) ArrayHelper::value('is_required', $data, FALSE));
      $field->setTitle(ArrayHelper::value('label', $data));
      if (\CRM_Core_BAO_CustomField::isSerialized($data)) {
        $field->setSerialize(\CRM_Core_DAO::SERIALIZE_SEPARATOR_BOOKEND);
      }
    }
    else {
      $name = ArrayHelper::value('name', $data);
      $field = new FieldSpec($name, $entity, $dataTypeName);
      $field->setRequired((bool) ArrayHelper::value('required', $data, FALSE));
      $field->setTitle(ArrayHelper::value('title', $data));
      $field->setSerialize(ArrayHelper::value('serialize', $data));
    }

    $field->setDefaultValue(ArrayHelper::value('default', $data));
    $field->setDescription(ArrayHelper::value('description', $data));

    $fkAPIName = ArrayHelper::value('FKApiName', $data);
    $fkClassName = ArrayHelper::value('FKClassName', $data);
    if ($fkAPIName || $fkClassName) {
      $field->setFkEntity($fkAPIName ?: TableHelper::getBriefName($fkClassName));
    }

    return $field;
  }

  /**
   * Get the data type from an array. Defaults to 'data_type' with fallback to
   * mapping for the integer value 'type'
   *
   * @param array $data
   *
   * @return string
   */
  private static function getDataType(array $data) {
    if (isset($data['data_type'])) {
      return $data['data_type'];
    }

    $dataTypeInt = ArrayHelper::value('type', $data);
    $dataTypeName = \CRM_Utils_Type::typeToString($dataTypeInt);

    return $dataTypeName;
  }

}
