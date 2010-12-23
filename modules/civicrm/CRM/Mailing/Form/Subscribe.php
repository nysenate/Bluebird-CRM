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
require_once 'CRM/Mailing/Event/BAO/Subscribe.php';

class CRM_Mailing_Form_Subscribe extends CRM_Core_Form
{
    protected $_groupID = null;

    function preProcess( ) 
    { 
        parent::preProcess( );
        $this->_groupID = CRM_Utils_Request::retrieve( 'gid', 'Integer', $this,
                                                       false, null, 'REQUEST' );

        // ensure that there is a destination, if not set the destination to the
        // referrer string
        if ( ! $this->controller->getDestination( ) ) {
            $this->controller->setDestination( null, true );
        }

        require_once 'CRM/Contact/BAO/Group.php';

        if ( $this->_groupID ) {
            $groupTypeCondition = CRM_Contact_BAO_Group::groupTypeCondition( 'Mailing' );
            
            // make sure requested qroup is accessible and exists
            $query = "
SELECT   title, description
  FROM   civicrm_group
 WHERE   id={$this->_groupID}  
   AND   visibility != 'User and User Admin Only'
   AND   $groupTypeCondition";
            
            $dao = CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
            if ( $dao->fetch( ) ) {
                $this->assign( 'groupName', $dao->title );
                CRM_Utils_System::setTitle(ts('Subscribe to Mailing List - %1', array(1 => $dao->title)));
            } else {
                CRM_Core_Error::statusBounce( "The specified group is not configured for this action OR The group doesn't exist." );
            }

            $this->assign( 'single', true  );
        } else {
            $this->assign( 'single', false );
            CRM_Utils_System::setTitle(ts('Mailing List Subscription'));
        }
    }

    /**
     * Function to actually build the form
     *
     * @return None
     * @access public
     */

    public function buildQuickForm( ) 
    {
        // add the email address
        $this->add( 'text',
                    'email',
                    ts( 'Email' ),
                    CRM_Core_DAO::getAttribute('CRM_Core_DAO_Email',
                                               'email' ),
                    true );
        $this->addRule( 'email', ts("Please enter a valid email address (e.g. 'yourname@example.com')."), 'email' );

        if ( ! $this->_groupID ) {
            // create a selector box of all public groups
            $groupTypeCondition = CRM_Contact_BAO_Group::groupTypeCondition( 'Mailing' );

            $query = "
SELECT   id, title, description
  FROM   civicrm_group
 WHERE   ( saved_search_id = 0
    OR     saved_search_id IS NULL )
   AND   visibility != 'User and User Admin Only'
   AND   $groupTypeCondition
ORDER BY title";
            $dao = CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
            $rows   =  array( );
            while ( $dao->fetch( ) ) {
                $row                = array( );
                $row['id']          = $dao->id;
                $row['title']       = $dao->title;
                $row['description'] = $dao->description;
                $row['checkbox'   ] = CRM_Core_Form::CB_PREFIX . $row['id'];
                $this->addElement( 'checkbox',
                                   $row['checkbox'],
                                   null, null );
                $rows[] = $row;
            }
            if ( empty( $rows ) ) {
                CRM_Core_Error::fatal( ts( 'There are no public mailing list groups to display.' ) );
            }
            $this->assign( 'rows', $rows );
            $this->addFormRule( array( 'CRM_Mailing_Form_Subscribe', 'formRule' ) );
        }

        $addCaptcha = true;

        // if recaptcha is not set, then dont add it
        $config = CRM_Core_Config::singleton( );
        if ( empty( $config->recaptchaPublicKey ) ||
             empty( $config->recaptchaPrivateKey ) ) {
            $addCaptcha = false;
        } else {
            // if this is POST request and came from a block,
            // lets add recaptcha only if already present
            // gross hack for now
            if ( ! empty( $_POST ) &&
                 ! array_key_exists( 'recaptcha_challenge_field', $_POST ) ) {
                $addCaptcha = false;
            }
        }
        
        if ( $addCaptcha ) {
            // add captcha
            require_once 'CRM/Utils/ReCAPTCHA.php';
            $captcha =& CRM_Utils_ReCAPTCHA::singleton( );
            $captcha->add( $this );
        }
        
        $this->addButtons( array(
                                 array ( 'type'      => 'next',
                                         'name'      => ts('Subscribe'),
                                         'isDefault' => true   ),
                                 array ( 'type'       => 'cancel',
                                         'name'      => ts('Cancel') ),
                                 )
                                 
                           );
    }
    
    static function formRule( $fields ) {
        if ( $errors ) {
            return $errors;
        } else {
            foreach ( $fields as $name => $dontCare ) {
                if ( substr( $name, 0, CRM_Core_Form::CB_PREFIX_LEN ) == CRM_Core_Form::CB_PREFIX ) {
                    return true;
                }
            }
        }
        return array( '_qf_default' => 'Please select one or more mailing lists.' );
    }
    
    /**
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {    
        $params = $this->controller->exportValues( $this->_name );

        $groups = array( );
        if ( $this->_groupID ) {
            $groups[] = $this->_groupID;
        } else {
            foreach ( $params as $name => $dontCare ) {
                if ( substr( $name, 0, CRM_Core_Form::CB_PREFIX_LEN ) == CRM_Core_Form::CB_PREFIX ) {
                    $groups[] = substr( $name, CRM_Core_Form::CB_PREFIX_LEN );
                }
            }
        }
        
        CRM_Mailing_Event_BAO_Subscribe::commonSubscribe( $groups, $params );
    }
}

