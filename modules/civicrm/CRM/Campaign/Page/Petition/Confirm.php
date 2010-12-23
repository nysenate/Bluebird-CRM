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

require_once 'CRM/Core/Config.php';
require_once 'CRM/Core/Error.php';
require_once 'CRM/Core/Page.php';

class CRM_Campaign_Page_Petition_Confirm extends CRM_Core_Page 
{
    function run( ) {
        require_once 'CRM/Utils/Request.php';
        $contact_id   = CRM_Utils_Request::retrieve( 'cid', 'Integer', CRM_Core_DAO::$_nullObject );
        $subscribe_id = CRM_Utils_Request::retrieve( 'sid', 'Integer', CRM_Core_DAO::$_nullObject );
        $hash         = CRM_Utils_Request::retrieve( 'h'  , 'String' , CRM_Core_DAO::$_nullObject );
        $activity_id  = CRM_Utils_Request::retrieve( 'a'  , 'String' , CRM_Core_DAO::$_nullObject );
        $petition_id = CRM_Utils_Request::retrieve( 'p'  , 'String' , CRM_Core_DAO::$_nullObject );
        
        if ( ! $contact_id   ||
             ! $subscribe_id ||
             ! $hash ) {
            CRM_Core_Error::fatal( ts( "Missing input parameters" ) );
        }

        require_once 'CRM/Mailing/Event/BAO/Confirm.php';
        $result = $this->confirm( $contact_id, $subscribe_id, $hash, $activity_id, $petition_id );
        if ( $result === false ) {
            $this->assign( 'success', $result );
        } else {
            $this->assign( 'success', true    );
            // $this->assign( 'group'  , $result );
        }

		require_once 'CRM/Contact/BAO/Contact/Location.php';
        list( $displayName, $email ) = CRM_Contact_BAO_Contact_Location::getEmailDetails( $contact_id );
        $this->assign( 'display_name', $displayName);
        $this->assign( 'email'       , $email );
        $this->assign( 'petition_id'       , $petition_id );

		$this->assign('survey_id', $petition_id);
		
		// send thank you email
		require_once 'CRM/Campaign/Form/Petition/Signature.php';
		$params['contactId'] = $contact_id;
		$params['email-Primary'] = $email;
		$params['sid'] =  $petition_id;
		$params['activityId'] = $activity_id;
		CRM_Campaign_BAO_Petition::sendEmail( $params, CRM_Campaign_Form_Petition_Signature::EMAIL_THANK );

        parent::run();
    }
    
    /**
     * Confirm email verification
     *
     * @param int $contact_id       The id of the contact
     * @param int $subscribe_id     The id of the subscription event
     * @param string $hash          The hash
     * @return boolean              True on success
     * @access public
     * @static
     */
    public static function confirm($contact_id, $subscribe_id, $hash, $activity_id, $petition_id) 
    {
        require_once 'CRM/Mailing/Event/BAO/Subscribe.php';
        $se =& CRM_Mailing_Event_BAO_Subscribe::verify($contact_id, $subscribe_id, $hash);
        
        if (! $se) {
            return false;
        }
        
        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );
        
        $ce = new CRM_Mailing_Event_BAO_Confirm();
        $ce->event_subscribe_id = $se->id;
        $ce->time_stamp = date('YmdHis');
        $ce->save();
        
        
        require_once 'CRM/Contact/BAO/GroupContact.php';
        CRM_Contact_BAO_GroupContact::updateGroupMembershipStatus( $contact_id, $se->group_id,
                                                                   'Email',$ce->id);

        require_once 'CRM/Campaign/BAO/Petition.php';
        $bao = new CRM_Campaign_BAO_Petition ();
        $bao->confirmSignature($activity_id,$contact_id,$petition_id);
    }
}

