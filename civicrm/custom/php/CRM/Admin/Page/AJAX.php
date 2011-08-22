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
 * This class contains all the function that are called using AJAX
 */
class CRM_Admin_Page_AJAX
{
    /**
     * Function to build menu tree     
     */    
    static function getNavigationList( ) {
        require_once 'CRM/Core/BAO/Navigation.php';
        echo CRM_Core_BAO_Navigation::buildNavigation( true, false );           
        CRM_Utils_System::civiExit();
    }
    
    /**
     * Function to process drag/move action for menu tree
     */
    static function menuTree( ) {
        require_once 'CRM/Core/BAO/Navigation.php';
        echo CRM_Core_BAO_Navigation::processNavigation( $_GET );           
        CRM_Utils_System::civiExit();
    }

    /**
     * Function to build status message while 
     * enabling/ disabling various objects
     */
    static function getStatusMsg( ) 
    {        
        $recordID  = CRM_Utils_Type::escape( $_POST['recordID'], 'Integer' );
        $recordBAO = CRM_Utils_Type::escape( $_POST['recordBAO'], 'String' );
        $op        = CRM_Utils_Type::escape( $_POST['op'], 'String' );
        $show      = null;

        if ($op == 'disable-enable') {
            $status = ts('Are you sure you want to enable this record?');
        } else {
            switch ($recordBAO) {
                
            case 'CRM_Core_BAO_UFGroup':
                require_once(str_replace('_', DIRECTORY_SEPARATOR, $recordBAO) . ".php");
                $method = 'getUFJoinRecord'; 
                $result = array($recordBAO,$method);
                $ufJoin = call_user_func_array(($result), array($recordID,true));
                if (!empty($ufJoin)) {
                    $status = ts('This profile is currently used for %1.', array(1 => implode (', ' , $ufJoin))) . ' <br/><br/>' . ts('If you disable the profile - it will be removed from these forms and/or modules. Do you want to continue?');
                } else {
                    $status = ts('Are you sure you want to disable this profile?');   
                }
                break;
            
            case 'CRM_Price_BAO_Set':
                require_once(str_replace('_', DIRECTORY_SEPARATOR, $recordBAO) . ".php");
                $usedBy   = CRM_Price_BAO_Set::getUsedBy( $recordID );
                $priceSet = CRM_Price_BAO_Set::getTitle( $recordID );
                
                if ( !CRM_Utils_System::isNull( $usedBy ) ) {
                    $template = CRM_Core_Smarty::singleton( );
                    $template->assign( 'usedBy', $usedBy );
                    $comps = array( "Event"        => "civicrm_event", 
                                    "Contribution" => "civicrm_contribution_page" );
                    $contexts = array( );
                    foreach ( $comps as $name => $table ) {
                        if ( array_key_exists( $table, $usedBy ) ) {
                            $contexts[] = $name;
                        }
                    }
                    $template->assign( 'contexts', $contexts );
                    
                    $show   = "noButton";
                    $table  = $template->fetch( 'CRM/Price/Page/table.tpl' );
                    $status = ts('Unable to disable the \'%1\' price set - it is currently in use by one or more active events, contribution pages or contributions.', array(1 => $priceSet)) . "<br/> $table";
                } else {
                    $status = ts('Are you sure you want to disable \'%1\' Price Set?', array(1 => $priceSet));
                }
                break;
                
            case 'CRM_Event_BAO_Event':
                $status = ts('Are you sure you want to disable this Event?');
                break;
                
            case 'CRM_Core_BAO_UFField':
                $status = ts('Are you sure you want to disable this CiviCRM Profile field?');
                break;   
                
            case 'CRM_Contribute_BAO_ManagePremiums':
                $status = ts('Are you sure you want to disable this premium? This action will remove the premium from any contribution pages that currently offer it. However it will not delete the premium record - so you can re-enable it and add it back to your contribution page(s) at a later time.');
                break;
                
            case 'CRM_Contact_BAO_RelationshipType':
                $status = ts('Are you sure you want to disable this relationship type?') . '<br/><br/>' . ts('Users will no longer be able to select this value when adding or editing relationships between contacts.');
                break;
                
            case 'CRM_Contribute_BAO_ContributionType':
                $status = ts('Are you sure you want to disable this contribution type?');
                break;
                
            case 'CRM_Core_BAO_PaymentProcessor':
                $status = ts('Are you sure you want to disable this payment processor?') . ' <br/><br/>' . ts('Users will no longer be able to select this value when adding or editing transaction pages.');
                break;

            case 'CRM_Core_BAO_PaymentProcessorType':
                $status = ts('Are you sure you want to disable this payment processor type?');
                 break;
    
            case 'CRM_Core_BAO_LocationType':
                $status = ts('Are you sure you want to disable this location type?') . ' <br/><br/>' . ts('Users will no longer be able to select this value when adding or editing contact locations.');
                break;

            case 'CRM_Event_BAO_ParticipantStatusType':
                $status = ts('Are you sure you want to disable this Participant Status?') . '<br/><br/> ' . ts('Users will no longer be able to select this value when adding or editing Participant Status.');
                break;
                
            case 'CRM_Mailing_BAO_Component':
                $status = ts('Are you sure you want to disable this component?');
                break;
                
            case 'CRM_Core_BAO_CustomField':
                $status = ts('Are you sure you want to disable this custom data field?');
                break;
                
            case 'CRM_Core_BAO_CustomGroup':
                $status = ts('Are you sure you want to disable this custom data group? Any profile fields that are linked to custom fields of this group will be disabled.');
                break;

            case 'CRM_Core_BAO_MessageTemplates':
                $status = ts('Are you sure you want to disable this message tempate?');
                break;
                
            case 'CRM_ACL_BAO_ACL':
                $status = ts('Are you sure you want to disable this ACL?');
                break;
                
            case 'CRM_ACL_BAO_EntityRole':
                $status = ts('Are you sure you want to disable this ACL Role Assignment?');
                break;
            case 'CRM_Member_BAO_MembershipType':
                $status = ts('Are you sure you want to disable this membership type?');
                break;
        
            case 'CRM_Member_BAO_MembershipStatus':
                $status = ts('Are you sure you want to disable this membership status rule?');
                break;
                
            case 'CRM_Price_BAO_Field':
                $status = ts('Are you sure you want to disable this price field?');
                break;
                
            case 'CRM_Contact_BAO_Group':
                $status = ts('Are you sure you want to disable this Group?');
                break;
                
            case 'CRM_Core_BAO_OptionGroup':
                $status = ts('Are you sure you want to disable this Option?');
                break;

            case 'CRM_Contact_BAO_ContactType':
                $status = ts('Are you sure you want to disable this Contact Type?');
                break;
                
            case 'CRM_Core_BAO_OptionValue':
                require_once(str_replace('_', DIRECTORY_SEPARATOR, $recordBAO) . ".php");
                $label = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionValue', $recordID, 'label' );
                $status = ts('Are you sure you want to disable this \'%1\' record ?', array(1 => $label));
                break;

            default:
                $status = ts('Are you sure you want to disable this record?');
                break;
            }
        }
        $statusMessage['status'] = $status;
        $statusMessage['show']   = $show;
        
        echo json_encode( $statusMessage );
        CRM_Utils_System::civiExit();
    }
    
    static function getTagList( ) {
        $name     = CRM_Utils_Type::escape( $_GET['name'], 'String' );
        $parentId = CRM_Utils_Type::escape( $_GET['parentId'], 'Integer' );
        
        $isSearch = null;
        if ( isset( $_GET['search'] ) ) {
            $isSearch = CRM_Utils_Type::escape( $_GET['search'], 'Integer' );
        }

        $tags = array( );

	  //NYSS treat issue codes and keywords using normal method
      if ( $parentId != 292 || $isSearch ) {
            
        // always add current search term as possible tag
        // here we append :::value to determine if existing / new tag should be created
        if ( !$isSearch ) {
            $tags[] = array( 'name' => $name,
                             'id'   => $name. ":::value" );            
        }

        $query = "SELECT id, name FROM civicrm_tag WHERE parent_id = {$parentId} and name LIKE '%{$name}%'";
        $dao = CRM_Core_DAO::executeQuery( $query );
        
        while( $dao->fetch( ) ) {
            // make sure we return tag name entered by user only if it does not exists in db
            if ( $name == $dao->name ) {
                $tags = array();
            }
            // escape double quotes, which break results js
            $tags[] = array( 'name' =>  addcslashes($dao->name, '"'),
                             'id'   => $dao->id );
        }
        
        echo json_encode($tags);         
        CRM_Utils_System::civiExit( );

      //NYSS leg positions should retrieve list from open leg and create value in tag table
      } elseif ( $parentId == 292 ) {
        
        		$billNo = $name; 
        		$ch = curl_init();

				curl_setopt($ch, CURLOPT_URL,"http://open.nysenate.gov/legislation/search/?term=otype:bill+AND+oid:(" . $billNo . "+OR+" . $billNo . "*)&searchType=&format=json&pageSize=10");
        		
        		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$content = curl_exec($ch);
				
				$json = array();
		 		$json = json_decode($content, true);
       
				curl_close($ch);
							
        		function cmp($a, $b)
				{
	
					$a1 = str_split($a['id']);
					$b1 = str_split($b['id']);
					$a1_c = count($a1);
					$b1_c = count($b1);
		
					$a1_num = '';
					$b1_num = '';
					if (preg_match( "/^[A-Z]+$/",$a1[$a1_c-1])){
					for ($i=1; $i<$a1_c - 1; $i++)
 			 			{
  						$a1_num .= $a1[$i] ;
  						}
						$a1_num = intval($a1_num);
					}
				else {
					for ($i=1; $i<= $a1_c - 1; $i++)
 			 			{
  						$a1_num .= $a1[$i] ;
  						}
						$a1_num = intval($a1_num);			
						}
					if (preg_match( "/^[A-Z]+$/",$b1[$b1_c-1])){ 
						for ($i=1; $i<$b1_c - 1; $i++)
 			 				{
  							$b1_num .= $b1[$i] ;
  							}
						$b1_num = intval($b1_num);
						}
					else { 
						for ($i=1; $i<= $b1_c - 1; $i++)
 			 				{
  							$b1_num .= $b1[$i] ;
  							}
						$b1_num = intval($b1_num);			
						}

						if ($a1 == $b1) 
            				return 0;
        				elseif ($a1[0] < $b1[0])
        					return -1;
        				elseif ($a1[0] > $b1[0])
        					return 1;
        				else {
        					if ($a1_num < $b1_num)
        						return -1;
        					elseif ($a1_num > $b1_num)
        						return 1;
        					else
        						{
        					if($a1 < $b1)
        						return -1;
        					elseif ($a1 > $b1)
        						return 1;
        					}
        				}
					}
				usort($json, "cmp");
				
        		for ($j=0; $j < count($json); $j++){


					//construct positions
					$positiontags = array();
					$positiontags[] = $json[$j]['id'];
					$positiontags[] = $json[$j]['id'].' - FOR';
					$positiontags[] = $json[$j]['id'].' - AGAINST';

					
					//construct tags array
					foreach ( $positiontags as $positiontag ) {
						
                        //if ( $json[$j]['sponsor'] ) { $positiontag_name .= ' ('.$json[$j]['sponsor'].')'; }
						
						//add sponsor to display if exists
        	 			if ( $json[$j]['sponsor'] ) { $positiontag_name = $positiontag.' ('.$json[$j]['sponsor'].')'; }
							else { $positiontag_name = $positiontag; }

						//exit($positiontag_name);

	   	 				$tags[] = array('name'    => $positiontag_name,
											'id'      => $positiontag_name, //include full value (includes sponsor)
											'sponsor' => $json[$j]['sponsor'] );
      				} //end foreach
					
				}
                   	
        	echo json_encode($tags );
        	CRM_Utils_System::civiExit( );
        	
      } //end leg pos condition
    }
    
    static function mergeTagList( ) {
        $name   = CRM_Utils_Type::escape( $_GET['s'],      'String' );
        $fromId = CRM_Utils_Type::escape( $_GET['fromId'], 'Integer' );
        $limit  = CRM_Utils_Type::escape( $_GET['limit'],  'Integer' );
        
        // build used-for clause to be used in main query
        $usedForTagA   = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_Tag', $fromId, 'used_for' );
        $usedForClause = array();
        if ( $usedForTagA ) {
            $usedForTagA = explode( ",", $usedForTagA );
            foreach( $usedForTagA as $key => $value ) {
                $usedForClause[] = "t1.used_for LIKE '%{$value}%'";
            }
        }
        $usedForClause  = !empty( $usedForClause ) ? implode( " OR " , $usedForClause ) : '1';
        sort($usedForTagA);

        // query to list mergable tags
        $query  = "
SELECT t1.name, t1.id, t1.used_for, t2.name as parent
FROM   civicrm_tag t1 
LEFT JOIN civicrm_tag t2 ON t1.parent_id = t2.id
WHERE  t1.id <> {$fromId} AND 
       t1.name LIKE '%{$name}%' AND
       ({$usedForClause}) 
LIMIT $limit";
        $dao    = CRM_Core_DAO::executeQuery( $query );
        
        while( $dao->fetch( ) ) {
            $warning = 0;
            if ( !empty($dao->used_for) ) {
                $usedForTagB = explode( ',', $dao->used_for );
                sort($usedForTagB);
                $usedForDiff   = array_diff( $usedForTagA, $usedForTagB );
                if ( !empty($usedForDiff) ) {
                    $warning = 1;
                }
            }
            $tag = addcslashes($dao->name, '"') . "|{$dao->id}|{$warning}\n";
            echo $tag = $dao->parent ? ( addcslashes($dao->parent, '"') . ' :: ' . $tag ) : $tag;
        }
        CRM_Utils_System::civiExit( );
    }

    static function processTags( ) {
        $skipTagCreate = $skipEntityAction = $entityId = null;
        $action           = CRM_Utils_Type::escape( $_POST['action'], 'String' );
        $parentId         = CRM_Utils_Type::escape( $_POST['parentId'], 'Integer' );
        if ( $_POST['entityId'] ) {
            $entityId     = CRM_Utils_Type::escape( $_POST['entityId'], 'Integer' );
        }
        
        $entityTable       = CRM_Utils_Type::escape( $_POST['entityTable'], 'String' );

        if ( $_POST['skipTagCreate'] ) {
            $skipTagCreate = CRM_Utils_Type::escape( $_POST['skipTagCreate'], 'Integer' );
        }
        
        if ( $_POST['skipEntityAction'] ) {
            $skipEntityAction = CRM_Utils_Type::escape( $_POST['skipEntityAction'], 'Integer' );
        }
        
		//NYSS
		if ( $parentId == 292 ) {
        	$tagID = $_POST['tagID'] ;
        
        	$createNewTag = false;
			$query2 = "SELECT id, name FROM civicrm_tag WHERE parent_id = {$parentId} and name = '{$tagID}'";
			$dao2 = CRM_Core_DAO::executeQuery( $query2 );

			//if tag exists use; else plan to create new
			if (!$dao2->fetch( ) ) {
        	    $createNewTag = true;
        	} else {
        	    $tagID = $dao2->id;
        	}
		} else {
        // check if user has selected existing tag or is creating new tag
        // this is done to allow numeric tags etc. 
        $tagValue = explode( ':::', $_POST['tagID'] );
        
        $createNewTag = false;
        $tagID  = $tagValue[0];
        if ( isset( $tagValue[1] ) && $tagValue[1] == 'value' ) {
            $createNewTag = true;
        }       
		}
		
		//NYSS - retrieve OpenLeg ID and construct URL
		$bill_url = '';
		$sponsor = CRM_Utils_Type::escape( $_POST['sponsor'], 'String' );
		if ( $parentId == 292 ) {
			$ol_id = substr( $tagID, 0, strpos( $tagID, ' ' ) );
			if ( !$ol_id ) { $ol_id = $tagID; } //account for bill with no position appended
			$ol_url = 'http://open.nysenate.gov/legislation/bill/'.$ol_id;
			$sponsor = ( $sponsor ) ? ' ('.$sponsor.')' : '';
			$bill_url = '<a href="'.$ol_url.'" target=_blank>'.$ol_url.'</a>'.$sponsor;
		}
		        
        require_once 'CRM/Core/BAO/EntityTag.php';
        $tagInfo = array( );
        // if action is select
        if ( $action == 'select' ) {
            // check the value of tagID
            // if numeric that means existing tag
            // else create new tag
            if ( !$skipTagCreate && $createNewTag ) {
                //NYSS 3667 strip spaces for new tags
				require_once 'CRM/Utils/String.php';
				$tagID = CRM_Utils_String::stripSpaces($tagID);
				
				$params = array( 'name'      => $tagID, 
                                 'parent_id' => $parentId,
								 'description' => $bill_url ); //LCD

                require_once 'CRM/Core/BAO/Tag.php';
                $tagObject = CRM_Core_BAO_Tag::add( $params, CRM_Core_DAO::$_nullArray );
                
                $tagInfo = array( 'name'   => $tagID,
                                  'id'     => $tagObject->id,
                                  'action' => $action );
                $tagID = $tagObject->id;                         
            }
            
            if ( !$skipEntityAction && $entityId ) {
                // save this tag to contact
                $params = array( 'entity_table' => $entityTable,
                                 'entity_id'    => $entityId,
                                 'tag_id'       => $tagID);
                             
                CRM_Core_BAO_EntityTag::add( $params );
            }
        } elseif ( $action == 'delete' ) {  // if action is delete
            if ( !is_numeric( $tagID ) ) {
                $tagID = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_Tag', $tagID, 'id',  'name' );
            }
            if ( $entityId ) {
                // delete this tag entry for the entity
                $params = array( 'entity_table' => $entityTable,
                                 'entity_id'    => $entityId,
                                 'tag_id'       => $tagID);
                             
                CRM_Core_BAO_EntityTag::del( $params );
            }
            $tagInfo = array( 'id'     => $tagID,
                              'action' => $action );
        }
        
        echo json_encode( $tagInfo );
        CRM_Utils_System::civiExit( );
    } 

    function mappingList(  ) {
        $params = array( 'mappingID' );
        foreach ( $params as $param ) {
            $$param = CRM_Utils_Array::value( $param, $_POST );
        }

        if ( !$mappingID ) {
            echo json_encode( array('error_msg' => 'required params missing.' ) );
            CRM_Utils_System::civiExit( );
        }

        require_once "CRM/Core/BAO/ScheduleReminders.php";
        list( $sel1, $sel2 ) = CRM_Core_BAO_ScheduleReminders::getSelection1( $mappingID );

        $elements = array( );
        foreach ( $sel1 as $id => $name ) {
            $elements[] = array( 'name'  => $name,
                                 'value' => $id );
        }

        require_once "CRM/Utils/JSON.php";
        echo json_encode( $elements );
        CRM_Utils_System::civiExit( );
    } 

    function mappingList1(  ) {
        $params = array( 'mappingID' );
        foreach ( $params as $param ) {
            $$param = CRM_Utils_Array::value( $param, $_POST );
        }

        if ( !$mappingID ) {
            echo json_encode( array('error_msg' => 'required params missing.' ) );
            CRM_Utils_System::civiExit( );
        }

        require_once "CRM/Core/BAO/ScheduleReminders.php";
        list( $sel1, $sel2 ) =  CRM_Core_BAO_ScheduleReminders::getSelection1( $mappingID );

        $elements = array( );
        foreach ( $sel2 as $id => $name ) {
            $elements[] = array( 'name'  => $name,
                                 'value' => $id );
        }

        require_once "CRM/Utils/JSON.php";
        echo json_encode( $elements );
        CRM_Utils_System::civiExit( );
    } 
   
    static function mergeTags( ) {
        $tagAId = CRM_Utils_Type::escape( $_POST['fromId'], 'Integer' );
        $tagBId   = CRM_Utils_Type::escape( $_POST['toId'],   'Integer' );
        
        require_once 'CRM/Core/BAO/EntityTag.php';
        $result = CRM_Core_BAO_EntityTag::mergeTags( $tagAId, $tagBId );

        if ( !empty( $result['tagB_used_for'] ) ) {
            require_once 'CRM/Core/OptionGroup.php';
            $usedFor = CRM_Core_OptionGroup::values('tag_used_for');
            foreach ( $result['tagB_used_for'] as &$val ) {
                $val = $usedFor[$val];
            }
            $result['tagB_used_for'] = implode( ', ', $result['tagB_used_for'] );
        }

        echo json_encode( $result );
        CRM_Utils_System::civiExit( );
    } 

}
