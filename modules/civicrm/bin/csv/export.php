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

class EntityExporter extends civicrm_cli {

    function __construct() {
       parent::__construct ();

       $this->separator = ",";
       $this->params = array ('version' => 3);
       if (sizeof($this->args >= 1)) {
         $this->entity = $this->args [0];
         // first one is an Entity ?
         $dao = _civicrm_api3_get_DAO ($this->entity);

         if (!$dao) 
           die ("\nusage: cd /your/civicrm_root;php bin/csv/export.php -u{username} -p{password} -s{site (or default)} {entity} and optionnaly field=value\n");
       }

       $result = civicrm_api ($this->entity , 'getfields', array ('version' => 3) );
       if ($result['is_error']) 
          die ("\n'$this->entity' isn't a recognized Entity\n");
       $this->columns =   array_keys ($result['values']);
       
       $argv = sizeof($this->args);
       for ($i =1; $i<$argv;$i++) {
           $t=split ('=',$this->args[$i]);
           if (sizeof ($t) == 2) {
             $this->params [$t[0]]= $t[1];
           } else {
             echo "\nWARNING: invalid param '".$this->args[$i]."' (expected field=value)\n";
           }
       }
    }

  function run() {
    $out = fopen("php://output", 'w');
    fputcsv($out, $this->columns, $this->separator, '"');

    $this->row = 1;
    $result = civicrm_api ($this->entity,'Get',$this->params);
    foreach ($result['values'] as $row) {
      fputcsv($out, $row, $this->separator, '"');
    }
    fclose($out);
  }
}

$entityExporter = new EntityExporter ();
$entityExporter->run();
echo "\n";
?>
