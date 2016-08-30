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
    $query = $this->createMatchQuery();
    bbscript_log(LL::DEBUG, "Running query $query");
    $records = CRM_Core_DAO::executeUnbufferedQuery($query);

    // Prep a new db connection
    $log_db = DB::connect(CIVICRM_LOGGING_DSN);

    // For each PK value in the civi table, pull the civi record, the log record,
    // and compare the two
    while ($records->fetch()) {
      bbscript_log(LL::DEBUG, "Found no-match record:\n".var_export($records,1));
      $bad_id = $records->id;
      $match_query = "SELECT * FROM {$this->logdb}.log_{$this->tablename} WHERE id=$bad_id ORDER BY log_date DESC LIMIT 1";
      $match_result = $log_db->query($match_query);
      if (!($match_row = $match_result->fetchRow(DB_FETCHMODE_ASSOC))) {
        bbscript_log(LL::ERROR, "Table:{$this->tablename}, ID:$bad_id: No log records found");
      }
      else {
        $bad_fields = array();
        foreach ($this->fields as $key => $val) {
          if ($records->{$key} != $match_row[$key]) {
            $bad_fields[] = $key;
          }
        }
        bbscript_log(LL::ERROR, "Table:{$this->tablename}, ID:$bad_id: Fields differ: " . implode(',', $bad_fields));
      }
    }
  }

  public function createMatchQuery() {
    if (is_null($this->tablename)) {
      bbscript_log(LL::ERROR, "createMatchQuery() called without specifying table");
      return false;
    }
    $fields = array();
    $join = array();
    foreach ($this->fields as $key=>$val) {
      $fields[] = "main.$key";
      $fields[] = "hist.$key as log_$key";
      $join[] = "((main.{$key} = hist.{$key}) or (main.{$key} IS NULL AND hist.{$key} IS NULL))";
    }
    $query = "SELECT " . implode(',',$fields) . " FROM {$this->cividb}.{$this->tablename} main " .
      "JOIN ( SELECT MAX(log_date) as max_date, id FROM {$this->logdb}.log_{$this->tablename} " .
      "GROUP BY id ) join_max ON (join_max.id = main.id) LEFT JOIN {$this->logdb}.log_{$this->tablename} hist " .
      "ON (join_max.max_date = hist.log_date) AND " . implode(' AND ', $join) . " WHERE hist.id IS NULL";
    return $query;
  }
}