<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

require_once 'CRM/Logging/ReportSummary.php';
require_once 'api/api.php'; //NYSS

class CRM_Report_Form_Contact_LoggingSummary extends CRM_Logging_ReportSummary
{
    function __construct()
    {
        $this->_columns = array(
            'log_civicrm_contact' => array(
                'dao' => 'CRM_Contact_DAO_Contact',
                'fields' => array(
                    'id' => array(
                        'no_display' => true,
                        'required'   => true,
                    ),
                    'log_user_id' => array(
                        'no_display' => true,
                        'required'   => true,
                    ),
                    //NYSS log type
                    'log_type' => array(
                        'title'    => ts('Log Type'),
                        'required' => true,
                        'default'  => true,
                    ),
                    'log_date' => array(
                        'default'  => true,
                        'required' => true,
                        'type'     => CRM_Utils_Type::T_TIME,
                        'title'    => ts('When'),
                    ),
                    'altered_contact' => array(
                        'default' => true,
                        'name'    => 'display_name',
                        'title'   => ts('Altered Contact'),
                    ),
                    'log_conn_id' => array(
                       'no_display' => true,
                       'required'   => true
                    ),
                    'log_action' => array(
                        'default'  => true,
                        'title'    => ts('Action'),
                        'required' => true,
                    ),
                    //NYSS add job ID
                    'log_job_id' => array(
                        'title'   => ts('Job ID'),
                    ),
                    //NYSS show details
                    'log_details' => array(
                        'title'   => ts('Show Details'),
                        'name'    => 'id',
                    ),
                    'is_deleted' => array(
                        'no_display' => true,
                        'required'   => true,
                    ),
                ),
                'filters' => array(
                    'log_date' => array(
                        'title'        => ts('When'),
                        'operatorType' => CRM_Report_Form::OP_DATE,
                        'type' => CRM_Utils_Type::T_DATE,
                    ),
                    'altered_contact' => array(
                        'name'  => 'display_name',
                        'title' => ts('Altered Contact'),
                        'type'  => CRM_Utils_Type::T_STRING,
                    ),
                    'log_action' => array(
                        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                        'options'      => array('Insert' => ts('Insert'), 'Update' => ts('Update'), 'Delete' => ts('Delete')),
                        'title'        => ts('Action'),
                        'type'         => CRM_Utils_Type::T_STRING,
                    ),
                    //NYSS add job ID
                    'log_job_id' => array(
                        'title'        => ts('Job ID'),
                        'type'         => CRM_Utils_Type::T_STRING,
                    ),
                    'id' => array(
                        'no_display' => true,
                        'type'       => CRM_Utils_Type::T_INT,
                    ),

                ),
            ),
            'civicrm_contact' => array(
                'dao' => 'CRM_Contact_DAO_Contact',
                'fields' => array(
                    'altered_by' => array(
                        'default' => true,
                        'name'    => 'display_name',
                        'title'   => ts('Altered By'),
                    ),
                ),
                'filters' => array(
                    'altered_by' => array(
                        'name'  => 'display_name',
                        'title' => ts('Altered By'),
                        'type'  => CRM_Utils_Type::T_STRING,
                    ),
                ),
            ),
        );
        parent::__construct();
    }

    //NYSS - perform query manipulation so that we also pull the tag logs
    function postProcess( ) {
        $this->beginPostProcess( );
        $this->buildQuery( true );

        $contactReplace = array(
            //'log_civicrm_contact_log_action' => 'log_action',
            //'log_civicrm_contact_log_date'   => 'log_date',
            'SQL_CALC_FOUND_ROWS'             => '',
            'crm_contact_civireport.log_type' => "'Contact'",
            );
        $contactSelect = str_replace( array_keys($contactReplace), array_values($contactReplace), $this->_select );
        $cidWhere = ( $this->cid ) ? " crm_contact_civireport.id = {$this->cid} " : 1;
        $contactSql = "{$contactSelect}, 'Contact' log_type {$this->_from} WHERE $cidWhere {$this->_groupBy}";

        //calculate tags
        $tagReplace = array(
            'SQL_CALC_FOUND_ROWS'                 => '',
            'crm_contact_civireport.id'           => 'crm_contact_civireport.entity_id',
            'crm_contact_civireport.display_name' => "CONCAT(tag_contact.display_name,'  [',tag_table.name,']')",
            'crm_contact_civireport.is_deleted'   => 'tag_contact.is_deleted',
            'crm_contact_civireport.log_type'     => "'Tag'",
            );
        $tagSelect = str_replace( array_keys($tagReplace), array_values($tagReplace), $this->_select );
        $tagFrom   = "FROM `{$this->loggingDB}`.log_civicrm_entity_tag crm_contact_civireport
                      LEFT JOIN civicrm_contact     contact_civireport
                        ON (crm_contact_civireport.log_user_id = contact_civireport.id)
                      JOIN civicrm_contact tag_contact
                        ON crm_contact_civireport.entity_id = tag_contact.id
                      JOIN civicrm_tag tag_table
                        ON crm_contact_civireport.tag_id = tag_table.id";
        $cidWhere = ( $this->cid ) ? " crm_contact_civireport.entity_id = {$this->cid} " : 1;
        $tagWhere  = "WHERE crm_contact_civireport.entity_table = 'civicrm_contact' AND $cidWhere";
        $tagSql    = "$tagSelect, 'Tag' log_type $tagFrom $tagWhere";

        //extend log to other tables
        $sqlParams = array( 'logDB'  => $this->loggingDB,
                            'select' => $this->_select,
                            'cid'    => $this->cid,
                            );
        $groupSql   = self::_getGroupSQL($sqlParams);
        $relASql    = self::_getRelationshipASQL($sqlParams);
        $relBSql    = self::_getRelationshipBSQL($sqlParams);
        $noteSql    = self::_getNoteSQL($sqlParams);
        $commentSql = self::_getCommentSQL($sqlParams, $this->_where);

        //now combine the query
        $whereReplace = array(
            'log_date'                            => 'log_civicrm_contact_log_date',
            'crm_contact_civireport.display_name' => 'log_civicrm_contact_altered_contact',
            'crm_contact_civireport.log_job_id'   => 'log_civicrm_contact_log_job_id',
            'crm_contact_civireport.log_action'   => 'log_civicrm_contact_log_action',
            'contact_civireport.display_name'     => 'civicrm_contact_altered_by',
            'crm_contact_civireport.id'           => 'log_civicrm_contact_id',
            );
        $sqlWhere = str_replace( array_keys($whereReplace), array_values($whereReplace), $this->_where );

        $sql = "SELECT SQL_CALC_FOUND_ROWS * 
                FROM ( ( $contactSql ) UNION
                       ( $tagSql ) UNION
                       ( $noteSql ) UNION
                       ( $commentSql ) UNION
                       ( $groupSql ) UNION
                       ( $relASql ) UNION
                       ( $relBSql )
                     ) tmpCombined 
                {$sqlWhere}
                {$this->_orderBy}
                {$this->_limit}";
        //CRM_Core_Error::debug_var('combined sql',$sql);
        //CRM_Core_Error::debug_var('combined dao',CRM_Core_DAO::executeQuery($sql));

        //4198 get distinct contact count for log report total
        $sqlDistinct = "SELECT SQL_CALC_FOUND_ROWS *
                        FROM ( ( $contactSql ) UNION
                               ( $tagSql ) UNION
                               ( $noteSql ) UNION
                               ( $commentSql ) UNION
                               ( $groupSql ) UNION
                               ( $relASql ) UNION
                               ( $relBSql )
                             ) tmpCombined
                        {$sqlWhere}
                        GROUP BY log_civicrm_contact_id";
        //CRM_Core_Error::debug_var('sqlDistinct',$sqlDistinct);
        CRM_Core_DAO::executeQuery($sqlDistinct);

        $this->_distinctCount = CRM_Core_DAO::singleValueQuery( "SELECT FOUND_ROWS();" );
        //CRM_Core_Error::debug_var('distinctCount',$distinctCount);

        $this->buildRows ( $sql, $rows );
        $this->formatDisplay( $rows );
        $this->doTemplateAssignment( $rows );
        $this->endPostProcess( $rows );
    }

    function alterDisplay(&$rows)
    {
        // cache for id → is_deleted mapping
        $isDeleted = array();

        foreach ($rows as &$row) {
            if (!isset($isDeleted[$row['log_civicrm_contact_id']])) {
                $isDeleted[$row['log_civicrm_contact_id']] = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact', $row['log_civicrm_contact_id'], 'is_deleted') !== '0';
            }

            if ( !$isDeleted[$row['log_civicrm_contact_id']] ) {
                $row['log_civicrm_contact_altered_contact_link'] = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid=' . $row['log_civicrm_contact_id']);
                $row['log_civicrm_contact_altered_contact_hover'] = ts("Go to contact summary");
            }
            $row['civicrm_contact_altered_by_link'] = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid=' . $row['log_civicrm_contact_log_user_id']);
            $row['civicrm_contact_altered_by_hover'] = ts("Go to contact summary");

            if ($row['log_civicrm_contact_is_deleted'] and $row['log_civicrm_contact_log_action'] == 'Update') {
                $row['log_civicrm_contact_log_action'] = ts('Delete (to trash)');
            }

            if ($row['log_civicrm_contact_log_action'] == 'Update') {
                $q = "reset=1&log_conn_id={$row['log_civicrm_contact_log_conn_id']}&log_date={$row['log_civicrm_contact_log_date']}";
                if ( $this->cid ) $q .= '&cid='.$this->cid;

                //NYSS append instance id so we return properly
                $q .= '&instanceID='.$this->_id;

                $url = CRM_Report_Utils_Report::getNextUrl('logging/contact/detail', $q, false, true);
                $row['log_civicrm_contact_log_action_link'] = $url;
                $row['log_civicrm_contact_log_action_hover'] = ts("View details for this update");
                $row['log_civicrm_contact_log_action'] = '<div class="icon details-icon"></div> ' . ts('Update');
            }

            unset($row['log_civicrm_contact_log_user_id']);
            unset($row['log_civicrm_contact_log_conn_id']);

            //CRM_Core_Error::debug_var('row',$row);
            if ( $this->_showDetails ) {
                $cid = $row['log_civicrm_contact_id'];
                $row['show_details'] = self::getContactDetails($cid);
            }
        }
    }

    function select() {
        //NYSS get log details param and unset column
        $cols   =& $this->_columns;
        $params = $this->_submitValues;
        $this->_showDetails = 0;
        if ( isset($cols['log_civicrm_contact']['fields']['log_details']) &&
             $params['fields']['log_details'] ) {
            $this->_showDetails = 1;
            unset($cols['log_civicrm_contact']['fields']['log_details']);
        }
        
        parent::select();
    }

    function from()
    {
        $this->_from = "
            FROM `{$this->loggingDB}`.log_civicrm_contact {$this->_aliases['log_civicrm_contact']}
            LEFT JOIN civicrm_contact     {$this->_aliases['civicrm_contact']}
              ON ({$this->_aliases['log_civicrm_contact']}.log_user_id = {$this->_aliases['civicrm_contact']}.id)
        ";
        //NYSS LEFT JOIN on user_id since sometimes its NULL (temp fix)
    }

    //4198 calculate distinct contacts
    function statistics( &$rows ) {
        $statistics = parent::statistics( $rows );
        $statistics['counts']['rowsFound'] = array( 'title' => ts('Contact(s) Changed'),
                                                    'value' => $this->_distinctCount );
        return $statistics;
    }

    //NYSS 5184 alter pager url
    function setPager( $rowCount = self::ROW_COUNT_LIMIT ) {
        if ( $this->_limit && ($this->_limit != '') ) {
            require_once 'CRM/Utils/Pager.php';
            $sql    = "SELECT FOUND_ROWS();";
            $this->_rowsFound = CRM_Core_DAO::singleValueQuery( $sql );
            $params = array( 'total'        => $this->_rowsFound,
                             'rowCount'     => $rowCount,
                             'status'       => ts( 'Records' ) . ' %%StatusMessage%%',
                             'buttonBottom' => 'PagerBottomButton',
                             'buttonTop'    => 'PagerTopButton',
                             'pageID'       => $this->get( CRM_Utils_Pager::PAGE_ID ) );

            $pager = new CRM_Utils_Pager( $params );

            //NYSS
            if ( CRM_Utils_Request::retrieve('context', 'String') == 'contact' ) {
                $context = CRM_Utils_Request::retrieve('context', 'String');
                $path = CRM_Utils_System::currentPath();
                foreach ( $pager->_response as $k => $v) {
                    $urlReplace = array( $path     => 'civicrm/contact/view',
                                         'force=1' => 'selectedChild=log',
                                         );
                    $pager->_response[$k] = str_replace( array_keys($urlReplace), array_values($urlReplace), $v );
                }
                //CRM_Core_Error::debug('pager',$pager);
            }

            $this->assign_by_ref( 'pager', $pager );
        }
    }

    //NYSS change how pagination works in contact context
    function buildForm( ) {

        parent::buildForm( );

        if ( CRM_Utils_Request::retrieve('context', 'String') == 'contact' &&
             $cid = $this->cid ) {

            $this->_attributes['action'] = "/civicrm/contact/view?reset=1&cid={$cid}&selectedChild=log";
            $this->_attributes['method'] = "get";
            $this->addElement( 'hidden', 'selectedChild', 'log' );
            //CRM_Core_Error::debug_var('LoggingSummary buildForm $this->_attributes',$this->_attributes);
            //CRM_Core_Error::debug_var('LoggingSummary buildForm this',$this);
            //CRM_Core_Error::debug_var('cid',$cid);
        }
    }

    function getContactDetails( $cid ) {

        $left = $middle = $right = array();
        $leftList = $middleList = $addressList = '';

        $IMProvider = CRM_Core_PseudoConstant::IMProvider();

        $params = array( 'version' => 3,
                         'id'      => $cid,
                         );
        $contact = civicrm_api( 'contact', 'getsingle', $params );
        //CRM_Core_Error::debug('contact',$contact);

        $left['nick_name']  = "{$contact['nick_name']} (nickname)";
        $left['gender']     = "{$contact['gender']} (gender)";
        $left['job_title']  = "{$contact['job_title']} (job title)";
        $left['birth_date'] = "{$contact['birth_date']} (birthday)";

        $middle['phone']    = "{$contact['phone']} (phone)";
        $middle['email']    = "{$contact['email']} (email)";
        $middle['im']       = "{$contact['im']} ({$IMProvider[$contact['provider_id']]})";

        $address['street1'] = $contact['street_address'];
        $address['street2'] = $contact['supplemental_address_1'];
        $address['street3'] = $contact['supplemental_address_2'];
        $address['city']    = $contact['city'];
        $address['state']   = $contact['state_province'];
        $address['zip']     = $contact['postal_code'];

        //check against contact and remove if empty
        foreach ( $left as $f => $v ) {
            if ( empty($contact[$f]) ) {
                unset($left[$f]);
            }
        }
        foreach ( $middle as $f => $v ) {
            if ( empty($contact[$f]) ) {
                unset($middle[$f]);
            }
        }
        $address = array_filter($address);

        if ( !empty($left) ) {
            $leftList  = "<div class='logLeftList'><ul><li>";
            $leftList .= implode("</li>\n<li>", $left);
            $leftList .= '</li></ul></div>';
        }
        if ( !empty($middle) ) {
            $middleList  = "<div class='logMiddleList'><ul><li>";
            $middleList .= implode("</li>\n<li>", $middle);
            $middleList .= '</li></ul></div>';
        }

        if ( !empty($address) ) {
            $addressList  = "<div class='logRightList'><ul><li>";
            $addressList .= ( $address['street3'] ) ? "{$address['street3']}<br />" : '';
            $addressList .= ( $address['street1'] ) ? "{$address['street1']}<br />" : '';
            $addressList .= ( $address['street2'] ) ? "{$address['street2']}<br />" : '';
            $addressList .= "{$address['city']}, {$address['state']} {$address['zip']}";
            $addressList .= '</li></ul></div>';
        }

        $html = $leftList.$middleList.$addressList;
        //CRM_Core_Error::debug_var('html',$html);

        return $html;
    }

    function _getGroupSQL( $sqlParams ) {

        $replace = array(
            'SQL_CALC_FOUND_ROWS'                 => '',
            'crm_contact_civireport.id'           => 'crm_contact_civireport.contact_id',
            'crm_contact_civireport.display_name' => "CONCAT(group_contact.display_name,'  [',IFNULL(group_table.title, crm_contact_civireport.group_id),']')",
            'crm_contact_civireport.is_deleted'   => 'group_contact.is_deleted',
            'crm_contact_civireport.log_type'     => "'Group'",
            'crm_contact_civireport.log_action'   => 'crm_contact_civireport.status',
            );

        $select = str_replace( array_keys($replace), array_values($replace), $sqlParams['select'] );
        $from   = "FROM `{$sqlParams['logDB']}`.log_civicrm_group_contact crm_contact_civireport
                   LEFT JOIN civicrm_contact contact_civireport
                     ON (crm_contact_civireport.log_user_id = contact_civireport.id)
                   JOIN civicrm_contact group_contact
                     ON crm_contact_civireport.contact_id = group_contact.id
                   LEFT JOIN civicrm_group group_table
                     ON crm_contact_civireport.group_id = group_table.id";
        $cidWhere = ( $sqlParams['cid'] ) ? " crm_contact_civireport.contact_id = {$sqlParams['cid']} " : 1;
        $where  = "WHERE $cidWhere
                     AND group_table.is_hidden != 1
                     AND crm_contact_civireport.log_action != 'Initialization'";
        $sql    = "$select, 'Group' log_type $from $where";
        //CRM_Core_Error::debug_var('sql',$sql);

        return $sql;
    }

    function _getRelationshipASQL( $sqlParams ) {

        $replace = array(
            'SQL_CALC_FOUND_ROWS'                 => '',
            'crm_contact_civireport.id'           => 'crm_contact_civireport.contact_id_a',
            'crm_contact_civireport.display_name' => "CONCAT(rel_contact.display_name,'  [',rel_table.label_a_b,']')",
            'crm_contact_civireport.is_deleted'   => 'rel_contact.is_deleted',
            'crm_contact_civireport.log_type'     => "'Relationship'",
            'crm_contact_civireport.log_action'   => "IF ( crm_contact_civireport.log_action = 'Update', 
                                                           'Modified', 
                                                           crm_contact_civireport.log_action )",
            );

        $select = str_replace( array_keys($replace), array_values($replace), $sqlParams['select'] );
        $from   = "FROM `{$sqlParams['logDB']}`.log_civicrm_relationship crm_contact_civireport
                   LEFT JOIN civicrm_contact contact_civireport
                     ON (crm_contact_civireport.log_user_id = contact_civireport.id)
                   JOIN civicrm_contact rel_contact
                     ON crm_contact_civireport.contact_id_a = rel_contact.id
                   JOIN civicrm_relationship_type rel_table
                     ON crm_contact_civireport.relationship_type_id = rel_table.id";
        $cidWhere = ( $sqlParams['cid'] ) ? " crm_contact_civireport.contact_id_a = {$sqlParams['cid']} " : 1;
        $where  = "WHERE $cidWhere";
        $sql    = "$select, 'Relationship' log_type $from $where";
        //CRM_Core_Error::debug('sql',$sql);

        return $sql;
    }

    function _getRelationshipBSQL( $sqlParams ) {

        $replace = array(
            'SQL_CALC_FOUND_ROWS'                 => '',
            'crm_contact_civireport.id'           => 'crm_contact_civireport.contact_id_b',
            'crm_contact_civireport.display_name' => "CONCAT(rel_contact.display_name,'  [',rel_table.label_b_a,']')",
            'crm_contact_civireport.is_deleted'   => 'rel_contact.is_deleted',
            'crm_contact_civireport.log_type'     => "'Relationship'",
            'crm_contact_civireport.log_action'   => "IF ( crm_contact_civireport.log_action = 'Update', 
                                                           'Modified', 
                                                           crm_contact_civireport.log_action )",
            );

        $select = str_replace( array_keys($replace), array_values($replace), $sqlParams['select'] );
        $from   = "FROM `{$sqlParams['logDB']}`.log_civicrm_relationship crm_contact_civireport
                   LEFT JOIN civicrm_contact contact_civireport
                     ON (crm_contact_civireport.log_user_id = contact_civireport.id)
                   JOIN civicrm_contact rel_contact
                     ON crm_contact_civireport.contact_id_b = rel_contact.id
                   JOIN civicrm_relationship_type rel_table
                     ON crm_contact_civireport.relationship_type_id = rel_table.id";
        $cidWhere = ( $sqlParams['cid'] ) ? " crm_contact_civireport.contact_id_b = {$sqlParams['cid']} " : 1;
        $where  = "WHERE $cidWhere";
        $sql    = "$select, 'Relationship' log_type $from $where";
        //CRM_Core_Error::debug('sql',$sql);

        return $sql;
    }

    function _getNoteSQL( $sqlParams ) {

        $replace = array(
            'SQL_CALC_FOUND_ROWS'                 => '',
            'crm_contact_civireport.id'           => 'crm_contact_civireport.entity_id',
            'crm_contact_civireport.display_name' => "CONCAT(note_contact.display_name,'  [',crm_contact_civireport.subject,']')",
            'crm_contact_civireport.is_deleted'   => 'note_contact.is_deleted',
            'crm_contact_civireport.log_type'     => "'Note'",
            'crm_contact_civireport.log_action'   => "IF ( crm_contact_civireport.log_action = 'Update', 
                                                           'Modified', 
                                                           crm_contact_civireport.log_action )",
            );

        $select = str_replace( array_keys($replace), array_values($replace), $sqlParams['select'] );
        $from   = "FROM `{$sqlParams['logDB']}`.log_civicrm_note crm_contact_civireport
                   LEFT JOIN civicrm_contact     contact_civireport
                     ON (crm_contact_civireport.log_user_id = contact_civireport.id)
                   JOIN civicrm_contact note_contact
                     ON crm_contact_civireport.entity_id = note_contact.id";
        $cidWhere = ( $sqlParams['cid'] ) ? " crm_contact_civireport.entity_id = {$sqlParams['cid']} " : 1;
        $where  = "WHERE crm_contact_civireport.entity_table = 'civicrm_contact' AND $cidWhere";
        $sql    = "$select, 'Note' log_type $from $where";
        //CRM_Core_Error::debug('sql',$sql);

        return $sql;
    }

    //NYSS 5217
    function _getCommentSQL( $sqlParams, $rawWhere ) {

        $replace = array(
            'SQL_CALC_FOUND_ROWS'                 => '',
            'crm_contact_civireport.id'           => 'note_tbl.entity_id',
            'crm_contact_civireport.display_name' => "CONCAT(note_contact.display_name,'  [',crm_contact_civireport.subject,']')",
            'crm_contact_civireport.is_deleted'   => 'note_contact.is_deleted',
            'crm_contact_civireport.log_type'     => "'Comment'",
            'crm_contact_civireport.log_action'   => "IF ( crm_contact_civireport.log_action = 'Update', 
                                                           'Modified', 
                                                           crm_contact_civireport.log_action )",
            );

        $select = str_replace( array_keys($replace), array_values($replace), $sqlParams['select'] );
        $from   = "FROM `{$sqlParams['logDB']}`.log_civicrm_note crm_contact_civireport
                   JOIN civicrm_note note_tbl
                     ON crm_contact_civireport.entity_id = note_tbl.id
                   LEFT JOIN civicrm_contact contact_civireport
                     ON (crm_contact_civireport.log_user_id = contact_civireport.id)
                   JOIN civicrm_contact note_contact
                     ON note_tbl.entity_id = note_contact.id";
        $cidWhere = ( $sqlParams['cid'] ) ? " note_tbl.entity_id = {$sqlParams['cid']} " : 1;
        $where  = "WHERE crm_contact_civireport.entity_table = 'civicrm_note' AND $cidWhere";
        $sql    = "$select, 'Comment' log_type $from $where";
        //CRM_Core_Error::debug_var('comment sql',$sql);

        return $sql;
    }
}
