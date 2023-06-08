<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
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

define('PPDEBUG', 0); //set debug mode status
define('EXITLOC', 0); //define exit location in script
define('TRACKTIME', 0); //track time at points in the script
define('BATCH', 5000); //when processing in batches, amount we do at a time

/**
 * This class provides the functionality to export large data sets for print production.
 */
class CRM_Contact_Form_Task_ExportPrintProduction extends CRM_Contact_Form_Task
{
  /**
   * @var string
   */
  protected $_name;

  /**
   * Build the form
   *
   * @access public
   * @return void
   */
  function buildQuickForm() {
    idebug($this, 'exportPrintProd buildForm $this', 3);

    CRM_Utils_System::setTitle(ts('Print Production Export'));

    require_once 'CRM/Core/Permission.php';
    if (CRM_Core_Permission::check('export print production files')) {
      $this->addElement('text', 'avanti_job_id', ts('Avanti Job ID'));
    }

    //4677
    $this->addElement('checkbox', 'merge_households', ts('Merge Household Records'), null);

    //5174
    $this->addElement('checkbox', 'primaryAddress', ts('Export Primary Address'), null);

    $select2style = [
      'multiple' => TRUE,
      'style' => 'width: 300px',
      'class' => 'crm-select2',
      'placeholder' => ts('- select -'),
    ];

    $rts = CRM_Core_OptionGroup::values('record_type_20100906230753');
    $this->add( 'select',
      'exclude_rt',
      ts('Exclude Record Types'),
      $rts,
      false,
      $select2style
    );

    $groups = CRM_Core_PseudoConstant::group( );
    $this->add( 'select',
      'excludeGroups',
      ts('Exclude Groups'),
      $groups,
      false,
      $select2style
    );

    //5142, 7719
    $bbconfig = get_bluebird_instance_config();
    if (!empty($bbconfig['export.use_district_excludes'])
      && CRM_Core_Permission::check('export print production files')
    ) {
      $this->addElement('text', 'district_excludes', ts('District # to Process Exclusions and Add Seeds') );
      $this->addRule( 'district_excludes',
        ts('Please enter the district exclusion as a number (integer only). This will also add the district seeds to the export.'),
        'positiveInteger'
      );
    }

    //5150
    $this->addElement('checkbox', 'excludeSeeds', ts('Exclude Seeds Group'), null );

    //5495
    $this->addElement('text', 'restrict_district', ts('Restrict by District #') );
    $this->addRule( 'restrict_district',
      ts('Please enter the district restriction as a number (integer only).'),
      'positiveInteger'
    );

    $states = CRM_Core_PseudoConstant::stateProvinceForCountry( 1228 );
    $this->add( 'select',
      'restrict_state',
      ts( 'Restrict by State' ),
      [0 => '- select -'] + $states,
      false,
      [
        'id' => 'restrict_state',
      ]
    );

    //8952
    $this->addElement('text', 'restrict_zip', ts('Restrict by Zip Code') );

    //7777 - restrict by all district info fields
    $this->addElement('text', 'di_congressional_district_46', ts('Restrict by Congressional District') );
    $this->addElement('text', 'di_ny_assembly_district_48', ts('Restrict by Assembly District') );
    $this->addElement('text', 'di_election_district_49', ts('Restrict by Election District') );
    $this->addElement('text', 'di_county_50', ts('Restrict by County') );
    $this->addElement('text', 'di_county_legislative_district_51', ts('Restrict by County Legislative District') );
    $this->addElement('text', 'di_town_52', ts('Restrict by Town') );
    $this->addElement('text', 'di_ward_53', ts('Restrict by Ward') );
    $this->addElement('text', 'di_school_district_54', ts('Restrict by School District') );
    $this->addElement('text', 'di_new_york_city_council_55', ts('Restrict by NYC Council') );

    //6397
    $orderBy = [
      'male_eldest' => 'Eldest Male',
      'female_eldest' => 'Eldest Female',
    ];
    $this->add( 'select',
      'orderBy',
      ts( 'Order By' ),
      $orderBy,
      false,
      [
        'id' => 'orderBy',
      ]
    );

    $this->addDefaultButtons( 'Export Print Production' );
  } // buildQuickForm()


  function setDefaultValues() {
    $defaults = [
      'orderBy' => 'male_eldest',
    ];
    //$defaults['restrict_state'] = 1031; //NY

    return $defaults;
  } // setDefaultValues()


  /**
   * process the form after the input has been submitted and validated
   *
   * @access public
   * @return None
   */
  public function postProcess() {
    ini_set('max_execution_time', 1800);

    //set start time
    itime('start');

    //get form values
    $params = $this->controller->exportValues($this->_name);
    idebug($params, 'exportPrintProd postProcess params', 2);

    $avanti_job_id = ( $params['avanti_job_id'] ) ? 'avanti-'.$params['avanti_job_id'].'_' : '';
    $merge_households = $params['merge_households'] ?? FALSE;
    $primaryAddress = $params['primaryAddress'] ?? FALSE;
    $exclude_rt = implode(',', $params['exclude_rt'] ?? []);
    $excludeGroups = $params['excludeGroups'] ?? [];
    $districtExclude = $params['district_excludes'] ?? NULL;
    $excludeSeeds = $params['excludeSeeds'] ?? FALSE;
    $restrictDistrict = $params['restrict_district'] ?? NULL;
    $restrictState = $params['restrict_state'] ?? NULL;
    $restrictZip = $params['restrict_zip'] ?? NULL;
    $orderByOpt = $params['orderBy'];

    //get instance name (strip first element from url)
    $instance = substr($_SERVER['HTTP_HOST'], 0, strpos($_SERVER['HTTP_HOST'], '.'));

    //get option
    $aGender = getOptions('gender');
    $aSuffix = getOptions('individual_suffix');
    $aPrefix = getOptions('individual_prefix');
    $aStates = getStates();

    //generate random number for export and tables
    $rnd = date('Ymdhis');
    $tmpTbl = "nyss_temp_export_$rnd";
    $tmpTblIds = $tmpTbl.'_IDs';

    //retrieve Mailing Exclusions group id
    $eogid = CRM_Core_DAO::singleValueQuery( "SELECT id FROM civicrm_group WHERE name LIKE 'Mailing_Exclusions';" );
    if (!$eogid) $eogid = 0; //prevent errors if group is not found

    //5150 add any members of the seed group unless intentionally excluding
    $localSeedsList = 0;
    if (!$excludeSeeds) {
      $localSeeds = [];
      $sql = "
        SELECT contact_id
        FROM civicrm_group_contact
        WHERE group_id = (SELECT id FROM civicrm_group WHERE name LIKE 'Mailing_Seeds')
        AND status = 'Added';";
      $dao = CRM_Core_DAO::executeQuery($sql);
      while ( $dao->fetch() ) {
        $this->_contactIds[] = $dao->contact_id;
        $localSeeds[] = $dao->contact_id;
      }
      $localSeedsList = implode(',',$localSeeds);
      if ( empty($localSeedsList) ) { $localSeedsList = 0; }
    }

    $this->_contactIds = array_unique($this->_contactIds);
    idebug($this->_contactIds, 'exportPrintProd postProcess $this->_contactIds', 2);

    $ids = implode("),(",$this->_contactIds);
    $ids = "($ids)";

    //create temp table to hold IDs
    $sql = "
      CREATE TEMPORARY TABLE $tmpTblIds
      (id int not null primary key)
      ENGINE = myisam;
    ";
    CRM_Core_DAO::executeQuery($sql);

    $sql = "INSERT INTO $tmpTblIds VALUES $ids;";
    CRM_Core_DAO::executeQuery($sql);
    itime('after building ID table');

    if ($excludeGroups) {
      excludeGroupContacts($tmpTblIds, $excludeGroups, $localSeedsList);
    }

    //now construct sql to retrieve fields and inject in a second tmp table
    $cFlds = getColumns( 'columns' );
    $sFlds = getColumns( 'select' );
    idebug($cFlds, 'cFlds', 2);

    $sql = "
      CREATE TABLE $tmpTbl
      ( $cFlds,
        INDEX match1 (first_name (50), middle_name (50), last_name (50), suffix_id (4), birth_date, gender_id)
      )
      ENGINE = myisam;";
    $dao = CRM_Core_DAO::executeQuery($sql);
    idebug($dao, 'dao tmp table2', 2);

    //begin with temp table so we work with a smaller data set
    $sql = "
      INSERT INTO $tmpTbl
      SELECT $sFlds FROM $tmpTblIds t
      JOIN civicrm_contact c ON t.id = c.id 
    ";

    //address joins
    if ($primaryAddress) {
      //5174 if selected, export primary address only
      $sql .= "
        LEFT JOIN civicrm_address a
          ON a.contact_id = t.id
          AND a.is_primary = 1 ";
    }
    else {
      //join with address if primary or BOE mailing and non primary
      $sql .= "
        LEFT JOIN civicrm_address a
          ON a.contact_id = t.id
          AND a.id = IF( (SELECT npm.id FROM civicrm_address npm WHERE npm.contact_id = t.id AND npm.location_type_id = 13 AND npm.is_primary = 0 LIMIT 1),(SELECT npm.id FROM civicrm_address npm WHERE npm.contact_id = t.id AND npm.location_type_id = 13 AND npm.is_primary = 0 LIMIT 1),(SELECT pm.id FROM civicrm_address pm WHERE pm.contact_id = t.id AND pm.is_primary = 1 LIMIT 1)) ";
    }
    $sql .= " LEFT JOIN civicrm_value_district_information_7 di ON di.entity_id = a.id ";

    //household joins
    $sql .= "
      LEFT JOIN civicrm_relationship cr
        ON cr.contact_id_a = t.id
        AND ( cr.end_date IS NULL || cr.end_date > Now() )
        AND ( cr.relationship_type_id = 6 OR cr.relationship_type_id = 7 )
        AND cr.is_active = 1 ";
    $sql .= " LEFT JOIN civicrm_contact ch ON ch.id = cr.contact_id_b ";

    //join with group to exclude Mailing_Exclusions
    $sql .= "
      LEFT JOIN civicrm_group_contact cgc
        ON cgc.contact_id = t.id
        AND status = 'Added'
        AND group_id = $eogid ";

    //exclude RTs
    if ($exclude_rt != null) {
      $sql .= "
        LEFT JOIN civicrm_value_constituent_information_1 cvci
          ON t.id = cvci.entity_id ";
    }

    //exclude deceased, trashed, do not mail, do not mail (undeliverable/trade)
    $sql .= " WHERE c.is_deceased = 0 AND c.is_deleted = 0 AND c.do_not_mail = 0 AND c.do_not_trade = 0 ";

    //exclude empty last name, empty org name (if org type), and empty address
    $sql .= " AND (
      (c.contact_type = 'Individual' AND c.last_name IS NOT NULL AND c.last_name != '') OR
      (c.contact_type = 'Individual' AND c.organization_name IS NOT NULL AND c.organization_name != '') OR
      (c.contact_type != 'Individual')
    ) ";
    $sql .= " AND (
      (c.contact_type = 'Organization' AND c.organization_name IS NOT NULL AND c.organization_name != '') OR
      (c.contact_type != 'Organization')
    ) ";
    $sql .= " AND (
      (a.street_address IS NOT NULL AND a.street_address != '') OR
      (a.supplemental_address_1 IS NOT NULL AND a.supplemental_address_1 != '')
    ) ";

    //exclude impossibly old contacts
    $sql .= " AND (
      c.birth_date IS NULL OR
      c.birth_date = '' OR
      c.birth_date > '1901-01-01'
    ) ";

    //exclude mailing exclusion group
    $sql .= " AND ( cgc.id IS NULL ) ";

    //exclude RTs
    if ( $exclude_rt != null ) {
      $sql .= " AND (
        cvci.record_type_61 IS NULL OR
        cvci.record_type_61 NOT IN ($exclude_rt) OR
        (cvci.record_type_61 IN ($exclude_rt) AND t.id IN ($localSeedsList))
      )";
    }

    //restrict by district ID
    if ( $restrictDistrict != null ) {
      $sql .= " AND (
        di.ny_senate_district_47 = $restrictDistrict OR
        (di.ny_senate_district_47 != $restrictDistrict AND t.id IN ($localSeedsList)) OR
        (di.ny_senate_district_47 IS NULL AND t.id IN ($localSeedsList))
      )";
    }

    //restrict by state
    if ( !empty($restrictState) ) {
      $sql .= " AND (
        a.state_province_id = $restrictState OR
        (a.state_province_id != $restrictState AND t.id IN ($localSeedsList)) OR
        (a.state_province_id IS NULL AND t.id IN ($localSeedsList))
      )";
    }

    //8952 restrict by zip code
    if ( !empty($restrictZip) ) {
      $restrictZipArray = explode(',', $restrictZip);
      $restrictZip = "'".implode("','", $restrictZipArray)."'";
      $restrictZip = ($restrictZip == "''") ? '' : $restrictZip;
      $sql .= " AND (
        a.postal_code IN ($restrictZip) OR
        (a.postal_code NOT IN ($restrictZip) AND t.id IN ($localSeedsList)) OR
        (a.postal_code IS NULL AND t.id IN ($localSeedsList))
      )";
    }

    //7777 cycle through and look for any district info fields
    foreach ($params as $f => $v) {
      if (!empty($f) && strpos($f, 'di_') === 0 && !empty($v)) {
        $dbFld = substr($f, 3);
        if (strpos($v, ',') !== FALSE) {
          $allVals = explode(',', $v);
          foreach ( $allVals as &$v ) {
            if (!is_numeric($v)) {
              $v = trim($v);
              $v = "'{$v}'";
            }
          }
          $valList = implode(',', $allVals);
          $sql .= " AND (
            di.{$dbFld} IN ({$valList}) OR
            (di.{$dbFld} NOT IN ({$valList}) AND t.id IN ($localSeedsList)) OR
            (di.{$dbFld} IS NULL AND t.id IN ($localSeedsList))
          )";
        }
        else {
          if (!is_numeric($v)) {
            $v = "'{$v}'";
          }
          $sql .= " AND (
            di.{$dbFld} = {$v} OR
            (di.{$dbFld} != {$v} AND t.id IN ($localSeedsList)) OR
            (di.{$dbFld} IS NULL AND t.id IN ($localSeedsList))
          )";
        }
      }
    }

    //group by contact ID in case any joins with multiple records cause dupe primary in our temp table
    $sql .= " GROUP BY c.id ";

    //6397 - determine gender based order by clause from params
    switch ($orderByOpt) {
      case 'female_eldest':
        $orderByGender = " CASE
          WHEN c.gender_id=1 THEN 1
          WHEN c.gender_id=2 THEN 2
          WHEN c.gender_id=4 THEN 3
          ELSE 999 END,
        ";
        break;
      default: //male_eldest
        $orderByGender = " CASE
          WHEN c.gender_id=2 THEN 1
          WHEN c.gender_id=1 THEN 2
          WHEN c.gender_id=4 THEN 3
          ELSE 999 END,
        ";
    }

    //order export by individuals, gender parameter, empty gender values and empty birth dates last
    $sql .= " ORDER BY CASE WHEN c.contact_type='Individual' THEN 1 WHEN c.contact_type='Household' THEN 2 ELSE 3 END, ";
    $sql .= $orderByGender;
    $sql .= " IFNULL(c.birth_date, '9999-01-01');";

    idebug($sql, 'sql');

    //first set mysql group by mode
    CRM_Core_DAO::executeQuery("SET SESSION sql_mode = '';");

    $dao = CRM_Core_DAO::executeQuery($sql);
    idebug($dao, 'dao insert fields', 2);
    itime('after inserting into full temp table');
    iexit(1);

    //merge Households
    if ( $merge_households ) {
      mergeHouseholds( $tmpTbl );
      itime('after merging households');
    }

    //5142 remove district exclusions
    if ( $districtExclude ) {
      processDistrictExclude( $districtExclude, $tmpTbl, $localSeedsList );
    }
    itime('after removing district exclusions');

    //remove the household_id column so print prod processing is not altered
    $sql = "ALTER TABLE $tmpTbl DROP COLUMN household_id;";
    CRM_Core_DAO::executeQuery($sql);

    //check if printProduction subfolder exists; if not, create it
    $config = CRM_Core_Config::singleton();
    $path = $config->uploadDir.'printProduction/';

    if (!file_exists($path)) {
      mkdir( $path, 0775 );
    }

    //set filename, environment, and full path
    $filename = 'printExport_'.$instance.'_'.$avanti_job_id.$rnd.'.tsv';

    //strip /data/ and everything after environment value
    $env = substr( $config->uploadDir, 6, strpos($config->uploadDir, '/', 6 )-6);
    $fname = $path.'/'.$filename;

    $fhout = fopen($fname, 'w');

    //passed by ref to build
    $issueCodes = NULL;
    getIssueCodesRecursive($issueCodes);
    $issueCodeIDs = (!empty($issueCodes)) ? implode(', ', array_keys($issueCodes)) : 0;
    itime('after getIssueCodesRecursive');

    $sql = "
      SELECT tmp.id, t.tag_id
      FROM civicrm_entity_tag t
      INNER JOIN $tmpTbl tmp
        ON t.entity_id = tmp.id
      WHERE tag_id IN ({$issueCodeIDs})
    ";
    $issdao = CRM_Core_DAO::executeQuery($sql);
    itime('after get entity_tag query');

    $iss = [];
    while ($issdao->fetch()) {
      $ic = $issueCodes[$issdao->tag_id];
      if (!empty($ic)) {
        $iss[$issdao->id][] = $ic;
      }
    }
    itime('after build $iss array');

    $aHeader = [];
    $firstLine = TRUE;
    $adjusted_count = 0;

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
    $sql = "SELECT * FROM $tmpTbl";
    /**
     * NYSS #9748
     * force an unbuffered query
     */
    $dao = CRM_Core_DAO::executeUnbufferedQuery($sql);

    //fetch records
    itime('before fetching records from temp table');
    while ($dao->fetch()) {
      idebug($dao, 'dao retrieve from temp table', 2);

      //add the issue codes
      if (!empty($iss[$dao->id])) $dao->issueCodes = implode(',',$iss[$dao->id]);

      //write out the header rowv2($fhout, $aOut,"\t",'',false,false);
      if ($firstLine) {
        foreach($dao as $name => $val) {
          if (!isset($skipVars[$name])) {
            $aHeader[] = $name;
          }
        }
        //only append tags header if not already set
        if (end($aHeader) != 'issueCodes') {
          $aHeader[] = "issueCodes";
        }

        CRM_NYSS_BAO_NYSS::fputcsv2($fhout, $aHeader, "\t", '', FALSE, FALSE);
        $firstLine = false;
      }

      //fields that have been preprocessed can be skipped
      $excludeFields = ['issueCodes'];

      $aOut = [];
      foreach($dao as $name => $val) {
        //replace IDs for option values; format dates; general string cleanup;
        if (!isset($skipVars[$name])) {
          if (!empty($val) && !in_array($name, $excludeFields)) {
            switch ($name) {
              case 'gender_id':
                $val = $aGender[$val];
                break;

              case 'suffix_id':
                $val = $aSuffix[$val];
                break;

              case 'prefix_id':
                $val = $aPrefix[$val];
                break;

              case 'state_province_id':
                $val = $aStates[$val];
                break;

              case 'birth_date':
                $val = date('Y-m-d', strtotime($val));
                break;

              default:
            }

            $val = str_replace("'", "", $val);
            $val = str_replace("\"", "", $val);
          }

          $aOut[$name] = $val;
        }
      }

      idebug($aOut, 'aOut', 1);
      iexit(2);

      //handle empty prefix values and special prefixes that need reinterpreting
      if (strlen(trim($aOut['prefix_id'])) == 0 && $aOut['contact_type'] == 'Individual') {
        //construct prefix using gender if possible
        if ($aOut['gender_id'] == 'Male') {
          $aOut['prefix_id'] = 'Mr.';
        }
        elseif ($aOut['gender_id'] == "Female") {
          $aOut['prefix_id'] = "Ms.";
        }
        else {
          $aOut['prefix_id'] = "";
        }

        //reconstruct postal_greeting if Dear Lastname; else assume it's been set purposely
        if ($aOut['postal_greeting_display'] == 'Dear '.$aOut['last_name']) {
          $aOut['postal_greeting_display'] = 'Dear '.$aOut['prefix_id'].' '.$aOut['last_name'];
        }
      }
      elseif ($aOut['prefix_id'] == 'The Honorable') {
        //construct prefix using gender if possible
        if ($aOut['gender_id'] == 'Male') {
          $aOut['prefix_id'] = 'Mr.';
        }
        elseif ($aOut['gender_id'] == "Female") {
          $aOut['prefix_id'] = "Ms.";
        }
        else {
          $aOut['prefix_id'] = "";
        }

        //reconstruct postal_greeting if Dear The Honorable Lastname; else assume it's been set purposely
        if ($aOut['postal_greeting_display'] == 'Dear The Honorable '.$aOut['last_name']) {
          $aOut['postal_greeting_display'] = 'Dear '.$aOut['prefix_id'].' '.$aOut['last_name'];
        }
      }

      CRM_NYSS_BAO_NYSS::fputcsv2($fhout, $aOut, "\t", '', FALSE, FALSE, TRUE);
      $adjusted_count++;
    } //dao fetch end

    $dao->free();

    //batch cleanup
    CRM_NYSS_BAO_NYSS::fputcsv2($fhout, $aOut, "\t", '', FALSE, FALSE, TRUE, TRUE);
    itime('after fetching records and writing to file');

    //generate issue code and keyword stats
    $ic_stats = statsIssueCodes($tmpTbl);
    $key_stats = statsKeywords($tmpTbl);
    $tag_stats = array_merge(['Issue Code'=>'Count'], $ic_stats, [''=>'','Keyword'=>'Count'], $key_stats);

    //set filename and full path
    $filenameStats = 'printExportTagStats_'.$instance.'_'.$avanti_job_id.$rnd.'.tsv';
    $fnameStats = $path.'/'.$filenameStats;
    $fhoutStats = fopen($fnameStats, 'w');

    idebug($filename, 'filename', 2);
    idebug($filenameStats, 'filenameStats', 2);

    //write to file
    foreach ($tag_stats as $tag_name => $tag_stat) {
      fwrite($fhoutStats, $tag_name."\t".$tag_stat."\n" );
    }

    $urlStats = "http://".$_SERVER['HTTP_HOST'].'/nyss_getfile?file='.$filenameStats;
    $urlcleanStats = urlencode( $urlStats );
    //end stats

    //get rid of temp tables
    $sql = "DROP TABLE $tmpTbl, $tmpTblIds;";
    CRM_Core_DAO::executeQuery($sql);

    $url = "http://".$_SERVER['HTTP_HOST'].'/nyss_getfile?file='.$filename;
    $urlclean = urlencode( $url );
    $body = "Contact export: $urlclean \r\n\r\n
             Tag stats export: $urlcleanStats \r\n";
    $href = "mailto:?subject=print export: $filename&body=$body";

    $status = [];
    $status[] = "Print Production Export";
    $status[] = "District: $instance (task $rnd).";
    $status[] = sizeof($this->_contactIds). " contact(s) were originally retrieved.";
    $status[] = $adjusted_count. " contact(s) were exported after adjustments.";
    $status[] = "<a href=\"$href\">Click here</a> to email the link to print production.";

    if (CRM_Core_Permission::check('export print production files')) {
      $status[] = "Download the export file: <a href=\"$url\" target=\"_blank\">".$filename.'</a>';
      $status[] = "Download the stats file: <a href=\"$urlStats\" target=\"_blank\">".$filenameStats.'</a>';
    }

    $statusOutput = '<ul>';
    foreach ($status as $st) {
      $statusOutput .= "<li>{$st}</li>";
    }
    $statusOutput .= "</ul>";

    $this->set('status', $status);
    $this->set('statusOutput', $statusOutput);

    //CRM_Core_Session::setStatus( $statusOutput, 'Print Production Export Results', 'no-popup' );

    itime('final', TRUE);
    iexit(4);
  } // postProcess()
}//end class

function getIssueCodesRecursive(&$issueCodes, $parent_id = NULL) {
  if ($parent_id == NULL) {
    $issueCodes = [];

    $dao = CRM_Core_DAO::executeQuery("
      SELECT id
      FROM civicrm_tag
      WHERE name = 'Issue Codes';
    ");
    $dao->fetch();
    $parent_id = $dao->id;
    $dao->free();
  }

  $dao = CRM_Core_DAO::executeQuery("
    SELECT id, name
    FROM civicrm_tag
    WHERE parent_id = $parent_id;
  ");

  while ($dao->fetch()) {
    $issueCodes[$dao->id] = $dao->name;
    getIssueCodesRecursive($issueCodes, $dao->id);
  }
  $dao->free();
} // getIssueCodesRecursive()


function getOptions($strGroup) {
  $dao = CRM_Core_DAO::executeQuery("
    SELECT id
    FROM civicrm_option_group
    WHERE name='".$strGroup."';
  ");
  $dao->fetch();
  $optionGroupID = $dao->id;
  $dao->free();

  $dao = CRM_Core_DAO::executeQuery("
    SELECT name, label, value
    FROM civicrm_option_value
    WHERE option_group_id=$optionGroupID;
  ");

  $options = [];
  while ($dao->fetch()) {
    $name = (strlen($dao->label) > 0) ? $dao->label : $dao->name;
    $options[$dao->value] = $name;
  }
  $dao->free();

  return $options;
} //getOptions()


function getStates() {
  $dao = CRM_Core_DAO::executeQuery("
    SELECT id, abbreviation
    FROM civicrm_state_province
  ");

  $options = [];
  while ($dao->fetch()) {
    $options[$dao->id] = $dao->abbreviation;
  }
  $dao->free();

  return $options;
} //getStates()


function statsIssueCodes( $tmpTbl ) {
  $sql = "
    SELECT civicrm_tag.name, COUNT( civicrm_entity_tag.id ) as ic_count
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

  $dao = CRM_Core_DAO::executeQuery($sql);
  $ic_stats = [];
  while ($dao->fetch()) {
    $ic_stats[stripslashes(iconv('UTF-8', 'Windows-1252', $dao->name))] = $dao->ic_count;
  }
  $dao->free();

  return $ic_stats;
} // statsIssueCodes()


function statsKeywords( $tmpTbl ) {
  $sql = "
    SELECT civicrm_tag.name, COUNT( civicrm_entity_tag.id ) as key_count
    FROM civicrm_entity_tag
    INNER JOIN civicrm_tag
      ON ( civicrm_entity_tag.tag_id = civicrm_tag.id )
    INNER JOIN $tmpTbl
      ON ( $tmpTbl.id = civicrm_entity_tag.entity_id )
    WHERE ( civicrm_entity_tag.entity_table LIKE '%civicrm_contact%' )
      AND ( civicrm_tag.parent_id = 296 )
    GROUP BY name
    ORDER BY key_count DESC;";

  $dao = CRM_Core_DAO::executeQuery($sql);
  $key_stats = [];
  while ($dao->fetch()) {
    $key_stats[stripslashes(iconv('UTF-8', 'Windows-1252', $dao->name))] = $dao->key_count;
  }
  $dao->free();

  return $key_stats;
} // statsKeywords()


//merge temp table down into households
function mergeHouseholds( $tbl ) {
  //our resulting export could have actual household records OR
  //individuals who are part of households OR both

  //if a household record exists along with individuals,
  //we can simply remove the individual records from the export
  $sql = "
    DELETE t1.*
    FROM $tbl t1
    JOIN $tbl t2
      ON t1.household_id = t2.id
    WHERE t1.contact_type = 'Individual';";
  CRM_Core_DAO::executeQuery($sql);

  //if we have multiple individuals from a single household
  //we need to condense into a single record
  $sql = "
    CREATE TEMPORARY TABLE {$tbl}_hdupe
    SELECT id
    FROM $tbl
    WHERE household_id IS NOT NULL
    GROUP BY household_id
    HAVING count(id) > 1 ";
  CRM_Core_DAO::executeQuery($sql);

  $sql = "
    DELETE t1.*
    FROM $tbl t1
    JOIN {$tbl}_hdupe t2
    ON t1.id = t2.id";
  CRM_Core_DAO::executeQuery($sql);

  //now we want to copy the household greeting/address to the primary fields
  $sql = "
    UPDATE $tbl
    SET postal_greeting_display = household_postal_greeting_display,
      addressee_display = household_addressee_display,
      contact_type = 'Household-Individual'
    WHERE household_id IS NOT NULL
      AND contact_type = 'Individual';";
  CRM_Core_DAO::executeQuery($sql);

  //drop temp table
  $sql = "DROP TEMPORARY TABLE {$tbl}_hdupe;";
  CRM_Core_DAO::executeQuery($sql);

  return;
} // mergeHouseholds()


//defines the columns in our table and select statement
function getColumns( $output = 'select' ) {
  $fields = [
    'c.id' => [
      'alias' => 'id',
      'def'   => 'int not null primary key'
    ],
    'c.contact_type' => [
      'alias' => 'contact_type',
      'def'   => 'varchar(64)'
    ],
    'c.first_name' => [
      'alias' => 'first_name',
      'def'   => 'varchar(64)'
    ],
    'c.last_name' => [
      'alias' => 'last_name',
      'def'   => 'varchar(64)'
    ],
    'c.middle_name' => [
      'alias' => 'middle_name',
      'def'   => 'varchar(64)'
    ],
    'c.job_title' => [
      'alias' => 'job_title',
      'def'   => 'varchar(255)'
    ],
    'c.birth_date' => [
      'alias' => 'birth_date',
      'def'   => 'varchar(32)'
    ],
    'c.organization_name' => [
      'alias' => 'organization_name',
      'def'   => 'varchar(128)'
    ],
    'c.postal_greeting_display' => [
      'alias' => 'postal_greeting_display',
      'def'   => 'varchar(255)'
    ],
    'c.addressee_display' => [
      'alias' => 'addressee_display',
      'def'   => 'varchar(255)'
    ],
    'c.gender_id' => [
      'alias' => 'gender_id',
      'def'   => 'varchar(64)'
    ],
    'c.prefix_id' => [
      'alias' => 'prefix_id',
      'def'   => 'varchar(64)'
    ],
    'c.suffix_id' => [
      'alias' => 'suffix_id',
      'def'   => 'varchar(64)'
    ],
    'ch.id' => [
      'alias' => 'household_id',
      'def'   => 'varchar(64)'
    ],
    'cr.relationship_type_id' => [
      'alias' => 'relationship_type_id',
      'def'   => 'varchar(64)'
    ],
    'ch.household_name' => [
      'alias' => 'household_name',
      'def'   => 'varchar(128)'
    ],
    'ch.nick_name' => [
      'alias' => 'household_nickname',
      'def'   => 'varchar(128)'
    ],
    'ch.postal_greeting_display' => [
      'alias' => 'household_postal_greeting_display',
      'def'   => 'varchar(255)'
    ],
    'ch.addressee_display' => [
      'alias' => 'household_addressee_display',
      'def'   => 'varchar(255)'
    ],
    'LPAD(congressional_district_46,2,\'0\')'=> [
      'alias' => 'congressional_district_46',
      'def'   => 'varchar(64)'
    ],
    'LPAD(ny_senate_district_47,2,\'0\')' => [
      'alias' => 'ny_senate_district_47',
      'def'   => 'varchar(64)'
    ],
    'LPAD(ny_assembly_district_48,3,\'0\')' => [
      'alias' => 'ny_assembly_district_48',
      'def'   => 'varchar(64)'
    ],
    'LPAD(election_district_49,3,\'0\')' => [
      'alias' => 'election_district_49',
      'def'   => 'varchar(64)'
    ],
    'LPAD(county_50,2,\'0\')' => [
      'alias' => 'county_50',
      'def'   => 'varchar(64)'
    ],
    'LPAD(county_legislative_district_51,2,\'0\')' => [
      'alias' => 'county_legislative_district_51',
      'def'   => 'varchar(64)'
    ],
    'town_52' => [
      'alias' => 'town_52',
      'def'   => 'varchar(64)'
    ],
    'LPAD(ward_53,2,\'0\')' => [
      'alias' => 'ward_53',
      'def'   => 'varchar(64)'
    ],
    'LPAD(school_district_54,3,\'0\')' => [
      'alias' => 'school_district_54',
      'def'   => 'varchar(64)'
    ],
    'LPAD(new_york_city_council_55,2,\'0\')' => [
      'alias' => 'new_york_city_council_55',
      'def'   => 'varchar(64)'
    ],
    'street_address' => [
      'alias' => 'street_address',
      'def'   => 'varchar(96)'
    ],
    'supplemental_address_1' => [
      'alias' => 'supplemental_address_1',
      'def'   => 'varchar(96)'
    ],
    'supplemental_address_2' => [
      'alias' => 'supplemental_address_2',
      'def'   => 'varchar(96)'
    ],
    'street_number' => [
      'alias' => 'street_number',
      'def'   => 'varchar(16)'
    ],
    'street_number_suffix' => [
      'alias' => 'street_number_suffix',
      'def'   => 'varchar(8)'
    ],
    'street_name' => [
      'alias' => 'street_name',
      'def'   => 'varchar(64)'
    ],
    'street_unit' => [
      'alias' => 'street_unit',
      'def'   => 'varchar(16)'
    ],
    'city' => [
      'alias' => 'city',
      'def'   => 'varchar(64)'
    ],
    'postal_code' => [
      'alias' => 'postal_code',
      'def'   => 'varchar(12)'
    ],
    'postal_code_suffix' => [
      'alias' => 'postal_code_suffix',
      'def'   => 'varchar(12)'
    ],
    'state_province_id' => [
      'alias' => 'state_province_id',
      'def'   => 'varchar(12)'
    ],
  ];

  switch ( $output ) {
    case 'select':
      $selectVals = [];

      foreach ($fields as $field => $details) {
        $selectVals[] = $field.' as '.$details['alias'];
      }

      return implode(', ', $selectVals);

    case 'columns':
      $colVals = [];

      foreach ($fields as $field => $details) {
        $colVals[] = $details['alias'].' '.$details['def'];
      }

      return implode( ', ', $colVals );

    default:
      return '';
  }
} // getColumns()


function excludeGroupContacts( $tbl, $groups, $localSeedsList ) {
  //get group contacts
  $excludeContacts = [];
  foreach ( $groups as $group ) {
    $groupContacts = CRM_Contact_BAO_Group::getMember( $group );
    $excludeContacts = array_merge( $excludeContacts, array_keys($groupContacts) );
  }
  $contactList = implode(',', $excludeContacts);

  $localSeedsList = (!empty($localSeedsList)) ? $localSeedsList : 0;

  //remove contacts from temp table
  $sql = "
    DELETE FROM $tbl
    WHERE id IN ( $contactList )
      AND id NOT IN ( $localSeedsList );";
  CRM_Core_DAO::executeQuery($sql);

  return;
} // excludeGroupContacts()


/**
*
* 5142
* given a district ID, collect district exclusions and remove from the import
*
*/
function processDistrictExclude( $districtID, $tbl, $localSeedsList ) {
  itime('processDistrictExclude start');

  //retrieve the instance name using the district ID
  $instance = $dbBase = '';
  $bbFullConfig = get_bluebird_config();
  foreach ($bbFullConfig as $group => $details) {
    if (!empty($group) && strpos($group, 'instance:') !== false) {
      if ($details['district'] == $districtID) {
        $instance = substr($group, 9);
        $dbBase = $details['db.basename'];
        break;
      }
    }
  }

  $localSeedsList = ( $localSeedsList ) ? $localSeedsList : 0;

  //retrieve values using db basename and create temp table
  $db = $bbFullConfig['globals']['db.civicrm.prefix'].$dbBase;
  $dTbl = "{$tbl}_d{$districtID}";

  //need to list sa columns to avoid naming conflicts
  $sql = "
    CREATE TABLE $dTbl
    (INDEX match1 (first_name ( 50 ), middle_name ( 50 ), last_name ( 50 ), suffix_id (4), birth_date, gender_id))
    ENGINE=myisam
    SELECT c.id, sc.*, sa.address_id, sa.street_address, sa.country_id, sa.state_province_id, sa.supplemental_address_1, sa.supplemental_address_2, sa.postal_code, sa.city
    FROM $db.civicrm_contact c
    LEFT JOIN $db.shadow_contact sc
      ON c.id = sc.contact_id
    LEFT JOIN $db.shadow_address sa
      ON c.id = sa.contact_id
    WHERE c.is_deleted = 0
      AND ( c.do_not_mail = 1 OR c.do_not_trade = 1 )";
  CRM_Core_DAO::executeQuery($sql);
  itime('processDistrictExclude after query execute');

  //now compare the district exclude table ($dTbl) to the main export table ($tbl)
  //and remove matches from the main table
  //run with three separate queries as it's much faster than a single where clause with OR
  $contactElements = [
    "-- Individual check
    ( contact_type = 'Individual'
      AND BB_NORMALIZE(source.last_name) = district.last_name
      AND BB_NORMALIZE(source.first_name) = district.first_name
      AND (source.suffix_id IS NULL OR district.suffix_id IS NULL OR source.suffix_id = district.suffix_id)
      AND (source.middle_name IS NULL OR district.middle_name IS NULL OR BB_NORMALIZE(source.middle_name) = district.middle_name)
      AND (source.birth_date IS NULL OR district.birth_date IS NULL OR source.birth_date = district.birth_date)
      AND (source.gender_id IS NULL OR district.gender_id IS NULL OR source.gender_id = district.gender_id) )",
    "-- Organization checks
    ( contact_type = 'Organization'
      AND BB_NORMALIZE(source.organization_name) = district.organization_name )",
    "-- Household checks
    ( contact_type = 'Household'
      AND BB_NORMALIZE(source.household_name) = district.household_name )",
  ];
  foreach ( $contactElements as $ele ) {
    $sql = "
      DELETE FROM $tbl
      WHERE id IN ( SELECT id FROM (
        SELECT source.id
        FROM $tbl as source JOIN $dTbl as district USING (contact_type)
        WHERE
        -- contact specific checks
        $ele
        -- AND all of the address checks pass
        AND source.postal_code=district.postal_code
        AND BB_NORMALIZE_ADDR(source.street_address) = district.street_address
        AND (source.city IS NULL OR district.city IS NULL OR BB_NORMALIZE_ADDR(source.city) = district.city)
        AND (source.state_province_id IS NULL OR district.state_province_id IS NULL OR source.state_province_id = district.state_province_id)
        ) AS tmpMatch
      )
      AND id NOT IN ($localSeedsList);";
    idebug($sql, 'dedupe match sql');
    $dao = CRM_Core_DAO::executeQuery($sql);
  }
  itime('processDistrictExclude after dedupe comparison');

  //remove temp exclusion table
  $sql = "DROP TABLE $dTbl;";
  $dao = CRM_Core_DAO::executeQuery($sql);

  //now retrieve district seeds and add them to the main temp table
  addExternalSeeds($tbl, $db);
  itime('processDistrictExclude after addExternalSeeds');

  return;
} // processDistrictExclude()


function addExternalSeeds($tbl, $db) {
  $sFlds = getColumns( 'select' );
  $sFlds = str_replace( 'c.id as id', "(c.id + 1000000000) as id", $sFlds ); //avoid conflicts with source db

  $eogid = CRM_Core_DAO::singleValueQuery( "SELECT id FROM $db.civicrm_group WHERE name LIKE 'Mailing_Seeds';" );
  if ( !$eogid ) $eogid = 0;

  $sql = "
    INSERT INTO {$tbl}
    SELECT $sFlds
    FROM $db.civicrm_contact c";
  $sql .= "
    LEFT JOIN $db.civicrm_address a
      ON a.contact_id=c.id
      AND a.id = IF((SELECT npm.id
        FROM $db.civicrm_address npm
        WHERE npm.contact_id = c.id
           AND npm.location_type_id = 13
           AND npm.is_primary = 0
        LIMIT 1),
        (SELECT npm.id
        FROM $db.civicrm_address npm
        WHERE npm.contact_id = c.id
          AND npm.location_type_id = 13
          AND npm.is_primary = 0
        LIMIT 1),
        (SELECT pm.id
        FROM $db.civicrm_address pm
        WHERE pm.contact_id = c.id
          AND pm.is_primary = 1
        LIMIT 1)) ";
  $sql .= "
    LEFT JOIN $db.civicrm_value_district_information_7 di
      ON di.entity_id = a.id ";

  //household joins
  $sql .= "
    LEFT JOIN $db.civicrm_relationship cr
      ON cr.contact_id_a = c.id
      AND ( cr.end_date IS NULL || cr.end_date > Now() )
      AND ( cr.relationship_type_id = 6 OR cr.relationship_type_id = 7 )
      AND cr.is_active = 1 ";
  $sql .= "
    LEFT JOIN $db.civicrm_contact ch
      ON ch.id = cr.contact_id_b ";

  //join with group to include Mailing_Exclusions
  $sql .= "
    JOIN $db.civicrm_group_contact cgc
      ON cgc.contact_id = c.id
      AND status = 'Added'
      AND group_id = $eogid ";

  //exclude deceased, trashed, do not mail, do not mail (undeliverable/trade)
  $sql .= "
    WHERE c.is_deceased = 0
      AND c.is_deleted = 0
      AND c.do_not_mail = 0
      AND c.do_not_trade = 0 ";

  //exclude empty last name, empty org name (if org type), and empty address
  $sql .= " AND ( ( c.contact_type = 'Individual' AND c.last_name IS NOT NULL AND c.last_name != '' ) OR ( c.contact_type = 'Individual' AND c.organization_name IS NOT NULL AND c.organization_name != '' ) OR c.contact_type != 'Individual' ) ";
  $sql .= " AND ( ( c.contact_type = 'Organization' AND c.organization_name IS NOT NULL AND c.organization_name != '' ) OR c.contact_type != 'Organization' ) ";
  $sql .= " AND ( ( a.street_address IS NOT NULL AND a.street_address != '' ) OR ( a.supplemental_address_1 IS NOT NULL AND a.supplemental_address_1 != '' ) ) ";

  //exclude impossibly old contacts
  $sql .= " AND ( c.birth_date IS NULL OR c.birth_date = '' OR c.birth_date > '1901-01-01' ) ";

  //group by contact ID in case any joins with multiple records cause dupe primary in our temp table
  $sql .= " GROUP BY c.id ";
  //CRM_Core_Error::debug_var('sql',$sql);

  $dao = CRM_Core_DAO::executeQuery($sql);
  $dao->free();

  return;
} // addExternalSeeds()


//display debug based on constant
function idebug( $var, $varName = '', $level = 1 ) {
  if ( !PPDEBUG )
    return;

  //if second param is an int we assume its the level
  if ( is_int($varName) ) {
    $level = $varName;
    $varName = '';
  }

  if ( PPDEBUG >= $level ) {
    if ( $varName ) {
      CRM_Core_Error::debug_var($varName, $var);
    }
    else {
      CRM_Core_Error::debug_var('debug var', $var);
    }
  }
} // idebug()


//exit if debug enabled and exit location defined
function iexit($loc = 0) {
  if (!PPDEBUG)
    return;

  if ($loc == EXITLOC) {
    exit();
  }
}

function itime($location = 'elapsed time', $total = FALSE) {
  if (!TRACKTIME)
    return;

  static $timeLogs = [];

  if (empty($timeLogs)) {
    $timeLogs[] = microtime(TRUE);
    CRM_Core_Error::debug_log_message("starting to track time...");
    return;
  }
  else {
    $timeLogsValues = array_values($timeLogs);
    $last = end($timeLogsValues);
    $timeLogs[] = $new = microtime(TRUE);

    $diff = $new - $last;
    CRM_Core_Error::debug_log_message("{$location}: $diff");
  }

  if ($total && count($timeLogs) > 1) {
    $timeLogsValues = array_values($timeLogs);
    $first = array_shift($timeLogsValues);
    $diff = $new - $first;
    CRM_Core_Error::debug_log_message("total elapsed time: $diff");
  }
}
