<?php
/*
 *
 * 17730-rebuild-display-names.php
 * Searches for contacts with nick names and executes an idempotent save
 * with the purpose of rebuilding the contact's display_name.
 *
 * @see NYSS #17730
 *
 * Project: BluebirdCRM
 * Author: Nate Frank
 * Organization: New York State Senate
 * Date: 2026-03-23
 *
 */

error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

define('DEFAULT_LOG_LEVEL', 'INFO');

#[CRM_NYSS_Attribute_IssueRefs('17730')]
class CRM_NYSS_Scripts_RebuildNickNameDisplayNames {

  private $short_opts = 'dv';
  private $long_opts = ['dryrun', 'verbose'];
  private $user_opts = NULL;
  private ?string $site = NULL;
  private bool $dryrun = FALSE;
  private bool $verbose = FALSE;
  private $bbcfg;

  public function run() {
    require_once '../civicrm/scripts/script_utils.php';
    $this->init();
    $this->rebuild();
    exit();
  }

  private function rebuild(): void {
    // Fetch all Individual contacts with a nick_name set
    $contacts = \Civi\Api4\Contact::get(FALSE)
      ->addSelect('id', 'first_name', 'last_name', 'sort_name', 'display_name')
      ->addWhere('contact_type', '=', 'Individual')
      ->addWhere('nick_name', 'IS NOT EMPTY')
      ->addWhere('is_deleted', '=', FALSE)
      ->execute();

    echo "Found " . $contacts->count() . " contacts with nick names.\n";

    if ($this->dryrun) {
      if ($this->verbose) {
        foreach ($contacts as $contact) {
          echo "  [{$contact['id']}] {$contact['sort_name']}\n";
        }
      }
      return;
    }

    $count = 0;
    $errors = 0;
    foreach ($contacts as $contact) {
      if ($this->verbose) {
        echo "Processing contact {$contact['id']}: {$contact['sort_name']}\n";
      }
      try {
        // Re-saving first_name and last_name triggers CRM_Contact_BAO_Individual::format(),
        // which recomputes sort_name and display_name via the TokenProcessor.
        \Civi\Api4\Contact::update(FALSE)
          ->addWhere('id', '=', $contact['id'])
          ->addValue('first_name', $contact['first_name'])
          ->addValue('last_name', $contact['last_name'])
          ->execute();
        $count++;
      }
      catch (Exception $e) {
        echo "Error processing contact {$contact['id']}: " . $e->getMessage() . "\n";
        $errors++;
      }
    }

    echo "Rebuilt display names for $count contacts.\n";
    if ($errors) {
      echo "$errors contacts had errors.\n";
    }
  }

  private function init(): void {
    $this->user_opts = civicrm_script_init($this->short_opts, $this->long_opts);

    echo "Rebuilding display names for contacts with nick names...\n";

    if ($this->user_opts === null) {
      $stdusage = civicrm_script_usage();
      $usage = implode(' ', array_map(function($s) {
        return '[--' . $s . ']';
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

    $this->site = $this->user_opts['site'] ?? NULL;

    $this->bbcfg = get_bluebird_instance_config($this->user_opts['site']);

    $civicrm_root = $this->bbcfg['drupal.rootdir'] . '/sites/all/modules/civicrm';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    if (!CRM_Utils_System::loadBootstrap([], FALSE, FALSE, $civicrm_root)) {
      CRM_Core_Error::debug_log_message('Failed to bootstrap CMS from CRM_NYSS_Scripts_RebuildNickNameDisplayNames.');
      throw new Exception('Failed to bootstrap CMS from CRM_NYSS_Scripts_RebuildNickNameDisplayNames');
    }
  }

}

$class = new CRM_NYSS_Scripts_RebuildNickNameDisplayNames();
$class->run();

echo "processing completed.\n";
