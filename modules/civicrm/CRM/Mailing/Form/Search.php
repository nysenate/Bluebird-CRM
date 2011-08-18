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

require_once 'CRM/Core/Form.php';

class CRM_Mailing_Form_Search extends CRM_Core_Form {

    public function preProcess( ) {
        parent::preProcess( );
    }

    public function buildQuickForm( ) {
        $this->add( 'text', 'mailing_name', ts( 'Mailing Name' ),
                    CRM_Core_DAO::getAttribute('CRM_Mailing_DAO_Mailing', 'title') );
                    
        $this->addDate( 'mailing_from', ts('From'), false, array( 'formatType' => 'searchDate') );
        $this->addDate( 'mailing_to', ts('To'), false, array( 'formatType' => 'searchDate') );
        
        $this->add( 'text', 'sort_name', ts( 'Created or Sent by' ), 
                    CRM_Core_DAO::getAttribute('CRM_Contact_DAO_Contact', 'sort_name') );
        
        require_once 'CRM/Campaign/BAO/Campaign.php';
        CRM_Campaign_BAO_Campaign::addCampaignInComponentSearch( $this );
        
        foreach ( array('Scheduled', 'Complete', 'Running') as $status ) {
            $this->addElement( 'checkbox', "mailing_status[$status]", null, $status );
        }

        $this->addButtons(array( 
                                array ('type'      => 'refresh', 
                                       'name'      => ts('Search'), 
                                       'isDefault' => true ), 
                                ) ); 
    }

    function setDefaultValues( ) {
        $defaults = array( );
        foreach ( array('Scheduled', 'Complete', 'Running') as $status ) {
            $defaults['mailing_status'][$status] = 1;
        }
        return $defaults;
    }

    function postProcess( ) {
        $params = $this->controller->exportValues( $this->_name );
        
        $parent = $this->controller->getParent( );
        if ( ! empty( $params ) ) {
            $fields = array( 'mailing_name', 'mailing_from', 'mailing_to', 'sort_name', 'campaign_id', 'mailing_status' );
            foreach ( $fields as $field ) {
                if ( isset( $params[$field] ) &&
                     ! CRM_Utils_System::isNull( $params[$field] ) ) { 
                    if ( in_array( $field, array( 'mailing_from', 'mailing_to' ) ) ) { 
                        $time = ( $field == 'mailing_to' ) ? '235959' : null;
                        $parent->set( $field, CRM_Utils_Date::processDate( $params[$field], $time ) );
                    } else {
                        $parent->set( $field, $params[$field] );
                    }
                } else {
                    $parent->set( $field, null );
                }
            }
        }
    }
}


