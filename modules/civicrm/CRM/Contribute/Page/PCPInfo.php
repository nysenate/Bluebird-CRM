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

require_once 'CRM/Core/Page.php';
require_once 'CRM/Contribute/BAO/PCP.php';

/**
 * PCP Info Page - Summary about the PCP
 */
class CRM_Contribute_Page_PCPInfo extends CRM_Core_Page
{

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
        $session = CRM_Core_Session::singleton( );
        $config = CRM_Core_Config::singleton( );
        $permissionCheck = false;
        $statusMessage = '';
        if ( $config->userFramework != 'Joomla') {
            $permissionCheck = CRM_Core_Permission::check('administer CiviCRM');
        }
        //get the pcp id.
        $this->_id = CRM_Utils_Request::retrieve( 'id', 'Positive', $this, true );

        $action  = CRM_Utils_Request::retrieve( 'action', 'String'  , $this, false );
        
        $prms = array( 'id' => $this->_id );
        
        CRM_Core_DAO::commonRetrieve( 'CRM_Contribute_DAO_PCP', $prms, $pcpInfo );
        if ( empty( $pcpInfo ) ) {
            $statusMessage = ts( 'The personal campaign page you requested is currently unavailable.' );
            CRM_Core_Error::statusBounce( $statusMessage,
                                          $config->userFrameworkBaseURL );
        }

        CRM_Utils_System::setTitle($pcpInfo['title']);
        $this->assign('pcp', $pcpInfo );

        require_once 'CRM/Contribute/PseudoConstant.php';
        require_once 'CRM/Core/OptionGroup.php';
        $pcpStatus  = CRM_Contribute_PseudoConstant::pcpStatus( );
        $approvedId = CRM_Core_OptionGroup::getValue( 'pcp_status', 'Approved', 'name' );        
        
        // check if PCP is created by anonymous user
        $anonymousPCP = CRM_Utils_Request::retrieve( 'ap', 'Boolean', $this );
        if ( $anonymousPCP ) {
            $loginUrl = $config->userFrameworkBaseURL;
            
            switch ( ucfirst($config->userFramework) ) {
            case 'Joomla' : 
                $loginUrl  = str_replace( 'administrator/', '', $loginUrl );
                $loginUrl .= 'index.php?option=com_user&view=login';
                break;
                
            case 'Drupal' :
                $loginUrl .= 'user';
                break;
            }           
            
            $anonMessage = ts('Once you\'ve received your new account welcome email, you can <a href=%1>click here</a> to login and promote your campaign page.', array( 1 => $loginUrl) );
            CRM_Core_Session::setStatus( $anonMessage );
        } else {
           $statusMessage = ts( 'The personal campaign page you requested is currently unavailable. However you can still support the campaign by making a contribution here.' ); 
        }
                                          
        if ( $pcpInfo['status_id'] != $approvedId  || ! $pcpInfo['is_active'] ) {
            if (  $pcpInfo['contact_id'] != $session->get( 'userID' ) && ! $permissionCheck ) {
                CRM_Core_Error::statusBounce( $statusMessage, CRM_Utils_System::url( 'civicrm/contribute/transact',
                                                                                     "reset=1&id={$pcpInfo['contribution_page_id']}",
                                                                                     false, null, false, true ) );
            }        
        } else {
            $getStatus = CRM_Contribute_BAO_PCP::getStatus( $this->_id );
            if ( ! $getStatus ) {
                // PCP not enabled for this contribution page. Forward everyone to main contribution page
                CRM_Core_Error::statusBounce( $statusMessage, CRM_Utils_System::url( 'civicrm/contribute/transact',
                                                                                     "reset=1&id={$pcpInfo['contribution_page_id']}",
                                                                                     false, null, false, true ) );
            }
        }

        $default = array();
        
        CRM_Core_DAO::commonRetrieveAll( 'CRM_Contribute_DAO_ContributionPage', 'id', 
                                         $pcpInfo['contribution_page_id'], $default, array( 'start_date', 'end_date' ) );

        require_once "CRM/Contribute/PseudoConstant.php";
        $this->assign( 'pageName', CRM_Contribute_PseudoConstant::contributionPage( $pcpInfo['contribution_page_id'], true ) );

        if( $pcpInfo['contact_id'] == $session->get( 'userID' ) ) {
            $owner = $default[$pcpInfo['contribution_page_id']];
            $owner['status'] = CRM_Utils_Array::value( $pcpInfo['status_id'], $pcpStatus );
            $this->assign('owner', $owner );
            
            require_once 'CRM/Contribute/BAO/PCP.php';
            $link  = CRM_Contribute_BAO_PCP::pcpLinks( );
            unset($link['all'][CRM_Core_Action::ENABLE]);
            $hints = array(
                           CRM_Core_Action::UPDATE  => ts('Change the content and appearance of your page'),
                           CRM_Core_Action::DETACH  => ts('Send emails inviting your friends to support your campaign!'),
                           CRM_Core_Action::BROWSE  => ts('Update your personal contact information'),
                           CRM_Core_Action::DISABLE => ts('De-activate the page (you can re-activate it later)'),
                           CRM_Core_Action::DELETE  => ts('Remove the page (this cannot be undone!)'),
                           );
            CRM_Core_DAO::commonRetrieveAll( 'CRM_Contribute_DAO_PCPBlock', $pcpInfo['contribution_page_id'], 
                                             'entity_id', $blockValues, array('is_tellfriend_enabled') );
            
            $blockId = array_pop( $blockValues );
            $replace = array( 'id'    => $this->_id,
                              'block' => $blockId['id'] );
            if ( !CRM_Utils_Array::value( 'is_tellfriend_enabled', $blockId ) || 
                 CRM_Utils_Array::value( 'status_id', $pcpInfo )!= $approvedId ) {
                unset($link['all'][CRM_Core_Action::DETACH]);   
            }
            
            $this->assign( 'links'  , $link['all'] );
            $this->assign( 'hints'  , $hints       );
            $this->assign( 'replace', $replace     );
        }
        
        $honor = CRM_Contribute_BAO_PCP::honorRoll( $this->_id );
        
        if ( $file_id = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_EntityFile', $this->_id , 'file_id', 'entity_id') ) {
            $image = '<img src="'.CRM_Utils_System::url( 'civicrm/file', 
                                                         "reset=1&id=$file_id&eid={$this->_id}" ) . '" />';
            $this->assign('image', $image);
        }

        $totalAmount = CRM_Contribute_BAO_PCP::thermoMeter( $this->_id );
        $achieved    = round($totalAmount/$pcpInfo['goal_amount'] *100, 2);
        

        if ( $linkText = CRM_Contribute_BAO_PCP::getPcpBlockStatus( $pcpInfo['contribution_page_id'] ) ) {
            $linkTextUrl = CRM_Utils_System::url( 'civicrm/contribute/campaign',
                                                                      "action=add&reset=1&pageId={$pcpInfo['contribution_page_id']}",
                                                                      true, null, true,
                                                                      true );
            $this->assign( 'linkTextUrl', $linkTextUrl );
            $this->assign( 'linkText', $linkText );
        }
         
        $this->assign('honor', $honor );
        $this->assign('total', $totalAmount ? $totalAmount : '0.0' );
        $this->assign('achieved', $achieved <= 100 ? $achieved : 100 );

        if ( $achieved <= 100 ) {
            $this->assign('remaining', 100- $achieved );
        }
        // make sure that we are between  registration start date and registration end date
        $startDate = CRM_Utils_Date::unixTime( CRM_Utils_Array::value( 'start_date', $owner ) );

        $endDate = CRM_Utils_Date::unixTime( CRM_Utils_Array::value( 'end_date', $owner ) );

        $now = time( );
        $validDate = true;
        if ( $startDate && $startDate >= $now ) {
            $validDate = false;
        }
        if ( $endDate && $endDate < $now ) {
            $validDate = false;
        }
        
        $this->assign( 'validDate', true  );
        if ( $validDate ) {
            
            $contributionText = ts('Contribute Now');
            if ( CRM_Utils_Array::value('donate_link_text',$pcpInfo ) ) {
                $contributionText = $pcpInfo['donate_link_text'];
            }
                
            $this->assign( 'contributionText', $contributionText );
            
            // we always generate urls for the front end in joomla
            if ( $action == CRM_Core_Action::PREVIEW ) {
                $url    = CRM_Utils_System::url( 'civicrm/contribute/transact',
                                                 "id={$pcpInfo['contribution_page_id']}&pcpId={$this->_id}&reset=1&action=preview",
                                                 true, null, true,
                                                 true );
            } else {
                $url = CRM_Utils_System::url( 'civicrm/contribute/transact',
                                              "id={$pcpInfo['contribution_page_id']}&pcpId={$this->_id}&reset=1",
                                              true, null, true,
                                              true );
            }
            $this->assign( 'contributeURL', $url    );
        }
        
        // we do not want to display recently viewed items, so turn off
        $this->assign('displayRecent' , false );
        
        $single = $permission = false;
        switch ( $action ) {
        case CRM_Core_Action::BROWSE:
            $subForm = 'PCPAccount';
            $form    = "CRM_Contribute_Form_PCP_$subForm";
            $single  = true;
            break;
            
        case CRM_Core_Action::UPDATE:
            $subForm = 'Campaign';
            $form    = "CRM_Contribute_Form_PCP_$subForm";
            $single  = true;
            break;
        }

        $userID = $session->get('userID');
        //make sure the user has "administer CiviCRM" permission 
        //OR has created the PCP
        if ( CRM_Core_Permission::check( 'administer CiviCRM' ) ||
             ( $userID && (CRM_Core_DAO::getFieldValue('CRM_Contribute_DAO_PCP', $this->_id , 'contact_id') ==  $userID ) ) ) {
            $permission = true;
        }
        if ( $single && $permission ) {
            require_once 'CRM/Core/Controller/Simple.php';
            $controller = new CRM_Core_Controller_Simple( $form, $subForm, $action); 
            $controller->set('id', $this->_id);
            $controller->set('single', true );
            $controller->process(); 
            return $controller->run();
        }
        $session->pushUserContext( CRM_Utils_System::url(CRM_Utils_System::currentPath( ),'reset=1&id='.$this->_id ));
        parent::run();
    }
    
    function getTemplateFileName() 
    {
        if ( $this->_id ) {
            $templateFile = "CRM/Contribute/Page/{$this->_id}/PCPInfo.tpl";
            $template     =& CRM_Core_Page::getTemplate( );
            if ( $template->template_exists( $templateFile ) ) {
                return $templateFile;
            }
        }
        return parent::getTemplateFileName( );
    }


}

