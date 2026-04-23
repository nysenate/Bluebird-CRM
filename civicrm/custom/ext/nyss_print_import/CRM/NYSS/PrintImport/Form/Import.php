<?php
declare(strict_types = 1);

use CRM_NYSS_PrintImport_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_NYSS_PrintImport_Form_Import extends CRM_Core_Form {

    const BATCHSIZE = 250;
    const BATCHSIZETAG = 1000;
    const PROGRESS_INTERVAL = 500;
    const VARSTATS = FALSE;


    /** @var Bluebird Config */
    protected static $bbcfg;
    /** @var Bluebird Instance name */
    protected static $instance;

    protected static $nyss_conn;
    protected static $dupe_pri_script;
    protected static $greeting_script;
    protected static $flag_email_script;
    protected static $fix_entity_tags_script;

    /** @var bool When true the database is not updates. It only shows what would happen. */
    protected bool $dryrun = FALSE;

    protected bool $null_override = FALSE;

    protected bool $boe = FALSE;

    protected bool $dedupe = FALSE;
    protected bool $allow_delete = FALSE;

    protected ?int $dedupe_rule;

    /** @var int tracks import line numbers */
    protected static int $nyss_ioline = 0;
    /** @var int total import line counts */
    protected static int $nyss_iototallines = 0;
    /** @var array buffer for bulk inserts. - Claude */
    protected static array $nyss_insert_buffer = [];

    /** @var int[] Address IDs flagged for deletion after the import loop. */
    private array $flagged_address_deletions = [];
  /**
   * @throws \CRM_Core_Exception
   */
  public function buildQuickForm(): void {

      $this->addElement('file', "csv", ts('Upload File'), 'size=30 maxlength=255');
      $this->addUploadElement("csv");

      $this->add('text', 'servercsv', ts('OR Server Filename'));

    // add form elements
    $this->add(
      'select', // field type
      'runnum', // field name
        ts('How Many Records to Process'), // field label
      $this->getRecordCountOptions(), // list of options
      TRUE // is required
    );

      $this->addElement('advcheckbox', 'dryrun', ts('Dry Run'));
      $this->addElement('advcheckbox', 'sendemail', ts('Send Summary Email'));
      $this->addElement('advcheckbox', 'debug', ts('Enable Debug Logging'));

      $this->addElement('advcheckbox', 'dedupe', ts('Dedupe on Import'));

      $this->add(
          'select', // field type
          'deduperule', // field name
          ts('Dedupe Rule'), // field label
          $this->getDedupeRuleOptions(), // list of options
          FALSE // is required
      );

      $this->addElement('advcheckbox', 'boeimport', ts('Process as BOE Import'));
      $this->addElement('advcheckbox', 'emailimport', ts('Process as Email Import'));
      $this->addElement('advcheckbox', 'parsename', ts('Parse Full Name'));
      $this->addElement('advcheckbox', 'fieldoverried', ts('Null/Empty Override'));
      $this->addElement('advcheckbox', 'allowdelete', ts('Allow Contact Deletion'));

      $add_to_group_options = [
          'all' => ts('Add All'),
          'new' => ts('Add New'),
          'none' => ts('None'),
      ];
      $this->addRadio('addtogroup_handling', "Add to Group Handling", $add_to_group_options);

      $this->add('text', 'addtogroup', ts('Group Name'));

      $greeting_options = [
          'imported' => ts('Process Imported Contacts Only '),
          'all' => ts('Process All Contacts'),
          'none' => ts('Disable Greeting Generation'),
      ];
      $this->addRadio('greetinggeneration', "Greeting Generation", $greeting_options);

    $this->addButtons([
      [
        'type' => 'submit',
        'name' => E::ts('Submit'),
        'isDefault' => TRUE,
      ],
    ]);

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function validate(): bool {
    parent::validate();

    $values = $this->exportValues();

    if (!empty($values['servercsv'])) {
      $filePath = '/data/importData/' . $values['servercsv'];
      if (!file_exists($filePath)) {
        $this->setElementError('servercsv', ts('No file by that name was found.'));
      }
    }
    else {
      $file = $_FILES['csv'] ?? NULL;
      if (empty($file) || $file['error'] !== UPLOAD_ERR_OK || empty($file['name'])) {
        $this->setElementError('csv', ts('You must select a valid file to upload.'));
      }
      else {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'tab', 'txt'])) {
          $this->setElementError('csv', ts('Invalid file type. Please upload a CSV, TAB, or TXT file.'));
        }
      }
    }

    return (0 == count($this->_errors));
  }

  /**
   * Flag an address ID for deletion after the import loop completes.
   *
   * @param int $addressId
   */
  protected function flagAddressDeletion(int $addressId): void {
    // Duplicates are allowed, carrying over behavior from the original
    // nyss_io.module. MySQL silently no-ops the redundant DELETE.
    $this->flagged_address_deletions[] = $addressId;
  }

  /**
   * Return all address IDs flagged for deletion.
   *
   * @return int[]
   */
  protected function getFlaggedAddressDeletions(): array {
    return $this->flagged_address_deletions;
  }

  public function postProcess(): void {

      $values = $this->exportValues();
    //$options = $this->getRecordCountOptions();

      //temporarily bump memory limit
      ini_set('memory_limit', '10000M');
      set_time_limit(0); // moved out of per-record loop - Claude

      //track processing time
      $timeStart = time();

      self::$bbcfg = get_bluebird_instance_config();
      self::$instance = get_config_value(self::$bbcfg, 'shortname', null);
      self::$nyss_conn = nyss_getConnection(self::$bbcfg);
      //self::$approot = get_config_value($bbcfg, 'app.rootdir', null);

      // Get and store paths of external scripts
      self::$dupe_pri_script = Civi::paths()->getPath("[civicrm.root]/scripts/fixDupePrimary.sh");
      self::$greeting_script = Civi::paths()->getPath("[civicrm.root]/civicrm/scripts/updateAllGreetings.php");
      self::$flag_email_script = Civi::paths()->getPath("[civicrm.root]/scripts/flagBadEmails.sh");
      self::$fix_entity_tags_script = Civi::paths()->getPath("[civicrm.root]/scripts/fixEntityTags.sh");

      $nyss_iofields = CRM_NYSS_PrintImport_Definitions::getFields();
      $nyss_iosuffixes= CRM_NYSS_PrintImport_Definitions::getSuffixes();
      $nyss_ioprefixes= CRM_NYSS_PrintImport_Definitions::getPrefixes();

      $this->dryrun = (!empty($values['dryrun'])) ? TRUE : FALSE;
      $this->null_override = (!empty($values['fieldoverried'])) ? TRUE : FALSE;
      $this->boe = (!empty($values['boeimport'])) ? TRUE : FALSE;
      $this->allow_delete = (!empty($values['allowdelete'])) ? TRUE : FALSE;
      if (!empty($values['debug'])) {
          CRM_NYSS_PrintImport_Utils::$debug = TRUE;
      }

      //if processing as an email import, set other required options
      if ($values['emailimport']==1) {
          $this->dedupe = TRUE;
          $this->dedupe_rule = 1;
      }

      //initialize arrays to be populated with contact id's during import
      $imported_contacts = $updated_contacts = $new_contacts = $delete_contacts = $tag_contacts = [];

      //get some civicrm values and flip so we can easily lookup values
      $aGender = array_flip(CRM_NYSS_PrintImport_Utils::getCiviOptions('\Civi\Api4\Contact', 'gender_id'));

      // Stored in both directions: [id => label] for building display_name, [label => id] for fixPrefix/fixSuffix
      $prefixOptions = CRM_NYSS_PrintImport_Utils::getCiviOptions('\Civi\Api4\Contact', 'prefix_id');
      $aPrefix       = array_flip($prefixOptions);
      $suffixOptions = CRM_NYSS_PrintImport_Utils::getCiviOptions('\Civi\Api4\Contact', 'suffix_id');
      $aSuffix       = array_flip($suffixOptions);

      $aLocType = array_flip(CRM_NYSS_PrintImport_Utils::getCiviOptions('\Civi\Api4\Address', 'location_type_id'));
      $aStates  = array_flip(CRM_NYSS_PrintImport_Utils::getCiviOptions('\Civi\Api4\Address', 'state_province_id'));

      // Read import file
      if (!empty($values['servercsv'])) {
          $filename = '/data/importData/'.$values['servercsv'];
      } else {
          $filename = $_FILES['csv']['tmp_name'];
      }

      $fo = new CRM_NYSS_PrintImport_IOFileObject($filename, "\t");
      self::$nyss_iototallines = $fo->countLines();

      $dryrun_txt = ($this->dryrun ? 'TRUE' : 'FALSE');
      CRM_NYSS_PrintImport_Utils::out('status',
          "importing file with [dryrun = {$dryrun_txt}] AND [count = {$values['runnum']}].<br>import file: {$filename}", TRUE);
      CRM_NYSS_PrintImport_Utils::flush();

      $options = [
        CRM_NYSS_PrintImport_Importer::OPT_DRYRUN => $this->dryrun,
        CRM_NYSS_PrintImport_Importer::OPT_NULL   => $this->null_override,
        CRM_NYSS_PrintImport_Importer::OPT_BOE    => $this->boe,
        CRM_NYSS_PrintImport_Importer::OPT_DELETE => $this->allow_delete
      ];
      $importer        = new CRM_NYSS_PrintImport_Importer(self::$nyss_conn, $options);
      $finder          = new CRM_NYSS_PrintImport_Finder(self::$nyss_conn);
      $post_processor  = new CRM_NYSS_PrintImport_PostProcessor(self::$nyss_conn, $this->dryrun);

      mysqli_query(self::$nyss_conn, "START TRANSACTION");

      while ($l = $fo->getLine()) {
          ++self::$nyss_ioline;

          if (is_numeric($values['runnum'])) {
              if ($values['runnum'] <= self::$nyss_ioline) {
                  CRM_NYSS_PrintImport_Utils::out('debug',
                      "Stopping at {self::$nyss_ioline} lines.", TRUE);
                  break;
              }
          }

          if (self::$nyss_ioline % self::PROGRESS_INTERVAL == 0) {
              CRM_NYSS_PrintImport_Utils::out('status',
                  "processed {self::$nyss_ioline} lines", TRUE);
              CRM_NYSS_PrintImport_Utils::flush();
          }

          // Handle Deletes when delete_contact field is set to 1...
          if (array_key_exists('delete_contact', $l) && $l['delete_contact'] == 1) {
              // But, allow delete was not checked.
              if (! $this->allow_delete) {
                  CRM_NYSS_PrintImport_Utils::out('debug',
                      'contact flagged for deletion but user did not check allow delete. skipping record:', TRUE);
              }
              $deleteResult = $importer->deleteContact($l, self::$nyss_ioline);
              if ($deleteResult) {
                  $delete_contacts[] = $l['id'];
              }
              continue; // explicit instructions to delete row were given. Don't import this row.
          }

          // Dedupe lookup — only when dedupe is enabled and no contact ID provided
          if ($this->dedupe && empty($l['id'])) {
              $finder->findContact($l, $this->dedupe_rule);
          }

          // Find related custom table record if contact is known
          if (!empty($l['id'])) {
              $finder->findRelatedCustom($l);
          }

          if ($this->boe) {
              CRM_NYSS_PrintImport_Preparer::prepareBOE($l);
          }

          //fix the gender entries
          CRM_NYSS_PrintImport_Preparer::fixGender($l, $aGender);

          //fix birthdate
          CRM_NYSS_PrintImport_Preparer::fixBirthdate($l);

          //fix location_type_id if set and not numeric
          CRM_NYSS_PrintImport_Preparer::fixLocType($l, $aLocType);

          //fix boe registration date
          CRM_NYSS_PrintImport_Preparer::fixBOERegDate($l);

          // NYSS 4107 parse name
          if ($values['parsename'] && isset($l['full_name'])) {
              CRM_NYSS_PrintImport_Preparer::fixParseName($l);
          }

          //fix prefixes
          CRM_NYSS_PrintImport_Preparer::fixPrefix($l, $aPrefix, $nyss_ioprefixes);

          //fix suffixes
          CRM_NYSS_PrintImport_Preparer::fixSuffix($l, $aSuffix, $nyss_iosuffixes);

          //set state id
          CRM_NYSS_PrintImport_Preparer::fixState($l, $aStates);

          //if street_unit value exists, and there is no "apt" text, prepend to value
          CRM_NYSS_PrintImport_Preparer::fixStreetUnit($l);

          //form the street address from parsed values; parse street_address into components if needed
          CRM_NYSS_PrintImport_Preparer::fixStreetAddress($l);

          //parse street_number into numeric part and suffix
          CRM_NYSS_PrintImport_Preparer::fixStreetNumber($l);

          //strip leading/trailing/double spaces; escape single quotes
          CRM_NYSS_PrintImport_Preparer::cleanData($l);

          // Flag addresses marked for deletion (must run before findAddress so only
          // explicitly-passed address IDs are captured, not ones we look up below)
          if (!empty($l['address_id']) && strtolower($l['street_address']) == 'delete') {
              $this->flagAddressDeletion((int) $l['address_id']);
          }

          // Resolve address, district, and email IDs for existing records
          $finder->findAddress($l);
          $finder->findDistrict($l);
          $finder->findEmail($l);

          // civicrm_contact
          $contactMode = $importer->detectImportMode('civicrm_contact', $l);
          CRM_NYSS_PrintImport_Preparer::prepareContactType($l);
          CRM_NYSS_PrintImport_Preparer::prepareContactName($l, $prefixOptions, $suffixOptions);
          $importer->importData('civicrm_contact', $l);

          // Track imported contact IDs for post-loop processing
          if (!$this->dryrun) {
              $imported_contacts[] = $l['id'];
              if ($contactMode === CRM_NYSS_PrintImport_Importer::MODE_INSERT) {
                  $new_contacts[] = $l['id'];
              }
              else {
                  $updated_contacts[] = $l['id'];
              }
          }

          // civicrm_address — only if the record contains address fields
          $l['contact_id'] = $l['id'];
          if ($importer->hasFieldsForTable('civicrm_address', $l, $this->null_override)) {
              CRM_NYSS_PrintImport_Preparer::prepareAddressLastImport($l);
              CRM_NYSS_PrintImport_Preparer::prepareAddressLocationType($l);
              CRM_NYSS_PrintImport_Preparer::prepareAddressCountry($l);
              CRM_NYSS_PrintImport_Preparer::prepareAddressPrimary($l);
              $importer->importData('civicrm_address', $l);
          }

          // civicrm_phone
          $l['phone_contact_id'] = $l['contact_id'];
          CRM_NYSS_PrintImport_Preparer::preparePhone($l);
          $importer->importData('civicrm_phone', $l);

          // civicrm_email
          $l['email_contact_id'] = $l['contact_id'];
          CRM_NYSS_PrintImport_Preparer::prepareEmail($l);
          $importer->importData('civicrm_email', $l);

          // civicrm_value_constituent_information_1
          $l['constinfo_entity_id'] = $l['id'];
          $importer->importData('civicrm_value_constituent_information_1', $l);

          // civicrm_value_district_information_7
          $l['entity_id'] = $l['address_id'];
          $importer->importData('civicrm_value_district_information_7', $l);

          $post_processor->processCurrentEmployer($l);
          $post_processor->processNote($l);

          //process pipe separated list of keywords
          if (!empty($l['keywords'])) {
              $keywords = explode('|', str_replace(['| ','"'], ['|',''], $l['keywords']));
              foreach ($keywords as $keyword) {
                  $tag_contacts['Keywords'][$keyword][] = $l['id'];
              }
          }

          //process pipe separated list of website petitions
          if (!empty($l['petitions'])) {
              $petitions = explode('|', str_replace(['| ','"'], ['|',''], $l['petitions']));
              foreach ($petitions as $petition) {
                  $tag_contacts['Website Petitions'][$petition][] = $l['id'];
              }
          }

          if (self::$nyss_ioline % self::BATCHSIZE === 0) {
              $importer->flushInsertBuffer();
              mysqli_query(self::$nyss_conn, "COMMIT");
              CRM_Core_DAO::singleValueQuery("SELECT 1"); // keepalive: prevents wait_timeout on CiviCRM's DAO connection
              mysqli_query(self::$nyss_conn, "START TRANSACTION");
          }
      }

      // Flush and commit any records that didn't fill a complete batch.
      $importer->flushInsertBuffer();
      mysqli_query(self::$nyss_conn, "COMMIT");

      CRM_NYSS_PrintImport_Utils::out('debug', "file rows import complete. beginning post process steps.", TRUE);

      // Process tags after final commit so all contact records are visible
      if (!empty($tag_contacts)) {
          foreach ($tag_contacts as $type => $tag_list) {
              $post_processor->processTags($tag_list, $type);
          }
      }

      CRM_NYSS_PrintImport_Utils::out('status', "Processing time: " . (time() - $timeStart) . " sec.", TRUE);

      $post_processor->processGroup(
          $values['addtogroup'] ?? '',
          $values['addtogroup_handling'] ?? 'none',
          $imported_contacts,
          $new_contacts
      );

      CRM_NYSS_PrintImport_Utils::out('status', "Processing time: " . (time() - $timeStart) . " sec.", TRUE);

      if ($values['emailimport'] == 1) {
          $post_processor->processEmailImport($new_contacts);
      }

      $address_deletions  = $this->getFlaggedAddressDeletions();
      $address_del_count  = count($address_deletions);
      $post_processor->processAddressDelete($address_deletions);

      CRM_NYSS_PrintImport_Utils::out('status', "Processing time: " . (time() - $timeStart) . " sec.", TRUE);

      // Summary counts
      $num_updated = count($updated_contacts);
      $num_created = count($new_contacts);
      $num_deleted = count($delete_contacts);

      CRM_NYSS_PrintImport_Utils::out('status', "{$num_updated} updated, {$num_created} created.", TRUE);
      CRM_NYSS_PrintImport_Utils::out('status', "{$num_deleted} deleted.", TRUE);
      CRM_NYSS_PrintImport_Utils::out('status', "{$address_del_count} addresses deleted.", TRUE);
      CRM_NYSS_PrintImport_Utils::out('status', "Imported " . self::$nyss_ioline . " lines.", TRUE);

      CRM_NYSS_PrintImport_Utils::out('status', "Processing time: " . (time() - $timeStart) . " sec.", TRUE);

      if (!empty($values['sendemail'])) {
          $post_processor->sendSummaryEmail(
              $filename,
              $num_updated,
              $num_created,
              $num_deleted,
              $address_del_count,
              self::$nyss_ioline
          );
      }

      if (!$this->dryrun) {
          // Fix duplicate primary flags
          $dupe_pri_output = shell_exec(self::$dupe_pri_script . ' ' . self::$instance);
          CRM_NYSS_PrintImport_Utils::out('status', $dupe_pri_output, TRUE);
      }

      CRM_NYSS_PrintImport_Utils::out('status', "Processing time: " . (time() - $timeStart) . " sec.", TRUE);

      // Run greeting generation unless disabled
      $greeting_mode = $values['greetinggeneration'] ?? 'none';
      CRM_NYSS_PrintImport_Utils::out('debug', "greeting generation using option: {$greeting_mode}...", TRUE);
      if ($greeting_mode !== 'none') {
          if ($this->dryrun) {
              CRM_NYSS_PrintImport_Utils::out('debug', "dry run: would run greeting generation ({$greeting_mode}).", TRUE);
          }
          else {
              $tbl_opt = '';
              if ($greeting_mode === 'imported') {
                  $rnd     = date('Ymdhis');
                  $tmp_tbl = "nyss_temp_greetings_{$rnd}";
                  CRM_NYSS_PrintImport_Utils::out('debug', "tmpTbl: {$tmp_tbl}", TRUE);
                  CRM_Core_DAO::executeQuery("CREATE TABLE {$tmp_tbl} (id INT NOT NULL PRIMARY KEY) ENGINE = myisam");
                  $all_ids = array_merge($updated_contacts, $new_contacts);
                  if (!empty($all_ids)) {
                      $safe_ids = implode('), (', array_map('intval', $all_ids));
                      CRM_Core_DAO::executeQuery("INSERT IGNORE INTO {$tmp_tbl} (id) VALUES ({$safe_ids})");
                  }
                  $tbl_opt = " --idtbl={$tmp_tbl}";
              }
              $greeting_output = shell_exec('php ' . self::$greeting_script . ' -S ' . self::$instance . ' --quiet' . $tbl_opt);
              CRM_NYSS_PrintImport_Utils::out('status', $greeting_output, TRUE);
          }
      }

      CRM_NYSS_PrintImport_Utils::out('status', "Processing time: " . (time() - $timeStart) . " sec.", TRUE);

      if (!$this->dryrun) {
          // Flag malformed emails and place on hold
          $flag_email_output = shell_exec(self::$flag_email_script . ' --ok ' . self::$instance);
          CRM_NYSS_PrintImport_Utils::out('status', $flag_email_output, TRUE);
      }

      CRM_NYSS_PrintImport_Utils::out('status', "Processing time: " . (time() - $timeStart) . " sec.", TRUE);


    //CRM_Core_Session::setStatus(E::ts('You picked color "%1"', [
    //  1 => $options[$values['favorite_color']],
    //]));
    parent::postProcess();
  }

  public function getRecordCountOptions(): array {
    $options = [
        '' => E::ts('- select -'),
        '10' => E::ts('10'),
        '100' => E::ts('100'),
        '500' => E::ts('500'),
        '1000' => E::ts('1000'),
        'All' => E::ts('All'),
    ];
    return $options;
  }

  public function getDedupeRuleOptions(): array {
      $dedupeRuleGroups = \Civi\Api4\DedupeRuleGroup::get(TRUE)
          ->addSelect('id', 'title')
          ->addWhere('contact_type', '=', 'Individual')
          ->addOrderBy('title', 'ASC')
          ->execute();

      $dedupeRuleOptions = [];
      foreach ($dedupeRuleGroups as $dedupeRuleGroup) {
          $dedupeRuleOptions[$dedupeRuleGroup['id']] = $dedupeRuleGroup['title'];
      }

      return $dedupeRuleOptions;
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames(): array {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = [];
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
