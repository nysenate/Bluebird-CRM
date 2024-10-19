<?php
use Civi\Api4\ContactLayout;
use Civi\Api4\Navigation;
use CRM_Contactlayout_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Contactlayout_Upgrader extends CRM_Extension_Upgrader_Base {

  /**
   * Install script
   */
  public function install() {
  }

  /**
   * Called just after installation.
   */
  public function postInstall() {
    // Add menu item for contact summary editor.
    try {
      $parent = Navigation::get()
        ->addWhere('name', '=', 'Customize Data and Screens')
        ->setCheckPermissions(FALSE)
        ->setLimit(1)
        ->execute()
        ->first();
      Navigation::create()
        ->setCheckPermissions(FALSE)
        ->addValue('label', E::ts('Contact Summary Layouts'))
        ->addValue('name', 'contact_summary_editor')
        ->addValue('permission', 'administer CiviCRM')
        ->addValue('url', 'civicrm/admin/contactlayout')
        ->addValue('parent_id', $parent['id'])
        ->execute();
    }
    catch (Exception $e) {
      // Couldn't create menu item.
    }
  }

  /**
   * Uninstall routine.
   */
  public function uninstall() {
    try {
      Navigation::delete()
        ->setCheckPermissions(FALSE)
        ->addWhere('name', '=', 'contact_summary_editor')
        ->execute();
    }
    catch (Exception $e) {
      // Couldn't delete menu item.
    }
  }

  /**
   * Change layout format from columns only to rows + columns.
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_1000() {
    $this->ctx->log->info('Applying update 1000 - Change layout format from columns only to rows + columns.');
    foreach (ContactLayout::get()->setSelect(['id', 'blocks'])->setCheckPermissions(FALSE)->execute() as $layout) {
      ContactLayout::update()
        ->addWhere('id', '=', $layout['id'])
        ->addValue('blocks', [$layout['blocks']])
        ->setCheckPermissions(FALSE)
        ->execute();
    }
    return TRUE;
  }

  /**
   * Add support for tabs.
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_1001() {
    $this->ctx->log->info('Applying update 1001 - Add support for tabs.');
    CRM_Core_DAO::executeQuery("ALTER TABLE `civicrm_contact_layout` ADD COLUMN `tabs` longtext COMMENT 'Contains json encoded layout tabs.'");
    return TRUE;
  }

  /**
   * Point menu items to standalone base page.
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_1002() {
    $this->ctx->log->info('Applying update 1002 - Point menu items to standalone base page.');
    CRM_Core_DAO::executeQuery("UPDATE `civicrm_navigation` SET url = 'civicrm/admin/contactlayout' WHERE url LIKE '%contact-summary-editor'");
    return TRUE;
  }

  /**
   * Make tabs optional.
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_1003() {
    $this->ctx->log->info('Applying update 1003 - Make tabs optional.');
    CRM_Core_DAO::executeQuery("ALTER TABLE `civicrm_contact_layout` MODIFY COLUMN `tabs` longtext COMMENT 'Contains json encoded layout tabs.'");
    return TRUE;
  }

}
