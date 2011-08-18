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

require_once "CRM/Core/Form.php";
require_once "CRM/Core/BAO/CustomGroup.php";
require_once 'CRM/Core/BAO/File.php';
require_once 'CRM/Core/BAO/Preferences.php';
require_once "CRM/Contact/Form/Task.php";
require_once "CRM/Activity/BAO/Activity.php";
require_once "CRM/Custom/Form/CustomData.php";

/**
 * This class generates form components for Activity
 * 
 */
class CRM_Activity_Form_Activity extends CRM_Contact_Form_Task
{
    /**
     * The id of the object being edited / created
     *
     * @var int
     */
    public $_activityId;

    /**
     * The id of activity type 
     *
     * @var int
     */
    public $_activityTypeId;

    /**
     * The name of activity type 
     *
     * @var string
     */
    public $_activityTypeName;

    /**
     * The id of currently viewed contact
     *
     * @var int
     */
    public $_currentlyViewedContactId;

    /**
     * The id of source contact and target contact
     *
     * @var int
     */
    protected $_sourceContactId;
    protected $_targetContactId;
    protected $_asigneeContactId;
    
    protected $_single;
    
    public $_context;
    public $_compContext;
    public $_action;
    public $_activityTypeFile;

    /**
     * The id of the logged in user, used when add / edit 
     *
     * @var int
     */
    public $_currentUserId;

    /**
     * The array of form field attributes
     *
     * @var array
     */
    public $_fields;

    /**
     * The the directory inside CRM, to include activity type file from 
     *
     * @var string
     */
    protected $_crmDir = 'Activity';

    /*
     * Survey activity
     *
     * @var boolean
     */
    protected $_isSurveyActivity;
    
    protected $_values = array( );

    /**
     * The _fields var can be used by sub class to set/unset/edit the 
     * form fields based on their requirement  
     *
     */
    function setFields() 
    {
        $this->_fields = 
            array(
                  'subject'                  =>  array( 'type'        => 'text',
                                                        'label'       => ts('Subject'),
                                                        'attributes'  => CRM_Core_DAO::getAttribute( 'CRM_Activity_DAO_Activity', 
                                                                                                     'subject' ),
                                                        ),
                  'duration'                 =>  array( 'type'        => 'text',
                                                        'label'       => ts('Duration'),
                                                        'attributes'  => array( 'size'=> 4,'maxlength' => 8 ),
                                                        'required'    => false,
                                                        ),
                  'location'                 =>  array( 'type'       => 'text',
                                                        'label'      => ts('Location'),
                                                        'attributes' => 
                                                        CRM_Core_DAO::getAttribute( 'CRM_Activity_DAO_Activity', 
                                                                                    'location' ),
                                                        'required'   => false,
                                                        ),
                  'details'                  => array(  'type'       => 'wysiwyg',
                                                        'label'      => ts('Details'),
                                                        'attributes' => array( 'rows' => 4, 'cols' => 60 ), // forces a smaller edit window
                                                        'required'   => false, 
                                                        ),
                  'status_id'                 =>  array( 'type'       => 'select',
                                                         'label'      => ts('Status'),
                                                         'attributes' => 
                                                         CRM_Core_PseudoConstant::activityStatus( ),
                                                         'required'   => true, 
                                                         ),
                  'priority_id'               =>  array( 'type'       => 'select',
                                                         'label'      => ts('Priority'),
                                                         'attributes' => 
                                                         CRM_Core_PseudoConstant::priority( ),
                                                         'required'   => true,
                                                         ),
                  'source_contact_id'         =>  array( 'type'       => 'text',
                                                         'label'      => ts('Added By'),
                                                         'required'   => false,
                                                         ),
                  'followup_activity_type_id' =>  array( 'type'       => 'select',
                                                         'label'      => ts('Followup Activity'),
                                                         'attributes' => array( '' => '- '.ts('select activity').' -' ) +
                                                         CRM_Core_PseudoConstant::ActivityType( false ),
                                                         ),
                  'interval'                  =>  array( 'type'       => 'text',
                                                         'label'      => 'in',
                                                         'attributes' => 
                                                         array( 'size'=> 4,'maxlength' => 8 ),
                                                         ),
                  'interval_unit'             =>  array( 'type'       => 'select',
                                                         'label'      =>  null,
                                                         'attributes' => 
                                                         CRM_Core_OptionGroup::values( 'recur_frequency_units', 
                                                                                       false, false, false, 
                                                                                       null, 'name' ),
                                                         ),
                  // Add optional 'Subject' field for the Follow-up Activiity, CRM-4491
                  'followup_activity_subject' =>  array( 'type'       => 'text',
                                                         'label'      => ts('Subject'),
                                                         'attributes' => CRM_Core_DAO::getAttribute( 'CRM_Activity_DAO_Activity', 
                                                                                                     'subject' ),
                                                         ),
                  
                  );
        
        // append (s) for interval_unit attribute list
        foreach ( $this->_fields['interval_unit']['attributes'] as $name => $label ) {
            $this->_fields['interval_unit']['attributes'][$name] = $label . '(s)';
        }
        
        if ( ( $this->_context == 'standalone' ) &&
             ( $printPDF = CRM_Utils_Array::key( 'Print PDF Letter', $this->_fields['followup_activity_type_id']['attributes'] ) ) ) {
            unset( $this->_fields['followup_activity_type_id']['attributes'][$printPDF] );
        }
    }

    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    function preProcess( ) 
    {
        $this->_cdType     = CRM_Utils_Array::value( 'type', $_GET );
        $this->assign('cdType', false);
        if ( $this->_cdType ) {
            $this->assign('cdType', true);
            return CRM_Custom_Form_CustomData::preProcess( $this );
        }

        $this->_atypefile  = CRM_Utils_Array::value( 'atypefile', $_GET );
        $this->assign('atypefile', false);
        if ( $this->_atypefile ) {
            $this->assign('atypefile', true);
        }

        $this->_addAssigneeContact = CRM_Utils_Array::value( 'assignee_contact', $_GET );
        $this->assign('addAssigneeContact', false);
        if ( $this->_addAssigneeContact ) {
            $this->assign('addAssigneeContact', true);
        }

        $this->_addTargetContact = CRM_Utils_Array::value( 'target_contact', $_GET );
        $this->assign('addTargetContact', false);
        if ( $this->_addTargetContact ) {
            $this->assign('addTargetContact', true);
        }

        $session = CRM_Core_Session::singleton( );
        $this->_currentUserId = $session->get( 'userID' );
        
        $this->_currentlyViewedContactId = $this->get('contactId');
        if ( ! $this->_currentlyViewedContactId ) {
            $this->_currentlyViewedContactId = CRM_Utils_Request::retrieve( 'cid', 'Positive', $this );
        }
        $this->assign( 'contactId', $this->_currentlyViewedContactId );
        
        if ( $this->_currentlyViewedContactId ) {
            require_once 'CRM/Contact/Page/View.php';
            CRM_Contact_Page_View::setTitle( $this->_currentlyViewedContactId );
        }

        //give the context.
        if ( !isset($this->_context) ) {
            $this->_context = CRM_Utils_Request::retrieve( 'context', 'String', $this );
            require_once 'CRM/Contact/Form/Search.php';
            if ( CRM_Contact_Form_Search::isSearchContext( $this->_context ) ) {
                $this->_context = 'search';
            } else if ( !in_array( $this->_context, array( 'dashlet', 'dashletFullscreen' ) ) 
                        && $this->_currentlyViewedContactId ) {
                $this->_context = 'activity';
            }
            $this->_compContext = CRM_Utils_Request::retrieve( 'compContext', 'String', $this );
        }

        $this->assign( 'context', $this->_context );
        
        $this->_action = CRM_Utils_Request::retrieve('action', 'String', $this );
        
        if ( $this->_action & CRM_Core_Action::DELETE ) {
            if ( !CRM_Core_Permission::check( 'delete activities' ) ) {
                CRM_Core_Error::fatal( ts( 'You do not have permission to access this page' ) );  
            }
        }
        
        //CRM-6957
        //when we come from contact search, activity id never comes.
        //so don't try to get from object, it might gives you wrong one.
        
        // if we're not adding new one, there must be an id to
        // an activity we're trying to work on.
        if ( $this->_action != CRM_Core_Action::ADD && 
             get_class( $this->controller ) != 'CRM_Contact_Controller_Search' ) {
            $this->_activityId = CRM_Utils_Request::retrieve( 'id', 'Positive', $this );
        }
        
        $this->_activityTypeId = CRM_Utils_Request::retrieve( 'atype', 'Positive', $this );
        $this->assign( 'atype', $this->_activityTypeId );
        
        //check for required permissions, CRM-6264 
        require_once 'CRM/Case/BAO/Case.php';
        if ( $this->_activityId &&
             in_array( $this->_action, array( CRM_Core_Action::UPDATE, CRM_Core_Action::VIEW ) ) && 
             !CRM_Activity_BAO_Activity::checkPermission( $this->_activityId, $this->_action ) ) {
            CRM_Core_Error::fatal( ts( 'You do not have permission to access this page.' ) );
        }
        if ( ( $this->_action & CRM_Core_Action::VIEW ) &&
             CRM_Activity_BAO_Activity::checkPermission( $this->_activityId, CRM_Core_Action::UPDATE ) ) {
            $this->assign('permission', 'edit');
        }
        
        if ( !$this->_activityTypeId && $this->_activityId ) {
            $this->_activityTypeId = CRM_Core_DAO::getFieldValue( 'CRM_Activity_DAO_Activity',
                                                                  $this->_activityId,
                                                                  'activity_type_id' );
        }

        //Assigning Activity type name
        if ( $this->_activityTypeId ) {
            require_once 'CRM/Core/OptionGroup.php';
            $activityTName = CRM_Core_OptionGroup::values( 'activity_type', false, false, false, 'AND v.value = '.$this->_activityTypeId , 'name' );
            if ( $activityTName[$this->_activityTypeId] ) {
                $this->_activityTypeName = $activityTName[$this->_activityTypeId];
                $this->assign( 'activityTName', $activityTName[$this->_activityTypeId] );
            }
        }
        
        // Assign pageTitle to be "Activity - "+ activity name
        $pageTitle = 'Activity - '.CRM_Utils_Array::value( $this->_activityTypeId, $activityTName );
    	$this->assign( 'pageTitle', $pageTitle );

        //check the mode when this form is called either single or as
        //search task action
        if ( $this->_activityTypeId          || 
             $this->_context == 'standalone' || 
             $this->_currentlyViewedContactId ) { 
            $this->_single = true;
            $this->assign( 'urlPath', 'civicrm/activity' );
        } else {
            //set the appropriate action
            $url = CRM_Utils_System::currentPath( );
            $seachPath = array_pop( explode( '/', $url ) );
            $searchType = 'basic';
            $this->_action = CRM_Core_Action::BASIC;
            switch ( $seachPath ) {
            case 'basic' :
                $searchType = $seachPath;
                $this->_action = CRM_Core_Action::BASIC;
                break;
                
            case 'advanced' :
                $searchType = $seachPath;
                $this->_action = CRM_Core_Action::ADVANCED;
                break;
                
            case 'builder':
                $searchType = $seachPath;
                $this->_action = CRM_Core_Action::PROFILE;
                break;
                
            case 'custom':
                $this->_action = CRM_Core_Action::COPY;
                $searchType = $seachPath;
                break;
            }
            
            parent::preProcess( );
            $this->_single    = false;

            $this->assign( 'urlPath'   , "civicrm/contact/search/$searchType" );
            $this->assign( 'urlPathVar', "_qf_Activity_display=true&qfKey={$this->controller->_key}" ); 
        }
        
        $this->assign( 'single', $this->_single );
        $this->assign( 'action', $this->_action);
        
        if ( $this->_action & CRM_Core_Action::VIEW ) {
            // get the tree of custom fields
            $this->_groupTree =& CRM_Core_BAO_CustomGroup::getTree( 'Activity', $this,
                                                                    $this->_activityId, 0, $this->_activityTypeId );
        }

        if ( $this->_activityTypeId ) {
            //set activity type name and description to template
            require_once 'CRM/Core/BAO/OptionValue.php';
            list( $this->_activityTypeName, $activityTypeDescription ) = 
                CRM_Core_BAO_OptionValue::getActivityTypeDetails( $this->_activityTypeId );
            $this->assign( 'activityTypeName',        $this->_activityTypeName );
            $this->assign( 'activityTypeDescription', $activityTypeDescription );
        }
        
        // set user context
        $urlParams = $urlString = null;
        $qfKey = CRM_Utils_Request::retrieve( 'key', 'String', $this );
        
        //validate the qfKey
        require_once 'CRM/Utils/Rule.php';
        if ( !CRM_Utils_Rule::qfKey( $qfKey ) ) $qfKey = null;
        
        if ( $this->_context == 'fulltext' ) {
            $keyName   = '&qfKey';
            $urlParams = 'force=1';
            $urlString = 'civicrm/contact/search/custom';
            if ( $this->_action == CRM_Core_Action::UPDATE ) { 
                $keyName   = '&key';
                $urlParams .= '&context=fulltext&action=view';
                $urlString  = 'civicrm/contact/view/activity';
            }
            if ( $qfKey ) $urlParams .= "$keyName=$qfKey";
            $this->assign( 'searchKey',  $qfKey );
        } else if ( in_array( $this->_context, array( 'standalone', 'home', 'dashlet', 'dashletFullscreen' ) ) ) {
            $urlParams = 'reset=1';
            $urlString = 'civicrm/dashboard';
        } else if ( $this->_context == 'search' ) {
            $urlParams = 'force=1';
            if ( $qfKey ) $urlParams .= "&qfKey=$qfKey"; 
            if ( $this->_compContext == 'advanced' ) {
                $urlString = 'civicrm/contact/search/advanced';
            } else {
                $urlString = 'civicrm/activity/search';
            }
            $this->assign( 'searchKey',  $qfKey );
        } else if ( $this->_context != 'caseActivity' ) {
            $urlParams = "action=browse&reset=1&cid={$this->_currentlyViewedContactId}&selectedChild=activity";
            $urlString = 'civicrm/contact/view';
        }

        if ( $urlString ) {
            $session->pushUserContext( CRM_Utils_System::url( $urlString, $urlParams ) );
        }
        
        // hack to retrieve activity type id from post variables
        if ( ! $this->_activityTypeId ) {
            $this->_activityTypeId = CRM_Utils_Array::value( 'activity_type_id', $_POST );
        }

        // when custom data is included in this page
        if ( CRM_Utils_Array::value( 'hidden_custom', $_POST ) ) {
            // we need to set it in the session for the below code to work
            // CRM-3014
            //need to assign custom data subtype to the template
            $this->set( 'type'    , 'Activity' );
            $this->set( 'subType' , $this->_activityTypeId );
            $this->set( 'entityId', $this->_activityId );
            CRM_Custom_Form_CustomData::preProcess( $this );
            CRM_Custom_Form_CustomData::buildQuickForm( $this );
            CRM_Custom_Form_CustomData::setDefaultValues( $this );           
        }

        // add attachments part
        CRM_Core_BAO_File::buildAttachment( $this,
                                            'civicrm_activity',
                                            $this->_activityId );

        // figure out the file name for activity type, if any
        if ( $this->_activityTypeId   &&
             $this->_activityTypeFile = 
             CRM_Activity_BAO_Activity::getFileForActivityTypeId($this->_activityTypeId, $this->_crmDir) ) {
            
            require_once "CRM/{$this->_crmDir}/Form/Activity/{$this->_activityTypeFile}.php";
            $this->assign( 'activityTypeFile', $this->_activityTypeFile );
            $this->assign( 'crmDir', $this->_crmDir );
        }

        $this->setFields( );

        if ( $this->_activityTypeFile ) {
            eval("CRM_{$this->_crmDir}_Form_Activity_{$this->_activityTypeFile}::preProcess( \$this );");
        }
        
        $this->_values = $this->get( 'values' );
        if ( !is_array( $this->_values ) ) {
            $this->_values = array( );
            if ( isset( $this->_activityId ) && $this->_activityId ) {
                $params = array( 'id' => $this->_activityId );
                CRM_Activity_BAO_Activity::retrieve( $params, $this->_values );
            }
            $this->set( 'values', $this->_values );
        }
    }
    
    /**
     * This function sets the default values for the form. For edit/view mode
     * the default values are retrieved from the database
     * 
     * @access public
     * @return None
     */
    function setDefaultValues( ) 
    {
        if ( $this->_cdType ) {
            return CRM_Custom_Form_CustomData::setDefaultValues( $this );
        }
        
        $defaults = $this->_values;
        $params   = array( );
        $config   = CRM_Core_Config::singleton( );

        // if we're editing...
        if ( isset( $this->_activityId ) ) {
            $defaults['source_contact_qid'] = $defaults['source_contact_id'];
            $defaults['source_contact_id']  = $defaults['source_contact'];
            
            if ( !CRM_Utils_Array::crmIsEmptyArray( $defaults['target_contact'] ) ) {
                $target_contact_value = explode(';', trim($defaults['target_contact_value'] ) );
                $target_contact = array_combine( array_unique( $defaults['target_contact'] ), $target_contact_value );

                if ( $this->_action & CRM_Core_Action::VIEW ) {
                    $this->assign( 'target_contact', $target_contact );
                } else {    
                    $this->assign( 'target_contact', $this->formatContactValues( $target_contact ) );
                } 
            }
            
            if ( !CRM_Utils_Array::crmIsEmptyArray( $defaults['assignee_contact'] ) ) {
                $assignee_contact_value = explode(';', trim($defaults['assignee_contact_value'] ) );
                $assignee_contact = array_combine( $defaults['assignee_contact'], $assignee_contact_value );            
            
                if ( $this->_action & CRM_Core_Action::VIEW ) {
                    $this->assign( 'assignee_contact', $assignee_contact ); 
                } else {
                    $this->assign( 'assignee_contact', $this->formatContactValues( $assignee_contact ) ); 
                }
            }
            
            if ( !CRM_Utils_Array::value('activity_date_time', $defaults) ) {
                list( $defaults['activity_date_time'], $defaults['activity_date_time_time'] ) = CRM_Utils_Date::setDateDefaults( null, 'activityDateTime' );
            } else if ( $this->_action & CRM_Core_Action::UPDATE ){
            	$this->assign( 'current_activity_date_time', $defaults['activity_date_time'] );
                list( $defaults['activity_date_time'], 
                      $defaults['activity_date_time_time'] ) = CRM_Utils_Date::setDateDefaults( $defaults['activity_date_time'], 'activityDateTime' );
            }

            //set the assigneed contact count to template
            if ( !empty( $defaults['assignee_contact'] ) ) {
                $this->assign( 'assigneeContactCount', count( $defaults['assignee_contact'] ) );
            } else {
                $this->assign( 'assigneeContactCount', 1 );
            }

            //set the target contact count to template
            if ( !empty( $defaults['target_contact'] ) ) {
                $this->assign( 'targetContactCount', count( $defaults['target_contact'] ) );
            } else {
                $this->assign( 'targetContactCount', 1 );
            }      
            
            if ( $this->_context != 'standalone' )  {
                $this->assign( 'target_contact_value'  , 
                               CRM_Utils_Array::value( 'target_contact_value', $defaults ) );
                $this->assign( 'assignee_contact_value', 
                               CRM_Utils_Array::value( 'assignee_contact_value', $defaults ) );
                $this->assign( 'source_contact_value'  , 
                               CRM_Utils_Array::value( 'source_contact', $defaults ) );
            }

            // set default tags if exists
            require_once 'CRM/Core/BAO/EntityTag.php';
            $defaults['tag'] = CRM_Core_BAO_EntityTag::getTag( $this->_activityId, 'civicrm_activity' );
          
        } else {
            // if it's a new activity, we need to set default values for associated contact fields
            // since those are jQuery fields, unfortunately we cannot use defaults directly
            $this->_sourceContactId = $this->_currentUserId;
            $this->_targetContactId = $this->_currentlyViewedContactId;
            $target_contact = array( );
            
            $defaults['source_contact_id'] = self::_getDisplayNameById( $this->_sourceContactId );
            $defaults['source_contact_qid'] = $this->_sourceContactId;
            if ( $this->_context != 'standalone' && isset( $this->_targetContactId ) ) {
                $target_contact[$this->_targetContactId] = self::_getDisplayNameById( $this->_targetContactId );
            }
            
            $this->assign( 'target_contact', $this->formatContactValues( $target_contact ) ); 
            
            list( $defaults['activity_date_time'], $defaults['activity_date_time_time'] ) = CRM_Utils_Date::setDateDefaults( null, 'activityDateTime' );
        }

        if (  $this->_activityTypeId ) {
            $defaults['activity_type_id'] =  $this->_activityTypeId;
        }
        
        if ( $this->_action & ( CRM_Core_Action::DELETE | CRM_Core_Action::RENEW ) ) {
            $this->assign( 'delName', $defaults['subject'] );
        }
        
        if ( $this->_activityTypeFile ) {
            eval('$defaults += CRM_'.$this->_crmDir.'_Form_Activity_'. 
                 $this->_activityTypeFile . '::setDefaultValues($this);');
        }
        if ( ! CRM_Utils_Array::value( 'priority_id', $defaults ) ) {
            require_once 'CRM/Core/PseudoConstant.php';
            $priority = CRM_Core_PseudoConstant::priority( );
            $defaults['priority_id'] = array_search( 'Normal', $priority );
        }
        return $defaults;
    }
    /**
     * Function to format contact values before assigning to autocomplete widget
     *
     * @param array $contactNames associated array of contact name and ids
     * @return json encoded object
     * @private
     */  
    function formatContactValues( &$contactNames ) {
        //format target/assignee contact
        $formatContacts = array( );
        foreach( $contactNames as $id => $name ) {
            $formatContacts[] = array( 'id'   => $id,
                                       'name' => $name );
        }
    
        return json_encode( $formatContacts );
    }

    public function buildQuickForm( ) 
    {
        if ( $this->_action & ( CRM_Core_Action::DELETE | CRM_Core_Action::RENEW ) ) { 
            //enable form element (ActivityLinks sets this true)
            $this->assign( 'suppressForm', false );

            $button = ts('Delete');
            if (  $this->_action & CRM_Core_Action::RENEW ) {
                $button = ts('Restore');
            } 
            $this->addButtons(array( 
                                    array ( 'type'      => 'next', 
                                            'name'      => $button, 
                                            'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 
                                            'isDefault' => true   ), 
                                    array ( 'type'      => 'cancel', 
                                            'name'      => ts('Cancel'),
                                            )
                                     ));
            return;
        }
        
        if ( ! $this->_single && !empty($this->_contactIds) ) {
            $withArray          = array();
            require_once 'CRM/Contact/BAO/Contact.php';
            foreach ( $this->_contactIds as $contactId ) {
                $withDisplayName = self::_getDisplayNameById($contactId);
                $withArray[] = "\"$withDisplayName\" ";
            }
            $this->assign('with', implode(', ', $withArray));
        } 
        
        if ( $this->_cdType ) {
            return CRM_Custom_Form_CustomData::buildQuickForm( $this );
        }

        //build other activity links
        require_once 'CRM/Activity/Form/ActivityLinks.php';
        CRM_Activity_Form_ActivityLinks::buildQuickForm( );

        //enable form element (ActivityLinks sets this true)
        $this->assign( 'suppressForm', false );

        $element =& $this->add('select', 'activity_type_id', ts('Activity Type'),
                               $this->_fields['followup_activity_type_id']['attributes'],
                               false, array('onchange' => 
                                            "buildCustomData( 'Activity', this.value );") );

        //freeze for update mode.
        if ( $this->_action & CRM_Core_Action::UPDATE ) {
            $element->freeze( );
        }
       
        foreach ( $this->_fields as $field => $values ) {
            if( CRM_Utils_Array::value($field, $this->_fields ) ) {
                $attribute = null;
                if ( CRM_Utils_Array::value( 'attributes', $values ) ) {
                    $attribute = $values['attributes'];
                }
                
                $required = false;
                if ( CRM_Utils_Array::value( 'required', $values ) ) {
                    $required = true;
                }
                if ( $values['type'] == 'wysiwyg' ) {
                    $this->addWysiwyg( $field, $values['label'], $attribute, $required );
                } else {
                    $this->add($values['type'], $field, $values['label'], $attribute, $required );
                }
            }
        }
        
        //CRM-7362 --add campaigns.
        require_once 'CRM/Campaign/BAO/Campaign.php';
        CRM_Campaign_BAO_Campaign::addCampaign( $this, CRM_Utils_Array::value( 'campaign_id', $this->_values ) );
        
        //add engagement level CRM-7775
        $buildEngagementLevel = false;
        if ( CRM_Campaign_BAO_Campaign::isCampaignEnable( ) &&
             CRM_Campaign_BAO_Campaign::accessCampaign( ) ) {
            $buildEngagementLevel = true;
            require_once 'CRM/Campaign/PseudoConstant.php';
            $this->add( 'select', 'engagement_level', 
                        ts( 'Engagement Index' ), 
                        array( '' => ts( '- select -' ) ) + CRM_Campaign_PseudoConstant::engagementLevel( ) );
            $this->addRule( 'engagement_level', 
                            ts('Please enter the engagement index as a number (integers only).'), 
                            'positiveInteger');
        }
        $this->assign( 'buildEngagementLevel', $buildEngagementLevel );

        // check for survey activity
        $this->_isSurveyActivity = false;

        if ( $this->_activityId && CRM_Campaign_BAO_Campaign::isCampaignEnable( ) &&
             CRM_Campaign_BAO_Campaign::accessCampaign( ) ) {

            require_once 'CRM/Campaign/BAO/Survey.php';
            $this->_isSurveyActivity = CRM_Campaign_BAO_Survey::isSurveyActivity( $this->_activityId );
            if ( $this->_isSurveyActivity ) {
                $surveyId = CRM_Core_DAO::getFieldValue( 'CRM_Activity_DAO_Activity', 
                                                         $this->_activityId, 
                                                         'source_record_id' );
                $responseOptions = CRM_Campaign_BAO_Survey::getResponsesOptions( $surveyId );
                if ( $responseOptions ) {
                    $this->add( 'select', 'result', ts('Result'),
                                array( '' => ts('- select -') ) + array_combine( $responseOptions, $responseOptions ) );
                }
                $surveyTitle = null;
                if ( $surveyId ) {
                    $surveyTitle = CRM_Core_DAO::getFieldValue( 'CRM_Campaign_DAO_Survey', $surveyId, 'title' );
                }
                $this->assign( 'surveyTitle', $surveyTitle );
            }
        }
        $this->assign( 'surveyActivity', $this->_isSurveyActivity );
        
        $this->addRule('duration', 
                       ts('Please enter the duration as number of minutes (integers only).'), 'positiveInteger');  
        
        $this->addRule('interval', ts('Please enter the follow-up interval as a number (integers only).'), 
                       'positiveInteger');

        $this->addDateTime( 'activity_date_time', ts('Date'), true, array( 'formatType' => 'activityDateTime') );  
        
        //autocomplete url
        $dataUrl = CRM_Utils_System::url( "civicrm/ajax/rest",
                                          "className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1&context=activity&reset=1",
                                          false, null, false );
        $this->assign( 'dataUrl',$dataUrl );

        //tokeninput url
        $tokenUrl = CRM_Utils_System::url( "civicrm/ajax/checkemail",
                                           "noemail=1",
                                           false, null, false );
        $this->assign( 'tokenUrl', $tokenUrl );
        
        $admin = CRM_Core_Permission::check( 'administer CiviCRM' );
        //allow to edit sourcecontactfield field if context is civicase.
        if ( $this->_context == 'caseActivity' ) {
            $admin = true;
        }
        
        $this->assign('admin', $admin);

        $sourceContactField =& $this->add( $this->_fields['source_contact_id']['type'],
                                           'source_contact_id', 
                                           $this->_fields['source_contact_id']['label'],
                                           null, 
                                           $admin );
        $hiddenSourceContactField =& $this->add( 'hidden', 'source_contact_qid', '', array( 'id' => 'source_contact_qid') );
        $targetContactField   =& $this->add( 'text', 'target_contact_id', ts('target') );
        $assigneeContactField =& $this->add( 'text', 'assignee_contact_id', ts('assignee') );

        if ( $sourceContactField->getValue( ) ) {
            $this->assign( 'source_contact',  $sourceContactField->getValue( ) );
        } else if ( $this->_currentUserId ) {
            // we're setting currently LOGGED IN user as source for this activity
            $this->assign( 'source_contact_value', self::_getDisplayNameById($this->_currentUserId) );
        }

        //need to assign custom data type and subtype to the template
        $this->assign('customDataType',     'Activity');
        $this->assign('customDataSubType',  $this->_activityTypeId );
        $this->assign('entityID',           $this->_activityId );

        if ( $this->_targetContactId ) {
            $defaultTargetContactName = 
                CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact',
                                             $this->_targetContactId,
                                             'sort_name' );
            $this->assign( 'target_contact_value', $defaultTargetContactName );
        }
        
        require_once 'CRM/Core/BAO/Tag.php';
        $tags = CRM_Core_BAO_Tag::getTags( 'civicrm_activity' );
        
        if ( !empty($tags) ) { 
            $this->add('select', 'tag',  ts( 'Tags' ), $tags, false, 
                       array( 'id' => 'tags',  'multiple'=> 'multiple', 'title' => ts('- select -') ));
        }
        
        // we need to hide activity tagset for special activities
        $specialActivities = array( 'Open Case' );

        if ( !in_array( $this->_activityTypeName, $specialActivities ) ) {
            // build tag widget
            require_once 'CRM/Core/Form/Tag.php';
            $parentNames = CRM_Core_BAO_Tag::getTagSet( 'civicrm_activity' );
            CRM_Core_Form_Tag::buildQuickForm( $this, $parentNames, 'civicrm_activity', $this->_activityId, false, true );
        }
        
        // if we're viewing, we're assigning different buttons than for adding/editing
        if ( $this->_action & CRM_Core_Action::VIEW ) { 
            if ( isset( $this->_groupTree ) ) {
				CRM_Core_BAO_CustomGroup::buildCustomDataView( $this, $this->_groupTree );
            }
			$buttons = array( );
            // do check for permissions 
            require_once 'CRM/Case/BAO/Case.php';
            if ( CRM_Case_BAO_Case::checkPermission( $this->_activityId, 'File On Case', $this->_activityTypeId ) ) {
                $buttons[] = array ( 'type'      => 'cancel',
                                     'name'      => ts('File on case'),
                                     'subName'   => 'file_on_case',
                                     'js'        => array ('onClick' => "Javascript:fileOnCase( \"file\", $this->_activityId ); return false;" ),
                                     );
            }
            // form should be frozen for view mode
            $this->freeze( );
			
            $buttons[] = array ( 'type'      => 'cancel',
                                 'name'      => ts('Done') );
            
            $this->addButtons( $buttons );
        } else {
            $message = array( 'completed' => ts('Are you sure? This is a COMPLETED activity with the DATE in the FUTURE. Click Cancel to change the date / status. Otherwise, click OK to save.'),
                              'scheduled' => ts('Are you sure? This is a SCHEDULED activity with the DATE in the PAST. Click Cancel to change the date / status. Otherwise, click OK to save.') 
                             );
            $js = array( 'onclick' => "return activityStatus(". json_encode( $message ) .");" ); 
            $this->addButtons( array(
                                     array ( 'type'      => 'upload',
                                             'name'      => ts('Save'),
                                             'js'        => $js,
                                             'isDefault' => true   ),
                                     array ( 'type'      => 'cancel',
                                             'name'      => ts('Cancel') ),
                                     )
                               );
        }

        if ( $this->_activityTypeFile ) {
            eval("CRM_{$this->_crmDir}_Form_Activity_{$this->_activityTypeFile}::buildQuickForm( \$this );");
        }

        if ( $this->_activityTypeFile ) {
            eval('$this->addFormRule' . 
                 "(array('CRM_{$this->_crmDir}_Form_Activity_{$this->_activityTypeFile}', 'formrule'), \$this);");
        }

        $this->addFormRule( array( 'CRM_Activity_Form_Activity', 'formRule' ), $this );
    }

    /**  
     * global form rule  
     *  
     * @param array $fields  the input form values  
     * @param array $files   the uploaded files if any  
     * @param array $options additional user data  
     *  
     * @return true if no errors, else array of errors  
     * @access public  
     * @static  
     */  
    static function formRule( $fields, $files, $self ) 
    { 
        // skip form rule if deleting
        if  ( CRM_Utils_Array::value( '_qf_Activity_next_',$fields) == 'Delete' ) {
            return true;
        }
        $errors = array( );
        if ( ! $self->_single && ! $fields['activity_type_id']) {
            $errors['activity_type_id'] = ts('Activity Type is a required field');
        }
        
        //Activity type is mandatory if creating new activity, CRM-4515
        if ( array_key_exists( 'activity_type_id', $fields ) && 
             ! CRM_Utils_Array::value( 'activity_type_id', $fields ) ) {
            $errors['activity_type_id'] = ts('Activity Type is required field.');
        }
        //FIX me temp. comment
        // make sure if associated contacts exist
        require_once 'CRM/Contact/BAO/Contact.php';

        if ( $fields['source_contact_id'] && ! is_numeric($fields['source_contact_qid'])) {
            $errors['source_contact_id'] = ts('Source Contact non-existent!');
        }

        if ( CRM_Utils_Array::value( 'assignee_contact_id', $fields ) ) {
            foreach ( explode( ',', $fields['assignee_contact_id'] ) as $key => $id ) {
                if ( $id && ! is_numeric($id)) {
                    $nullAssignee[] = $id;  
                }
            }
            if ( !empty( $nullAssignee ) ) {
                $errors["assignee_contact_id"] = ts('Assignee Contact(s) "%1" does not exist.<br/>', 
                                                    array(1 => implode( ", ", $nullAssignee )));
            }
        }
        if ( CRM_Utils_Array::value( 'target_contact_id', $fields ) ) {
            foreach ( explode( ',', $fields['target_contact_id'] ) as $key => $id ) {
                if ( $id && ! is_numeric($id)) {
                    $nullTarget[] = $id; 
                }
            }
            if ( !empty( $nullTarget ) ){
                $errors["target_contact_id"] = ts('Target Contact(s) "%1" does not exist.', 
                                                  array(1 => implode( ", ", $nullTarget ) ));
            }
        }
        
        if ( CRM_Utils_Array::value( 'activity_type_id', $fields ) == 3 && 
             CRM_Utils_Array::value( 'status_id', $fields ) == 1 ) {
            $errors['status_id'] = ts('You cannot record scheduled email activity.');
        } else if ( CRM_Utils_Array::value( 'activity_type_id', $fields ) == 4 && 
                    CRM_Utils_Array::value( 'status_id', $fields ) == 1) {
            $errors['status_id'] = ts('You cannot record scheduled SMS activity.');
        }
        
        if ( CRM_Utils_Array::value( 'followup_activity_type_id', $fields ) && !CRM_Utils_Array::value( 'interval', $fields ) ) {
            $errors['interval'] = ts('Interval is a required field.');
        }
        //Activity type is mandatory if subject is specified for an Follow-up activity, CRM-4515
        if ( CRM_Utils_Array::value( 'followup_activity_subject',$fields ) && 
             ! CRM_Utils_Array::value( 'followup_activity_type_id', $fields ) ) {
            $errors['followup_activity_subject'] = ts('Follow-up Activity type is a required field.');
        } 
        return $errors;
    }
    
    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess( $params = null ) 
    {
        if ( $this->_action & CRM_Core_Action::DELETE ) { 
            $deleteParams = array( 'id' => $this->_activityId );
            require_once 'CRM/Case/BAO/Case.php';
            $moveToTrash = CRM_Case_BAO_Case::isCaseActivity( $this->_activityId );
            CRM_Activity_BAO_Activity::deleteActivity( $deleteParams, $moveToTrash);

            // delete tags for the entity
            require_once 'CRM/Core/BAO/EntityTag.php';
            $tagParams = array( 'entity_table' => 'civicrm_activity',
                                'entity_id'    => $this->_activityId );
            CRM_Core_BAO_EntityTag::del( $tagParams );
            
            CRM_Core_Session::setStatus( ts("Selected Activity has been deleted sucessfully.") );
            return;
        }

        // store the submitted values in an array
        if ( ! $params ) {
            $params = $this->controller->exportValues( $this->_name );
        }

        //set activity type id
        if ( ! CRM_Utils_Array::value( 'activity_type_id', $params ) ) {
            $params['activity_type_id']   = $this->_activityTypeId;
        }
   
        if ( CRM_Utils_Array::value( 'hidden_custom', $params ) &&
             !isset($params['custom']) ) {
            $customFields     = 
                CRM_Core_BAO_CustomField::getFields( 'Activity', false, false, 
                                                     $this->_activityTypeId  );
            $customFields     = 
                CRM_Utils_Array::crmArrayMerge( $customFields, 
                                                CRM_Core_BAO_CustomField::getFields( 'Activity', false, false, 
                                                                                     null, null, true ) );
            $params['custom'] = CRM_Core_BAO_CustomField::postProcess( $params,
                                                                       $customFields,
                                                                       $this->_activityId,
                                                                       'Activity' );
        }

        // store the date with proper format
        $params['activity_date_time'] = CRM_Utils_Date::processDate( $params['activity_date_time'], $params['activity_date_time_time'] );

        // assigning formated value to related variable
        if ( CRM_Utils_Array::value( 'target_contact_id', $params ) ) {
            $params['target_contact_id'  ] = explode( ',', $params['target_contact_id'] );
        } else {
            $params['target_contact_id'] = array( );
        }

        if ( CRM_Utils_Array::value( 'assignee_contact_id', $params ) ) {
            $params['assignee_contact_id'] = explode( ',', $params['assignee_contact_id'] );
        } else {
            $params['assignee_contact_id'] = array( );
        }
        
        // get ids for associated contacts
        if ( ! $params['source_contact_id'] ) {
            $params['source_contact_id']   = $this->_currentUserId;
        } else {
            $params['source_contact_id'  ] = $this->_submitValues['source_contact_qid'];
        }

        if ( isset($this->_activityId) ) {
            $params['id'] = $this->_activityId;
        }

        // add attachments as needed
        CRM_Core_BAO_File::formatAttachment( $params,
                                             $params,
                                             'civicrm_activity',
                                             $this->_activityId );
                     
        // format target params
        if ( !$this->_single ) {
            $params['target_contact_id']   = $this->_contactIds;
        }

        $activityAssigned = array( );
        // format assignee params
        if ( !CRM_Utils_Array::crmIsEmptyArray($params['assignee_contact_id']) ) {
            //skip those assignee contacts which are already assigned
            //while sending a copy.CRM-4509.
            $activityAssigned = array_flip( $params['assignee_contact_id'] );
            if ( $this->_activityId ) {
                $assigneeContacts = CRM_Activity_BAO_ActivityAssignment::getAssigneeNames( $this->_activityId );
                $activityAssigned = array_diff_key( $activityAssigned, $assigneeContacts );
            }
        }

        // call begin post process. Idea is to let injecting file do
        // any processing before the activity is added/updated.
        $this->beginPostProcess( $params );

        $activity = CRM_Activity_BAO_Activity::create( $params );
        
        // add tags if exists
        $tagParams = array( );
        if ( !empty($params['tag']) ) {
            foreach( $params['tag'] as $tag ) {
                $tagParams[$tag] = 1;
            }
        }
        
        //save static tags
        require_once 'CRM/Core/BAO/EntityTag.php';
        CRM_Core_BAO_EntityTag::create( $tagParams, 'civicrm_activity',  $activity->id );
 
        //save free tags
        if ( isset( $params['activity_taglist'] ) && !empty( $params['activity_taglist'] ) ) {
            require_once 'CRM/Core/Form/Tag.php';
            CRM_Core_Form_Tag::postProcess( $params['activity_taglist'], $activity->id, 'civicrm_activity', $this );
        }
        
        // call end post process. Idea is to let injecting file do any
        // processing needed, after the activity has been added/updated.
        $this->endPostProcess( $params, $activity );

        // create follow up activity if needed
        $followupStatus = '';
        if ( CRM_Utils_Array::value('followup_activity_type_id', $params) ) {
            $followupActivity = CRM_Activity_BAO_Activity::createFollowupActivity( $activity->id, $params );
            $followupStatus = "A followup activity has been scheduled.";
        }

        // send copy to assignee contacts.CRM-4509
        $mailStatus = '';
        $config   =& CRM_Core_Config::singleton( );
        
        if ( !CRM_Utils_Array::crmIsEmptyArray($params['assignee_contact_id']) && $config->activityAssigneeNotification ) {
            $mailToContacts = array( );
            $assigneeContacts = CRM_Activity_BAO_ActivityAssignment::getAssigneeNames( $activity->id, true, false );
           
            //build an associative array with unique email addresses.  
            foreach( $activityAssigned as $id => $dnc ) {
                if( isset($id) && array_key_exists($id, $assigneeContacts) ) {
                    $mailToContacts[$assigneeContacts[$id]['email']] = $assigneeContacts[$id];
                }
            }
            
            if ( !CRM_Utils_array::crmIsEmptyArray($mailToContacts) ) {
                //include attachments while sendig a copy of activity.
                $attachments =& CRM_Core_BAO_File::getEntityFile( 'civicrm_activity', $activity->id );

                require_once "CRM/Case/BAO/Case.php";
                // CRM-8400 add param with _currentlyViewedContactId for URL link in mail
                $result = CRM_Case_BAO_Case::sendActivityCopy( null, $activity->id, $mailToContacts, $attachments, null );
                $mailStatus .= ts("A copy of the activity has also been sent to assignee contacts(s)."); 
            }
        }
        
        // set status message
        if ( CRM_Utils_Array::value('subject', $params) ) {
            $params['subject'] = "'".$params['subject']."'";
        }
        
        CRM_Core_Session::setStatus( ts('Activity %1 has been saved. %2. %3', 
                                        array( 1 => $params['subject'],
                                               2 => $followupStatus,
                                               3 => $mailStatus ) ) );
        
        return array( 'activity' => $activity );
    }

    /**
     * Shorthand for getting id by display name (makes code more readable)
     *
     * @access protected
     */
    protected function _getIdByDisplayName( $displayName ) 
    {
        return CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact',
                                            $displayName,
                                            'id',
                                            'sort_name' );
    }
    
    /**
     * Shorthand for getting display name by id (makes code more readable)
     *
     * @access protected
     */
    protected function _getDisplayNameById( $id ) 
    {
        return CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact',
                                            $id,
                                            'sort_name',
                                            'id' );
    }

    /**
     * Function to let injecting activity type file do any processing
     * needed, before the activity is added/updated
     *
     */
    function beginPostProcess( &$params ) 
    {
        if ( $this->_activityTypeFile ) {
            eval("CRM_{$this->_crmDir}_Form_Activity_{$this->_activityTypeFile}" . 
                 "::beginPostProcess( \$this, \$params );");
        }
    }

    /**
     * Function to let injecting activity type file do any processing
     * needed, after the activity has been added/updated
     *
     */
    function endPostProcess( &$params, &$activity ) 
    {
        if ( $this->_activityTypeFile ) {
            eval("CRM_{$this->_crmDir}_Form_Activity_{$this->_activityTypeFile}" . 
                 "::endPostProcess( \$this, \$params, \$activity );");
        }
    }
}
