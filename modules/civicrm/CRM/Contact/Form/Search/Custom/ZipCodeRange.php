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

require_once 'CRM/Contact/Form/Search/Custom/Base.php';

class CRM_Contact_Form_Search_Custom_ZipCodeRange
   extends    CRM_Contact_Form_Search_Custom_Base
   implements CRM_Contact_Form_Search_Interface {

    function __construct( &$formValues ) {
        parent::__construct( $formValues );

        $this->_columns = array( ts('Contact Id')   => 'contact_id'  ,
                                 ts('Name')         => 'sort_name'   ,
                                 ts('Email')        => 'email'       ,
                                 ts('Zip')          => 'postal_code' );
    }

    function buildForm( &$form ) {
        $form->add( 'text',
                    'postal_code_low',
                    ts( 'Postal Code Start' ),
                    true );

        $form->add( 'text',
                    'postal_code_high',
                    ts( 'Postal Code End' ),
                    true );

        /**
         * You can define a custom title for the search form
         */
         $this->setTitle('Zip Code Range Search');
         
         /**
         * if you are using the standard template, this array tells the template what elements
         * are part of the search criteria
         */
         $form->assign( 'elements', array( 'postal_code_low', 'postal_code_high' ) );
    }

    function summary( ) {
        $summary = array( );
        return $summary;
    }

    function all( $offset = 0, $rowcount = 0, $sort = null,
                  $includeContactIDs = false ) {
        $selectClause = "
contact_a.id           as contact_id ,
contact_a.sort_name    as sort_name  ,
email.email            as email   ,
address.postal_code    as postal_code
";
        return $this->sql( $selectClause,
                           $offset, $rowcount, $sort,
                           $includeContactIDs, null );

    }
    
    function from( ) {
        return "
FROM      civicrm_contact contact_a
LEFT JOIN civicrm_address address ON ( address.contact_id       = contact_a.id AND
                                       address.is_primary       = 1 )
LEFT JOIN civicrm_email   email   ON ( email.contact_id = contact_a.id AND
                                       email.is_primary = 1 )
";
    }

    function where( $includeContactIDs = false ) {
        $params = array( );

        $low    = CRM_Utils_Array::value( 'postal_code_low',
                                          $this->_formValues );
        $high   = CRM_Utils_Array::value( 'postal_code_high',
                                          $this->_formValues );
        if ( $low == null || $high == null ) {
            CRM_Core_Error::statusBounce( ts('Please provide start and end postal codes'),
                                          CRM_Utils_System::url( 'civicrm/contact/search/custom',
                                                                 "reset=1&csid={$this->_formValues['customSearchID']}",
                                                                 false, null, false, true ) );
        }

        $where  = "ROUND(address.postal_code) >= %1 AND ROUND(address.postal_code) <= %2";
        $params = array( 1 => array( trim( $low  ), 'Integer' ),
                         2 => array( trim( $high ), 'Integer' ) );

        return $this->whereClause( $where, $params );
    }

    function setDefaultValues( ) {
        return array( );
    }

    function templateFile( ) {
        return 'CRM/Contact/Form/Search/Custom.tpl';
    }
    
    function setTitle( $title ) {
        if ( $title ) {
            CRM_Utils_System::setTitle( $title );
        } else {
            CRM_Utils_System::setTitle(ts('Search'));
        }
    }
}


