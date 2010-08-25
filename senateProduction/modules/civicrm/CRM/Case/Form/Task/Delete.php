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

require_once 'CRM/Case/Form/Task.php';

/**
 * This class provides the functionality to delete a group of
 * participations. This class provides functionality for the actual
 * deletion.
 */
class CRM_Case_Form_Task_Delete extends CRM_Case_Form_Task 
{
    /**
     * Are we operating in "single mode", i.e. deleting one
     * specific case?
     *
     * @var boolean
     */
    protected $_single = false;
    /**
     * Are we moving case to Trash
     * 
     * @var boolean
     */
    public $_moveToTrash = true;

    /**
     * build all the data structures needed to build the form
     *
     * @return void
     * @access public
     */
    function preProcess( ) 
    {
        //check for delete
        if ( !CRM_Core_Permission::checkActionPermission( 'CiviCase', CRM_Core_Action::DELETE ) ) {
            CRM_Core_Error::fatal( ts( 'You do not have permission to access this page' ) );  
        }
        parent::preProcess( );
    }

    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    function buildQuickForm( ) 
    {
        $this->addDefaultButtons( ts( 'Delete Cases' ), 'done' );
    }

    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return None
     */
    public function postProcess( ) 
    {
        $deletedCases = 0;
        require_once 'CRM/Case/BAO/Case.php';
        foreach ( $this->_caseIds as $caseId ) {
            if ( CRM_Case_BAO_Case::deleteCase( $caseId, $this->_moveToTrash ) ) {
                $deletedCases++;
            }
        }

        $status = array(
                        ts( 'Deleted Case(s): %1',          array( 1 => $deletedCases ) ),
                        ts( 'Total Selected Case(s): %1',   array( 1 => count($this->_caseIds ) ) ),
                        );
        CRM_Core_Session::setStatus( $status );

    }
}


