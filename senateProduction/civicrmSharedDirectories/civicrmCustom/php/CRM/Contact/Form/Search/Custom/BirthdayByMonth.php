<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
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

class CRM_Contact_Form_Search_Custom_BirthdayByMonth
   extends    CRM_Contact_Form_Search_Custom_Base
   implements CRM_Contact_Form_Search_Interface {

    protected $_formValues;
    protected $_columns;

    function __construct( &$formValues ) {
        parent::__construct( $formValues );

        $this->_columns = array( ts('Contact Id') => 'contact_id',
                                 ts('Name')  	  => 'sort_name' ,
                                 ts('Birth Date') => 'birth_date');
    }

    function buildForm( &$form ) {

		$this->setTitle('Contacts By Birth Month');

		$month = array( ''   => '- select month -', 
						'1'  => 'January', 
						'2'  => 'February', 
						'3'  => 'March',
						'4'  => 'April', 
						'5'  => 'May' , 
						'6'  => 'June', 
						'7'  => 'July', 
						'8'  => 'August', 
						'9'  => 'September', 
						'10' => 'October', 
						'11' => 'November', 
						'12' => 'December'
						);

		$form->add( 'select',
                    'birth_month',
                    ts( 'Individual\'s Birth Month (1-12)' ),
					$month,
					false );
            
		/*$form->add( 'text',
                    'after_date',
                    ts( 'Birthday after (date)' ) );*/
        
		//$form->addElement('date', 'after_date', ts( 'Birthday after (date)' ), array('format' => 'MdY', 'minYear' => 1900, 'maxYear' => date('Y'))); 
		
		$form->addDate( 'start_date', ts( 'Birthday after (date)' ), false, array('formatType' => 'birth') );
		 
        //$form->assign( 'elements', array( 'birth_month', 'after_date') );
		$form->assign( 'elements', array( 'birth_month', 'start_date') );

		$form->setDefaults( $this->setDefaultValues() );
    }

    function summary( ) {
        return null;
    }

    function all( $offset = 0, $rowcount = 0, $sort = null,
                  $includeContactIDs = false ) {
        $selectClause = "contact_a.id as contact_id,
            		 	 contact_a.sort_name as sort_name,
            		 	 contact_a.birth_date as birth_date
        		        ";
        return $this->sql( $selectClause,
                           $offset, $rowcount, $sort,
                           $includeContactIDs, null );

    }
    
    function from( ) {
        return "FROM civicrm_contact contact_a ";
    }

    function where( $includeContactIDs = false ) {
        $params = array( );
		
		$birth_month = CRM_Utils_Array::value( 'birth_month', $this->_formValues );
        //$after_date  = CRM_Utils_Array::value( 'after_date', $this->_formValues );
		$after_date  = CRM_Utils_Date::mysqlToIso( CRM_Utils_Date::processDate( $this->_formValues['start_date'] ) );
		
		if ( $birth_month ) {
        	$where  = "MONTH( contact_a.birth_date ) = $birth_month ";
		}
		if ( $birth_month && $after_date ) {
			$where .= "AND ";
		}
		if ( $after_date ) {
			//$after_date = $after_date['Y'].'-'.$after_date['m'].'-'.$after_date['d'];
			$where .= "contact_a.birth_date > '$after_date' ";
		}
	
        return $this->whereClause( $where, $params );
    }

    function templateFile( ) {
        //return 'CRM/Contact/Form/Search/Custom/Sample.tpl';
		return 'CRM/Contact/Form/Search/Custom.tpl';
    }

    function setDefaultValues( ) {
        return array( 'birth_month' => 1, 'after_date' => '1900-01-01');
    }

    function alterRow( &$row ) {
        return null;
    }
    
    function setTitle( $title ) {
        if ( $title ) {
            CRM_Utils_System::setTitle( $title );
        } else {
            CRM_Utils_System::setTitle(ts('Search'));
        }
    }
}


