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
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'CRM/Core/DAO/Note.php';

/**
 * BAO object for crm_note table
 */
class CRM_Core_BAO_Note extends CRM_Core_DAO_Note 
{

    /**
     * const the max number of notes we display at any given time
     * @var int
     */
    const MAX_NOTES = 3;
    
    /**
     * given a note id, retrieve the note text
     * 
     * @param int  $id   id of the note to retrieve
     * 
     * @return string   the note text or null if note not found
     * 
     * @access public
     * @static
     */
    static function getNoteText( $id ) 
    {
        return CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_Note', $id, 'note' );
    }
    
    /**
     * given a note id, retrieve the note subject
     * 
     * @param int  $id   id of the note to retrieve
     * 
     * @return string   the note subject or null if note not found
     * 
     * @access public
     * @static
     */
    static function getNoteSubject( $id ) 
    {
        return CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_Note', $id, 'subject' );
    }

    /**
     * takes an associative array and creates a note object
     *
     * the function extract all the params it needs to initialize the create a
     * note object. the params array could contain additional unused name/value
     * pairs
     *
     * @param array  $params         (reference ) an assoc array of name/value pairs
     *
     * @return object CRM_Core_BAO_Note object
     * @access public
     * @static
     */
    static function &add( &$params , $ids ) 
    {
        $dataExists = self::dataExists( $params );
        if ( ! $dataExists ) {
            return CRM_Core_DAO::$_nullObject;
        }

        $note = new CRM_Core_BAO_Note( );
        
        $params['modified_date'] = date("Ymd");
        
        $note->copyValues( $params );
        if ( ! $params['contact_id'] ) {
            if ( $params['entity_table'] =='civicrm_contact' ) {
                $note->contact_id = $params['entity_id'];   
            } else {
                CRM_Core_Error::statusBounce(ts('We could not find your logged in user ID'));
            }
        }
        
        if ( CRM_Utils_Array::value( 'id', $ids ) ) {
            $note->id = CRM_Utils_Array::value( 'id', $ids );
        }
        
        $note->save( );

        if ( $note->entity_table == 'civicrm_contact' ) {
            require_once 'CRM/Core/BAO/Log.php';
            CRM_Core_BAO_Log::register( $note->entity_id,
                                        'civicrm_note',
                                        $note->id );
            require_once 'CRM/Contact/BAO/Contact.php';
            $displayName = CRM_Contact_BAO_Contact::displayName( $note->entity_id );

            // add the recently created Note
            require_once 'CRM/Utils/Recent.php';
            CRM_Utils_Recent::add( $displayName . ' - ' . $note->subject,
                                   CRM_Utils_System::url( 'civicrm/contact/view/note', 
                                                          "reset=1&action=view&cid={$note->entity_id}&id={$note->id}&context=home" ),
                                   $note->id,
                                   'Note',
                                   $note->entity_id,
                                   $displayName );
        }

        return $note;
    }

    /**
     * Check if there is data to create the object
     *
     * @param array  $params         (reference ) an assoc array of name/value pairs
     *
     * @return boolean
     * @access public
     * @static
     */
    static function dataExists( &$params ) 
    {
        // return if no data present
        if ( ! strlen( $params['note']) ) {
            return false;
        } 
        return true;
     }

    /**
     * Given the list of params in the params array, fetch the object
     * and store the values in the values array
     *
     * @param array $params        input parameters to find object
     * @param array $values        output values of the object
     * @param array $ids           the array that holds all the db ids
     * @param int   $numNotes      the maximum number of notes to return (0 if all)
     *
     * @return object   Object of CRM_Core_BAO_Note
     * @access public
     * @static
     */
    static function &getValues( &$params, &$values, $numNotes = self::MAX_NOTES ) 
    {
        if ( empty( $params ) ) {
            return null;
        }
        $note = new CRM_Core_BAO_Note( );
        $note->entity_id    = $params['contact_id'] ;        
        $note->entity_table = 'civicrm_contact';

        // get the total count of notes
        $values['noteTotalCount'] = $note->count( );

        // get only 3 recent notes
        $note->orderBy( 'modified_date desc' );
        $note->limit( $numNotes );
        $note->find();

        $notes = array( );
        $count = 0;
        while ( $note->fetch() ) {
            $values['note'][$note->id] = array();
            CRM_Core_DAO::storeValues( $note, $values['note'][$note->id] );
            $notes[] = $note;

            $count++;
            // if we have collected the number of notes, exit loop
            if ( $numNotes > 0 && $count >= $numNotes ) {
                break;
            }
        }
        
        return $notes;
    }

    /**
     * Function to delete the notes
     * 
     * @param int $id    note id
     * 
     * @return $return   no of deleted notes on success, false otherwise
     * @access public
     * @static
     * 
     */
    static function del( $id ) 
    {
        $return   = null;
        $note     = new CRM_Core_DAO_Note( );
        $note->id = $id;
        $return   = $note->delete();
        CRM_Core_Session::setStatus( ts('Selected Note has been Deleted Successfully.') );
        
        // delete the recently created Note
        require_once 'CRM/Utils/Recent.php';
        $noteRecent = array(
                        'id'   => $id,
                        'type' => 'Note'
                        );
        CRM_Utils_Recent::del( $noteRecent );

        return $return;
    }

    /**
     * delete all records for this contact id
     * 
     * @param int  $id    ID of the contact for which records needs to be deleted.
     * 
     * @return void
     * 
     * @access public
     * @static
     */
    public static function deleteContact( $id )
    {
        // need to delete for both entity_id
        $dao = new CRM_Core_DAO_Note();
        $dao->entity_table = 'civicrm_contact';
        $dao->entity_id   = $id;
        $dao->delete();

        // and the creator contact id
        $dao = new CRM_Core_DAO_Note();
        $dao->contact_id = $id;        
        $dao->delete();
    }

    /**
     * retrieve all records for this entity-id
     * 
     * @param int  $id ID of the relationship for which records needs to be retrieved.
     * 
     * @return array    Array of note properties
     * 
     * @access public
     * @static
     */
    public static function &getNote( $id, $entityTable = 'civicrm_relationship' )
    {
        $viewNote = array();
        
        $query = "
SELECT   id, note FROM civicrm_note
WHERE    entity_table=\"{$entityTable}\"
  AND    entity_id = %1
  AND    note is not null
ORDER BY modified_date desc";
        $params = array( 1 => array( $id, 'Integer' ) );

        $dao =& CRM_Core_DAO::executeQuery( $query, $params );

        while ( $dao->fetch() ) {
            $viewNote[$dao->id] = $dao->note;
        }
        return $viewNote;
    }
    
    /**
     * Function to get log record count for a Contact
     *
     * @param int $contactId Contact ID
     * 
     * @return int count of log records
     * @access public
     * @static
     */
     static function getContactNoteCount( $contactID ) {
         $query = "SELECT count(*) FROM civicrm_note 
                   WHERE civicrm_note.entity_table = 'civicrm_contact' AND civicrm_note.entity_id = {$contactID}";
         return CRM_Core_DAO::singleValueQuery( $query );
     }
}