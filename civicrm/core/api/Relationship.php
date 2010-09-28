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
 * Definition of the Group part of the CRM API. 
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

require_once 'CRM/Contact/BAO/Relationship.php';
require_once 'CRM/Contact/BAO/RelationshipType.php';

/**
 * Function to create new retaionship 
 *
 * @param  object  $contact                      A valid Contact object.
 *
 * @param  object $target_contact                A valid Contact object
 * @param  String $relationship_type_name        A valid Relationship_type eg. Parent of etc.
 * @param   array $ params                       Associative array of property name/value pairs to be inserted. See Data Model for                                                         available properties.
 *
 * @return     newly created 'relationship object' object
 *
 * @access     public        
 *
 */
function crm_create_relationship($contact =null, $target_contact= null, $relationship_type_name, $params) {
    $relationTypeID = null;
    if( ! isset( $contact->id ) and ! isset( $target_contact->id )) {
        return _crm_error('source or  target contact object does not have contact ID');
    }

    $sourceContact          = $contact->id;
    $targetContact          = $target_contact->id;
    require_once 'CRM/Contact/DAO/RelationshipType.php';
    $reletionType = & new CRM_Contact_DAO_RelationshipType();
    $reletionType->name_a_b = $relationship_type_name;
    $reletionType->find();
    if($reletionType->fetch()) {
        
        $relationTypeID = $reletionType->id;
        $relationTypeID .='_a_b';
    } 
    if (!$relationTypeID) {
        $reletionType = & new CRM_Contact_DAO_RelationshipType();
        $reletionType->name_b_a = $relationship_type_name;
        $reletionType->find();
        if($reletionType->fetch()) {
            
            $relationTypeID = $reletionType->id;
            $relationTypeID .='_b_a';
        }
    }
    
    if (!$relationTypeID) {
        return _crm_error('$relationship_type_ is not valid relationship type ');
    }
    $params['relationship_type_id' ] = $relationTypeID;
    $ids   ['contact'      ] = $sourceContact;
    $params['contact_check'] = array ( $targetContact => $targetContact) ;
    require_once 'CRM/Contact/BAO/Relationship.php';
    
    $errors = CRM_Contact_BAO_Relationship::checkValidRelationship( $params, $ids, $targetContact );
    
    if ( $errors ) {
        return _crm_error($errors);
    }
    
    if ( CRM_Contact_BAO_Relationship::checkDuplicateRelationship( $params ,$sourceContact,$targetContact )) {
        return _crm_error('Duplicate relationship');
    }

    $relationship = CRM_Contact_BAO_Relationship::add($params, $ids, $targetContact);
        
    if ( CRM_Core_Permission::access( 'CiviMember' ) ) {
        CRM_Contact_BAO_Relationship::relatedMemberships( $contact->contact_id,
                                                          $params, $ids,
                                                          CRM_Core_Action::ADD );
    }
    
    return $relationship;
    
}

/**
 * Function to get the relationship
 *
 * @param object  $contact_a                  A valid Contact object 
 * @param object  $contact_b                  A valid Contact object 
 * @param array   $relationship_type_name     An array of Relationship Type Name.
 * @param array   $returnProperties           Which properties should be included in the related Contact object(s). If NULL, the default                                                set of contact properties will be included.
 * @param array   $sort                       Associative array of one or more "property_name"=>"sort direction" pairs which will control                                               order of Contact objects returned
 * @param int     $offset                     Starting row index.
 *
 * @return        Array of all relationship.
 *
 * @access  public
 *
 */
function crm_get_relationships($contact_a,
                               $contact_b=null,
                               $relationship_type_name = null,
                               $returnProperties = null,
                               $sort = null,
                               $offset = 0,
                               $row_count = 25 ) {
    
    if( ! isset( $contact_a->id ) ) {
        return _crm_error('$contact_a is not valid contact datatype');
    }
    
    require_once 'CRM/Contact/BAO/Relationship.php';
    $contactID = $contact_a->id;
    $relationships = CRM_Contact_BAO_Relationship::getRelationship($contactID);
    
    if ( isset( $relationship_type_name ) && is_array( $relationship_type_name )  ){
        $result =array();
        foreach ( $relationship_type_name as $relationshipType ) {
            foreach( $relationships as $key => $relationship ) {
                if ( $relationship['relation'] ==  $relationshipType ) {
                    $result[$key] = $relationship;
                }
            }
        }
        $relationships = $result;
    }
    
    if( isset( $contact_b->id ) ) {
        $cid = $contact_b->id;
        $result =array();
        foreach($relationships as $key => $relationship) {
            if ($relationship['cid'] == $cid ) {
                $result[$key] = $relationship;
            }
        }
        $relationships = $result;
    }
    
    return $relationships;
}

/**
 * Function to delete relationship   
 *
 * @param object $contact                      A valid Contact object (passed by reference).
 * @param object $target_contact               A valid Contact object (passed by reference).
 * @param object $relationship_type       An array of Relationship_type objects.
 *
 *
 * @return null if successful
 * 
 * @access public
 *
 */
function crm_delete_relationship(&$contact, &$target_contact, $relationship_type) {
    require_once 'CRM/Contact/BAO/Relationship.php';
    $relationTypeID = null;
    
    if( ! isset( $contact->id ) && ! isset( $target_contact->id )) {
        return _crm_error('source or  target contact object does not have contact ID');
       
    }
    
    $sourceContact          = $contact->id;
    $targetContact          = $target_contact->id;
    if (!is_array($relationship_type)) {
        return _crm_error('$relationship_type is not array of relationship type objects');
    }
    
    foreach ($relationship_type as $rel ) {
        $relationShip =  & new CRM_Contact_DAO_Relationship();
     
        $relationShip->relationship_type_id = $rel->id ;
        $relationShip->find();
      
        while($relationShip->fetch()) {
            if($relationShip->contact_id_a == $sourceContact || $relationShip->contact_id_b == $sourceContact ){
                if($relationShip->contact_id_a == $targetContact || $relationShip->contact_id_b == $targetContact) {
                    CRM_Contact_BAO_Relationship::del($relationShip->id);   
                }
            }
            
        }
    }
    return null;
}

/**
 * Function to create relationship type
 *
 * @param  array $params   Associative array of property name/value pairs to insert in new relationship type.
 *
 * @return Newly created Relationship_type object
 *
 * @access public
 *
 */
function crm_create_relationship_type($params) {
   
    if(! isset($params['name_a_b']) and ! isset($params['name_b_a']) and ! isset($params['contact_type_a']) and ! isset($params['contact_type_b'] )) {
        return _crm_error('Return array is not properly set');
    }
    require_once 'CRM/Contact/BAO/RelationshipType.php';
    $relationType = CRM_Contact_BAO_RelationshipType::add( $params, $ids);
   
    return $relationType;
    
}

/**
 * Function to get all relationship type
 *
 * retruns  An array of Relationship_type objects
 * @access  public
 *
 */

function crm_get_relationship_types() {
    require_once 'CRM/Contact/DAO/RelationshipType.php';
    $relationshipTypes = array();
    $relationType = & new CRM_Contact_DAO_RelationshipType();
    $relationType->find();
    while($relationType->fetch())
        {
            $relationshipTypes[] = clone($relationType);
        }
    return $relationshipTypes;
    
}



/**
 * Function to update relationship
 *
 * @param object $relationship A valid Relationship object.
 * @param array  $params Associative array of property name/value pairs to be updated. See Data Model for available properties.
 *
 * @return updated relationship object 
 *
 * @access public
 *
 */

function crm_update_relationship(&$relationship, $params )
{
    $ids = array();
    
    if( ! isset($relationship->id) && ! isset($relationship->contact_id_a) && ! isset($relationship->contact_id_b)) {
        return _crm_error('$relationship is not valid relationship type object');
    }
    
    $conactId = $relationship->contact_id_b;
    $params['relationship_type_id' ] = $relationship->relationship_type_id.'_a_b';
    $ids['contact'] = $relationship->contact_id_a;
    $ids['relationship'] = $relationship->id;
    $ids['contactTarget'] = $relationship->contact_id_b;
    
    $relationship = CRM_Contact_BAO_Relationship::add($params, $ids,$conactId);
    
    if ( CRM_Core_Permission::access( 'CiviMember' ) ) {
        
        $params['contact_check'] = array( $relationship->contact_id_b => 1 );
        
        CRM_Contact_BAO_Relationship::relatedMemberships( $relationship->contact_id_a,
                                                          $params, $ids,
                                                          CRM_Core_Action::ADD );
    }
    
    return $relationship;
}


