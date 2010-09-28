<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
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
 * Definition of the Contribution part of the CRM API. 
 * More detailed documentation can be found 
 * {@link http://objectledge.org/confluence/display/CRM/CRM+v1.0+Public+APIs
 * here}
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

/**
 * Files required for this package
 */
require_once 'api/utils.php';

require_once 'CRM/Contribute/BAO/Contribution.php';

/**
 * Most API functions take in associative arrays ( name => value pairs
 * as parameters. Some of the most commonly used parameters are
 * described below
 *
 * @param array $params           an associative array used in construction
                                  / retrieval of the object
 * @param array $returnProperties the limited set of object properties that
 *                                need to be returned to the caller
 *
 */

/**
 * Create a new contribution.
 *
 * Creates a new contribution record and returns the newly created
 * Contribution object (including the contribution_id property).
 *
 * @param array  $params       Associative array of property name/value
 *                             pairs to insert in new contribution.
 *
 * @return CRM_Contribution|CRM_Error Newly created Contribution object
 *
 * @access public
 */
function &crm_create_contribution( &$params ) {
    _crm_initialize( );

    // return error if we do not get any params
    if (empty($params)) {
        return _crm_error( "Input Parameters empty" );
    }

    $error = _crm_check_contrib_params( $params );
    if (is_a($error, 'CRM_Core_Error')) {
        return $error;
    }

    $values  = array( );
   
    $error = _crm_format_contrib_params( $params, $values );
    if (is_a($error, 'CRM_Core_Error') ) {
        return $error;
    }
    $values["contact_id"] = $params["contact_id"];
    $values["source"]     = $params["source"];
   
    $ids     = array( );
   
    $contribution = CRM_Contribute_BAO_Contribution::create( $values, $ids );
    
    return $contribution;
}

/**
 * Get an existing contribution.
 *
 * Returns a single existing Contribution object which matches ALL property
 * values passed in $params. An error object is returned if there is
 * no match, or more than one match. This API can be used to retrieve
 * the CRM internal identifier (contribution_id) based on a unique property.
 * It can also be used to retrieve any desired
 * contribution properties based on a known contribution_id.
 *
 * @param array $params           Associative array of property name/value
 *                                pairs to attempt to match on.
 * @param array $returnProperties Which properties should be included in the
 *                                returned Contribution object. If NULL, the default
 *                                set of properties will be included.
 *
 * @return CRM_Contribution|CRM_Core_Error  Return the Contribution Object if found, else
 *                                Error Object
 *
 * @access public
 *
 */
function &crm_get_contribution( $params, $returnProperties = null ) {
    _crm_initialize( );

    // empty parameters ?
    if (empty($params)) {
        return _crm_error('$params is empty');
    }

    // correct parameter format ?
    if (!is_array($params)) {
        return _crm_error('$params is not an array');
    }

    if ( ! CRM_Utils_Array::value( 'contribution_id', $params ) ) {
        $returnProperties = array( 'trxn_id'         => 1 ,
                                   'contributiom_id' => 1);
        require_once 'CRM/Contact/BAO/Query.php';
        $newP =& CRM_Contact_BAO_Query::convertFormValues( $params );
        list( $contributions , $options ) = crm_search( $newP, $returnProperties );
        if ( count( $contributions ) != 1 ) {
            return _crm_error( count( $contributions ) . " contributions matching input params." );
        }
        $contributionIds = array_values( $contributions );
        $params['contribution_id'] = $contributionIds[0]["contribution_id"];
    }

    
    $newParams = array( 'id' => $params['contribution_id'] );
    $ids       = array( ); 
 
    $contribution =& CRM_Contribute_BAO_Contribution::getValues( $newParams, $defaults, $ids ); 

    if ( $contribution == null || is_a($contribution, 'CRM_Core_Error') || ! $contribution->id ) { 
        return _crm_error( 'Did not find contribution object for ' . $params['contribution_id'] ); 
    } 
 
    return $contribution; 
}

/**
 * Update a specified contribution.
 *
 * Updates a contribution with the values passed in the 'params' array. An
 * error is returned if an invalid contribution is passed, or an invalid
 * property name or property value is included in 'params'. An error
 * is also returned if the processing the update would violate data
 * integrity rules.
 *
 * @param CRM_Contribution $contribution A valid Contribution object
 * @param array            $params       Associative array of property
 *                                       name/value pairs to be updated. 
 *  
 * @return CRM_Contribution|CRM_Core_Error  Return the updated Contribution Object else
 *                                          Error Object (if integrity violation)
 *
 * @access public
 *
 */
function &crm_update_contribution( &$contribution, $params ) {
    _crm_initialize( );

    $values = array( );

    if ( ! isset( $contribution->id ) ) {
        return _crm_error( 'Invalid contribution object passed in' );
    }

    $error = _crm_format_contrib_params( $params, $values );
    if ( is_a($error, 'CRM_Core_Error') ) {
        return $error;
    }
    
    if (isset($params['source'])) {
        $values['contribution_source'] = $params['source'];        
    } 
    if($params['note']) {
        $contribution->note = $params['note'];
    }

    $contribution = _crm_update_contribution( $contribution, $values );
    
    return $contribution;
}

/**
 * Delete a specified contribution.
 *
 * @param CRM_Contribution $contribution Contribution object to be deleted
 *
 * @return void|CRM_Core_Error  An error if 'contribution' is invalid,
 *                              permissions are insufficient, etc.
 *
 * @access public
 *
 */
function crm_delete_contribution( &$contribution ) {
    _crm_initialize( );

    if ( ! isset( $contribution->id ) ) {
        return _crm_error( 'Invalid contribution object passed in' );
    }
    
    CRM_Contribute_BAO_Contribution::deleteContribution( $contribution->id );
}


/** 
 * Get all the contribution_ids 
 * 
 * @return $Contributions Array of contribution ids 
 *  
 * @access public 
 * 
 */ 
function crm_get_contributions() {
    $query = 'SELECT * FROM civicrm_contribution';
    $dao =& new CRM_Core_DAO( );
    $dao->query( $query );
    $contributions = array();
    while ( $dao->fetch( ) ) {
        $contributions[$dao->id] = $dao->id;
    }
    return $contributions;
}


