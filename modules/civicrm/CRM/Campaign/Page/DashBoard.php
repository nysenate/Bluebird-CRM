<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 --------------------------------------------------------------------+
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
require_once 'CRM/Core/Permission.php';
require_once 'CRM/Campaign/PseudoConstant.php';
require_once 'CRM/Campaign/BAO/Survey.php';
require_once 'CRM/Campaign/BAO/Petition.php';
require_once 'CRM/Campaign/BAO/Campaign.php';

/**
 * Page for displaying Campaigns
 */
class CRM_Campaign_Page_DashBoard extends CRM_Core_Page 
{
    /**
     * The action links that we need to display for the browse screen
     *
     * @var array
     */
    private static $_campaignActionLinks;
    private static $_surveyActionLinks;
    private static $_petitionActionLinks;
    
    /**
     * Get the action links for this page.
     *
     * @return array $_campaignActionLinks
     *
     */
    function &campaignActionLinks( )
    {
        // check if variable _actionsLinks is populated
        if ( !isset( self::$_campaignActionLinks ) ) {
            $deleteExtra = ts('Are you sure you want to delete this Campaign?');
            self::$_campaignActionLinks = array(
                                                CRM_Core_Action::UPDATE  => array(
                                                                                  'name'  => ts('Edit'),
                                                                                  'url'   => 'civicrm/campaign/add',
                                                                                  'qs'    => 'reset=1&action=update&id=%%id%%',
                                                                                  'title' => ts('Update Campaign') 
                                                                                  ),
                                                CRM_Core_Action::DISABLE => array(
                                                                                  'name'  => ts('Disable'),
                                                                                  'title' => ts('Disable Campaign'),
                                                                                  'extra' => 'onclick = "enableDisable( %%id%%,\''. 'CRM_Campaign_BAO_Campaign' . '\',\'' . 'enable-disable' . '\' );"',
                                                                                  'ref'   => 'disable-action'
                                                                                  ),
                                                CRM_Core_Action::ENABLE  => array(
                                                                                  'name'  => ts('Enable'),
                                                                                  'title' => ts('Enable Campaign'),
                                                                                  'extra' => 'onclick = "enableDisable( %%id%%,\''. 'CRM_Campaign_BAO_Campaign' . '\',\'' . 'disable-enable' . '\' );"',
                                                                                  'ref'   => 'enable-action',
                                                                                  ),
                                                CRM_Core_Action::DELETE  => array(
                                                                                  'name'  => ts('Delete'),
                                                                                  'url'   => 'civicrm/campaign/add',
                                                                                  'qs'    => 'action=delete&reset=1&id=%%id%%',
                                                                                  'title' => ts('Delete Campaign'),
                                                                                  ),
                                                );
        }
        
        return self::$_campaignActionLinks;
    }
   

    function &surveyActionLinks( $activityType= null )
    {
        // check if variable _actionsLinks is populated
        if ( !isset( self::$_surveyActionLinks ) ) {
            self::$_surveyActionLinks = array(
                                              CRM_Core_Action::UPDATE  => array(
                                                                                'name'  => ts('Edit'),
                                                                                'url'   => 'civicrm/survey/add',
                                                                                'qs'    => 'action=update&id=%%id%%&reset=1',
                                                                                'title' => ts('Update Survey') 
                                                                                ),
                                              
                                              CRM_Core_Action::DISABLE => array(
                                                                                'name'  => ts('Disable'),
                                                                                'extra' => 'onclick = "enableDisable( %%id%%,\''. 'CRM_Campaign_BAO_Survey' . '\',\'' . 'enable-disable' . '\' );"',
                                                                                'ref'   => 'disable-action',
                                                                                'title' => ts('Disable Survey')
                                                                                ),
                                              
                                              CRM_Core_Action::ENABLE  => array(
                                                                                'name'  => ts('Enable'),
                                                                                'extra' => 'onclick = "enableDisable( %%id%%,\''. 'CRM_Campaign_BAO_Survey' . '\',\'' . 'disable-enable' . '\' );"',
                                                                                'ref'   => 'enable-action',
                                                                                'title' => ts('Enable Survey')
                                                                                ),
                                              
                                              CRM_Core_Action::DELETE  => array(
                                                                                'name'  => ts('Delete'),
                                                                                'url'   => 'civicrm/survey/add',
                                                                                'qs'    => 'action=delete&id=%%id%%&reset=1',
                                                                                'title' => ts('Delete Survey'),
                                                                                ) 
                                              );
             self::$_petitionActionLinks = self::$_surveyActionLinks;
             self::$_petitionActionLinks [CRM_Core_Action::UPDATE]  = array(
                                                                                'name'  => ts('Edit'),
                                                                                'url'   => 'civicrm/petition/add',
                                                                                'qs'    => 'action=update&id=%%id%%&reset=1',
                                                                                'title' => ts('Update Petition')
                                                                                );
             self::$_petitionActionLinks [CRM_Core_Action::DISABLE] = array(
                                                                                'name'  => ts('Disable'),
                                                                                'extra' => 'onclick = "enableDisable( %%id%%,\''. 'CRM_Campaign_BAO_Survey' . '\',\'' . 'enable-disable' . '\' );"',
                                                                                'ref'   => 'disable-action',
                                                                                'title' => ts('Disable Petition')
                                                                                );     
			self::$_petitionActionLinks [CRM_Core_Action::ENABLE]  = array(
                                                                                'name'  => ts('Enable'),
                                                                                'extra' => 'onclick = "enableDisable( %%id%%,\''. 'CRM_Campaign_BAO_Survey' . '\',\'' . 'disable-enable' . '\' );"',
                                                                                'ref'   => 'enable-action',
                                                                                'title' => ts('Enable Petition')
                                                                                );                                              
			self::$_petitionActionLinks [CRM_Core_Action::DELETE]  = array(
                                                                                'name'  => ts('Delete'),
                                                                                'url'   => 'civicrm/petition/add',
                                                                                'qs'    => 'action=delete&id=%%id%%&reset=1',
                                                                                'title' => ts('Delete Petition'),
                                                                                );                                                                             
             self::$_petitionActionLinks [CRM_Core_Action::PROFILE]  = array(
                                                                                'name'  => ts('Sign'),
                                                                                'url'   => 'civicrm/petition/sign',
                                                                                'qs'    => 'sid=%%id%%&reset=1',
                                                                                'title' => ts('Sign Petition')
                                                                                );//CRM_Core_Action::PROFILE is used because there isn't a specific action for sign
             self::$_petitionActionLinks [CRM_Core_Action::BROWSE]  = array(
                                                                                'name'  => ts('Signatures'),
                                                                                'url'   => 'civicrm/activity/search',
                                                                                'qs'    => 'survey=%%id%%&force=1',
                                                                                'title' => ts('List the signatures')
                                                                                );//CRM_Core_Action::PROFILE is used because there isn't a specific action for sign
        }
       
 
        if ($activityType == "Petition") {
          return self::$_petitionActionLinks;
        }
        return self::$_surveyActionLinks;
    }
    
    function browseCampaign( ) 
    {
        $campaignsData = array( );
        //get the campaigns.
        $campaigns = CRM_Campaign_BAO_Campaign::getCampaign( true );
        if ( !empty( $campaigns ) ) {
            $campaignType    = CRM_Campaign_PseudoConstant::campaignType( );
            $campaignStatus  = CRM_Campaign_PseudoConstant::campaignStatus( );
            $properties      = array( 'id', 'name', 'title', 'status_id', 'description', 
                                      'campaign_type_id', 'is_active', 'start_date', 'end_date' );
            foreach( $campaigns as $cmpid => $campaign ) { 
                foreach ( $properties as $prop ) {
                    $campaignsData[$cmpid][$prop] = CRM_Utils_Array::value( $prop, $campaign );
                }
                $statusId = CRM_Utils_Array::value( 'status_id', $campaign );
                $campaignsData[$cmpid]['status'       ] = CRM_Utils_Array::value( $statusId, $campaignStatus );
                $campaignsData[$cmpid]['campaign_id'  ] = $campaign['id'];
                $campaignsData[$cmpid]['campaign_type'] = $campaignType[$campaign['campaign_type_id']];
                
                $action = array_sum( array_keys( $this->campaignActionLinks( ) ) );
                if ( $campaign['is_active'] ) {
                    $action -= CRM_Core_Action::ENABLE;
                } else {
                    $action -= CRM_Core_Action::DISABLE;
                }
                $campaignsData[$cmpid]['action'] = CRM_Core_Action::formLink( self::campaignActionLinks( ), 
                                                                              $action, 
                                                                              array('id' => $campaign['id'] ) );
            }
        }
        
        $this->assign( 'campaigns',      $campaignsData );
        $this->assign( 'addCampaignUrl', CRM_Utils_System::url( 'civicrm/campaign/add', 'reset=1&action=add' ) );
    }
   
    function browsePetition () {
        $surveysData = array( );
        //get the survey.
        $surveys = CRM_Campaign_BAO_Petition::getPetition( true );
        if ( !empty( $surveys ) ) {
            $campaigns     = CRM_Campaign_BAO_Campaign::getAllCampaign( );
            $surveyType    = CRM_Campaign_BAO_Survey::getSurveyActivityType( );
            foreach( $surveys as $sid => $survey ) {
                $surveysData[$sid] = $survey;
                $camapignId = CRM_Utils_Array::value( 'campaign_id', $survey );
                $surveysData[$sid]['campaign_id']       = CRM_Utils_Array::value( $camapignId, $campaigns );
                $surveysData[$sid]['activity_type']     = $surveyType[$survey['activity_type_id']];
                $surveysData[$sid]['result_id']         = CRM_Utils_Array::value( 'result_id', $survey );
                if ( CRM_Utils_Array::value( 'release_frequency', $survey ) ) {
                    $surveysData[$sid]['release_frequency'] = $survey['release_frequency'].' Day(s)';
                }
                
                $action = array_sum( array_keys( $this->surveyActionLinks($surveysData[$sid]['activity_type']  ) ) );
                if ( $survey['is_active'] ) {
                    $action -= CRM_Core_Action::ENABLE;
                } else {
                    $action -= CRM_Core_Action::DISABLE;
                }
                $surveysData[$sid]['action'] = CRM_Core_Action::formLink( $this->surveyActionLinks($surveysData[$sid]['activity_type'] ), 
                                                                          $action, 
                                                                          array('id' => $sid ) );
                
                if ( CRM_Utils_Array::value('activity_type', $surveysData[$sid] ) != 'Petition' ) {
                    $surveysData[$sid]['voterLinks'] =  CRM_Campaign_BAO_Survey::buildPermissionLinks( $sid );
                }
            }
        }
      
        $this->assign( 'surveys',      $surveysData );
        $this->assign( 'addSurveyUrl', CRM_Utils_System::url( 'civicrm/petition/add', 'reset=1&action=add' ) );
    }
 
    function browseSurvey( ) 
    {
        $surveysData = array( );
        //get the survey.
        $surveys = CRM_Campaign_BAO_Survey::getSurvey( true );
        if ( !empty( $surveys ) ) {
            $campaigns     = CRM_Campaign_BAO_Campaign::getAllCampaign( );
            $surveyType    = CRM_Campaign_BAO_Survey::getSurveyActivityType( );
            foreach( $surveys as $sid => $survey ) {
                $surveysData[$sid] = $survey;
                $campaignId = CRM_Utils_Array::value( 'campaign_id', $survey );
                $surveysData[$sid]['campaign_id']       = CRM_Utils_Array::value( $campaignId, $campaigns );
                $surveysData[$sid]['activity_type']     = $surveyType[$survey['activity_type_id']];
                if ( CRM_Utils_Array::value( 'release_frequency', $survey ) ) {
                    $surveysData[$sid]['release_frequency'] = $survey['release_frequency'].' Day(s)';
                }
                
                $action = array_sum( array_keys( $this->surveyActionLinks($surveysData[$sid]['activity_type']  ) ) );
                if ( $survey['is_active'] ) {
                    $action -= CRM_Core_Action::ENABLE;
                } else {
                    $action -= CRM_Core_Action::DISABLE;
                }
                $surveysData[$sid]['action'] = CRM_Core_Action::formLink( $this->surveyActionLinks($surveysData[$sid]['activity_type'] ), 
                                                                          $action, 
                                                                          array('id' => $sid ) );
                
                if ( CRM_Utils_Array::value('activity_type', $surveysData[$sid] ) != 'Petition' ) {
                    $surveysData[$sid]['voterLinks'] =  CRM_Campaign_BAO_Survey::buildPermissionLinks( $sid );
                }
            }
        }
      
        $this->assign( 'surveys',      $surveysData );
        $this->assign( 'addSurveyUrl', CRM_Utils_System::url( 'civicrm/survey/add', 'reset=1&action=add' ) );
    }
    
    function browse( ) 
    {   
        $this->_tabs = array( 'campaign' => ts( 'Campaigns' ), 
                              'survey'   => ts( 'Surveys' ),
                              'petition' => ts ('Petitions')
                       );
        
        $subPageType = CRM_Utils_Request::retrieve( 'type', 'String', $this );
        if ( $subPageType ) {
            //load the data in tabs.
            $this->{'browse'.ucfirst( $subPageType )}( );
        } else {
            //build the tabs.
            $this->buildTabs( );
        }
        $this->assign( 'subPageType', $subPageType );
        
        //give focus to proper tab.
        $this->assign( 'selectedTabIndex', array_search( CRM_Utils_Array::value( 'subPage', $_GET, 'campaign' ), 
                                                         array_keys( $this->_tabs ) ) ); 
    }
    
    function run( ) 
    {
        require_once 'CRM/Campaign/BAO/Campaign.php';
        if ( !CRM_Campaign_BAO_Campaign::accessCampaignDashboard( ) ) {
            CRM_Utils_System::permissionDenied( );
        }
        
        $this->browse( );
        
        parent::run();
    }
    
    function buildTabs( ) 
    {        
        $allTabs = array( );
        foreach ( $this->_tabs as $name => $title ) {
            $allTabs[] = array( 'id'    => $name,
                                'title' => $title,
                                'url'   => CRM_Utils_System::url( 'civicrm/campaign', "reset=1&type=$name&snippet=1" ) );
        }
        
        $this->assign( 'allTabs', $allTabs );
    }
    
}

