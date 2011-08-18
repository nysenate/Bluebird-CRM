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

/**
 * This class generates form components generic to Mobile provider
 * 
 */
class CRM_Member_Form extends CRM_Core_Form
{
    /**
     * The id of the object being edited / created
     *
     * @var int
     */
    protected $_id;

    /**
     * The name of the BAO object for this form
     *
     * @var string
     */
    protected $_BAOName;

    function preProcess( ) {
        $this->_id      = $this->get( 'id'      );
        $this->_BAOName = $this->get( 'BAOName' );
    }

    /**
     * This function sets the default values for the form. MobileProvider that in edit/view mode
     * the default values are retrieved from the database
     * 
     * @access public
     * @return None
     */
    function setDefaultValues( ) {
        $defaults = array( );
        $params   = array( );

        if ( isset ( $this->_id ) ) {
            $params = array( 'id' => $this->_id );
            require_once(str_replace('_', DIRECTORY_SEPARATOR, $this->_BAOName) . ".php");
            eval( $this->_BAOName . '::retrieve( $params, $defaults );' );
        }

        if (isset($defaults['minimum_fee'])) {
            require_once 'CRM/Utils/Money.php';
            $defaults['minimum_fee'] = CRM_Utils_Money::format($defaults['minimum_fee'], null, '%a');
        }
        
        
        if ( isset ($defaults['status'] ) ) {
            $this->assign( 'membershipStatus', $defaults['status'] );
        }
        
        if ( $this->_action & CRM_Core_Action::ADD ) {
            $defaults['is_active'] = 1;
        }
        
        if ( isset( $defaults['member_of_contact_id'] ) &&
             $defaults['member_of_contact_id'] ) {
            $defaults['member_org'] = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact', 
                                                                  $defaults['member_of_contact_id'], 'display_name');
        }
        return $defaults;
    }

    /**
     * Function to actually build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
    {
        if ( $this->_action & CRM_Core_Action::RENEW ) {
        	$this->addButtons( array(
                                 array ( 'type'      => 'upload',
                                         'name'      => ts('Renew'),
                                         'isDefault' => true   ),
                                 array ( 'type'      => 'cancel',
                                         'name'      => ts('Cancel') ),
                                 )
                           );
        } elseif ( $this->_action & CRM_Core_Action::DELETE ) {
            $this->addButtons(array(
                                    array ('type'      => 'next',
                                           'name'      => ts('Delete'),
                                           'isDefault' => true),
                                    array ('type'      => 'cancel',
                                           'name'      => ts('Cancel')),
                                    )
                              );
        } else {
            $this->addButtons( array(
                             array ( 'type'      => 'upload',
                                     'name'      => ts('Save'),
                                     'isDefault' => true   ),
                             array ( 'type'      => 'upload',
                                     'name'      => ts('Save and New'), 
                                     'subName'   => 'new' ),
                             array ( 'type'      => 'cancel',
                                     'name'      => ts('Cancel') ),
                             )
                       );
        }
    }

}


