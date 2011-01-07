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

require_once 'CRM/Core/Page.php';



abstract class CRM_Core_Page_Basic extends CRM_Core_Page {
    
    protected $_action;

    /**
     * define all the abstract functions here
     */

    /**
     * name of the BAO to perform various DB manipulations
     *
     * @return string
     * @access public
     */

    abstract function getBAOName( );

    /**
     * an array of action links
     *
     * @return array (reference)
     * @access public
     */
    abstract function &links( );

    /**
     * name of the edit form class
     *
     * @return string
     * @access public
     */
    abstract function editForm( );

    /**
     * name of the form
     *
     * @return string
     * @access public
     */
    abstract function editName( );

    /**
     * userContext to pop back to
     *
     * @param int $mode mode that we are in
     *
     * @return string
     * @access public
     */
    abstract function userContext( $mode = null );

    /**
     * function to get userContext params
     *
     * @param int $mode mode that we are in
     *
     * @return string
     * @access public
     */
    function userContextParams( $mode = null ) {
        return 'reset=1&action=browse';
    }

    /**
     * allow objects to be added based on permission
     *
     * @param int $id   the id of the object
     * @param int $name the name or title of the object
     *
     * @return string   permission value if permission is granted, else null
     * @access public
     */
    public function checkPermission( $id, $name ) {
        return CRM_Core_Permission::EDIT;
    }

    /**
     * allows the derived class to add some more state variables to
     * the controller. By default does nothing, and hence is abstract
     *
     * @param CRM_Core_Controller $controller the controller object
     *
     * @return void
     * @access public
     */
    function addValues( $controller ) {
    }

    /**
     * class constructor
     *
     * @param string $title title of the page
     * @param int    $mode  mode of the page
     *
     * @return CRM_Core_Page
     */
    function __construct( $title = null, $mode = null ) {
        parent::__construct($title, $mode);
    }

    /**
     * Run the basic page (run essentially starts execution for that page).
     *
     * @return void
     */
    function run( $args = null, $pageArgs = null, $sort = null )
    {
        // what action do we want to perform ? (store it for smarty too.. :) 
     
        $this->_action = CRM_Utils_Request::retrieve( 'action', 'String',
                                                      $this, false, 'browse' );
        $this->assign( 'action', $this->_action );

        // get 'id' if present
        $id  = CRM_Utils_Request::retrieve( 'id', 'Positive',
                                            $this, false, 0 );

        require_once(str_replace('_', DIRECTORY_SEPARATOR, $this->getBAOName()) . ".php");

        if ( $id ) {
            if ( ! $this->checkPermission( $id, null ) ) {
                CRM_Core_Error::fatal( ts( 'You do not have permission to make changes to the record' ) );
            }
        }

        if ( $this->_action &
             ( CRM_Core_Action::VIEW   | 
               CRM_Core_Action::ADD    |
               CRM_Core_Action::UPDATE |
               CRM_Core_Action::DELETE ) ) {
            $this->edit($this->_action, $id);                               // use edit form for view, add or update or delete
        } else {
            // if no action or browse 
            $this->browse(null, $sort);
        }

        return parent::run();
    }

    function superRun( ) {
        return parent::run( );
    }

    /**
     * browse all entities.
     *
     * @param int $action
     *
     * @return void
     * @access public
     */
    function browse( $action = null, $sort ) {
        $links =& $this->links();
        if ($action == null) {
            if ( ! empty( $links ) ) {
                $action = array_sum(array_keys($links));
            }
        }
        if ( $action & CRM_Core_Action::DISABLE ) {
            $action -= CRM_Core_Action::DISABLE;
        }
        if ( $action & CRM_Core_Action::ENABLE ) {
            $action -= CRM_Core_Action::ENABLE;
        }
        
        eval( '$object = new ' . $this->getBAOName( ) . '( );' );
        
        $values = array();
        
        /*
         * lets make sure we get the stuff sorted by name if it exists
         */
        $fields =& $object->fields( );
        $key = '';
        if ( CRM_Utils_Array::value( 'title', $fields ) ) {
            $key = 'title';
        }  else if ( CRM_Utils_Array::value( 'label', $fields ) ) {
            $key = 'label';
        } else if ( CRM_Utils_Array::value( 'name', $fields ) ) {
            $key = 'name';
        }
        
        if (trim($sort)) {
            $object->orderBy ( $sort );
        } else if ( $key ) {
            $object->orderBy ( $key . ' asc' );
        }
        
        
        // find all objects
        $object->find();
        while ($object->fetch()) {
            if ( ! isset( $object->mapping_type_id ) ||
                 $object->mapping_type_id != 1  ) {  // "1 for Search Builder"
                $permission = CRM_Core_Permission::EDIT;
                if ( $key ) {
                    $permission = $this->checkPermission( $object->id, $object->$key );
                }
                if ( $permission ) {
                    $values[$object->id] = array( );
                    CRM_Core_DAO::storeValues( $object, $values[$object->id]);

                    require_once 'CRM/Contact/DAO/RelationshipType.php';
                    CRM_Contact_DAO_RelationshipType::addDisplayEnums($values[$object->id]);
                    
                    // populate action links
                    $this->action( $object, $action, $values[$object->id], $links, $permission );
                    
                    if ( isset( $object->mapping_type_id ) ) {
                        require_once 'CRM/Core/PseudoConstant.php';
                        $mappintTypes = CRM_Core_PseudoConstant::mappingTypes( );
                        $values[$object->id]['mapping_type'] = $mappintTypes[$object->mapping_type_id];
                    }
                }
            }
        }
        $this->assign( 'rows', $values );
    }
    
    /**
     * Given an object, get the actions that can be associated with this
     * object. Check the is_active and is_required flags to display valid
     * actions
     *
     * @param CRM_Core_DAO $object the object being considered
     * @param int     $action the base set of actions
     * @param array   $values the array of values that we send to the template
     * @param array   $links  the array of links
     * @param string  $permission the permission assigned to this object
     *
     * @return void
     * @access private
     */
    function action( &$object, $action, &$values, &$links, $permission, $forceAction = false ) {
        $values['class'] = '';
        $newAction = $action;
        $hasDelete = $hasDisable = true;
        
        if ( in_array( $values['name'] , array( 'encounter_medium', 'case_type', 'case_status' ) ) ) {
            static $caseCount = null; 
            require_once 'CRM/Case/BAO/Case.php';
            if ( !isset( $caseCount ) ) {
                $caseCount = CRM_Case_BAO_Case::caseCount( null, false );
            }
            if ( $caseCount > 0 ) {
                $hasDelete = $hasDisable = false;
            }
        }

        if ( !$forceAction ) {
            if ( array_key_exists( 'is_reserved', $object ) && $object->is_reserved ) {
                $values['class'] = 'reserved';
                // check if object is relationship type
                if ( get_class( $object ) == 'CRM_Contact_BAO_RelationshipType' ) {
                    $newAction = CRM_Core_Action::VIEW + CRM_Core_Action::UPDATE;
                } else {
                    $newAction = 0;
                    $values['action'] = '';
                    return;
                }
            } else {
                if ( array_key_exists( 'is_active', $object ) ) {
                    if ( $object->is_active ) {
                        if ( $hasDisable ) {
                            $newAction += CRM_Core_Action::DISABLE;
                        }
                    } else {
                        $newAction += CRM_Core_Action::ENABLE;
                    }
                }
            }
        }
        
        //CRM-4418, handling edit and delete separately.
        $permissions = array( $permission ); 
        if ( $hasDelete && ( $permission == CRM_Core_Permission::EDIT ) ) {
            //previously delete was subset of edit 
            //so for consistency lets grant delete also.
            $permissions[] = CRM_Core_Permission::DELETE;
        }
        
        // make sure we only allow those actions that the user is permissioned for
        $newAction = $newAction & CRM_Core_Action::mask( $permissions );

        $values['action'] = CRM_Core_Action::formLink( $links, $newAction, array( 'id' => $object->id ) );
    }

    /**
     * Edit this entity.
     *
     * @param int $mode - what mode for the form ?
     * @param int $id - id of the entity (for update, view operations)
     * @return void
     */
    function edit( $mode, $id = null , $imageUpload = false , $pushUserContext = true) 
    {
        $controller = new CRM_Core_Controller_Simple( $this->editForm( ), $this->editName( ), $mode , $imageUpload );

       // set the userContext stack
        if( $pushUserContext ) {
            $session = CRM_Core_Session::singleton();
            $session->pushUserContext( CRM_Utils_System::url( $this->userContext( $mode ), $this->userContextParams( $mode ) ) );
        }
        if ($id !== null) {
            $controller->set( 'id'   , $id );
        }
        $controller->set('BAOName', $this->getBAOName());
        $this->addValues($controller);
        $controller->setEmbedded( true );
        $controller->process( );
        $controller->run( );
    }

}


