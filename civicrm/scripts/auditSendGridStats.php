<?php

error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

/**
 * Audit the SendGrid accumulator archive against Bluebird mailing event tables.
 *
 * Looks for the following anamolies:
 *   1. ARCHIVED in the accumulator but no corresponding Bluebird record.
 *   2. FAILED in the accumulator but a Bluebird record exists.
 *      This indicates an inconsistency. If there's a row in Bluebird, then why would it be a failure?
 *   3. Rows in the incoming table that have one or more matching record in Bluebird.
 *      This indicates that a row in the stats accumulator is failing to be archived -- potentially left in
 *      incoming to be processed over and over again.
 *   4. Duplicate Bluebird event rows for the same queue_id, caused by repeated
 *      processing of events that failed archival before the transaction fix. (See #3)
 *
 * Pass --instance with the servername as it appears in instance.servername.
 * Use  --fix to delete duplicate Bluebird records. Run without --fix first to review what would be changed.
 */
class CRM_NYSS_Scripts_AuditSendGridStats {

  // Each short opt at position i corresponds to long opt at position i.
  private string $short_opts = 'i:s:e:fl:h';
  private array $long_opts = [
    'instance=',
    'since=',
    'event-type=',
    'fix',
    'log-level=',
    'help',
  ];

  private        $user_opts    = NULL;
  private string $instance_name;
  private string $since;
  private ?string $filter_type = NULL;
  private bool   $fix          = FALSE;
  private ?mysqli $dbcon        = NULL;
  private array  $bbcfg;

  // Results populated by run_audit(), run_check_incoming_with_civi(), and run_check_duplicates().
  // Keyed by mailing_id; $dup_report is also kept for run_fix_duplicates().
  private array $mailing_report = [];
  private array $dup_report     = [];
  private array $dup_detail     = []; // [mailing_id => [event_type_label => count]]

  /**
   * Maps event types → CiviCRM table(s).
   * (dropped events route to bounce or unsubscribe depending on reason).
   */
  private const EVENT_TABLE_MAP = [
    'delivered'   => 'civicrm_mailing_event_sendgrid_delivered',
    'open'        => 'civicrm_mailing_event_opened',
    'bounce'      => 'civicrm_mailing_event_bounce',
    'click'       => 'civicrm_mailing_event_trackable_url_open',
    'unsubscribe' => 'civicrm_mailing_event_unsubscribe',
    'spamreport'  => 'civicrm_mailing_event_unsubscribe',
    'dropped'     => ['civicrm_mailing_event_bounce', 'civicrm_mailing_event_unsubscribe'],
    // 'deferred' and 'processed' write no CiviCRM records and are not audited.
  ];

  /** These tables could possibly contain duplicates. So, don't bother running a duplicate check on them */
  private const SKIP_DUP_TABLES = [
    'civicrm_mailing_event_trackable_url_open',
    'civicrm_mailing_event_opened',
  ];

  /** Maps CiviCRM event tables → readable event type label for reporting. */
  private const TABLE_LABEL_MAP = [
    'civicrm_mailing_event_sendgrid_delivered' => 'delivered',
    'civicrm_mailing_event_opened'             => 'open',
    'civicrm_mailing_event_bounce'             => 'bounce',
    'civicrm_mailing_event_trackable_url_open' => 'click',
    'civicrm_mailing_event_unsubscribe'        => 'unsubscribe',
  ];

  /** Skip these tables when --fix is used. Don't fix these tables. */
  private const SKIP_FIX_TABLES = [
    'civicrm_mailing_event_trackable_url_open',
  ];


  public function run(): void {
    require_once 'script_utils.php';
    require_once 'accumulatorEvents.inc.php';
    $this->init();

    $this->run_audit();
    $this->run_check_incoming_with_bb();
    $this->run_check_duplicates();

    if ($this->fix) {
      $this->run_fix_duplicates();
      $this->reset_dup_state();
      $this->run_check_duplicates();
    }

    $this->print_summary();
    exit(0);
  }


  private function init(): void {
    $raw = getopt($this->short_opts, $this->long_opts);
    if (isset($raw['h']) || isset($raw['help'])) {
      $this->print_help();
      exit(0);
    }

    $this->user_opts = civicrm_script_init($this->short_opts, $this->long_opts);

    if ($this->user_opts === NULL) {
      $stdusage = civicrm_script_usage();
      $usage    = implode(' ', array_map(fn($s) => '[--' . rtrim($s, '=') . ']', $this->long_opts));
      error_log("Usage: " . basename(__FILE__) . " $stdusage $usage");
      exit(1);
    }

    if (!empty($this->user_opts['log-level'])) {
      set_bbscript_log_level($this->user_opts['log-level']);
    }

    $this->instance_name = $this->user_opts['instance'] ?? '';
    if (!$this->instance_name) {
      error_log(basename(__FILE__) . ": --instance is required (the servername as it appears in the accumulator's instance table)");
      exit(1);
    }

    $this->since       = $this->user_opts['since'] ?? date('Y-m-d', strtotime('-30 days'));
    $this->filter_type = $this->user_opts['event-type'] ?? NULL;
    $this->fix = !empty($this->user_opts['fix']);

    if ($this->filter_type !== NULL && !array_key_exists($this->filter_type, self::EVENT_TABLE_MAP)) {
      $valid = implode(', ', array_keys(self::EVENT_TABLE_MAP));
      error_log("Unknown event type '{$this->filter_type}'. Valid: $valid");
      exit(1);
    }

    $this->bbcfg = get_bluebird_instance_config();

    $this->dbcon = get_accumulator_connection($this->bbcfg);
    if (!$this->dbcon) {
      error_log("Could not connect to accumulator database.");
      exit(1);
    }

    // Verify the instance name resolves in the accumulator.
    if (!$this->verify_instance()) {
      error_log(basename(__FILE__) . ": no instance with servername '{$this->instance_name}' found in the accumulator.");
      exit(1);
    }

    $config = CRM_Core_Config::singleton();
  }

    /**
     * Verify that the given --instance exists in the accumulator database.
     * @return bool
     */
  private function verify_instance(): bool {
      $stmt = mysqli_prepare($this->dbcon, "SELECT id FROM instance WHERE servername = ?");
      mysqli_stmt_bind_param($stmt, 's', $this->instance_name);
      mysqli_stmt_execute($stmt);
      $inst_result     = mysqli_stmt_get_result($stmt);
      $instance_exists = $inst_result && mysqli_num_rows($inst_result) > 0;
      mysqli_stmt_close($stmt);
      return $instance_exists;
  }

  private function run_audit(): void {
    $event_table_map = $this->filter_type
      ? [$this->filter_type => self::EVENT_TABLE_MAP[$this->filter_type]]
      : self::EVENT_TABLE_MAP;

    bbscript_log(LL::DEBUG, "Auditing instance '{$this->instance_name}' since {$this->since}.");

    $sql_tpl = "
      SELECT a.event_id, a.queue_id, a.result
      FROM archive a
      JOIN message  m ON a.message_id  = m.id
      JOIN instance i ON m.instance_id = i.id
      WHERE i.servername   = ?
        AND a.event_type   = ?
        AND a.result       IN ('ARCHIVED', 'FAILED')
        AND a.dt_processed >= ?
      ORDER BY a.event_id
    ";

    foreach ($event_table_map as $event_type => $event_table) {
      bbscript_log(LL::DEBUG, "Checking '$event_type' events...");

      $archived_qids = [];
      $failed_qids   = [];

      $stmt = mysqli_prepare($this->dbcon, $sql_tpl);
      if (!$stmt) {
        bbscript_log(LL::ERROR, "Failed to prepare archive query: " . mysqli_error($this->dbcon));
        continue;
      }
      mysqli_stmt_bind_param($stmt, 'sss', $this->instance_name, $event_type, $this->since);
      mysqli_stmt_execute($stmt);
      $result = mysqli_stmt_get_result($stmt);
      while ($row = mysqli_fetch_assoc($result)) {
        if ($row['result'] === 'ARCHIVED') {
          $archived_qids[] = (int)$row['queue_id'];
        } else {
          $failed_qids[] = (int)$row['queue_id'];
        }
      }
      mysqli_stmt_close($stmt);

      $archived_missing = [];
      $missing_from_bb = [];

      if (!empty($archived_qids)) {
        $found            = $this->get_bb_event_ids($archived_qids, $event_table);
        $archived_missing = array_values(array_diff($archived_qids, $found));
      }

      if (!empty($failed_qids)) {
        $missing_from_bb = $this->get_bb_event_ids($failed_qids, $event_table);
      }

      if (!empty($archived_missing)) {
        bbscript_log(LL::DEBUG, count($archived_missing) . " ARCHIVED $event_type events have no Bluebird record.");
        $qid_to_mailing = $this->get_mailing_ids_for_queues($archived_missing);
        foreach ($archived_missing as $qid) {
          $mid = $qid_to_mailing[$qid] ?? 0;
          if ($mid === 0) continue;
          $this->mailing_entry($mid);
          $this->mailing_report[$mid]['missing']++;
        }
      }

      if (!empty($missing_from_bb)) {
        bbscript_log(LL::DEBUG, count($missing_from_bb) . " FAILED $event_type events have an unexpected Bluebird record (possible pre-fix inflation).");
        $qid_to_mailing = $this->get_mailing_ids_for_queues($missing_from_bb);
        foreach ($missing_from_bb as $qid) {
          $mid = $qid_to_mailing[$qid] ?? 0;
          if ($mid === 0) continue;
          $this->mailing_entry($mid);
          $this->mailing_report[$mid]['unexplained_fails']++;
        }
      }
    }
  }

  private function run_check_incoming_with_bb(): void {
    bbscript_log(LL::DEBUG, "Checking for incoming events that already have a Bluebird record...");

    $event_table_map = $this->filter_type
      ? [$this->filter_type => self::EVENT_TABLE_MAP[$this->filter_type]]
      : self::EVENT_TABLE_MAP;

    foreach ($event_table_map as $event_type => $table_name) {

      $sql = "
        SELECT incoming.queue_id, incoming.mailing_id
        FROM incoming
        WHERE incoming.servername = ?
          AND incoming.event_type = ?
          AND incoming.dt_received >= ?
      ";

      $stmt = mysqli_prepare($this->dbcon, $sql);
      if (!$stmt) {
        bbscript_log(LL::ERROR, "Failed to prepare incoming query for $event_type: " . mysqli_error($this->dbcon));
        continue;
      }
      mysqli_stmt_bind_param($stmt, 'sss', $this->instance_name, $event_type, $this->since);
      mysqli_stmt_execute($stmt);
      $result = mysqli_stmt_get_result($stmt);

      $incoming_qids  = [];
      $qid_to_mailing = [];
      while ($row = mysqli_fetch_assoc($result)) {
        $qid                   = (int)$row['queue_id'];
        $incoming_qids[]       = $qid;
        $qid_to_mailing[$qid]  = (int)$row['mailing_id'];
      }
      mysqli_stmt_close($stmt);

      $found = [];
      if (!empty($incoming_qids)) {
        $found = $this->get_bb_event_ids($incoming_qids, $table_name);
      }

      if (!empty($found)) {
        bbscript_log(LL::DEBUG, count($found) . " incoming $event_type events already have a Bluebird record (re-processing risk).");
        foreach ($found as $qid) {
          $mid = $qid_to_mailing[$qid] ?? 0;
          if ($mid === 0) continue;
          $this->mailing_entry($mid);
          $this->mailing_report[$mid]['unarchived']++;
        }
      }
    }
  }


  private function run_check_duplicates(): void {
    bbscript_log(LL::DEBUG, "Checking for duplicate Bluebird event records...");

    $event_table_map = $this->filter_type
      ? [$this->filter_type => self::EVENT_TABLE_MAP[$this->filter_type]]
      : self::EVENT_TABLE_MAP;

    $checked_tables = [];
    foreach ($event_table_map as $table_name) {
      $tables = is_array($table_name) ? $table_name : [$table_name];
      foreach ($tables as $table) {
        if (in_array($table, $checked_tables) || in_array($table, self::SKIP_DUP_TABLES)) {
          continue;
        }
        $checked_tables[] = $table;

        $result = CRM_Core_DAO::executeQuery("
          SELECT t.event_queue_id, COUNT(*) AS cnt
          FROM $table t
          JOIN civicrm_mailing_event_queue meq ON meq.id = t.event_queue_id
          JOIN civicrm_mailing m ON m.id = meq.mailing_id
          WHERE meq.is_test = 0
            AND m.status = 'Complete'
          GROUP BY t.event_queue_id
          HAVING cnt > 1
          ORDER BY cnt DESC
        ");
        $dups = [];
        while ($result->fetch()) {
          $dups[(int)$result->event_queue_id] = (int)$result->cnt;
        }

        if (!empty($dups)) {
          $this->dup_report[$table] = $dups;
          bbscript_log(LL::DEBUG, count($dups) . " queue_ids with duplicate records found in $table.");
        }
      }
    }

    // Attribute duplicate queue_ids to their mailings (deduplicated across tables).
    if (!empty($this->dup_report)) {
      $all_dup_qids   = array_unique(array_merge(...array_values(array_map('array_keys', $this->dup_report))));
      $qid_to_mailing = $this->get_mailing_ids_for_queues($all_dup_qids);
      $mailing_dup_qids = [];
      foreach ($this->dup_report as $table => $dups) {
        $label = self::TABLE_LABEL_MAP[$table] ?? $table;
        foreach (array_keys($dups) as $qid) {
          $mid = $qid_to_mailing[$qid] ?? 0;
          if ($mid === 0) continue;
          $mailing_dup_qids[$mid][$qid] = TRUE;
          $this->dup_detail[$mid][$label] = ($this->dup_detail[$mid][$label] ?? 0) + 1;
        }
      }
      foreach ($mailing_dup_qids as $mid => $qids) {
        $this->mailing_entry($mid);
        $this->mailing_report[$mid]['duplicates'] = count($qids);
      }
    }
  }

  private function reset_dup_state(): void {
    $this->dup_report = [];
    $this->dup_detail = [];
    foreach ($this->mailing_report as $mid => $_) {
      $this->mailing_report[$mid]['duplicates'] = 0;
    }
  }


  private function run_fix_duplicates(): void {
    if (empty($this->dup_report)) {
      echo "No duplicates to fix.\n";
      return;
    }

    echo "WARNING: The --fix operation is destructive and will permanently delete database records.\n";
    echo "Have you taken a backup? Do you want to proceed? (y/n): ";
    $input = strtolower(trim(fgets(STDIN)));
    if ($input !== 'y') {
      echo "Aborted.\n";
      return;
    }

    foreach ($this->dup_report as $table => $dups) {
      if (in_array($table, self::SKIP_FIX_TABLES)) {
        continue;
      }

      // Scope to exactly the queue_ids that were reported — already filtered for
      // is_test = 0 and status = 'Complete' by the detection query.
      $dup_qids = implode(',', array_map('intval', array_keys($dups)));

      $to_delete = (int)CRM_Core_DAO::singleValueQuery("
        SELECT COUNT(*)
        FROM $table t1
        JOIN $table t2 ON t1.event_queue_id = t2.event_queue_id AND t1.id > t2.id
        WHERE t1.event_queue_id IN ($dup_qids)
      ");

      bbscript_log(LL::DEBUG, "Deleting $to_delete duplicate rows from $table...");
      $tx = new CRM_Core_Transaction();
      try {
        CRM_Core_DAO::executeQuery("
          DELETE t1
          FROM $table t1
          JOIN $table t2 ON t1.event_queue_id = t2.event_queue_id AND t1.id > t2.id
          WHERE t1.event_queue_id IN ($dup_qids)
        ");
        unset($tx);
        bbscript_log(LL::INFO, "Deleted $to_delete rows from $table.");
      }
      catch (Exception $e) {
        $tx->rollback();
        unset($tx);
        bbscript_log(LL::ERROR, "Failed to fix duplicates in $table: " . $e->getMessage());
      }
    }
  }


  private function get_bb_event_ids(array $queue_ids, $table_name): array {
    if (empty($queue_ids)) {
      return [];
    }

    if (is_array($table_name)) {
      $found = [];
      foreach ($table_name as $table) {
        $found = array_merge($found, $this->get_bb_event_ids($queue_ids, $table));
      }
      return array_values(array_unique($found));
    }

    $ids    = implode(',', $queue_ids);
    $result = CRM_Core_DAO::executeQuery(
      "SELECT DISTINCT event_queue_id FROM $table_name WHERE event_queue_id IN ($ids)"
    );
    $found  = [];
    while ($result->fetch()) {
      $found[] = (int)$result->event_queue_id;
    }
    return $found;
  }


  private function mailing_entry(int $mid): void {
    $this->mailing_report[$mid] ??= [
      'missing'      => 0,
      'unexplained_fails' => 0,
      'unarchived'        => 0,
      'duplicates'        => 0,
    ];
  }

  private function get_mailing_ids_for_queues(array $queue_ids): array {
    if (empty($queue_ids)) {
      return [];
    }
    $ids    = implode(',', array_map('intval', $queue_ids));
    $result = CRM_Core_DAO::executeQuery("
      SELECT meq.id, meq.mailing_id
      FROM civicrm_mailing_event_queue meq
      JOIN civicrm_mailing m ON m.id = meq.mailing_id
      WHERE meq.id IN ($ids)
        AND meq.is_test = 0
        AND m.status = 'Complete'
    ");
    $map = [];
    while ($result->fetch()) {
      $map[(int)$result->id] = (int)$result->mailing_id;
    }
    return $map;
  }

  private function print_help(): void {
    $script = basename(__FILE__);
    echo <<<HELP
Usage: php $script -S <site> --instance <servername> [OPTIONS]

Audits the SendGrid accumulator archive against Bluebird mailing event tables.

Required:
  -S <site>                  Bluebird site name (e.g. bb310)
  --instance <servername>    Accumulator instance servername (e.g. bb310.nysenate.gov)

Options:
  --since <date>             Check events on or after this date (default: 30 days ago)
                               Does not apply to --check-duplicates audit
  --event-type <type>        Limit audit to one event type:
                               delivered, open, bounce, click, unsubscribe, spamreport, dropped
  --fix                      Delete duplicate Bluebird records found in Bluebird event tables.
                             Prompts for confirmation before deleting. Run without --fix first.
  --log-level <level>        Log verbosity (e.g. NOTICE, WARN, ERROR)
  -h, --help                 Show this help message and exit

Anomalies detected:
  1. MISSING - Archived in accumulator with no matching Bluebird event
  2. FAILED -Events failed in accumulator, but Bluebird does have an event
  3. STUCK - Events stuck in incoming, indicated by an existing Bluebird event (re-processing risk)
  4. DUPLICATED - Bluebird event rows duplicated for the same queue_id

HELP;
  }

  private function print_summary(): void {
    $hr = str_repeat('-', 80);

    echo "\n$hr\n";
    echo "\033[1mMailing Stats Audit Report\033[0m\n";
    echo "Instance : {$this->instance_name}\n";
    echo "Period   : {$this->since} to now\n";
    echo "$hr\n\n";

    if (empty($this->mailing_report)) {
      echo "No anomalies found.\n\n$hr\n";
      return;
    }

    // Fetch mailing names.
    $ids_sql = implode(',', array_map('intval', array_keys($this->mailing_report)));
    $r       = CRM_Core_DAO::executeQuery("SELECT id, name FROM civicrm_mailing WHERE id IN ($ids_sql) AND status = 'Complete'");
    $names   = [];
    while ($r->fetch()) {
      $names[(int)$r->id] = $r->name;
    }

    ksort($this->mailing_report);

    // --- Table 1: Bluebird Anomalies ---
    $bb_rows = array_filter($this->mailing_report,
      fn($s) => $s['missing'] > 0 || $s['duplicates'] > 0
    );

    echo "\033[1mBluebird Anomalies\033[0m\n$hr\n";
    if (empty($bb_rows)) {
      echo "None found.\n";
    }
    else {
      $col = "%-10s | %-27s | %17s | %17s\n";
      echo "\033[1m";
      printf($col, 'Mailing ID', 'Mailing Name', 'Missing', 'Duplicated');
      echo "\033[0m" . str_repeat('-', 80) . "\n";
      foreach ($bb_rows as $mid => $s) {
        if (!isset($names[$mid])) continue;
        $name = substr($names[$mid], 0, 27);
        $mc   = $s['missing']    > 0 ? "{$s['missing']}"    : '';
        $dp   = $s['duplicates'] > 0 ? "{$s['duplicates']}" : '';
        printf($col, $mid, $name, $mc, $dp);
      }
        echo "\n$hr\n";
        echo "Missing - Archived in accumulator but no matching Bluebird event.\n";
        echo "Duplicated - Number of unexpected duplicates in Bluebird.\n\n";
    }

    // --- Table 2: Duplicates by Event Type ---
    echo "\n\n\033[1mDuplicates by Event Type\033[0m\n$hr\n";
    if (empty($this->dup_detail)) {
      echo "None found.\n";
    }
    else {
      $col2 = "%-10s | %-31s | %-20s | %10s\n";
      echo "\033[1m";
      printf($col2, 'Mailing ID', 'Mailing Name', 'Event Type', 'Duplicated');
      echo "\033[0m" . str_repeat('-', 80) . "\n";
      foreach ($this->dup_detail as $mid => $types) {
        if (!isset($names[$mid])) continue;
        $name = substr($names[$mid], 0, 31);
        foreach ($types as $label => $count) {
          printf($col2, $mid, $name, $label, $count);
        }
      }
    }

    // --- Table 3: Accumulator Stats Anomalies ---
    $sg_rows = array_filter($this->mailing_report,
      fn($s) => $s['unexplained_fails'] > 0 || $s['unarchived'] > 0
    );

    echo "\n\n\n\033[1mAccumulator Anomalies\033[0m\n$hr\n";
    if (empty($sg_rows)) {
      echo "None found.\n";
    }
    else {
      $col3 = "%-10s | %-27s | %17s | %17s\n";
      echo "\033[1m";
      printf($col3, 'Mailing ID', 'Mailing Name', 'Failed', 'Stuck');
      echo "\033[0m" . str_repeat('-', 80) . "\n";
      foreach ($sg_rows as $mid => $s) {
        if (!isset($names[$mid])) continue;
        $name = substr($names[$mid], 0, 27);
        $uf   = $s['unexplained_fails'] > 0 ? "{$s['unexplained_fails']}" : '';
        $ua   = $s['unarchived']        > 0 ? "{$s['unarchived']}"        : '';
        printf($col3, $mid, $name, $uf, $ua);
      }
        echo "\n$hr\n";
        echo "Failed - Events failed in accumulator, but Bluebird does have an event.\n";
        echo "Stuck - Events stuck in incoming, indicated by an existing Bluebird event.\n\n";
    }
  }
}

$script = new CRM_NYSS_Scripts_AuditSendGridStats();
$script->run();
