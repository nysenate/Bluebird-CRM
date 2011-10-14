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

require_once 'CRM/Admin/Form/Setting.php';
require_once 'CRM/Utils/Mail.php';
require_once "CRM/Core/BAO/Preferences.php";
/**
 * This class generates form components for Smtp Server
 * 
 */
class CRM_Admin_Form_Setting_Smtp extends CRM_Admin_Form_Setting
{
    protected $_testButtonName;

    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) {
        
        $outBoundOption = array( '3' => ts('mail()'), '0' => ts('SMTP'), '1' => ts('Sendmail'), '2' => ts('Disable Outbound Email') );        
        $this->addRadio('outBound_option', ts('Select Mailer'),  $outBoundOption );

        CRM_Utils_System::setTitle(ts('Settings - Outbound Mail'));
        $this->add('text','sendmail_path', ts('Sendmail Path'));
        $this->add('text','sendmail_args', ts('Sendmail Argument'));
        $this->add('text','smtpServer', ts('SMTP Server'));
        $this->add('text','smtpPort', ts('SMTP Port'));  
        $this->addYesNo( 'smtpAuth', ts( 'Authentication?' ));
        $this->addElement('text','smtpUsername', ts('SMTP Username')); 
        $this->addElement('password','smtpPassword', ts('SMTP Password')); 

        $this->_testButtonName = $this->getButtonName( 'refresh', 'test' );

        $this->add('submit',$this->_testButtonName, ts('Save & Send Test Email') ); 

        $this->addFormRule( array( 'CRM_Admin_Form_Setting_Smtp', 'formRule' ));
        parent::buildQuickForm();
    }

    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess() {
        $formValues   = $this->controller->exportValues($this->_name);

        $buttonName = $this->controller->getButtonName( );
        // check if test button
        if ( $buttonName == $this->_testButtonName ) {
            if ( $formValues['outBound_option'] == 2 ) {
                CRM_Core_Session::setStatus( ts('You have selected "Disable Outbound Email". A test email can not be sent.') );
            } else {
                $session = CRM_Core_Session::singleton( );
                $userID  =  $session->get( 'userID' );
                require_once 'CRM/Contact/BAO/Contact.php';
                list( $toDisplayName, $toEmail, $toDoNotEmail ) = CRM_Contact_BAO_Contact::getContactDetails( $userID );

                //get the default domain email address.CRM-4250
                require_once 'CRM/Core/BAO/Domain.php';
                list( $domainEmailName, $domainEmailAddress ) = CRM_Core_BAO_Domain::getNameAndEmail( );

                if ( !$domainEmailAddress || $domainEmailAddress == 'info@FIXME.ORG' ) {
                    require_once 'CRM/Utils/System.php';
                    $fixUrl = CRM_Utils_System::url("civicrm/admin/domain", 'action=update&reset=1');
                    CRM_Core_Error::fatal( ts( 'The site administrator needs to enter a valid \'FROM Email Address\' in <a href="%1">Administer CiviCRM &raquo; Configure &raquo; Domain Information</a>. The email address used may need to be a valid mail account with your email service provider.', array( 1 => $fixUrl ) ) );
                }
                
                if ( ! $toEmail ) {
                    CRM_Core_Error::statusBounce( ts('Cannot send a test email because your user record does not have a valid email address.' ));
                }
                
                if ( ! trim($toDisplayName) ) {
                    $toDisplayName = $toEmail;
                }
                
                $to   = '"' . $toDisplayName . '"' . "<$toEmail>";
                $from = '"' . $domainEmailName . '" <' . $domainEmailAddress . '>';
                $testMailStatusMsg = ts( 'Sending test email. FROM: %1 TO: %2.<br />', array( 1 => $domainEmailAddress, 2 => $toEmail ));

                $params = array( );
                if ($formValues['outBound_option'] == 0) {
                    $subject = "Test for SMTP settings";
                    $message = "SMTP settings are correct.";
                    
                    $params['host'] = $formValues['smtpServer'];
                    $params['port'] = $formValues['smtpPort'];
                    
                    if ( $formValues['smtpAuth'] ) {
                        $params['username'] = $formValues['smtpUsername'];
                        $params['password'] = $formValues['smtpPassword'];
                        $params['auth']     = true;
                    } else {
                        $params['auth']     = false;
                    }
                    $mailerName = 'smtp';
                } elseif ($formValues['outBound_option'] == 1) {
                    $subject = "Test for Sendmail settings";
                    $message = "Sendmail settings are correct.";
                    $params['sendmail_path'] = $formValues['sendmail_path'];
                    $params['sendmail_args'] = $formValues['sendmail_args'];
                    $mailerName = 'sendmail';
                } elseif ($formValues['outBound_option'] == 3) {
                    $subject = "Test for PHP mail settings";
                    $message = "mail settings are correct.";
                    $mailerName = 'mail';
                    
                } 

                $headers = array(   
                                 'From'                      => $from,
                                 'To'                        => $to,
                                 'Subject'                   => $subject,
                                 );
                
                $mailer =& Mail::factory( $mailerName, $params );
                
                CRM_Core_Error::ignoreException( );
                $result = $mailer->send( $toEmail, $headers, $message );
                if ( !is_a( $result, 'PEAR_Error' ) ) {
                    CRM_Core_Session::setStatus( $testMailStatusMsg . ts('Your %1 settings are correct. A test email has been sent to your email address.', array( 1 => strtoupper( $mailerName ) ) ) ); 
                } else {
                    $message = CRM_Utils_Mail::errorMessage ( $mailer, $result );
                    CRM_Core_Session::setStatus( $testMailStatusMsg . ts('Oops. Your %1 settings are incorrect. No test mail has been sent.', array(1 => strtoupper( $mailerName ) ) ) . $message );
                }
            }
        } 
        $mailingDomain = new CRM_Core_DAO_Preferences();
        $mailingDomain->domain_id  = CRM_Core_Config::domainID( );
        $mailingDomain->is_domain  = true;
        $mailingDomain->find(true);
        if ( $mailingDomain->mailing_backend ) {
            $values = unserialize( $mailingDomain->mailing_backend );
            require_once "CRM/Core/BAO/Setting.php";
            CRM_Core_BAO_Setting::formatParams( $formValues, $values );
        }
        
        // if password is present, encrypt it
        if ( ! empty( $formValues['smtpPassword'] ) ) {
            require_once 'CRM/Utils/Crypt.php';
            $formValues['smtpPassword'] = CRM_Utils_Crypt::encrypt( $formValues['smtpPassword'] );
        }
        
        $mailingDomain->mailing_backend = serialize( $formValues );
        $mailingDomain->save();
    }
    
    /**
     * global validation rules for the form
     *
     * @param   array  $fields   posted values of the form
     *
     * @return  array  list of errors to be posted back to the form
     * @static
     * @access  public
     */
    static function formRule( $fields ) 
    {
        if ($fields['outBound_option'] == 0) {
            if ( !$fields['smtpServer'] ) {
                $errors['smtpServer'] = 'SMTP Server name is a required field.';
            } 
            if ( !$fields['smtpPort'] ) {
                $errors['smtpPort'] = 'SMTP Port is a required field.';
            }
            if ( $fields['smtpAuth'] ) {
                if (!$fields['smtpUsername']){
                    $errors['smtpUsername'] = 'If your SMTP server requires authentication please provide a valid user name.';
                }
                if (!$fields['smtpPassword']) {
                    $errors['smtpPassword'] = 'If your SMTP server requires authentication, please provide a password.';
                }
            }
        }
        if ($fields['outBound_option'] == 1) {
            if ( !$fields['sendmail_path'] ) {
                $errors['sendmail_path'] = 'Sendmail Path is a required field.';
            } 
            if ( !$fields['sendmail_args'] ) {
                $errors['sendmail_args'] = 'Sendmail Argument is a required field.';
            }
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * This function sets the default values for the form.
     * default values are retrieved from the database
     * 
     * @access public
     * @return None
     */
    function setDefaultValues( ) 
    {
        if ( ! $this->_defaults ) {
            $this->_defaults = array( );

            require_once "CRM/Core/DAO/Preferences.php";
            $mailingDomain = new CRM_Core_DAO_Preferences();
            $mailingDomain->find(true);
            if ( $mailingDomain->mailing_backend ) {
                $this->_defaults = unserialize( $mailingDomain->mailing_backend );

                if ( ! empty( $this->_defaults['smtpPassword'] ) ) {
                    require_once 'CRM/Utils/Crypt.php';
                    $this->_defaults['smtpPassword'] = CRM_Utils_Crypt::decrypt( $this->_defaults['smtpPassword'] );
                }
            } else {
                if ( ! isset( $this->_defaults['smtpServer'] ) ) {
                    $this->_defaults['smtpServer'] = 'localhost';
                    $this->_defaults['smtpPort'  ] = 25;
                    $this->_defaults['smtpAuth'  ] = 0;
                }
                
                if ( ! isset( $this->_defaults['sendmail_path'] ) ) {
                    $this->_defaults['sendmail_path'] = '/usr/sbin/sendmail';
                    $this->_defaults['sendmail_args'] = '-i';
                }
            }
        }
        return $this->_defaults;
    }
}


