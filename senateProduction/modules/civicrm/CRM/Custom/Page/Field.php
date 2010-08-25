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
 * Create a page for displaying Custom Fields.
 *
 * Heart of this class is the run method which checks
 * for action type and then displays the appropriate
 * page.
 *
 */
class CRM_Custom_Page_Field extends CRM_Core_Page 
{
    
    /**
     * The group id of the field
     *
     * @var int
     * @access protected
     */
    protected $_gid;

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
            $deleteExtra = ts('Are you sure you want to delete this custom data field?');
            self::$_actionLinks = array(
                                        CRM_Core_Action::UPDATE  => array(
                                                                          'name'  => ts('Edit Field'),
                                                                          'url'   => 'civicrm/admin/custom/group/field',
                                                                          'qs'    => 'action=update&reset=1&gid=%%gid%%&id=%%id%%',
                                                                          'title' => ts('Edit Custom Field') 
                                                                          ),
                                        CRM_Core_Action::BROWSE  => array(
                                                                          'name'  => ts('Edit Multiple Choice Options'),
                                                                          'url'   => 'civicrm/admin/custom/group/field/option',
                                                                          'qs'    => 'reset=1&action=browse&gid=%%gid%%&fid=%%id%%',
                                                                          'title' => ts('List Custom Options'),
                                                                          ),
                                        CRM_Core_Action::PREVIEW => array(
                                                                          'name'  => ts('Preview Field Display'),
                                                                          'url'   => 'civicrm/admin/custom/group/field',
                                                                          'qs'    => 'action=preview&reset=1&gid=%%gid%%&id=%%id%%',
                                                                          'title' => ts('Preview Custom Field'),
                                                                          ),
                                        CRM_Core_Action::DISABLE => array(
                                                                          'name'  => ts('Disable'),
                                                                          'extra' => 'onclick = "enableDisable( %%id%%,\''. 'CRM_Core_BAO_CustomField' . '\',\'' . 'enable-disable' . '\' );"',
                                                                          'ref'   => 'disable-action',
                                                                          'title' => ts('Disable Custom Field'),
                                                                          
                                                                          ),
                                        CRM_Core_Action::ENABLE  => array(
                                                                          'name'  => ts('Enable'),
                                                                          'extra' => 'onclick = "enableDisable( %%id%%,\''. 'CRM_Core_BAO_CustomField' . '\',\'' . 'disable-enable' . '\' );"',
                                                                          'ref'   => 'enable-action',
                                                                          'title' => ts('Enable Custom Field'),
                                                                          ),
                                        CRM_Core_Action::DELETE  => array(
                                                                          'name'  => ts('Delete'),
                                                                          'url'   => 'civicrm/admin/custom/group/field',
                                                                          'qs'    => 'action=delete&reset=1&gid=%%gid%%&id=%%id%%',
                                                                          'title' => ts('Delete Custom Field'),
                                                                          'extra' => 'onclick = "return confirm(\'' . $deleteExtra . '\');"',
                                                                          ),
                                        );
        }
        return self::$_actionLinks;
    }

    /**
     * Browse all custom group fields.
     * 
     * @param null
     * 
     * @return void
     * @access public
     */
    function browse()
    {
        require_once 'CRM/Core/BAO/CustomField.php';
        $customField = array();
        $customFieldBAO = new CRM_Core_BAO_CustomField();
        
        // fkey is gid
        $customFieldBAO->custom_group_id = $this->_gid;
        $customFieldBAO->orderBy('weight, label');
        $customFieldBAO->find();
       
        while ($customFieldBAO->fetch()) {
            $customField[$customFieldBAO->id] = array();
            CRM_Core_DAO::storeValues( $customFieldBAO, $customField[$customFieldBAO->id]);
            $action = array_sum(array_keys($this->actionLinks()));
            if ($customFieldBAO->is_active) {
                $action -= CRM_Core_Action::ENABLE;
            } else {
                $action -= CRM_Core_Action::DISABLE;
            }

            switch($customFieldBAO->data_type) {
                
            case "String":
            case "Int":
            case "Float":
            case "Money":
                // if Multi Select field is selected in custom field
                if ( $customFieldBAO->html_type == 'Text') {
                    $action -= CRM_Core_Action::BROWSE;
                } 
                break;
            case "ContactReference":    
            case "Memo":
            case "Date":
            case "Boolean":
            case "StateProvince":
            case "Country":
            case "File":
            case "Link":
                $action -= CRM_Core_Action::BROWSE;
                break;
            }
            
            $customFieldDataType = CRM_Core_BAO_CustomField::dataType();
            $customField[$customFieldBAO->id]['data_type'] =
                $customFieldDataType[$customField[$customFieldBAO->id]['data_type']];
            $customField[$customFieldBAO->id]['order']  = $customField[$customFieldBAO->id]['weight'];
            $customField[$customFieldBAO->id]['action'] = CRM_Core_Action::formLink(self::actionLinks(), $action, 
                                                                                    array('id'  => $customFieldBAO->id,
                                                                                          'gid' => $this->_gid ));
        }

        $returnURL = CRM_Utils_System::url( 'civicrm/admin/custom/group/field', "reset=1&action=browse&gid={$this->_gid}" );
        $filter    = "custom_group_id = {$this->_gid}";
        require_once 'CRM/Utils/Weight.php';
        CRM_Utils_Weight::addOrder( $customField, 'CRM_Core_DAO_CustomField',
                                    'id', $returnURL, $filter );
        
        $this->assign('customField', $customField);
    }


    /**
     * edit custom data.
     *
     * editing would involved modifying existing fields + adding data to new fields.
     *
     * @param string  $action    the action to be invoked
     * @return void
     * @access public
     */
    function edit($action)
    {
        // create a simple controller for editing custom dataCRM/Custom/Page/Field.php
        $controller = new CRM_Core_Controller_Simple('CRM_Custom_Form_Field', ts('Custom Field'), $action);

        // set the userContext stack
        $session = CRM_Core_Session::singleton();
        $session->pushUserContext(CRM_Utils_System::url('civicrm/admin/custom/group/field', 'reset=1&action=browse&gid=' . $this->_gid));
       
        $controller->set('gid', $this->_gid);
        $controller->setEmbedded(true);
        $controller->process();
        $controller->run();

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
    function run()
    {
        require_once 'CRM/Core/BAO/CustomGroup.php';
       
        // get the group id
        $this->_gid = CRM_Utils_Request::retrieve('gid', 'Positive',
                                                  $this);
        $action = CRM_Utils_Request::retrieve('action', 'String',
                                              $this, false, 'browse'); // default to 'browse'
       
        if ($action & CRM_Core_Action::DELETE) {
            
            $session = & CRM_Core_Session::singleton();
            $session->pushUserContext(CRM_Utils_System::url('civicrm/admin/custom/group/field', 'reset=1&action=browse&gid=' . $this->_gid));
            $controller = new CRM_Core_Controller_Simple( 'CRM_Custom_Form_DeleteField',"Delete Custom Field", '' );
            $id = CRM_Utils_Request::retrieve('id', 'Positive',
                                              $this, false, 0);
            $controller->set('id', $id);
            $controller->setEmbedded( true );
            $controller->process( );
            $controller->run( );
            $fieldValues = array('custom_group_id' => $this->_gid);
            $wt = CRM_Utils_Weight::delWeight('CRM_Core_DAO_CustomField', $id, $fieldValues);
        }

        if ($this->_gid) {
            $groupTitle = CRM_Core_BAO_CustomGroup::getTitle($this->_gid);
            $this->assign('gid', $this->_gid);
            $this->assign('groupTitle', $groupTitle);
            CRM_Utils_System::setTitle(ts('%1 - Custom Fields', array(1 => $groupTitle)));
        }

        // assign vars to templates
        $this->assign('action', $action);

        $id = CRM_Utils_Request::retrieve('id', 'Positive',
                                          $this, false, 0);
        
        // what action to take ?
        if ($action & (CRM_Core_Action::UPDATE | CRM_Core_Action::ADD)) {
            $this->edit($action);   // no browse for edit/update/view
        } else if ($action & CRM_Core_Action::PREVIEW) {
            $this->preview($id) ;
        } else {
            require_once 'CRM/Core/BAO/CustomField.php';
            require_once 'CRM/Core/BAO/UFField.php';
            $this->browse();
        }

        // Call the parents run method
        parent::run();
    }

    /**
     * Preview custom field
     *
     * @param int  $id    custom field id
     * 
     * @return void
     * @access public
     */
    function preview($id)
    {
        $controller = new CRM_Core_Controller_Simple('CRM_Custom_Form_Preview', ts('Preview Custom Data'), CRM_Core_Action::PREVIEW);
        $session = CRM_Core_Session::singleton();
        $session->pushUserContext(CRM_Utils_System::url('civicrm/admin/custom/group/field', 'reset=1&action=browse&gid=' . $this->_gid));
        $controller->set('fieldId', $id);
        $controller->set('groupId', $this->_gid);
        $controller->setEmbedded(true);
        $controller->process();
        $controller->run();
    }
}

