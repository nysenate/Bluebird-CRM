<?php

/**
 * This class generates form components for custom data for a case category.
 */
abstract class CRM_Civicase_Form_CaseCategoryTypeCustomField extends CRM_Core_Form {

  /**
   * The entity id, used when editing/creating custom data.
   *
   * @var int
   */
  protected $entityId;

  /**
   * Entity sub type of the table id.
   *
   * @var int
   */
  protected $subTypeId;

  /**
   * Custom group Id.
   *
   * @var int
   */
  protected $groupId;

  /**
   * Entity Table.
   *
   * @var string
   */
  protected $entityTable = 'civicrm_case_type';

  /**
   * Returns entity type for the case category.
   *
   * @return string
   *   Entity type.
   */
  abstract protected function getEntityType();

  /**
   * Sets the appropriate variables needed by the custom data templates.
   */
  public function preProcess() {
    $this->groupId = CRM_Utils_Request::retrieve('groupId', 'Positive', $this, FALSE, NULL);
    $this->entityId = CRM_Utils_Request::retrieve('entityId', 'Positive', $this, TRUE);

    $groupTree = CRM_Core_BAO_CustomGroup::getTree(
      $this->getEntityType(),
      NULL,
      $this->entityId,
      $this->groupId ? $this->groupId : NULL,
      $this->entityId
    );

    // Simplified formatted groupTree.
    $groupTree = CRM_Core_BAO_CustomGroup::formatGroupTree(
      $groupTree,
      1,
      $this
    );

    $hasCustomFieldsAssigned = count($groupTree) !== 0;

    foreach ($groupTree as $groupValues) {
      $pageTitle = count($groupTree) > 1 ? 'Custom Group Fields' : $groupValues['title'];
      break;
    }

    CRM_Utils_System::setTitle(ts('Administer %1', [1 => $pageTitle]));

    $this->_defaults = [];
    CRM_Core_BAO_CustomGroup::setDefaults($groupTree, $this->_defaults);
    $this->setDefaults($this->_defaults);

    CRM_Core_BAO_CustomGroup::buildQuickForm($this, $groupTree);

    // Need to assign custom data type and subtype to the template.
    $this->assign('entityID', $this->entityId);
    $this->assign('groupID', $this->groupId);
    $this->assign('subType', $this->subTypeId);
    $this->assign('hasCustomFieldsAssigned', $hasCustomFieldsAssigned);
  }

  /**
   * {@inheritDoc}
   */
  public function buildQuickForm() {
    $this->addButtons(
      [
        [
          'type' => 'upload',
          'name' => ts('Save'),
          'isDefault' => TRUE,
        ],
        [
          'type' => 'cancel',
          'name' => ts('Cancel'),
        ],
      ]
    );
  }

  /**
   * Process the user submitted custom data values.
   */
  public function postProcess() {
    $params = $this->controller->exportValues($this->_name);
    CRM_Core_BAO_CustomValueTable::postProcess(
      $params,
      $this->entityTable,
      $this->entityId,
      $this->getEntityType()
    );
  }

}
