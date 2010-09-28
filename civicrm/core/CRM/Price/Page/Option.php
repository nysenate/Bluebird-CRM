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

require_once 'CRM/Core/Page.php';

/**
 * Create a page for displaying Custom Options.
 *
 * Heart of this class is the run method which checks
 * for action type and then displays the appropriate
 * page.
 *
 */
class CRM_Price_Page_Option extends CRM_Core_Page 
{
    /**
     * The field id of the option
     *
     * @var int
     * @access protected
     */
    protected $_fid;
    
    /**
     * The field id of the option
     *
     * @var int
     * @access protected
     */
    protected $_sid;

    /**
     * The action links that we need to display for the browse screen
     *
     * @var array
     * @access private
     */
    private static $_actionLinks;


    /**
     * Get the action links for this page.
     * 
     * @param null
     * 
     * @return array  array of action links that we need to display for the browse screen
     * @access public
     */
    function &actionLinks()
    {
        if (!isset(self::$_actionLinks)) {
            self::$_actionLinks = array(
                                        CRM_Core_Action::UPDATE  => array(
                                                                          'name'  => ts('Edit Option'),
                                                                          'url'   => 'civicrm/admin/price/field/option',
                                                                          'qs'    => 'reset=1&action=update&oid=%%oid%%&fid=%%fid%%&sid=%%sid%%',
                                                                          'title' => ts('Edit Price Option') 
                                                                          ),
                                        CRM_Core_Action::VIEW    => array(
                                                                          'name'  => ts('View'),
                                                                          'url'   => 'civicrm/admin/price/field/option',
                                                                          'qs'    => 'action=view&oid=%%oid%%',
                                                                          'title' => ts('View Price Option'),
                                                                          ),
                                        CRM_Core_Action::DISABLE => array(
                                                                          'name'  => ts('Disable'),
                                                                          'extra' => 'onclick = "enableDisable( %%oid%%,\''. 'CRM_Core_BAO_OptionValue' . '\',\'' . 'enable-disable' . '\' );"',
                                                                          'ref'   => 'disable-action',
                                                                          'title' => ts('Disable Price Option') 
                                                                          ),
                                        CRM_Core_Action::ENABLE  => array(
                                                                          'name'  => ts('Enable'),
                                                                          'extra' => 'onclick = "enableDisable( %%oid%%,\''. 'CRM_Core_BAO_OptionValue' . '\',\'' . 'disable-enable' . '\' );"',
                                                                          'ref'   => 'enable-action',
                                                                          'title' => ts('Enable Price Option') 
                                                                          ),
                                        CRM_Core_Action::DELETE  => array(
                                                                           'name'  => ts('Delete'),
                                                                           'url'   => 'civicrm/admin/price/field/option',
                                                                           'qs'    => 'action=delete&oid=%%oid%%',
                                                                           'title' => ts('Disable Price Option'),
                                                                          ),
                                        );
        }
        return self::$_actionLinks;
    }

    /**
     * Browse all price fields.
     * 
     * @param null
     * 
     * @return void
     * @access public
     */
    function browse( )
    {
        $customOption = array( );
        
        $groupParams  = array( 'name' => "civicrm_price_field.amount.{$this->_fid}" );
        
        require_once 'CRM/Core/OptionValue.php';
        CRM_Core_OptionValue::getValues( $groupParams, $customOption );
        $config = CRM_Core_Config::singleton( );
        foreach ( $customOption as $id => $values ) {
            $action = array_sum( array_keys( $this->actionLinks( ) ) );
            
            // update enable/disable links depending on price_field properties.
            if ( $values['is_active'] ) {
                $action -= CRM_Core_Action::ENABLE;
            } else {
                $action -= CRM_Core_Action::DISABLE;
            }
            if ( CRM_Utils_Array::value('is_default', $customOption[$id] ) ) {
                $customOption[$id]['is_default'] = '<img src="' . $config->resourceBase . 'i/check.gif" />';
            } else {
                $customOption[$id]['is_default'] = '';
            }
            $customOption[$id]['order']  = $customOption[$id]['weight'];
            $customOption[$id]['action'] = CRM_Core_Action::formLink( self::actionLinks( ), $action, 
                                                                      array( 'oid'  => $id,
                                                                             'fid'  => $this->_fid,
                                                                             'sid' => $this->_sid ) );
        }
        // Add order changing widget to selector
        $returnURL = CRM_Utils_System::url( 'civicrm/admin/price/field/option', "action=browse&reset=1&fid={$this->_fid}&sid={$this->_sid}" );
        $filter    = "option_group_id = (SELECT id FROM civicrm_option_group WHERE name = 'civicrm_price_field.amount.{$this->_fid}')";
        require_once 'CRM/Utils/Weight.php';
        CRM_Utils_Weight::addOrder( $customOption, 'CRM_Core_DAO_OptionValue',
                                    'id', $returnURL, $filter );

        $this->assign('customOption', $customOption);
    }


    /**
     * edit custom Option.
     *
     * editing would involved modifying existing fields + adding data to new fields.
     *
     * @param string  $action   the action to be invoked
     * 
     * @return void
     * @access public
     */
    function edit( $action )
    {
        $oid = CRM_Utils_Request::retrieve('oid', 'Positive',
                                           $this, false, 0);
        $params=array( );
        if ( $oid ) {
            $params['oid'] = $oid; 
            require_once 'CRM/Price/BAO/Set.php';
            $sid = CRM_Price_BAO_Set::getSetId($params);
            
            require_once 'CRM/Price/BAO/Set.php';
            $usedBy  =& CRM_Price_BAO_Set::getUsedBy( $sid );   
        }
        // set the userContext stack
        $session = CRM_Core_Session::singleton( );
        $session->pushUserContext( CRM_Utils_System::url( 'civicrm/admin/price/field/option', 
                                                          "reset=1&action=browse&fid={$this->_fid}&sid={$this->_sid}" ) );
        $controller = new CRM_Core_Controller_Simple( 'CRM_Price_Form_Option', ts('Price Field Option'), $action );
        $controller->set( 'fid', $this->_fid );
        $controller->setEmbedded( true );
        $controller->process( );
        $controller->run( );
        $this->browse( );
               
        if ( $action &  CRM_Core_Action::DELETE ) {
            // add breadcrumb 
            require_once 'CRM/Core/BAO/OptionValue.php';
            $url = CRM_Utils_System::url( 'civicrm/admin/price/field/option', 'reset=1' );
            CRM_Utils_System::appendBreadCrumb( ts('Price Option'),
                                                $url );
            $this->assign( 'usedPriceSetTitle', CRM_Core_BAO_OptionValue::getTitle($oid) );
            $this->assign( 'usedBy', $usedBy );
            $comps = array( "Event"        => "civicrm_event", 
                            "Contribution" => "civicrm_contribution_page" );
            $priceSetContexts = array( );
            foreach ( $comps as $name => $table ) {
                if ( array_key_exists( $table, $usedBy ) ) {
                    $priceSetContexts[] = $name;
                }
            }
            $this->assign( 'contexts', $priceSetContexts );
        }
        
    }
    
    /**
     * Run the page.
     *
     * This method is called after the page is created. It checks for the  
     * type of action and executes that action. 
     * 
     * @param null
     * 
     * @return void
     * @access public
     */
    function run( )
    {
        require_once 'CRM/Price/BAO/Field.php';
       
        // get the field id
        $this->_fid = CRM_Utils_Request::retrieve('fid', 'Positive',
                                                  $this, false, 0);
        //get the price set id
        if ( !$this->_sid ) {
            $this->_sid = CRM_Utils_Request::retrieve('sid', 'Positive', $this );
        }
        
        if ( $this->_sid ) {
            require_once 'CRM/Price/BAO/Set.php';
            CRM_Price_BAO_Set::checkPermission( $this->_sid );
        }
        //as url contain $sid so append breadcrumb dynamically.
        $breadcrumb = array( array( 'title' => ts( 'Price Fields' ),
                                    'url'   => CRM_Utils_System::url( 'civicrm/admin/price/field', 'reset=1&sid=' . $this->_sid ) ) );
        CRM_Utils_System::appendBreadCrumb( $breadcrumb );
        
        if ( $this->_fid ) {
            $fieldTitle = CRM_Price_BAO_Field::getTitle( $this->_fid );
            $this->assign( 'fid', $this->_fid );
            $this->assign( 'fieldTitle', $fieldTitle );
            CRM_Utils_System::setTitle(ts( '%1 - Price Options', array( 1 => $fieldTitle ) ) );

            $htmlType = CRM_Core_DAO::getFieldValue( 'CRM_Price_BAO_Field', $this->_fid, 'html_type' );
            $this->assign( 'addMoreFields', true );
            //for text price field only single option present
            if ( $htmlType == 'Text' ) {
                $this->assign( 'addMoreFields', false );
            }
        }
        
        // get the requested action
        $action = CRM_Utils_Request::retrieve( 'action', 'String',
                                               $this, false, 'browse' ); // default to 'browse'
        
        // assign vars to templates
        $this->assign( 'action', $action );
        
        $oid = CRM_Utils_Request::retrieve( 'oid', 'Positive',
                                            $this, false, 0 );
        // what action to take ?
        if ( $action & ( CRM_Core_Action::UPDATE | CRM_Core_Action::ADD | 
                         CRM_Core_Action::VIEW   | CRM_Core_Action::DELETE ) ) {
            $this->edit( $action );   // no browse for edit/update/view
        } else {
            require_once 'CRM/Core/BAO/OptionValue.php';
            $this->browse();
        }
        // Call the parents run method
        parent::run();
    }
}

