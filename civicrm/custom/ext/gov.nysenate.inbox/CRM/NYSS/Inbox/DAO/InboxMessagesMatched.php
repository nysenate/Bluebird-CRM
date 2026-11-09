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
 * @property int|string $row_id
 * @property int|string $message_id
 * @property int|string $matched_id
 * @property int|string $activity_id
 */
class CRM_NYSS_Inbox_DAO_InboxMessagesMatched extends CRM_NYSS_Inbox_DAO_Base {

  /**
   * Required by older versions of CiviCRM (<5.74).
   * @var string
   */
  public static $_tableName = 'nyss_inbox_messages_matched';

}
