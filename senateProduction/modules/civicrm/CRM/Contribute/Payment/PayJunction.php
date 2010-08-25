<?php
 
/**
 * Copyright (C) 2007
 * Licensed to CiviCRM under the Academic Free License version 3.0.
 *
 * Written and contributed by Phase2 Technology, LLC (http://www.phase2technology.com)
 *
 */

/** 
 * 
 * @package CRM 
 * @author Michael Morris and Gene Chi @ Phase2 Technology <mmorris@phase2technology.com>
 * $Id$ 
 * 
 */ 

require_once 'CRM/Core/Payment/PayJunction.php';

class CRM_Contribute_Payment_PayJunction extends CRM_Core_Payment_PayJunction 
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
       $processorName = $paymentProcessor['name'];
       if (self::$_singleton[$processorName] === null ) {
           self::$_singleton[$processorName] = new CRM_Contribute_Payment_PayJunction( $mode, $paymentProcessor );
       }
       return self::$_singleton[$processorName];
   } 

} // end class CRM_Contribute_Payment_PayJunction

