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
require_once 'CRM/Core/ShowHideBlocks.php';

/**
 * form to process actions on the field aspect of Price
 */
class CRM_Price_Form_Field extends CRM_Core_Form
{

    /**
     * Constants for number of options for data types of multiple option.
     */
    const NUM_OPTION = 11;

    /**
     * the custom set id saved to the session for an update
     *
     * @var int
     * @access protected
     */
    protected $_sid;

    /**
     * The field id, used when editing the field
     *
     * @var int
     * @access protected
     */
    protected $_fid;
    
    /**
     * Function to set variables up before form is built
     * 
     * @param null
     * 
     * @return void
     * @access public
     */
    public function preProcess()
    {
        require_once 'CRM/Price/BAO/Field.php';
        
        $this->_sid = CRM_Utils_Request::retrieve('sid', 'Positive', $this);
        $this->_fid = CRM_Utils_Request::retrieve('fid' , 'Positive', $this);
        $url = CRM_Utils_System::url( 'civicrm/admin/price/field', "reset=1&action=browse&sid={$this->_sid}");
        $breadCrumb     = array( array('title' => ts('Price Set Fields'),
                                       'url'   => $url) );
        CRM_Utils_System::appendBreadCrumb( $breadCrumb );        
        
    }

    /**
     * This function sets the default values for the form. Note that in edit/view mode
     * the default values are retrieved from the database
     * 
     * @param null
     * 
     * @return array    array of default values
     * @access public
     */
    function setDefaultValues()
    {
        $defaults = array();
       
        // is it an edit operation ?
        if (isset($this->_fid)) {
            $params = array('id' => $this->_fid);
            $this->assign('id',$this->_fid);
            CRM_Price_BAO_Field::retrieve($params, $defaults);
            $this->_sid = $defaults['price_set_id'];

            // if text, retrieve price
            if ( $defaults['html_type'] == 'Text' ) {
                $optionValues = array();
                
                require_once 'CRM/Core/OptionGroup.php';
                CRM_Core_OptionGroup::getAssoc( "civicrm_price_field.amount.{$this->_fid}", $optionValues );
                
                // fix the display of the monetary value, CRM-4038
                require_once 'CRM/Utils/Money.php';
                $defaults['price'] = CRM_Utils_Money::format( $optionValues['value'][1], null, '%a' );
            }
        } else {
            $defaults['is_active'] = 1;
            for($i=1; $i<=self::NUM_OPTION; $i++) {
                $defaults['option_status['.$i.']'] = 1;
                $defaults['option_weight['.$i.']'] = $i;
            }
        }

        if ($this->_action & CRM_Core_Action::ADD) {
            require_once 'CRM/Utils/Weight.php';
            $fieldValues = array('price_set_id' => $this->_sid);
            $defaults['weight'] = CRM_Utils_Weight::getDefaultWeight('CRM_Price_DAO_Field', $fieldValues);
            $defaults['options_per_line'] = 1;
            $defaults['is_display_amounts'] = 1;
        }

        return $defaults;
    }

    /**
     * Function to actually build the form
     *
     * @param null
     * 
     * @return void
     * @access public
     */
    public function buildQuickForm()
    {
        // lets trim all the whitespace
        $this->applyFilter('__ALL__', 'trim');
        
        // label
        $this->add('text', 'label', ts('Field Label'), CRM_Core_DAO::getAttribute('CRM_Price_DAO_Field', 'label'), true);
        
        // html_type
        $javascript = 'onchange="option_html_type(this.form)";';

        require_once 'CRM/Price/BAO/Field.php';
        $htmlTypes = CRM_Price_BAO_Field::htmlTypes( );
        
        $sel = $this->add('select', 'html_type', ts('Input Field Type'), 
                          $htmlTypes, true, $javascript );
        
        // Text box for Participant Count for a field
        $extendComponentId = CRM_Core_DAO::getFieldValue( 'CRM_Price_DAO_Set', $this->_sid, 'extends', 'id' );
        require_once 'CRM/Core/Component.php';
        if( $extendComponentId == CRM_Core_Component::getComponentID( 'CiviEvent' ) ) {
            $this->add('text', 'count', ts('Participant Count'), CRM_Core_DAO::getAttribute('CRM_Price_DAO_Field', 'count') );
            $this->addRule('count', ts('Participant Count should be a positive number') , 'positiveInteger');
        }
        
        // price (for text inputs)
        $this->add( 'text', 'price', ts('Price') );
        $this->registerRule( 'price', 'callback', 'money', 'CRM_Utils_Rule' );
        $this->addRule( 'price', ts('must be a monetary value'), 'money' );
        
        if ($this->_action == CRM_Core_Action::UPDATE) {
            $this->freeze('html_type');
        }

        // form fields of Custom Option rows
        $_showHide = new CRM_Core_ShowHideBlocks('','');
        $attributes = CRM_Core_DAO::getAttribute( 'CRM_Core_DAO_OptionValue' );
        $labelAttribute  = $attributes['label' ];
        $nameAttribute   = $attributes['name'  ];
        $weightAttribute = $attributes['weight'];

        for($i = 1; $i <= self::NUM_OPTION; $i++) {
            
            //the show hide blocks
            $showBlocks = 'optionField_'.$i;
            if ($i > 2) {
                $_showHide->addHide($showBlocks);
                if ($i == self::NUM_OPTION)
                    $_showHide->addHide('additionalOption');
            } else {
                $_showHide->addShow($showBlocks);
            }
            // label
            $this->add('text','option_label['.$i.']', ts('Label'), $labelAttribute);
            
            // name
            $this->add('text', 'option_name['.$i.']', ts('Name'), $nameAttribute);
            
            // Below rule is uncommented for CRM-1313
            $this->addRule('option_name['.$i.']' , ts('Please enter a valid amount for this field.'), 'money');
            
            // weight
            $this->add('text', 'option_weight['.$i.']', ts('Order'), $weightAttribute);

            // is active ?
            $this->add('checkbox', 'option_status['.$i.']', ts('Active?'));

            $defaultOption[$i] = $this->createElement('radio', null, null, null, $i);

            //for checkbox handling of default option
            $this->add('checkbox', "default_checkbox_option[$i]", null);
        }
         //default option selection
        $this->addGroup($defaultOption, 'default_option');
        $_showHide->addToTemplate();                

        // is_display_amounts
        $this->add('checkbox', 'is_display_amounts', ts('Display Amount?') );

        // weight
        $this->add('text', 'weight', ts('Order'), CRM_Core_DAO::getAttribute('CRM_Price_DAO_Field', 'weight'), true);
        $this->addRule('weight', ts('is a numeric field') , 'numeric');

        // checkbox / radio options per line
        $this->add('text', 'options_per_line', ts('Options Per Line'));
        $this->addRule('options_per_line', ts('must be a numeric value') , 'numeric');

        // help post, mask, attributes, javascript ?
        $this->add('textarea', 'help_post', ts('Field Help'), 
                   CRM_Core_DAO::getAttribute('CRM_Price_DAO_Field', 'help_post'));        

        // active_on
        $date_options = array(
            //'format' => 'dmY His',
            'minYear' => date('Y'),
            'maxYear' => date('Y') + 5,
            'addEmptyOption' => true
        );
        $this->add('date', 'active_on', ts('Active On'), $date_options );

        // expire_on
        $this->add('date', 'expire_on', ts('Expire On'), $date_options );

        // is required ?
        $this->add('checkbox', 'is_required', ts('Required?') );

        // is active ?
        $this->add('checkbox', 'is_active', ts('Active?'));
        
        // add buttons
        $this->addButtons(array(
                                array ('type'      => 'next',
                                       'name'      => ts('Save'),
                                       'isDefault' => true),
                                array ('type'      => 'next',
                                       'name'      => ts('Save and New'),
                                       'subName'   => 'new'),
                                array ('type'      => 'cancel',
                                       'name'      => ts('Cancel')),
                                )
                          );
        // is public?
        require_once 'CRM/Core/PseudoConstant.php';
        $this->add( 'select', 'visibility_id', ts('Visibility'), CRM_Core_PseudoConstant::visibility( ) );

        // add a form rule to check default value
        $this->addFormRule( array( 'CRM_Price_Form_Field', 'formRule' ),$this );

        // if view mode pls freeze it with the done button.
        if ($this->_action & CRM_Core_Action::VIEW) {
            $this->freeze();
            $url = CRM_Utils_System::url( 'civicrm/admin/price/field', 'reset=1&action=browse&sid=' . $this->_sid );
            $this->addElement( 'button',
                               'done',
                               ts('Done'),
                               array( 'onclick' => "location.href='$url'" ) );
        }
    }
    
    /**
     * global validation rules for the form
     *
     * @param array  $fields   (referance) posted values of the form
     *
     * @return array    if errors then list of errors to be posted back to the form,
     *                  true otherwise
     * @static
     * @access public
     */
    static function formRule( $fields, $files, $form ) {
        
        // all option fields are of type "money"
        $errors = array( );
        
        /** Check the option values entered
         *  Appropriate values are required for the selected datatype
         *  Incomplete row checking is also required.
         */
        if ( ( $form->_action & CRM_Core_Action::ADD || $form->_action & CRM_Core_Action::UPDATE ) &&
             $fields['html_type'] == 'Text' && $fields['price'] ==  NULL ) {
            $errors['price'] = ts( 'Price is a required field' );
        }
        //avoid the same price field label in Within PriceSet
        $priceFieldLabel = new CRM_Price_DAO_Field();
        $priceFieldLabel->label        = $fields['label'] ;
        $priceFieldLabel->price_set_id = $form->_sid;

        $dupeLabel = false;
        if ( $priceFieldLabel->find( true ) && $form->_fid != $priceFieldLabel->id ) {
            $dupeLabel = true;
        }
        
        if ( $dupeLabel ) {
            $errors['label'] = ts('Name already exists in Database.');
        }
        
        if ( ( is_numeric( CRM_Utils_Array::value( 'count', $fields ) ) && CRM_Utils_Array::value( 'count', $fields ) == 0 ) ) {
            $errors['count'] = ts('Participant Count must be greater than zero.');
        }
        
        if ( $form->_action & CRM_Core_Action::ADD ) {
            
            if( $fields['html_type'] != 'Text' ) {
                $countemptyrows = 0;
                
                for ( $index = ( self::NUM_OPTION ) ; $index > 0 ; $index-- ) { 
                    
                    $noLabel = $noAmount = $noWeight = 1;
                    if ( ! empty( $fields['option_label'][$index] ) ) {
                        $noLabel    =  0;

                        $duplicateIndex =  CRM_Utils_Array::key( $fields['option_label'][$index],
                                                                 $fields['option_label'] );
                        
                        if( ( ! ( $duplicateIndex === false ) ) && 
                            ( ! ( $duplicateIndex == $index ) ) ) {
                            $errors["option_label[{$index}]"] = ts( 'Duplicate label value' );      
                        }

                    }

                    // allow for 0 value.
                    if ( ! empty( $fields['option_name'][$index] ) ||
                         strlen( $fields['option_name'][$index] ) > 0 ) {
                        $noAmount    =  0;
                    }
                    
                    if ( ! empty( $fields['option_weight'][$index] ) ) {
                        $noWeight    =  0;

                        $duplicateIndex =  CRM_Utils_Array::key( $fields['option_weight'][$index],
                                                             $fields['option_weight'] );
                        
                        if( ( ! ( $duplicateIndex === false ) ) && 
                            ( ! ( $duplicateIndex == $index ) ) ) {
                            $errors["option_weight[{$index}]"] = ts( 'Duplicate weight value' );      
                        }
                    }
                    
                    if ( $noLabel && ! $noAmount ) {
                        $errors["option_label[{$index}]"] = ts( 'Label cannot be empty.' );      
                    }
                    
                    if ( ! $noLabel && $noAmount ) {
                        $errors["option_name[{$index}]"] = ts( 'Amount cannot be empty.' );
                    }

                    if ( $noLabel && $noAmount ) {
                        $countemptyrows++; 
                    }
                }
                
                if ( $countemptyrows == 11 ) {
                    $errors["option_label[1]"] = 
                        $errors["option_name[1]"] = 
                        ts( 'Label and value cannot be empty.' );    
                }
            }
          
            $_showHide = new CRM_Core_ShowHideBlocks('','');
            
            // do not process if no option rows were submitted
            if ( empty( $fields['option_name'] ) && empty( $fields['option_label'] ) ) {
                return true;
            }
            
            if ( empty( $fields['option_name'] ) ) {
                $fields['option_name'] = array( );
            }
            
            if ( empty( $fields['option_label'] ) ) {
                $fields['option_label'] = array( );
            }
            
            $dupeLabels = array();
            $count = 0;
            for ( $idx = 1; $idx <= self::NUM_OPTION; $idx++ ) {
                
                $_flagOption = 0;
                $_rowError   = 0;
                
                $showBlocks  = 'optionField_'.$idx;
                
                // both value and label are empty
                if ( $fields['option_name'][$idx] == '' && $fields['option_label'][$idx] == '' ) {
                    $_showHide->addHide($showBlocks);
                    $count++;
                    
                    if( $count == 11 ) { 
                        $showBlocks = 'optionField_'.'1';
                        $_showHide->addShow($showBlocks);
                    }
                    
                    continue;
                }
                
                $_showHide->addShow($showBlocks);
                
                if ( $fields['option_name'][$idx] != '' ) {
                    // check for empty label
                    if ( $fields['option_label'][$idx] == '' ) {
                        $errors['option_label]['.$idx.']'] = ts( 'Option label cannot be empty' );
                    }
                    // all fields are money fields
                    if ( ! CRM_Utils_Rule::money( $fields['option_name'][$idx] ) ) {
                        $_flagOption = 1;
                        $errors['option_name['.$idx.']'] = ts( 'Please enter a valid money value.' );
                        
                    }
                }
                
                if ( $fields['option_label'][$idx] != '' ) {
                    // check for empty value
                    if ( $fields['option_name'][$idx] == '' ) {
                        $errors['option_name]['.$idx.']'] = ts( 'Option value cannot be empty' );
                    }
                    // check for duplicate labels, if not already done
                    if ( isset( $dupeLabels[$idx] ) ) {
                        continue;
                    }
                    $also_in = array_keys( $fields['option_label'], $fields['option_label'][$idx] );
                    // first match is always the current key
                    unset( $also_in[0] );
                    if ( !empty( $also_in ) ) {
                        $_flagOption = 1;
                        $errors['option_label]['.$idx.']'] = ts( 'Duplicate Option label' );
                        foreach ( $also_in as $also_in_key ) {
                            $errors['option_name]['.$also_in_key.']'] = ts( 'Duplicate Option label' );
                            $dupeValues[$also_in_key] = true;
                        }
                    }
                }
                
                if ($_flagOption) {
                    $_showHide->addShow($showBlocks);
                    $_rowError = 1;
                }
                
                // last row - hide "Additional Option" option
                if ($idx == self::NUM_OPTION) {
                    $hideBlock = 'additionalOption';
                    $_showHide->addHide($hideBlock);
                }
                
            }
            
            $_showHide->addToTemplate();
        }
        
        return empty($errors) ? true : $errors;
    }
    
    /**
     * Process the form
     * 
     * @param null
     * 
     * @return void
     * @access public
     */
    public function postProcess()
    {
        // store the submitted values in an array
        $params = $this->controller->exportValues('Field');
        
        $params['name']               = CRM_Utils_String::titleToVar($params['label']);
        $params['is_display_amounts'] = CRM_Utils_Array::value( 'is_display_amounts', $params, false );
        $params['is_required']        = CRM_Utils_Array::value( 'is_required', $params, false );
        $params['is_active']          = CRM_Utils_Array::value( 'is_active', $params, false );
        $params['active_on']          = CRM_Utils_Date::format( CRM_Utils_Array::value( 'active_on', $params ) );
        $params['expire_on']          = CRM_Utils_Date::format( CRM_Utils_Array::value( 'expire_on', $params ) );
        $params['visibility_id']      = CRM_Utils_Array::value( 'visibility_id', $params, false );
        $params['count']              = CRM_Utils_Array::value( 'count', $params, false );
        
        // need the FKEY - price set id
        $params['price_set_id'] = $this->_sid;
        
        require_once 'CRM/Utils/Weight.php';
        if ($this->_action & (CRM_Core_Action::UPDATE | CRM_Core_Action::ADD)) {
            $fieldValues = array( 'price_set_id' => $this->_sid );
            $oldWeight   = null;
            if ( $this->_fid ) {
                $oldWeight = CRM_Core_DAO::getFieldValue( 'CRM_Price_DAO_Field', $this->_fid, 'weight', 'id' );
            }
            $params['weight'] = 
                CRM_Utils_Weight::updateOtherWeights( 'CRM_Price_DAO_Field', $oldWeight, $params['weight'], $fieldValues );
        }
        
        // make value <=> name consistency.
        if ( isset( $params['option_name'] ) ) $params['option_value'] = $params['option_name'];
        $params['is_enter_qty'] = CRM_Utils_Array::value( 'is_enter_qty', $params, false );
        
        if ( $params['html_type'] == 'Text' ) {
            // if html type is Text, force is_enter_qty on
            $params['is_enter_qty'] = 1;
            // modify params values as per the option group and option
            // value
            $params['option_value'] = array( 1 => $params['price'] );
            $params['option_name']  = array( 1 => $params['price'] );
            $params['option_label'] = array( 1 => $params['label'] );
            $params['option_weight'] = array( 1 => $params['weight'] );
            $params['is_active']    = array( 1 => 1 );
        }
        
        $ids = array( );
        
        if ( $this->_fid ) {
            $ids['id'] = $this->_fid;
        }
        
        $priceField = CRM_Price_BAO_Field::create( $params, $ids );
        
        if( ! is_a( $priceField, 'CRM_Core_Error' ) ) {
            CRM_Core_Session::setStatus(ts('Price Field \'%1\' has been saved.', array(1 => $priceField->label)));
        }
        $buttonName = $this->controller->getButtonName( );
        $session = CRM_Core_Session::singleton( );
        if ( $buttonName == $this->getButtonName( 'next', 'new' ) ) {
            CRM_Core_Session::setStatus(ts(' You can add another price set field.'));
            $session->replaceUserContext(CRM_Utils_System::url('civicrm/admin/price/field', 'reset=1&action=add&sid=' . $this->_sid));
        }
    }
}

