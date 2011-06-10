<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                               |
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

/*
 * Using this script you can update Email Greetings, Postal Greetings,
 * and Addressee for a specific contact type
 *
 * params for this script
 * ct=Individual or ct=Household or ct=Organization (ct = contact type)
 * gt=email_greeting or gt=postal_greeting or gt=addressee (gt = greeting )
 * id=greeting option value 
 *
 * IMPORTANT: You must first create valid option value before using via
 * admin interface.
 * Check option lists for Email Greetings, Postal Greetings and Addressee 
 */

    
require_once 'script_utils.php';


function run()
{
    $prog = basename(__FILE__);
    $shortopts = 'c:g:i:f';
    $longopts = array('ct=', 'gt=', 'id=', 'force');
    $stdusage = civicrm_script_usage();
    $usage = "[--ct|-c {Individual|Household|Organization}]  [--gt|-g {email|postal|addressee}]  [--id|-i greeting_value]  [--force|-f]";
    $contactOpts = array(
      'i' => array(1 => 'Individual'),
      'h' => array(2 => 'Household'),
      'o' => array(3 => 'Organization')
    );
    $greetingOpts = array(
      'e' => 'email_greeting',
      'p' => 'postal_greeting',
      'a' => 'addressee'
    );

    $optlist = civicrm_script_init($shortopts, $longopts);
    if ($optlist === null) {
      error_log("Usage: $prog  $stdusage  $usage");
      exit(1);
    }

    if (empty($optlist['ct']) || empty($optlist['gt'])) {
      error_log("$prog: Must use both --ct and --gt to specify the contact type and greeting type.");
      exit(1);
    }

    if (!is_cli_script()) {
        echo "<pre>\n";
    }

    //log the execution of script
    require_once 'CRM/Core/Error.php';
    CRM_Core_Error::debug_log_message('updateGreetings.php');

    require_once 'CRM/Core/Config.php';
    $config = CRM_Core_Config::singleton();

    $contactOptIdx = strtolower($optlist['ct'][0]);
    if (isset($contactOpts[$contactOptIdx])) {
      list($contactTypeIdx, $contactType) = each($contactOpts[$contactOptIdx]);
    }
    else {
      //CRM_Core_Error::fatal( ts('Invalid Contact Type.') );
      echo ts("$prog: $contactOptIdx: Invalid Contact Type.\n");
      exit(1);
    }
    
    $greetingOptIdx = strtolower($optlist['gt'][0]);
    if (isset($greetingOpts[$greetingOptIdx])) {
      $greeting = $greetingOpts[$greetingOptIdx];
    }
    else {
      echo ts("$prog: $greetingOptIdx: Invalid Greeting Type.\n");
      exit(1);
    }

    // "Email" and "Postal" greetings are not cached for organizations.
    if (strpos('ep', $greetingOptIdx) !== false && $contactOptIdx == 'o') {
        echo ts("You cannot use $greeting for contact type $contactType.\n");
        exit(1);
    }

    $valueID = $id = $optlist['id'];

    // if valueID is not passed use default value 
    if ( !$valueID ) {
        require_once 'CRM/Core/OptionGroup.php';
        $defaultValueID = CRM_Core_OptionGroup::values(
            $greeting, null, null, null, 
            " AND is_default=1 AND ( filter={$contactTypeIdx} OR filter=0 )",
            "value");
        $valueID = array_pop( $defaultValueID );
    }

    $filter = array( 'contact_type'  => $contactType, 
                     'greeting_type' => $greeting );

    require_once 'CRM/Core/PseudoConstant.php';
    $allGreetings = CRM_Core_PseudoConstant::greeting( $filter );            
    $originalGreetingString = $greetingString = CRM_Utils_Array::value( $valueID, $allGreetings );
    if ( !$greetingString ) {
        echo ts('Incorrect greeting value id %1.', array( 1 => $valueID));
        exit(1);
    }
    
    // build return properties based on tokens
    require_once 'CRM/Activity/BAO/Activity.php';
    $greetingTokens = CRM_Activity_BAO_Activity::getTokens( $greetingString );
    $tokens = CRM_Utils_Array::value( 'contact', $greetingTokens );
    $greetingsReturnProperties = array( );
    if ( is_array( $tokens ) ) {
        $greetingsReturnProperties = array_fill_keys( array_values( $tokens ), 1 );
    }

    //process all contacts only when --force is specified.
    $processAll = $processOnlyIdSet = false;
    if ($optlist['force'] == true) {
        $processAll = true;
    } elseif ($optlist['force'] == 2 ) {
        $processOnlyIdSet = true;
    }
    
    //FIXME : apiQuery should handle these clause.
    $filterContactFldIds = $filterIds = array( );
    if ( !$processAll ) {
        $idFldName = $displayFldName = null;
        if ( $greeting == 'email_greeting' || $greeting == 'postal_greeting' ||  $greeting == 'addressee' ) {
            $idFldName = $greeting . '_id';
            $displayFldName = $greeting . '_display';
        }

        if ( $idFldName ) {
            $sql = "
SELECT DISTINCT id, $idFldName
  FROM civicrm_contact 
 WHERE contact_type = %1 
   AND ( {$idFldName} IS NULL OR 
     ( {$idFldName} IS NOT NULL AND {$displayFldName} IS NULL ) )
   ";
            $dao = CRM_Core_DAO::executeQuery( $sql, array( 1 => array( $contactType, 'String' ) ) );
            while ( $dao->fetch( ) ) {
                $filterContactFldIds[$dao->id] = $dao->$idFldName;

                if (!CRM_Utils_System::isNull( $dao->$idFldName)) {
                    $filterIds[$dao->id] = $dao->$idFldName;
                }
            }
          
        }
        if ( empty( $filterContactFldIds ) ) {
            $filterContactFldIds[] = 0;
        }
    }
    // retrieve only required contact information
    require_once 'CRM/Mailing/BAO/Mailing.php';
    $extraParams[] = array( 'contact_type', '=', $contactType, 0, 0 );
    list($greetingDetails) = CRM_Mailing_BAO_Mailing::getDetails( array_keys( $filterContactFldIds ),
                                                                  $greetingsReturnProperties, 
                                                                  false, false, $extraParams );
    // perform token replacement and build update SQL
    $contactIds = array( );
    $cacheFieldQuery = "UPDATE civicrm_contact SET {$greeting}_display = CASE id ";
    foreach ( $greetingDetails as $contactID => $contactDetails ) {
        if ( !$processAll && !array_key_exists( $contactID, $filterContactFldIds ) ) {
            continue;
        }
        if ( $processOnlyIdSet ) { 
            if ( !array_key_exists( $contactID, $filterIds ) ) {
                continue;
            }
            if ( $id ) {
                $greetingString = $originalGreetingString;
                $contactIds[] = $contactID;
            } else {
                if ( $greetingBuffer = CRM_Utils_Array::value($filterContactFldIds[$contactID], $allGreetings) ) {
                    $greetingString = $greetingBuffer;
                }  
            }
            $allContactIds[] = $contactID;
        } else {
            $greetingString = $originalGreetingString;	 
            if ( $greetingBuffer = CRM_Utils_Array::value($filterContactFldIds[$contactID], $allGreetings) ) {
                $greetingString = $greetingBuffer;
            } else {
                $contactIds[] = $contactID;  
            }
        }
        CRM_Activity_BAO_Activity::replaceGreetingTokens($greetingString, $contactDetails, $contactID );
        $greetingString = CRM_Core_DAO::escapeString( $greetingString );
        $cacheFieldQuery .= " WHEN {$contactID} THEN '{$greetingString}' ";
        
        $allContactIds[] = $contactID;
    }
    
    if ( !empty( $allContactIds ) ) {
        $cacheFieldQuery .= " ELSE {$greeting}_display
                          END;"; 
        if ( !empty( $contactIds ) ) {
            // need to update greeting _id field.
        $queryString = "
UPDATE civicrm_contact 
   SET {$greeting}_id = {$valueID} 
 WHERE id IN (" . implode( ',', $contactIds ) . ")";
        CRM_Core_DAO::executeQuery( $queryString );
        }
        
        // now update cache field
        CRM_Core_DAO::executeQuery( $cacheFieldQuery );
    }
}


run();
echo "\n\n Greeting is updated for contact(s). (Done) \n";
