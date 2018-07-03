<?php

namespace Civi\Api4\Service\Spec;

class CustomFieldSpec extends FieldSpec {
  /**
   * @var int
   */
  protected $customFieldId;

  /**
   * @var int
   */
  protected $customGroupId;

  /**
   * @inheritDoc
   */
  public function setDataType($dataType) {
    switch ($dataType) {
      case 'ContactReference':
        $this->setFkEntity('Contact');
        $dataType = 'Integer';
        break;

      case 'File':
      case 'StateProvince':
      case 'Country':
        $this->setFkEntity($dataType);
        $dataType = 'Integer';
        break;
    }
    return parent::setDataType($dataType);
  }

  /**
   * @return int
   */
  public function getCustomFieldId() {
    return $this->customFieldId;
  }

  /**
   * @param int $customFieldId
   *
   * @return $this
   */
  public function setCustomFieldId($customFieldId) {
    $this->customFieldId = $customFieldId;

    return $this;
  }

  /**
   * @return int
   */
  public function getCustomGroupId() {
    return $this->customGroupId;
  }

  /**
   * @param int $customGroupId
   *
   * @return $this
   */
  public function setCustomGroupId($customGroupId) {
    $this->customGroupId = $customGroupId;

    return $this;
  }

}
