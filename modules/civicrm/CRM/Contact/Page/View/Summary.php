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

require_once 'CRM/Contact/Page/View.php';
require_once 'CRM/Contact/BAO/Contact.php';

/**
 * Main page for viewing contact.
 *
 */
class CRM_Contact_Page_View_Summary extends CRM_Contact_Page_View {

    /** 
     * Heart of the viewing process. The runner gets all the meta data for 
     * the contact and calls the appropriate type of page to view. 
     * 
     * @return void 
     * @access public 
     * 
     */ 
    function preProcess( ) 
    {
        parent::preProcess( );
		
		// actions buttom contextMenu
		$menuItems = CRM_Contact_BAO_Contact::contextMenu( );
		
		$this->assign('actionsMenuList',$menuItems);
		
        //retrieve inline custom data
        $entityType    = $this->get('contactType');
        $entitySubType = $this->get('contactSubtype');

        $groupTree =& CRM_Core_BAO_CustomGroup::getTree( $entityType,
                                                         $this, 
                                                         $this->_contactId,
                                                         null,
                                                         $entitySubType );

        CRM_Core_BAO_CustomGroup::buildCustomDataView( $this,
                                                       $groupTree );

        // also create the form element for the activity links box
        $controller = new CRM_Core_Controller_Simple( 'CRM_Activity_Form_ActivityLinks',
                                                       ts('Activity Links'),
                                                       null );
        $controller->setEmbedded( true );
        $controller->run( );
    }

    /**
     * Heart of the viewing process. The runner gets all the meta data for
     * the contact and calls the appropriate type of page to view.
     *
     * @return void
     * @access public
     *
     */
    function run( )
    {
        $this->preProcess( );

        if ( $this->_action & CRM_Core_Action::UPDATE ) {
            $this->edit( );
        } else {
            $this->view( );
        }

        return parent::run( );
    }

    /**
     * Edit name and address of a contact
     *
     * @return void
     * @access public
     */
    function edit( ) 
    {
        // set the userContext stack
        $session = CRM_Core_Session::singleton();
        $url = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid=' . $this->_contactId );
        $session->pushUserContext( $url );
        
        $controller = new CRM_Core_Controller_Simple( 'CRM_Contact_Form_Contact', ts('Contact Page'), CRM_Core_Action::UPDATE );
        $controller->setEmbedded( true );
        $controller->process( );
        return $controller->run( );
    }

    /**
     * View summary details of a contact
     *
     * @return void
     * @access public
     */
    function view( ) 
    {
        $session = CRM_Core_Session::singleton();
        $url     = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid=' . $this->_contactId );
        $session->pushUserContext( $url );

        $params   = array( );
        $defaults = array( );
        $ids      = array( );

        $params['id'] = $params['contact_id'] = $this->_contactId;
        $params['noRelationships'] = $params['noNotes'] = $params['noGroups'] = true;
        $contact = CRM_Contact_BAO_Contact::retrieve( $params, $defaults, true );
        
        $communicationType = array( 
                                    'phone'   => array( 
                                                        'type' => 'phoneType', 
                                                        'id'   => 'phone_type'), 
                                    'im'      => array( 
                                                        'type' => 'IMProvider', 
                                                        'id'   => 'provider'  ),
                                    'website' => array( 
                                                        'type' => 'websiteType', 
                                                        'id'   => 'website_type' ),                                                        
                                    'address' => array( 'skip' => true, 'customData' => 1 ),
									'email'   => array( 'skip' => true ),
									'openid'  => array( 'skip' => true )
                            ); 
        
        foreach( $communicationType as $key => $value ) {
            if ( CRM_Utils_Array::value( $key, $defaults ) ) {
                foreach( $defaults[$key] as &$val ) {
                    CRM_Utils_Array::lookupValue( $val, 'location_type', CRM_Core_PseudoConstant::locationType(), false );
                    if ( !CRM_Utils_Array::value( 'skip', $value ) ) {
                        eval( '$pseudoConst = CRM_Core_PseudoConstant::'.$value['type'].'( );' );
                        CRM_Utils_Array::lookupValue( $val, $value['id'], $pseudoConst, false );
                    }
                }
                if ( isset($value['customData']) ) {
                    foreach( $defaults[$key] as $blockId => $blockVal ) {
                        $groupTree = CRM_Core_BAO_CustomGroup::getTree( ucfirst($key),
                                                                        $this,
                                                                        $blockVal['id'] );
                        // we setting the prefix to dnc_ below so that we don't overwrite smarty's grouptree var. 
                        $defaults[$key][$blockId]['custom'] = 
                            CRM_Core_BAO_CustomGroup::buildCustomDataView( $this, $groupTree, false, null, "dnc_" );
                    }
                    // reset template variable since that won't be of any use, and could be misleading
                    $this->assign( "dnc_viewCustomData", null );
                }
            }
        }
		
        if ( CRM_Utils_Array::value( 'gender_id',  $defaults ) ) {
            $gender =CRM_Core_PseudoConstant::gender();
            $defaults['gender_display'] =  $gender[CRM_Utils_Array::value( 'gender_id',  $defaults )];
        }

        // to make contact type label available in the template -
        $contactType = array_key_exists( 'contact_sub_type',  $defaults ) ? 
            $defaults['contact_sub_type'] : $defaults['contact_type'];
        $defaults['contact_type_label'] = 
            CRM_Contact_BAO_ContactType::contactTypePairs( true, $contactType );

        // get contact tags
        require_once 'CRM/Core/BAO/EntityTag.php';
        $contactTags = CRM_Core_BAO_EntityTag::getContactTags($this->_contactId);       
        
        if ( !empty( $contactTags ) ) {
            $defaults['contactTag'] = implode( ', ', $contactTags );
        }
        
        $defaults['privacy_values'] = CRM_Core_SelectValues::privacy();
        
        //Show blocks only if they are visible in edit form
        require_once 'CRM/Core/BAO/Preferences.php';
        $this->_editOptions  = CRM_Core_BAO_Preferences::valueOptions( 'contact_edit_options' );
        $configItems = array( 'CommBlock'     => 'Communication Preferences',
                              'Demographics'  => 'Demographics',
                              'TagsAndGroups' => 'Tags and Groups',
                              'Notes'         => 'Notes' );

        foreach ( $configItems as $c => $t ) {
            $varName = '_show' . $c;
            $this->$varName = CRM_Utils_Array::value( $c, $this->_editOptions );
            $this->assign( substr( $varName, 1 ), $this->$varName );
        }

        // get contact name of shared contact names
        $sharedAddresses = array( );
        $shareAddressContactNames = CRM_Contact_BAO_Contact_Utils::getAddressShareContactNames( $defaults['address'] );
        foreach ( $defaults['address'] as $key => $addressValue ) {
            if ( CRM_Utils_Array::value( 'master_id', $addressValue ) && !$shareAddressContactNames[ $addressValue['master_id']]['is_deleted'] ) {
                $sharedAddresses[$key]['shared_address_display'] = array( 'address' => $addressValue['display'],
                    'name'    => $shareAddressContactNames[ $addressValue['master_id'] ]['name'] ); 
            }
        }
        $this->assign( 'sharedAddresses', $sharedAddresses );

        //get the current employer name
        if ( CRM_Utils_Array::value( 'contact_type', $defaults ) == 'Individual' ) {
            if ( $contact->employer_id && $contact->organization_name ) {
                $defaults['current_employer']    =  $contact->organization_name;
                $defaults['current_employer_id'] =  $contact->employer_id;
            }
            
            //for birthdate format with respect to birth format set 
            $this->assign( 'birthDateViewFormat',  CRM_Utils_Array::value( 'qfMapping', CRM_Utils_Date::checkBirthDateFormat( ) ) );
        }
        
        $this->assign( $defaults );
        
        // also assign the last modifed details
        require_once 'CRM/Core/BAO/Log.php';
        $lastModified =& CRM_Core_BAO_Log::lastModified( $this->_contactId, 'civicrm_contact' );
        $this->assign_by_ref( 'lastModified', $lastModified );
        
        $allTabs  = array( );
        $weight = 10;        
        
        $this->_viewOptions = CRM_Core_BAO_Preferences::valueOptions( 'contact_view_options', true );
        $changeLog = $this->_viewOptions['log'];
        $this->assign_by_ref( 'changeLog' , $changeLog );
        require_once 'CRM/Core/Component.php';
        $components = CRM_Core_Component::getEnabledComponents();

        foreach ( $components as $name => $component ) {
            if ( CRM_Utils_Array::value( $name, $this->_viewOptions ) &&
                 CRM_Core_Permission::access( $component->name ) ) {
                $elem = $component->registerTab();

                // FIXME: not very elegant, probably needs better approach
                // allow explicit id, if not defined, use keyword instead
                if( array_key_exists( 'id', $elem ) ) {
                    $i = $elem['id'];
                } else {
                    $i = $component->getKeyword();
                }
                $u = $elem['url'];
               
                //appending isTest to url for test soft credit CRM-3891. 
                //FIXME: hack ajax url.
                $q = "reset=1&snippet=1&force=1&cid={$this->_contactId}"; 
                if ( CRM_Utils_Request::retrieve('isTest', 'Positive', $this) ) {
                    $q = $q."&isTest=1";
                }                
                $allTabs[] = array( 'id'     =>  $i,
                                    'url'    => CRM_Utils_System::url( "civicrm/contact/view/$u", $q ),
                                    'title'  => $elem['title'],
                                    'weight' => $elem['weight'],
                                    'count'  => CRM_Contact_BAO_Contact::getCountComponent( $u, $this->_contactId ) );
                // make sure to get maximum weight, rest of tabs go after
                // FIXME: not very elegant again
                if( $weight < $elem['weight'] ) {
                    $weight = $elem['weight'];
                }
            }
        }
        
        $rest = array( 'activity'      => ts('Activities')    ,
                       'case'          => ts('Cases')         ,
                       'rel'           => ts('Relationships') ,
                       'group'         => ts('Groups')        ,
                       'note'          => ts('Notes')         ,
                       'tag'           => ts('Tags')          ,
                       'log'           => ts('Change Log')    ,
                       );

        $config = CRM_Core_Config::singleton( );
        if ( isset( $config->sunlight ) &&
             $config->sunlight ) {
            $title = ts('Elected Officials');
            $rest['sunlight'] = $title;
            $this->_viewOptions[$title] = true;
        }
        
        foreach ( $rest as $k => $v ) {
            if ( CRM_Utils_Array::value($k, $this->_viewOptions) ) {
                $allTabs[] = array( 'id'     =>  $k,
                                    'url'    => CRM_Utils_System::url( "civicrm/contact/view/$k",
                                                                       "reset=1&snippet=1&cid={$this->_contactId}" ),
                                    'title'  => $v,
                                    'weight' => $weight,
                                    'count'  => CRM_Contact_BAO_Contact::getCountComponent( $k, $this->_contactId ) );
                $weight += 10;
            }
        }
        
        // now add all the custom tabs
        $entityType   = $this->get('contactType');
        $activeGroups = CRM_Core_BAO_CustomGroup::getActiveGroups( $entityType,
                                                                   'civicrm/contact/view/cd',
                                                                   $this->_contactId );
                                             
        foreach ( $activeGroups as $group ) {
            $id = "custom_{$group['id']}";
            $allTabs[] = array( 'id'     => $id,
                                'url'    => CRM_Utils_System::url( $group['path'], $group['query'] . "&snippet=1&selectedChild=$id"),
                                'title'  => $group['title'],
                                'weight' => $weight,
								'count'  => CRM_Contact_BAO_Contact::getCountComponent( $id, $this->_contactId, $group['table_name'] )  );
            $weight += 10;
        }

        // see if any other modules want to add any tabs
        require_once 'CRM/Utils/Hook.php';
        CRM_Utils_Hook::tabs( $allTabs, $this->_contactId );

        // now sort the tabs based on weight
        require_once 'CRM/Utils/Sort.php';
        usort( $allTabs, array( 'CRM_Utils_Sort', 'cmpFunc' ) );

        $this->assign( 'allTabs'     , $allTabs     );
     
        $selectedChild = CRM_Utils_Request::retrieve( 'selectedChild', 'String', $this, false, 'summary' );
        $this->assign( 'selectedChild', $selectedChild );
        
        // hook for contact summary
        require_once 'CRM/Utils/Hook.php';
        $contentPlacement = CRM_Utils_Hook::SUMMARY_BELOW;  // ignored but needed to prevent warnings
        CRM_Utils_Hook::summary( $this->_contactId, $content, $contentPlacement );
        if ( $content ) {
            $this->assign_by_ref( 'hookContent', $content );
            $this->assign( 'hookContentPlacement', $contentPlacement );
        }
    }

    function getTemplateFileName() {
        if ( $this->_contactId ) {
            $csType = $this->get('contactSubtype');
            if ( $csType ) {
                $templateFile = "CRM/Contact/Page/View/SubType/{$csType}.tpl";
                $template     = CRM_Core_Page::getTemplate( );
                if ( $template->template_exists( $templateFile ) ) {
                    return $templateFile;
                }
            }
        }
        return parent::getTemplateFileName( );
    }
}
