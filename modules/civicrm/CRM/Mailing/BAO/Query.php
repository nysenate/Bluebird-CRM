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
 | Version 3, 19 November 2007.                                       |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
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

class CRM_Mailing_BAO_Query
{
     
    static $_mailingFields = null;

    static function &getFields( ) {
        if ( ! self::$_mailingFields ) {
            self::$_mailingFields = array( );
            $_mailingFields['mailing_id'] = array(
                                                  'name'  => 'mailing_id',
                                                  'title' => 'Mailing ID',
                                                  'where' => 'civicrm_mailing.id',
                                                  );
        }
        return self::$_mailingFields;
    }

    /**
     * if mailings are involved, add the specific Mailing fields
     *
     * @return void
     * @access public
     */
    static function select( &$query) {
        // if Mailing mode add mailing id
        if ( $query->_mode  & CRM_Contact_BAO_Query::MODE_MAILING ) {
            $query->_select['mailing_id'] = "civicrm_mailing.id as mailing_id";
            $query->_element['mailing_id'] = 1;
            $query->_tables['civicrm_mailing'] = 1;
            $query->_whereTables['civicrm_mailing'] = 1;
        }
    }

    static function where( &$query ) {
        $grouping = null;
        foreach ( array_keys( $query->_params ) as $id ) {
            if ( substr( $query->_params[$id][0], 0, 8 ) == 'mailing_' ) {
                if ( $query->_mode == CRM_Contact_BAO_QUERY::MODE_CONTACTS ) {
                    $query->_useDistinct = true;
                }
                $grouping = $query->_params[$id][3];
                self::whereClauseSingle( $query->_params[$id], $query );
            }
        }
    }

    static function from( $name, $mode, $side ) {
        $from = null;
        switch ( $name ) {
            case 'civicrm_mailing_event_queue':
                $from = " $side JOIN civicrm_mailing_event_queue ON civicrm_mailing_event_queue.contact_id = contact_a.id";
                break;

            case 'civicrm_mailing_job':
                $from = " $side JOIN civicrm_mailing_job ON civicrm_mailing_job.id = civicrm_mailing_event_queue.job_id";
                break;
                
            case 'civicrm_mailing':
                $from = " $side JOIN civicrm_mailing on civicrm_mailing.id = civicrm_mailing_job.mailing_id";
                break;
                
            case 'civicrm_mailing_event_bounce':
            case 'civicrm_mailing_event_opened':
            case 'civicrm_mailing_event_reply':
            case 'civicrm_mailing_event_trackable_url_open':
                $from = " $side JOIN $name ON $name.event_queue_id = civicrm_mailing_event_queue.id";
                 break;
        }

        return $from;
    }

    static function defaultReturnProperties( $mode ) {
        $properties = null;
        if ( $mode & CRM_Contact_BAO_Query::MODE_MAILING ) {
            $properties = array('mailing_id' => 1);
        }
        return $properties;
    }

    static function whereClauseSingle( &$values, &$query ) {
        list( $name, $op, $value, $grouping, $wildcard ) = $values;

        $fields = array( );
        $fields = self::getFields();
        switch ( $name ) {
            case 'mailing_name':
                $value = strtolower( addslashes( $value ) );
                if ( $wildcard ) {
                    $value = "%$value%";
                    $op    = 'LIKE';
                }                
                $query->_where[$grouping][] = "LOWER(civicrm_mailing.name) $op '$value'";
                $query->_qill[$grouping][]  = "Mailing Name $op \"$value\"";
                $query->_tables['civicrm_mailing_event_queue'] = $query->_whereTables['civicrm_mailing_event_queue'] = 1;
                $query->_tables['civicrm_mailing_job'] = $query->_whereTables['civicrm_mailing_job'] = 1;
                $query->_tables['civicrm_mailing'] = $query->_whereTables['civicrm_mailing'] = 1;

                return;

            case 'mailing_date':
            case 'mailing_date_low':
            case 'mailing_date_high':
                // process to / from date
                $query->_tables['civicrm_mailing_event_queue'] = $query->_whereTables['civicrm_mailing_event_queue'] = 1;
                $query->_tables['civicrm_mailing_job'] = $query->_whereTables['civicrm_mailing_job'] = 1;
                $query->dateQueryBuilder( $values,
                    'civicrm_mailing_job', 'mailing_date', 'start_date', 'Mailing Delivery Date');
                return;

            case 'mailing_delivery_status':
                require_once('CRM/Mailing/PseudoConstant.php');
                self::mailingEventQueryBuilder($query, $values,
                    'civicrm_mailing_event_bounce', 'mailing_delivery_status', ts('Mailing Delivery Status'), CRM_Mailing_PseudoConstant::yesNoOptions('bounce'));
                return;
            case 'mailing_open_status':
                require_once('CRM/Mailing/PseudoConstant.php');
                self::mailingEventQueryBuilder($query, $values,
                    'civicrm_mailing_event_opened', 'mailing_open_status', ts('Mailing: Trackable Opens'), CRM_Mailing_PseudoConstant::yesNoOptions('open'));
                return;
            case 'mailing_click_status':
                require_once('CRM/Mailing/PseudoConstant.php');
                self::mailingEventQueryBuilder($query, $values,
                    'civicrm_mailing_event_trackable_url_open', 'mailing_click_status', ts('Mailing: Trackable URL Clicks'), CRM_Mailing_PseudoConstant::yesNoOptions('click'));
                return;
            case 'mailing_reply_status':
                require_once('CRM/Mailing/PseudoConstant.php');
                self::mailingEventQueryBuilder($query, $values,
                    'civicrm_mailing_event_reply', 'mailing_reply_status', ts('Mailing: Trackable Replies'), CRM_Mailing_PseudoConstant::yesNoOptions('reply'));
                return;

        }
    }

    /**
     * add all the elements shared between Mailing search and advnaced search
     *
     * @access public
     * @return void
     * @static
     */
    static function buildSearchForm( &$form ) {
        // mailing selectors
        $form->addElement( 'text', 'mailing_name', ts('Mailing Name'), CRM_Core_DAO::getAttribute('CRM_Mailing_DAO_Mailing', 'name') );
        $form->addDate( 'mailing_date_low', ts('Mailing Date - From'), false, array( 'formatType' => 'searchDate') );
        $form->addDate( 'mailing_date_high', ts('To'), false, array( 'formatType' => 'searchDate') );

        // event filters
        require_once('CRM/Mailing/PseudoConstant.php');
        $form->addRadio( 'mailing_delivery_status', ts('Delivery Status'), CRM_Mailing_PseudoConstant::yesNoOptions('bounce'));
        $form->addRadio( 'mailing_open_status', ts('Trackable Opens'), CRM_Mailing_PseudoConstant::yesNoOptions('open'));
        $form->addRadio( 'mailing_click_status', ts('Trackable URLs'), CRM_Mailing_PseudoConstant::yesNoOptions('click'));
        $form->addRadio( 'mailing_reply_status', ts('Trackable Replies'), CRM_Mailing_PseudoConstant::yesNoOptions('reply'));

        $form->assign( 'validCiviMailing', true );
        $form->addFormRule( array( 'CRM_Mailing_BAO_Query', 'formRule' ), $form );
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
    static function formRule( $fields, $files, $self ) {
        $errors = array( );
        // if an event filter is specified, then a mailing selector must also be specified
        if ( ( CRM_Utils_Array::value('mailing_delivery_status', $fields) ||
               CRM_Utils_Array::value('mailing_open_status', $fields) ||
               CRM_Utils_Array::value('mailing_click_status', $fields) ||
               CRM_Utils_Array::value('mailing_reply_status', $fields) ) &&
             ( !CRM_Utils_Array::value('mailing_name', $fields) &&
               !CRM_Utils_Array::value('mailing_date_low', $fields) &&
               !CRM_Utils_Array::value('mailing_date_high', $fields) ) ) {
            $errors['mailing_name'] = ts('Must specify mailing name or date');
            // Keep search form opened in case of form rule.
            if ( is_a($self, 'CRM_Contact_Form_Search_Advanced') && !isset(CRM_Contact_BAO_Query::$_openedPanes['Mailings']) ) {
                CRM_Contact_BAO_Query::$_openedPanes['Mailings'] = true;
                $self->assign( 'openedPanes' , CRM_Contact_BAO_Query::$_openedPanes );
            }
        }
        return $errors;
    }

    static function addShowHide( &$showHide ) {
        $showHide->addHide( 'MailingForm' );
        $showHide->addShow( 'MailingForm_show' );
    }

    static function searchAction( &$row, $id ) {
    }
    
    static function tableNames( &$tables ) {
    }

    /**
     * Filter query results based on which contacts do (not) have a particular mailing event in their history.
     *
     * @param $query
     * @param $values
     * @param $tableName
     * @param $fieldName
     * @param $fieldTitle
     * @return void
     */
    static function mailingEventQueryBuilder(&$query, &$values, $tableName, $fieldName, $fieldTitle, &$valueTitles) {
        list( $name, $op, $value, $grouping, $wildcard ) = $values;

        if (empty($value) || $value == 'A') {
            // don't do any filtering
            return;
        }

        if ($value == 'Y') {
            $query->_where[$grouping][] = $tableName . ".id is not null ";
        } else if ($value == 'N') {
            $query->_where[$grouping][] = $tableName . ".id is null ";
        }

        $query->_qill[$grouping ][] = $fieldTitle . ' - ' . $valueTitles[$value];
        $query->_tables['civicrm_mailing_event_queue'] = $query->_whereTables['civicrm_mailing_event_queue'] = 1;
        $query->_tables[$tableName] = $query->_whereTables[$tableName] = 1;
    }
    
}
