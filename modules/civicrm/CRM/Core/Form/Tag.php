<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

/**
 * This class generates form element for free tag widget
 * 
 */
class CRM_Core_Form_Tag
{
    public $_entityTagValues;
    
    /**
     * Function to build tag widget if correct parent is passed
     * 
     * @param object  $form form object
     * @param string  $parentName parent name ( tag name)
     * @param string  $entityTable entitytable 'eg: civicrm_contact'
     * @param int     $entityId    entityid  'eg: contact id'
     *
     * @return void
     * @access public
     * @static
     */
    static function buildQuickForm( &$form, $parentNames, $entityTable, $entityId = null, $skipTagCreate = false, 
                                    $skipEntityAction = false, $searchMode = false ) {        
        $tagset = $form->_entityTagValues = array( );
        $mode   = null;

        foreach( $parentNames as &$parentNameItem ) {
            // get the parent id for tag list input for keyword
            $parentId = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_Tag', $parentNameItem, 'id',  'name' );
            
            // check if parent exists
            $entityTags = array( );
            if ( $parentId ) {
                $tagsetItem = 'parentId_'.$parentId;
                $tagset[$tagsetItem]['parentName'] = $parentNameItem;
                $tagset[$tagsetItem]['parentID'  ] =  $parentId;        

                //tokeninput url
                $qparams = "parentId={$parentId}";

                if ( $searchMode ) {
                    $qparams .= '&search=1';
                }

                $tagUrl = CRM_Utils_System::url( 'civicrm/ajax/taglist',
                                                 $qparams,
                                                 false, null, false );

                $tagset[$tagsetItem]['tagUrl'          ] = $tagUrl;
                $tagset[$tagsetItem]['entityTable'     ] = $entityTable;
                $tagset[$tagsetItem]['skipTagCreate'   ] = $skipTagCreate;
                $tagset[$tagsetItem]['skipEntityAction'] = $skipEntityAction;
                
                switch ( $entityTable ) {
                case 'civicrm_activity':
                    $tagsetElementName = "activity_taglist";
                    $mode = 'activity';
                    break;		
                case 'civicrm_case'    :
                    $tagsetElementName = "case_taglist";
                    $mode = 'case';
                    break;
                default: 		
                    $tagsetElementName = "contact_taglist";
                    $mode = 'contact';
                }

                $tagset[$tagsetItem]['tagsetElementName'  ] = $tagsetElementName;

                $form->add( 'text', "{$tagsetElementName}[{$parentId}]", null );
                if ( $entityId ) {
                    $tagset[$tagsetItem]['entityId'] = $entityId;
                    require_once 'CRM/Core/BAO/EntityTag.php';
                    $entityTags = CRM_Core_BAO_EntityTag::getChildEntityTags( $parentId, $entityId, $entityTable );                    
                } else {

                    switch ( $entityTable ) {
                    case 'civicrm_activity':
                        if ( !empty( $form->_submitValues['activity_taglist'] ) && 
                            CRM_Utils_Array::value( $parentId, $form->_submitValues['activity_taglist']) ) {
                            $allTags = CRM_Core_Pseudoconstant::tag( );
                            $tagIds  = explode( ',', $form->_submitValues['activity_taglist'][$parentId] );
                            foreach( $tagIds as $tagId ) {
                                if ( is_numeric( $tagId ) ) {
                                    $tagName = $allTags[$tagId];
                                } else {
                                    $tagName = $tagId;
                                }
                                $entityTags[$tagId] = array( 'id'   => $tagId,
                                                             'name' => $tagName );
                            } 
                        }
                        break;

                    case 'civicrm_case':
                        if ( !empty( $form->_submitValues['case_taglist'] ) && 
                           CRM_Utils_Array::value( $parentId, $form->_submitValues['case_taglist']) ) {
                           $allTags = CRM_Core_Pseudoconstant::tag( );
                           $tagIds  = explode( ',', $form->_submitValues['case_taglist'][$parentId] );
                           foreach( $tagIds as $tagId ) {
                               if ( is_numeric( $tagId ) ) {
                                   $tagName = $allTags[$tagId];
                               } else {
                                   $tagName = $tagId;
                               }
                               $entityTags[$tagId] = array( 'id'   => $tagId,
                                                            'name' => $tagName );
                           }
                        }    
                        break;
                    
                    default: 		
                        if ( !empty($form->_formValues['contact_tags']) ) {
                            require_once 'CRM/Core/BAO/Tag.php';
                            $contactTags = CRM_Core_BAO_Tag::getTagsUsedFor( 'civicrm_contact', true, false, $parentId );

                            foreach( array_keys($form->_formValues['contact_tags']) as $tagId ) {
                                if ( CRM_Utils_Array::value($tagId, $contactTags) ) {
                                    $tagName = $tagId;
                                    if ( is_numeric($tagId) ) $tagName = $contactTags[$tagId];

                                    $entityTags[$tagId] = array( 'id'   => $tagId,
                                                                 'name' => $tagName );
                                }
                            }
                        }                  
                    }
                }    
                
                if ( !empty( $entityTags ) ) {
                    // assign as simple array for display in smarty
                    $tagset[$tagsetItem]['entityTagsArray'] =  $entityTags;
                    // assign as json for js widget
                    $tagset[$tagsetItem]['entityTags'] =  json_encode( array_values( $entityTags ) );

                    if ( !empty( $form->_entityTagValues ) ) {
                        $form->_entityTagValues = CRM_Utils_Array::crmArrayMerge( $entityTags, $form->_entityTagValues );
                    } else {
                        $form->_entityTagValues = $entityTags;
                    }                        
                }
            }
        }
	
        if ( !empty( $tagset ) ) {
            $form->assign( "tagsetInfo_$mode", $tagset );
        }
    }
    
    /**
     * Function to save entity tags when it is not save used AJAX
     *
     */
    static function postProcess( &$params, $entityId, $entityTable = 'civicrm_contact', &$form ) {
        foreach( $params as $value ) {
            if ( !$value ) {
                continue;
            }
            $tagsIDs = explode( ',', $value );
            $insertValues = array( );
            $insertSQL    = null;
            if ( !empty( $tagsIDs ) ) {
                foreach( $tagsIDs as $tagId ) {
                    if ( is_numeric( $tagId ) ) {
                        if ( $form->_action != CRM_Core_Action::UPDATE ) {
                            $insertValues[] = "( {$tagId}, {$entityId}, '{$entityTable}' ) ";
                        } else if ( !array_key_exists( $tagId, $form->_entityTagValues ) ) {
                            $insertValues[] = "( {$tagId}, {$entityId}, '{$entityTable}' ) ";
                        }
                    }
                }
                
                if ( !empty( $insertValues ) ) {
                    $insertSQL = 'INSERT INTO civicrm_entity_tag ( tag_id, entity_id, entity_table ) VALUES '. implode( ', ', $insertValues ) . ';';
                    CRM_Core_DAO::executeQuery( $insertSQL );
                }
            }
        }
    } 
}
