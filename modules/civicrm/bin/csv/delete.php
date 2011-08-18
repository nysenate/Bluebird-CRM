<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 +--------------------------------------------------------------------+
 | Copyright Tech To The People http:tttp.eu (c) 2011                 |
 +--------------------------------------------------------------------+
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007.                                       |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

// call this script from civicrm_root
require_once ('bin/cli.php');
require_once 'api/api.php';
require_once 'api/v3/utils.php';

class EntityImporter extends civicrm_cli {

    function __construct() {
       parent::__construct ();

       $this->separator = ",";
       if (sizeof($this->args >= 1)) {
         $this->entity = $this->args [0];
         // first one is an Entity ?
         $dao = _civicrm_api3_get_DAO ($this->entity);

         if (!$dao) 
           die ("\nusage: cd /your/civicrm_root;php bin/csv/delete.php -u{username} -p{password} -s{site} (or default) {entity} {csvfile} (you get more help with only the entity\n");
       }

       if (sizeof($this->args) != 2) {
         $result = civicrm_api ($this->entity , 'getfields', array ('version' => 3) );
      	 die ("\nyou need to profide a csv file with at least one column 'id' \n");
       } 
       $this->file = $this->args [1];
    }

  function run() {
	$this->row = 1;
	$handle = fopen($this->file, "r");
	//header
	$header = fgetcsv($handle, 1000, $this->separator);
  if (!$header) {
    $this->separator = ";";
    rewind($handle);
	  $header = fgetcsv($handle, 1000, $this->separator);
  }
  if (!$header) {
    die ("invalid file format for". $this->file . ". I must be a valid csv with separator ',' or ';'");
  }
  $this->header  = $header;
	while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
	    $num = count($data);
	    $this->row++;
	    $params = $this->convertLine ($data);
	    $this->processLine ($params);
	    }
	fclose($handle);
    return;
  }

  function  processline ($params) {
    $result= civicrm_api ($this->entity,'Delete',$params);
    if ($result['is_error'])
       echo "\nERROR line ". $this->row . ": ".$result['error_message'] ."\n";
    else 
       echo "\nline ". $this->row . ": deleted ". $this->entity . " id= ".$params['id'] ."\n";
  }

  /* return a params as expected */
  function convertLine ($data) {
    $params = array('version' => 3);
    foreach ($this->header as $i => $field) {
      $params[$field] = $data [$i];
    }
    return $params;

  }
  }

$entityImporter = new EntityImporter ();
$entityImporter->run();
echo "\n";
?>
