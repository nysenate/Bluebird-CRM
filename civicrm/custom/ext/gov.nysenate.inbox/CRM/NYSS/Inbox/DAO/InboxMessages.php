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
 * @property int|string $message_id
 * @property int|string $imap_id
 * @property string $sender_name
 * @property string $sender_email
 * @property string $subject
 * @property string $body
 * @property string $forwarder
 * @property int|string $status
 * @property int|string $matcher
 * @property string $format
 * @property string $debug
 * @property string $updated_date
 * @property string $email_date
 * @property string $inbox_source
 */
class CRM_NYSS_Inbox_DAO_InboxMessages extends CRM_NYSS_Inbox_DAO_Base {

  /**
   * Required by older versions of CiviCRM (<5.74).
   * @var string
   */
  public static $_tableName = 'nyss_inbox_messages';

}
