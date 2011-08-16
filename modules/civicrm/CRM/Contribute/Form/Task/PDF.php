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
 *
 */

require_once 'CRM/Contribute/Form/Task.php';

/**
 * This class provides the functionality to email a group of
 * contacts. 
 */
class CRM_Contribute_Form_Task_PDF extends CRM_Contribute_Form_Task {

    /**
     * Are we operating in "single mode", i.e. updating the task of only
     * one specific contribution?
     *
     * @var boolean
     */
    public $_single = false;

    protected $_rows;

    /**
     * build all the data structures needed to build the form
     *
     * @return void
     * @access public
     */
    
    function preProcess( ) {
        $id = CRM_Utils_Request::retrieve( 'id', 'Positive',
                                           $this, false );

        if ( $id ) {
            $this->_contributionIds    = array( $id );
            $this->_componentClause =
                " civicrm_contribution.id IN ( $id ) ";
            $this->_single             = true;
            $this->assign( 'totalSelectedContributions', 1 );
        } else {
            parent::preProcess( );
        }

        // check that all the contribution ids have pending status
        $query = "
SELECT count(*)
FROM   civicrm_contribution
WHERE  contribution_status_id != 1
AND    {$this->_componentClause}";
        $count = CRM_Core_DAO::singleValueQuery( $query,
                                                 CRM_Core_DAO::$_nullArray );
        if ( $count != 0 ) {
            CRM_Core_Error::statusBounce( "Please select only online contributions with Completed status." ); 
        }

        // we have all the contribution ids, so now we get the contact ids
        parent::setContactIDs( );
        $this->assign( 'single', $this->_single );
        
        $qfKey = CRM_Utils_Request::retrieve( 'qfKey', 'String', $this );
        require_once 'CRM/Utils/Rule.php';
        $urlParams = 'force=1';
        if ( CRM_Utils_Rule::qfKey( $qfKey ) ) $urlParams .= "&qfKey=$qfKey";
        
        $url = CRM_Utils_System::url( 'civicrm/contribute/search', $urlParams );
        $breadCrumb = array ( array( 'url'   => $url,
                                     'title' => ts('Search Results') ) );
        
        CRM_Utils_System::appendBreadCrumb( $breadCrumb );
        CRM_Utils_System::setTitle( ts('Print Contribution Receipts') );
    }
    
    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    public function buildQuickForm()
    {
        
        $this->addElement( 'radio', 'output', null, ts('Email Receipts'), 'email_receipt', 
                           array('onClick' => "document.getElementById('selectPdfFormat').style.display = 'none';") );
        $this->addElement( 'radio', 'output', null, ts('PDF Receipts'), 'pdf_receipt',
                           array('onClick' => "document.getElementById('selectPdfFormat').style.display = 'block';") );
        $this->addRule('output', ts('Selection required') , 'required');

        require_once 'CRM/Core/BAO/PdfFormat.php';
        $this->add( 'select', 'pdf_format_id', ts( 'Page Format' ),
                     array( 0 => ts( '- default -' ) ) + CRM_Core_BAO_PdfFormat::getList( true ) );

        $this->addButtons( array(
                                 array ( 'type'      => 'next',
                                         'name'      => ts('Process Receipt(s)'),
                                         'isDefault' => true   ),
                                 array ( 'type'      => 'back',
                                         'name'      => ts('Cancel') ),
                                 )
                           );
    }

    /**
     * Set default values
     */
    function setDefaultValues( ) {
        require_once 'CRM/Core/BAO/PdfFormat.php';
        $defaultFormat = CRM_Core_BAO_PdfFormat::getDefaultValues();
        return array( 'pdf_format_id' => $defaultFormat['id'] );
    }

    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return None
     */
    public function postProcess() {
        // get all the details needed to generate a receipt
        $contribIDs = implode( ',', $this->_contributionIds );

        require_once 'CRM/Contribute/Form/Task/Status.php';
        $details =& CRM_Contribute_Form_Task_Status::getDetails( $contribIDs );

        require_once 'CRM/Core/Payment/BaseIPN.php';
        $baseIPN = new CRM_Core_Payment_BaseIPN( );

        $message  =  array( );
        $template = CRM_Core_Smarty::singleton( );

        $params = $this->controller->exportValues( $this->_name );
        
        $createPdf = false;
        if ( $params['output'] == "pdf_receipt" ) {
            $createPdf = true;
        }
        
        $excludeContactIds = array( );
        if ( !$createPdf ) {
            $returnProperties = array( 'email'        => 1,
                                       'do_not_email' => 1,
                                       'is_deceased'  => 1,
                                       'on_hold'      => 1
                                       );
            
            require_once 'CRM/Mailing/BAO/Mailing.php';
            list( $contactDetails ) = 
                CRM_Mailing_BAO_Mailing::getDetails( $this->_contactIds, $returnProperties, false, false );
            
            foreach ( $contactDetails as $id => $values ) {
                if ( empty( $values['email'] ) ||
                     CRM_Utils_Array::value( 'do_not_email', $values ) || 
                     CRM_Utils_Array::value( 'is_deceased', $values ) ||
                     CRM_Utils_Array::value( 'on_hold', $values ) ) {
                    $suppressedEmails++;
                    $excludeContactIds[] = $values['contact_id'];
                }
            }
        }
                
        foreach ( $details as $contribID => $detail ) {
            $input = $ids = $objects = array( );
            
            if ( in_array( $detail['contact'], $excludeContactIds ) ) {
                continue;
            }

            $input['component'] = $detail['component'];

            $ids['contact'     ]      = $detail['contact'];
            $ids['contribution']      = $contribID;
            $ids['contributionRecur'] = null;
            $ids['contributionPage']  = null;
            $ids['membership']        = $detail['membership'];
            $ids['participant']       = $detail['participant'];
            $ids['event']             = $detail['event'];

            if ( ! $baseIPN->validateData( $input, $ids, $objects, false ) ) {
                CRM_Core_Error::fatal( );
            }

            $contribution =& $objects['contribution'];
            // CRM_Core_Error::debug('o',$objects);


            // set some fake input values so we can reuse IPN code
            $input['amount']     = $contribution->total_amount;
            $input['is_test']    = $contribution->is_test;
            $input['fee_amount'] = $contribution->fee_amount;
            $input['net_amount'] = $contribution->net_amount;
            $input['trxn_id']    = $contribution->trxn_id;
            $input['trxn_date']  = isset( $contribution->trxn_date ) ? $contribution->trxn_date : null;

            // CRM_Core_Error::debug('input',$input);
            
            $values = array( );
            $mail = $baseIPN->sendMail( $input, $ids, $objects, $values, false, $createPdf );
            
            if ( $mail['html'] ) {
                $message[] = $mail['html'];
            } else {
                $message[] = nl2br( $mail['body'] );
            }

            // reset template values before processing next transactions
            $template->clearTemplateVars( );
        }
        if ( $createPdf ) {
            require_once 'CRM/Utils/PDF/Utils.php';
            CRM_Utils_PDF_Utils::html2pdf( $message,
                                         'civicrmContributionReceipt.pdf',
                                         false,
                                         $params['pdf_format_id'] );
            CRM_Utils_System::civiExit( );
        } else {
            if ( $suppressedEmails ) {
                $status = array( '', ts( 'Email was NOT sent to %1 contacts (no email address on file, or communication preferences specify DO NOT EMAIL, or contact is deceased).', array( 1 => $suppressedEmails ) ) );
            } else {
                $status = array( '', ts( 'Your mail has been sent.' ) );
            }
            CRM_Core_Session::setStatus( $status );
        }

    }

}


