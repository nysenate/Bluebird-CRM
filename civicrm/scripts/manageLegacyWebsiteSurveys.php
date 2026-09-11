<?php
/*
 *
 * manageLegacyWebsiteSurveys.php
 * NYSS #18999 - Written for this issue, but could be used for similar requests
 *
 * Manages legacy website surveys/questionnaires, which are stored as civicrm custom groups (custom groups with
 * extends=Activity and title matching ^Survey).
 *
 * This only touches the old structure, which is 1 custom group per webform.
 * The new structure (which this script doesn't touch) is all webform survey data is stored in Website_Survey.
 * @see NYSS #16799
 *
 * Usage Notes / Command Line Options
 * --list (-l)                          -- List surveys (default action if no other action given).
 * --enable=<id|name> (-e)              -- Enable one survey by id or exact machine name.
 * --disable=<id|name> (-x)             -- Disable one survey by id or exact machine name.
 * --enable-stale-before=<date> (-E)    -- Enable all surveys with no activity newer than <date>.
 * --disable-stale-before=<date> (-X)   -- Disable all surveys with no activity newer than <date>.
 *                                          A survey with zero activity is judged by when the
 *                                          survey itself was created, not treated as automatically
 *                                          stale, so a brand-new survey with no submissions yet
 *                                          isn't disabled before it's had a chance to be used.
 * --status=<all|enabled|disabled> (-s) -- List filter. Default: all.
 * --newer-than=<date> (-a)             -- List filter: last activity on/after <date>.
 * --older-than=<date> (-o)             -- List filter: last activity on/before <date>, or none ever.
 * --dryrun (-d)                        -- Doesn't update the database. Just reports what it would do.
 * --yes (-y)                           -- Skip the confirmation prompt for bulk enable/disable.
 * --log-level=<level> (-L)             -- Log verbosity (e.g. NOTICE, WARN, ERROR).
 *
 * Project: BluebirdCRM
 * Organization: New York State Senate
 *
 */

error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

#[CRM_NYSS_Attribute_IssueRef('18999')]
class CRM_NYSS_Scripts_ManageLegacyWebsiteSurveys {

  private const EXCLUDED_GROUP_NAMES = ['Website_Survey'];

  // Each short opt at position i corresponds to long opt at position i.
  private string $short_opts = 'le:x:E:X:s:a:o:dL:hy';
  private array $long_opts = [
    'list',
    'enable=',
    'disable=',
    'enable-stale-before=',
    'disable-stale-before=',
    'status=',
    'newer-than=',
    'older-than=',
    'dryrun',
    'log-level=',
    'help',
    'yes',
  ];

  private $user_opts = NULL;
  private string $action = 'list';
  private bool $dryrun = FALSE;
  private bool $yes = FALSE;
  private string $statusFilter = 'all';
  private ?string $newerThan = NULL;
  private ?string $olderThan = NULL;
  private ?string $singleTarget = NULL;
  private ?string $staleDate = NULL;
  private $bbcfg;

  public function run(): void {
    require_once 'script_utils.php';
    $this->init();
    $this->execute();
    exit();
  }

  private function execute(): void {
    switch ($this->action) {
      case 'list':
        $this->doList();
        break;

      case 'enable':
        $this->doSingle(TRUE);
        break;

      case 'disable':
        $this->doSingle(FALSE);
        break;

      case 'enable-stale-before':
        $this->doBulk(TRUE, $this->staleDate);
        break;

      case 'disable-stale-before':
        $this->doBulk(FALSE, $this->staleDate);
        break;
    }
  }

  /**
   * Base "is this a legacy survey" filter, shared by every query in this script.
   * Website_Survey is excluded by name even though its title ("Website Survey")
   * doesn't match the ^Survey regex anyway -- this is deliberate defense-in-depth.
   */
  private function baseSurveyQuery() {
    return \Civi\Api4\CustomGroup::get(FALSE)
      ->addSelect('id', 'name', 'title', 'is_active', 'table_name', 'created_date')
      ->addWhere('extends', '=', 'Activity')
      ->addWhere('title', 'REGEXP', '^Survey')
      ->addWhere('name', 'NOT IN', self::EXCLUDED_GROUP_NAMES)
      ->addOrderBy('title', 'ASC');
  }

  private function getSurveyGroups(array $filters = []): array {
    $query = $this->baseSurveyQuery();

    $status = $filters['status'] ?? 'all';
    if ($status === 'enabled') {
      $query->addWhere('is_active', '=', TRUE);
    }
    elseif ($status === 'disabled') {
      $query->addWhere('is_active', '=', FALSE);
    }

    return (array) $query->execute();
  }

  /**
   * Resolves --enable/--disable's <id|name> argument to a survey custom group,
   * scoped to the same base filter so a non-survey CustomGroup can never be targeted.
   */
  private function resolveSurveyIdentifier(string $idOrName): ?array {
    $query = $this->baseSurveyQuery();

    if (ctype_digit($idOrName)) {
      $query->addWhere('id', '=', (int) $idOrName);
    }
    else {
      $query->addWhere('name', '=', $idOrName);
    }

    return $query->execute()->first();
  }

  /**
   * Guards against interpolating anything but a well-formed custom-data table name into SQL.
   * table_name is system-generated (trusted), but this is cheap defense-in-depth.
   */
  private function assertSafeTableName(string $tableName): bool {
    if (!preg_match('/^civicrm_value_[a-z0-9_]+$/', $tableName)) {
      bbscript_log(LL::ERROR, "Refusing to query unsafe table name '{$tableName}'.");
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Joins a survey's dynamic value table to civicrm_activity via entity_id to compute
   * activity count and first/last activity_date_time. Can't go through API4 since the
   * table name is dynamic per survey.
   */
  private function getActivityStats(string $tableName): array {
    $default = ['count' => 0, 'first' => NULL, 'last' => NULL];

    if (!$this->assertSafeTableName($tableName)) {
      return $default;
    }

    $dao = CRM_Core_DAO::executeQuery("
      SELECT COUNT(*) AS activity_count,
             MIN(a.activity_date_time) AS first_activity,
             MAX(a.activity_date_time) AS last_activity
      FROM civicrm_activity a
      INNER JOIN {$tableName} v ON v.entity_id = a.id
    ");
    $dao->fetch();

    return [
      'count' => (int) $dao->activity_count,
      'first' => $dao->first_activity,
      'last'  => $dao->last_activity,
    ];
  }

  /**
   * Dry-run-aware single toggle. Uses the same CustomGroup::update idiom as the
   * gov.nysenate.webintegration extension's WebIntegration.UpdateSurveyGroups action,
   * so it goes through hooks and cache invalidation rather than raw SQL.
   */
  private function setSurveyActive(array $group, bool $active): void {
    $currentLabel = $group['is_active'] ? 'Enabled' : 'Disabled';
    $newLabel = $active ? 'Enabled' : 'Disabled';

    if ($this->dryrun) {
      echo "[Dryrun] Would set survey '{$group['title']}' (id={$group['id']}, name={$group['name']}) {$currentLabel} -> {$newLabel}\n";
      return;
    }

    \Civi\Api4\CustomGroup::update(FALSE)
      ->addValue('is_active', $active)
      ->addWhere('id', '=', $group['id'])
      ->execute();

    echo "Survey '{$group['title']}' (id={$group['id']}) set to {$newLabel}\n";
  }

  private function doList(): void {
    $groups = $this->getSurveyGroups(['status' => $this->statusFilter]);

    $rows = [];
    foreach ($groups as $group) {
      $stats = $this->getActivityStats($group['table_name']);

      if ($this->newerThan !== NULL) {
        // A survey with no activity ever can't be "newer than" anything.
        if ($stats['last'] === NULL || strtotime($stats['last']) < strtotime("{$this->newerThan} 00:00:00")) {
          continue;
        }
      }

      if ($this->olderThan !== NULL) {
        // A survey with no activity ever is vacuously "not newer than" any date.
        if ($stats['last'] !== NULL && strtotime($stats['last']) > strtotime("{$this->olderThan} 23:59:59")) {
          continue;
        }
      }

      $rows[] = [
        'id'        => $group['id'],
        'name'      => $group['name'],
        'title'     => $group['title'],
        'count'     => $stats['count'],
        'first'     => $stats['first'],
        'last'      => $stats['last'],
        'is_active' => (bool) $group['is_active'],
      ];
    }

    if (empty($rows)) {
      echo "No surveys found matching filters.\n";
      return;
    }

    $this->printSurveyTable($rows);
  }

  private function doSingle(bool $enable): void {
    $group = $this->resolveSurveyIdentifier($this->singleTarget);
    if ($group === NULL) {
      echo "Error: no survey found with id or name '{$this->singleTarget}'.\n";
      exit(1);
    }

    $label = $enable ? 'Enabled' : 'Disabled';
    if ((bool) $group['is_active'] === $enable) {
      echo "Survey '{$group['title']}' (id={$group['id']}) is already {$label}.\n";
      return;
    }

    $this->setSurveyActive($group, $enable);
  }

  private function doBulk(bool $enable, string $thresholdDate): void {
    $groups = $this->getSurveyGroups([]);

    $eligible = [];
    foreach ($groups as $group) {
      $stats = $this->getActivityStats($group['table_name']);

      // A survey with recorded activity is judged on its last activity date. A survey with
      // NO activity yet is judged on when the survey itself was created, not treated as
      // automatically eligible -- otherwise a brand-new survey with no submissions yet
      // would be swept up and disabled before it ever had a chance to be used.
      if ($stats['last'] !== NULL) {
        $isEligible = strtotime($stats['last']) <= strtotime("{$thresholdDate} 23:59:59");
      }
      elseif ($group['created_date'] !== NULL) {
        $isEligible = strtotime($group['created_date']) <= strtotime("{$thresholdDate} 23:59:59");
      }
      else {
        // Unknown creation date and no activity -- don't guess, exclude from bulk changes.
        $isEligible = FALSE;
      }
      if (!$isEligible) {
        continue;
      }

      $eligible[] = [
        'group'        => $group,
        'stats'        => $stats,
        'would_change' => (bool) $group['is_active'] !== $enable,
      ];
    }

    if (empty($eligible)) {
      echo "No surveys are eligible (none with last activity on or before {$thresholdDate}, and no survey with zero activity was created on or before that date either).\n";
      return;
    }

    $this->printBulkChangeTable($eligible, $enable);

    $toChange = array_values(array_filter($eligible, fn($e) => $e['would_change']));
    $alreadyCorrect = count($eligible) - count($toChange);
    $label = $enable ? 'enabled' : 'disabled';

    echo "\n" . count($toChange) . " would be changed, {$alreadyCorrect} already {$label} (skipped), " . count($eligible) . " total eligible.\n";

    if (empty($toChange) || $this->dryrun) {
      return;
    }

    if (!$this->yes) {
      echo "Proceed with " . ($enable ? 'enabling' : 'disabling') . " " . count($toChange) . " surveys? (y/n): ";
      $input = strtolower(trim(fgets(STDIN)));
      if ($input !== 'y') {
        echo "Aborted.\n";
        return;
      }
    }

    foreach ($toChange as $entry) {
      $this->setSurveyActive($entry['group'], $enable);
    }
  }

  private function validateDate(string $date): string {
    $dt = DateTime::createFromFormat('Y-m-d', $date);
    if ($dt === FALSE || $dt->format('Y-m-d') !== $date) {
      echo "Error: invalid date '{$date}'; expected format YYYY-MM-DD.\n";
      exit(1);
    }
    return $date;
  }

  private function printSurveyTable(array $rows): void {
    $hr = str_repeat('-', 110);
    $col = "%-6s | %-20s | %-40s | %10s | %-19s | %-19s | %-8s\n";

    echo "\n$hr\n";
    echo "\033[1mLegacy Website Surveys\033[0m\n";
    echo "$hr\n";
    echo "\033[1m";
    printf($col, 'ID', 'Name', 'Title', 'Activities', 'First Activity', 'Last Activity', 'Status');
    echo "\033[0m" . str_repeat('-', 110) . "\n";

    foreach ($rows as $row) {
      printf(
        $col,
        $row['id'],
        substr($row['name'], 0, 20),
        substr($row['title'], 0, 40),
        $row['count'],
        $row['first'] ?? '(no activity)',
        $row['last'] ?? '(no activity)',
        $row['is_active'] ? 'Enabled' : 'Disabled'
      );
    }

    echo "$hr\n";
    echo count($rows) . " survey(s) shown.\n";
  }

  private function printBulkChangeTable(array $eligible, bool $enable): void {
    $hr = str_repeat('-', 128);
    $col = "%-6s | %-20s | %-32s | %-19s | %-19s | %-10s | %-10s\n";
    $newLabel = $enable ? 'Enabled' : 'Disabled';

    echo "\n$hr\n";
    echo "\033[1mBulk " . ($enable ? 'Enable' : 'Disable') . " -- surveys with no activity newer than the threshold date\033[0m\n";
    echo "$hr\n";
    echo "\033[1m";
    printf($col, 'ID', 'Name', 'Title', 'Last Activity', 'Survey Created', 'Current', 'New');
    echo "\033[0m" . str_repeat('-', 128) . "\n";

    foreach ($eligible as $entry) {
      $group = $entry['group'];
      $stats = $entry['stats'];
      $currentLabel = $group['is_active'] ? 'Enabled' : 'Disabled';

      printf(
        $col,
        $group['id'],
        substr($group['name'], 0, 20),
        substr($group['title'], 0, 32),
        $stats['last'] ?? '(no activity)',
        // Only meaningful (and only used for eligibility) when there's no activity.
        $stats['last'] === NULL ? ($group['created_date'] ?? '(unknown)') : '-',
        $currentLabel,
        $entry['would_change'] ? $newLabel : $currentLabel
      );
    }

    echo "$hr\n";
  }

  private function print_help(): void {
    $script = basename(__FILE__);
    echo <<<HELP
Usage: php $script -S <site> [OPTIONS]

Manages legacy website "Surveys". Each legacy website survey is a custom groups (civicrm_custom_group rows with extends=Activity and title matching ^Survey, one custom group per web form). 

The consolidated Website_Survey custom group, which is the new structure for website survey submissions sis not handled by this script.

Actions (mutually exclusive; default is --list):
  -l, --list                          List surveys
  -e, --enable=<id|name>              Enable one survey by id or exact machine name
  -x, --disable=<id|name>             Disable one survey by id or exact machine name
  -E, --enable-stale-before=<date>    Enable all surveys with no activity newer than <date>
  -X, --disable-stale-before=<date>   Disable all surveys with no activity newer than <date>
                                         A survey with zero activity is judged by when the
                                         survey itself was created, not treated as
                                         automatically stale -- this protects a brand-new
                                         survey with no submissions yet from being disabled
                                         before it's had a chance to be used.

List filters (only apply with --list):
  -s, --status=<all|enabled|disabled>   Default: all
  -a, --newer-than=<date>               Last activity on/after <date>
  -o, --older-than=<date>               Last activity on/before <date>, or none ever

Other:
  -d, --dryrun            Do not write to the database; print what would change
  -y, --yes               Skip confirmation prompt for bulk enable/disable
  -L, --log-level=<level>
  -h, --help               Show this help message and exit

Dates must be in YYYY-MM-DD format.

HELP;
  }

  /**
   * Checks $argv directly for -h/--help rather than using getopt(), because getopt()
   * stops parsing as soon as it hits an option it doesn't recognize (e.g. -S/--site,
   * which isn't part of this script's own $short_opts/$long_opts -- it's only added
   * later, inside civicrm_script_init()). That made --help silently get dropped
   * whenever it appeared after --site on the command line.
   */
  private function isHelpRequested(): bool {
    foreach (($_SERVER['argv'] ?? []) as $arg) {
      if ($arg === '-h' || $arg === '--help') {
        return TRUE;
      }
    }
    return FALSE;
  }

  private function init(): void {
    if ($this->isHelpRequested()) {
      $this->print_help();
      exit(0);
    }

    $this->user_opts = civicrm_script_init($this->short_opts, $this->long_opts);

    if ($this->user_opts === NULL) {
      $stdusage = civicrm_script_usage();
      $usage = implode(' ', array_map(fn($s) => '[--' . rtrim($s, '=') . ']', $this->long_opts));
      error_log("Usage: " . basename(__FILE__) . " $stdusage $usage");
      exit(1);
    }

    if (!empty($this->user_opts['log-level'])) {
      set_bbscript_log_level($this->user_opts['log-level']);
    }

    $this->dryrun = !empty($this->user_opts['dryrun']);
    if ($this->dryrun) {
      echo "Running in --dryrun mode\n";
    }

    $this->yes = !empty($this->user_opts['yes']);

    // Determine which single action was requested; enforce mutual exclusivity.
    $actionKeys = ['list', 'enable', 'disable', 'enable-stale-before', 'disable-stale-before'];
    $present = [];
    foreach ($actionKeys as $key) {
      if (!empty($this->user_opts[$key])) {
        $present[] = $key;
      }
    }
    if (count($present) > 1) {
      echo "Error: --" . implode(', --', $present) . " are mutually exclusive.\n";
      exit(1);
    }
    $this->action = $present[0] ?? 'list';

    // List filters only apply to --list; warn (don't fail) if given alongside another action.
    if ($this->action !== 'list') {
      foreach (['status', 'newer-than', 'older-than'] as $filterKey) {
        if (($this->user_opts[$filterKey] ?? NULL) !== NULL) {
          echo "Warning: --{$filterKey} only applies to --list; ignoring.\n";
        }
      }
    }

    $this->statusFilter = $this->user_opts['status'] ?? 'all';
    if (!in_array($this->statusFilter, ['all', 'enabled', 'disabled'], TRUE)) {
      echo "Error: --status must be one of: all, enabled, disabled (got '{$this->statusFilter}').\n";
      exit(1);
    }

    if ($this->action === 'list') {
      if (($this->user_opts['newer-than'] ?? NULL) !== NULL) {
        $this->newerThan = $this->validateDate($this->user_opts['newer-than']);
      }
      if (($this->user_opts['older-than'] ?? NULL) !== NULL) {
        $this->olderThan = $this->validateDate($this->user_opts['older-than']);
      }
    }
    elseif ($this->action === 'enable') {
      $this->singleTarget = trim((string) $this->user_opts['enable']);
    }
    elseif ($this->action === 'disable') {
      $this->singleTarget = trim((string) $this->user_opts['disable']);
    }
    elseif ($this->action === 'enable-stale-before') {
      $this->staleDate = $this->validateDate(trim((string) $this->user_opts['enable-stale-before']));
    }
    elseif ($this->action === 'disable-stale-before') {
      $this->staleDate = $this->validateDate(trim((string) $this->user_opts['disable-stale-before']));
    }

    $this->bbcfg = get_bluebird_instance_config($this->user_opts['site']);

    $civicrm_root = $this->bbcfg['drupal.rootdir'] . '/sites/all/modules/civicrm';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    if (!CRM_Utils_System::loadBootstrap([], FALSE, FALSE, $civicrm_root)) {
      CRM_Core_Error::debug_log_message('Failed to bootstrap CMS from CRM_NYSS_Scripts_ManageLegacyWebsiteSurveys.');
      throw new Exception('Failed to bootstrap CMS from CRM_NYSS_Scripts_ManageLegacyWebsiteSurveys');
    }
  }

}

$script = new CRM_NYSS_Scripts_ManageLegacyWebsiteSurveys();
$script->run();

echo "processing completed.\n";
