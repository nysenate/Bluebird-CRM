<?php

use CRM_Civicase_Service_CaseCategoryMenu as CaseCategoryMenu;
use CRM_Civicase_Service_CaseCategoryCustomDataType as CaseCategoryCustomDataType;
use CRM_Civicase_Service_CaseCategoryCustomFieldExtends as CaseCategoryCustomFieldExtends;

/**
 * Class CRM_Civicase_Service_CaseTypeCategoryEventHandler.
 */
class CRM_Civicase_Service_CaseTypeCategoryEventHandler {

  /**
   * Menu handler.
   *
   * @var \CRM_Civicase_Service_CaseCategoryMenu
   */
  protected $menu;

  /**
   * Custom data handler.
   *
   * @var \CRM_Civicase_Service_CaseCategoryCustomDataType
   */
  protected $customData;

  /**
   * Custom field handler.
   *
   * @var \CRM_Civicase_Service_CaseCategoryCustomFieldExtends
   */
  protected $customFieldExtends;

  /**
   * CRM_Civicase_Service_CaseTypeCategoryEventHandler constructor.
   *
   * @param \CRM_Civicase_Service_CaseCategoryMenu $menu
   *   Menu handler.
   * @param \CRM_Civicase_Service_CaseCategoryCustomDataType $customData
   *   Custom data handler.
   * @param \CRM_Civicase_Service_CaseCategoryCustomFieldExtends $customFieldExtends
   *   Custom field handler.
   */
  public function __construct(CaseCategoryMenu $menu, CaseCategoryCustomDataType $customData, CaseCategoryCustomFieldExtends $customFieldExtends) {
    $this->menu = $menu;
    $this->customData = $customData;
    $this->customFieldExtends = $customFieldExtends;
  }

  /**
   * Perform actions on case type category create.
   *
   * @param string $caseCategoryName
   *   Case type category name.
   */
  public function onCreate($caseCategoryName) {
    if (!$caseCategoryName) {
      return;
    }

    $this->menu->createItems($caseCategoryName);
    $this->customFieldExtends->create($caseCategoryName, "Case ({$caseCategoryName})");
    $this->customData->create($caseCategoryName);
  }

  /**
   * Perform actions on case type category update.
   *
   * @param int $caseCategoryId
   *   Case type category id.
   * @param bool $caseCategoryStatus
   *   (Optional) Case type category status (enabled / disabled).
   * @param string $caseCategoryIcon
   *   (Optional) Case type category icon.
   */
  public function onUpdate($caseCategoryId, $caseCategoryStatus = NULL, $caseCategoryIcon = NULL) {
    if (!$caseCategoryId) {
      return;
    }

    $updateParams = [];
    if (isset($caseCategoryStatus)) {
      $updateParams['is_active'] = !empty($caseCategoryStatus) ? 1 : 0;
    }
    if (isset($caseCategoryIcon)) {
      $updateParams['icon'] = 'crm-i ' . $caseCategoryIcon;
    }

    if (empty($updateParams)) {
      return;
    }

    $this->menu->updateItems($caseCategoryId, $updateParams);
  }

  /**
   * Removes case type category menu item from the civicrm navigation bar.
   *
   * @param string $caseCategoryName
   *   Case type category name.
   */
  public function onDelete($caseCategoryName) {
    if (!$caseCategoryName) {
      return;
    }

    $this->menu->deleteItems($caseCategoryName);
    $this->customFieldExtends->delete($caseCategoryName);
    $this->customData->delete($caseCategoryName);
  }

}
