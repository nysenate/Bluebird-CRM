<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.1                                                |
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

require_once 'CRM/Core/Form.php'; //NYSS

/**
 * form helper class for an Email object
 */
class CRM_Contact_Form_Inline_Email extends CRM_Core_Form {
  /**
   * contact id of the contact that is been viewed
   */
  private $_contactId;
    
  /**
   * email addresses of the contact that is been viewed
   */
  private $_emails = array();

  /**
   * No of email blocks for inline edit
   */
  private $_blockCount = 6;

  /**
   * call preprocess
   */
  public function preProcess() {
    //NYSS accommodate the absence of autoload
    require_once 'CRM/Core/BAO/Email.php';
    require_once 'CRM/Utils/Request.php';
    require_once 'CRM/Core/BAO/Block.php';

    //get all the existing email addresses
    $this->_contactId = CRM_Utils_Request::retrieve( 'cid', 'Positive', $this, true, null, $_REQUEST );

    $this->assign( 'contactId', $this->_contactId );
    $email = new CRM_Core_BAO_Email( );
    $email->contact_id = $this->_contactId;
    $this->_emails = CRM_Core_BAO_Block::retrieveBlock( $email, null );
  }



  /**
   * global validation rules for the form
   *
   * @param array $fields     posted values of the form
   * @param array $errors     list of errors to be posted back to the form
   *
   * @return $errors
   * @static
   * @access public
   */
  static function formRule( $fields, $errors ) {

    require_once 'CRM/Contact/Form/Contact.php';
    $hasData = $hasPrimary = $errors = array( );
    if ( CRM_Utils_Array::value( 'email', $fields ) && is_array( $fields['email'] ) ) {
      foreach ( $fields['email'] as $instance => $blockValues ) {
        $dataExists = CRM_Contact_Form_Contact::blockDataExists( $blockValues );

        if ( $dataExists ) {
          $hasData[] = $instance;
          if ( CRM_Utils_Array::value( 'is_primary', $blockValues ) ) {
            $hasPrimary[] = $instance;
            if ( !$primaryID &&
                 CRM_Utils_Array::value( 'email', $blockValues ) ) {
              $primaryID = $blockValues['email'];
            }
          }
        }
      }

      if ( empty( $hasPrimary ) && !empty( $hasData ) ) {
        $errors["email[1][is_primary]"] = ts('One email should be marked as primary.' );
      }

      if ( count( $hasPrimary ) > 1 ) {
        $errors["email[".array_pop($hasPrimary)."][is_primary]"] = ts( 'Only one email can be marked as primary.' );
      }
    }

    if ( empty($hasData) ) {
      //no emails set; make sure we have fname/lname/org/house
      require_once 'CRM/Contact/BAO/Contact.php';

      $hasContact = true;

      $contact = new CRM_Contact_BAO_Contact( );
      $contact->id = $fields['cid'];
      $contact->find(true);

      switch ( $contact->contact_type ) {
        case 'Individual':
          if ( empty($contact->first_name) && empty($contact->last_name) ) {
            $hasContact = false;
          }
          break;
        case 'Organization':
          if ( empty($contact->organization_name) ) {
            $hasContact = false;
          }
          break;
        case 'Household':
          if ( empty($contact->household_name) ) {
            $hasContact = false;
          }
          break;
      }
      if ( !$hasContact ) {
        $errors["email[1][email]"] = ts('Email must be set if no contact name is present.' );
      }
    }

    return $errors;
  }

  /**
   * build the form elements for an email object
   *
   * @return void
   * @access public
   */
  public function buildQuickForm( ) {
    require_once 'CRM/Contact/Form/Edit/Email.php'; //NYSS

    $totalBlocks    = $this->_blockCount;
    $actualBlockCount = 1;
    if ( count( $this->_emails ) > 1 ) {
      $actualBlockCount = $totalBlocks = count( $this->_emails );
      if ( $totalBlocks < $this->_blockCount ) {
        $additionalBlocks = $this->_blockCount - $totalBlocks;
        $totalBlocks      += $additionalBlocks;
      }
      else {
        $actualBlockCount++;
        $totalBlocks++;
      }
    }

    $this->assign('actualBlockCount', $actualBlockCount);
    $this->assign('totalBlocks',    $totalBlocks);
    
    $this->applyFilter('__ALL__','trim');

    for ( $blockId = 1; $blockId < $totalBlocks; $blockId++ ) {
      CRM_Contact_Form_Edit_Email::buildQuickForm( $this, $blockId, true );
    }

    $buttons = array( 
      array( 
        'type'      => 'upload',
        'name'      => ts('Save'),
        'isDefault' => true),
      array( 
        'type'      => 'refresh',
        'name'      => ts('Cancel') ) );

    $this->addButtons(  $buttons );

    $this->addFormRule( array( 'CRM_Contact_Form_Inline_Email', 'formRule' ), $this );//NYSS
  }

  /**
   * set defaults for the form
   * @return void
   * @access public
   */
  public function setDefaultValues() {
    $defaults = array();
    if ( !empty( $this->_emails ) ) { 
      foreach( $this->_emails as $id => $value ) {
        $defaults['email'][$id] = $value;
      }
    }
    return $defaults;
  }

  /**
   * process the form 
   * @return void
   * @access public
   */
  public function postProcess() {
    $params = $this->exportValues(  );
    
    if ( CRM_Utils_Array::value( '_qf_Email_refresh', $params ) ) {
      $response = array( 'status' => 'cancel' );
    }
    else {
      // need to process / save emails
      
      $params['contact_id']         = $this->_contactId;
      $params['updateBlankLocInfo'] = true;
      
      // save email changes
      CRM_Core_BAO_Block::create( 'email', $params );

      $response = array( 'status' => 'save' );

      //NYSS hit contact object so we can access changelog
      require_once 'CRM/Contact/DAO/Contact.php';
      $contact = new CRM_Contact_DAO_Contact( );
      $contact->id = $this->_contactId;
      $contact->find(true);
      //make sure dates doesn't get reset
      $contact->birth_date    = CRM_Utils_Date::isoToMysql($contact->birth_date); 
      $contact->deceased_date = CRM_Utils_Date::isoToMysql($contact->deceased_date);
      $contact->save();
    }

    echo json_encode( $response );
    CRM_Utils_System::civiExit( );    
  }


}
