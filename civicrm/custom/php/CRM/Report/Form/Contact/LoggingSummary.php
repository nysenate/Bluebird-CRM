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
                        'default' => true,
                        'title'   => ts('Action'),
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

    function alterDisplay(&$rows)
    {
        // cache for id → is_deleted mapping
        $isDeleted = array();

        foreach ($rows as &$row) {
            if (!isset($isDeleted[$row['log_civicrm_contact_id']])) {
                $isDeleted[$row['log_civicrm_contact_id']] = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact', $row['log_civicrm_contact_id'], 'is_deleted') !== '0';
            }

            if (!$isDeleted[$row['log_civicrm_contact_id']]) {
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
            JOIN civicrm_contact     {$this->_aliases['civicrm_contact']}
            ON ({$this->_aliases['log_civicrm_contact']}.log_user_id = {$this->_aliases['civicrm_contact']}.id)
        ";
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
}
