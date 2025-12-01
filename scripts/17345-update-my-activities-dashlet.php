<?php

error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

define('DEFAULT_LOG_LEVEL', 'INFO');

#[CRM_NYSS_Attribute_IssueRefs('17345')]
class CRM_NYSS_Scripts_UpdateMyActivitiesDashlet {

  const OLD_DASHLET_NAME = 'activity';
  const NEW_DASHLET_NAME = 'afsearchNYSSMyActivities';

  private $short_opts = 'dvrf';
  private $long_opts = ['dryrun','verbose', 'restore', 'restore-file='];
  private $user_opts = NULL;
  private bool $dryrun = FALSE;
  private bool $verbose = FALSE;
  private bool $restore = FALSE;
  private int $old_id; // The database ID of the dashlet being removed
  private int $new_id; // The database ID of the dashlet being added
  /** @var string|null user provided file path */
  private ?string $restore_file = NULL;
  private $bbcfg;

  public function run() {
    require_once '../civicrm/scripts/script_utils.php';
    $this->init();

    // Get old Activities Dashlet ID
    $old_dashlet = \Civi\Api4\Dashboard::get(FALSE)
      ->addSelect('id', 'name', 'label')
      ->addWhere('name', '=', self::OLD_DASHLET_NAME)
      ->setLimit(1)
      ->execute()->single();

    $this->old_id = $old_dashlet['id'];

    // Get new My Activities Dashlet ID
    $new_dashlet = \Civi\Api4\Dashboard::get(FALSE)
      ->addSelect('id', 'name', 'label')
      ->addWhere('name', '=', self::NEW_DASHLET_NAME)
      ->setLimit(1)
      ->execute()->single();

    $this->new_id = $new_dashlet['id'];

    if ($this->restore) {
      $this->run_restore();
    }
    else {
      $this->run_update();
    }

    exit();
  }

  /**
   * If restore-file opt was given, check that the path is writable and
   * check if the file exists or doesn't exist (depending on the circumstance)
   *
   * Otherwise, create a default file or look for default files (depeneding on the circumstance)
   * @return string path to a writable file
   */
  private function get_restore_filename(): string {
    if ($this->restore_file) {
      if (preg_match('/^\~/', $this->restore_file)) {
        throw new Exception('Please provide the full path to a file. ~ expansion not supported.');
      }
      if ($this->restore) {
        // file must exist and be readable
        if (file_exists($this->restore_file) && is_readable($this->restore_file)) {
          return $this->restore_file;
        } else {
          throw new Exception('User supplied restore file does not exist or is not readable');
        }
      } else {
        // file must not already exist
        if (file_exists($this->restore_file)) {
          throw new Exception('User supplied restore file already exists');
        } else {
          return $this->restore_file;
        }
      }
    } else {
      if ($this->restore) {
        // look for latest restore file based on timestamp and filename pattern
        $files = glob(getcwd().'/my-activities-dashlet-restore-*.txt');
        if (sizeof($files) < 1) {
          throw new Exception('No restore files found');
        }
        rsort($files); // newest first if timestamp in filename
        return $files[0];
      } else {
        $timestamp = date('Ymd_His');
        if (!is_writable(__DIR__)) {
          throw new Exception("Cannot write to " . __DIR__);
        }
        return getcwd(). "/my-activities-dashlet-restore-{$timestamp}.txt";
      }
    }
  }

  private function run_update() {

    // restore file for writing
    $restore_filename = $this->get_restore_filename();

    // Get list of contacts with old dashlet enabled
    $dashboard_contacts = \Civi\Api4\DashboardContact::get(FALSE)
      ->addSelect('contact_id')
      ->addWhere('dashboard_id', '=', $this->old_id)
      ->addWhere('is_active', '=', TRUE)
      ->execute();

    // For each of these contacts, enable the new dashlet and disable the old
    if (! $this->dryrun) {
      // create a restore file.
      echo 'saving restore file to... ' . $restore_filename . "\n";
      file_put_contents($restore_filename, serialize($dashboard_contacts));

      foreach ($dashboard_contacts as $c) {
        //Civi::log()->debug(__FUNCTION__, ['$sql' => $sql,'dao' => $dao]);
        bbscript_log(LL::INFO, "enable {$this->new_id} dashlet on contact {$c['contact_id']}\n");
        $enable_results = \Civi\Api4\DashboardContact::update(FALSE)
          ->addValue('is_active', TRUE)
          ->addWhere('contact_id', '=', $c['contact_id'])
          ->addWhere('dashboard_id', '=', $this->new_id)
          ->execute();
        echo "New dashlet enabled for " . $c['contact_id'] . "\n";

        bbscript_log(LL::INFO, "disable {$this->old_id} dashlet on contact {$c['contact_id']}\n");
        $disable_results = \Civi\Api4\DashboardContact::update(FALSE)
          ->addValue('is_active', FALSE)
          ->addWhere('contact_id', '=', $c['contact_id'])
          ->addWhere('dashboard_id', '=', $this->old_id)
          ->execute();
        echo "Old dashlet disabled for " . $c['contact_id'] . "\n";

        //foreach ($results as $result) {
        // do something
        //}
      }
    } else {
      if ($this->verbose) {
        foreach ($dashboard_contacts as $c) {
          echo "Contact: {$c['contact_id']}\n";
        }
      }
      echo "found " . $dashboard_contacts->count() . " contacts who are using old dashlet\n";
    }

    // Now disable the entry in the dashboard table, so it no longer appears
    if (! $this->dryrun) {
      bbscript_log(LL::INFO, "disable {$this->old_id} dashlet.\n");
      $results = \Civi\Api4\Dashboard::update(FALSE)
        ->addValue('is_active', FALSE)
        ->addWhere('id', '=', $this->old_id)
        ->execute();
      if ($this->verbose) {
        echo "disable ".self::OLD_DASHLET_NAME." dashlet.\n";
      }
    } else {
      echo "dryrun: Not disabling old dashlet\n";
    }

  }

  private function run_restore() {

    $restore_data = unserialize(file_get_contents($this->get_restore_filename()));

    // First re-enable the entry in the dashboard table, so it no longer appears
    if (! $this->dryrun) {
      bbscript_log(LL::INFO, "RESTORE - Re-enable {$this->old_id} dashlet.\n");
      $results = \Civi\Api4\Dashboard::update(FALSE)
        ->addValue('is_active', TRUE)
        ->addWhere('id', '=', $this->old_id)
        ->execute();
      if ($this->verbose) {
        echo "RESTORE - Re-enable ".self::OLD_DASHLET_NAME." dashlet.\n";
      }
    } else {
      echo "dryrun: re-enable old dashlet in restore.\n";
    }

    if (! $this->dryrun) {

      foreach ($restore_data as $c) {
        //Civi::log()->debug(__FUNCTION__, ['$sql' => $sql,'dao' => $dao]);
        bbscript_log(LL::INFO, "RESTORE - disable {$this->new_id} dashlet on contact {$c['contact_id']}\n");
        $enable_results = \Civi\Api4\DashboardContact::update(FALSE)
          ->addValue('is_active', FALSE)
          ->addWhere('contact_id', '=', $c['contact_id'])
          ->addWhere('dashboard_id', '=', $this->new_id)
          ->execute();
        echo "RESTORE - New dashlet disabled for " . $c['contact_id'] . "\n";

        bbscript_log(LL::INFO, "RESTORE enable {$this->old_id} dashlet on contact {$c['contact_id']}\n");
        $disable_results = \Civi\Api4\DashboardContact::update(FALSE)
          ->addValue('is_active', TRUE)
          ->addWhere('contact_id', '=', $c['contact_id'])
          ->addWhere('dashboard_id', '=', $this->old_id)
          ->execute();
        echo "RESTORE - Old dashlet enabled for " . $c['contact_id'] . "\n";

        //foreach ($results as $result) {
        // do something
        //}
      }
    } else {
      if ($this->verbose) {
        foreach ($restore_data as $c) {
          echo "Contact: {$c['contact_id']}\n";
        }
      }
      echo "found " . $restore_data->count() . " contacts to restore.\n";
    }
  }

  private function init(): void {

    // Parse the options
    $this->user_opts = civicrm_script_init($this->short_opts, $this->long_opts);
    //Civi::log()->debug(__FUNCTION__, ['$optlist' => $optlist]);

    echo "Bootstrapping My Activities Dashlet Update...\n";

    if ($this->user_opts === null) {
      $stdusage = civicrm_script_usage();
      $usage = implode(' ', array_map(function($s) {
        return '[--'.$s.']';
      }, $this->long_opts));
      error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
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

    $this->restore = ($this->user_opts['restore'] ?? FALSE) ? TRUE : FALSE;
    if ($this->restore) {
      echo "Running in --restore mode\n";
    }

    $this->restore_file = $this->user_opts['restore-file'] ?? NULL;

    //get instance settings
    $this->bbcfg = get_bluebird_instance_config($this->user_opts['site']);
    //bbscript_log(LL::TRACE, "bbcfg", $bbcfg);
    //Civi::log()->debug(__FUNCTION__, ['bbcfg' => $bbcfg]);

    $civicrm_root = $this->bbcfg['drupal.rootdir'].'/sites/all/modules/civicrm';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    if (!CRM_Utils_System::loadBootstrap([], FALSE, FALSE, $civicrm_root)) {
      CRM_Core_Error::debug_log_message('Failed to bootstrap CMS from  CRM_NYSS_Scripts_UpdateMyActivitiesDashlet.');
      throw new Exception('Failed to bootstrap CMS from CRM_NYSS_Scripts_UpdateMyActivitiesDashlet');
    }

    //$this->log_db = $this->bbcfg['db.log.prefix'].$this->bbcfg['db.basename'];
    //$this->civi_db = $this->bbcfg['db.civicrm.prefix'].$this->bbcfg['db.basename'];
  }
}

//run the script
$class = new CRM_NYSS_Scripts_UpdateMyActivitiesDashlet();
$class->run();

echo "processing completed.\n";