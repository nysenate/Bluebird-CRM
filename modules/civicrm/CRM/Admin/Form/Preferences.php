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

require_once 'CRM/Core/Form.php';

/**
 * This class generates form components for Location Type
 * 
 */
class CRM_Admin_Form_Preferences extends CRM_Core_Form
{
    protected $_system    = false;
    protected $_contactID = null;
    protected $_action    = null;

    protected $_cbs       = null;

    protected $_config    = null;

    protected $_params    = null;

    function preProcess( ) {
        $this->_contactID = CRM_Utils_Request::retrieve( 'cid', 'Positive',
                                                         $this, false );
        $this->_system    = CRM_Utils_Request::retrieve( 'system', 'Boolean',
                                                         $this, false, true );
        $this->_action    = CRM_Utils_Request::retrieve( 'action', 'String',
                                                         $this, false, 'update' );
        if ( isset($action) ) {
            $this->assign( 'action', $action );
        }

        $session = CRM_Core_Session::singleton( );

        require_once 'CRM/Core/DAO/Preferences.php';
        $this->_config = new CRM_Core_DAO_Preferences( );
        $this->_config->domain_id = CRM_Core_Config::domainID( );

        if ( $this->_system ) {
            if ( CRM_Core_Permission::check( 'administer CiviCRM' ) ) {
                $this->_contactID = null;
            } else {
                CRM_Utils_System::fatal( 'You do not have permission to edit preferences' );
            }
            $this->_config->is_domain  = 1;
            $this->_config->contact_id = null;
        } else {
            if ( ! $this->_contactID ) {
                $this->_contactID = $session->get( 'userID' );
                if ( ! $this->_contactID ) {
                    CRM_Utils_System::fatal( 'Could not retrieve contact id' );
                }
                $this->set( 'cid', $this->_contactID );
            }
            $this->_config->is_domain  = 0;
            $this->_config->contact_id = $this->_contactID;
        }

        $this->_config->find( true );
        $session->pushUserContext( CRM_Utils_System::url('civicrm/admin/setting', 'reset=1') );
    }

    function cbsDefaultValues( &$defaults ) {
        require_once 'CRM/Core/BAO/CustomOption.php';
        foreach ( $this->_cbs as $name => $title ) {
            if ( isset( $this->_config->$name ) &&
                 $this->_config->$name ) {
                $value = explode( CRM_Core_BAO_CustomOption::VALUE_SEPERATOR,
                                  substr( $this->_config->$name, 1, -1 ) );
                if ( ! empty( $value ) ) {
                    $defaults[$name] = array( );
                    foreach ( $value as $n => $v ) {
                        $defaults[$name][$v] = 1;
                    }
                }
            }
        }
    }

    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
    {
        parent::buildQuickForm( );

        require_once 'CRM/Core/OptionGroup.php';
        foreach ( $this->_cbs as $name => $title ) {
            $options = array_flip( CRM_Core_OptionGroup::values( $name, false, false, true ) );
            $newOptions = array( );
            foreach ( $options as $key => $val ) {
                $newOptions[ $key ] = $val;
            }
            $this->addCheckBox( $name, $title, 
                                $newOptions,
                                null, null, null, null,
                                array( '&nbsp;&nbsp;', '&nbsp;&nbsp;', '<br/>' ) );
        }

        $this->addButtons( array(
                                 array ( 'type'      => 'next',
                                         'name'      => ts('Save'),
                                         'isDefault' => true   ),
                                 array ( 'type'      => 'cancel',
                                         'name'      => ts('Cancel') ),
                                 )
                           );

        if ($this->_action == CRM_Core_Action::VIEW ) {
            $this->freeze( );
        }
       
    }

    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        foreach ( $this->_cbs as $name => $title ) {
            if ( CRM_Utils_Array::value( $name, $this->_params ) &&
                 is_array( $this->_params[$name] ) ) {
                $this->_config->$name = 
                    CRM_Core_BAO_CustomOption::VALUE_SEPERATOR .
                    implode( CRM_Core_BAO_CustomOption::VALUE_SEPERATOR,
                             array_keys( $this->_params[$name] ) ) .
                    CRM_Core_BAO_CustomOption::VALUE_SEPERATOR;
            } else {
                $this->_config->$name = 'NULL';
            }
        }

        $this->_config->save( );
    }//end of function

}


