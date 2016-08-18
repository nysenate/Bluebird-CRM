<?php

/**
 * Created by PhpStorm.
 * User: sbink
 */
class TableProcessor {
  public function __construct($tablename, $dbname, $logdb_name) {
    // Some properties
    $this->tablename = (string) $tablename;
    $this->cividb = $dbname;
    $this->logdb = $logdb_name;
    $this->fields = array();

    // Load the fields from the Civi table
    $civifields = self::retrieveSchema($tablename, $this->cividb);
    bbscript_log(LL::DEBUG, "Discovered schema for CiviDB $tablename");

    // Load the fields from the Log table
    $logfields = self::retrieveSchema("log_$tablename", $this->logdb);
    bbscript_log(LL::DEBUG, "Discovered schema for LogDB log_$tablename");

    // Find the primary key and note it
    $pk = array_filter($civifields, function ($e) { return $e['is_primary']===true; });
    $this->primary_key = (count($pk) ? key($pk) : NULL);
    bbscript_log(LL::DEBUG, "Detected primary key for table {$this->tablename}: ".var_export($this->primary_key,1));

    // The only fields we can check are those existing in both Civi and Log tables
    foreach (array_intersect(array_keys($civifields), array_keys($logfields)) as $key=>$val) {
      $this->fields[$val] = $civifields[$val];
    }
    bbscript_log(LL::DEBUG, "Found common fields for table {$this->tablename}:\n".implode(',', array_keys($this->fields)));
  }

  public static function retrieveSchema($tablename, $db='') {
    // initialize return
    $ret = array();

    // Looking for the return of "DESCRIBE TABLE"
    $query = "DESCRIBE " . ($db ? "{$db}." : '') . $tablename;
    $result = CRM_Core_DAO::executeQuery($query);
    while ($result->fetch()) {
      // for each record, parse the Type to get some attributes, like the data type,
      // precision, etc.  If no data type is found, don't include it.
      $m = array();
      preg_match('/^([a-z]+)(\\(([^)]+)\\))?( ?(.*))?/i', $result->Type, $m);
      if (isset($m[1])) {
        $ret[$result->Field] = array(
          'type' => $m[1],
          'precision' => isset($m[3]) ? $m[3] : NULL,
          'modifier' => isset($m[5]) ? $m[5] : NULL,
          'is_primary' => ($result->Key === 'PRI'),
        );
      }
    }
    return $ret;
  }

  public function process() {
    // If there's no table name, nothing to do.
    if (!$this->tablename) {
      bbscript_log(LL::ERROR, "process() could not find a table name!");
      return false;
    }
    // If there's no common fields, nothing to do.
    if (!count($this->fields)) {
      bbscript_log(LL::ERROR, "No common fields were found for {$this->tablename}");
      return false;
    }
    // If there's no primary key, nothing we *can* do.
    if (!$this->primary_key) {
      bbscript_log(LL::ERROR, "No Primary Key field found for {$this->tablename}");
      return false;
    }
    bbscript_log(LL::INFO, "Processing {$this->tablename}");

    // pull all the primary key values for the Civi records
    // Pulls the PK only to save space.  Also, uses an unbuffered query
    $query = "SELECT `{$this->primary_key}` FROM {$this->tablename} ORDER BY `{$this->primary_key}`";
    bbscript_log(LL::DEBUG, "Running unbuffered query $query");
    $records = CRM_Core_DAO::executeUnbufferedQuery($query);

    // Because the $records loop is based on an unbuffered query, we're going
    // to need a second DB connection to pull the individual records
    $log_db = DB::connect(CIVICRM_LOGGING_DSN);

    // Prep the fields names for the SELECT clause
    $fields = '`' . implode('`,`', array_keys($this->fields)) . '`';

    // Easy reference to the name of the PK field
    $pk_name = $this->primary_key;
    bbscript_log(LL::DEBUG, "Looking for fields: " .var_export($fields,1));

    // For each PK value in the civi table, pull the civi record, the log record,
    // and compare th two
    while ($records->fetch()) {
      // Eacy reference to the value of the PK field
      $pk_val = $records->{$pk_name};

      // Look up the full record in Civi
      $query = "SELECT $fields FROM {$this->cividb}.{$this->tablename} WHERE `{$pk_name}` = '$pk_val'";
      bbscript_log(LL::DEBUG, "Civi Query: $query");
      $civi_result = $log_db->query($query);
      $civi_row = $civi_result->fetchRow(DB_FETCHMODE_ASSOC);

      // Look up the full record in Logging
      $query = "SELECT $fields FROM {$this->logdb}.log_{$this->tablename} WHERE `{$pk_name}` = '$pk_val' ORDER BY log_date DESC LIMIT 1";
      bbscript_log(LL::DEBUG, "Log Query: $query");
      $log_result = $log_db->query($query);
      $log_row = $log_result->fetchRow(DB_FETCHMODE_ASSOC);

      // If either row did not populate, something is horrible wrong.  Record an error and move on.
      if (!$civi_row || !$log_row) {
        bbscript_log(LL::WARN, "Failed to find similar rows when searching {$this->tablename} on $pk_name=$pk_val");
        continue;
      }

      // Simple initial check for match
      if ($civi_row !== $log_row) {
        // If that check fails, find out which fields differ.  Record the error and move on.
        $invalid = array();
        foreach ($this->fields as $key => $val) {
          if ($civi_row[$key] != $log_row[$key]) {
            $invalid[] = $key;
          }
        }
        bbscript_log(LL::WARN, "Record from {$this->tablename} with $pk_name=$pk_val has mismatch on fields: ".implode(',',$invalid));
        continue;
      }

      // All is well, records match.
      bbscript_log(LL::INFO, "Found matching records for {$this->tablename} $pk_name=$pk_val ");
    }
  }
}