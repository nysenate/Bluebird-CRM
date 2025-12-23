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
  private ?string $site = NULL;
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

    if (! $this->new_id ?? NULL) {
        throw new Exception('Failed: Can\'t find the ID of the New Dashlet');
    }

    if (! $this->old_id ?? NULL) {
        throw new Exception('Failed: Can\'t find the ID of the Old Dashlet');
    }

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
        $glob_pattern = "/my-activities-dashlet-restore-{$this->site}-*.txt";
        $files = glob(getcwd().$glob_pattern);
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
        return getcwd(). "/my-activities-dashlet-restore-{$this->site}-{$timestamp}.txt";
      }
    }
  }

  private function run_update() {

    // restore file for writing
    $restore_filename = $this->get_restore_filename();

    // Get list of contacts with old dashlet enabled
    $dashboard_contacts = \Civi\Api4\DashboardContact::get(FALSE)
      ->addSelect('id','dashboard_id','contact_id','is_active')
      ->addWhere('dashboard_id', '=', $this->old_id)
      ->execute();

    // For each of these contacts, enable the new dashlet and disable the old
    if (! $this->dryrun) {
        // create a restore file.
        echo 'saving restore file to... ' . $restore_filename . "\n";
        file_put_contents($restore_filename, serialize($dashboard_contacts));

        $save_call = \Civi\Api4\DashboardContact::save(FALSE);
        foreach ($dashboard_contacts as $dc) {
        //Civi::log()->debug(__FUNCTION__, ['$sql' => $sql,'dao' => $dao]);
            echo "Processing Contact Dashboard " . $dc['contact_id'] . "\n";
            // de-activate the old dashlet
            $save_call->addRecord(
                [
                    'id' => $dc['id'],
                    'contact_id' => $dc['contact_id'],
                    'dashboard_id' => $this->old_id,
                    'is_active' => FALSE,
                ]
            );

            // enable new dashlet for contacts that had an active old 'My Activities' dashlet
            if ($dc['is_active']) {
                echo "Contact " . $dc['contact_id'] . " has old 'My Activities' dashlet. Will Replace it with new\n";
                // check if there's an entry for the new dashlet.
                // (There shouldn't be on the first run, but just in case.)
                $new_exists_check = \Civi\Api4\DashboardContact::get(FALSE)
                    ->addSelect('id')
                    ->addWhere('dashboard_id', '=', $this->new_id)
                    ->addWhere('contact_id', '=', $dc['contact_id'])
                    ->execute();

                // if a record already exists, force an update by including id
                if ($new_exists_check->count()) {
                    echo "Update existing record for Contact " . $dc['contact_id'] ."\n";
                    $save_call->addRecord(
                        [
                            'id' => $new_exists_check[0]['id'],
                            'contact_id' => $dc['contact_id'],
                            'dashboard_id' => $this->new_id,
                            'is_active' => TRUE,
                        ]
                    );
                    // otherwise, it should be an update
                } else {
                    echo "Insert new reord for Contact " . $dc['contact_id'] . "\n";
                    $save_call->addRecord(
                        [
                            'contact_id' => $dc['contact_id'],
                            'dashboard_id' => $this->new_id,
                            'is_active' => TRUE,
                        ]
                    );
                }
            }
        }
        try {
            $save_call->execute();
        } catch(Exception $e) {
            echo 'An Exception occurred while saving contact dashboards: ' . $e->getMessage() . "\n";
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
      if ($results->count()) {
          if ($this->verbose) {
              echo "Disabled ".self::OLD_DASHLET_NAME." dashlet.\n";
          }
      } else {
          echo "Failed to disable ".self::OLD_DASHLET_NAME." dashlet.\n";
      }

    } else {
      echo "dryrun: Not disabling old dashlet\n";
    }

  }

  private function run_restore() {

    $restore_data = unserialize(file_get_contents($this->get_restore_filename()));

    // First re-enable the entry in the dashboard table, so it no longer appears
    if (! $this->dryrun) {
        try {
            bbscript_log(LL::INFO, "RESTORE - Re-enable {$this->old_id} dashlet.\n");
            $results = \Civi\Api4\Dashboard::update(FALSE)
                ->addValue('is_active', TRUE)
                ->addWhere('id', '=', $this->old_id)
                ->execute();

            if ($this->verbose) {
                echo "RESTORE - Re-enable ".self::OLD_DASHLET_NAME." dashlet.\n";
            }
        } catch(Exception $e) {
            die($e->getMessage());
        }
    } else {
      echo "dryrun: re-enable old dashlet in restore.\n";
    }

    if (! $this->dryrun) {

      foreach ($restore_data as $c) {
        //Civi::log()->debug(__FUNCTION__, ['$sql' => $sql,'dao' => $dao]);
          try {
              bbscript_log(LL::INFO, "RESTORE - disable {$this->new_id} dashlet on contact {$c['contact_id']}\n");
              $enable_results = \Civi\Api4\DashboardContact::update(FALSE)
                  ->addValue('is_active', FALSE)
                  ->addWhere('contact_id', '=', $c['contact_id'])
                  ->addWhere('dashboard_id', '=', $this->new_id)
                  ->execute();

              echo "RESTORE - New dashlet disabled for " . $c['contact_id'] . "\n";

              bbscript_log(LL::INFO, "RESTORE enable {$this->old_id} dashlet on contact {$c['contact_id']}\n");

              $disable_results = \Civi\Api4\DashboardContact::update(FALSE)
                  ->addValue('is_active', $c['is_active'])
                  ->addWhere('contact_id', '=', $c['contact_id'])
                  ->addWhere('dashboard_id', '=', $this->old_id)
                  ->execute();

              echo "RESTORE - Old dashlet reset for " . $c['contact_id'] . "\n";

          } catch (Exception $e) {
              die($e->getMessage());
          }
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

    $this->site = $this->user_opts['site'] ?? NULL;

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