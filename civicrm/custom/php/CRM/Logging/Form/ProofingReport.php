<?php

/*
 * NYSS 5260
 * SOS Log Proofing Report
 * Created: May, 2012
 * Author:  Brian Shaughnessy
 */

/**
 * This class generates form components
 * 
 */
class CRM_Logging_Form_ProofingReport extends CRM_Core_Form
{
  /**
   * pre-form data checks
   *
   * @return void
   * @access public
   */
  function preProcess( ) {
    //handle breadcrumbs
    $url = CRM_Utils_System::url( 'civicrm/logging/proofingreport', 'reset=1' );
    $breadCrumb = array(
      array(
        'url' => $url,
        'title' => ts('Log Proofing Report')
      )
    );
    CRM_Utils_System::appendBreadCrumb( $breadCrumb );

    //set page title
    CRM_Utils_System::setTitle( ts('Generate Log Proofing Report') );
  }
    
  /**
   * Build the form
   *
   * @access public
   * @return void
   */
  public function buildQuickForm() {
    $this->addElement( 'text', 'jobID', ts('Job ID') );

    $this->addElement( 'text', 'alteredBy', ts('Altered By') );

    $this->addDate( 'start_date', ts('Date from'), FALSE, array( 'formatType' => 'custom') );
    $this->addDate( 'end_date', ts('...to'), FALSE, array( 'formatType' => 'custom') );

    $this->add( 'select', 'pdf_format_id', ts( 'Page Format' ),
      array( 0 => ts( '- default -' ) ) + CRM_Core_BAO_PdfFormat::getList( true ) );

    //7582/7685/11831 add tags
    $tags = CRM_Core_BAO_Tag::getColorTags('civicrm_contact');
    if (!empty($tags)) {
      $this->add('select2', 'tag', ts('Tag(s)'), $tags, FALSE, array('class' => 'huge', 'placeholder' => ts('- select -'), 'multiple' => TRUE));
    }

    // build tag widget
    $parentNames = CRM_Core_BAO_Tag::getTagSet('civicrm_contact');
    foreach ($parentNames as $k => $name) {
      if (!in_array($name, array('Keywords', 'Positions'))) {
        unset($parentNames[$k]);
      }
    }
    CRM_Core_Form_Tag::buildQuickForm($this, $parentNames, 'civicrm_contact', NULL, TRUE);

    $this->add('checkbox', 'merge_house', 'Merge Households? (CSV export only)');

    $this->addButtons(
      array(
        array(
          'type' => 'next',
          'name' => ts('Generate PDF Report'),
        ),
        array(
          'type' => 'upload',
          'name' => ts('Generate Print Report'),
          'isDefault' => TRUE
        ),
        array(
          'type' => 'submit',
          'name' => ts('Generate CSV'),
        ),
      )
    );

    $this->addFormRule(array('CRM_Logging_Form_ProofingReport', 'formRule'), $this);
  }
    
  /**
   * Set default values
   */
  function setDefaultValues() {
    $defaults = array(
      'year' => date('Y'),
      'pdf_format_id' => 1895,
    );
    return $defaults;
  }

  /**
   * global form rule
   *
   * @param array $fields  the input form values
   * @param array $files   the uploaded files if any
   * @param array $options additional user data
   *
   * @return true if no errors, else array of errors
   * @access public
   * @static
   */
  static function formRule( $fields, $files, $self )
  {
    //CRM_Core_Error::debug_var('fields',$fields);
    $errors = array();

    if (empty($fields['jobID']) &&
      empty($fields['alteredBy']) &&
      empty($fields['start_date']) &&
      empty($fields['end_date'])
    ) {
      $errors['jobID'] = ts('You must select a Job ID or Altered By value, and date field to run this report.');
    }

    if (empty($fields['start_date'])) {
      $errors['start_date'] = 'You must select a start date to run this report.';
    }

    //7776 check if date range is > 1 month
    $dateStart = new DateTime(CRM_Utils_Array::value('start_date', $fields));
    $dateEnd = new DateTime(CRM_Utils_Array::value('end_date', $fields, date('Y-m-d')));

    $interval = $dateStart->diff($dateEnd);
    $days = $interval->format('%a');
    //CRM_Core_Error::debug_var('days', $days);

    if ($days > 366) {
      $errors['end_date'] = 'The date range cannot exceed 180 days. Please adjust your start and end dates to a smaller interval in order to run this report.';
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
    //CRM_Core_Error::debug_var('this', $this);

    $bbconfig = get_bluebird_instance_config();
    $logDB = $bbconfig['db.log.prefix'].$bbconfig['db.basename'];
    $civiDB = $bbconfig['db.civicrm.prefix'].$bbconfig['db.basename'];

    //get form parameters and create sql criteria
    $formParams = $this->controller->exportValues( $this->_name );
    //CRM_Core_Error::debug_var('formParams', $formParams);

    $sqlParams = $rows = array();
    $startDate = $endDate = $alteredByFrom = '';
    if ($formParams['jobID']) {
      $sqlParams['job'] = "main.log_job_id = '{$formParams['jobID']}'";
    }
    if ($formParams['alteredBy']) {
      $sqlParams['alteredby'] = "ab.sort_name LIKE '%{$formParams['alteredBy']}%'";
      $alteredByFrom = "LEFT JOIN $civiDB.civicrm_contact ab ON main.log_user_id = ab.id ";
    }
    if ($formParams['start_date']) {
      $startDate = date( 'Y-m-d', strtotime($formParams['start_date']) );
      $sqlParams['startdate'] = "main.log_date >= '{$startDate} 00:00:00'";
    }
    if ($formParams['end_date']) {
      $endDate = date( 'Y-m-d', strtotime($formParams['end_date']) );
      $sqlParams['enddate'] = "main.log_date <= '{$endDate} 23:59:59'";
    }

    //handle tags
    $tagsSelected = explode(',', CRM_Utils_Array::value('tag', $formParams, array()));
    foreach (CRM_Utils_Array::value('contact_taglist', $formParams) as $tagSet => $tagSetList) {
      $tagsSelected = array_merge($tagsSelected, explode(',', $tagSetList));
    }
    $tagsSelected = array_filter($tagsSelected);

    if (!empty($tagsSelected)) {
      $tagsSelectedList = implode(',', $tagsSelected);
      $sqlParams['tag'] = "tag_id IN ({$tagsSelectedList})";
    }

    //compile WHERE clauses
    $sqlWhere = implode(' ) AND ( ', $sqlParams);

    $dateNow = date('F jS Y h:i a');

    //begin construction of html
    $html  = self::_reportCSS();
    $html .= "<h2>SOS Proofing Report: $dateNow</h2>";

    if ($startDate || $endDate) {
      if ($startDate && !$endDate) {
        $dateRange = "$startDate &#8211; Now";
      }
      elseif (!$startDate && $endDate) {
        $dateRange = "Before $endDate";
      }
      else {
        $dateRange = "$startDate &#8211; $endDate";
      }
      $html .= "<h3>Date Range: $dateRange</h3>";
    }

    if ($formParams['jobID']) {
      $html .= "<h3>Job ID: {$formParams['jobID']}</h3>";
    }
    if ($formParams['alteredBy']) {
      $html .= "<h3>Altered By Search: {$formParams['alteredBy']}</h3>";
    }

    $html .= "
      <table>
        <tr>
          <th>When</th>
          <th>Altered Contact</th>
          <th>Street and Mailing Address</th>
          <th>Gender/DOB/Phone</th>
          <th>Contact Email</th>
          <th>Tag(s)</th>
          <th>Group(s)</th>
        </tr>";

    CRM_Core_DAO::executeQuery("SET SESSION group_concat_max_len = 100000;");

    //create temp table
    $rnd = mt_rand(1,9999999999999999);
    $tmpChgProof = "nyss_temp_changeproof_$rnd";
    $sql = "
      CREATE TABLE {$tmpChgProof}
      (id INT NOT NULL PRIMARY KEY, logDate VARCHAR(100), logDateLong TIMESTAMP, tagList VARCHAR(5100), groupList VARCHAR(5100))
      ENGINE=MyISAM;
    ";
    CRM_Core_DAO::executeQuery($sql);

    //insert contacts with tag changes
    $query = "
      INSERT INTO {$tmpChgProof}
      SELECT main.entity_id as id, 
        DATE_FORMAT(MAX(log_date), '%m/%d/%Y %h:%i %p') as logDate,
        MAX(log_date) as logDateLong,
        GROUP_CONCAT(CONCAT(t.name, ' (', main.log_action, ')') ORDER BY t.name SEPARATOR ', ') as tagList,
        NULL as groupList
      FROM {$logDB}.log_civicrm_entity_tag main
      JOIN {$civiDB}.civicrm_tag t
        ON main.tag_id = t.id
      $alteredByFrom
      WHERE ($sqlWhere)
        AND entity_table = 'civicrm_contact'
        AND main.log_action != 'Initialization'
      GROUP BY main.entity_id
    ";
    //CRM_Core_Error::debug_var('tags query',$query);
    CRM_Core_DAO::executeQuery($query);

    //if no tag option, look for changes to contacts
    if (empty($tagsSelected)) {
      //contacts
      $query = "
        INSERT IGNORE INTO {$tmpChgProof}
        SELECT main.id, 
          DATE_FORMAT(MAX(main.log_date), '%m/%d/%Y %h:%i %p') as logDate,
          MAX(main.log_date) as logDateLong,
          NULL as tagList,
          NULL as groupList
        FROM {$logDB}.log_civicrm_contact main
        $alteredByFrom
        WHERE ( $sqlWhere )
          AND main.log_action != 'Initialization'
        GROUP BY main.id
      ";
      //CRM_Core_Error::debug_var('contact query', $query);
      CRM_Core_DAO::executeQuery($query);
    }

    //insert contacts with group changes
    //remove the tag param first to avoid sql errors
    $sqlParams2 = $sqlParams;
    unset($sqlParams2['tag']);
    $sqlWhere2 = implode(' ) AND ( ', $sqlParams2);

    if (empty($tagsSelected)) {
      $query = "
        INSERT INTO {$tmpChgProof}
        SELECT main.contact_id as id,
          DATE_FORMAT(MAX(main.log_date), '%m/%d/%Y %h:%i %p') as logDate,
          MAX(log_date) as logDateLong,
          NULL as tagList,
          GROUP_CONCAT(CONCAT(g.title, ' (', main.log_action, ')') ORDER BY g.title SEPARATOR ', ') as groupList
        FROM {$logDB}.log_civicrm_group_contact main
        JOIN {$civiDB}.civicrm_group g
          ON main.group_id = g.id
        $alteredByFrom
        WHERE ( $sqlWhere2 )
          AND main.log_action != 'Initialization'
        GROUP BY main.contact_id

        ON DUPLICATE KEY UPDATE groupList = (
          SELECT GROUP_CONCAT(CONCAT(g.title, ' (', main.log_action, ')') ORDER BY g.title SEPARATOR ', ')
          FROM {$logDB}.log_civicrm_group_contact main
          JOIN {$civiDB}.civicrm_group g
            ON main.group_id = g.id
          $alteredByFrom
          WHERE ( $sqlWhere2 )
            AND main.log_action != 'Initialization'
            AND main.contact_id = {$tmpChgProof}.id
          GROUP BY main.contact_id
        )
      ";
      //CRM_Core_Error::debug_var('groups query',$query);
      CRM_Core_DAO::executeQuery($query);
    }
    else {
      //if we are filtering by tag, only update existing records with groupList
      $query = "
        UPDATE {$tmpChgProof}
        SET groupList = (
          SELECT GROUP_CONCAT(CONCAT(g.title, ' (', main.log_action, ')') ORDER BY g.title SEPARATOR ', ')
          FROM {$logDB}.log_civicrm_group_contact main
          JOIN {$civiDB}.civicrm_group g
            ON main.group_id = g.id
          $alteredByFrom
          WHERE ( $sqlWhere2 )
            AND main.log_action != 'Initialization'
            AND main.contact_id = {$tmpChgProof}.id
          GROUP BY main.contact_id
        )
      ";
      //CRM_Core_Error::debug_var('groups update query',$query);
      CRM_Core_DAO::executeQuery($query);
    }

    //get records from temp table
    $sql = "
      SELECT *
      FROM {$tmpChgProof}
      ORDER BY logDateLong
    ";
    $dao = CRM_Core_DAO::executeQuery($sql);

    while ($dao->fetch()) {
      //CRM_Core_Error::debug_var('dao',$dao);
      $params = array(
        'version' => 3,
        'id' => $dao->id,
      );
      $cDetails = civicrm_api('contact','getsingle',$params);
      //CRM_Core_Error::debug_var('cDetails',$cDetails);

      //address block
      $address = array();
      if (!empty($cDetails['street_address'])) {
        $address[] = $cDetails['street_address'];
      }
      if (!empty($cDetails['supplemental_address_1'])) {
        $address[] = $cDetails['supplemental_address_1'];
      }
      if (!empty($cDetails['city']) || !empty($cDetails['postal_code'])) {
        $postSuffix = ($cDetails['postal_code_suffix']) ? '-'.$cDetails['postal_code_suffix'] : '';
        $address[] = $cDetails['city'].', '
          .$cDetails['state_province'].' '
          .$cDetails['postal_code'].$postSuffix;
      }
      $addressHTML = implode('<br />', $address);

      //gender/dob/phone block
      $gdp = array();
      if (!empty($cDetails['gender'])) {
        $gdp[] = $cDetails['gender'];
      }
      if ( isset($cDetails['birth_date']) && !empty($cDetails['birth_date']) ) {
        $gdp[] = date('m/d/Y', strtotime($cDetails['birth_date']));
      }
      if ( !empty($cDetails['phone']) ) {
        $gdp[] = $cDetails['phone'];
      }
      $gdpHTML = implode('<br />', $gdp);

      //cleanup tag list
      $tagList = str_replace(' (Insert)', '', $dao->tagList);
      $tagList = str_replace(' (Delete)', ' (removed)', $tagList);

      //7352 groups
      /*$sql = "
        SELECT GROUP_CONCAT(g.title SEPARATOR ', ')
        FROM civicrm_group_contact gc
        JOIN civicrm_group g
          ON gc.group_id = g.id
        WHERE contact_id = {$dao->id}
      ";
      $groupList = CRM_Core_DAO::singleValueQuery($sql);*/

      //cleanup group list
      $groupList = str_replace(' (Insert)', '', $dao->groupList);
      $groupList = str_replace(' (Delete)', ' (removed)', $groupList);

      $html .= "
        <tr>
          <td>{$dao->logDate}</td>
          <td><a href='/civicrm/contact/view?reset=1&cid={$dao->id}' target='_blank'>{$cDetails['display_name']}</a></td>
          <td>{$addressHTML}&nbsp;</td>
          <td>{$gdpHTML}&nbsp;</td>
          <td>{$cDetails['email']}&nbsp;</td>
          <td>{$tagList}&nbsp;</td>
          <td>{$groupList}&nbsp;</td>
        </tr>";

      $rows[$dao->id] = array(
        'id' => $dao->id,
        'sort_name' => CRM_Utils_Array::value('sort_name', $cDetails, ''),
        'display_name' => CRM_Utils_Array::value('display_name', $cDetails, ''),
        'individual_prefix' => CRM_Utils_Array::value('individual_prefix', $cDetails, ''),
        'first_name' => CRM_Utils_Array::value('first_name', $cDetails, ''),
        'middle_name' => CRM_Utils_Array::value('middle_name', $cDetails, ''),
        'last_name' => CRM_Utils_Array::value('last_name', $cDetails, ''),
        'individual_suffix' => CRM_Utils_Array::value('individual_suffix', $cDetails, ''),
        'organization_name' => CRM_Utils_Array::value('organization_name', $cDetails, ''),
        'household_name' => CRM_Utils_Array::value('household_name', $cDetails, ''),
        'street_address' => CRM_Utils_Array::value('street_address', $cDetails, ''),
        'mailing_address' => CRM_Utils_Array::value('supplemental_address_1', $cDetails, ''),
        'building' => CRM_Utils_Array::value('supplemental_address_2', $cDetails, ''),
        'city' => CRM_Utils_Array::value('city', $cDetails, ''),
        'state_province' => CRM_Utils_Array::value('state_province', $cDetails, ''),
        'postal_code' => CRM_Utils_Array::value('postal_code', $cDetails, ''),
        'postal_code_suffix' => CRM_Utils_Array::value('postal_code_suffix', $cDetails, ''),
        'birth_date' => CRM_Utils_Array::value('birth_date', $cDetails, ''),
        'gender' => CRM_Utils_Array::value('gender', $cDetails, ''),
        'phone' => CRM_Utils_Array::value('phone', $cDetails, ''),
        'email' => CRM_Utils_Array::value('email', $cDetails, ''),
        'postal_greeting' => CRM_Core_DAO::singleValueQuery("SELECT postal_greeting_display FROM civicrm_contact WHERE id = {$dao->id}"),
        'taglist' => stripslashes(iconv('UTF-8', 'Windows-1252', $tagList)),
        'grouplist' => stripslashes(iconv('UTF-8', 'Windows-1252', $groupList)),
        'when' => $dao->logDate,
      );

      //check if household rel exists
      if ( !empty($formParams['merge_house']) ) {
        $sql = "
          SELECT contact_id_b
          FROM civicrm_relationship
          WHERE contact_id_a = {$dao->id}
            AND relationship_type_id IN (7,6)
            AND is_active = 1
            AND (end_date IS NULL OR end_date > NOW())
          LIMIT 1
        ";
        $rows[$dao->id]['house_id'] = CRM_Core_DAO::singleValueQuery($sql);
      }

      //set col headers after the first row is constructed
      if ( !isset($this->_columnHeaders) ) {
        foreach ( $rows[$dao->id] as $hdr => $dontcare ) {
          $this->_columnHeaders[$hdr] = array('title' => $hdr);
        }
      }
    }

    //add summary counts
    $html .= "
      <tr class='tableSummary'>
        <td>Contacts Changed:</td>
        <td colspan='6'>{$dao->N}</td>
      </tr>";

    //close table
    $html .= "</table>";

    //remove temp table
    CRM_Core_DAO::executeQuery("
      DROP TABLE IF EXISTS {$tmpChgProof}
    ");

    //now generate pdf
    $actionName = $this->controller->getButtonName( );
    //PDF
    if ( $actionName == '_qf_ProofingReport_next' ) {
      CRM_Utils_PDF_Utils::html2pdf( $html,
        'LogProofingReport.pdf',
        FALSE,
        $formParams['pdf_format_id']
      );
    }
    //Print
    elseif ( $actionName == '_qf_ProofingReport_upload' ) {
      echo $html;
    }
    //CSV
    elseif ( $actionName == '_qf_ProofingReport_submit' ) {
      if ( $formParams['merge_house'] ) {
        self::_mergeHouseholds($rows);
      }
      CRM_Report_Utils_Report::export2csv($this, $rows);
    }

    CRM_Utils_System::civiExit( );
  }//postProcess

  //generate css
  function _reportCSS() {
    $css = "
<style type='text/css'>
<!--
h2, h3 {
  font-family: Arial, Helvetica, sans-serif;
  font-weight: normal;
}
h3 {
  font-weight: bold;
  font-size: 16px;
}
table {
  font-family: Arial, Helvetica, sans-serif;
  font-size: 12px;
}
th {
  background-color: #CCCCCC;
  vertical-align: top;
  border-bottom: solid 1px #999999;
  border-right: solid 1px #999999;
  padding: 4px;
}
td {
  vertical-align: top;
  border-bottom: solid 1px #CCCCCC;
  border-right: solid 1px #CCCCCC;
  padding: 4px;
}
tr.tableSummary td {
  font-weight: bold;
  background-color: #CCCCCC;
}
-->
</style>
";

    return $css;
  }//_reportCSS

  /*
   * if merge_household option selected, we run through this function during CSV export
   * existing rows are passed and the household ID included if exists
   *  - cycle through rows.
   *  - if household ID present, see if household is already part of the export. if so, unset and use existing record.
   *  - if household not present, conduct lookup and overwrite record with household details
   *
   * this algorithm will also handle the situation where multiple indivs from the same household are present.
   * in such cases, the first indiv will be overwritten with the house, and subsequent ones unset given the
   * now existence of the house record.
   *
   * @ &$rows  passed by reference so we can manipulate
   */
  function _mergeHouseholds(&$rows) {
    //CRM_Core_Error::debug_var('_mergeHouseholds rows', $rows);

    foreach ( $rows as $cid => $cDetails ) {
      if ( !empty($cDetails['house_id']) ) {
        if ( isset($rows[$cDetails['house_id']]) ) {
          //CRM_Core_Error::debug_log_message("Household {$cDetails['house_id']} already present. Removing individual record.");
          unset($rows[$cid]);
        }
        else {
          $params = array(
            'version' => 3,
            'id' => $cDetails['house_id'],
          );
          $house = civicrm_api('contact', 'getsingle', $params);
          //CRM_Core_Error::debug_var('_mergeHouseholds $house', $house);

          //add to rows; pass some non-standard details from indiv record; unset indiv
          $rows[$cDetails['house_id']] = array(
            'id' => $cDetails['house_id'],
            'sort_name' => CRM_Utils_Array::value('sort_name', $house, ''),
            'display_name' => CRM_Utils_Array::value('display_name', $house, ''),
            'individual_prefix' => CRM_Utils_Array::value('individual_prefix', $house, ''),
            'first_name' => CRM_Utils_Array::value('first_name', $house, ''),
            'middle_name' => CRM_Utils_Array::value('middle_name', $house, ''),
            'last_name' => CRM_Utils_Array::value('last_name', $house, ''),
            'individual_suffix' => CRM_Utils_Array::value('individual_suffix', $house, ''),
            'organization_name' => CRM_Utils_Array::value('organization_name', $house, ''),
            'household_name' => CRM_Utils_Array::value('household_name', $house, ''),
            'street_address' => CRM_Utils_Array::value('street_address', $house, ''),
            'mailing_address' => CRM_Utils_Array::value('supplemental_address_1', $house, ''),
            'building' => CRM_Utils_Array::value('supplemental_address_2', $house, ''),
            'city' => CRM_Utils_Array::value('city', $house, ''),
            'state_province' => CRM_Utils_Array::value('state_province', $house, ''),
            'postal_code' => CRM_Utils_Array::value('postal_code', $house, ''),
            'postal_code_suffix' => CRM_Utils_Array::value('postal_code_suffix', $house, ''),
            'birth_date' => CRM_Utils_Array::value('birth_date', $house, ''),
            'gender' => CRM_Utils_Array::value('gender', $house, ''),
            'phone' => CRM_Utils_Array::value('phone', $house, ''),
            'email' => CRM_Utils_Array::value('email', $house, ''),
            'postal_greeting' => CRM_Core_DAO::singleValueQuery("SELECT postal_greeting_display FROM civicrm_contact WHERE id = {$cDetails['house_id']}"),
            'taglist' => $cDetails['tagList'],
            'when' => $cDetails['when'],
            'house_id' => '',
          );

          unset($rows[$cid]);
        }
      }
    }
  }
}
