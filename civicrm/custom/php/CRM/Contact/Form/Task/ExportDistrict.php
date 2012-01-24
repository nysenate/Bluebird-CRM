<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
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

        require_once 'CRM/Core/Permission.php';
        if ( CRM_Core_Permission::check( 'export print production files' ) ) {
            $this->addElement('text', 'avanti_job_id', ts('Avanti Job ID') );
        }

        require_once 'CRM/Core/PseudoConstant.php';
        $locTypes = CRM_Core_PseudoConstant::locationType();
        $locTypes = array( 0 => 'Primary' ) + $locTypes;
        $this->add( 'select',
                    'locType',
                    ts( 'Address Location Type' ),
                    $locTypes,
                    false, 
                    array( 'id' => 'locType' )
                    );

        $this->addDefaultButtons( 'Export District' );

        $defaults['locType'] = 0;
        $this->setDefaults($defaults);
    }

    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return None
     */
    public function postProcess() {

        //get form values
        $params = $this->controller->exportValues( $this->_name );
        $avanti_job_id = ( $params['avanti_job_id'] ) ? 'avanti-'.$params['avanti_job_id'].'_' : '';
        $loc_type      = $params['locType'];

        //get instance name (strip first element from url)
        $instance = substr( $_SERVER['HTTP_HOST'], 0, strpos( $_SERVER['HTTP_HOST'], '.' ) );

        //get option lists
        $aGender = getOptions("gender");
        $aSuffix = getOptions("individual_suffix");
        $aPrefix = getOptions("individual_prefix");
        $aStates = getStates();

        //determine address location type clause
        $addressClause = '';

        if ( $loc_type == 0 ) {
            $addressClause = 'a.is_primary = 1';
        } else {
            $addressClause = "a.location_type_id = $loc_type";
        }

        //generate random number for export and tables
        $rnd = mt_rand(1,9999999999999999);

        $this->_contactIds = array_unique($this->_contactIds);

        $ids = implode("),(",$this->_contactIds);
        $ids = "($ids)";

        $sql = "CREATE TABLE tmpExport$rnd(id int not null primary key);";
        $dao = &CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );

        $sql = "INSERT INTO tmpExport$rnd VALUES$ids;";
        $dao = &CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );

        $sql = "SELECT c.id, c.first_name, c.middle_name, c.last_name, c.suffix_id, ";
        $sql .= "street_number, street_number_suffix, street_name, street_unit, street_address, supplemental_address_1, supplemental_address_2, city, state_province_id, postal_code, postal_code_suffix, ";
        $sql .= "c.birth_date, c.gender_id, phone, ";
        $sql .= "town_52, LPAD(ward_53,2,'0') as ward_53, LPAD(election_district_49,3,'0') as election_district_49, LPAD(congressional_district_46,2,'0') as congressional_district_46, LPAD(ny_senate_district_47,2,'0') as ny_senate_district_47, LPAD(ny_assembly_district_48,3,'0') as ny_assembly_district_48, LPAD(school_district_54,3,'0') as school_district_54, LPAD(county_50,2,'0') as county_50, ";
        $sql .= "email, a.location_type_id, is_deleted, a.id AS address_id, di.id AS districtinfo_id, p.id AS phone_id, e.id AS email_id ";

        $sql .= " FROM civicrm_contact c ";
        $sql .= " INNER JOIN tmpExport$rnd t on t.id=c.id ";
        $sql .= " LEFT JOIN civicrm_address a on a.contact_id=c.id AND $addressClause ";
        $sql .= " LEFT JOIN civicrm_value_district_information_7 di ON di.entity_id=a.id ";
        $sql .= " LEFT JOIN civicrm_phone p on p.contact_id=c.id AND p.is_primary=1 ";
        $sql .= " LEFT JOIN civicrm_email e on e.contact_id=c.id AND e.is_primary=1 ";
    
        //4874 - include last log record timestamp

        $sql .= " ORDER BY CASE WHEN c.gender_id=2 THEN 1 WHEN c.gender_id=1 THEN 2 WHEN c.gender_id=4 THEN 3 ELSE 999 END, ";
        $sql .= " IFNULL(c.birth_date, '9999-01-01');";
        //order export by oldest male, then oldest female
        //ensure empty values fall last

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
        $filename = 'districtExport_'.$instance.'_'.$avanti_job_id.$rnd.'.tsv'; 

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

        $url = "http://".$_SERVER['HTTP_HOST'].'/nyss_getfile?file='.$filename;
        $urlclean = urlencode( $url );
        $href = "mailto:?subject=district export: $filename&body=".$urlclean;

        $status = array();
        $status[] = "District Merge/Purge Export";
        $status[] = "District: $instance (task $rnd).";
        $status[] = sizeof($this->_contactIds). " contact(s) were exported.";
        $status[] = "<a href=\"$href\">Click here</a> to email the link to print production.";

        require_once 'CRM/Core/Permission.php';
        if ( CRM_Core_Permission::check( 'export print production files' ) ) {
            $status[] = "Download the file: <a href=\"$url\" target=\"_blank\">".$filename.'</a>';
        }

        CRM_Core_Session::setStatus( $status );
    } //end postProcess
} //end class

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

function getOptions($strGroup) {

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

function getStates() {

  $session =& CRM_Core_Session::singleton();

  $dao = &CRM_Core_DAO::executeQuery("SELECT id, abbreviation from civicrm_state_province", CRM_Core_DAO::$_nullArray);

  $options = array();

  while ($dao->fetch()) {
    $options[$dao->id] = $dao->abbreviation;
  }

  return $options;
} // getStates()
