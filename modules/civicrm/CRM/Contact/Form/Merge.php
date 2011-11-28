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

require_once 'CRM/Core/Form.php';
require_once 'CRM/Dedupe/Merger.php';
require_once 'CRM/Contact/BAO/Contact.php';

class CRM_Contact_Form_Merge extends CRM_Core_Form
{
    // the id of the contact that tere's a duplicate for; this one will 
    // possibly inherit some of $_oid's properties and remain in the system
    var $_cid         = null;

    // the id of the other contact - the duplicate one that will get deleted
    var $_oid         = null;

    var $_contactType = null;
    
    // variable to keep all location block ids.
    protected $_locBlockIds = array( );
    
    // FIXME: QuickForm can't create advcheckboxes with value set to 0 or '0' :(
    // see HTML_QuickForm_advcheckbox::setValues() - but patching that doesn't 
    // help, as QF doesn't put the 0-value elements in exportValues() anyway...
    // to side-step this, we use the below UUID as a (re)placeholder
    var $_qfZeroBug = 'e8cddb72-a257-11dc-b9cc-0016d3330ee9';

    function preProcess()
    {
        require_once 'CRM/Core/BAO/CustomGroup.php';
        require_once 'CRM/Core/OptionGroup.php';
        require_once 'CRM/Core/OptionValue.php';
        if ( ! CRM_Core_Permission::check( 'merge duplicate contacts' ) ) {
            CRM_Core_Error::fatal( ts( 'You do not have access to this page' ) );
        }

        $cid  = CRM_Utils_Request::retrieve( 'cid', 'Positive', $this, true );
        $oid  = CRM_Utils_Request::retrieve( 'oid', 'Positive', $this, true );
        $flip = CRM_Utils_Request::retrieve( 'flip', 'Positive', $this, false );

        $this->_rgid = $rgid = CRM_Utils_Request::retrieve( 'rgid','Positive', $this, false );
        $this->_gid  = $gid  = CRM_Utils_Request::retrieve( 'gid', 'Positive', $this, false );
        $this->_mergeId      = CRM_Utils_Request::retrieve( 'mergeId', 'Positive', $this, false );

        require_once 'CRM/Dedupe/BAO/Rule.php';
        CRM_Dedupe_BAO_Rule::validateContacts( $cid, $oid );

        //load cache mechanism 
        require_once 'CRM/Core/BAO/PrevNextCache.php';
        $contactType = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact', $cid, 'contact_type' );
        $cacheKey  = "merge $contactType";
        $cacheKey .= $rgid ? "_{$rgid}" : '_0';
        $cacheKey .= $gid  ? "_{$gid}"  : '_0';
        
        $join  = "LEFT JOIN civicrm_dedupe_exception de ON ( pn.entity_id1 = de.contact_id1 AND 
                                                             pn.entity_id2 = de.contact_id2 )";
        $where = "de.id IS NULL";   

        $pos = CRM_Core_BAO_PrevNextCache::getPositions( $cacheKey, $cid, $oid, $this->_mergeId, $join, $where, $flip  );
 
        // Block access if user does not have EDIT permissions for both contacts.
        require_once 'CRM/Contact/BAO/Contact/Permission.php';
        if ( ! ( CRM_Contact_BAO_Contact_Permission::allow( $cid, CRM_Core_Permission::EDIT ) && 
                 CRM_Contact_BAO_Contact_Permission::allow( $oid, CRM_Core_Permission::EDIT ) ) ) {
            CRM_Utils_System::permissionDenied( );
        }
        
        // get user info of main contact.
        $config = CRM_Core_Config::singleton( );
        $config->doNotResetCache = 1;

        require_once 'CRM/Core/Permission.php';
        $viewUser = CRM_Core_Permission::check( 'access user profiles' );
        $mainUfId = CRM_Core_BAO_UFMatch::getUFId( $cid );
        $mainUser = null;
        if ( $mainUfId ) {
            if ( $config->userFramework == 'Drupal' ) {
                $mainUser = user_load( $mainUfId );
            } else if ( $config->userFramework == 'Joomla' ) {
                $mainUser = JFactory::getUser( $mainUfId );
            }
            
            $this->assign( 'mainUfId', $mainUfId );
            $this->assign( 'mainUfName', $mainUser->name );
        }

        $flipUrl = CRM_Utils_system::url( 'civicrm/contact/merge', 
                                          "reset=1&action=update&cid={$oid}&oid={$cid}&rgid={$rgid}&gid={$gid}" );
        if ( !$flip ) {
            $flipUrl .= '&flip=1';
        }
        $this->assign( 'flip', $flipUrl );

        $this->prev = $this->next = null;
        foreach ( array( 'prev', 'next' ) as $position ) {
            if ( !empty( $pos[$position] ) ) {
                if ( $pos[$position]['id1'] && $pos[$position]['id2'] ) {
                    $urlParam = "reset=1&cid={$pos[$position]['id1']}&oid={$pos[$position]['id2']}&mergeId={$pos[$position]['mergeId']}&action=update";

                    if ( $rgid ) {
                        $urlParam .= "&rgid={$rgid}";
                    }
                    if ( $gid ) {
                        $urlParam .= "&gid={$gid}";
                    }
                    
                    $this->$position = CRM_Utils_system::url( 'civicrm/contact/merge', $urlParam );
                    $this->assign( $position, $this->$position );
                }
            }
        }

        // get user info of other contact.
        $otherUfId = CRM_Core_BAO_UFMatch::getUFId( $oid );
        $otherUser = null;

        if ( $otherUfId ) {
            if ( $config->userFramework == 'Drupal' ) {
                $otherUser = user_load( $otherUfId );
            } else if ( $config->userFramework == 'Joomla' ) {
                $otherUser = JFactory::getUser( $otherUfId );
            }
        
            $this->assign( 'otherUfId', $otherUfId );
            $this->assign( 'otherUfName', $otherUser->name );
        }
        
        $cmsUser = ( $mainUfId && $otherUfId ) ? true : false;  
        $this->assign( 'user', $cmsUser );
                
        $session = CRM_Core_Session::singleton( );
        
        // context fixed.
        if ( $rgid ) {
            $urlParam = "reset=1&action=browse&rgid={$rgid}";
            if ( $gid ) {
                $urlParam .= "&gid={$gid}";
            }
            $session->pushUserContext( CRM_Utils_system::url( 'civicrm/contact/dedupefind', $urlParam ) );
        }
        
        // ensure that oid is not the current user, if so refuse to do the merge
        
        if ( $session->get( 'userID' ) == $oid ) {
            $display_name = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact', $oid, 'display_name' );
            $message = ts( 'The contact record which is linked to the currently logged in user account - \'%1\' - cannot be deleted.',
                           array( 1 => $display_name ) );
            CRM_Core_Error::statusBounce( $message );
        }
        
        $diffs = CRM_Dedupe_Merger::findDifferences($cid, $oid);
        
        $mainParams  = array('contact_id' => $cid, 'return.display_name' => 1, 'return.contact_sub_type' => 1, 'version' => 2);
        $otherParams = array('contact_id' => $oid, 'return.display_name' => 1, 'return.contact_sub_type' => 1, 'version' => 2);
        // API 2 has to have the requested fields spelt-out for it
        foreach (CRM_Dedupe_Merger::$validFields as $field) {
            $mainParams["return.$field"] = $otherParams["return.$field"] = 1;
        }

        $mainParams  = array('contact_id' => $cid, 'return.display_name' => 1, 'return.contact_sub_type' => 1);
        $otherParams = array('contact_id' => $oid, 'return.display_name' => 1, 'return.contact_sub_type' => 1);
        // API 2 has to have the requested fields spelt-out for it
        foreach (CRM_Dedupe_Merger::$validFields as $field) {
            $mainParams["return.$field"] = $otherParams["return.$field"] = 1;
        }
        $main  =& civicrm_contact_get($mainParams);
        //CRM-4524
        $main  = reset( $main );
        if ( $main['contact_id'] != $cid ) {
            CRM_Core_Error::fatal( ts( 'The main contact record does not exist' ) );
        }

        $other =& civicrm_contact_get($otherParams);
        //CRM-4524
        $other = reset( $other );
        if ( $other['contact_id'] != $oid ) {
            CRM_Core_Error::fatal( ts( 'The other contact record does not exist' ) );
        }

        require_once 'CRM/Contact/BAO/ContactType.php';
        $subtypes = CRM_Contact_BAO_ContactType::subTypePairs( null, true, '' );

        $this->assign('contact_type', $main['contact_type']);
        if(isset($main['contact_sub_type'])) {
            $this->assign('main_contact_subtype',  CRM_Utils_Array::value( 'contact_sub_type', $subtypes[$main['contact_sub_type']] ) );
        }
        if(isset($other['contact_sub_type'])) {
            $this->assign('other_contact_subtype', CRM_Utils_Array::value( 'contact_sub_type', $subtypes[$other['contact_sub_type']] ) );
        }
        $this->assign('main_name',    $main['display_name']);
        $this->assign('other_name',   $other['display_name']);
        $this->assign('main_cid',     $main['contact_id']);
        $this->assign('other_cid',    $other['contact_id']);

        $this->_cid         = $cid;
        $this->_oid         = $oid;
        $this->_rgid        = $rgid;
        $this->_contactType = $main['contact_type'];
        $this->addElement('checkbox', 'toggleSelect', null, null, array('onclick' => "return toggleCheckboxVals('move_',this);"));

        require_once 'CRM/Contact/DAO/Contact.php';
        $fields =& CRM_Contact_DAO_Contact::fields();

        // FIXME: there must be a better way
        foreach (array('main', 'other') as $moniker) {
            $contact =& $$moniker;
            $specialValues[$moniker] = array('preferred_communication_method' => CRM_Utils_array::value('preferred_communication_method', $contact));
            $names = array('preferred_communication_method' => array('newName'   => 'preferred_communication_method_display',
                                                                     'groupName' => 'preferred_communication_method'));
            CRM_Core_OptionGroup::lookupValues($specialValues[$moniker], $names);
        }
        foreach ( CRM_Core_OptionValue::getFields() as $field => $params ) {
            $fields[$field]['title'] = $params['title'];
        }

        if (!isset($diffs['contact'])) $diffs['contact'] = array();
        foreach ($diffs['contact'] as $field) {
            foreach (array('main', 'other') as $moniker) {
                $contact =& $$moniker;
                $value = CRM_Utils_Array::value( $field, $contact );
                $label = isset( $specialValues[$moniker][$field] ) ? $specialValues[$moniker]["{$field}_display"] : $value;
                if ( CRM_Utils_Array::value( 'type', $fields[$field] ) && $fields[$field]['type'] == CRM_Utils_Type::T_DATE ) {
                    if ( $value ) {
                        $value = str_replace( '-', '', $value );
                        $label = CRM_Utils_Date::customFormat( $label );
                    } else {
                        $value = "null";
                    }
                } elseif ( CRM_Utils_Array::value( 'type', $fields[$field] ) && $fields[$field]['type'] == CRM_Utils_Type::T_BOOLEAN ) {
                    if ( $label === '0' ) {
                        $label = ts('[ ]');
                    }
                    if ( $label === '1' ) {
                        $label = ts('[x]');
                    }
                }
                $rows["move_$field"][$moniker] = $label;
                if ( $moniker == 'other' ) {
                    if ( $value === null ) {
                        $value = 'null';
                    }
                    if ( $value === 0 or $value === '0' ) {
                        $value = $this->_qfZeroBug;
                    }
                    $this->addElement( 'advcheckbox', "move_$field", null, null, null, $value );
                }
            }
            $rows["move_$field"]['title'] = $fields[$field]['title'];
        }
        
        // handle location blocks.
        require_once 'api/v2/Location.php';
        $mainParams['version'] = $otherParams['version'] = 3;
        
        $locations['main']  =& civicrm_location_get($mainParams);
        $locations['other'] =& civicrm_location_get($otherParams);
        $allLocationTypes   = CRM_Core_PseudoConstant::locationType( );
        
        $mainLocAddress = array();
        foreach ( array( 'Email', 'Phone', 'IM', 'OpenID', 'Address' ) as $block ) {
            $name = strtolower( $block );
            foreach ( array('main', 'other') as $moniker ) {
                $blockValue = CRM_Utils_Array::value( $name, $locations[$moniker], array( ) );
                
                if ( empty( $blockValue ) ) {
                    $locValue[$moniker][$name] = 0;
                    $locLabel[$moniker][$name] = $locTypes[$moniker][$name] = array( );
                } else {
                    $locValue[$moniker][$name] = true; 
                    foreach ( $blockValue as $count => $blkValues ) {
                        $fldName   = $name;
                        $locTypeId = $blkValues['location_type_id'];
                        if ( $name == 'im' ) $fldName = 'name';
                        if ( $name == 'address' ) $fldName = 'display';
                        $locLabel[$moniker][$name][$count] = $blkValues[$fldName];
                        $locTypes[$moniker][$name][$count] = $locTypeId;
                        if ( $moniker == 'main' && $name == 'address' ) {
                            $mainLocAddress["main_$locTypeId"] = $blkValues[$fldName];
                            $this->_locBlockIds['main']['address'][$locTypeId] = $blkValues['id'];
                        } else {
                            $this->_locBlockIds[$moniker][$name][$count] = $blkValues['id'];
                        }
                    }
                }
            }
            
            if ( $locValue['other'][$name] != 0 ) {
                foreach ( $locLabel['other'][$name] as $count => $value ) {
                    $locTypeId = $locTypes['other'][$name][$count];
                    $rows["move_location_{$name}_$count"]['other'] = $value;
                    $rows["move_location_{$name}_$count"]['main']  = $locLabel['main'][$name][$count];
                    $rows["move_location_{$name}_$count"]['title'] = ts( '%1:%2:%3',
                                                                         array( 1 => $block, 
                                                                                2 => $count, 
                                                                                3 => $allLocationTypes[$locTypeId] ) );
                    
                    $this->addElement( 'advcheckbox', "move_location_{$name}_{$count}" );
                    
                    // make sure default location type is always on top
                    $mainLocTypeId  = CRM_Utils_Array::value( $count, $locTypes['main'][$name], $locTypeId );
                    $locTypeValues  = $allLocationTypes;
                    $defaultLocType = array( $mainLocTypeId => $locTypeValues[$mainLocTypeId] );
                    unset($locTypeValues[$mainLocTypeId]);
                    
                    // keep 1-1 mapping for address - location type.
                    $js = null;
                    if ( $name == 'address' && !empty( $mainLocAddress ) ) {
                        $js = array( 'onChange' => "mergeAddress( this, $count );" );
                    }
                    
                    $this->addElement( 'select', "location[{$name}][$count][locTypeId]", null, 
                                       $defaultLocType + $locTypeValues, $js );
                    
                    if ( $name != 'address' ) {
                        $this->addElement( 'advcheckbox', "location[{$name}][$count][operation]", null, ts('add new') );
                    }
                }
            }
        }
        $this->assign( 'mainLocAddress', json_encode( $mainLocAddress ) );        
        
        // handle custom fields
        $mainTree  =& CRM_Core_BAO_CustomGroup::getTree($this->_contactType, $this, $this->_cid, -1,CRM_Utils_Array::value('contact_sub_type', $main));
        $otherTree =& CRM_Core_BAO_CustomGroup::getTree($this->_contactType, $this, $this->_oid, -1,CRM_Utils_Array::value('contact_sub_type', $other));
        if (!isset($diffs['custom'])) $diffs['custom'] = array();
        foreach ($otherTree as $gid => $group) {
            $foundField = false;
            if ( ! isset( $group['fields'] ) ) {
                continue;
            }

            foreach ($group['fields'] as $fid => $field) {
                if (in_array($fid, $diffs['custom'])) {
                    if (!$foundField) {
                        $rows["custom_group_$gid"]['title'] = $group['title'];
                        $foundField = true;
                    }
                    if ( is_array( $mainTree[$gid]['fields'][$fid]['customValue'] ) ) {
                        foreach ( $mainTree[$gid]['fields'][$fid]['customValue'] as $valueId => $values ) {
                            $rows["move_custom_$fid"]['main']  = CRM_Core_BAO_CustomGroup::formatCustomValues( $values,
                                                                                                               $field, true);
                        }
                    }
                    if ( is_array( $otherTree[$gid]['fields'][$fid]['customValue'] ) ) {
                        foreach ( $otherTree[$gid]['fields'][$fid]['customValue'] as $valueId => $values ) {
                            $rows["move_custom_$fid"]['other'] = CRM_Core_BAO_CustomGroup::formatCustomValues( $values,
                                                                                                               $field, true);
                            $value = $values['data'] ? $values['data'] : $this->_qfZeroBug;
                        }
                    }
                    $rows["move_custom_$fid"]['title'] = $field['label'];
                    
                    $this->addElement('advcheckbox', "move_custom_$fid", null, null, null, $value);
                }
            }
        }
        
        $this->assign('rows', $rows);
        
        // add the related tables and unset the ones that don't sport any of the duplicate contact's info
        $relTables = CRM_Dedupe_Merger::relTables();
        $activeRelTables = CRM_Dedupe_Merger::getActiveRelTables($oid);
        $activeMainRelTables = CRM_Dedupe_Merger::getActiveRelTables($cid);
        foreach ($relTables as $name => $null) {
            if ( !in_array( $name, $activeRelTables ) &&  
                 !( ( $name == 'rel_table_users' ) && in_array( $name, $activeMainRelTables ) ) ) {
                unset($relTables[$name]);
                continue;
            }
            $el = $this->addElement('checkbox', "move_$name");
            $el->setChecked (true);
            $relTables[$name]['main_url']  = str_replace('$cid', $cid, $relTables[$name]['url']);
            $relTables[$name]['other_url'] = str_replace('$cid', $oid, $relTables[$name]['url']);
            if ( $name == 'rel_table_users' ) {
                $relTables[$name]['main_url']    = str_replace('$ufid', $mainUfId,  $relTables[$name]['url']);
                $relTables[$name]['other_url']   = str_replace('$ufid', $otherUfId, $relTables[$name]['url']);
                $find = array( '$ufid', '$ufname');
                if($mainUser) {
                    $replace = array( $mainUfId, $mainUser->name );
                    $relTables[$name]['main_title']  = str_replace( $find, $replace, $relTables[$name]['title']);
                }
                if($otherUser) {
                    $replace = array( $otherUfId, $otherUser->name );
                    $relTables[$name]['other_title'] = str_replace( $find, $replace, $relTables[$name]['title']);
                }
            }
            if ( $name == 'rel_table_memberships' ) {
                $this->addElement('checkbox', "operation[move_{$name}][add]", null, ts('add new'));
            }
        }
        foreach ($relTables as $name => $null) {
            $relTables["move_$name"] = $relTables[$name];
            unset($relTables[$name]);
        }
        $this->assign('rel_tables', $relTables);
        $this->assign( 'userContextURL', $session->readUserContext( ) );
    }
    
    function setDefaultValues()
    {
        return array('deleteOther' => 1);
    }
    
    function addRules()
    {
    }

    public function buildQuickForm()
    {
        CRM_Utils_System::setTitle( ts( 'Merge %1s', array( 1 => $this->_contactType ) ) );
        $name = ts('Merge');
        if ( $this->next ) {
            $name = ts('Merge and Goto Next Pair');
        }
        
        if ( $this->next || $this->prev ) {
            $button = array(                                 
                            array( 'type' => 'next',   
                                   'name' => $name,
                                   'isDefault' => true ),
                            array( 'type' => 'submit',
                                   'name' => ts('Merge and Goto Listing') ),  
                            array( 'type' => 'cancel',
                                   'name' => ts('Cancel') ) );
        } else {
            $button = array(                                 
                            array( 'type' => 'next',   
                                   'name' => $name,
                                   'isDefault' => true ),
                            array( 'type' => 'cancel',
                                   'name' => ts('Cancel') ) );
        }
        
        $this->addButtons( $button );
    }
    
    public function postProcess()
    {
        $formValues = $this->exportValues();
        
        // reset all selected contact ids from session 
        // when we came from search context, CRM-3526
        $session = CRM_Core_Session::singleton( );
        if ( $session->get('selectedSearchContactIds') ) {
            $session->resetScope( 'selectedSearchContactIds' );
        }
        
        $relTables =& CRM_Dedupe_Merger::relTables();
        $moveTables = $locBlocks = $tableOperations = array( );
        foreach ( $formValues as $key => $value ) {
            if ($value == $this->_qfZeroBug) $value = '0';
            if ((in_array(substr($key, 5), CRM_Dedupe_Merger::$validFields) or 
                 substr($key, 0, 12) == 'move_custom_') and $value != null) {
                $submitted[substr($key, 5)] = $value;
            } elseif (substr($key, 0, 14) == 'move_location_' and $value != null) {
                $locField   = explode( '_',  $key );
                $fieldName  = $locField[2];
                $fieldCount = $locField[3];
                $operation  = CRM_Utils_Array::value( 'operation', $formValues['location'][$fieldName][$fieldCount] );
                // default operation is overwrite.
                if ( !$operation ) {
                    $operation = 2; 
                }
                
                $locBlocks[$fieldName][$fieldCount]['operation'] = $operation;
                $locBlocks[$fieldName][$fieldCount]['locTypeId'] = 
                    CRM_Utils_Array::value( 'locTypeId', $formValues['location'][$fieldName][$fieldCount] );
            } elseif (substr($key, 0, 15) == 'move_rel_table_' and $value == '1') {
                $moveTables = array_merge($moveTables, $relTables[substr($key, 5)]['tables']);
                if ( array_key_exists('operation', $formValues) ) {
                    foreach ( $relTables[substr($key, 5)]['tables'] as $table ) {
                        if ( array_key_exists($key, $formValues['operation']) ) {
                            $tableOperations[$table] = $formValues['operation'][$key];
                        }
                    }
                }
            }
        }
        
        // process location blocks.
        if ( !empty( $locBlocks ) ) {
            $locComponent = array( 'email'   => 'Email',
                                   'phone'   => 'Phone',
                                   'im'      => 'IM',
                                   'openid'  => 'OpenID',
                                   'address' => 'Address' );
            
            require_once 'CRM/Contact/BAO/Contact.php';
            $primaryBlockIds = CRM_Contact_BAO_Contact::getLocBlockIds( $this->_cid, array( 'is_primary' => 1 ) );
            $billingBlockIds = CRM_Contact_BAO_Contact::getLocBlockIds( $this->_cid, array( 'is_billing' => 1 ) );
            
            foreach ( $locBlocks as $name => $block ) {
                if ( !is_array($block) || CRM_Utils_System::isNull($block) ) continue; 
                $daoName = $locComponent[$name];
                $primaryDAOId = (array_key_exists($name, $primaryBlockIds) )?array_pop($primaryBlockIds[$name]):null;
                $billingDAOId = (array_key_exists($name, $billingBlockIds) )?array_pop($billingBlockIds[$name]):null;
                
                foreach ( $block as $blkCount => $values ) {
                    $locTypeId      = CRM_Utils_Array::value( 'locTypeId', $values, 1 );
                    $operation      = CRM_Utils_Array::value( 'operation', $values, 2 );
                    $updateBlockId  = CRM_Utils_Array::value( $blkCount,   $this->_locBlockIds['other'][$name] );
                    
                    // keep 1-1 mapping for address - loc type.
                    $idKey = $blkCount;
                    if ( $name == 'address' ) $idKey = $locTypeId;  
                    $deleteBlockId = CRM_Utils_Array::value( $idKey, $this->_locBlockIds['main'][$name] );
                    
                    if ( !$updateBlockId ) continue;
                    
                    require_once "CRM/Core/DAO/{$daoName}.php";
                    eval("\$updateDAO = new CRM_Core_DAO_$daoName();");
                    $updateDAO->id = $updateBlockId;
                    $updateDAO->contact_id = $this->_cid;
                    $updateDAO->location_type_id = $locTypeId;
                    
                    // contact having primary block.
                    if ( $primaryDAOId ) $updateDAO->is_primary = 0;
                    if ( $billingDAOId ) $updateDAO->is_billing = 0;
                    
                    // overwrite - need to delete block from main contact.
                    if ( $deleteBlockId && ($operation == 2) ) {
                        eval("\$deleteDAO = new CRM_Core_DAO_$daoName();");
                        $deleteDAO->id = $deleteBlockId;
                        $deleteDAO->find( true );
                        
                        // since we overwrite primary block.
                        if ( $primaryDAOId && ($primaryDAOId == $deleteDAO->id) ) $updateDAO->is_primary = 1;
                        if ( $billingDAOId && ($billingDAOId == $deleteDAO->id) ) $updateDAO->is_billing = 1;
                        
                        $deleteDAO->delete( );
                        $deleteDAO->free( );
                    }
                    
                    $updateDAO->update( );
                    $updateDAO->free( );
                }
            }
        }
        
        // FIXME: fix gender, prefix and postfix, so they're edible by createProfileContact()
        $names['gender']            = array( 'newName' => 'gender_id',          'groupName' => 'gender' );
        $names['individual_prefix'] = array( 'newName' => 'prefix_id',          'groupName' => 'individual_prefix' );
        $names['individual_suffix'] = array( 'newName' => 'suffix_id',          'groupName' => 'individual_suffix' );
        $names['addressee']         = array( 'newName' => 'addressee_id',       'groupName' => 'addressee' );
        $names['email_greeting']    = array( 'newName' => 'email_greeting_id',  'groupName' => 'email_greeting' );
        $names['postal_greeting']   = array( 'newName' => 'postal_greeting_id', 'groupName' => 'postal_greeting' );
        CRM_Core_OptionGroup::lookupValues( $submitted, $names, true );

        // FIXME: fix custom fields so they're edible by createProfileContact()
        $cgTree =& CRM_Core_BAO_CustomGroup::getTree( $this->_contactType, $this, null, -1 );
        
        $cFields = array( );
        foreach ( $cgTree as $key => $group ) {
            if (!isset($group['fields'])) continue;
            foreach ( $group['fields'] as $fid => $field ) {
                $cFields[$fid]['attributes'] = $field;
            }
        }
        
        if ( !isset( $submitted ) ) {
            $submitted = array();
        }
        foreach ( $submitted as $key => $value ) {
            if ( substr( $key, 0, 7 ) == 'custom_' ) {
                $fid = (int) substr($key, 7);
                $htmlType = $cFields[$fid]['attributes']['html_type'];
                switch ( $htmlType ) {
                    
                case 'File':
                    $customFiles[] = $fid;
                    unset($submitted["custom_$fid"]);
                    break;

                case 'Select Country':
                case 'Select State/Province':
                    $submitted[$key] = CRM_Core_BAO_CustomField::getDisplayValue($value, $fid, $cFields);
                    break;
                    
                case 'CheckBox':
                case 'AdvMulti-Select':
                case 'Multi-Select':
                case 'Multi-Select Country':
                case 'Multi-Select State/Province':
                    // Merge values from both contacts for multivalue fields, CRM-4385
                    // get the existing custom values from db.
                    require_once 'CRM/Core/BAO/CustomValueTable.php';
                    $customParams = array( 'entityID' => $this->_cid, $key => true );
                    $customfieldValues = CRM_Core_BAO_CustomValueTable::getValues( $customParams ); 
                    if ( CRM_Utils_array::value( $key, $customfieldValues ) ) {
                        $existingValue = explode( CRM_Core_DAO::VALUE_SEPARATOR, $customfieldValues[$key] );
                        if ( is_array( $existingValue ) && !empty( $existingValue ) ) {
                            $mergeValue = $submmtedCustomValue = array( );
                            if ( $value ) {
                                $submmtedCustomValue = explode( CRM_Core_DAO::VALUE_SEPARATOR, $value );
                            }
                            
                            //hack to remove null and duplicate values from array.
                            foreach ( array_merge( $submmtedCustomValue, $existingValue ) as $k => $v ) {
                                if ( $v != '' && !in_array( $v, $mergeValue ) ) {
                                    $mergeValue[] = $v;
                                }
                            }
                            
                            //keep state and country as array format. 
                            //for checkbox and m-select format w/ VALUE_SEPARATOR
                            if ( in_array( $htmlType, array( 'CheckBox', 'Multi-Select', 'AdvMulti-Select' ) ) ) {
                                $submitted[$key] = 
                                    CRM_Core_DAO::VALUE_SEPARATOR .
                                    implode( CRM_Core_DAO::VALUE_SEPARATOR,
                                             $mergeValue ) .
                                    CRM_Core_DAO::VALUE_SEPARATOR;
                            } else {
                                $submitted[$key] = $mergeValue; 
                            }
                        }
                    } else if ( in_array( $htmlType, array( 'Multi-Select Country', 'Multi-Select State/Province' ) ) ) {
                        //we require submitted values should be in array format
                        if ( $value ) {
                            $mergeValueArray = explode( CRM_Core_DAO::VALUE_SEPARATOR, $value );   
                            //hack to remove null values from array.
                            $mergeValue = array( );
                            foreach (  $mergeValueArray as $k => $v ) {
                                if ( $v != '' ) {
                                    $mergeValue[] = $v;
                                }
                            }
                            $submitted[$key] = $mergeValue; 
                        }
                    }
                    break;
                    
                default:
                    break;
                }
            }
        }

        // handle the related tables
        if ( isset( $moveTables ) ) {
            CRM_Dedupe_Merger::moveContactBelongings( $this->_cid, $this->_oid, $moveTables, $tableOperations );
        }
        
        // move file custom fields
        // FIXME: move this someplace else (one of the BAOs) after discussing
        // where to, and whether CRM_Core_BAO_File::delete() shouldn't actually,
        // like, delete a file...
        require_once 'CRM/Core/BAO/File.php';
        require_once 'CRM/Core/DAO/CustomField.php';
        require_once 'CRM/Core/DAO/CustomGroup.php';
        require_once 'CRM/Core/DAO/EntityFile.php';
        require_once 'CRM/Core/Config.php';

        if ( !isset( $customFiles ) ) {
            $customFiles = array();
        }
        foreach ( $customFiles as $customId ) {
            list( $tableName, $columnName, $groupID ) = CRM_Core_BAO_CustomField::getTableColumnGroup( $customId );

            // get the contact_id -> file_id mapping
            $fileIds = array();
            $sql = "SELECT entity_id, {$columnName} AS file_id FROM {$tableName} WHERE entity_id IN ({$this->_cid}, {$this->_oid})";
            $dao =& CRM_Core_DAO::executeQuery($sql, CRM_Core_DAO::$_nullArray);
            while ($dao->fetch()) {
                $fileIds[$dao->entity_id] = $dao->file_id;
            }
            $dao->free();

            // delete the main contact's file
            if ( !empty($fileIds[$this->_cid]) ) {
                CRM_Core_BAO_File::delete($fileIds[$this->_cid], $this->_cid, $customId);
            }
            
            // move the other contact's file to main contact
            $sql = "UPDATE {$tableName} SET {$columnName} = {$fileIds[$this->_oid]} WHERE entity_id = {$this->_cid}";
            CRM_Core_DAO::executeQuery($sql, CRM_Core_DAO::$_nullArray);
            $sql = "UPDATE civicrm_entity_file SET entity_id = {$this->_cid} WHERE entity_table = '{$tableName}' AND file_id = {$fileIds[$this->_oid]}";
            CRM_Core_DAO::executeQuery($sql, CRM_Core_DAO::$_nullArray);
        }
        
        // move view only custom fields CRM-5362
        $viewOnlyCustomFields = array( );
        foreach ( $submitted as $key => $value ) {
            $fid = (int) substr($key, 7);
            if ( array_key_exists( $fid, $cFields ) && 
                 CRM_Utils_Array::value( 'is_view', $cFields[$fid]['attributes'] ) ) {
                $viewOnlyCustomFields[$key] = $value;
            }
        }
        //special case to set values for view only, CRM-5362
        if ( !empty( $viewOnlyCustomFields ) ) {
            require_once 'CRM/Core/BAO/CustomValueTable.php';
            $viewOnlyCustomFields['entityID'] = $this->_cid;
            CRM_Core_BAO_CustomValueTable::setValues( $viewOnlyCustomFields );
        }
        
        // move other's belongings and delete the other contact
        CRM_Dedupe_Merger::moveContactBelongings( $this->_cid, $this->_oid );
        $otherParams = array('contact_id' => $this->_oid);

        if ( CRM_Core_Permission::check( 'merge duplicate contacts' ) && 
             CRM_Core_Permission::check( 'delete contacts' ) ) {
            // if ext id is submitted then set it null for contact to be deleted
            if ( CRM_Utils_Array::value( 'external_identifier', $submitted ) ) {
                $query = "UPDATE civicrm_contact SET external_identifier = null WHERE id = {$this->_oid}";
                CRM_Core_DAO::executeQuery( $query );
            }
            civicrm_contact_delete($otherParams);
            CRM_Core_BAO_PrevNextCache::deleteItem( $this->_oid );
        } else {
            CRM_Core_Session::setStatus( ts('Do not have sufficient permission to delete duplicate contact.') );
        }
        
        if ( isset( $submitted ) ) {
            $submitted['contact_id'] = $this->_cid;
            CRM_Contact_BAO_Contact::createProfileContact( $submitted, CRM_Core_DAO::$_nullArray, $this->_cid );
        }
        CRM_Core_Session::setStatus( ts('The contacts have been merged.') );
        $url = CRM_Utils_System::url( 'civicrm/contact/view', "reset=1&cid={$this->_cid}" );
        if ( CRM_Utils_Array::value('_qf_Merge_submit',$formValues) ) {
            $listParamsURL =  "reset=1&action=update&rgid={$this->_rgid}";
            if ( $this->_gid ) {
                $listParamsURL .= "&gid={$this->_gid}";
            }
            $lisitingURL = CRM_Utils_System::url( 'civicrm/contact/dedupefind', 
                                                  $listParamsURL
                                                  );
            CRM_Utils_System::redirect( $lisitingURL );
        }
      
        if ( $this->next && $this->_mergeId ) {
            $cacheKey  = "merge {$this->_contactType}";
            $cacheKey .= $this->_rgid ? "_{$this->_rgid}" : '_0';
            $cacheKey .= $this->_gid  ? "_{$this->_gid}"  : '_0';
        
            $join  = "LEFT JOIN civicrm_dedupe_exception de ON ( pn.entity_id1 = de.contact_id1 AND 
                                                                 pn.entity_id2 = de.contact_id2 )";
            $where = "de.id IS NULL";   

            $pos = CRM_Core_BAO_PrevNextCache::getPositions( $cacheKey, null, null, $this->_mergeId, $join, $where );

            if ( !empty($pos) &&
                 $pos['next']['id1'] && 
                 $pos['next']['id2'] ) {

                $urlParam = "reset=1&cid={$pos['next']['id1']}&oid={$pos['next']['id2']}&mergeId={$pos['next']['mergeId']}&action=update";
                if ( $this->_rgid ) $urlParam .= "&rgid={$this->_rgid}";
                if ( $this->_gid )  $urlParam .= "&gid={$this->_gid}";
                
                $url  = CRM_Utils_system::url( 'civicrm/contact/merge', $urlParam );
            }
        }

        CRM_Utils_System::redirect( $url );
    }
        
}
