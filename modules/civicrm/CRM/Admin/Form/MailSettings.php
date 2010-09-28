<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
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

require_once 'CRM/Admin/Form.php';
require_once 'CRM/Core/BAO/MailSettings.php';

/**
 * This class handles mail account settings.
 * 
 */
class CRM_Admin_Form_MailSettings extends CRM_Admin_Form
{  
    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
    {
        parent::buildQuickForm( );
        
        if ($this->_action & CRM_Core_Action::DELETE ) { 
            return;
        }
        
        $this->applyFilter('__ALL__', 'trim');
        
        //get the attributes.
        $attributes = CRM_Core_DAO::getAttribute( 'CRM_Core_DAO_MailSettings' );
        
        //build setting form
        $this->add( 'text', 'name', ts('Name'), $attributes['name'], true );
        
        $this->add('text', 'domain', ts('Email Domain'), $attributes['domain'], true );
        $this->addRule( 'domain', ts('Email domain must use a valid internet domain format (e.g. \'example.org\').'), 'domain' );
        
        $this->add( 'text', 'localpart', ts('Localpart'), $attributes['localpart'] );
        
        $this->add('text', 'return_path', ts('Return-Path'), $attributes['return_path'] );
        $this->addRule( 'return_path', ts('Return-Path must use a valid email address format.'), 'email' );
        
        require_once 'CRM/Core/PseudoConstant.php';
        $this->add( 'select', 'protocol', 
                    ts( 'Protocol' ),
                    array('' => ts('- select -')) + CRM_Core_PseudoConstant::mailProtocol(),
                    true);
        
        $this->add( 'text', 'server', ts('Server'), $attributes['server'] );
        
        $this->add( 'text', 'username', ts('Username'), array( 'autocomplete' => 'off' ) );
        
        $this->add( 'password', 'password', ts('Password'), array( 'autocomplete' => 'off' ) );
        
        $this->add( 'text', 'source', ts('Source'), $attributes['source'] );
        
        $this->add('checkbox', 'is_ssl', ts('Use SSL?')); 
        
        $this->add('checkbox', 'is_default', ts('Default Option?')); 
    }
    
    /** 
     * Function to process the form 
     * 
     * @access public 
     * @return None 
     */ 
    function postProcess( ) 
    {
        if ( $this->_action & CRM_Core_Action::DELETE ) {
            CRM_Core_BAO_MailSettings::deleteMailSettings( $this->_id );
            CRM_Core_Session::setStatus( ts('Selected Mail Setting has been deleted.') );
            return;
        }
        
        //get the submitted form values.  
        $formValues = $this->controller->exportValues( $this->_name );
        
        //form fields.
        $fields = array( 'name', 
                         'domain',          
                         'localpart',
                         'server',
                         'return_path',
                         'protocol',
                         'port',
                         'username',
                         'password',
                         'source',
                         'is_ssl',
                         'is_default' );
        
        $params = array( );
        foreach ( $fields as $f ) {
            if ( in_array( $f, array( 'is_default', 'is_ssl' ) ) ) {
                $params[$f] = CRM_Utils_Array::value( $f, $formValues, false );
            } else {
                $params[$f] = CRM_Utils_Array::value( $f, $formValues );
            }
        }

        $params['domain_id'] = CRM_Core_Config::domainID( );

        // assign id only in update mode
        $status = ts( 'Your New  Email Settings have been saved.' );
        if ( $this->_action & CRM_Core_Action::UPDATE ) { 
            $params['id'] = $this->_id;
            $status = ts( 'Your Email Settings have been updated.' );
        }
        
        $mailSettings = CRM_Core_BAO_MailSettings::create( $params );
        
        if ( $mailSettings->id ) {
            CRM_Core_Session::setStatus( $status );
        } else {
            CRM_Core_Session::setStatus( ts( 'Your changes are not saved.') ); 
        }
    }
    
}


