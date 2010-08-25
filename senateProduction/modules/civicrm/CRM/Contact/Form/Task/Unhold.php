<?php
require_once 'CRM/Contact/Form/Task.php';

class CRM_Contact_Form_Task_Unhold extends CRM_Contact_Form_Task 
{
    /** 
     * Function to set variables up before form is built 
     *                                                           
     * @return void 
     * @access public 
     */ 
    function preProcess( ) 
    { 
        parent::preProcess( );
    }
    
    function buildQuickForm( ) 
    {
        $this->addDefaultButtons( ts('Unhold Email'), 'done' );
    }
    
    public function postProcess( ) 
    {   
        // Query to unhold emails of selected contacts
        
        if ( count($this->_contactIds) >= 1 ) {
            $queryString = "
UPDATE civicrm_email SET on_hold = 0, hold_date = null 
WHERE on_hold = 1 AND hold_date is not null AND contact_id in (". implode(", ", $this->_contactIds) .")";
            CRM_Core_DAO::executeQuery( $queryString );
            $sql = "SELECT ROW_COUNT( )";
            $result = CRM_Core_DAO::singleValueQuery( $sql );
            if ( $result ) {
                $status = array( ts( '%1 emails were found on hold and updated.', array( 1 => $result ) ) );
            } else {
                $status = ts( 'No contact found with email on hold status.' );
            }
        } else {
            $status = ts( 'Please select one or more contact for this action' ); 
        }
        
        $session =& CRM_Core_Session::singleton( );
        CRM_Core_Session::setStatus( $status ); 
        
    }
}
