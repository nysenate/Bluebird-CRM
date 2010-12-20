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
                 'contact_insert' => array(
                                           'label'     => t( 'Contact has been created' ),
                                           'module'    => 'CiviCRM Contact',
                                           'arguments' => rules_events_hook_civicrm_arguments( t( 'Created contact' ) ),
                                           ),
                 'contact_update' => array(
                                           'label'     => t( 'Contact has been updated' ),
                                           'module'    => 'CiviCRM Contact',
                                           'arguments' => rules_events_hook_civicrm_arguments( t( 'Updated contact' ) ),
                                           ),
                 'contact_view'   => array(
                                           'label'     => t( 'Contact has been viewed' ),
                                           'module'    => 'CiviCRM Contact',
                                           'arguments' => rules_events_hook_civicrm_arguments( t( 'Viewed contact' ) ),
                                           ),
                 'contact_delete' => array(
                                           'label'     => t( 'Contact has been deleted' ),
                                           'module'    => 'CiviCRM Contact',
                                           'arguments' => rules_events_hook_civicrm_arguments( t( 'Deleted contact' ) ),
                                           )
                 );
}

/**
 * Returns some arguments suitable for hook_civicrm
 */
function rules_events_hook_civicrm_arguments( $label ) {
	return
		array(
			'contact' => array( 'type'  => 'contact',
			'label' => $label )
		);
	//rules_events_global_civicrm_argument( );
}

/**
 * Gets the user account of the logged in user and civicrm contact
 * FIX Me: This function is never called
 */
function rules_events_global_civicrm_argument( ) {
	return 
		array(
			'userContact' => array( 'type'    => 'contact',
			'label'   => t( 'Logged in User' ),
			'handler' => 'rules_events_argument_global_userContact' )
		);
	rules_events_global_user_argument( );
}

/*
 * FIX ME: this function is never called
 */
function rules_events_argument_global_userContact( ) {
    $session = CRM_Core_Session::singleton( );
    return _civicrm_rules_load_contact( $session->get( 'userID' ) );
}

/**
 * Implementation of hook_rules_condition_info().
 * FIX ME : Here we should add sensible conditions
 */
function civicrm_rules_condition_info() {
	/*
	// commenting conditions for now since it does not make sense to me
	return array(
    'rules_condition_civicrm_comparison' => array(
      'label' => t('Compare two contacts'),
      'arguments' => array(
        'contact_1' => array('type' => 'contact', 'label' => t('Contact 1')),
        'contact_2' => array('type' => 'contact', 'label' => t('Contact 2')),
      ),
      'help' => t('Evaluates to TRUE, if both given contacts are the same.'),
      'module' => 'CiviCRM',
    ),
    'rules_condition_civicrm_ingroup' => array(
      'label' => t('User is in group(s)'),
      'arguments' => array(
        'contact' => array('type' => 'contact', 'label' => t('Contact')),
      ),
      'help' => t('Whether the contact is in the selected group(s).'),
      'module' => 'CiviCRM',
    )
	);
	 */
}

/**
 * A simple contact comparison
 * FIX ME: this function is called by condition and can be removed
 */
function rules_condition_civicrm_comparison( $contact_1, $contact_2 ) {
  return $contact_1->id == $contact_2->id;
}

/**
 * Condition user: condition to check whether user has particular roles
 * FIX ME: this function is called by condition and can be removed
 */
function rules_condition_civicrm_ingroup($contact, $settings) {
  $groups = $settings['groups'];
  $operation = $settings['operation'];

  switch ($operation) {
    case 'OR':
      foreach ($groups as $gid) {
          if ( _civicrm_rules_in_group( $contact, $gid ) ) {
              return TRUE;
          }
      }
      return FALSE;

    case 'AND':
      foreach ($groups as $gid) {
          if ( ! _civicrm_rules_in_group( $contact, $gid ) ) {
              return FALSE;
          }
      }
      return TRUE;
  }

}

/**
 * Implementation of hook_rules_action_info().
 */
function civicrm_rules_action_info() {
	return array(
		'rules_action_civicrm_add_to_group' => array(
			'label' => t('Add Contact to CiviCRM Group'),
			'arguments' => array(
				'contact' => array('type' => 'contact', 'label' => t('Contact who should be added to the group')),
			),
			'module' => 'CiviCRM Contact',
    ),
		'rules_action_civicrm_remove_from_group' => array(
			'label' => t('Remove Contact from CiviCRM Group'),
			'arguments' => array(
				'contact' => array('type' => 'contact', 'label' => t('Contact who should be removed from the group')),
			),
			'module' => 'CiviCRM Contact',
    ),

        'rules_action_civicrm_send_email' => array(
            'label' => t('Send Email'),
            'arguments' => array(
                'contact' => array('type' => 'contact', 'label' => t('Send an email')),
            ),
            'module' => 'CiviCRM Contact',
    )
        
	);
}

function rules_action_civicrm_send_email($contact, $settings) {
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
function rules_action_civicrm_send_email_form( $settings, &$form ) {
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
      'label' => t('contact'),
      'class' => 'rules_data_type_contact',
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
