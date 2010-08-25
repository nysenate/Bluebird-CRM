<?php

/*
 * Copyright (C) 2007
 * Licensed to CiviCRM under the Academic Free License version 3.0.
 *
 * Written and contributed by Ideal Solution, LLC (http://www.idealso.com)
 *
 */

/**
 * @package CRM
 * @author Marshal Newrock <marshal@idealso.com>
 * $Id: Dummy.php 26018 2010-01-25 09:00:59Z deepak $
 **/

require_once 'CRM/Core/Payment/Dummy.php';

class CRM_Event_Payment_Dummy extends CRM_Core_Payment_Dummy {
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
     *
     * @return object
     * @static
     */
    static function &singleton( $mode, &$paymentProcessor ) {
        if (self::$_singleton === null ) {
            self::$_singleton = new CRM_Event_Payment_Dummy( $mode, $paymentProcessor );
        }
        return self::$_singleton;
    }

}

