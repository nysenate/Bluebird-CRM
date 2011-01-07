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

require_once 'CRM/Contribute/Form/ContributionPage.php';
require_once 'CRM/Contribute/PseudoConstant.php';

/**
 * form to process actions fo adding product to contribution page                            
 */
class CRM_Contribute_Form_ContributionPage_AddProduct extends CRM_Contribute_Form_ContributionPage 
{
    
    static $_products;

    static $_pid;

    /**
     * Function to pre  process the form
     *
     * @access public
     * @return None
     */
    public function preProcess() 
    {
        parent::preProcess();
      
        $this->_products = CRM_Contribute_PseudoConstant::products($this->_id);
        $this->_pid      = CRM_Utils_Request::retrieve('pid', 'Positive',
                                                       $this, false, 0);

        if ( $this->_pid  ) {
            $dao = new CRM_Contribute_DAO_PremiumsProduct();
            $dao->id = $this->_pid;
            $dao->find(true);
            $temp = CRM_Contribute_PseudoConstant::products();
            $this->_products[$dao->product_id] = $temp[$dao->product_id];
        }
        
        //$this->_products = array_merge(array('' => '-- Select Product --') , $this->_products );
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

        if ( $this->_pid ) {
            $dao = new CRM_Contribute_DAO_PremiumsProduct();
            $dao->id = $this->_pid;
            $dao->find(true);
            $defaults['product_id']    = $dao->product_id;
            $defaults['weight'] = $dao->weight;
        }
        if(! isset ($defaults['weight']) || !( $defaults['weight'])) {
            $pageID    = CRM_Utils_Request::retrieve('id', 'Positive',
                                                     $this, false, 0);
            require_once 'CRM/Contribute/DAO/Premium.php';
            $dao = new CRM_Contribute_DAO_Premium();
            $dao->entity_table = 'civicrm_contribution_page';
            $dao->entity_id = $pageID; 
            $dao->find(true);
            $premiumID = $dao->id;
                        
            $sql = 'SELECT max( weight ) as max_weight FROM civicrm_premiums_product WHERE premiums_id = %1';
            $params = array( 1 => array( $premiumID, 'Integer' ));
            $dao =& CRM_Core_DAO::executeQuery( $sql, $params );
            $dao->fetch();
            $defaults['weight'] = $dao->max_weight + 1 ;
        }
        RETURN $defaults;
    }

    /**
     * Function to actually build the form
     *
     * @return void
     * @access public
     */
    public function buildQuickForm()
    {
        $urlParams = 'civicrm/admin/contribute/premium';
        if ( $this->_action & CRM_Core_Action::DELETE ) {
            $session = CRM_Core_Session::singleton();
            $url = CRM_Utils_System::url( $urlParams, 'reset=1&action=update&id='.$this->_id ); 
            $session->pushUserContext( $url );
            if (CRM_Utils_Request::retrieve('confirmed', 'Boolean',
                                            CRM_Core_DAO::$_nullObject, '', '', 'GET') ) {
                require_once 'CRM/Contribute/DAO/PremiumsProduct.php';
                $dao = new CRM_Contribute_DAO_PremiumsProduct();
                $dao->id = $this->_pid;
                $dao->delete();
                CRM_Core_Session::setStatus( ts('Selected Premium Product has been removed from this Contribution Page.') );
                CRM_Utils_System::redirect($url);
            } 
          

            $this->addButtons(array(
                                    array ( 'type'      => 'next',
                                            'name'      => ts('Delete'),
                                            'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;',
                                            'isDefault' => true   ),
                                    array ( 'type'      => 'cancel',
                                            'name'      => ts('Cancel') ),
                                    )
                              );
            return;
            
        }
        
        if ( $this->_action & CRM_Core_Action::PREVIEW ) {
            require_once 'CRM/Contribute/BAO/Premium.php';
            CRM_Contribute_BAO_Premium::buildPremiumPreviewBlock( $this, null ,$this->_pid );
            $this->addButtons(array(
                                    array ('type'      => 'next',
                                           'name'      => ts('Done with Preview'),
                                           'isDefault' => true),
                                    )
                              );
            return;
        }
        
        $session = CRM_Core_Session::singleton();
        $url = CRM_Utils_System::url( $urlParams, 'reset=1&action=update&id='.$this->_id ); 
        $session->pushUserContext( $url );

        $this->add('select', 'product_id', ts('Select the Product') . ' ', $this->_products ,true);
     
        $this->addElement('text','weight', ts('Weight'),CRM_Core_DAO::getAttribute('CRM_Contribute_DAO_PremiumsProduct', 'weight') );
             
        $this->addRule('weight',ts('Please enter integer value for weight') , 'integer');
        $session = CRM_Core_Session::singleton();
        $single = $session->get('singleForm');
        $session->pushUserContext( CRM_Utils_System::url( $urlParams, 'action=update&reset=1&id=' . $this->_id ) );
      
        if ( $single ) {
            $this->addButtons(array(
                                    array ( 'type'      => 'next',
                                            'name'      => ts('Save'),
                                            'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;',
                                            'isDefault' => true   ),
                                    array ( 'type'      => 'cancel',
                                            'name'      => ts('Cancel') ),
                                    )
                              );
        } else {
            parent::buildQuickForm( );
        }
    }

    /**
     * Process the form
     *
     * @return void
     * @access public
     */
    public function postProcess()
    {
        // get the submitted form values.
        $params    = $this->controller->exportValues( $this->_name );
        $pageID    = CRM_Utils_Request::retrieve('id', 'Positive',
                                                 $this, false, 0);
        $urlParams = 'civicrm/admin/contribute/premium';
        if($this->_action & CRM_Core_Action::PREVIEW) {
            $session = CRM_Core_Session::singleton();
            $url = CRM_Utils_System::url( $urlParams, 'reset=1&action=update&id='.$this->_id );
            $single = $session->get('singleForm');
            CRM_Utils_System::redirect($url);
            return;
        }
        
        if($this->_action & CRM_Core_Action::DELETE) {
            $session = CRM_Core_Session::singleton();
            $url = CRM_Utils_System::url( $urlParams, 'reset=1&action=update&id='.$this->_id );
            require_once 'CRM/Contribute/DAO/PremiumsProduct.php';
            $dao = new CRM_Contribute_DAO_PremiumsProduct();
            $dao->id = $this->_pid;
            $dao->delete();
            CRM_Core_Session::setStatus( ts('Selected Premium Product has been removed from this Contribution Page.') );
            CRM_Utils_System::redirect($url);
        } else { 
            $session = CRM_Core_Session::singleton();
            $url = CRM_Utils_System::url( $urlParams, 'reset=1&action=update&id='.$this->_id );
            if ( $this->_pid ) {
                $params['id'] =  $this->_pid;
            }
            require_once 'CRM/Contribute/DAO/Premium.php';
            $dao = new CRM_Contribute_DAO_Premium();
            $dao->entity_table = 'civicrm_contribution_page';
            $dao->entity_id = $pageID; 
            $dao->find(true);
            $premiumID = $dao->id;
            $params['premiums_id'] = $premiumID;
            

            require_once 'CRM/Contribute/DAO/PremiumsProduct.php';
            $dao = new CRM_Contribute_DAO_PremiumsProduct();
            $dao->copyValues($params);
            $dao->save();
            CRM_Utils_System::redirect($url);
        }
    }
    
    /** 
     * Return a descriptive name for the page, used in wizard header 
     * 
     * @return string 
     * @access public 
     */ 
    public function getTitle( ) 
    {
        return ts( 'Add Premium to Contribution Page' );
    }
}

