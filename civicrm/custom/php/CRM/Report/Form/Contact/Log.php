<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 5                                                  |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2019                                |
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
 * @copyright CiviCRM LLC (c) 2004-2019
 */
class CRM_Report_Form_Contact_Log extends CRM_Report_Form {

  protected $_summary = NULL;
  protected $_addressField = false; //NYSS

  /**
   * Class constructor.
   */
  public function __construct() {

    $this->activityTypes = CRM_Core_PseudoConstant::activityType(TRUE, TRUE);
    asort($this->activityTypes);

    $this->_columns = array(
      'civicrm_contact' => array(
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' => array(
          'sort_name' => array(
            'title' => ts('Modified By'),
            'required' => TRUE,
          ),
          'id' => array(
            'no_display' => TRUE,
            'required' => TRUE,
          ),
        ),
        'filters' => array(
          'sort_name' => array(
            'title' => ts('Modified By'),
            'type' => CRM_Utils_Type::T_STRING,
          ),
        ),
        'grouping' => 'contact-fields',
      ),
      'civicrm_contact_touched' => array(
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' => array(
          'sort_name_touched' => array(
            'title' => ts('Touched Contact'),
            'name' => 'sort_name',
            'required' => TRUE,
          ),
          'id' => array(
            'no_display' => TRUE,
            'required' => TRUE,
          ),
        ),
        'filters' => array(
          'sort_name_touched' => array(
            'title' => ts('Touched Contact'),
            'name' => 'sort_name',
            'type' => CRM_Utils_Type::T_STRING,
          ),
        ),
        'grouping' => 'contact-fields',
      ),
          
      //NYSS address
      'civicrm_address' =>
      array(
        'dao' => 'CRM_Core_DAO_Address',
        'grouping' => 'contact-fields',
        'fields' =>
        array(
          'street_address' => array('no_display' => true),
          'city' => array('no_display' => true),
          'postal_code' => array('no_display' => true),
          'state_province_id' => array(
            'title' => ts('State/Province'),
            'no_display' => true
          ),
        ),
      ),

      'civicrm_activity' => array(
        'dao' => 'CRM_Activity_DAO_Activity',
        'fields' => array(
          'id' => array(
            'title' => ts('Activity ID'),
            'no_display' => TRUE,
            'required' => TRUE,
          ),
          'subject' => array(
            'title' => ts('Touched Activity'),
            'required' => TRUE,
          ),
          'activity_type_id' => array(
            'title' => ts('Activity Type'),
            'required' => TRUE,
          ),
        ),
      ),
      'civicrm_activity_source' => array(
        'dao' => 'CRM_Activity_DAO_ActivityContact',
        'fields' => array(
          'contact_id' => array(
            'no_display' => TRUE,
            'required' => TRUE,
          ),
        ),
      ),
      'civicrm_log' => array(
        'dao' => 'CRM_Core_DAO_Log',
        'fields' => array(
          'modified_date' => array(
            'title' => ts('Modified Date'),
            'required' => TRUE,
          ),
          'data' => array(
            'title' => ts('Description'),
          ),
        ),
        'filters' => array(
          'modified_date' => array(
            'title' => ts('Modified Date'),
            'operatorType' => CRM_Report_Form::OP_DATE,
            'type' => CRM_Utils_Type::T_DATE,
            'default' => 'this.week',
          ),
          //NYSS
          'data' =>
          array(
            'title' => ts('Description'),
            'type' => CRM_Utils_Type::T_STRING,
          ),
          //NYSS exclude activity records
          'exclude_activities' =>
          array(
            'name' => 'exclude_activities' ,
            'title' => ts('Exclude Activity Records'),
            'type' => CRM_Utils_Type::T_INT,
            'operatorType' => CRM_Report_Form::OP_SELECT,
            'options' => array('0'=>'No', '1'=>'Yes'),
            'default' => 1,
          ),
        ),
      ),
    );

    parent::__construct();
  }

  public function preProcess() {
    parent::preProcess();
  }

  public function select() {
    $select = array();
    $this->_columnHeaders = array();
    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('fields', $table)) {
        foreach ($table['fields'] as $fieldName => $field) {
          if (!empty($field['required']) ||
            !empty($this->_params['fields'][$fieldName])
          ) {

            $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['type'] = CRM_Utils_Array::value('type', $field);
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = $field['title'];
          }
        }
      }
    }

    $this->_select = "SELECT " . implode(', ', $select) . " ";
  }

  /**
   * @param $fields
   * @param $files
   * @param $self
   *
   * @return array
   */
  public static function formRule($fields, $files, $self) {
    $errors = $grouping = array();
    return $errors;
  }

  public function from() {
    $activityContacts = CRM_Activity_BAO_ActivityContact::buildOptions('record_type_id', 'validate');
    $sourceID = CRM_Utils_Array::key('Activity Source', $activityContacts);
    $this->_from = "
        FROM civicrm_log {$this->_aliases['civicrm_log']}
        inner join civicrm_contact {$this->_aliases['civicrm_contact']} on {$this->_aliases['civicrm_log']}.modified_id = {$this->_aliases['civicrm_contact']}.id
        left join civicrm_contact {$this->_aliases['civicrm_contact_touched']} on ({$this->_aliases['civicrm_log']}.entity_table='civicrm_contact' AND {$this->_aliases['civicrm_log']}.entity_id = {$this->_aliases['civicrm_contact_touched']}.id)
        left join civicrm_activity {$this->_aliases['civicrm_activity']} on ({$this->_aliases['civicrm_log']}.entity_table='civicrm_activity' AND {$this->_aliases['civicrm_log']}.entity_id = {$this->_aliases['civicrm_activity']}.id)
        LEFT JOIN civicrm_activity_contact {$this->_aliases['civicrm_activity_source']} ON
          {$this->_aliases['civicrm_activity']}.id = {$this->_aliases['civicrm_activity_source']}.activity_id AND
          {$this->_aliases['civicrm_activity_source']}.record_type_id = {$sourceID}
        ";
  }

  public function where() {
    $clauses = array();
    $this->_having = '';
    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('filters', $table)) {
        foreach ($table['filters'] as $fieldName => $field) {
          $clause = NULL;
          if (CRM_Utils_Array::value('operatorType', $field) & CRM_Report_Form::OP_DATE
          ) {
            $relative = CRM_Utils_Array::value("{$fieldName}_relative", $this->_params);
            $from = CRM_Utils_Array::value("{$fieldName}_from", $this->_params);
            $to = CRM_Utils_Array::value("{$fieldName}_to", $this->_params);

            $clause = $this->dateClause($field['dbAlias'], $relative, $from, $to);
          }
          else {
            $op = CRM_Utils_Array::value("{$fieldName}_op", $this->_params);
            if ($op) {
              $clause = $this->whereClause($field,
                $op,
                CRM_Utils_Array::value("{$fieldName}_value", $this->_params),
                CRM_Utils_Array::value("{$fieldName}_min", $this->_params),
                CRM_Utils_Array::value("{$fieldName}_max", $this->_params)
              );
            }
          }
          
          //NYSS 3504 process contact logs only
          if ($field['name'] == 'exclude_activities') {
            $excludeActivities = CRM_Utils_Array::value("{$fieldName}_value", $this->_params);
            if ($excludeActivities == 1) {
              $clause = "({$this->_aliases['civicrm_log']}.entity_table = 'civicrm_contact')";
            }
            else {//if not flagged, ignore filter value
              $clause = NULL;
            }
          }

          if (!empty($clause)) {
            $clauses[] = $clause;
          }
        }
      }
    }

    $clauses[] = "({$this->_aliases['civicrm_log']}.entity_table <> 'civicrm_domain')";
    $this->_where = "WHERE " . implode(' AND ', $clauses);
  }

  public function orderBy() {
    $this->_orderBy = "
ORDER BY {$this->_aliases['civicrm_log']}.modified_date DESC, {$this->_aliases['civicrm_contact']}.sort_name, {$this->_aliases['civicrm_contact_touched']}.sort_name
";
  }
  
  //NYSS alter the way count is generated
  function statistics( &$rows ) {
        
    $statistics = array();
    //CRM_Core_Error::debug($rows);
    
    $count = 0;
    $past_touched = 0;
    foreach ( $rows as $row ) {
      $current_touched = $row['civicrm_contact_touched_id'];
      if ( $current_touched != $past_touched ) {
        $count++;
      }
      $past_touched = $current_touched;
      //echo $count.'<br />';
    }
        
    $this->countStat  ( $statistics, $count );
    $this->filterStat ( $statistics );
        
    //CRM_Core_Error::debug($statistics);
    return $statistics;
  }
  
  function countStat( &$statistics, $count ) {
    $statistics['counts']['rowCount'] = array(
      'title' => ts('Row(s) Listed'),
      'value' => $count,
    );
    
    //CRM_Core_Error::debug($this);
    $this->_select = 'SELECT DISTINCT contact_touched_civireport.id as distinctContacts';
    $query = $this->_select.' '.$this->_from.' '.$this->_where.' '.$this->_groupBy;
    //echo $query;
    $distinctContacts = CRM_Core_DAO::executeQuery( $query );
    $rowCount = $distinctContacts->N;
    
    if ( $this->_rowsFound && ($this->_rowsFound > $count) ) {
      $statistics['counts']['rowsFound'] = array(
        'title' => ts('Total Row(s)'),
        'value' => $rowCount );
    }
  }
    
  /**
   * Alter display of rows.
   *
   * Iterate through the rows retrieved via SQL and make changes for display purposes,
   * such as rendering contacts as links.
   *
   * @param array $rows
   *   Rows generated by SQL, with an array for each row.
   */
  public function alterDisplay(&$rows) {

    $entryFound = FALSE;
    $display_flag = $prev_cid = $cid = 0;
    //CRM_Core_Error::debug($rows);
    foreach ($rows as $rowNum => $row) {
      //NYSS 3504 don't repeat contact details if its same as the previous row
      $rows[$rowNum]['hideTouched' ] = 0;
      if ( $this->_outputMode != 'csv' ) {
        if ( array_key_exists('civicrm_contact_touched_id', $row ) && !empty($row['civicrm_contact_touched_id']) ) {
          if ( $cid = $row['civicrm_contact_touched_id'] ) {
            if ( $rowNum == 0 ) {
              $prev_cid = $cid;
            }
            else {
              if( $prev_cid == $cid ) {
                $display_flag = 1;
                $prev_cid = $cid;
              }
              else {
                $display_flag = 0;
                $prev_cid = $cid;
              }
            }
                        
            if ( $display_flag ) {
              $rows[$rowNum]['hideTouched' ] = 1;
            }
          }
        }
      }
      
      // convert display name to links
      if (array_key_exists('civicrm_contact_sort_name', $row) &&
        array_key_exists('civicrm_contact_id', $row)
      ) {
        $url = CRM_Utils_System::url('civicrm/contact/view',
          'reset=1&cid=' . $row['civicrm_contact_id'],
          $this->_absoluteUrl
        );
        $rows[$rowNum]['civicrm_contact_sort_name_link'] = $url;
        $rows[$rowNum]['civicrm_contact_sort_name_hover'] = ts("View Contact details for this contact.");
        $entryFound = TRUE;
      }
      
      //NYSS strip out the activity targets (could be multiple)
      if ( array_key_exists('civicrm_activity_activity_type_id', $row ) &&
        $row['civicrm_activity_activity_type_id'] != '' &&
        strpos( $row['civicrm_log_data'], 'target=' ) ) {
        // source, target, assignee are concatenated; we need to strip out the target
        $loc_target = strrpos( $row['civicrm_log_data'], 'target=' );
        $loc_assign = strrpos( $row['civicrm_log_data'], ', assignee=' );
        $str_target = substr( $row['civicrm_log_data'], $loc_target + 7, $loc_assign - $loc_target - 7 );
        //CRM_Core_Error::debug('lc', $loc_target);
        //CRM_Core_Error::debug('la', $loc_assign);
        //CRM_Core_Error::debug('st', $str_target);
        
        $targets = explode( ',', $str_target );
        //CRM_Core_Error::debug('at', $targets);
        
        // build links
        $atlist = array();
        foreach ( $targets as $target ) {
          $turl = CRM_Utils_System::url( 'civicrm/contact/view', 'reset=1&cid='.$target, $this->_absoluteUrl );
          $tc_params = array(
            'version' => 3,
            'id' => $target
          );
          $tc_contacts = civicrm_api('contact', 'getsingle', $tc_params);
          $atlist[] = '<a href="'.$turl.'">'.$tc_contacts['display_name'].'</a>';
        }
        $stlist = implode( ', ', $atlist );
        //CRM_Core_Error::debug('stlist', $stlist);
        $rows[$rowNum]['civicrm_activity_targets_list'] = $stlist;
        
      }

      // convert touched name to links with details
      if ( array_key_exists('civicrm_contact_touched_display_name_touched', $row) &&
        array_key_exists('civicrm_contact_touched_id', $row) &&
        $row['civicrm_contact_touched_display_name_touched'] !== '' ) {
                
        //NYSS add details about touched contact via API
        //Gender, DOB, ALL District Information.
        if ( $row['civicrm_contact_touched_id'] ) {
          //get address, phone, email
          require_once 'api/api.php';
        
          $cid = $row['civicrm_contact_touched_id'];
          $params = array(
            'version' => 3,
            'id' => $cid,
          );
          $contact = civicrm_api('contact', 'getsingle', $params);
          //CRM_Core_Error::debug_var('$contact',$contact);

          $rows[$rowNum]['civicrm_contact_touched_phone'] =
            ( !empty($contact['phone']) ) ? '<li>'.$contact['phone'].'</li>' : '';

          $rows[$rowNum]['civicrm_contact_touched_email'] =
            ( !empty($contact['email']) ) ? '<li>'.$contact['email'].'</li>' : '';

          //setup address
          $addr = '';
          if ( !empty($contact['street_address']) ) {
            $addr = $contact['street_address'].'<br />';
          }
          if ( !empty($contact['city']) ) {
            $addr .= $contact['city'].', ';
          }
          if ( !empty($contact['state_province']) || !empty($contact['postal_code']) ) {
            $addr .= $contact['state_province'].' '.$contact['postal_code'];
          }
          if ( !empty($addr) ) {
            $rows[$rowNum]['civicrm_contact_touched_address'] = '<li>'.$addr.'</li>';
          }

          //setup demographics
          $diDemo = '';
          if ( $contact['gender'] ) $diDemo .= '<li>Gender: '.$contact['gender'].'</li>';
          if ( $contact['birth_date'] ) $diDemo .= '<li>Birthday: '.$contact['birth_date'].'</li>';
          $rows[$rowNum]['civicrm_contact_touched_demographics'] = $diDemo;
        }
        //NYSS end
        
        $url = CRM_Utils_System::url(
          'civicrm/contact/view',
          'reset=1&cid='.$row['civicrm_contact_touched_id'],
          $this->_absoluteUrl );
        $rows[$rowNum]['civicrm_contact_touched_display_name_touched_link' ] = $url;
        $rows[$rowNum]['civicrm_contact_touched_display_name_touched_hover'] =
          ts("View Contact details for this contact.");
        $entryFound = true;
      }

      if (array_key_exists('civicrm_activity_subject', $row) &&
        array_key_exists('civicrm_activity_id', $row) &&
        $row['civicrm_activity_subject'] !== ''
      ) {
        $url = CRM_Utils_System::url('civicrm/contact/view/activity',
          'reset=1&action=view&id=' . $row['civicrm_activity_id'] . '&cid=' .
          $row['civicrm_activity_source_contact_id'] . '&atype=' .
          $row['civicrm_activity_activity_type_id'],
          $this->_absoluteUrl
        );
        $rows[$rowNum]['civicrm_activity_subject_link'] = $url;
        $rows[$rowNum]['civicrm_activity_subject_hover'] = ts("View Contact details for this contact.");
        $entryFound = TRUE;
      }

      if (array_key_exists('civicrm_activity_activity_type_id', $row)) {
        if ($value = $row['civicrm_activity_activity_type_id']) {
          $rows[$rowNum]['civicrm_activity_activity_type_id'] = $this->activityTypes[$value];
        }
        $entryFound = TRUE;
      }

      // skip looking further in rows, if first row itself doesn't
      // have the column we need
      if (!$entryFound) {
        break;
      }
    }
  }

}
