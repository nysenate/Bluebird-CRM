<?php
require_once 'ConfigBase.php';

class Config extends ConfigBase {
  /**
   * Available options array.  Structure is:
   *   array( <long_option_name> = array(
   *            'index' => <string>,
   *            'val'   => ( 0 | 1 | 2 ),
   *            'label' => <string>,
   *            'short' => <character>,
   *            'description' => <string>
   *            'required' => <boolean>
   *            )
   *   )
   * 'index' = the index name to use in Config->options
   * 'val' = if a value is required (0 = No, 1 = Yes, 2 = Optional)
   * 'label' = a user-friendly name for this option
   * 'short' = an optional, single-character shortcut (e.g., -h vs --help)
   * 'description' = a user-friendly description for this option
   * 'required' = indicates if this option must be specified (by CLI or file)
   **/


  protected $_available_command_options = array(
    'config-file' => array(
      'val' => self::CONFIG_VALUE_REQUIRED,
      'label' => 'Config file',
      'description' => "File holding all runtime configurations",
      'default' => 'processMailboxes.ini',
    ),
    'server' => array(
      'val' => self::CONFIG_VALUE_REQUIRED,
      'label' => 'IMAP server',
      'short' => 's',
      'description' => "The FQDN or IP of the IMAP server",
      'default' => 'senmail.senate.state.ny.us'
    ),
    'port' => array(
      'val' => self::CONFIG_VALUE_REQUIRED,
      'label' => 'IMAP port',
      'short' => 'p',
      'description' => "The port used to contact the IMAP server",
      'default' => 143,
    ),
    'imap-user' => array(
      'val' => self::CONFIG_VALUE_REQUIRED,
      'label' => 'IMAP username',
      'short' => 'u',
      'description' => "Username to use for the IMAP login",
    ),
    'imap-pass' => array(
      'val' => self::CONFIG_VALUE_REQUIRED,
      'label' => 'IMAP password',
      'short' => 'P',
      'description' => "Password to use for the IMAP login",
    ),
    'imap-flags' => array(
      'val' => self::CONFIG_VALUE_REQUIRED,
      'label' => 'IMAP flags',
      'short' => 'f',
      'description' => "IMAP connection flags to use (see http://php.net/manual/en/function.imap-open.php)",
      'default' => '/imap/notls',
    ),
    'cmd' => array(
      'val' => self::CONFIG_VALUE_REQUIRED,
      'label' => 'Command',
      'short' => 'c',
      'description' => "The command this script should execute (poll|list|delarchive|reprocess)",
      'default' => 'list',
    ),
    'mailbox' => array(
      'val' => self::CONFIG_VALUE_REQUIRED,
      'label' => 'Mailbox',
      'short' => 'm',
      'description' => 'The name of the primary IMAP mailbox',
      'default' => "INBOX",
    ),
    'archivebox' => array(
      'val' => self::CONFIG_VALUE_REQUIRED,
      'label' => 'Archive',
      'short' => 'a',
      'description' => 'The name of the archive IMAP mailbox',
      'default' => 'Archive',
    ),
    'log-level' => array(
      'val' => self::CONFIG_VALUE_REQUIRED,
      'label' => 'Log level',
      'description' => "Used to the set the logging level for the script (FATAL|ERROR|WARN|NOTICE|INFO|DEBUG|TRACE|0-6)",
      'short' => 'l',
    ),
    'unread-only' => array(
      'val' => self::CONFIG_VALUE_NONE,
      'label' => 'Unread Only',
      'description' => 'Ensure the script processes only unread emails',
      'short' => 'r',
    ),
    'no-archive' => array(
      'val' => self::CONFIG_VALUE_NONE,
      'label' => 'No Archive',
      'description' => 'Suppresses the default behavior of archiving messages after processing',
      'short' => 'n',
    ),
    'no-email' => array(
      'val' => self::CONFIG_VALUE_NONE,
      'label' => 'No Email',
      'description' => 'Suppresses sending denial emails to unauthorized senders',
      'short' => 'e',
    ),
    'reproc-select' => array(
      'val' => self::CONFIG_VALUE_OPTIONAL,
      'label' => 'Reprocess Selection',
      'description' => 'Execute the reprocessing algorithms using the supplied filter.  Use --reprocess-help for more information.',
    ),
    'reprocess-help' => array(
      'val' => self::CONFIG_VALUE_NONE,
      'label' => 'Reprocess Help Text',
      'description' => 'Print the long-form help text for reprocessing, and exit.',
    ),
  );
}