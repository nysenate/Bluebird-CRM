<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
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
require_once 'CRM/Contribute/DAO/ContributionPage.php';

/**
 * Create a page for displaying Contribute Pages
 * Contribute Pages are pages that are used to display
 * contributions of different types. Pages consist
 * of many customizable sections which can be
 * accessed.
 *
 * This page provides a top level browse view
 * of all the contribution pages in the system.
 *
 */
class CRM_Contribute_Page_ContributionPage extends CRM_Core_Page 
{
    /**
     * The action links that we need to display for the browse screen
     *
     * @var array
     */
    private static $_actionLinks;

    private static $_links = null;

    protected $_pager = null;

    protected $_sortByCharacter;

    /**
     * Get the action links for this page.
     *
     * @return array $_actionLinks
     *
     */
    function &actionLinks()
    {
        // check if variable _actionsLinks is populated
        if (!isset(self::$_actionLinks)) {
            // helper variable for nicer formatting
            $deleteExtra = ts('Are you sure you want to delete this Contribution page?');
            $copyExtra = ts('Are you sure you want to make a copy of this Contribution page?');
            
            self::$_actionLinks = array(
                                        CRM_Core_Action::UPDATE  => array(
                                                                          'name'  => ts('Configure'),
                                                                          'url'   => CRM_Utils_System::currentPath( ),
                                                                          'qs'    => 'reset=1&action=update&id=%%id%%',
                                                                          'title' => ts('Configure') 
                                                                          ),
                                        CRM_Core_Action::PREVIEW => array(
                                                                          'name'  => ts('Test-drive'),
                                                                          'url'   => 'civicrm/contribute/transact',
                                                                          'qs'    => 'reset=1&action=preview&id=%%id%%',
                                                                          'title' => ts('Preview'),
                                                                          ),
                                        CRM_Core_Action::FOLLOWUP    => array(
                                                                          'name'  => ts('Live Page'),
                                                                          'url'   => 'civicrm/contribute/transact',
                                                                          'qs'    => 'reset=1&id=%%id%%',
                                                                          'title' => ts('FollowUp'),
                                                                          'fe'    =>'true',
                                                                          ),
                                        CRM_Core_Action::DISABLE => array(
                                                                          'name'  => ts('Disable'),
                                                                          'title' => ts('Disable'),
                                                                          'extra' => 'onclick = "enableDisable( %%id%%,\''. 'CRM_Contribute_BAO_ContributionPage' . '\',\'' . 'enable-disable' . '\' );"',
                                                                          'ref'   => 'disable-action'
                                                                          ),
                                        CRM_Core_Action::ENABLE  => array(
                                                                          'name'  => ts('Enable'),
                                                                          'extra' => 'onclick = "enableDisable( %%id%%,\''. 'CRM_Contribute_BAO_ContributionPage' . '\',\'' . 'disable-enable' . '\' );"',
                                                                          'ref'   => 'enable-action',
                                                                          'title' => ts('Enable'),
                                                                          ),
                                        CRM_Core_Action::DELETE  => array(
                                                                          'name'  => ts('Delete'),
                                                                          'url'   => CRM_Utils_System::currentPath( ),
                                                                          'qs'    => 'action=delete&reset=1&id=%%id%%',
                                                                          'title' => ts('Delete Custom Field'),
                                                                          'extra' => 'onclick = "return confirm(\'' . $deleteExtra . '\');"',
                                                                          ),
                                        CRM_Core_Action::COPY     => array(
                                                                           'name'  => ts('Copy Contribution Page'),
                                                                           'url'   => CRM_Utils_System::currentPath( ),                                                                                                'qs'    => 'action=copy&gid=%%id%%',
                                                                           'title' => ts('Make a Copy of CiviCRM Contribution Page'),
                                                                           'extra' => 'onclick = "return confirm(\'' . $copyExtra . '\');"',
                                                                           ),
                                        );
        }
        return self::$_actionLinks;
    }

    /**
     * Run the page.
     *
     * This method is called after the page is created. It checks for the  
     * type of action and executes that action.
     * Finally it calls the parent's run method.
     *
     * @return void
     * @access public
     *
     */
    function run()
    {

        // get the requested action
        $action = CRM_Utils_Request::retrieve('action', 'String',
                                              $this, false, 'browse'); // default to 'browse'

        // assign vars to templates
        $this->assign('action', $action);
        $id = CRM_Utils_Request::retrieve('id', 'Positive',
                                          $this, false, 0);

        // set breadcrumb to append to 2nd layer pages
        $breadCrumb = array( array('title' => ts('Manage Contribution Pages'),
                                   'url'   => CRM_Utils_System::url( CRM_Utils_System::currentPath( ), 
                                                                     'reset=1' )) );
       
        // what action to take ?
        if ( $action & CRM_Core_Action::ADD ) {
            $session = CRM_Core_Session::singleton( ); 
            $session->pushUserContext( CRM_Utils_System::url( CRM_Utils_System::currentPath( ),
                                                             'action=browse&reset=1' ) );
            require_once 'CRM/Contribute/Controller/ContributionPage.php';
            $controller = new CRM_Contribute_Controller_ContributionPage( null, $action );
            CRM_Utils_System::setTitle( ts('Manage Contribution Page') );
            CRM_Utils_System::appendBreadCrumb( $breadCrumb );
            return $controller->run( );
        } else if ($action & CRM_Core_Action::UPDATE ) {
            CRM_Utils_System::appendBreadCrumb( $breadCrumb );
            $session = CRM_Core_Session::singleton( ); 
            $session->pushUserContext( CRM_Utils_System::url( CRM_Utils_System::currentPath( ),
                                                             "action=update&reset=1&id={$id}") );
            require_once 'CRM/Contribute/Page/ContributionPageEdit.php';
            $page = new CRM_Contribute_Page_ContributionPageEdit( );
            return $page->run( );
        } else if ($action & CRM_Core_Action::PREVIEW) {
            CRM_Utils_System::appendBreadCrumb( $breadCrumb );
            require_once 'CRM/Contribute/Page/ContributionPageEdit.php';
            $page = new CRM_Contribute_Page_ContributionPageEdit( );
            return $page->run( );
        } else if ($action & CRM_Core_Action::COPY) {
            $session = CRM_Core_Session::singleton();
            CRM_Core_Session::setStatus("A copy of the contribution page has been created" );
            $this->copy( );
        } else if ($action & CRM_Core_Action::DELETE) {
            CRM_Utils_System::appendBreadCrumb( $breadCrumb );
            $subPage = CRM_Utils_Request::retrieve( 'subPage', 'String',
                                                    $this );
            if ( $subPage == 'AddProductToPage' ) {
                require_once 'CRM/Contribute/Page/ContributionPageEdit.php';
                $page = new CRM_Contribute_Page_ContributionPageEdit( );
                return $page->run( );
            } else {
                CRM_Utils_System::appendBreadCrumb( $breadCrumb );
                $session = CRM_Core_Session::singleton();
                $session->pushUserContext( CRM_Utils_System::url( CRM_Utils_System::currentPath( ), 'reset=1&action=browse' ) );
                $controller = new CRM_Core_Controller_Simple( 'CRM_Contribute_Form_ContributionPage_Delete',
                                                               'Delete Contribution Page',
                                                               CRM_Core_Action::DELETE );
                $id = CRM_Utils_Request::retrieve('id', 'Positive',
                                                  $this, false, 0);
                $query = "SELECT ccp.title
FROM civicrm_contribution_page ccp JOIN civicrm_pcp cp ON ccp.id = cp.contribution_page_id
WHERE cp.contribution_page_id = {$id}";
                if ( $pageTitle = CRM_Core_DAO::singleValueQuery( $query ) ) {
                    CRM_Core_Session::setStatus(" The '{$pageTitle}' cannot be deleted! You must Delete all Personal Campaign Page(s) related with this contribution page prior to deleting the page." );
                    CRM_Utils_System::redirect( CRM_Utils_System::url('civicrm/admin/contribute','reset=1') );   
                }

                $controller->set('id', $id);
                $controller->process( );
                return $controller->run( );
            }
        } else {
            require_once 'CRM/Contribute/BAO/ContributionPage.php';
            
            // finally browse the contribution pages
            $this->browse();
            CRM_Utils_System::setTitle( ts('Manage Contribution Pages') );
        }
       
        return parent::run();
    }

    /**
     * This function is to make a copy of a contribution page, including
     * all the fields in the page
     *
     * @return void
     * @access public
     */
    function copy( ) 
    {
        $gid = CRM_Utils_Request::retrieve('gid', 'Positive',
                                           $this, true, 0, 'GET');

        require_once 'CRM/Contribute/BAO/ContributionPage.php';
        CRM_Contribute_BAO_ContributionPage::copy( $gid );

        CRM_Utils_System::redirect( CRM_Utils_System::url( CRM_Utils_System::currentPath( ), 'reset=1' ) );
    }

    /**
     * Browse all contribution pages
     *
     * @return void
     * @access public
     * @static
     */
    function browse($action=null)
    {
        $this->_sortByCharacter = CRM_Utils_Request::retrieve( 'sortByCharacter',
                                                               'String',
                                                               $this );
        $createdId = CRM_Utils_Request::retrieve('cid', 'Positive',
                                          $this, false, 0);

        if ( $this->_sortByCharacter == 1 ||
             ! empty( $_POST ) ) {
            $this->_sortByCharacter = '';
            $this->set( 'sortByCharacter', '' );
        }

        $this->search( );

        $params = array( );
                      
        $whereClause = $this->whereClause( $params, false );
        $this->pagerAToZ( $whereClause, $params );

        $params      = array( );
        $whereClause = $this->whereClause( $params, true );
        $this->pager( $whereClause, $params );
        
        list( $offset, $rowCount ) = $this->_pager->getOffsetAndRowCount( );
        
        //check for delete CRM-4418
        require_once 'CRM/Core/Permission.php'; 
        $allowToDelete = CRM_Core_Permission::check( 'delete in CiviContribute' );
        
        $query = "
SELECT *
FROM civicrm_contribution_page
WHERE $whereClause
ORDER BY title asc
   LIMIT $offset, $rowCount";

        $dao = CRM_Core_DAO::executeQuery( $query, $params, true, 'CRM_Contribute_DAO_ContributionPage' );
        
        while ($dao->fetch()) {
            $contribution[$dao->id] = array();
            CRM_Core_DAO::storeValues($dao, $contribution[$dao->id]);
            // form all action links
            $action = array_sum(array_keys($this->actionLinks()));
            
            if ( $dao->is_active ) {
                $action -= CRM_Core_Action::ENABLE;
            } else {
                $action -= CRM_Core_Action::DISABLE;
            }   
            
            //CRM-4418
            if ( !$allowToDelete ) {
                $action -= CRM_Core_Action::DELETE; 
            }
            
            $contribution[$dao->id]['action'] = CRM_Core_Action::formLink(self::actionLinks(), $action, 
                                                                          array('id' => $dao->id));
        }
        if (isset($contribution)) {
            $this->assign('rows', $contribution);
        }        
    }

     function search( ) {
        if ( isset($this->_action) &
             ( CRM_Core_Action::ADD    |
               CRM_Core_Action::UPDATE |
               CRM_Core_Action::DELETE ) ) {
            return;
        }
       
        $form = new CRM_Core_Controller_Simple( 'CRM_Contribute_Form_SearchContribution', ts( 'Search Contribution' ), CRM_Core_Action::ADD );
        $form->setEmbedded( true );
        $form->setParent( $this );
        $form->process( );
        $form->run( );
    }
    
    function whereClause( &$params, $sortBy = true ) {
        $values  =  array( );
        $clauses = array( );
        $title   = $this->get( 'title' );
        $createdId = $this->get( 'cid' );
        
        if( $createdId ) {
            $clauses[] = "(created_id = {$createdId})";
        }

        if ( $title ) {
            $clauses[] = "title LIKE %1";
            if ( strpos( $title, '%' ) !== false ) {
                $params[1] = array( trim($title), 'String', false );
            } else {
                $params[1] = array( trim($title), 'String', true );
            }
        }

        $value = $this->get( 'contribution_type_id' );
        $val = array( );
         if( $value) {
             if ( is_array( $value ) ) {
                 foreach ($value as $k => $v) {
                     if ($v) {
                         $val[$k] = $k;
                     }
                 } 
                 $type = implode (',' ,$val);
             }
             
             $clauses[] = "contribution_type_id IN ({$type})";
         }
         
         if ( $sortBy &&
             $this->_sortByCharacter ) {
            $clauses[] = 'title LIKE %3';
            $params[3] = array( $this->_sortByCharacter . '%', 'String' );
        }
        
        if ( empty( $clauses ) ) {
            // Let template know if user has run a search or not
            $this->assign('isSearch', 0);
            return 1;
        } else {
            $this->assign('isSearch', 1);
        }
            
        return implode( ' AND ', $clauses );
    }

     function pager( $whereClause, $whereParams ) {
        require_once 'CRM/Utils/Pager.php';

        $params['status']       = ts('Contribution %%StatusMessage%%');
        $params['csvString']    = null;
        $params['buttonTop']    = 'PagerTopButton';
        $params['buttonBottom'] = 'PagerBottomButton';
        $params['rowCount']     = $this->get( CRM_Utils_Pager::PAGE_ROWCOUNT );
        if ( ! $params['rowCount'] ) {
            $params['rowCount'] = CRM_Utils_Pager::ROWCOUNT;
        }

        $query = "
SELECT count(id)
  FROM civicrm_contribution_page
 WHERE $whereClause";

        $params['total'] = CRM_Core_DAO::singleValueQuery( $query, $whereParams );
            
        $this->_pager = new CRM_Utils_Pager( $params );
        $this->assign_by_ref( 'pager', $this->_pager );
    }

    function pagerAtoZ( $whereClause, $whereParams ) {
        require_once 'CRM/Utils/PagerAToZ.php';
        
        $query = "
   SELECT DISTINCT UPPER(LEFT(title, 1)) as sort_name
     FROM civicrm_contribution_page
    WHERE $whereClause
 ORDER BY LEFT(title, 1)
";
        $dao = CRM_Core_DAO::executeQuery( $query, $whereParams );

        $aToZBar = CRM_Utils_PagerAToZ::getAToZBar( $dao, $this->_sortByCharacter, true );
        $this->assign( 'aToZ', $aToZBar );
    }
}

