<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
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
		
		require_once 'CRM/Core/Permission.php';
        if ( CRM_Core_Permission::check( 'export print production files' ) ) {
			$this->addElement('text', 'avanti_job_id', ts('Avanti Job ID') );
        }
		
		//4677
		$this->addElement('checkbox', 'merge_households', ts('Merge Household Records'), null );
		
		require_once 'CRM/Core/OptionGroup.php';
        $rts = CRM_Core_OptionGroup::values('record_type_20100906230753');
		$this->add( 'select', 'exclude_rt',  ts( 'Exclude Record Types' ), $rts, false, 
                    array( 'id' => 'exclude_rt',  'multiple'=> 'multiple', 'title' => ts('- select -') ));

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
	
	//get form values
	$params = $this->controller->exportValues( $this->_name );
	
	$avanti_job_id    = ( $params['avanti_job_id'] ) ? 'avanti-'.$params['avanti_job_id'].'_' : '';
	$merge_households = $params['merge_households'];
	$exclude_rt       = implode( ',', $params['exclude_rt'] );
	
	//get instance name (strip first element from url)
	$instance = substr( $_SERVER['HTTP_HOST'], 0, strpos( $_SERVER['HTTP_HOST'], '.' ) );

	//get option
	$aGender = getOptions("gender");
	$aSuffix = getOptions("individual_suffix");
    $aPrefix = getOptions("individual_prefix");
	$aStates = getStates();
	
	//generate random number for export and tables
	$rnd = mt_rand(1,9999999999999999);

    //retrieve Mailing Exclusions group id
	$eogid = CRM_Core_DAO::singleValueQuery( "SELECT id FROM civicrm_group WHERE name LIKE 'Mailing_Exclusions';" );
	if ( !$eogid ) $eogid = 0; //prevent errors if group is not found

	//add any members of the seed group
	$sql = "SELECT contact_id FROM civicrm_group_contact WHERE group_id = (SELECT id FROM civicrm_group WHERE name LIKE 'Mailing_Seeds') AND status = 'Added';";
	$dao = &CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );
	while ($dao->fetch()) $this->_contactIds[] = $dao->contact_id;

    $this->_contactIds = array_unique($this->_contactIds);

	$ids = implode("),(",$this->_contactIds);
	$ids = "($ids)";

	//create temp table to hold IDs
	$sql = "CREATE TEMPORARY TABLE tmpExport{$rnd}_IDs (id int not null primary key) TYPE=myisam;";
	$dao = CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );

	$sql = "INSERT INTO tmpExport{$rnd}_IDs VALUES $ids;";
    $dao = CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );

	//now construct sql to retrieve fields and inject in a second tmp table
	$cFlds = getColumns( 'columns' );
	$sFlds = getColumns( 'select' );
	
	//CRM_Core_Error::debug('cFlds', $cFlds);exit();
	
	$sql   = "CREATE TABLE tmpExport$rnd ( $cFlds ) TYPE = myisam;";
	$dao   = CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );
	//CRM_Core_Error::debug('dao tmp table2', $dao);exit();
	
	$sql  = "INSERT INTO tmpExport$rnd ";
	
	$sql .= "SELECT $sFlds ";
	
	//begin with temp table so we work with a smaller data set
	$sql .= " FROM tmpExport{$rnd}_IDs t ";
	
	$sql .= " JOIN civicrm_contact c ON t.id = c.id ";
	
	//join with address if primary or BOE mailing and non primary
	$sql .= " LEFT JOIN civicrm_address a ON a.contact_id=t.id AND a.id = IF((SELECT npm.id FROM civicrm_address npm WHERE npm.contact_id = t.id AND npm.location_type_id = 13 AND npm.is_primary = 0),(SELECT npm.id FROM civicrm_address npm WHERE npm.contact_id = t.id AND npm.location_type_id = 13 AND npm.is_primary = 0),(SELECT pm.id FROM civicrm_address pm WHERE pm.contact_id = t.id AND pm.is_primary = 1)) ";	
	$sql .= " LEFT JOIN civicrm_value_district_information_7 di ON di.entity_id=a.id ";
	
	//household joins
	$sql .= " LEFT JOIN civicrm_relationship cr ON cr.contact_id_a = t.id AND (cr.end_date IS NULL || cr.end_date > Now()) AND (cr.relationship_type_id=6 OR cr.relationship_type_id=7) ";
    $sql .= " LEFT JOIN civicrm_contact ch ON ch.id = cr.contact_id_b ";
	
	//join with group to exclude Mailing_Exclusions
	$sql .= " LEFT JOIN civicrm_group_contact cgc ON cgc.contact_id = t.id AND status = 'Added' AND group_id = $eogid ";
	
	//exclude RTs
	if ( $exclude_rt != null ) {
	    $sql .= " LEFT JOIN civicrm_value_constituent_information_1 cvci ON t.id = cvci.entity_id ";
	}
	
	//exclude deceased, trashed, do not mail
	$sql .= " WHERE c.is_deceased=0 AND c.is_deleted=0 AND c.do_not_mail=0 ";
	
	//exclude empty last name, empty org name (if org type), and empty address
	$sql .= " AND ( ( c.contact_type = 'Individual' AND c.last_name IS NOT NULL AND c.last_name != '' ) OR ( c.contact_type = 'Individual' AND c.organization_name IS NOT NULL AND c.organization_name != '' ) OR c.contact_type != 'Individual' ) ";
	$sql .= " AND ( ( c.contact_type = 'Organization' AND c.organization_name IS NOT NULL AND c.organization_name != '' ) OR c.contact_type != 'Organization' ) ";
	$sql .= " AND ( ( a.street_address IS NOT NULL AND a.street_address != '' ) OR ( a.supplemental_address_1 IS NOT NULL AND a.supplemental_address_1 != '' ) ) ";
	
	//exclude impossibly old contacts
	$sql .= " AND ( c.birth_date IS NULL OR c.birth_date = '' OR c.birth_date > '1901-01-01' ) ";
	
	//exclude mailing exclusion group
	$sql .= " AND ( cgc.id IS NULL ) "; 
	
	//exclude RTs
	if ( $exclude_rt != null ) {
	    $sql .= " AND ( cvci.record_type_61 IS NULL OR cvci.record_type_61 NOT IN ( $exclude_rt ) ) ";
	}
	
	//order export by individuals, oldest male, oldest female, empty gender values and empty birth dates last
	$sql .= " ORDER BY CASE WHEN c.contact_type='Individual' THEN 1 WHEN c.contact_type='Household' THEN 2 ELSE 3 END, "; 
	$sql .= " CASE WHEN c.gender_id=2 THEN 1 WHEN c.gender_id=1 THEN 2 WHEN c.gender_id=4 THEN 3 ELSE 999 END, ";
	$sql .= " IFNULL(c.birth_date, '9999-01-01');";
	
	//CRM_Core_Error::debug($sql); exit();

	$dao = CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );
	//CRM_Core_Error::debug('dao insert fields', $dao); exit();

	//merge Households
	if ( $merge_households ) {
		mergeHouseholds( "tmpExport$rnd" );
	}

	//check if printProduction subfolder exists; if not, create it
	$config =& CRM_Core_Config::singleton();
	$path   = $config->uploadDir.'printProduction/';

	if ( !file_exists($path) ) {
		mkdir( $path, 0775 );
	}
	
	//set filename, environment, and full path
    $filename = 'printExport_'.$instance.'_'.$avanti_job_id.$rnd.'.tsv'; 
	
	//strip /data/ and everything after environment value
	$env   = substr( $config->uploadDir, 6, strpos( $config->uploadDir, '/', 6 )-6 );
    $fname = $path.'/'.$filename;

	$fhout = fopen($fname, 'w');

	//passed by ref to build
	$issueCodes = null;
	getIssueCodesRecursive($issueCodes);

        $sql    = "SELECT tmp.id, t.tag_id FROM civicrm_entity_tag t INNER JOIN tmpExport$rnd tmp on t.entity_id=tmp.id";
        $issdao = CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );
        $iss    = array();

        while ($issdao->fetch()) {
	 		$ic = $issueCodes[$issdao->tag_id];
         	if (!empty($ic)) {
           		$iss[$issdao->id][] = $ic;
	 		}
		}

    $aHeader           = array();
	$firstLine         = true;
    $adjusted_count    = 0;
	$nonPrimaryMailing = array();
	
	//skip DAO fields
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
	
	//retrieve records from temp table
	$sql = "SELECT * FROM tmpExport$rnd";
	$dao = CRM_Core_DAO::executeQuery($sql, CRM_Core_DAO::$_nullArray);
	
	//fetch records
	while ($dao->fetch()) {
		//CRM_Core_Error::debug($dao);
		
		//set ids to have primary address removed if mailing address exists and not primary
		if ( $dao->addr_location_id == 13 && !$dao->addr_primary ) {
			$nonPrimaryMailing[] = $dao->id;
		}
		
		//add the issue codes
		if (!empty($iss[$dao->id])) $dao->issueCodes = implode(',',$iss[$dao->id]);

		//write out the header rowv2($fhout, $aOut,"\t",'',false,false);
		if ($firstLine) {

	        foreach($dao as $name=>$val) {
	            if (!isset($skipVars[$name])) {
					$aHeader[] = $name;
				}
			}
			//only append tags header if not already set
			if ( end( $aHeader ) != 'issueCodes' ) {
				$aHeader[] = "issueCodes";
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
				$aOut[$name] =  $val;
			}
		}

		//CRM_Core_Error::debug($aOut); exit();
		
		//handle empty prefix values and special prefixes that need reinterpreting
	 	if ( strlen(trim($aOut['prefix_id'])) == 0 && $aOut['contact_type'] == 'Individual' ) {
			//construct prefix using gender if possible
			if ( $aOut['gender_id'] == 'Male' ) $aOut['prefix_id'] = 'Mr.';
			elseif ( $aOut['gender_id']=="Female" ) $aOut['prefix_id']="Ms.";
			else $aOut['prefix_id']="";
			
			//reconstruct postal_greeting if Dear Lastname; else assume it's been set purposely
			if ( $aOut['postal_greeting_display'] == 'Dear '.$aOut['last_name'] ) {
				$aOut['postal_greeting_display'] = 'Dear '.$aOut['prefix_id'].' '.$aOut['last_name'];
			}
		} elseif ( $aOut['prefix_id'] == 'The Honorable' ) {
			//construct prefix using gender if possible
			if ( $aOut['gender_id'] == 'Male' ) $aOut['prefix_id'] = 'Mr.';
			elseif ( $aOut['gender_id']=="Female" ) $aOut['prefix_id']="Ms.";
			else $aOut['prefix_id']="";
			
			//reconstruct postal_greeting if Dear The Honorable Lastname; else assume it's been set purposely
			if ( $aOut['postal_greeting_display'] == 'Dear The Honorable '.$aOut['last_name'] ) {
				$aOut['postal_greeting_display'] = 'Dear '.$aOut['prefix_id'].' '.$aOut['last_name'];
			}
		}
 
		fputcsv2($fhout, $aOut,"\t",'',false,false);
		$adjusted_count++;
		
	} //dao fetch end
//exit();

	//generate issue code and keyword stats
	$ic_stats = statsIssueCodes( 'tmpExport'.$rnd );
	$key_stats = statsKeywords( 'tmpExport'.$rnd );
	$tag_stats = array_merge( array('Issue Code'=>'Count'), $ic_stats, array(''=>'','Keyword'=>'Count'), $key_stats);
	//CRM_Core_Error::debug($ic_stats); exit();
	//CRM_Core_Error::debug($key_stats); exit();
	//CRM_Core_Error::debug($tag_stats); exit();
	
	//set filename and full path
    $filenameStats = 'printExportTagStats_'.$instance.'_'.$avanti_job_id.$rnd.'.tsv';
    $fnameStats = $path.'/'.$filenameStats;
	$fhoutStats = fopen($fnameStats, 'w');
	
	//write to file
	foreach ( $tag_stats as $tag_name => $tag_stat ) {
		//fputcsv2($fhoutStats, $tag_stats,"\t",'',false,false);
		fwrite($fhoutStats, $tag_name."\t".$tag_stat."\n" );
	}
	
	$urlStats = "http://".$_SERVER['HTTP_HOST'].'/nyss_getfile?file='.$filenameStats;
	$urlcleanStats = urlencode( $urlStats );
	//end stats
	
	//get rid of temp tables
    $sql = "DROP TABLE tmpExport{$rnd}, tmpExport{$rnd}_IDs;";
    $dao = CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );	
		
	$url  = "http://".$_SERVER['HTTP_HOST'].'/nyss_getfile?file='.$filename;
	$urlclean = urlencode( $url );
	$body = "Contact export: $urlclean \r\n\r\n
			 Tag stats export: $urlcleanStats \r\n";
	$href = "mailto:?subject=print export: $filename&body=$body";
		
	$status = array();
	$status[] = "Print Production Export";
	$status[] = "District: $instance (task $rnd).";
	$status[] = sizeof($this->_contactIds). " contact(s) were originally retrieved.";
	$status[] = $adjusted_count. " contact(s) were exported after adjustments.";
	$status[] = "<a href=\"$href\">Click here</a> to email the link to print production.";
		
	require_once 'CRM/Core/Permission.php';
    if ( CRM_Core_Permission::check( 'export print production files' ) ) {
		$status[] = "Download the export file: <a href=\"$url\" target=\"_blank\">".$filename.'</a>';
		$status[] = "Download the stats file: <a href=\"$urlStats\" target=\"_blank\">".$filenameStats.'</a>';
    }
        
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

function getIssueCodesRecursive(&$issueCodes, $parent_id=null) {

	if ($parent_id==null) {

		$issueCodes = array();

		$dao = &CRM_Core_DAO::executeQuery("SELECT id from civicrm_tag where name='Issue Codes';", CRM_Core_DAO::$_nullArray);
  		$dao->fetch();
  		$parent_id = $dao->id;
	}

	$newCodes=array();
	$dao = &CRM_Core_DAO::executeQuery("SELECT id,name from civicrm_tag where parent_id = $parent_id;", CRM_Core_DAO::$_nullArray);
 	while ($dao->fetch()) $newCodes[$dao->id] = $dao->name; 
		
	foreach ($newCodes as $key=>$val) {

		$issueCodes[$key] = $val;
	
	        getIssueCodesRecursive($issueCodes, $key);
	}
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

function statsIssueCodes( $tmpTbl ) {

	$sql = "SELECT civicrm_tag.name, COUNT( civicrm_entity_tag.id ) as ic_count
  			FROM civicrm_entity_tag
       		 INNER JOIN civicrm_tag
       		  ON ( civicrm_entity_tag.tag_id = civicrm_tag.id )
			 INNER JOIN $tmpTbl
       		  ON ( $tmpTbl.id = civicrm_entity_tag.entity_id )
 			WHERE ( civicrm_entity_tag.entity_table LIKE '%civicrm_contact%' )
       		 AND ( civicrm_tag.parent_id != 292 )
			 AND ( civicrm_tag.parent_id != 296 )
       		 AND ( civicrm_tag.is_tagset != 1 )
			GROUP BY name
			ORDER BY ic_count DESC;";
	
    $dao = &CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );
    $ic_stats = array();
    while ($dao->fetch()) {
		$ic_stats[$dao->name] = $dao->ic_count;
	}

	return $ic_stats;
	
}

function statsKeywords( $tmpTbl ) {

	$sql = "SELECT civicrm_tag.name, COUNT( civicrm_entity_tag.id ) as key_count
  			FROM civicrm_entity_tag
       		 INNER JOIN civicrm_tag
       		  ON ( civicrm_entity_tag.tag_id = civicrm_tag.id )
			 INNER JOIN $tmpTbl
       		  ON ( $tmpTbl.id = civicrm_entity_tag.entity_id )
 			WHERE ( civicrm_entity_tag.entity_table LIKE '%civicrm_contact%' )
			 AND ( civicrm_tag.parent_id = 296 )
			GROUP BY name
			ORDER BY key_count DESC;";
	
    $dao = &CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );
    $key_stats = array();
    while ($dao->fetch()) {
		$key_stats[stripslashes($dao->name)] = $dao->key_count;
	}

	return $key_stats;
	
}

//merge temp table down into households
function mergeHouseholds( $tbl ) {
	
	//our resulting export could have actual household records OR
	//individuals who are part of households OR both
	
	//if a household record exists along with individuals, 
	//we can simply remove the individual records from the export
	$sql = "DELETE t1.*
            FROM $tbl t1
			JOIN $tbl t2
			  ON t1.household_id = t2.id
			WHERE t1.contact_type = 'Individual';";
    CRM_Core_DAO::executeQuery($sql);
	
	//if we have multiple individuals from a single household
	//we need to condense into a single record
	$sql = "CREATE TEMPORARY TABLE {$tbl}_hdupe 
	        SELECT id
			FROM $tbl
			WHERE household_id IS NOT NULL
			GROUP BY household_id
			HAVING count(id) > 1 ";
	CRM_Core_DAO::executeQuery($sql);
	
	$sql = "DELETE t1.*
	        FROM $tbl t1
			JOIN {$tbl}_hdupe t2
			  ON t1.id = t2.id";
	CRM_Core_DAO::executeQuery($sql);
	
	//now we want to copy the household greeting/address to the primary fields
	$sql = "UPDATE $tbl
	        SET postal_greeting_display = household_postal_greeting_display,
			    addressee_display = household_addressee_display,
				contact_type = 'Household-Individual'
			WHERE household_id IS NOT NULL AND
			    contact_type = 'Individual';";
	CRM_Core_DAO::executeQuery($sql);
	
	//drop temp table
	$sql = "DROP TEMPORARY TABLE {$tbl}_hdupe;";
    $dao = CRM_Core_DAO::executeQuery( $sql );
	
	return;

} //end mergeHouseholds

//defines the columns in our table and select statement
function getColumns( $output = 'select' ) {

	$fields = array( 
	    'c.id'           					=> array( 'alias' => 'id',
		                                              'def'   => 'int not null primary key' ),
	    'c.contact_type' 					=> array( 'alias' => 'contact_type',
		                                              'def'   => 'varchar(64)' ),
	    'c.first_name'   					=> array( 'alias' => 'first_name',
		                                              'def'   => 'varchar(64)' ),
	    'c.last_name'    					=> array( 'alias' => 'last_name',
		                                              'def'   => 'varchar(64)' ),
	    'c.middle_name'  					=> array( 'alias' => 'middle_name',
		                                              'def'   => 'varchar(64)' ),
	    'c.job_title'    					=> array( 'alias' => 'job_title',
		                                              'def'   => 'varchar(255)' ),
	    'c.birth_date'    					=> array( 'alias' => 'birth_date',
		                                              'def'   => 'varchar(32)' ),
	    'c.organization_name'    			=> array( 'alias' => 'organization_name',
		                                              'def'   => 'varchar(128)' ),
	    'c.postal_greeting_display'   		=> array( 'alias' => 'postal_greeting_display',
		                                              'def'   => 'varchar(255)' ),
	    'c.addressee_display'    			=> array( 'alias' => 'addressee_display',
		                                              'def'   => 'varchar(255)' ),
	    'c.gender_id'    					=> array( 'alias' => 'gender_id',
		                                              'def'   => 'varchar(64)' ),
	    'c.prefix_id'    					=> array( 'alias' => 'prefix_id',
		                                              'def'   => 'varchar(64)' ),
	    'c.suffix_id'    					=> array( 'alias' => 'suffix_id',
		                                              'def'   => 'varchar(64)' ),
	    'ch.id'    							=> array( 'alias' => 'household_id',
		                                              'def'   => 'varchar(64)' ),
	    'cr.relationship_type_id'    		=> array( 'alias' => 'relationship_type_id',
		                                              'def'   => 'varchar(64)' ),
	    'ch.household_name'    				=> array( 'alias' => 'household_name',
		                                              'def'   => 'varchar(128)' ),
	    'ch.nick_name'    					=> array( 'alias' => 'household_nickname',
		                                              'def'   => 'varchar(128)' ),
	    'ch.postal_greeting_display'		=> array( 'alias' => 'household_postal_greeting_display',
		                                              'def'   => 'varchar(255)' ),
	    'ch.addressee_display'    			=> array( 'alias' => 'household_addressee_display',
		                                              'def'   => 'varchar(255)' ),
	    'congressional_district_46'    		=> array( 'alias' => 'congressional_district_46',
		                                              'def'   => 'varchar(64)' ),
	    'ny_senate_district_47'    			=> array( 'alias' => 'ny_senate_district_47',
		                                              'def'   => 'varchar(64)' ),
	    'ny_assembly_district_48'    		=> array( 'alias' => 'ny_assembly_district_48',
		                                              'def'   => 'varchar(64)' ),
	    'election_district_49'    			=> array( 'alias' => 'election_district_49',
		                                              'def'   => 'varchar(64)' ),
	    'county_50'    						=> array( 'alias' => 'county_50',
		                                              'def'   => 'varchar(64)' ),
	    'county_legislative_district_51'	=> array( 'alias' => 'county_legislative_district_51',
		                                              'def'   => 'varchar(64)' ),
	    'town_52'    						=> array( 'alias' => 'town_52',
		                                              'def'   => 'varchar(64)' ),
	    'ward_53'    						=> array( 'alias' => 'ward_53',
		                                              'def'   => 'varchar(64)' ),
	    'school_district_54'    			=> array( 'alias' => 'school_district_54',
		                                              'def'   => 'varchar(64)' ),
	    'new_york_city_council_55'    		=> array( 'alias' => 'new_york_city_council_55',
		                                              'def'   => 'varchar(64)' ),
	    'neighborhood_56'    				=> array( 'alias' => 'neighborhood_56',
		                                              'def'   => 'varchar(64)' ),
	    'street_address'    				=> array( 'alias' => 'street_address',
		                                              'def'   => 'varchar(96)' ),
	    'supplemental_address_1'    		=> array( 'alias' => 'supplemental_address_1',
		                                              'def'   => 'varchar(96)' ),
	    'supplemental_address_2'    		=> array( 'alias' => 'supplemental_address_2',
		                                              'def'   => 'varchar(96)' ),
	    'street_number'    					=> array( 'alias' => 'street_number',
		                                              'def'   => 'varchar(16)' ),
	    'street_number_suffix'    			=> array( 'alias' => 'street_number_suffix',
		                                              'def'   => 'varchar(8)' ),
	    'street_name'    					=> array( 'alias' => 'street_name',
		                                              'def'   => 'varchar(64)' ),
	    'street_unit'    					=> array( 'alias' => 'street_unit',
		                                              'def'   => 'varchar(16)' ),
	    'city'    							=> array( 'alias' => 'city',
		                                              'def'   => 'varchar(64)' ),
	    'postal_code'    					=> array( 'alias' => 'postal_code',
		                                              'def'   => 'varchar(12)' ),
	    'postal_code_suffix'    			=> array( 'alias' => 'postal_code_suffix',
		                                              'def'   => 'varchar(12)' ),
	    'state_province_id'    				=> array( 'alias' => 'state_province_id',
		                                              'def'   => 'varchar(12)' ),
	    );
	
	switch ( $output ) {
		case 'select':
			$selectVals = array();
			$selectList = '';
			
			foreach ( $fields as $field => $details ) {
				$selectVals[] = $field.' as '.$details['alias'];
			}
			$selectList = implode( ', ', $selectVals );
			return $selectList;
			
			break;

		case 'columns':
			$colVals = array();
			$colList = '';
			
			foreach ( $fields as $field => $details ) {
				$colVals[] = $details['alias'].' '.$details['def'];
			}
			$colList = implode( ', ', $colVals );
			return $colList;
			
			break;
		
		default:

			return '';
	}
}