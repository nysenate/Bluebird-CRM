<?php

function get_connection($config)
{
  $dbuser = $config['signups.db.user'];
  $dbpass = $config['signups.db.pass'];
  $dbhost = $config['signups.db.host'];
  $dbport = (isset($config['signups.db.port'])) ? ':'.$config['signups.db.port'] : '';
  $dbname = $config['signups.db.name'];

  $conn_str = "$dbuser@$dbhost$dbport";
  $conn = mysql_connect("$dbhost$dbport", $dbuser, $dbpass, true);
  if (!$conn) {
    die("Could not establish connection to $conn_str\n".mysql_error()."\n");
  }

  if (!mysql_select_db($dbname, $conn)) {
    die("Could not use database $dbname at $conn_str\n".mysql_error($conn)."\n");
  }

  return $conn;
} // get_connection()


function get_report_path($bbcfg, $target_date)
{
  $datadir = $bbcfg['data.rootdir'];
  $sitedir = $bbcfg['servername'];
  $instance = $bbcfg['shortname'];
  $dirname = $bbcfg['signups.reports.dirname'];
  $dirpath = "$datadir/$sitedir/$dirname";

  if ($target_date == null) {
    if (isset($bbcfg['signups.reports.date_format'])) {
      $datefmt = $bbcfg['signups.reports.date_format'];
    }
    else {
      $datefmt = "Y.m.d";
    }
    $target_date = date($datefmt);
  }

  if (!is_dir($dirpath)) {
    if (!mkdir($dirpath, 0777, true)) {
      die("Could not create $dirpath\n");
    }
  }

  $template_options = array('<date>' => $target_date,
                            '<instance>' => $instance);

  if (isset($bbcfg['signups.reports.name_template'])) {
    $name_template = $bbcfg['signups.reports.name_template'];
  }
  else {
    $name_template = 'signups_<instance>_<date>.xls';
  }

  $filename = str_replace(array_keys($template_options),
                          array_values($template_options),
                          $name_template);

  return "$dirpath/$filename";
} // get_report_path()


function get_or_create_list($title, $conn)
{
  $title = mysql_real_escape_string($title, $conn);
  $sql = "SELECT id FROM list WHERE title='$title'";

  if ($result = mysql_query($sql,$conn)) {
    if ($row = mysql_fetch_assoc($result)) {
      //Existing List
      return $row['id'];
    }
    else {
      //New list
      $sql = "INSERT INTO list (title) VALUES ('$title')";
      if ($result = mysql_query($sql,$conn)) {
        return mysql_insert_id($conn);
      }
    }
  }

  die(mysql_error($conn)."\n".$sql);
} // get_or_create_list()


function get_or_create_issue($issue, $conn)
{
  static $issue_ids = array();

  if (isset($issue_ids[$issue]) === FALSE) {
    $sql = "SELECT id FROM issue WHERE name='$issue'";
    if (!$result = mysql_query($sql,$conn)) {
      die(mysql_error($conn)."\n".$sql);
    }

    if (mysql_num_rows($result)) {
      $row = mysql_fetch_assoc($result);
      $issue_ids[$issue] = $row['id'];
    }
    else {
      $sql = "INSERT INTO issue (name) VALUES ('$issue')";
      if (!$result = mysql_query($sql, $conn)) {
        die(mysql_error($conn)."\n".$sql);
      }

      $issue_ids[$issue] = mysql_insert_id();
    }
  }

  return $issue_ids[$issue];
} //get_or_create_issue

?>
