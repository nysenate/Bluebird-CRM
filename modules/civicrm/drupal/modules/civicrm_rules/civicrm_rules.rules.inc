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
 | Version 3, 19 November 2007.                                       |
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

require_once 'utils.inc';

/**
 * Implementation of hook_rules_event_info().
 */
function civicrm_rules_event_info( ) {
    return array(
                 'contact_create' => array(
                                           'label'     => t( 'Contact has been created' ),
                                           'module'    => 'CiviCRM Contact',
                                           'arguments' => rules_events_hook_civicrm_arguments( t( 'Created contact' ),
                                                                                               'contact' ),
                                           ),
                 'contact_edit' => array(
                                         'label'     => t( 'Contact has been updated' ),
                                         'module'    => 'CiviCRM Contact',
                                         'arguments' => rules_events_hook_civicrm_arguments( t( 'Updated contact' ),
                                                                                             'contact' ),
                                         ),
                 'contact_view'   => array(
                                           'label'     => t( 'Contact has been viewed' ),
                                           'module'    => 'CiviCRM Contact',
                                           'arguments' => rules_events_hook_civicrm_arguments( t( 'Viewed contact' ),
                                                                                               'contact' ),
                                           ),
                 'contact_delete' => array(
                                           'label'     => t( 'Contact has been deleted' ),
                                           'module'    => 'CiviCRM Contact',
                                           'arguments' => rules_events_hook_civicrm_arguments( t( 'Deleted contact' ),
                                                                                               'contact' ),
                                           ),
                 'mailing_create' => array(
                                           'label'     => t( 'Mailing has been created' ),
                                           'module'    => 'CiviCRM Mailing',
                                           'arguments' => rules_events_hook_civicrm_arguments( t( 'Created mailing' ),
                                                                                               'mailing' ),
                                           ),
                 'mailing_edit' => array(
                                         'label'     => t( 'Mailing has been updated' ),
                                         'module'    => 'CiviCRM Mailing',
                                         'arguments' => rules_events_hook_civicrm_arguments( t( 'Updated Mailing' ),
                                                                                             'mailing' ),
                                         ),
                 'mailing_uploaded' => array(
                                             'label'     => t( 'Mailing content has been uploaded' ),
                                             'module'    => 'CiviCRM Mailing',
                                             'arguments' => rules_events_hook_civicrm_arguments( t( 'Mailing content uploaded' ),
                                                                                                 'mailing' ),
                                              ),
                 'mailing_scheduled' => array(
                                              'label'     => t( 'Mailing has been scheduled' ),
                                              'module'    => 'CiviCRM Mailing',
                                              'arguments' => rules_events_hook_civicrm_arguments( t( 'Scheduled mailing' ),
                                                                                                  'mailing' ),
                                              ),
                 'mailing_approved' => array(
                                              'label'     => t( 'Mailing has been approved/rejected' ),
                                              'module'    => 'CiviCRM Mailing',
                                              'arguments' => rules_events_hook_civicrm_arguments( t( 'Approved mailing' ),
                                                                                                  'mailing' ),
                                              ),
                 'mailing_inform'     => array(
                                               'label'     => t( 'Inform scheduler about the mailing' ),
                                               'module'    => 'CiviCRM Mailing',
                                               'arguments' => rules_events_hook_civicrm_arguments( t( 'Inform Mailing' ),
                                                                                                   'mailing' ),
                                             ),
                 'mailing_queued' => array(
                                           'label'     => t( 'Mailing has been queued' ),
                                           'module'    => 'CiviCRM Mailing',
                                           'arguments' => rules_events_hook_civicrm_arguments( t( 'Queued mailing' ),
                                                                                               'mailing' ),
                                           ),
                 'mailing_complete' => array(
                                             'label'     => t( 'Mailing has been completed' ),
                                             'module'    => 'CiviCRM Mailing',
                                             'arguments' => rules_events_hook_civicrm_arguments( t( 'Completed mailing' ),
                                                                                                 'mailing' ),
                                             )
                 );
}

/**
 * Returns some arguments suitable for hook_civicrm
 */
function rules_events_hook_civicrm_arguments( $label, $type = 'contact' ) {
	return
		array(
              $type   => array( 'type'  => $type,
                                'label' => $label )
              );
}

/**
 * Implementation of hook_rules_action_info().
 */
function civicrm_rules_action_info() {
	return 
        array(
              'rules_action_civicrm_add_to_group' => 
              array(
                    'label' => t('Add Contact to CiviCRM Group'),
                    'arguments' => array(
                                         'contact' => array('type' => 'contact',
                                                            'label' => t('Contact who should be added to the group')),
                                         ),
                    'module' => 'CiviCRM Contact',
                    ),

              'rules_action_civicrm_remove_from_group' =>
              array(
                    'label' => t('Remove Contact from CiviCRM Group'),
                    'arguments' => array(
                                         'contact' => array('type' => 'contact',
                                                            'label' => t('Contact who should be removed from the group')),
                                         ),
                    'module' => 'CiviCRM Contact',
                    ),

              'rules_action_civicrm_contact_send_email' => 
              array(
                    'label' => t('Send Email'),
                    'arguments' => array(
                                         'contact' => array('type' => 'contact',
                                                            'label' => t('Send Email'))
                                         ),
                    'module' => 'CiviCRM Contact',
                    ),

              'rules_action_civicrm_mailing_send_email' =>
              array(
                    'label' => t('Send Email'),
                    'arguments' => array(
                                         'mailing' => array('type' => 'mailing',
                                                            'label' => t('Send Email'))
                                         ),
                    'module' => 'CiviCRM Mailing',
                    )
              
              );
}

function rules_action_civicrm_mailing_send_email( $mailing, $settings ) {
    if( $mailing->id ) {
        require_once 'CRM/Utils/Token.php';
        $tokens['mailing'] = array( 'mailing.editUrl',
                                    'mailing.scheduleUrl', 
                                    'mailing.html', 
                                    'mailing.creatorEmail', 
                                    'mailing.creator' );
        $settings['toEmail'] = CRM_Utils_Token::replaceMailingTokens( $settings['to'],      $mailing, null, $tokens );
        $settings['html']    = CRM_Utils_Token::replaceMailingTokens( $settings['message'], $mailing, null, $tokens );
        $settings['subject'] = CRM_Utils_Token::replaceMailingTokens( $settings['subject'], $mailing, null, $tokens );

        CRM_Utils_Mail::send( $settings );
        return array('mailing' => $mailing );
    }
}

function rules_action_civicrm_mailing_send_email_form( $settings = array(), &$form ) {
    civicrm_initialize();
    //get the default from email address.
    require_once 'CRM/Core/OptionGroup.php';
    $defaultAddress = CRM_Core_OptionGroup::values( 'from_email_address', null, null, null, ' AND is_default = 1' );
    $settings['from'] = $defaultAddress[1];

    if ( !CRM_Utils_Array::value('subject', $settings ) )
    $settings['subject'] = 'New Email: {mailing.subject}';
    
    if ( !CRM_Utils_Array::value('message', $settings ) ) {
    $settings['message'] = '
A new mailing has been scheduled: {mailing.name}

{mailing.viewUrl}
You can review the mail here: {mailing.editUrl}
You can schedule the mail for here: {mailing.scheduleUrl}

The content of the email is:
<pre>
{mailing.html}
</pre>
';
    }

    rules_action_mail_form( $settings, $form );
}

function rules_action_civicrm_contact_send_email($contact, $settings) {
    if ( $contact->id ) {
        $params = array();
        $params['from'] = "Admin";
        $params['toEmail'] = $settings['send_email'];
        $params['subject'] = $settings['email_subject'];

        $text[] = "First Name  : $contact->first_name";
        $text[] = "Middle Name : $contact->middle_name";
        $text[] = "Last Name   : $contact->last_name";
        
        $params['text'] = implode("\n", $text);
        CRM_Utils_Mail::send($params);
        return array('contact' => $contact );
    }
}

/**
 * Define form for sending an email
 *
 */
function rules_action_civicrm_contact_send_email_form( $settings, &$form ) {
	$settings += array('send_email' => '' );
    $settings += array('email_subject' => '' );
	$form['settings']['send_email'] = array(
                                            '#type' => 'textfield',
                                            '#title' => t('Send an Email'),
                                            '#description' => t('Enter e-mail address of recipient. (Use a comma to separate multiple e-mail addresses.)'),
                                            '#default_value' => $settings['send_email'],
                                            );

    $form['settings']['email_subject'] = array(
                                               '#type' => 'textfield',
                                               '#title' => t('Message Subject'),
                                               '#description' => t('Enter subject for the email'),
                                               '#default_value' => $settings['email_subject'],
                                               );
}

/**
 * Define form for add to group configuration
 *
 */
function rules_action_civicrm_add_to_group_form( $settings, &$form ) {
	$settings += array('groups' => '' );
	
	$form['settings']['groups'] = array(
                                        '#type' => 'select',
                                        '#title' => t('Select the group'),
                                        '#options' => _civicrm_get_groups( ),
                                        '#multiple' => true,
                                        '#default_value' => $settings['groups'],
                                        '#description' => t('Contact will be added to selected group.'),
                                        );
}

/**
 * Define form for remove from group configuration
 *
 */
function rules_action_civicrm_remove_from_group_form( $settings, &$form ) {
	$settings += array('groups' => '' );
	
	$form['settings']['groups'] = array(
                                        '#type' => 'select',
                                        '#title' => t('Select the group'),
                                        '#options' => _civicrm_get_groups( ),
                                        '#multiple' => true,
                                        '#default_value' => $settings['groups'],
                                        '#description' => t('Contact will be removed from selected group.'),
                                        );
}

/**
 * Action contact: add a contact to a group
 */
function rules_action_civicrm_add_to_group($contact, $settings) {
	if ( $contact->id ) {
		foreach ( $settings['groups'] as $gid ) {
			_civicrm_rules_group_contact( $contact->id, $gid );
		}
		return array('contact' => $contact );
	}
}

/**
 * Action contact: remove a contact from a group
 */
function rules_action_civicrm_remove_from_group($contact, $settings) {
	if ( $contact->id ) {
		foreach ( $settings['groups'] as $gid ) {
			_civicrm_rules_group_contact( $contact->id, $gid, 'remove' );
		}
		return array('contact' => $contact );
	}
}

/**
 * Action: Creates a contact.
 * FIX ME: This function is not called, hence can be deleted 
 */
function rules_action_civicrm_contact_create($first_name, $last_name, $email) {
    $params = array( 'first_name' => $first_name,
                     'last_name'  => $last_name ,
                     'email'      => $email );
    
    require_once 'api/v2/Contact.php';
    $contact = civicrm_contact_create( $params );

    if ( ! $contact['is_error'] ) {
        return array( 'contact' => _civicrm_rules_load_contact( $contact['id'] ) );
    } else {
        return array( 'contact' => null );
    }
}

/**
 * Implementation of hook_rules_data_type_info().
 */
function civicrm_rules_data_type_info() {
    return array(
                 'contact' => array(
                                    'label' => t('Contact'),
                                    'class' => 'rules_data_type_contact',
                                    'savable' => FALSE,
                                    'identifiable' => TRUE,
                                    ),
                 'mailing' => array(
                                    'label' => t('Mailing'),
                                    'class' => 'rules_data_type_mailing',
                                    'savable' => FALSE,
                                    'identifiable' => TRUE,
                                    ),
                 );
}

/**
 * Defines the user data type
 */
class rules_data_type_contact extends rules_data_type {
    function load($id) {
        return _civicrm_rules_load_contact( $id );
    }
    
    function get_identifier() {
        $contact = $this->get();
        return $contact->id;
    }
}

class rules_data_type_mailing extends rules_data_type {
    function load($id) {
        return _civicrm_rules_load_mailing( $id );
    }
    
    function get_identifier() {
        $mailing = $this->get();
        return $mailing->id;
    }
}
