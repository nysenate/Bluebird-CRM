<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'CRM/Contact/Form/Task.php';
require_once 'CRM/Core/BAO/EntityTag.php';

/**
 * This class provides the functionality to export large data sets for print production.
 */
class CRM_Contact_Form_Task_ExportDistrict extends CRM_Contact_Form_Task {

    /**
     * @var string
     */
    protected $_name;

    /**
     * all the tags in the system
     *
     * @var array
     */
    protected $_tags;

    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    function buildQuickForm( ) {
		
		CRM_Utils_System::setTitle( ts('Export District for Merge/Purge') );
        $this->addDefaultButtons( 'Export District' );
		
    }

    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return None
     */
    public function postProcess() {

	//get the list of genders
	$aGender = getOptions("gender");
	$aSuffix = getOptions("individual_suffix");
        $aPrefix = getOptions("individual_prefix");

	$aStates = getStates();
	
	//generate random number for export and tables
	$rnd = mt_rand(1,9999999999999999);

	//add any members of the seed group
	/*$sql = "SELECT contact_id FROM civicrm_group_contact WHERE group_id = (SELECT id FROM civicrm_group WHERE name LIKE 'Mailing Seeds');";
	$dao = &CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );
	while ($dao->fetch()) $this->_contactIds[] = $dao->contact_id;

        $this->_contactIds = array_unique($this->_contactIds);

	$ids = implode("),(",$this->_contactIds);
	$ids = "($ids)";*/

	$sql = "CREATE TEMPORARY TABLE tmpExport$rnd(id int not null primary key);";
	$dao = &CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );

	/*$sql = "INSERT INTO tmpExport$rnd VALUES$ids;";
    $dao = &CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );*/

	$sql = "SELECT c.id, c.first_name, c.middle_name, c.last_name, c.suffix_id, ";
	$sql .= "street_number, street_name, street_unit, supplemental_address_1, supplemental_address_2, city, state_province_id, postal_code, postal_code_suffix, ";
	$sql .= "c.birth_date, c.gender_id, phone, ";
	$sql .= "town_52, ward_53, election_district_49, congressional_district_46, ny_senate_district_47, ny_assembly_district_48, school_district_54, county_50, ";
	$sql .= "email, a.location_type_id, is_deleted ";

	$sql .= " FROM civicrm_contact c ";
	$sql .= " LEFT JOIN civicrm_address a on a.contact_id=c.id AND a.is_primary=1 ";
	$sql .= " LEFT JOIN civicrm_value_district_information_7 di ON di.entity_id=a.id ";
	$sql .= " LEFT JOIN civicrm_phone p on p.contact_id=c.id AND p.is_primary=1 ";
	$sql .= " LEFT JOIN civicrm_email e on e.contact_id=e.id AND e.is_primary=1 ";
	
	$sql .= " ORDER BY CASE WHEN c.gender_id=2 THEN 1 ELSE 999 END, c.birth_date;";

	$dao = &CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );

	$skipVars['_DB_DataObject_version'] = 1;
        $skipVars['__table'] = 1;
        $skipVars['N'] = 1;
        $skipVars['_database_dsn'] = 1;
        $skipVars['_query'] = 1;
        $skipVars['_DB_resultid'] = 1;
        $skipVars['_resultFields'] = 1;
        $skipVars['_link_loaded'] = 1;
        $skipVars['_join'] = 1;
        $skipVars['_lastError'] = 1;
        $skipVars['_database_dsn_md5'] = 1;
        $skipVars['_database'] = 1;

	$config =& CRM_Core_Config::singleton();

	//check if printProduction subfolder exists; if not, create it
	$path = $config->uploadDir.'printProduction/';

	if ( !file_exists($path) ) {
		mkdir( $path, 0775 );
	}
	
	//set filename, environment, and full path
    $filename = 'districtExport'.$rnd.'.tsv'; 
	//strip /data/ and everything after environment value
	$env = substr( $config->uploadDir, 6, strpos( $config->uploadDir, '/', 6 )-6 );
    $fname = $path.'/'.$filename;

	$fhout = fopen($fname, 'w');


    $aHeader=array();
	$firstLine = true;
        while ($dao->fetch()) {

		//write out the header rowv2($fhout, $aOut,"\t",'',false,false);
		if ($firstLine) {
			foreach($dao as $name=>$val) {
            	if (!isset($skipVars[$name])) {
					$aHeader[] = $name;
				}
			}

			fputcsv2($fhout, $aHeader,"\t",'',false,false);
			$firstLine=false;
		}

	    $aOut = array();
		foreach($dao as $name=>$val) {
			if (!isset($skipVars[$name])) {

				if ($name=="gender_id") $val = $aGender[$val];
                                if ($name=="suffix_id") $val = $aSuffix[$val];
                                if ($name=="prefix_id") $val = $aPrefix[$val];

                                if ($name=="state_province_id") $val = $aStates[$val];

				if ($name=="birth_date") {
					if (strtotime($val)) $val = date("Y-m-d",strtotime($val));
					else $val = "";
//print $val."\n";
				}
					
		                $val = str_replace("'","",$val);
                		$val = str_replace("\"","",$val);
				$aOut[] =  $val;
			}
		}

	    fputcsv2($fhout, $aOut,"\t",'',false,false);
	}
//exit;
		//get rid of helper table
        $sql = "DROP TABLE tmpExport$rnd;";
        $dao = &CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );

		$href = "mailto:?subject=print export task: districtExport$rnd.tsv&body=".urlencode( "http://".$_SERVER['HTTP_HOST'].'/nyss_getfile?file='.urlencode($filename) );
		$status[] = "Task $rnd exported ". sizeof($this->_contactIds). " Contact(s). &nbsp;&nbsp;<a href=\"$href\">Click here</a> to email the link.";
        
        CRM_Core_Session::setStatus( $status );
    } //end of function

}

function fputcsv2 ($fh, array $fields, $delimiter = ',', $enclosure = '"', $mysql_null = false, $blank_as_null = false) {

    $delimiter_esc = preg_quote($delimiter, '/');
    $enclosure_esc = preg_quote($enclosure, '/');

    $output = array();
    foreach ($fields as $field) {
        if ($mysql_null && ($field === null || ($blank_as_null && strlen($field)==0))) {
            $output[] = 'NULL';
            continue;
        }

        $output[] = preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field) ? (
            $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure
        ) : $field;
    }
    fwrite($fh, join($delimiter, $output) . "\n");
}

function getOptions($strGroup)
{
  $session =& CRM_Core_Session::singleton();

  $dao = &CRM_Core_DAO::executeQuery("SELECT id from civicrm_option_group where name='".$strGroup."';", CRM_Core_DAO::$_nullArray);
  $dao->fetch();
  $optionGroupID = $dao->id;

  $dao = &CRM_Core_DAO::executeQuery("SELECT name, label, value from civicrm_option_value where option_group_id=$optionGroupID;", CRM_Core_DAO::$_nullArray);

  $options = array();

  while ($dao->fetch()) {
    $name = (strlen($dao->label) > 0) ? $dao->label : $dao->name;
    $options[$dao->value] = $name;
  }

  return $options;
} // getOptions()

function getStates()
{
  $session =& CRM_Core_Session::singleton();

  $dao = &CRM_Core_DAO::executeQuery("SELECT id, abbreviation from civicrm_state_province", CRM_Core_DAO::$_nullArray);

  $options = array();

  while ($dao->fetch()) {
    $options[$dao->id] = $dao->abbreviation;
  }

  return $options;
} // getOptions()

