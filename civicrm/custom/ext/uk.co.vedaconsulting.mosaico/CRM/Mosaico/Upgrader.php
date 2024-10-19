<?php

/**
 * Collection of upgrade steps.
 */
class CRM_Mosaico_Upgrader extends CRM_Extension_Upgrader_Base {

  /**
   * Install module
   */
  public function install() {
    // This would normally be added by Mailing_Template_Category.mgd.php but needs to be present before we save the option value
    // The 'match' param will prevent it from being double-inserted.
    \Civi\Api4\OptionGroup::save(FALSE)
      ->addRecord([
        'name' => 'mailing_template_category',
        'title' => 'Mailing Template Category',
        'is_reserved' => TRUE,
        'is_active' => TRUE,
      ])
      ->setMatch(['name'])
      ->execute();

    $existingCategories = \Civi\Api4\OptionValue::get(FALSE)
      ->selectRowCount()
      ->addWhere('option_group_id.name', '=', 'mailing_template_category')
      ->execute();

    // If there are no categories, insert the default "Newsletter"
    if (!$existingCategories->count()) {
      \Civi\Api4\OptionValue::save(FALSE)
        ->addRecord([
          'option_group_id.name' => 'mailing_template_category',
          'label' => 'Newsletter',
          'value' => '1',
          'name' => 'newsletter',
          'is_default' => TRUE,
        ])
        ->execute();
    }
  }

  /**
   * Add table `civicrm_mosaico_template`.
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_4700() {
    $this->ctx->log->info('Applying update 4700');
    CRM_Core_DAO::executeQuery('CREATE TABLE `civicrm_mosaico_template` (
      `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT \'Unique Template ID\',
      `title` varchar(64)    COMMENT \'Title\',
      `base` varchar(64)    COMMENT \'Name of the Mosaico base template (e.g. versafix-1)\',
      `html` longtext    COMMENT \'Fully renderd HTML\',
      `metadata` longtext    COMMENT \'Mosaico metadata (JSON)\',
      `content` longtext    COMMENT \'Mosaico content (JSON)\' ,
       PRIMARY KEY ( `id` )
    )  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;
    ');
    return TRUE;
  }

  /**
   * One of the 2.x-alpha releases was missing the file `Mosaico.setting.php`.
   * If you installed on Civi v4.6 and tried to change the layout setting while
   * this file was missing, you could have a boinked record in "civicrm_setting".
   */
  public function upgrade_4701() {
    $this->ctx->log->info('Applying update 4701');
    if (CRM_Core_BAO_SchemaHandler::checkIfFieldExists('civicrm_setting', 'group_name')) {
      CRM_Core_DAO::executeQuery('UPDATE civicrm_setting SET group_name = "Mosaico Preferences" WHERE name="mosaico_layout"');
    }
    return TRUE;
  }

  /**
   * Extend civicrm_mosaico_template.title.
   */
  public function upgrade_4702() {
    $this->ctx->log->info('Applying update 4702');
    CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_mosaico_template MODIFY title varchar(255)');
    return TRUE;
  }

  /**
   * Add menu for traditional mailing.
   */
  public function upgrade_4703() {
    $this->ctx->log->info('Applying update 4703');
    $domainId = CRM_Core_Config::domainID();

    civicrm_api3('Navigation', 'create', [
      'sequential' => 1,
      'domain_id' => $domainId,
      'url' => "civicrm/a/#/mailing/new/traditional",
      'permission' => "access CiviMail,create mailings",
      'label' => "New Mailing (Traditional)",
      'permission_operator' => "OR",
      'has_separator' => 0,
      'is_active' => 1,
      'parent_id' => "Mailings",
    ]);

    CRM_Core_Invoke::rebuildMenuAndCaches(TRUE);

    return TRUE;
  }

  /**
   * Add menu for traditional mailing.
   */
  public function upgrade_4704() {
    $this->ctx->log->info('Applying update 4704');

    CRM_Core_DAO::executeQuery('
      ALTER TABLE civicrm_mosaico_template
      ADD COLUMN `msg_tpl_id` int unsigned NULL COMMENT \'FK to civicrm_msg_template.\'
    ');

    CRM_Core_DAO::executeQuery('
      ALTER TABLE civicrm_mosaico_template
      ADD CONSTRAINT FK_civicrm_mosaico_template_msg_tpl_id
      FOREIGN KEY (`msg_tpl_id`) REFERENCES `civicrm_msg_template`(`id`)
      ON DELETE SET NULL
    ');

    CRM_Core_Invoke::rebuildMenuAndCaches(TRUE);

    return TRUE;
  }

  /**
   * Add category_id column.
   */
  public function upgrade_4705() {
    $this->ctx->log->info('Applying update 4705');

    CRM_Core_DAO::executeQuery('
      ALTER TABLE civicrm_mosaico_template
      ADD COLUMN `category_id` int unsigned NULL COMMENT \'ID of the category this mailing template is currently belongs. Foreign key to civicrm_option_value.\'
    ');

    return TRUE;
  }

  /**
   * Convert "Newsletter" category from a managed entity to a one-off insert.
   */
  public function upgrade_4706() {
    $this->ctx->log->info('Applying update 4706');

    // Stop managing the "newsletter" category - it should only be inserted once as a default,
    // but the "managed" thing was preveting the user from deleting it.
    \Civi\Api4\Managed::delete(FALSE)
      ->addWhere('module', '=', 'uk.co.vedaconsulting.mosaico')
      ->addWhere('name', '=', 'OptionGroup_mailing_template_category_newsletter')
      ->execute();

    // This would normally be added by Mailing_Template_Category.mgd.php but needs to be present before we save the option value
    // The 'match' param will prevent it from being double-inserted.
    \Civi\Api4\OptionGroup::save(FALSE)
      ->addRecord([
        'name' => 'mailing_template_category',
        'title' => 'Mailing Template Category',
        'is_reserved' => TRUE,
        'is_active' => TRUE,
      ])
      ->setMatch(['name'])
      ->execute();

    $existingCategories = \Civi\Api4\OptionValue::get(FALSE)
      ->selectRowCount()
      ->addWhere('option_group_id.name', '=', 'mailing_template_category')
      ->execute();

    // If there are no categories, insert the default "Newsletter"
    if (!$existingCategories->count()) {
      \Civi\Api4\OptionValue::save(FALSE)
        ->addRecord([
          'option_group_id.name' => 'mailing_template_category',
          'label' => 'Newsletter',
          'value' => '1',
          'name' => 'newsletter',
          'is_default' => TRUE,
        ])
        ->execute();
    }

    return TRUE;
  }

  /**
   * Add domain_id to the civicrm_mosaico_template table.
  */
  public function upgrade_4707() {
    $this->ctx->log->info('Applying update 4707');

    CRM_Core_DAO::executeQuery('
      ALTER TABLE civicrm_mosaico_template
      ADD COLUMN `domain_id` int unsigned NULL COMMENT \'FK from civicrm_domain.\'
    ');

    CRM_Core_DAO::executeQuery('
      ALTER TABLE civicrm_mosaico_template
      ADD CONSTRAINT FK_civicrm_mosaico_template_domain_id
      FOREIGN KEY (`domain_id`) REFERENCES `civicrm_domain`(`id`)
      ON DELETE SET NULL
    ');

    // Update existing templates with default domain ID
    $domainID = CRM_Core_Config::domainID();
    CRM_Core_DAO::executeQuery("UPDATE civicrm_mosaico_template SET domain_id = {$domainID} WHERE domain_id IS NULL");

    return TRUE;
  }

}
