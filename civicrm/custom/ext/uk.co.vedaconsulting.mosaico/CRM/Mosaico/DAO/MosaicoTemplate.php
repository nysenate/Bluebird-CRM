<?php

/**
 * DAOs provide an OOP-style facade for reading and writing database records.
 *
 * DAOs are a primary source for metadata in older versions of CiviCRM (<5.74)
 * and are required for some subsystems (such as APIv3).
 *
 * This stub provides compatibility. It is not intended to be modified in a
 * substantive way. Property annotations may be added, but are not required.
 * @property string $id
 * @property string $title
 * @property string $base
 * @property string $html
 * @property string $metadata
 * @property string $content
 * @property string $msg_tpl_id
 * @property string $category_id
 * @property string $domain_id
 */
class CRM_Mosaico_DAO_MosaicoTemplate extends CRM_Mosaico_DAO_Base {

  /**
   * Required by older versions of CiviCRM (<5.74).
   * @var string
   */
  public static $_tableName = 'civicrm_mosaico_template';

}
