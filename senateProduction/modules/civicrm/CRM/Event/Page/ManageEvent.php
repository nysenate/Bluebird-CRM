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
require_once 'CRM/Event/BAO/Event.php';

/**
 * Page for displaying list of events
 */
class CRM_Event_Page_ManageEvent extends CRM_Core_Page
{
    /**
     * The action links that we need to display for the browse screen
     *
     * @var array
     * @static
     */
    static $_actionLinks = null;

    static $_links = null;

    protected $_pager = null;

    protected $_sortByCharacter;

    protected $_isTemplate = false;

    /**
     * Get action Links
     *
     * @return array (reference) of action links
     */
    function &links()
    {
        if (!(self::$_actionLinks)) {
            // helper variable for nicer formatting
            $copyExtra = ts('Are you sure you want to make a copy of this Event?');
            $deleteExtra = ts('Are you sure you want to delete this Event?');
            
            self::$_actionLinks = array(
                                        CRM_Core_Action::UPDATE  => array(
                                                                          'name'  => ts('Configure'),
                                                                          'url'   => CRM_Utils_System::currentPath( ),
                                                                          'qs'    => 'action=update&id=%%id%%&reset=1',
                                                                          'title' => ts('Configure Event') 
                                                                          ),
                                        CRM_Core_Action::PREVIEW => array(
                                                                          'name'  => ts('Test-drive'),
                                                                          'url'   => 'civicrm/event/info',
                                                                          'qs'    => 'reset=1&action=preview&id=%%id%%',
                                                                          'title' => ts('Preview') 
                                                                          ),
                                        CRM_Core_Action::DISABLE => array(
                                                                          'name'  => ts('Disable'),
                                                                          'extra' => 'onclick = "enableDisable( %%id%%,\''. 'CRM_Event_BAO_Event' . '\',\'' . 'enable-disable' . '\' );"',
                                                                          'ref'   => 'disable-action',
                                                                          'title' => ts( 'Disable Event' )
                                                                          ),
                                        CRM_Core_Action::ENABLE  => array(
                                                                          'name'  => ts('Enable'),
                                                                          'extra' => 'onclick = "enableDisable( %%id%%,\''. 'CRM_Event_BAO_Event' . '\',\'' . 'disable-enable' . '\' );"',
                                                                          'ref'   => 'enable-action',
                                                                          'title' => ts( 'Enable Event' )
                                                                          ),
                                        CRM_Core_Action::DELETE  => array(
                                                                          'name'  => ts('Delete'),
                                                                          'url'   => CRM_Utils_System::currentPath( ),
                                                                          'qs'    => 'action=delete&id=%%id%%',
                                                                          'extra' => 'onclick = "return confirm(\'' . $deleteExtra . '\');"',
                                                                          'title' => ts('Delete Event') 
                                                                          ),
                                        CRM_Core_Action::COPY     => array(
                                                                           'name'  => ts('Copy'),
                                                                           'url'   => CRM_Utils_System::currentPath( ), 
                                                                           'qs'    => 'reset=1&action=copy&id=%%id%%',
                                                                           'extra' => 'onclick = "return confirm(\'' . $copyExtra . '\');"',
                                                                           'title' => ts('Copy Event') 
                                                                           )
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
        $id = CRM_Utils_Request::retrieve( 'id', 'Positive',
                                           $this, false, 0, 'REQUEST' );

        // figure out whether we’re handling an event or an event template
        if ($id) {
            $this->_isTemplate = CRM_Core_DAO::getFieldValue('CRM_Event_DAO_Event', $id, 'is_template');
        } elseif ($action & CRM_Core_Action::ADD) {
            $this->_isTemplate = CRM_Utils_Request::retrieve('is_template', 'Boolean', $this);
        }
        
        if ( !$this->_isTemplate && $id ) {
            $breadCrumb = array(array('title' => ts('Manage Events'),
                                      'url'   => CRM_Utils_System::url(CRM_Utils_System::currentPath(), 'reset=1')));
            CRM_Utils_System::appendBreadCrumb( $breadCrumb );
        }

        // what action to take ?
        if ( $action & CRM_Core_Action::DELETE ) {
            $session = CRM_Core_Session::singleton();
            $session->pushUserContext( CRM_Utils_System::url( CRM_Utils_System::currentPath( ), 'reset=1&action=browse' ) );
            $controller = new CRM_Core_Controller_Simple( 'CRM_Event_Form_ManageEvent_Delete',
                                                          'Delete Event',
                                                          $action );
            $controller->set( 'id', $id );
            $controller->process( );
            return $controller->run( );
        } else if ($action & CRM_Core_Action::COPY ) {
            $this->copy( );
        }
        
        // finally browse the custom groups
        $this->browse();
        
        // parent run 
        parent::run();
    }

    /**
     * browse all events
     * 
     * @return void
     */
    function browse()
    {
        $this->_sortByCharacter = CRM_Utils_Request::retrieve( 'sortByCharacter',
                                                               'String',
                                                               $this );
        $createdId = CRM_Utils_Request::retrieve('cid', 'Positive', $this, false, 0);
        if ( $this->_sortByCharacter == 1 ||
             ! empty( $_POST ) ) {
            $this->_sortByCharacter = '';
            $this->set( 'sortByCharacter', '' );
        }

        $this->_force = $this->_searchResult = null;
      
        $this->search( );

        $config = CRM_Core_Config::singleton( );
        
        $params = array( );
        $this->_force = CRM_Utils_Request::retrieve( 'force', 'Boolean',
                                                       $this, false ); 
        $this->_searchResult = CRM_Utils_Request::retrieve( 'searchResult', 'Boolean', $this );
      
        $whereClause = $this->whereClause( $params, false, $this->_force );
        $this->pagerAToZ( $whereClause, $params );

        $params      = array( );
        $whereClause = $this->whereClause( $params, true, $this->_force );
        $whereClause .= ' AND (is_template = 0 OR is_template IS NULL)'; // because is_template != 1 would be to simple

        $this->pager( $whereClause, $params );

        list( $offset, $rowCount ) = $this->_pager->getOffsetAndRowCount( );

        // get all custom groups sorted by weight
        $manageEvent = array();

        $query = "
  SELECT *
    FROM civicrm_event
   WHERE $whereClause
ORDER BY start_date desc
   LIMIT $offset, $rowCount";
        
        $dao = CRM_Core_DAO::executeQuery( $query, $params, true, 'CRM_Event_DAO_Event' );
        $permissions = CRM_Event_BAO_Event::checkPermission( );

        while ($dao->fetch()) {
            if ( in_array( $dao->id, $permissions[CRM_Core_Permission::VIEW] ) ) {
                $manageEvent[$dao->id] = array();
                CRM_Core_DAO::storeValues( $dao, $manageEvent[$dao->id]);
            
                // form all action links
                $action = array_sum(array_keys($this->links()));
            
                if ($dao->is_active) {
                    $action -= CRM_Core_Action::ENABLE;
                } else {
                    $action -= CRM_Core_Action::DISABLE;
                }
            
                if ( ! in_array( $dao->id, $permissions[CRM_Core_Permission::DELETE] ) ) {
                    $action -= CRM_Core_Action::DELETE; 
                }
                if ( ! in_array( $dao->id, $permissions[CRM_Core_Permission::EDIT] ) ) {
                    $action -= CRM_Core_Action::UPDATE; 
                }
            
                $manageEvent[$dao->id]['action'] = CRM_Core_Action::formLink( self::links(), 
                                                                              $action, 
                                                                              array( 'id' => $dao->id ),
                                                                              true );

                $params = array( 'entity_id'    => $dao->id, 
                                 'entity_table' => 'civicrm_event',
                                 'is_active'    => 1
                                 );
                
                require_once 'CRM/Core/BAO/Location.php';
                $defaults['location'] = CRM_Core_BAO_Location::getValues( $params, true );

                require_once 'CRM/Friend/BAO/Friend.php';
                $manageEvent[$dao->id]['friend'] = CRM_Friend_BAO_Friend::getValues( $params );

                if ( isset ( $defaults['location']['address'][1]['city'] ) ) {
                    $manageEvent[$dao->id]['city'] = $defaults['location']['address'][1]['city'];
                }
                if ( isset( $defaults['location']['address'][1]['state_province_id'] )) {
                    $manageEvent[$dao->id]['state_province'] = CRM_Core_PseudoConstant::stateProvince($defaults['location']['address'][1]['state_province_id']);
                }
            }
        }
        $this->assign('rows', $manageEvent);
        
        require_once 'CRM/Event/PseudoConstant.php';
        $statusTypes        = CRM_Event_PseudoConstant::participantStatus(null, 'is_counted = 1');
        $statusTypesPending = CRM_Event_PseudoConstant::participantStatus(null, 'is_counted = 0');
        $findParticipants['statusCounted'] = implode( ', ', array_values( $statusTypes ) );
        $findParticipants['statusNotCounted'] = implode( ', ', array_values( $statusTypesPending ) );
        $this->assign('findParticipants', $findParticipants);
    }
    
    /**
     * This function is to make a copy of a Event, including
     * all the fields in the event wizard
     *
     * @return void
     * @access public
     */
    function copy( )
    {
        $id = CRM_Utils_Request::retrieve('id', 'Positive', $this, true, 0, 'GET');
        
        $urlString = 'civicrm/event/manage';
        require_once 'CRM/Event/BAO/Event.php';
        $copyEvent = CRM_Event_BAO_Event::copy( $id );
        $urlParams = 'reset=1';
        // Redirect to Copied Event Configuration
        if ( $copyEvent->id ) {
            $urlString  = 'civicrm/event/manage/eventInfo';
            $urlParams .=  '&action=update&id='.$copyEvent->id;
        }

        return CRM_Utils_System::redirect( CRM_Utils_System::url( $urlString, $urlParams ) );
    }
    
    function search( )
    {
        if ( isset($this->_action) &
             ( CRM_Core_Action::ADD    |
               CRM_Core_Action::UPDATE |
               CRM_Core_Action::DELETE ) ) {
            return;
        }
       
        $form = new CRM_Core_Controller_Simple( 'CRM_Event_Form_SearchEvent', ts( 'Search Events' ), CRM_Core_Action::ADD );
        $form->setEmbedded( true );
        $form->setParent( $this );
        $form->process( );
        $form->run( );
    }
    
    function whereClause( &$params, $sortBy = true, $force )
    {
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

        $value = $this->get( 'event_type_id' );
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
            $clauses[] = "event_type_id IN ({$type})";
        }
        
        $eventsByDates = $this->get( 'eventsByDates' );
        if ($this->_searchResult) {
            if ( $eventsByDates) {
                require_once 'CRM/Utils/Date.php';
                
                $from = $this->get( 'start_date' );
                if ( ! CRM_Utils_System::isNull( $from ) ) {
                    $clauses[] = '( start_date >= %3 OR start_date IS NULL )';
                    $params[3] = array( $from, 'String' );
                }
                
                $to = $this->get( 'end_date' );
                if ( ! CRM_Utils_System::isNull( $to ) ) {
                    $clauses[] = '( end_date <= %4 OR end_date IS NULL )';
                    $params[4] = array( $to, 'String' );
                }
                
            } else {
                $curDate = date( 'YmdHis' );
                $clauses[5] =  "(end_date >= {$curDate} OR end_date IS NULL)";
            }
        
        } else {
            $curDate = date( 'YmdHis' );
            $clauses[] =  "(end_date >= {$curDate} OR end_date IS NULL)";
        }

        if ( $sortBy &&
             $this->_sortByCharacter ) {
            $clauses[] = 'title LIKE %6';
            $params[6] = array( $this->_sortByCharacter . '%', 'String' );
        }

        // dont do a the below assignment when doing a 
        // AtoZ pager clause
        if ( $sortBy ) {
            if ( count( $clauses ) > 1 || $eventsByDates  ) {
                $this->assign( 'isSearch', 1 );
            } else {
                $this->assign( 'isSearch', 0 );
            }
        }

        return !empty($clauses) ? implode( ' AND ', $clauses ) : '(1)';
    }
    
    function pager( $whereClause, $whereParams )
    {
        require_once 'CRM/Utils/Pager.php';
        
        $params['status']       = ts('Event %%StatusMessage%%');
        $params['csvString']    = null;
        $params['buttonTop']    = 'PagerTopButton';
        $params['buttonBottom'] = 'PagerBottomButton';
        $params['rowCount']     = $this->get( CRM_Utils_Pager::PAGE_ROWCOUNT );
        if ( ! $params['rowCount'] ) {
            $params['rowCount'] = CRM_Utils_Pager::ROWCOUNT;
        }

        $query = "
SELECT count(id)
  FROM civicrm_event
 WHERE $whereClause";

        $params['total'] = CRM_Core_DAO::singleValueQuery( $query, $whereParams );
            
        $this->_pager = new CRM_Utils_Pager( $params );
        $this->assign_by_ref( 'pager', $this->_pager );
    }

    function pagerAtoZ( $whereClause, $whereParams )
    {
        require_once 'CRM/Utils/PagerAToZ.php';
        
        $query = "
   SELECT DISTINCT UPPER(LEFT(title, 1)) as sort_name
     FROM civicrm_event
    WHERE $whereClause
 ORDER BY LEFT(title, 1)
";
        $dao = CRM_Core_DAO::executeQuery( $query, $whereParams );

        $aToZBar = CRM_Utils_PagerAToZ::getAToZBar( $dao, $this->_sortByCharacter, true );
        $this->assign( 'aToZ', $aToZBar );
    }
    
}

