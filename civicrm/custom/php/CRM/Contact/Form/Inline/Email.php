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
    //get all the existing email addresses
    $this->_contactId = CRM_Utils_Request::retrieve( 'cid', 'Positive', $this, true, null, $_REQUEST );
    
    $this->assign( 'contactId', $this->_contactId );
    $email = new CRM_Core_BAO_Email( );
    $email->contact_id = $this->_contactId;
//CRM_Core_Error::debug_var('email', $email);
    $this->_emails = CRM_Core_BAO_Block::retrieveBlock( $email, null );
  }

  /**
   * build the form elements for an email object
   *
   * @return void
   * @access public
   */
  public function buildQuickForm( ) {        
    $totalBlocks    = $this->_blockCount;
    $actualBlockCount = 1;
    if ( count( $this->_emails ) > 1 ) {
      $actualBlockCount = $totalBlocks = count( $this->_emails );
      $additionalBlocks = $this->_blockCount - $totalBlocks;
      $totalBlocks      += $additionalBlocks;  
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
    }

    echo json_encode( $response );
    CRM_Utils_System::civiExit( );    
  }
}
