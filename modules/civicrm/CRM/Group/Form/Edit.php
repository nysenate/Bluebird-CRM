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

require_once 'CRM/Core/Form.php';
require_once "CRM/Custom/Form/CustomData.php";
require_once 'CRM/Contact/BAO/GroupNesting.php';
require_once 'CRM/Core/BAO/Domain.php';

/**
 * This class is to build the form for adding Group
 */
class CRM_Group_Form_Edit extends CRM_Core_Form 
{
    
    /**
     * the group id, used when editing a group
     *
     * @var int
     */
    protected $_id;

    /**
     * the group object, if an id is present
     *
     * @var object
     */
    protected $_group;

    /**
     * The title of the group being deleted
     *
     * @var string
     */
    protected $_title;
    
    /**
     * Store the group values
     *
     * @var array
     */
    protected $_groupValues;
    
    /**
     * what blocks should we show and hide.
     *
     * @var CRM_Core_ShowHideBlocks
     */
    protected $_showHide;

    /**
     * the civicrm_group_organization table id
     *
     * @var int
     */
    protected $_groupOrganizationID;

    /**
     * set up variables to build the form
     *
     * @return void
     * @acess protected
     */
    function preProcess( ) 
	{
		$this->_id = $this->get( 'id' );
        
        if ( $this->_id ) {
            $breadCrumb = array( array('title' => ts('Manage Groups'),
                                       'url'   => CRM_Utils_System::url( 'civicrm/group', 
                                                                         'reset=1' )) );
            CRM_Utils_System::appendBreadCrumb( $breadCrumb );

            $this->_groupValues = array( );
            $params   = array( 'id' => $this->_id );
            $this->_group =& CRM_Contact_BAO_Group::retrieve( $params,
                                                              $this->_groupValues );
            $this->_title = $this->_groupValues['title'];
        }

        $this->assign ( 'action', $this->_action );
        $this->assign ( 'showBlockJS', true );

        if ($this->_action == CRM_Core_Action::DELETE) {    
            if ( isset($this->_id) ) {
                $this->assign( 'title' , $this->_title );
                $this->assign( 'count', CRM_Contact_BAO_Group::memberCount( $this->_id ) );
                CRM_Utils_System::setTitle( ts('Confirm Group Delete') );
            }
        } else {
            if ( isset($this->_id) ) {
                $groupValues = array( 'id'              => $this->_id,
                                      'title'           => $this->_title,
                                      'saved_search_id' =>
                                      isset( $this->_groupValues['saved_search_id'] ) ?
                                      $this->_groupValues['saved_search_id'] : '' );
                if ( isset($this->_groupValues['saved_search_id']) ){
                    $groupValues['mapping_id'] = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_SavedSearch', 
                                                                              $this->_groupValues['saved_search_id'], 
                                                                              'mapping_id' ) ;
                }
                $this->assign_by_ref( 'group', $groupValues );
                
                CRM_Utils_System::setTitle( ts('Group Settings: %1', array( 1 => $this->_title ) ) );
            }
            $session = CRM_Core_Session::singleton( );
            $session->pushUserContext(CRM_Utils_System::url('civicrm/group', 'reset=1'));
        }

		//build custom data
        CRM_Custom_Form_Customdata::preProcess( $this, null, null, 1, 'Group', $this->_id );
    }
    
    /*
     * This function sets the default values for the form. LocationType that in edit/view mode
     * the default values are retrieved from the database
     *
     * @access public
     * @return None
     */
    function setDefaultValues( ) {
        $defaults = array( );

        if ( isset( $this->_id ) ) {
            $defaults = $this->_groupValues;
            if ( CRM_Utils_Array::value('group_type',$defaults) ) {
                $types = explode( CRM_Core_DAO::VALUE_SEPARATOR,
                                  substr( $defaults['group_type'], 1, -1 ) );
                $defaults['group_type'] = array( );
                foreach ( $types as $type ) {
                    $defaults['group_type'][$type] = 1;
                }
            }
            
            if ( defined( 'CIVICRM_MULTISITE' ) && CIVICRM_MULTISITE && 
                 CRM_Core_Permission::check( 'administer Multiple Organizations' ) ) {
                require_once 'CRM/Contact/BAO/GroupOrganization.php';
                CRM_Contact_BAO_GroupOrganization::retrieve( $this->_id, $defaults );
                
                if ( CRM_Utils_Array::value( 'group_organization', $defaults ) ) {
                    //used in edit mode
                    $this->_groupOrganizationID = $defaults['group_organization'];
                }

                $this->assign( 'organizationID', $defaults['organization_id'] );  
            }
        }

        if ( !CRM_Utils_Array::value('parents',$defaults) ) {
            $defaults['parents'] = CRM_Core_BAO_Domain::getGroupId( );
        }

		// custom data set defaults
		$defaults += CRM_Custom_Form_Customdata::setDefaultValues( $this );
        return $defaults;
    }

    /**
     * Function to actually build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
	{
        if ( $this->_action == CRM_Core_Action::DELETE ) {
            $this->addButtons( array(
                                     array ( 'type'      => 'next',
                                             'name'      => ts('Delete Group'),
                                             'isDefault' => true   ),
                                     array ( 'type'       => 'cancel',
                                             'name'      => ts('Cancel') ),
                                     )
                               );
            return;
        } 

        $this->applyFilter('__ALL__', 'trim');
        $this->add('text', 'title'       , ts('Name') . ' ' ,
                   CRM_Core_DAO::getAttribute( 'CRM_Contact_DAO_Group', 'title' ),true );
        $this->addFormRule( array( 'CRM_Group_Form_Edit', 'formRule' ), $this );
        
        $this->add('textarea', 'description', ts('Description') . ' ', 
                   CRM_Core_DAO::getAttribute( 'CRM_Contact_DAO_Group', 'description' ) );

        require_once 'CRM/Core/OptionGroup.php';
        $groupTypes = CRM_Core_OptionGroup::values( 'group_type', true );
        $config= CRM_Core_Config::singleton( );
        if ( (isset( $this->_id ) &&
             CRM_Utils_Array::value( 'saved_search_id', $this->_groupValues ) ) 
             || ( $config->userFramework == 'Joomla' ) ) {
            unset( $groupTypes['Access Control'] );
        }
        
        if ( ! CRM_Core_Permission::access( 'CiviMail' ) ) {
            unset( $groupTypes['Mailing List'] );
        }

        if ( ! empty( $groupTypes ) ) {
            $this->addCheckBox( 'group_type',
                                ts( 'Group Type' ),
                                $groupTypes,
                                null, null, null, null, '&nbsp;&nbsp;&nbsp;' );
        }

        $this->add( 'select', 'visibility', ts('Visibility'),
                    CRM_Core_SelectValues::ufVisibility( true ), true ); 
        
        $groupNames =& CRM_Core_PseudoConstant::group();

        $parentGroups = $parentGroupElements = array( );
        if ( isset( $this->_id ) &&
             CRM_Utils_Array::value( 'parents', $this->_groupValues ) ) {
            $parentGroupIds = explode( ',', $this->_groupValues['parents'] );
            foreach ( $parentGroupIds as $parentGroupId ) {
                $parentGroups[$parentGroupId] = $groupNames[$parentGroupId];
                if ( array_key_exists($parentGroupId, $groupNames) ) {
                    $parentGroupElements[$parentGroupId] = $groupNames[$parentGroupId];
                    $this->addElement( 'checkbox', "remove_parent_group_$parentGroupId",
                                       $groupNames[$parentGroupId] );
                }
            }
        }
        $this->assign_by_ref( 'parent_groups', $parentGroupElements );
        
        if ( isset( $this->_id ) ) {
            require_once 'CRM/Contact/BAO/GroupNestingCache.php';
            $potentialParentGroupIds =
                CRM_Contact_BAO_GroupNestingCache::getPotentialCandidates( $this->_id,
                                                                           $groupNames );
        } else {
            $potentialParentGroupIds = array_keys( $groupNames );
        }

        $parentGroupSelectValues = array( '' => '- ' . ts('select') . ' -' );
        foreach ( $potentialParentGroupIds as $potentialParentGroupId ) {
            if ( array_key_exists( $potentialParentGroupId, $groupNames ) ) {
                $parentGroupSelectValues[$potentialParentGroupId] = $groupNames[$potentialParentGroupId];
            }
        }
        
        if ( count( $parentGroupSelectValues ) > 1 ) {
            if ( defined( 'CIVICRM_MULTISITE' ) && CIVICRM_MULTISITE ) {
                $required = empty($parentGroups) ? true : false;
                $required = ( ($this->_id && CRM_Core_BAO_Domain::isDomainGroup($this->_id)) || 
                              !isset($this->_id) ) ? false : $required;
            } else {
                $required = false;
            }
            $this->add( 'select', 'parents', ts('Add Parent'), $parentGroupSelectValues, $required );
        }
        if ( defined( 'CIVICRM_MULTISITE' ) && CIVICRM_MULTISITE && 
             CRM_Core_Permission::check( 'administer Multiple Organizations' ) ) {
            //group organization Element
            $groupOrgDataURL =  CRM_Utils_System::url( 'civicrm/ajax/search', 'org=1', false, null, false );
            $this->assign('groupOrgDataURL',$groupOrgDataURL );
            
            $this->addElement('text', 'organization', ts('Organization'), '' );
            $this->addElement('hidden', 'organization_id', '', array( 'id' => 'organization_id') );
        }
		//build custom data
		CRM_Custom_Form_Customdata::buildQuickForm( $this );

        $this->addButtons( array(
                                 array ( 'type'      => 'upload',
                                         'name'      =>
                                         ( $this->_action == CRM_Core_Action::ADD ) ?
                                         ts('Continue') : ts('Save'),
                                         'isDefault' => true   ),
                                 array ( 'type'       => 'cancel',
                                         'name'      => ts('Cancel') ),
                                 )
                           );
        
        if ( defined( 'CIVICRM_MULTISITE' ) && CIVICRM_MULTISITE ) {
            $doParentCheck = ($this->_id && CRM_Core_BAO_Domain::isDomainGroup($this->_id)) ? false : true;
        } else {
            $doParentCheck = false;
        }
        if ( $doParentCheck ) {
            $this->addFormRule( array( 'CRM_Group_Form_Edit', 'formRule' ), $parentGroups );
        }
    }
    
    /**
     * global validation rules for the form
     *
     * @param array $fields posted values of the form
     *
     * @return array list of errors to be posted back to the form
     * @static
     * @access public
     */
    static function formRule( $fields, $fileParams, $parentGroups ) 
    {
        $errors = array( );

        $grpRemove = 0;
        foreach ( $fields as $key => $val ) {
            if ( substr( $key, 0, 20 ) == 'remove_parent_group_' ) {
                $grpRemove++;
            }
        }

        $grpAdd = 0;
        if ( CRM_Utils_Array::value( 'parents', $fields ) ) {
            $grpAdd++;
        }

        if ( (count($parentGroups) >= 1) && (($grpRemove - $grpAdd) >=  count($parentGroups)) ) {
            $errors['parents'] = ts( 'Make sure at least one parent group is set.' );
        }
        
        // do check for both name and title uniqueness
        if ( CRM_Utils_Array::value( 'title', $fields ) ) {
            $title = trim( $fields['title'] );
            $name  = CRM_Utils_String::titleToVar( $title, 63 );
            $query  = "
SELECT count(*)
FROM   civicrm_group 
WHERE  (name LIKE %1 OR title LIKE %2) 
AND    id <> %3
";
            $grpCnt = CRM_Core_DAO::singleValueQuery( $query, array( 1 => array( $name,  'String' ),
                                                                     2 => array( $title, 'String' ),
                                                                     3 => array( (int)$parentGroups->_id, 'Integer' ) ) );
            if ( $grpCnt ) {
                $errors['title'] = ts( 'Group \'%1\' already exists.', array( 1 => $fields['title']) );
            }
        }

        return empty($errors) ? true : $errors;
    }    

    /**
     * Process the form when submitted
     *
     * @return void
     * @access public
     */
    public function postProcess( ) 
	{
        CRM_Utils_System::flushCache( 'CRM_Core_DAO_Group' );

        $updateNestingCache = false;
        if ($this->_action & CRM_Core_Action::DELETE ) {
            CRM_Contact_BAO_Group::discard( $this->_id );
            CRM_Core_Session::setStatus( ts("The Group '%1' has been deleted.", array(1 => $this->_title)) );        
            $updateNestingCache = true;
        } else {
            // store the submitted values in an array
            $params = $this->controller->exportValues( $this->_name );

            $params['is_active'] = CRM_Utils_Array::value( 'is_active', $this->_groupValues, 1 );

            if ($this->_action & CRM_Core_Action::UPDATE ) {
                $params['id'] = $this->_id;
            }

            if ( $this->_action & CRM_Core_Action::UPDATE && isset($this->_groupOrganizationID ) ) {
                $params['group_organization'] = $this->_groupOrganizationID;
            }

            $customFields = CRM_Core_BAO_CustomField::getFields( 'Group' );
            $params['custom'] = CRM_Core_BAO_CustomField::postProcess( $params,
                                                                       $customFields,
                                                                       $this->_id,
                                                                       'Group' );
            
            require_once 'CRM/Contact/BAO/Group.php';
            $group =& CRM_Contact_BAO_Group::create( $params );
            
            /*
             * Remove any parent groups requested to be removed
             */
            if ( CRM_Utils_Array::value( 'parents', $this->_groupValues ) ) {
                $parentGroupIds = explode( ',', $this->_groupValues['parents'] );
                foreach ( $parentGroupIds as $parentGroupId ) {
                    if ( isset( $params["remove_parent_group_$parentGroupId"] ) ) {
                        CRM_Contact_BAO_GroupNesting::remove( $parentGroupId, $group->id );
                        $updateNestingCache = true;
                    }
                }
            }
            
            CRM_Core_Session::setStatus( ts('The Group \'%1\' has been saved.', array(1 => $group->title)) );        
            
            /*
             * Add context to the session, in case we are adding members to the group
             */
            if ($this->_action & CRM_Core_Action::ADD ) {
                $this->set( 'context', 'amtg' );
                $this->set( 'amtgID' , $group->id );
                
                $session = CRM_Core_Session::singleton( );
                $session->pushUserContext( CRM_Utils_System::url( 'civicrm/group/search', 'reset=1&force=1&context=smog&gid=' . $group->id ) );
            }
        }

        // update the nesting cache
        if ( $updateNestingCache ) {
            require_once 'CRM/Contact/BAO/GroupNestingCache.php';
            CRM_Contact_BAO_GroupNestingCache::update( );
        }
    }
}


