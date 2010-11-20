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
class CRM_Contact_Form_Task_ExportPrintProduction extends CRM_Contact_Form_Task {

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
		
		CRM_Utils_System::setTitle( ts('Print Production Export') );
        $this->addDefaultButtons( 'Export Print Production' );
		
    }

    function addRules( )
    {
        $this->addFormRule( array( 'CRM_Contact_Form_Task_ExportPrintProduction', 'formRule' ) );
    }
    
    static function formRule( $form, $rule) {
        $errors =array();
        if ( empty( $form['tag'] ) && empty( $form['taglist'] ) ) {
            //$errors['_qf_default'] = "Please select atleast one tag.";
        }
        return $errors;
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

	//generate random number for export and tables
	$rnd = mt_rand(1,9999999999999999);

	$this->_contactIds = array_unique($this->_contactIds);

	$ids = implode("),(",$this->_contactIds);
	$ids = "($ids)";

	$sql = "CREATE TEMPORARY TABLE tmpExport$rnd(id int not null primary key);";
	$dao = &CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );

	$sql = "INSERT INTO tmpExport$rnd VALUES$ids;";
        $dao = &CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );

	$sql = "SELECT c.id, c.contact_type, c.do_not_mail, c.first_name, c.last_name, c.middle_name, c.job_title, c.birth_date, c.is_deceased, c.organization_name, c.postal_greeting_custom, c.postal_greeting_display, c.addressee_custom, c.addressee_display, c.gender_id, c.prefix_id, c.suffix_id, cr.relationship_type_id, ch.household_name as household_name, ch.nick_name as household_nickname, ch.postal_greeting_display as household_postal_greeting_display, ch.addressee_display as household_Addressee_display, ";
	$sql .= "congressional_district_46, ny_senate_district_47, ny_assembly_district_48, election_district_49, county_50, county_legislative_district_51, town_52, ward_53, school_district_54, new_york_city_council_55, neighborhood_56, ";
	$sql .= "street_address, supplemental_address_1, supplemental_address_2, street_number, street_number_suffix, street_name, street_unit, city, postal_code, postal_code_suffix, state_province_id, geo_code_1, geo_code_2, county_id ";
	$sql .= " FROM civicrm_contact c ";
	$sql .= " LEFT JOIN civicrm_address a on a.contact_id=c.id AND a.is_primary=1 ";
	$sql .= " LEFT JOIN civicrm_value_district_information_7 di ON di.entity_id=a.id ";
	$sql .= " LEFT  JOIN civicrm_relationship cr ON cr.contact_id_a = c.id AND (cr.end_date IS NULL || cr.end_date > Now()) AND (cr.relationship_type_id=6 OR cr.relationship_type_id=7)";
        $sql .= " LEFT  JOIN civicrm_contact ch ON ch.id = cr.contact_id_b ";
	$sql .= " INNER JOIN tmpExport$rnd t ON c.id=t.id ";
	$sql .= " WHERE c.is_deleted=0 AND c.do_not_mail=0 ORDER BY CASE WHEN c.gender_id=2 THEN 1 ELSE 999 END, c.birth_date DESC;";

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
	$path = "$config->uploadDir.'printProduction/";

	if ( !file_exists($path) ) {
		mkdir( $path, 0775 );
	}

        $fname = $path.'/printExport'.$rnd.'.tsv';

//echo $config->uploadDir;exit;

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

		//$href = "mailto:?subject=print export task: printExport$rnd.tsv&body=".urlencode("http://".$_SERVER['HTTP_HOST'].str_replace('/data/www/nyss/','/',$config->uploadDir).$fname);
        $href = "mailto:?subject=print export task: printExport$rnd.tsv&body=".urlencode("http://".$_SERVER['HTTP_HOST'].'/nyssgetfile?file='.urlencode($fname));
		$status[] = "Task $rnd exported ". sizeof($this->_contactIds). " Contact(s). &nbsp;&nbsp;<a href=\"$href\">Click here</a> to email the link.";
        
        CRM_Core_Session::setStatus( $status );
    }//end of function


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
/*
function getIssueCodesForUser() {

	global $issueCodes;

	if (!isset($issueCodes)) {

		$issueCodes = array();

		$dao = &CRM_Core_DAO::executeQuery("SELECT id from civicrm_tag where name='Issue Codes';", CRM_Core_DAO::$_nullArray);
  		$dao->fetch();
  		$parent_id = $dao->id;

		getIssueCodeChildren($parent_id);


		$dao = &CRM_Core_DAO::executeQuery("SELECT id,name from civicrm_tag where parent_id = $parent_id;", CRM_Core_DAO::$_nullArray);
		$foundOne = false;
 		while ($dao->fetch()) {
				
			$foundOne = true;
			$codes[$dao->id] = $dao->name;
		}
	}
}

function getIssueCodeChildren($parent_id) {

	$global $issueCodes;

        $issueCodes = array_merge($codes,$arr);

        foreach ($arr as $key=>$val) {

           $arr = getIssueCodeChildren($key);

	}
}
*/
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

