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

class CRM_Custom_Form_DeleteFile extends CRM_Core_Form {

    /**
     * the file id
     *
     * @var int
     */
    protected $_id;

    /**
     * the entity id 
     *
     * @var array
     */
    protected $_eid;

    function preProcess( ) {
        require_once 'CRM/Utils/Request.php';
        $this->_id  = CRM_Utils_Request::retrieve( 'id' , 'Positive', $this, true );
        $this->_eid = CRM_Utils_Request::retrieve( 'eid', 'Positive', $this, true );
    }

    /**
     * Function to actually build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) {
        
        $this->addButtons( array(
                                 array ( 'type'      => 'next',
                                         'name'      => ts('Delete'),
                                         'subName'   => 'view',
                                         'isDefault' => true   ),
                                 array ( 'type'       => 'cancel',
                                         'name'      => ts('Cancel') ) ) );
    }
    
    /**
     * Process the form when submitted
     *
     * @return void
     * @access public
     */
    public function postProcess( ) {
        require_once 'CRM/Core/BAO/File.php';
        CRM_Core_BAO_File::delete($this->_id, $this->_eid);
        CRM_Core_Session::setStatus( ts('The attached file has been deleted.') );
        
        $session = CRM_Core_Session::singleton();   
        $toUrl   = $session->popUserContext();
        CRM_Utils_System::redirect($toUrl);
    }
}

