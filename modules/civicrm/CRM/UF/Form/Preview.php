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

require_once 'CRM/Core/Form.php';
require_once 'CRM/Profile/Form.php';
require_once 'CRM/Core/BAO/UFGroup.php';

/**
 * This class generates form components 
 * for previewing Civicrm Profile Group
 * 
 */
class CRM_UF_Form_Preview extends CRM_Core_Form
{
    /** 
     * The group id that we are editing
     * 
     * @var int 
     */ 
    protected $_gid; 
 
    /** 
     * the fields needed to build this form 
     * 
     * @var array 
     */ 
    public $_fields; 

    /**
     * pre processing work done here.
     *
     * gets session variables for group or field id
     *
     * @param
     * @return void
     *
     * @access public
     *
     */
    function preProcess()
    {     
        $flag  = false;
        $this->_gid = $this->get( 'id' );
        $this->set( 'gid', $this->_gid );
        $field = CRM_Utils_Request::retrieve('field', 'Boolean', $this, true , 0);

        if( $field ) {
            $this->_fields = CRM_Core_BAO_UFGroup::getFields( $this->_gid, false, null, null, null, true);
            require_once 'CRM/Core/DAO/UFField.php';
            $fieldDAO = new CRM_Core_DAO_UFField();
            $fieldDAO->id = $this->get( 'fieldId' );
            $fieldDAO->find(true);
            
            if ( $fieldDAO->is_active == 0 ) {
                CRM_Core_Error::statusBounce( ts('This field is inactive so it will not be displayed on profile form.') );
            } elseif ( $fieldDAO->is_view == 1 ) {
                CRM_Core_Error::statusBounce( ts('This field is view only so it will not be displayed on profile form.') );
            }
            $name = $fieldDAO->field_name;
            // preview for field
            $specialFields = array ('street_address','supplemental_address_1', 'supplemental_address_2', 'city', 'postal_code', 'postal_code_suffix', 'geo_code_1', 'geo_code_2', 'state_province', 'country', 'county', 'phone', 'email', 'im' );
            
            if ($fieldDAO->location_type_id) {
                $name .= '-' . $fieldDAO->location_type_id;
            } else if ( in_array( $name, $specialFields ) ) {
                $name .= '-Primary';
            }
            
            if ( isset($fieldDAO->phone_type) ) {
                $name .= '-'.$fieldDAO->phone_type;
            }
            
            $fieldArray[$name]= $this->_fields[$name];
            $this->_fields = $fieldArray;
            if (! is_array($this->_fields[$name])) {
                $flag = true;
            }
            $this->assign('previewField',true);
        } else {
            $this->_fields = CRM_Core_BAO_UFGroup::getFields( $this->_gid );
        }
        
        if ( $flag ) {
            $this->assign('viewOnly',false);
        } else {
            $this->assign('viewOnly',true);
        }
        
        $this->set('fieldId',null);
        $this->assign("fields",$this->_fields); 
    }


    /**
     * Set the default form values
     *
     * @access protected
     * @return array the default array reference
     */
    function &setDefaultValues()
    {
        $defaults = array();
        $stateCountryMap = array( );
        foreach ($this->_fields as $name => $field ) {
            if ($customFieldID = CRM_Core_BAO_CustomField::getKeyID($field['name'])) {
                CRM_Core_BAO_CustomField::setProfileDefaults( $customFieldID, $name, $defaults, null, CRM_Profile_Form::MODE_REGISTER );
            }

            //CRM-5403
            if ( ( substr($name,0,14) === 'state_province') || ( substr($name,0,7) === 'country' ) ) {
                list( $fieldName, $index ) = CRM_Utils_System::explode( '-', $name, 2 );
                if ( ! array_key_exists( $index, $stateCountryMap ) ) {
                    $stateCountryMap[$index] = array( );
                }
                $stateCountryMap[$index][$fieldName] = $name;
            }
        }

        // also take care of state country widget
        if ( !empty($stateCountryMap) ) {
            require_once 'CRM/Core/BAO/Address.php';
            CRM_Core_BAO_Address::addStateCountryMap( $stateCountryMap, $defaults );
        }
        
        //set default for country.
        CRM_Core_BAO_UFGroup::setRegisterDefaults( $this->_fields, $defaults );

        // now fix all state country selectors
        require_once 'CRM/Core/BAO/Address.php';
        CRM_Core_BAO_Address::fixAllStateSelects( $this, $defaults );
        
        return $defaults;
    }

    /**
     * Function to actually build the form
     *
     * @return void
     * @access public
     */
    public function buildQuickForm()
    {
        foreach ($this->_fields as $name => $field ) {
            if ( ! CRM_Utils_Array::value( 'is_view', $field ) ) {
                CRM_Core_BAO_UFGroup::buildProfile($this, $field, CRM_Profile_Form::MODE_CREATE );
            }
        }
        
        $this->addButtons(array(
                                array ('type'      => 'cancel',
                                       'name'      => ts('Done with Preview'),
                                       'isDefault' => true),
                                )
                          );
    }
}


