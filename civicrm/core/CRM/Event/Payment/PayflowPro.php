<?php 
  /*
   +------------------------------------------------------------------------------------+
   | PayflowPro Core Payment Module for CiviCRM version 3.2                             |
   +------------------------------------------------------------------------------------+
   | Coded 2009 by Eileen McNaughton (Fuzion.co.nz)                                     |
   | Licensed to CiviCRM under the Academic Free License version 3.0.                   | 
   |                                                                                    |
   +------------------------------------------------------------------------------------+
  */
require_once 'CRM/Core/Payment/PayflowPro.php';

class CRM_Event_Payment_PayflowPro extends CRM_Core_Payment_PayflowPro {
    /** 
     * We only need one instance of this object. So we use the singleton 
     * pattern and cache the instance in this variable 
     * 
     * @var object 
     * @static 
     */ 
    static private $_singleton = null; 
    
    /** 
     * Constructor 
     *
     * @param string $mode the mode of operation: live or test
     * 
     * @return void 
     */ 
    function __construct( $mode, &$paymentProcessor ) {
        parent::__construct( $mode, $paymentProcessor );
    }
    
    /** 
     * singleton function used to manage this object 
     * 
     * @param string $mode the mode of operation: live or test
     
     * @return object 
     * @static 
     * 
     */ 
    static function &singleton( $mode, &$paymentProcessor ) {
        if (self::$_singleton === null ) { 
            self::$_singleton = new CRM_Event_Payment_PayflowPro( $mode, $paymentProcessor );
        } 
        return self::$_singleton; 
    } 
    
    /**  
     * Sets appropriate parameters for checking out to google
     *  
     * @param array $params  name value pair of contribution datat
     *  
     * @return void  
     * @access public 
     *  
     */  
    function doTransferCheckout( &$params ) {
        parent::doTransferCheckout( $params, 'event' );
    }
    
}
