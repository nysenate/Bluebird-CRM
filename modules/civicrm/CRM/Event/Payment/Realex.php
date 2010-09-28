<?php

  /**
   * @package CRM
   * @author John Griffin <john.griffin@enableinteractive.co.uk>
   * @author Tom Kirkpatrick <tkp@kirkdesigns.co.uk>
   * $Id:$
   **/

require_once 'CRM/Core/Payment/Realex.php';

class CRM_Event_Payment_Realex extends CRM_Core_Payment_Realex {
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
    static function &singleton( $mode, &$paymentProcessor ) 
    {
        if (self::$_singleton === null ) {
            self::$_singleton = new CRM_Event_Payment_Realex( $mode, $paymentProcessor );
        }
        return self::$_singleton;
    }
}
?>
