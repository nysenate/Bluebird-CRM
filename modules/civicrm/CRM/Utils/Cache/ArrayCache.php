<?php

class CRM_Utils_Cache_Arraycache {

    /**
     * The cache storage container, an in memory array by default
     */
    private $_cache;

    function __construct( ) {
        $this->_cache = array();
    }

    function set( $key, &$value ) {
        $this->_cache[$key] = $value;
    }

    function get( $key ) {
        return CRM_Utils_Array::value($key, $this->_cache);
    }

    function delete( $key ) {
        unset($this->_cache[$key]);
    }

    function flush( ) {
        unset($this->_cache);
        $this->_cache = array();
    }

}
