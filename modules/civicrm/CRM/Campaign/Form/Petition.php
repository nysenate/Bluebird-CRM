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
require_once 'CRM/Campaign/BAO/Survey.php';
require_once 'CRM/Campaign/Form/Survey.php';

/**
 * This class generates form components for adding a petition 
 * 
 */

class CRM_Campaign_Form_Petition extends CRM_Campaign_Form_Survey
{

    public function preProcess()
    {
    	parent::preProcess();
        if ( $this->_action & ( CRM_Core_Action::UPDATE | CRM_Core_Action::DELETE ) ) {
            $this->_surveyId = CRM_Utils_Request::retrieve('id', 'Positive', $this, true);

            if ( $this->_action & CRM_Core_Action::UPDATE ) {
                CRM_Utils_System::setTitle( ts('Edit Petition') ); 
            } else {
                CRM_Utils_System::setTitle( ts('Delete Petition') ); 
            }
        }
        
        $session = CRM_Core_Session::singleton();
        $url     = CRM_Utils_System::url('civicrm/campaign', 'reset=1&subPage=petition'); 
        $session->pushUserContext( $url );
        
        CRM_Utils_System::appendBreadCrumb( array( array( 'title' => ts('Petition Dashboard'), 'url' => $url ) ) );
    }

    /**
     * This function sets the default values for the form. Note that in edit/view mode
     * the default values are retrieved from the database
     * 
     * @param null
     * 
     * @return array    array of default values
     * @access public
     */
    function setDefaultValues()
    {
    	$defaults = parent::setDefaultValues();
    	
        require_once 'CRM/Core/BAO/UFJoin.php';
		$ufJoinParams = array( 'entity_table' => 'civicrm_survey',
					   'entity_id'    => $this->_surveyId,
					   'weight'       => 2);

		if ( $ufGroupId = CRM_Core_BAO_UFJoin::findUFGroupId( $ufJoinParams ) ) {
			$defaults['contact_profile_id'] = $ufGroupId;
		}

        return $defaults;
    
    }
    

    public function buildQuickForm()
    {

        if ( $this->_action & CRM_Core_Action::DELETE ) {
            
            $this->addButtons( array(
                                     array ( 'type'      => 'next',
                                             'name'      => ts('Delete'),
                                             'isDefault' => true   ),
                                     array ( 'type'      => 'cancel',
                                             'name'      => ts('Cancel') ),
                                     )
                               );
            return;
        }

        require_once 'CRM/Event/PseudoConstant.php';
        require_once 'CRM/Core/BAO/UFGroup.php';
        require_once 'CRM/Core/OptionGroup.php';
       
        $this->add('text', 'title', ts('Petition Title'), CRM_Core_DAO::getAttribute('CRM_Campaign_DAO_Survey', 'title'), true );

        $attributes = CRM_Core_DAO::getAttribute( 'CRM_Campaign_DAO_Survey' );
        
        $petitionTypeID = CRM_Core_OptionGroup::getValue( 'activity_type', 'petition',  'name' );
        $this->addElement( 'hidden', 'activity_type_id', $petitionTypeID );
//        $surveyActivityTypes = CRM_Campaign_BAO_Survey::getSurveyActivityType( );
        // Activity Type id
//        $this->add('select', 'activity_type_id', ts('Select Activity Type'), array( '' => ts('- select -') ) + $surveyActivityTypes, true );
        
        // script / instructions / description of petition purpose
        $this->addWysiwyg('instructions',ts('Introduction'), $attributes['instructions']);
        
        // Campaign id
        require_once 'CRM/Campaign/BAO/Campaign.php';
        $campaigns = CRM_Campaign_BAO_Campaign::getAllCampaign( );
        $this->add('select', 'campaign_id', ts('Campaign'), array( '' => ts('- select -') ) + $campaigns );

        $customContactProfiles = CRM_Core_BAO_UFGroup::getProfiles( array('Individual') );
        // custom group id
        $this->add('select', 'contact_profile_id', ts('Contact Profile'), 
                   array( '' => ts('- select -')) + $customContactProfiles, true );
        
        $customProfiles = CRM_Core_BAO_UFGroup::getProfiles( array('Activity') );
        // custom group id
        $this->add('select', 'profile_id', ts('Activity Profile'), 
                   array( '' => ts('- select -')) + $customProfiles );
                
        // is active ?
        $this->add('checkbox', 'is_active', ts('Is Active?'));
        
        // is default ?
        $this->add('checkbox', 'is_default', ts('Is Default?'));

        // add buttons
        $this->addButtons(array(
                                array ('type'      => 'next',
                                       'name'      => ts('Save'),
                                       'isDefault' => true),
                                array ('type'      => 'next',
                                       'name'      => ts('Save and New'),
                                       'subName'   => 'new'),
                                array ('type'      => 'cancel',
                                       'name'      => ts('Cancel')),
                                )
                          ); 
        
        // add a form rule to check default value
        $this->addFormRule( array( 'CRM_Campaign_Form_Survey', 'formRule' ),$this );

    }
    
    
    public function postProcess()
    {
        // store the submitted values in an array
        $params = $this->controller->exportValues( $this->_name );
               
        $session = CRM_Core_Session::singleton( );

        $params['last_modified_id'] = $session->get( 'userID' );
        $params['last_modified_date'] = date('YmdHis');

        if ( $this->_surveyId ) {

            if ( $this->_action & CRM_Core_Action::DELETE ) {
                CRM_Campaign_BAO_Survey::del( $this->_surveyId );
                CRM_Core_Session::setStatus(ts(' Petition has been deleted.'));
                $session->replaceUserContext( CRM_Utils_System::url('civicrm/campaign', 'reset=1&subPage=petition' ) ); 
                return;
            }

            $params['id'] = $this->_surveyId;

        } else { 
            $params['created_id']   = $session->get( 'userID' );
            $params['created_date'] = date('YmdHis');
        } 

        $params['is_active' ] = CRM_Utils_Array::value('is_active', $params, 0);
        $params['is_default'] = CRM_Utils_Array::value('is_default', $params, 0);

        $surveyId = CRM_Campaign_BAO_Survey::create( $params  );

        require_once 'CRM/Core/BAO/UFJoin.php';
        
        // also update the ProfileModule tables 
        $ufJoinParams = array( 'is_active'    => 1, 
                               'module'       => 'CiviCampaign',
                               'entity_table' => 'civicrm_survey', 
                               'entity_id'    => $surveyId->id );
        
        // first delete all past entries
        if ( $this->_surveyId ) {
            CRM_Core_BAO_UFJoin::deleteAll( $ufJoinParams );
        }    
        if ( CRM_Utils_Array::value('profile_id' , $params) ) {
            $ufJoinParams['weight'     ] = 1;
            $ufJoinParams['uf_group_id'] = $params['profile_id'];
            CRM_Core_BAO_UFJoin::create( $ufJoinParams ); 
        }

        if ( CRM_Utils_Array::value('contact_profile_id' , $params) ) {
            $ufJoinParams['weight'     ] = 2;
            $ufJoinParams['uf_group_id'] = $params['contact_profile_id'];
            CRM_Core_BAO_UFJoin::create( $ufJoinParams ); 
        }
        
        if( ! is_a( $surveyId, 'CRM_Core_Error' ) ) {
            CRM_Core_Session::setStatus(ts('Petition has been saved.'));
        }
        
        $buttonName = $this->controller->getButtonName( );
        if ( $buttonName == $this->getButtonName( 'next', 'new' ) ) {
            CRM_Core_Session::setStatus(ts(' You can add another Petition.'));
            $session->replaceUserContext( CRM_Utils_System::url('civicrm/petition/add', 'reset=1&action=add' ) );
        } else {
            $session->replaceUserContext( CRM_Utils_System::url('civicrm/campaign', 'reset=1&subPage=petition' ) ); 
        }
    }
    
}


?>
