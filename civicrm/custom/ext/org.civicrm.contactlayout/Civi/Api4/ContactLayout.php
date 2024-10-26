<?php

namespace Civi\Api4;

/**
 * ContactLayout entity - visual layouts for the contact summary screen.
 *
 * @see https://civicrm.org/extensions/contact-layout-editor
 */
class ContactLayout extends Generic\DAOEntity {

  /**
   * @param bool $checkPermissions
   * @return Action\ContactLayout\GetBlocks
   */
  public static function getBlocks($checkPermissions = TRUE) {
    return (new Action\ContactLayout\GetBlocks(__CLASS__, __FUNCTION__, ['CRM_Contactlayout_BAO_ContactLayout', 'getAllBlocks']))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Action\ContactLayout\GetTabs
   */
  public static function getTabs($checkPermissions = TRUE) {
    return (new Action\ContactLayout\GetTabs(__CLASS__, __FUNCTION__, ['CRM_Contactlayout_BAO_ContactLayout', 'getAllTabs']))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Action\ContactLayout\Replace
   */
  public static function replace($checkPermissions = TRUE) {
    return (new Action\ContactLayout\Replace(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  public static function permissions():array {
    return [
      'get' => ['access CiviCRM'],
    ];
  }

}
