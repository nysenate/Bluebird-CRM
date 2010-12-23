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

require_once 'CRM/Profile/Form.php';
/**
 * This class generates form components for custom data
 * 
 * It delegates the work to lower level subclasses and integrates the changes
 * back in. It also uses a lot of functionality with the CRM API's, so any change
 * made here could potentially affect the API etc. Be careful, be aware, use unit tests.
 *
  */
class CRM_Profile_Form_Edit extends CRM_Profile_Form
{
    protected $_postURL   = null;
    protected $_cancelURL = null;
    protected $_errorURL  = null;
    protected $_context;
    protected $_blockNo;

    /**
     * pre processing work done here.
     *
     * @param
     * @return void
     *
     * @access public
     *
     */
    function preProcess()
    {
        $this->_mode = CRM_Profile_Form::MODE_CREATE;

        //set the context for the profile
        $this->_context = CRM_Utils_Request::retrieve( 'context', 'String', $this );
        
        //set the block no
        $this->_blockNo = CRM_Utils_Request::retrieve( 'blockNo', 'String', $this );
            
        if ( $this->_context ) {
            $this->assign( 'context', $this->_context );
        }

        if ( $this->_blockNo ) {
            $this->assign( 'blockNo', $this->_blockNo );
        }

        if ( $this->get( 'skipPermission' ) ) {
            $this->_skipPermission = true;
        }

        if ( $this->get( 'edit' ) ) {
            //this is edit mode.
            $this->_mode = CRM_Profile_Form::MODE_EDIT;
            
            // make sure we have right permission to edit this user
            $session = CRM_Core_Session::singleton();
            $userID = $session->get( 'userID' );
            $id = CRM_Utils_Request::retrieve( 'id', 'Positive', $this, false, $userID );
            
            require_once 'CRM/Contact/BAO/Contact/Utils.php';
            if ( $id != $userID ) {
                // do not allow edit for anon users in joomla frontend, CRM-4668, unless u have checksum CRM-5228
                require_once 'CRM/Contact/BAO/Contact/Permission.php';
                $config = CRM_Core_Config::singleton( );
                if ( $config->userFrameworkFrontend ) {
                    CRM_Contact_BAO_Contact_Permission::validateOnlyChecksum( $id, $this );
                } else {
                    CRM_Contact_BAO_Contact_Permission::validateChecksumContact( $id, $this );
                }
                $this->_isPermissionedChecksum = true;
            }
        }

        parent::preProcess( );

        // make sure the gid is set and valid
        if ( ! $this->_gid ) {
            CRM_Core_Error::fatal( ts( 'The requested Profile (gid=%1) is disabled, OR there is no Profile with that ID, OR a valid \'gid=\' integer value is missing from the URL. Contact the site administrator if you need assistance.',
                                      array( 1 => $this->_gid )) );        
        }

        // and also the profile is of type 'Profile'
        $query = "
SELECT module
  FROM civicrm_uf_join
 WHERE module = 'Profile'
   AND uf_group_id = %1
";
        $params = array( 1 => array( $this->_gid, 'Integer' ) );
        $dao =& CRM_Core_DAO::executeQuery( $query, $params );
        if ( ! $dao->fetch( ) ) {
            CRM_Core_Error::fatal( ts( 'The requested Profile (gid=%1) is not configured to be used for \'Profile\' edit and view forms in its Settings. Contact the site administrator if you need assistance.',
                                      array( 1 => $this->_gid )) );        
        }
    }

    /**
     * Function to actually build the form
     *
     * @return void
     * @access public
     */
    public function buildQuickForm()
    {
        // add the hidden field to redirect the postProcess from
        require_once 'CRM/UF/Form/Group.php';
        require_once 'CRM/Core/DAO/UFGroup.php';
        $ufGroup = new CRM_Core_DAO_UFGroup( );
        
        $ufGroup->id = $this->_gid;
        if ( ! $ufGroup->find(true) ) {
            CRM_Core_Error::fatal( );
        }
        
        // set the title
        CRM_Utils_System::setTitle( $ufGroup->title );
        $this->assign( 'recentlyViewed', false );
        
        if ( $this->_context != 'dialog' ) {
            $this->_postURL   = CRM_Utils_Array::value( 'postURL', $_POST );
            $this->_cancelURL = CRM_Utils_Array::value( 'cancelURL', $_POST );

            $gidString = $this->_gid;
            if ( !empty( $this->_profileIds ) ) {
                $gidString = implode( ',', $this->_profileIds );
            }

            if ( ! $this->_postURL ) {
                $this->_postURL = $ufGroup->post_URL;
            }
            
            if ( ! $this->_postURL ) {
                if ( $this->_context == 'Search' ) {
                    $this->_postURL = CRM_Utils_System::url( 'civicrm/contact/search' );
                } elseif ( $this->_id && $this->_gid ) {
                   $this->_postURL = CRM_Utils_System::url('civicrm/profile/view',
                                                            "reset=1&id={$this->_id}&gid={$gidString}" );
                }
            }
            
            if ( ! $this->_cancelURL ) {
                if (  $ufGroup->cancel_URL ) {
                    $this->_cancelURL = $ufGroup->cancel_URL;
                } else {
                    $this->_cancelURL = CRM_Utils_System::url('civicrm/profile',
                                                              "reset=1&gid={$gidString}" );
                }
            }
            
            // we do this gross hack since qf also does entity replacement
            $this->_postURL   = str_replace( '&amp;', '&', $this->_postURL   );
            $this->_cancelURL = str_replace( '&amp;', '&', $this->_cancelURL );
            
            $this->addElement( 'hidden', 'postURL', $this->_postURL );
            if ( $this->_cancelURL ) {
                $this->addElement( 'hidden', 'cancelURL', $this->_cancelURL );
            }
            
            // also retain error URL if set
            $this->_errorURL = CRM_Utils_Array::value( 'errorURL', $_POST );
            if ( $this->_errorURL ) {
                // we do this gross hack since qf also does entity replacement 
                $this->_errorURL = str_replace( '&amp;', '&', $this->_errorURL ); 
                $this->addElement( 'hidden', 'errorURL', $this->_errorURL ); 
            }
            
            // replace the session stack in case user cancels (and we dont go into postProcess)
            $session = CRM_Core_Session::singleton(); 
            $session->replaceUserContext( $this->_postURL ); 
        }

        parent::buildQuickForm( );

        //get the value from session, this is set if there is any file
        //upload field
        $uploadNames = $this->get('uploadNames');
          
        if ( !empty($uploadNames) ) {
            $buttonName = 'upload'; 
        } else {
            $buttonName = 'next'; 
        }
        
        $buttons[] = array( 'type'      => $buttonName,
                            'name'      => ts('Save'),
                            'isDefault' => true);
        
        if ( $this->_context != 'dialog' ) {
            $buttons[] = array( 'type'      => 'cancel',
                                'name'      => ts('Cancel'),
                                'isDefault' => true);
        }

        $this->addButtons(  $buttons );
        
        $this->addFormRule( array( 'CRM_Profile_Form', 'formRule' ), $this );
    }
    
    /**
     * Process the user submitted custom data values.
     *
     * @access public
     * @return void
     */
    public function postProcess( ) 
    {
        parent::postProcess( );
        
        // this is special case when we create contact using Dialog box
        if ( $this->_context == 'dialog' )  {
            $sortName = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact', $this->_id, 'sort_name' );
            $returnArray = array( 'contactID'         => $this->_id,
                                  'sortName'          => $sortName,
                                  'newContactSuccess' => true );
                    
            echo json_encode( $returnArray );
            CRM_Utils_System::civiExit( );
        }

        CRM_Core_Session::setStatus(ts('Thank you. Your information has been saved.'));

        $session = CRM_Core_Session::singleton( );
        // only replace user context if we do not have a postURL
        if ( ! $this->_postURL  ) {
            $gidString = $this->_gid;
            if ( !empty( $this->_profileIds ) ) {
                $gidString = implode( ',', $this->_profileIds );
            }

            $url = CRM_Utils_System::url( 'civicrm/profile/view',
                                          "reset=1&id={$this->_id}&gid={$gidString}" );
        }

        $session->replaceUserContext( $url );
    }
    
    /**
     * Function to intercept QF validation and do our own redirection
     *
     * We use this to send control back to the user for a user formatted page
     * This allows the user to maintain the same state and display the error messages
     * in their own theme along with any modifications
     *
     * This is a first version and will be tweaked over a period of time
     *
     * @access    public                                                              
     * @return    boolean   true if no error found 
     */
    function validate( ) {
        $errors = parent::validate( );

        if ( ! $errors &&
             CRM_Utils_Array::value( 'errorURL', $_POST ) ) {
            $message = null;
            foreach ( $this->_errors as $name => $mess ) {
                $message .= $mess;
                $message .= '<p>';
            }
            
            if ( function_exists( 'drupal_set_message' ) ) {
                drupal_set_message( $message );
            }
            
            $message = urlencode( $message );

            $errorURL = $_POST['errorURL'];
            if ( strpos( $errorURL, '?' ) !== false ) {
                $errorURL .= '&';
            } else {
                $errorURL .= '?';
            }
            $errorURL .= "gid={$this->_gid}&msg=$message";
            CRM_Utils_System::redirect( $errorURL );
        }
        
        return $errors;
    }

}

