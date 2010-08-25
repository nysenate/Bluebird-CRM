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

require_once 'CRM/Mailing/Selector/Browse.php';
require_once 'CRM/Core/Selector/Controller.php';
require_once 'CRM/Core/Page.php';

/**
 * This implements the profile page for all contacts. It uses a selector
 * object to do the actual dispay. The fields displayd are controlled by
 * the admin
 */
class CRM_Mailing_Page_Browse extends CRM_Core_Page {

    /**
     * all the fields that are listings related
     *
     * @var array
     * @access protected
     */
    protected $_fields;

    /**
     * the mailing id of the mailing we're operating on
     *
     * @int
     * @access protected
     */
    protected $_mailingId;

    /**
     * the action that we are performing (in CRM_Core_Action terms)
     *
     * @int
     * @access protected
     */
    protected $_action;

    public $_sortByCharacter;

    public $_unscheduled;
    public $_archived;
    
    /**
     * scheduled mailing
     *
     * @boolean
     * @access public
     */
    public $_scheduled;

    /**
     * Heart of the viewing process. The runner gets all the meta data for
     * the contact and calls the appropriate type of page to view.
     *
     * @return void
     * @access public
     *
     */
    function preProcess() 
    {
        $this->_unscheduled = $this->_archived = false;
        $this->_mailingId = CRM_Utils_Request::retrieve('mid', 'Positive', $this);

        // check that the user has permission to access mailing id
        require_once 'CRM/Mailing/BAO/Mailing.php';
        CRM_Mailing_BAO_Mailing::checkPermission( $this->_mailingId );

        $this->_action    = CRM_Utils_Request::retrieve('action', 'String', $this);
        $this->assign('action', $this->_action);
    }

    /** 
     * run this page (figure out the action needed and perform it). 
     * 
     * @return void 
     */ 
    function run($newArgs) {

        $this->preProcess();
        if ( isset( $_GET['runJobs'] ) || CRM_Utils_Array::value( '2', $newArgs ) == 'queue' ) {
            require_once 'CRM/Mailing/BAO/Job.php';
            CRM_Mailing_BAO_Job::runJobs();
        }

        $this->_sortByCharacter = CRM_Utils_Request::retrieve( 'sortByCharacter',
                                                               'String',
                                                               $this );


        if ( $this->_sortByCharacter == 1 ||
             ! empty( $_POST ) ) {
            $this->_sortByCharacter = '';
            $this->set( 'sortByCharacter', '' );
        }

        if ( CRM_Utils_Array::value( 3,  $newArgs ) == 'unscheduled' ) {
            $this->_unscheduled = true;
        }
        $this->set( 'unscheduled', $this->_unscheduled );
        
        if ( CRM_Utils_Array::value( 3,  $newArgs ) == 'archived' ) {
            $this->_archived = true;
        }
        $this->set( 'archived', $this->_archived );

        if ( CRM_Utils_Array::value( 3,  $newArgs ) == 'scheduled' ) {
            $this->_scheduled = true;
        }
        $this->set( 'scheduled', $this->_scheduled );
        
        $this->_createdId = CRM_Utils_Request::retrieve( 'cid', 'Positive', $this, false, 0 );
        if ( $this->_createdId ) {
            $this->set( 'createdId', $this->_createdId );
        }
        
        $this->search( );
        
        $session = CRM_Core_Session::singleton();
        $context = $session->readUserContext( );
        
        if ($this->_action & CRM_Core_Action::DISABLE) {                 
            if (CRM_Utils_Request::retrieve('confirmed', 'Boolean', $this )) {
                require_once 'CRM/Mailing/BAO/Job.php';
                CRM_Mailing_BAO_Job::cancel($this->_mailingId);
                CRM_Utils_System::redirect( $context );
            } else {
                $controller = new CRM_Core_Controller_Simple( 'CRM_Mailing_Form_Browse',
                                                               ts('Cancel Mailing'),
                                                               $this->_action );
                $controller->setEmbedded( true );
                $controller->run( );
            }
        } else if ($this->_action & CRM_Core_Action::DELETE) {
            if (CRM_Utils_Request::retrieve('confirmed', 'Boolean', $this )) {
                
                // check for action permissions.
                if ( !CRM_Core_Permission::checkActionPermission( 'CiviMail', $this->_action ) ) {
                    CRM_Core_Error::fatal( ts( 'You do not have permission to access this page' ) );
                }
                
                require_once 'CRM/Mailing/BAO/Mailing.php';
                CRM_Mailing_BAO_Mailing::del($this->_mailingId);
                CRM_Utils_System::redirect($context);
            } else {
                $controller = new CRM_Core_Controller_Simple( 'CRM_Mailing_Form_Browse',
                                                               ts('Delete Mailing'),
                                                               $this->_action );
                $controller->setEmbedded( true );
                $controller->run( );
            }
        } else if ( $this->_action & CRM_Core_Action::RENEW ) {
            //archive this mailing, CRM-3752.
            if (CRM_Utils_Request::retrieve('confirmed', 'Boolean', $this )) {
                //set is_archived to 1
                CRM_Core_DAO::setFieldValue( 'CRM_Mailing_DAO_Mailing', $this->_mailingId, 'is_archived', true );
                CRM_Utils_System::redirect($context);
            } else {
                $controller = new CRM_Core_Controller_Simple( 'CRM_Mailing_Form_Browse',
                                                               ts('Archive Mailing'),
                                                               $this->_action );
                $controller->setEmbedded( true );
                $controller->run( );
            }
        }
     
        $selector = new CRM_Mailing_Selector_Browse( );
        $selector->setParent( $this );
        
        $controller = new CRM_Core_Selector_Controller(
                                                        $selector ,
                                                        $this->get( CRM_Utils_Pager::PAGE_ID ),
                                                        $this->get( CRM_Utils_Sort::SORT_ID ).$this->get(CRM_Utils_Sort::SORT_DIRECTION),
                                                        CRM_Core_Action::VIEW, 
                                                        $this, 
                                                        CRM_Core_Selector_Controller::TEMPLATE );
        
        $controller->setEmbedded( true );
        $controller->run( );
        
        //hack to display results as per search
        $rows = $controller->getRows($controller);
        $this->assign('rows', $rows );
        
        $urlParams = 'reset=1';
        $urlString = 'civicrm/mailing/browse';
        if ( CRM_Utils_Array::value( 3,  $newArgs ) == 'unscheduled' ) {
            $urlString .= '/unscheduled';
            $urlParams .= '&scheduled=false';
            $this->assign('unscheduled', true );
            CRM_Utils_System::setTitle(ts('Draft and Unscheduled Mailings'));
        } else if ( CRM_Utils_Array::value( 3,  $newArgs ) == 'archived' ) {
            $urlString .= '/archived';
            $this->assign('archived', true );
            CRM_Utils_System::setTitle(ts('Archived Mailings'));
        } else if ( CRM_Utils_Array::value( 3,  $newArgs)  == 'scheduled' ) {
            $urlString .= '/scheduled';
            $urlParams .= '&scheduled=true';
            CRM_Utils_System::setTitle(ts('Scheduled and Sent Mailings'));
        } else {
            CRM_Utils_System::setTitle(ts('Find Mailings'));
        }
        
        $crmRowCount = CRM_Utils_Request::retrieve( 'crmRowCount', 'Integer', CRM_Core_DAO::$_nullObject );
        $crmPID      = CRM_Utils_Request::retrieve( 'crmPID', 'Integer', CRM_Core_DAO::$_nullObject );
        if ( $crmRowCount || $crmPID ) {
            $urlParams .= '&force=1';
            $urlParams .= $crmRowCount ? '&crmRowCount=' . $crmRowCount : ''; 
            $urlParams .= $crmPID ? '&crmPID=' . $crmPID : ''; 
        }
        
        $crmSID      = CRM_Utils_Request::retrieve( 'crmSID', 'Integer', CRM_Core_DAO::$_nullObject );
        if ( $crmSID ) {
            $urlParams .= '&crmSID=' . $crmSID;
        } 

        $session = CRM_Core_Session::singleton( );
        $url = CRM_Utils_System::url( $urlString, $urlParams );
        $session->pushUserContext( $url );
        
        return parent::run( );
    }

    function search( ) {
        if ( $this->_action &
             ( CRM_Core_Action::ADD    |
               CRM_Core_Action::UPDATE ) ) {
            return;
        }

        $form = new CRM_Core_Controller_Simple( 'CRM_Mailing_Form_Search', ts( 'Search Mailings' ), CRM_Core_Action::ADD );
        $form->setEmbedded( true );
        $form->setParent( $this );
        $form->process( );
        $form->run( );
    }

    function whereClause( &$params, $sortBy = true ) {
        $values =  array( );

        $clauses = array( );
        $title   = $this->get( 'mailing_name' );
        //echo " name=$title  ";
        if ( $title ) {
            $clauses[] = 'name LIKE %1';
            if ( strpos( $title, '%' ) !== false ) {
                $params[1] = array( $title, 'String', false );
            } else {
                $params[1] = array( $title, 'String', true );
            }
        }

        if ( $sortBy &&
             $this->_sortByCharacter ) {
            $clauses[] = 'name LIKE %2';
            $params[2] = array( $this->_sortByCharacter . '%', 'String' );
        }

        return implode( ' AND ', $clauses );
    }

}


