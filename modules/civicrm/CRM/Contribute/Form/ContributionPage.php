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
require_once 'CRM/Core/PseudoConstant.php';
require_once 'CRM/Contribute/PseudoConstant.php';

/**
 * form to process actions on the group aspect of Custom Data
 */
class CRM_Contribute_Form_ContributionPage extends CRM_Core_Form {

    /**
     * the page id saved to the session for an update
     *
     * @var int
     * @access protected
     */
    protected $_id;

    /**
     * the pledgeBlock id saved to the session for an update
     *
     * @var int
     * @access protected
     */
    protected $_pledgeBlockID;
    
    /** 
     * are we in single form mode or wizard mode?
     * 
     * @var boolean
     * @access protected 
     */ 
    protected $_single;

    /**
     * is this the first page?
     *
     * @var boolean 
     * @access protected  
     */  
    protected $_first = false;

    /**
     * store price set id.
     *
     * @var int 
     * @access protected  
     */  
    protected $_priceSetID = null;
    

    /**
     * Function to set variables up before form is built
     *
     * @return void
     * @access public
     */
    public function preProcess()
    {
        // current contribution page id
        $this->_id     = $this->get( 'id' );
        $this->_single = $this->get( 'single' );

        if ( !$this->_single ) {
            $session = CRM_Core_Session::singleton();
            $this->_single = $session->get('singleForm');
        }
 
        // setting title and 3rd level breadcrumb for html page if contrib page exists
        if ( $this->_id ) {
            $title = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_ContributionPage', $this->_id, 'title' );
            $breadCrumb = array( array('title' => ts('Configure Contribution Page'), 
                                       'url'   => CRM_Utils_System::url( CRM_Utils_System::currentPath( ), 
                                                                         "action=update&reset=1&id={$this->_id}" )) );
            CRM_Utils_System::appendBreadCrumb( $breadCrumb );
        }
        if ($this->_action == CRM_Core_Action::UPDATE) {
            CRM_Utils_System::setTitle(ts('Configure Page - %1', array(1 => $title)));
        } else if ($this->_action == CRM_Core_Action::VIEW) {
            CRM_Utils_System::setTitle(ts('Preview Page - %1', array(1 => $title)));
        } else if ($this->_action == CRM_Core_Action::DELETE) {
            CRM_Utils_System::setTitle(ts('Delete Page - %1', array(1 => $title)));
        } 
    }

    /**
     * Function to actually build the form
     *
     * @return void
     * @access public
     */
    public function buildQuickForm()
    {
        $this->applyFilter('__ALL__', 'trim');

        if ( $this->_single ) {
            $this->addButtons(array(
                                    array ( 'type'      => 'next',
                                            'name'      => ts('Save'),
                                            'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                                            'isDefault' => true   ),
                                    array ( 'type'      => 'cancel',
                                            'name'      => ts('Cancel') ),
                                    )
                              );
        } else {
            $buttons = array( );
            if ( ! $this->_first ) {
                $buttons[] =  array ( 'type'      => 'back', 
                                      'name'      => ts('<< Previous'), 
                                      'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' );
            }
            $buttons[] = array ( 'type'      => 'next',
                                 'name'      => ts('Continue >>'),
                                 'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                                 'isDefault' => true   );
            $buttons[] = array ( 'type'      => 'cancel',
                                 'name'      => ts('Cancel') );

            $this->addButtons( $buttons );
        }

        // views are implemented as frozen form
        if ($this->_action & CRM_Core_Action::VIEW) {
            $this->freeze();
            $this->addElement('button', 'done', ts('Done'), array('onclick' => "location.href='civicrm/admin/custom/group?reset=1&action=browse'"));
        }
    }

    /**
     * This function sets the default values for the form. Note that in edit/view mode
     * the default values are retrieved from the database
     *
     * @access public
     * @return void
     */
    function setDefaultValues()
    {
        $defaults = array();
        $config   = CRM_Core_Config::singleton();
        if (isset($this->_id)) {
            $params = array('id' => $this->_id);
            CRM_Core_DAO::commonRetrieve( 'CRM_Contribute_DAO_ContributionPage', $params, $defaults);
            
            //set defaults for pledgeBlock values.
            require_once 'CRM/Pledge/BAO/PledgeBlock.php';
            $pledgeBlockParams = array( 'entity_id'    => $this->_id,
                                        'entity_table' => ts('civicrm_contribution_page') );
            $pledgeBlockDefaults = array( );
            CRM_Pledge_BAO_pledgeBlock::retrieve( $pledgeBlockParams, $pledgeBlockDefaults );
            if ( $this->_pledgeBlockID = CRM_Utils_Array::value('id', $pledgeBlockDefaults ) ) {
                $defaults['is_pledge_active'] = true;
            }
            $pledgeBlock = array( 'is_pledge_interval', 'max_reminders', 
                                  'initial_reminder_day', 'additional_reminder_day' );
            foreach ( $pledgeBlock  as $key ) {
                $defaults[$key] = CRM_Utils_Array::value( $key, $pledgeBlockDefaults ); 
            }
            require_once 'CRM/Core/BAO/CustomOption.php';
            if ( CRM_Utils_Array::value( 'pledge_frequency_unit', $pledgeBlockDefaults ) ) {
                $defaults['pledge_frequency_unit'] = 
                    array_fill_keys( explode( CRM_Core_BAO_CustomOption::VALUE_SEPERATOR, 
                                              $pledgeBlockDefaults['pledge_frequency_unit'] ), '1' );
            }

            // fix the display of the monetary value, CRM-4038
            require_once 'CRM/Utils/Money.php';
            if (isset($defaults['goal_amount'])) {
                $defaults['goal_amount'] = CRM_Utils_Money::format($defaults['goal_amount'], null, '%a');
            }
            
            // get price set id.
            require_once 'CRM/Price/BAO/Set.php';
            $this->_priceSetID = CRM_Price_BAO_Set::getFor( 'civicrm_contribution_page', $this->_id );
            if ( $this->_priceSetID ) $defaults['price_set_id'] = $this->_priceSetID;
            
            if ( CRM_Utils_Array::value( 'end_date', $defaults ) ) {
                list( $defaults['end_date'], $defaults['end_date_time'] ) = CRM_Utils_Date::setDateDefaults( $defaults['end_date'] );
            }
            
            if ( CRM_Utils_Array::value( 'start_date', $defaults ) ) {
                list( $defaults['start_date'], $defaults['start_date_time'] ) = CRM_Utils_Date::setDateDefaults( $defaults['start_date'] );
            }
        } else {
            $defaults['is_active'] = 1;
            // set current date as start date
            list( $defaults['start_date'], $defaults['start_date_time'] ) = CRM_Utils_Date::setDateDefaults( );
        }

        if (! isset($defaults['for_organization'])) {
            $defaults['for_organization'] = ts('I am contributing on behalf of an organization.');
        }

        if ( CRM_Utils_Array::value( 'recur_frequency_unit',$defaults ) ) {
            require_once 'CRM/Core/BAO/CustomOption.php';
            $defaults['recur_frequency_unit'] = 
                array_fill_keys( explode( CRM_Core_BAO_CustomOption::VALUE_SEPERATOR, 
                                          $defaults['recur_frequency_unit'] ), '1' );
        } else {
            require_once 'CRM/Core/OptionGroup.php';
            $defaults['recur_frequency_unit'] = 
                array_fill_keys( CRM_Core_OptionGroup::values( 'recur_frequency_units' ), '1' );
        }
        
        if ( CRM_Utils_Array::value( 'is_for_organization', $defaults ) ) {
            $defaults['is_organization'] = 1;
        } else {
            $defaults['is_for_organization'] = 1;
        }
        

        return $defaults;
    }

    /**
     * Process the form
     *
     * @return void
     * @access public
     */
    public function postProcess()
    {
    }
}


