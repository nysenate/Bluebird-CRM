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

/**
 * This class generates form components for Message templates
 * used by memberhsip email and send email
 * 
 */
class CRM_Admin_Form_MessageTemplates extends CRM_Admin_Form
{
    // which (and whether) mailing workflow this template belongs to
    protected $_workflow_id = null;

    /**
     * This function sets the default values for the form. 
     * the default values are retrieved from the database
     * 
     * @access public
     * @return None
     */
    public function setDefaultValues( ) {
        $defaults = array( );
        $defaults =& parent::setDefaultValues( );
        $this->_workflow_id = CRM_Utils_Array::value( 'workflow_id', $defaults );
        $this->assign( 'workflow_id', $this->_workflow_id );

        // FIXME: we need to fix the Cancel button here as we don’t know whether it’s a workflow template in buildQuickForm()
        if ($this->_workflow_id and $this->_action & CRM_Core_Action::UPDATE) {
            $cancelURL = CRM_Utils_System::url('civicrm/admin/messageTemplates', 'selectedChild=workflow&reset=1');
            $cancelURL = str_replace('&amp;', '&', $cancelURL);
            $this->addButtons(
                array(
                    array(
                        'type'      => 'next',
                        'name'      => ts('Save'),
                        'isDefault' => true,
                    ),
                    array(
                        'type'      => 'cancel',
                        'name'      => ts('Cancel'),
                        'js'        => array('onclick' => "location.href='{$cancelURL}'; return false;"),
                    ),
                )
            );
        }

        return $defaults;
    }

    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
    {

        // For VIEW we only want Done button
        if ($this->_action & CRM_Core_Action::VIEW ) { 
            // currently, the above action is used solely for previewing default workflow templates
            $cancelURL = CRM_Utils_System::url('civicrm/admin/messageTemplates', 'selectedChild=workflow&reset=1');
            $cancelURL = str_replace('&amp;', '&', $cancelURL);
            $this->addButtons( array(
                                 array ( 'type'      => 'cancel',
                                         'name'      => ts('Done'),
                                         'js'        => array('onclick' => "location.href='{$cancelURL}'; return false;"),
                                         'isDefault' => true   ),
                                        )
                                 );
        } else {
            parent::buildQuickForm( );        
        }
        
        if ($this->_action & CRM_Core_Action::DELETE ) { 
            return;
        }
        
        require_once 'CRM/Utils/System.php';
        $breadCrumb = array( array('title' => ts('Message Templates'), 
                                   'url'   => CRM_Utils_System::url( 'civicrm/admin/messageTemplates', 
                                                                     'action=browse&reset=1' )) );
        CRM_Utils_System::appendBreadCrumb( $breadCrumb );

        $this->applyFilter('__ALL__', 'trim');
        $this->add('text', 'msg_title', ts('Message Title'), CRM_Core_DAO::getAttribute( 'CRM_Core_DAO_MessageTemplates', 'msg_title' ),true );
        
        $this->add('text', 'msg_subject',
                   ts('Message Subject'), 
                   CRM_Core_DAO::getAttribute( 'CRM_Core_DAO_MessageTemplates', 'msg_subject' ) );

        //get the tokens.
        $tokens = CRM_Core_SelectValues::contactTokens( );
                
        //sorted in ascending order tokens by ignoring word case
        natcasesort($tokens);
        $this->assign('tokens', json_encode( $tokens ) );
        
        $this->add( 'select', 'token1',  ts( 'Insert Tokens' ), 
                    $tokens , false, 
                    array(
                          'size'     => "5",
                          'multiple' => true,
                          'onchange' => "return tokenReplText(this);"
                          )
                    );
        
        $this->add( 'select', 'token2',  ts( 'Insert Tokens' ), 
                    $tokens , false,
                    array(
                          'size'     => "5",
                          'multiple' => true,
                          'onchange' => "return tokenReplHtml(this);"
                          )
                    );

        $this->add( 'select', 'token3',  ts( 'Insert Tokens' ), 
                    $tokens , false, 
                    array(
                          'size'     => "5",
                          'multiple' => true,
                          'onchange' => "return tokenReplText(this);"
                          )
                    );
        
        $this->add('textarea', 'msg_text', ts('Text Message'), 
                   "cols=50 rows=6" );

        // if not a system message use a wysiwyg editor, CRM-5971
        if ( $this->_id &&
             CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_MessageTemplates',
                                          $this->_id,
                                          'workflow_id' ) ) {
            $this->add('textarea', 'msg_html', ts('HTML Message'),
                       "cols=50 rows=6" );
        } else {
            $this->addWysiwyg( 'msg_html', ts('HTML Message'),
                               array('cols' => '80', 'rows' => '8',
                                     'onkeyup' =>"return verify(this)" ) );
        }

     
        $this->add('checkbox', 'is_active', ts('Enabled?'));

        if ($this->_action & CRM_Core_Action::VIEW) {
            $this->freeze();
            CRM_Utils_System::setTitle(ts('View System Default Message Template'));
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
        require_once 'CRM/Core/BAO/MessageTemplates.php';
        if ( $this->_action & CRM_Core_Action::DELETE ) {
            CRM_Core_BAO_MessageTemplates::del( $this->_id );
        } elseif ($this->_action & CRM_Core_Action::VIEW) {
            // currently, the above action is used solely for previewing default workflow templates
            CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/admin/messageTemplates', 'selectedChild=workflow&reset=1'));
        } else { 
            $params = array( );
            
            // store the submitted values in an array
            $params = $this->exportValues();

            if ( $this->_action & CRM_Core_Action::UPDATE ) {
                $params['id'] = $this->_id;
            }

            if ($this->_workflow_id) {
                $params['workflow_id'] = $this->_workflow_id;
                $params['is_active']   = true;
            }
            
            $messageTemplate = CRM_Core_BAO_MessageTemplates::add( $params );
            CRM_Core_Session::setStatus( ts('The Message Template \'%1\' has been saved.', array( 1 => $messageTemplate->msg_title ) ) );

            if ($this->_workflow_id) {
                CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/admin/messageTemplates', 'selectedChild=workflow&reset=1'));
            }
        }
    }
}


