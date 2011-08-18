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

class CRM_Group_Form_Search extends CRM_Core_Form {

    public function preProcess( ) {
        parent::preProcess( );
    }

   function setDefaultValues( ) {
        $defaults = array( );
        $defaults['active_status'] = 1;
        return $defaults;
    }

    public function buildQuickForm( ) {
        $this->add( 'text', 'title', ts( 'Find' ),
                    CRM_Core_DAO::getAttribute( 'CRM_Contact_DAO_Group', 'title' ) );

        require_once 'CRM/Core/OptionGroup.php';
        $groupTypes = CRM_Core_OptionGroup::values( 'group_type', true );
        $config= CRM_Core_Config::singleton( );
        if ( $config->userFramework == 'Joomla' ) {
            unset( $groupTypes['Access Control'] );
        }
        
        $this->addCheckBox( 'group_type',
                            ts( 'Type' ),
                            $groupTypes,
                            null, null, null, null, '&nbsp;&nbsp;&nbsp;' );
        
        $this->add( 'select', 'visibility', ts('Visibility'        ),
                    array( '' => ts('- any visibility -' ) ) + CRM_Core_SelectValues::ufVisibility( true ) );
        $this->addElement( 'checkbox', 'active_status' , ts( 'Enabled' ) );
        $this->addElement( 'checkbox', 'inactive_status' , ts( 'Disabled' ) );
        $this->addButtons(array( 
                                array ('type'      => 'refresh', 
                                       'name'      => ts('Search'), 
                                       'isDefault' => true ), 
                                ) ); 

        parent::buildQuickForm( );
    }

    function postProcess( ) {
        $params = $this->controller->exportValues( $this->_name );
        $parent = $this->controller->getParent( );
        if ( ! empty( $params ) ) {
            $fields = array( 'title', 'group_type', 'visibility','active_status','inactive_status' );
            foreach ( $fields as $field ) {
                if ( isset( $params[$field] ) &&
                     ! CRM_Utils_System::isNull( $params[$field] ) ) {
                    $parent->set( $field, $params[$field] );
                } else {
                    $parent->set( $field, null );
                }
            }
        }
    }
}


