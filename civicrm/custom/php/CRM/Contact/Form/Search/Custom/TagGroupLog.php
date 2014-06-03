<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.4                                                |
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

class CRM_Contact_Form_Search_Custom_TagGroupLog
  extends CRM_Contact_Form_Search_Custom_Base
  implements CRM_Contact_Form_Search_Interface {

  protected $_formValues;
  protected $_columns;

  function __construct( &$formValues ) {
    parent::__construct( $formValues );

    $this->_columns = array(
      ts('&nbsp;') => 'contact_type',
      ts('Name') => 'sort_name' ,
      //ts('Street Address') => 'street_address',
      //ts('City') => 'city',
      //ts('State') => 'state',
      //ts('Postal Code') => 'postal_code',
      //ts('Email') => 'email',
      //ts('Phone') => 'phone',
      ts('Log Date') => 'log_date',
      ts('Log Details') => 'log_details',
      ts('Altered By') => 'altered_by',
    );
  }

  function buildForm( &$form ) {
    $this->setTitle('Tag/Group Changelog Search');

    $searchType = array(
      '1' => ts('Tags'),
      '2' => ts('Groups'),
    );
    $form->addRadio('search_type', ts('Search Type'), $searchType, NULL, '&nbsp;', TRUE);

    //construct tags/groups
    $groups = CRM_Core_PseudoConstant::group();

    $tags = CRM_Core_BAO_Tag::getTags();
    $keywords = CRM_Core_BAO_Tag::getTagsUsedFor( $usedFor = array( 'civicrm_contact' ),
      $buildSelect = true,
      $all = false,
      $parentId = 296
    );
    asort($keywords);
    if ( $keywords ) {
      //lets indent keywords
      foreach ( $keywords as $key => $keyword ) {
        $keywords[$key] = '&nbsp;&nbsp;'.$keyword;
      }
      $tags = $tags + array ('296' => 'Keywords') + $keywords;
    }

    $legpos = CRM_Core_BAO_Tag::getTagsUsedFor( $usedFor = array( 'civicrm_contact' ),
      $buildSelect = true,
      $all = false,
      $parentId = 292
    );
    asort($legpos);
    if ( $legpos ) {
      //lets indent leg positions
      foreach ( $legpos as $key => $pos ) {
        $legpos[$key] = '&nbsp;&nbsp;'.$pos;
      }
      $tags = $tags + array ('292' => 'Legislative Positions') + $legpos;
    }

    $fTags = &$form->addElement('advmultiselect', 'tag',
      ts('Tag(s)'), $tags,
      array(
        'size' => 5,
        'style' => 'width:270px',
        'class' => 'advmultiselect',
      )
    );

    $fGroups = &$form->addElement('advmultiselect', 'group',
      ts('Group(s)'), $groups,
      array(
        'size' => 5,
        'style' => 'width:270px',
        'class' => 'advmultiselect',
      )
    );
    
    $form->addDate( 'start_date', ts( 'Date from' ), false, array('formatType' => 'birth') );
    $form->addDate( 'end_date', ts( 'Date to' ), false, array('formatType' => 'birth') );
    
    $formfields = array(
      'start_date',
      'end_date',
      'search_type',
      'tag',
      'group',
    );
    $form->assign( 'elements', $formfields );
    
    $form->add('hidden', 'form_message' );

    $form->setDefaults( $this->setDefaultValues() );
    $form->addFormRule( array( 'CRM_Contact_Form_Search_Custom_TagGroupLog', 'formRule' ), $this );
  }//buildForm
  
  static function formRule( $fields ) {
    $errors = array( );
    //CRM_Core_Error::debug_var('formRule fields', $fields);

    if ( $fields['search_type'] == 1 && empty($fields['tag']) ) {
      $errors['form_message'] = ts( 'Please select at least one tag.' );
    }
    elseif ( $fields['search_type'] == 2 && empty($fields['group']) ) {
      $errors['form_message'] = ts( 'Please select at least one group.' );
    }
        
    return empty($errors) ? true : $errors;
  }//formRule

  function summary( ) {
    return null;
  }

  function contactIDs($offset = 0, $rowcount = 0, $sort = NULL, $returnSQL = FALSE) {
    return $this->all($offset, $rowcount, $sort, FALSE, TRUE);
  }

  function all(
    $offset = 0, $rowcount = 0, $sort = NULL,
    $includeContactIDs = FALSE, $justIDs = FALSE
  ) {

    $log_details = '';
    switch($this->_formValues['search_type']) {
      case 1:
        $log_details = "CONCAT(tag.name, ' (', CASE WHEN log_et.log_action = 'Insert' THEN 'Added' WHEN log_et.log_action = 'Delete' THEN 'Removed' ELSE log_et.log_action END, ')')";
        break;

      case 2:
        $log_details = "CONCAT(grp.title, ' (', CASE WHEN log_et.log_action = 'Insert' THEN 'Added' WHEN log_et.log_action = 'Delete' THEN 'Removed' ELSE log_et.log_action END, ')')";
        break;
    }

    if ($justIDs) {
      $selectClause = "contact_a.id as contact_id";
      $sort = 'contact_a.id';
    }
    else {
      $selectClause = "
        contact_a.id as contact_id,
        contact_a.sort_name as sort_name,
        contact_a.contact_type as contact_type,
        log_et.log_date,
        {$log_details} as log_details,
        ab.display_name as altered_by
      ";
    }
    
    //CRM_Core_Error::debug('select',$selectClause); exit();
    return $this->sql( $selectClause,
      $offset, $rowcount, $sort,
      $includeContactIDs, null
    );
  }
    
  function from( ) {
    //CRM_Core_Error::debug_var('$this->_formValues', $this->_formValues);

    $bbconfig = get_bluebird_instance_config();
    //CRM_Core_Error::debug_var('$bbconfig', $bbconfig);

    $logDB = $bbconfig['db.log.prefix'].$bbconfig['db.basename'];

    switch($this->_formValues['search_type']) {
      case 1:
        $from = "
          FROM civicrm_contact contact_a
          JOIN {$logDB}.log_civicrm_entity_tag log_et
            ON contact_a.id = log_et.entity_id
            AND log_et.entity_table = 'civicrm_contact'
            AND log_et.log_action != 'Initialization'
          JOIN civicrm_tag tag
            ON log_et.tag_id = tag.id
          LEFT JOIN civicrm_contact ab
            ON log_et.log_user_id = ab.id
        ";
        break;

      case 2:
        $from = "
          FROM civicrm_contact contact_a
          JOIN {$logDB}.log_civicrm_group_contact log_et
            ON contact_a.id = log_et.contact_id
            AND log_et.log_action != 'Initialization'
          JOIN civicrm_group grp
            ON log_et.group_id = grp.id
          LEFT JOIN civicrm_contact ab
            ON log_et.log_user_id = ab.id
        ";
        break;
    }

    return $from;
  }//from

  function where( $includeContactIDs = false ) {
    $params = array( );

    $start_date = CRM_Utils_Date::mysqlToIso( CRM_Utils_Date::processDate( $this->_formValues['start_date'] ) );
    $end_date  = CRM_Utils_Date::mysqlToIso( CRM_Utils_Date::processDate( $this->_formValues['end_date'] ) );
    
    //add filters by start/end date
    if ( $start_date ) {
      $where[] = "log_et.log_date >= '$start_date' ";
    }
    if ( $end_date ) {
      $where[] = "log_et.log_date <= '$end_date' ";
    }

    switch($this->_formValues['search_type']) {
      case 1:
        $tags = implode(',', $this->_formValues['tag']);
        $where[] = "log_et.tag_id IN ({$tags}) ";
        break;

      case 2:
        $groups = implode(',', $this->_formValues['group']);
        $where[] = "log_et.group_id IN ({$groups}) ";
        break;
    }
    
    //standard clauses
    $where[] = "contact_a.is_deleted = 0 ";
    $where[] = "contact_a.is_deceased = 0 ";
    
    if ( !empty($where) ) {
      $whereClause = implode( ' AND ', $where );
    }
    else {
      $whereClause = '';
    }
    //CRM_Core_Error::debug_var('whereClause', $whereClause);
    
    return $this->whereClause( $whereClause, $params );
  }

  function count() {
    $sql = $this->all();
    $dao = CRM_Core_DAO::executeQuery($sql, CRM_Core_DAO::$_nullArray);
    return $dao->N;
  }

  function templateFile( ) {
    return 'CRM/Contact/Form/Search/Custom/TagGroupLog.tpl';
  }

  function setDefaultValues( ) {
  }

  function alterRow( &$row ) {
    require_once( 'CRM/Contact/BAO/Contact/Utils.php' );
    $row['contact_type' ] =
      CRM_Contact_BAO_Contact_Utils::getImage( $row['contact_type'],
        false,
        $row['contact_id'] );
  }

  function setTitle( $title ) {
    if ( $title ) {
      CRM_Utils_System::setTitle( $title );
    }
    else {
      CRM_Utils_System::setTitle(ts('Search'));
    }
  }
}
