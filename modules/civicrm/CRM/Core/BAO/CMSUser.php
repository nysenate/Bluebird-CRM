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

/** 
 *  this file contains functions for synchronizing cms users with CiviCRM contacts
 */

require_once 'DB.php';

class CRM_Core_BAO_CMSUser  
{
    /**
     * Function for synchronizing cms users with CiviCRM contacts
     *  
     * @param NULL
     * 
     * @return void
     * 
     * @static
     * @access public
     */
    static function synchronize( ) 
    {
        //start of schronization code
        $config = CRM_Core_Config::singleton( );

        CRM_Core_Error::ignoreException( );
        $db_uf =& self::dbHandle( $config );

        if ( $config->userFramework == 'Drupal' ) { 
            $id   = 'uid'; 
            $mail = 'mail'; 
            $name = 'name';
        } else if ( $config->userFramework == 'Joomla' ) { 
            $id   = 'id'; 
            $mail = 'email'; 
            $name = 'name';
        } else { 
            CRM_Core_Error::fatal( "CMS user creation not supported for this framework" ); 
        } 

        set_time_limit(300);

        $sql   = "SELECT $id, $mail, $name FROM {$config->userFrameworkUsersTableName} where $mail != ''";
        $query = $db_uf->query( $sql );
        
        $user            = new StdClass( );
        $uf              = $config->userFramework;
        $contactCount    = 0;
        $contactCreated  = 0;
        $contactMatching = 0;
        while ( $row = $query->fetchRow( DB_FETCHMODE_ASSOC ) ) {
            $user->$id   = $row[$id];
            $user->$mail = $row[$mail];
            $user->$name = $row[$name];
            $contactCount++;
            if ($match = CRM_Core_BAO_UFMatch::synchronizeUFMatch( $user, $row[$id], $row[$mail], $uf, 1, null, true ) ) {
                $contactCreated++;
            } else {
                $contactMatching++;
            }
            if (is_object($match)) {
              $match->free();
            }
        }
        
        $db_uf->disconnect( );
        
        //end of schronization code
        $status = ts('Synchronize Users to Contacts completed.');
        $status .= ' ' . ts('Checked one user record.', array('count' => $contactCount, 'plural' => 'Checked %count user records.'));
        if ($contactMatching) {
            $status .= ' ' . ts('Found one matching contact record.', array('count' => $contactMatching, 'plural' => 'Found %count matching contact records.'));
        }
        $status .= ' ' . ts('Created one new contact record.', array('count' => $contactCreated, 'plural' => 'Created %count new contact records.'));
        CRM_Core_Session::setStatus($status, true);
        CRM_Utils_System::redirect( CRM_Utils_System::url( 'civicrm/admin', 'reset=1' ) );
    }

    /**
     * Function to create CMS user using Profile
     *
     * @param array  $params associated array 
     * @param string $mail email id for cms user
     *
     * @return int contact id that has been created
     * @access public
     * @static
     */
    static function create( &$params, $mail ) 
    {
        $config  = CRM_Core_Config::singleton( );
        
        $isDrupal = ucfirst($config->userFramework) == 'Drupal' ? TRUE : FALSE;
        $isJoomla = ucfirst($config->userFramework) == 'Joomla' ? TRUE : FALSE;

        if ( $isDrupal ) {
            $ufID = self::createDrupalUser( $params, $mail );
            if ( (variable_get('user_register', TRUE ) == 1) && !variable_get('user_email_verification', TRUE ) ) {
                $contact = array('email' => $params[$mail] );
                if ( self::userExists( $contact ) ) {
                    return $ufID;
                }
            }
        } elseif ( $isJoomla ) {            
            $ufID = self::createJoomlaUser( $params, $mail );           
        }

        if ( $ufID !== false &&
             isset( $params['contactID'] ) ) {
            // create the UF Match record
            $ufmatch                 = new CRM_Core_DAO_UFMatch( );
            $ufmatch->domain_id      =  CRM_Core_Config::domainID( );
            $ufmatch->uf_id          =  $ufID;
            $ufmatch->contact_id     =  $params['contactID'];
            $ufmatch->uf_name        =  $params[$mail];
            $ufmatch->save( );
            
            // Simulate user login by storing details in session.
            // Might break if we ever allow admins to create CMS users.
            // This allows anonymous creator of PCP to see their page after they create it.
            //$session = CRM_Core_Session::singleton();
            //$session->set( 'userID'  , $ufmatch->contact_id );
        }
        
        return $ufID;
    }

    /**
     * Function to create Form for CMS user using Profile
     *
     * @param object  $form
     * @param integer $gid id of group of profile
     * @param string $emailPresent true, if the profile field has email(primary)
     *
     * @access public
     * @static
     */ 
    static function buildForm ( &$form, $gid, $emailPresent, $action = CRM_Core_Action::NONE) 
    {                                    
        $config = CRM_Core_Config::singleton( );
        $showCMS = false;
        
        $isDrupal = ucfirst($config->userFramework) == 'Drupal' ? TRUE : FALSE;
        $isJoomla = ucfirst($config->userFramework) == 'Joomla' ? TRUE : FALSE;
        //if CMS is configured for not to allow creating new CMS user,
        //don't build the form,Fixed for CRM-4036
        if ( $isJoomla ) {
            $userParams = &JComponentHelper::getParams('com_users');
            if ( !$userParams->get('allowUserRegistration') ) {
                return false;
            }
        } else if ( $isDrupal && ! variable_get('user_register', TRUE ) ) {
            return false;
        }
        // if cms is drupal having version greater than equal to 5.1
        // we also need email verification enabled, else we dont do it
        // then showCMS will true
        if ( $isDrupal OR $isJoomla ) {
            if ( $gid ) {                                        
                $isCMSUser = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_UFGroup', $gid, 'is_cms_user' );
            } 
            // $cms is true when there is email(primary location) is set in the profile field.
            $session = CRM_Core_Session::singleton( );                         
            $userID  = $session->get( 'userID' );      
            $showUserRegistration = false;
            if ( $action ) { 
                $showUserRegistration = true;
            }elseif (!$action && !$userID ) { 
                $showUserRegistration = true;
            }

            if ( $isCMSUser && $emailPresent ) {                
                if ( $showUserRegistration ) {
                    if ( $isCMSUser != 2  ) {
                        $extra = array(
                                       'onclick' => "return showHideByValue('cms_create_account','','details','block','radio',false );"
                                       );
                        $form->addElement('checkbox', 'cms_create_account', ts('Create an account?'), null, $extra);
                        $required = false;       
                    }else {
                        $form->add('hidden', 'cms_create_account', 1 );
                        $required = true;
                    }

                    $form->assign( 'isCMS', $required );       
                    require_once 'CRM/Core/Action.php';
                    if( ! $userID || $action & CRM_Core_Action::PREVIEW || $action & CRM_Core_Action::PROFILE ) {     
                        $form->add('text', 'cms_name', ts('Username'), null, $required );
                        if ( ( $isDrupal && !variable_get('user_email_verification', TRUE ) ) OR ( $isJoomla ) ) {       
                            $form->add('password', 'cms_pass', ts('Password') );
                            $form->add('password', 'cms_confirm_pass', ts('Confirm Password') );
                            } 
                        
                        $form->addFormRule( array( 'CRM_Core_BAO_CMSUser', 'formRule' ), $form );
                    } 
                    $showCMS = true;
                } 
            }
            
        } 

        $loginUrl =  $config->userFrameworkBaseURL;
        if ( $isJoomla ) {
            $loginUrl  = str_replace( 'administrator/', '', $loginUrl );
            $loginUrl .= 'index.php?option=com_user&view=login';
        } elseif ( $isDrupal ) {
            $loginUrl .= 'user';
            // For Drupal we can redirect user to current page after login by passing it as destination.
            require_once 'CRM/Utils/System.php';
            $args = null;

            $id = $form->get( 'id' );
            if ( $id ) {
                $args .= "&id=$id";
            } else {
                $gid =  $form->get( 'gid' );
                if ( $gid ) {
                    $args .= "&gid=$gid";
                } else {
                     // Setup Personal Campaign Page link uses pageId
                     $pageId =  $form->get( 'pageId' );
                    if ( $pageId ) {
                        $args .= "&pageId=$pageId&action=add";
                    }
                }
            }
    
            if ( $args ) {
                // append destination so user is returned to form they came from after login
                $destination = CRM_Utils_System::currentPath( ) . "?reset=1" . $args;
                $loginUrl .= '?destination=' . urlencode( $destination );
             }
        }
        $form->assign( 'loginUrl', $loginUrl );
        $form->assign( 'showCMS', $showCMS ); 
    } 
    
    static function formRule( $fields, $files, $self ) {
        if ( ! CRM_Utils_Array::value( 'cms_create_account', $fields ) ) {
            return true;
        }

        $config  = CRM_Core_Config::singleton( );
            
        $isDrupal = ucfirst($config->userFramework) == 'Drupal' ? TRUE : FALSE;
        $isJoomla = ucfirst($config->userFramework) == 'Joomla' ? TRUE : FALSE;

        $errors = array( );
        if ( $isDrupal || $isJoomla ) {
            $emailName = null;
            if ( ! empty( $self->_bltID ) ) {
                // this is a transaction related page
                $emailName = 'email-' . $self->_bltID;
            } else {
                // find the email field in a profile page
                foreach ( $fields as $name => $dontCare ) {
                    if(substr( $name, 0, 5 ) == 'email' ) {
                        $emailName = $name;
                        break;
                    }
                }
            }
                
            if ( $emailName == null ) {
                $errors['_qf_default'] == ts( 'Could not find an email address.' );
                return $errors;
            }

            if ( empty( $fields['cms_name'] ) ) {
                $errors['cms_name'] = ts( 'Please specify a username.' );
            }
                
            if ( empty( $fields[ $emailName ] ) ) {
                $errors[$emailName] = ts( 'Please specify a valid email address.' );
            }
                
            if ( ( $isDrupal && ! variable_get('user_email_verification', TRUE ) ) OR ( $isJoomla ) ) {
                if ( empty( $fields['cms_pass'] ) ||
                     empty( $fields['cms_confirm_pass'] ) ) {
                    $errors['cms_pass'] = ts( 'Please enter a password.' );
                }
                if ( $fields['cms_pass'] != $fields['cms_confirm_pass'] ) {
                    $errors['cms_pass'] = ts( 'Password and Confirm Password values are not the same.' );
                }
            }
                
            if ( ! empty( $errors ) ) {
                return $errors;
            }
                
            // now check that the cms db does not have the user name and/or email
            if ( $isDrupal OR $isJoomla ) {
                $params = array( 'name' => $fields['cms_name'],
                                 'mail' => $fields[$emailName] );
            }
                
            self::checkUserNameEmailExists( $params, $errors, $emailName );

        }           
        return ( ! empty( $errors ) ) ? $errors : true;
    }

    /**
     * Check if username and email exists in the drupal db
     * 
     * @params $params    array   array of name and mail values
     * @params $errors    array   array of errors
     * @params $emailName string  field label for the 'email'
     *
     * @return void
     * @static
     */
    static function checkUserNameEmailExists( &$params, &$errors, $emailName = 'email' )
    {
        $config  = CRM_Core_Config::singleton( );

        $isDrupal = ucfirst($config->userFramework) == 'Drupal' ? true : false;
        $isJoomla = ucfirst($config->userFramework) == 'Joomla' ? true : false;
        
        $dao = new CRM_Core_DAO( );
        $name  = $dao->escape( CRM_Utils_Array::value( 'name', $params ) );
        $email = $dao->escape( CRM_Utils_Array::value( 'mail', $params ) );


        if ( $isDrupal ) {
            _user_edit_validate(null, $params );
            $errors = form_get_errors( );
        
            if ( $errors ) {
                if ( CRM_Utils_Array::value( 'name', $errors ) ) {
                    $errors['cms_name'] = $errors['name'];
                } 
            
                if ( CRM_Utils_Array::value( 'mail', $errors ) ) {
                    $errors[$emailName] = $errors['mail'];
                } 
            
                // also unset drupal messages to avoid twice display of errors
                unset( $_SESSION['messages'] );
            }
        
            // drupal api sucks
            // do the name check manually
            $nameError = user_validate_name( $params['name'] );
            if ( $nameError ) {
                $errors['cms_name'] = $nameError;
            }
        
            $sql = "
SELECT name, mail
  FROM {$config->userFrameworkUsersTableName}
 WHERE (LOWER(name) = LOWER('$name')) OR (LOWER(mail) = LOWER('$email'))";
        } elseif ( $isJoomla ) {
            //don't allow the special characters and min. username length is two
            //regex \\ to match a single backslash would become '/\\\\/' 
            $isNotValid = (bool) preg_match('/[\<|\>|\"|\'|\%|\;|\(|\)|\&|\\\\|\/]/im', $name );
            if ( $isNotValid || strlen( $name ) < 2 ) {
                $errors['cms_name'] = ts("Your username contains invalid characters or is too short");
            }
            $sql = "
SELECT username, email
  FROM {$config->userFrameworkUsersTableName}
 WHERE (LOWER(username) = LOWER('$name')) OR (LOWER(email) = LOWER('$email'))
";
        }
        
        $db_cms = DB::connect($config->userFrameworkDSN);
        if ( DB::isError( $db_cms ) ) { 
            die( "Cannot connect to UF db via $dsn, " . $db_cms->getMessage( ) ); 
        }
        $query = $db_cms->query( $sql );
        $row = $query->fetchRow( );
        if ( !empty( $row ) ) {
            $dbName  = CRM_Utils_Array::value( 0, $row );
            $dbEmail = CRM_Utils_Array::value( 1, $row );
            if ( strtolower( $dbName ) == strtolower( $name ) ) {
                $errors['cms_name'] = ts( 'The username %1 is already taken. Please select another username.', 
                                          array( 1 => $name ) );
            }
            if ( strtolower( $dbEmail ) == strtolower( $email ) ) {
                $errors[$emailName] = ts( 'This email %1 is already registered. Please select another email.', 
                                          array( 1 => $email) );
            }
        }
    }
    
    /**
     * Function to check if a cms user already exists.
     *  
     * @param  Array $contact array of contact-details
     *
     * @return uid if user exists, false otherwise
     * 
     * @access public
     * @static
     */
    static function userExists( &$contact ) 
    {        
        $config = CRM_Core_Config::singleton( );

        $isDrupal = ucfirst($config->userFramework) == 'Drupal' ? true : false;
        $isJoomla = ucfirst($config->userFramework) == 'Joomla' ? true : false;
        
        $db_uf = DB::connect($config->userFrameworkDSN);
        
        if ( DB::isError( $db_uf ) ) { 
            die( "Cannot connect to UF db via $dsn, " . $db_uf->getMessage( ) ); 
        } 
        
        if ( !$isDrupal && !$isJoomla ) { 
            die( "Unknown user framework" ); 
        }
        
        if ( $isDrupal ) { 
            $id   = 'uid'; 
            $mail = 'mail';
        } elseif ( $isJoomla ) { 
            $id   = 'id'; 
            $mail = 'email';
        } 

        $sql   = "SELECT $id FROM {$config->userFrameworkUsersTableName} where $mail='" . $contact['email'] . "'";
        
        $query = $db_uf->query( $sql );
        
        if ( $row = $query->fetchRow( DB_FETCHMODE_ASSOC ) ) {
            $contact['user_exists'] = true;
            if ( $isDrupal ) {
                $result = $row['uid'];
            } elseif ( $isJoomla ) {
                $result = $row['id'];
            }
        } else {
            $result = false;
        }
        
        $db_uf->disconnect( );
        return $result;
    }
    
    /**
     * Function to create a user in Drupal.
     *  
     * @param array  $params associated array 
     * @param string $mail email id for cms user
     *
     * @return uid if user exists, false otherwise
     * 
     * @access public
     * @static
     */
    static function createDrupalUser( &$params, $mail )
    {
        $values['values']  = array (
                                    'name' => $params['cms_name'],
                                    'mail' => $params[$mail],
                                    'op'   => 'Create new account'
                                    );
        if ( !variable_get('user_email_verification', TRUE )) {
            $values['values']['pass']['pass1'] = $params['cms_pass'];
            $values['values']['pass']['pass2'] = $params['cms_pass'];
        }

        $config = CRM_Core_Config::singleton( );

        // we also need to redirect b
        $config->inCiviCRM = true;

        $res = drupal_execute( 'user_register', $values );
        
        $config->inCiviCRM = false;
        
        if ( form_get_errors( ) ) {
            return false;
        }

        // looks like we created a drupal user, lets make another db call to get the user id!
        $db_cms = DB::connect($config->userFrameworkDSN);
        if ( DB::isError( $db_cms ) ) { 
            die( "Cannot connect to UF db via $dsn, " . $db_cms->getMessage( ) ); 
        }

        //Fetch id of newly added user
        $id_sql   = "SELECT uid FROM {$config->userFrameworkUsersTableName} where name = '{$params['cms_name']}'";
        $id_query = $db_cms->query( $id_sql );
        $id_row   = $id_query->fetchRow( DB_FETCHMODE_ASSOC ) ;
        return $id_row['uid'];
    }

    /**
     * Function to create a user of Joomla.
     *  
     * @param array  $params associated array 
     * @param string $mail email id for cms user
     *
     * @return uid if user exists, false otherwise
     * 
     * @access public
     * @static
     */
    static function createJoomlaUser( &$params, $mail ) 
    {
        $userParams = &JComponentHelper::getParams('com_users');

        // get the default usertype
        $userType = $userParams->get('new_usertype');
        if ( !$usertype ) {
            $usertype = 'Registered';
        }

        $acl = &JFactory::getACL();

        // Prepare the values for a new Joomla! user.
        $values                 = array();
        $values['name']         = trim($params['cms_name']);
        $values['username']     = trim($params['cms_name']);
        $values['password']     = $params['cms_pass'];
        $values['password2']    = $params['cms_confirm_pass'];
        $values['email']        = trim($params[$mail]);
        $values['gid']          = $acl->get_group_id( '', $userType);
        $values['sendEmail']    = 0; 
        
        $useractivation = $userParams->get( 'useractivation' );
        if ( $useractivation == 1 ) { 
            jimport('joomla.user.helper');
            // block the User
            $values['block'] = 1; 
            $values['activation'] =JUtility::getHash( JUserHelper::genRandomPassword() ); 
        } else { 
            // don't block the user
            $values['block'] = 0; 
        }

        // Get an empty JUser instance.
        $user =& JUser::getInstance( 0 );
        $user->bind( $values );

        // Store the Joomla! user.
        if ( ! $user->save( ) ) {
            // Error can be accessed via $user->getError();
            return false;
        }
        //since civicrm don't have own tokens to use in user
        //activation email. we have to use com_user tokens, CRM-5809
        $lang =& JFactory::getLanguage();
        $lang->load( 'com_user' );
        require_once 'components/com_user/controller.php';
        UserController::_sendMail( $user, $user->password2 );
        return $user->get('id');
    }

    static function updateUFName( $ufID, $ufName ) 
    {
        $config = CRM_Core_Config::singleton( );
        
        if ( $config->userFramework == 'Drupal' ) {
            if ( function_exists( 'user_load' ) ) { // CRM-5555
                $user = user_load( array( 'uid' => $ufID ) );
                if ($user->mail != $ufName) {
                    user_save( $user, array( 'mail' => $ufName ) );
                    $user = user_load( array( 'uid' => $ufID ) );
                }
            }
        } else if ( $config->userFramework == 'Joomla' ) {
            $db_uf = self::dbHandle( $config );
            $ufID   = CRM_Utils_Type::escape( $ufID, 'Integer' );
            $ufName = CRM_Utils_Type::escape( $ufName, 'String' );

            $sql = "
UPDATE {$config->userFrameworkUsersTableName}
SET    email = '$ufName'
WHERE  id    = $ufID";

            $db_uf->query( $sql );
            $db_uf->disconnect( );
        }
    }

    static function &dbHandle( &$config ) {
        CRM_Core_Error::ignoreException( );
        $db_uf = DB::connect($config->userFrameworkDSN);
        CRM_Core_Error::setCallback();
        if ( ! $db_uf ||
             DB::isError( $db_uf ) ) { 
            $session = CRM_Core_Session::singleton( );
            $session->pushUserContext( CRM_Utils_System::url('civicrm/admin', 'reset=1' ) );
            CRM_Core_Error::statusBounce( ts( "Cannot connect to UF db via %1. Please check the CIVICRM_UF_DSN value in your civicrm.settings.php file",
                                              array( 1 => $db_uf->getMessage( ) ) ) );
        }
        $db_uf->query('/*!40101 SET NAMES utf8 */');
        return $db_uf;
    }

}

