<?php
 
/*
 +--------------------------------------------------------------------+
 | eWAY Core Payment Module for CiviCRM version 3.1 & 1.9             |
 +--------------------------------------------------------------------+
 | Licensed to CiviCRM under the Academic Free License version 3.0    |
 |                                                                    |
 | Written & Contributed by Dolphin Software P/L - March 2008         |
 +--------------------------------------------------------------------+
 |                                                                    |
 | This file is a NOT YET part of CiviCRM.                            |
 |                                                                    |
 | This module is based very much on the recent PayJunction module    |
 | contributed by Phase2 Technology, LLC                              |
 |                                                                    |
 +--------------------------------------------------------------------+
*/

require_once 'CRM/Core/Payment/eWAY.php';

class CRM_Event_Payment_eWAY extends CRM_Core_Payment_eWAY 
{        
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
   function __construct( $mode, &$paymentProcessor ) 
   {
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
   static function &singleton( $mode, &$paymentProcessor ) 
   {
      if (self::$_singleton === null ) 
      { 
         self::$_singleton = new CRM_Event_Payment_eWAY( $mode, $paymentProcessor );
      } 
      return self::$_singleton; 
   } 

} // end class CRM_Event_Payment_eWAY

