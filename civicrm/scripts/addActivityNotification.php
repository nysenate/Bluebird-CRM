<?php
/*
 *
 * addScheduledReminder.php
 *
 * Creates 'Activity' related Scheduled Reminder aka ActionSchedule entity based on command line options.
 * A Scheduled Reminder comes in the form of an email notification.
 *
 * Real world examples:
 * 1. An office wants an Activity Assignee to be reminded 2 hours before a scheduled meeting
 * 2. An office wants an Activity Creator to be notified 1 day after an activity was "due"
 *
 * Project: BluebirdCRM
 * Author: Nate Frank
 * Organization: New York State Senate
 * Date: 2026-03-25
 *
 */

error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

define('DEFAULT_LOG_LEVEL', 'INFO');

class CRM_NYSS_Scripts_AddActivityNotification {

  const START_CONDITION_BEFORE = 'before';
  const START_CONDITION_AFTER  = 'after';
  const START_UNIT_DAY         = 'day';
  const START_UNIT_HOUR        = 'hour';
  const START_DATE_ACTIVITY    = 'activity_date_time';
  const MAPPING_ID_ACTIVITY    = 1;
  const RECIPIENT_ASSIGNEE     = 1;

  private $short_opts = 'dvt:l:s:j:ahi:';
  private $long_opts = ['dryrun', 'verbose', 'types=', 'title=', 'statuses=', 'subject=', 'after', 'hours', 'interval='];
  private $user_opts = NULL;
  private ?string $site = NULL;
  private bool $dryrun = FALSE;
  private bool $verbose = FALSE;
  private array $activityTypes = [];
  private array $activityStatuses = [];
  private string $title = 'Activity Notification';
  private string $name = 'activity_notification';
  /** @var string
   *  @note can include tokens such as {activity.activity_type_id:label} or {activity.subject}
   */
  private string $subject = 'Bluebird Activity Notification: {activity.activity_type_id:label}';
  private string $body_html;
  private string $body_text;
  private int $start_offset = 1;
  private string $start_unit = self::START_UNIT_DAY;
  private string $start_condition = self::START_CONDITION_BEFORE;
  /** @var string
   * Not a date.
   */
  private string $start_date = self::START_DATE_ACTIVITY;
  private $bbcfg;

  public function run() {
    require_once 'script_utils.php';
    $this->init();
    $this->execute();
    exit();
  }

  private function execute(): void {
    // Bootstrap verification: fetch the domain name
    $domain = \Civi\Api4\Domain::get(FALSE)
      ->addSelect('name', 'version')
      ->execute()
      ->first();
    echo "Bootstrap OK — domain: {$domain['name']}, CiviCRM version: {$domain['version']}\n";

    $this->ensureUniqueName();

    $this->activityTypes = $this->resolveActivityTypes();
    echo "Selected activity types: " . implode(', ', $this->activityTypes) . "\n";

    $this->activityStatuses = $this->resolveActivityStatuses();
    echo "Selected activity statuses: " . implode(', ', $this->activityStatuses) . "\n";

    // start_unit defaults to day, but can be set to hour with a CLI option.
    $this->start_unit = match(true) {
        !empty($this->user_opts['hours']) => self::START_UNIT_HOUR,
        default => self::START_UNIT_DAY
    };

    // start_condition defaults to before, but can be set to after with a CLI option.
    $this->start_condition = match(true) {
        !empty($this->user_opts['after']) => self::START_CONDITION_AFTER,
        default => self::START_CONDITION_BEFORE
    };

    $values = [
      'name'                  => $this->name,
      'title'                 => $this->title,
      'mapping_id'            => self::MAPPING_ID_ACTIVITY,
      'entity_value'          => $this->activityTypes,
      'entity_status'         => $this->activityStatuses,
      'record_activity'       => TRUE,
      'start_action_offset'   => $this->start_offset,
      'start_action_unit'     => $this->start_unit,
      'start_action_condition' => $this->start_condition,
      'start_action_date'     => $this->start_date,
      'subject'               => $this->subject,
      'body_html'             => $this->body_html,
      'body_text'             => $this->body_text,
      'recipient'             => self::RECIPIENT_ASSIGNEE,
      'is_active'             => TRUE,
    ];

    if ($this->dryrun) {
      echo "\n[Dryrun] Would create ActionSchedule with the following values:\n";
      foreach ($values as $key => $value) {
        $display = match(true) {
          is_bool($value)  => ($value ? 'true' : 'false'),
          is_array($value) => implode(', ', $value),
          default          => $value,
        };
        echo "  {$key}: {$display}\n";
      }
      return;
    }

    $create = \Civi\Api4\ActionSchedule::create(FALSE);
    foreach ($values as $key => $value) {
      $create->addValue($key, $value);
    }
    $result = $create->execute()->first();
    echo "Created ActionSchedule id: {$result['id']}\n";
  }

  /**
   * Checks that $this->name does not already exist in ActionSchedule.
   * If it does, prompts the user for a new title until a unique one is provided.
   */
  private function ensureUniqueName(): void {
    while (TRUE) {
      $existing = \Civi\Api4\ActionSchedule::get(FALSE)
        ->addSelect('id')
        ->addWhere('name', '=', $this->name)
        ->execute()
        ->first();

      if ($existing === NULL) {
        break;
      }

      echo "An ActionSchedule with name '{$this->name}' already exists.\n";
      echo "Enter a new title (or Ctrl+C to abort): ";
      $input = trim(fgets(STDIN));

      if ($input === '') {
        echo "Title cannot be empty.\n";
        continue;
      }

      if (strlen($input) > 50) {
        echo "Title must be 50 characters or fewer (got " . strlen($input) . ").\n";
        continue;
      }

      $this->title = $input;
      $this->name = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $input));
    }
  }

  /**
   * Returns a list of activity type values to use for the schedule.
   * If --types was passed on the command line, parses that comma-separated list.
   * Otherwise, presents an interactive prompt with all active activity types.
   */
  private function resolveActivityTypes(): array {
    // Command-line path
    if (!empty($this->user_opts['types'])) {
      $labels = array_map('trim', explode(',', $this->user_opts['types']));
      return $this->labelsToValues($labels, 'activity_type');
    }

    // Interactive path — fetch all active activity types
    $allTypes = \Civi\Api4\OptionValue::get(FALSE)
      ->addSelect('value', 'label')
      ->addWhere('option_group_id:name', '=', 'activity_type')
      ->addWhere('is_active', '=', TRUE)
      ->addOrderBy('label', 'ASC')
      ->execute();

    $typeMap = [];  // index => ['value' => ..., 'label' => ...]
    echo "\nAvailable activity types:\n";
    $i = 1;
    foreach ($allTypes as $type) {
      echo "  [{$i}] {$type['label']}\n";
      $typeMap[$i] = $type;
      $i++;
    }

    $selected = [];
    echo "\nEnter a number or label to add an activity type. Press Enter with no input when done.\n";
    while (TRUE) {
      echo "Add activity type (or Enter to finish): ";
      $input = trim(fgets(STDIN));

      if ($input === '') {
        if (empty($selected)) {
          echo "No activity types selected. Please select at least one.\n";
          continue;
        }
        break;
      }

      // Accept numeric index or label
      if (is_numeric($input) && isset($typeMap[(int) $input])) {
        $type = $typeMap[(int) $input];
      }
      else {
        // Try matching by label (case-insensitive)
        $type = NULL;
        foreach ($typeMap as $t) {
          if (strcasecmp($t['label'], $input) === 0) {
            $type = $t;
            break;
          }
        }
      }

      if ($type === NULL) {
        echo "  Not found: '{$input}'. Try a number from the list or an exact label.\n";
        continue;
      }

      if (in_array($type['value'], array_column($selected, 'value'))) {
        echo "  Already added: {$type['label']}\n";
        continue;
      }

      $selected[] = $type;
      echo "  Added: {$type['label']}\n";
    }

    return array_column($selected, 'value');
  }

  /**
   * Returns a list of activity status values to use for the schedule.
   * If --statuses was passed on the command line, parses that comma-separated list.
   * Otherwise, presents an interactive prompt with all active activity statuses.
   */
  private function resolveActivityStatuses(): array {
    // Command-line path
    if (!empty($this->user_opts['statuses'])) {
      $labels = array_map('trim', explode(',', $this->user_opts['statuses']));
      return $this->labelsToValues($labels, 'activity_status');
    }

    // Interactive path — fetch all active activity statuses
    $allStatuses = \Civi\Api4\OptionValue::get(FALSE)
      ->addSelect('value', 'label')
      ->addWhere('option_group_id:name', '=', 'activity_status')
      ->addWhere('is_active', '=', TRUE)
      ->addOrderBy('label', 'ASC')
      ->execute();

    $statusMap = [];  // index => ['value' => ..., 'label' => ...]
    echo "\nAvailable activity statuses:\n";
    $i = 1;
    foreach ($allStatuses as $status) {
      echo "  [{$i}] {$status['label']}\n";
      $statusMap[$i] = $status;
      $i++;
    }

    $selected = [];
    echo "\nEnter a number or label to add an activity status. Press Enter with no input when done.\n";
    while (TRUE) {
      echo "Add activity status (or Enter to finish): ";
      $input = trim(fgets(STDIN));

      if ($input === '') {
        if (empty($selected)) {
          echo "No activity statuses selected. Please select at least one.\n";
          continue;
        }
        break;
      }

      // Accept numeric index or label
      if (is_numeric($input) && isset($statusMap[(int) $input])) {
        $status = $statusMap[(int) $input];
      }
      else {
        // Try matching by label (case-insensitive)
        $status = NULL;
        foreach ($statusMap as $s) {
          if (strcasecmp($s['label'], $input) === 0) {
            $status = $s;
            break;
          }
        }
      }

      if ($status === NULL) {
        echo "  Not found: '{$input}'. Try a number from the list or an exact label.\n";
        continue;
      }

      if (in_array($status['value'], array_column($selected, 'value'))) {
        echo "  Already added: {$status['label']}\n";
        continue;
      }

      $selected[] = $status;
      echo "  Added: {$status['label']}\n";
    }

    return array_column($selected, 'value');
  }

  /**
   * Converts an array of option value labels to their corresponding values.
   */
  private function labelsToValues(array $labels, string $optionGroup): array {
    $optionValues = \Civi\Api4\OptionValue::get(FALSE)
      ->addSelect('value', 'label')
      ->addWhere('option_group_id:name', '=', $optionGroup)
      ->addWhere('label', 'IN', $labels)
      ->addWhere('is_active', '=', TRUE)
      ->execute();

    $found = [];
    $foundLabels = [];
    foreach ($optionValues as $ov) {
      $found[] = $ov['value'];
      $foundLabels[] = $ov['label'];
    }

    $missing = array_diff($labels, $foundLabels);
    if (!empty($missing)) {
      echo "Warning: option value(s) not found in '{$optionGroup}': " . implode(', ', $missing) . "\n";
    }

    return $found;
  }

  private function init(): void {
    $this->user_opts = civicrm_script_init($this->short_opts, $this->long_opts);

    if ($this->user_opts === null) {
      $stdusage = civicrm_script_usage();
      $usage = implode(' ', array_map(function($s) {
        $s = rtrim($s, ':');
        return match($s) {
          'types'    => '[--types=Type1,Type2,...]',
          'title'    => '[--title="My Title"]',
          'statuses' => '[--statuses=Status1,Status2,...]',
          'subject'  => '[--subject=Subject]',
          'after'    => '[--after]',
          'hours'    => '[--hours]',
          'interval' => '[--interval=N]',
          default    => '[--' . $s . ']',
        };
      }, $this->long_opts));
      error_log("Usage: " . basename(__FILE__) . "  $stdusage  $usage\n");
      exit(1);
    }

    $this->dryrun = ($this->user_opts['dryrun'] ?? FALSE) ? TRUE : FALSE;
    if ($this->dryrun) {
      echo "Running in --dryrun mode\n";
    }

    $this->verbose = ($this->user_opts['verbose'] ?? FALSE) ? TRUE : FALSE;
    if ($this->verbose) {
      echo "Running in --verbose mode\n";
    }

    if (!empty($this->user_opts['title'])) {
      $title = trim($this->user_opts['title']);
      if (strlen($title) > 50) {
        echo "Error: --title must be 50 characters or fewer (got " . strlen($title) . ").\n";
        exit(1);
      }
      $this->title = $title;
      $this->name = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $title));
    }

    if (isset($this->user_opts['interval'])) {
      $interval = (int) $this->user_opts['interval'];
      if ($interval < 1) {
        echo "Error: --interval must be a positive integer.\n";
        exit(1);
      }
      $this->start_offset = $interval;
    }

    if (!empty($this->user_opts['subject'])) {
      $subject = trim($this->user_opts['subject']);
      if (strlen($subject) > 100) {
          echo "Error: --subject must be 100 characters or fewer (got " . strlen($subject) . ").\n";
          exit(1);
      }
      $this->subject = $subject;
    }

    $this->body_html = <<<EOT
<p>Activity Notification:</p>

<ul>
    <li>Subject:&nbsp;{activity.subject}</li>
    <li>Activity Type:&nbsp;{activity.activity_type_id:label}</li>
    <li>Status:&nbsp;{activity.status_id:label}</li>
    <li>Date:&nbsp;{activity.activity_date_time}</li>
    <li>Link: {nyss.base_url}/civicrm/activity?action=view&amp;reset=1&amp;id={activity.id}</li>
</ul>
EOT;

    $this->body_text = <<<EOT
Activity Notification:

Subject: {activity.subject}
Activity Type: {activity.activity_type_id:label}
Status: {activity.status_id:label}
Date: {activity.activity_date_time}
Link: {nyss.base_url}/civicrm/activity?action=view&reset=1&id={activity.id}
EOT;

    $this->site = $this->user_opts['site'] ?? NULL;

    $this->bbcfg = get_bluebird_instance_config($this->user_opts['site']);

    $civicrm_root = $this->bbcfg['drupal.rootdir'] . '/sites/all/modules/civicrm';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    if (!CRM_Utils_System::loadBootstrap([], FALSE, FALSE, $civicrm_root)) {
      CRM_Core_Error::debug_log_message('Failed to bootstrap CMS from CRM_NYSS_Scripts_AddActivityNotification.');
      throw new Exception('Failed to bootstrap CMS from CRM_NYSS_Scripts_AddActivityNotification');
    }
  }

}

$class = new CRM_NYSS_Scripts_AddActivityNotification();
$class->run();

echo "processing completed.\n";
