<?php
/**
 * Define a custom exception class
 */
class API_Exception extends Exception
{
  private $extraParams = array();
  public function __construct($message, $code = 0, $extraParams = array(),Exception $previous = null) {
        // some code
    
        // make sure everything is assigned properly
        parent::__construct(ts($message), $code, $previous);
        $this->extraParams = $extraParams + array('error_code' => $code);
    }
   
    // custom string representation of object
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
    
    public function getExtraParams() {
        return $this->extraParams;
    }
    
    public function getErrorCodes(){
      return array(
        2000 => '$params was not an array',
        2001 => 'Invalid Value for Date field',
        2100 => 'String value is longer than permitted length'
      );
    }
}
