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
 * @property string $email_id
 * @property string $file_name
 * @property string $file_full
 * @property string $rejection
 * @property string $mime_type
 * @property int|string $size
 * @property string $ext
 */
class CRM_NYSS_Inbox_DAO_InboxAttachments extends CRM_NYSS_Inbox_DAO_Base {

  /**
   * Required by older versions of CiviCRM (<5.74).
   * @var string
   */
  public static $_tableName = 'nyss_inbox_attachments';

}
