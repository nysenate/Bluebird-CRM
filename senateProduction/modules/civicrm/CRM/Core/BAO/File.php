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

require_once 'CRM/Core/DAO/File.php';

/**
 * BAO object for crm_log table
 */

class CRM_Core_BAO_File extends CRM_Core_DAO_File {

    function path( $fileID,
                   $entityID,
                   $entityTable = null,
                   $quest = false  ) {
        require_once 'CRM/Core/DAO/EntityFile.php'; 
        
        $entityFileDAO = new CRM_Core_DAO_EntityFile();
        if ($entityTable) {
            $entityFileDAO->entity_table = $entityTable;
        }
        $entityFileDAO->entity_id    = $entityID;
        $entityFileDAO->file_id      = $fileID;
        
        if ( $entityFileDAO->find( true ) ) {
            require_once 'CRM/Core/DAO/File.php'; 
            $fileDAO = new CRM_Core_DAO_File( );
            $fileDAO->id = $fileID;
            if ( $fileDAO->find( true ) ) {
                $config = CRM_Core_Config::singleton( );
                if ( $quest ) {
                    if ($quest == '1') {
                        // to make quest part work as before
                        $path =
                            $config->customFileUploadDir .
                            'Student' . DIRECTORY_SEPARATOR .
                            $entityID . DIRECTORY_SEPARATOR .
                            $fileDAO->uri;
                    } else {
                        $path = 
                            $config->customFileUploadDir .
                            $quest . DIRECTORY_SEPARATOR .
                            $entityID . DIRECTORY_SEPARATOR .
                            $fileDAO->uri;
                    }
                } else {
                    $path = $config->customFileUploadDir . $fileDAO->uri;
                }

                if ( file_exists( $path ) && is_readable( $path ) ) {
                    return array( $path, $fileDAO->mime_type );
                }

            }
        }

        return array( null, null );
    }

    
    public function filePostProcess($data, $fileID, 
                                    $entityTable, $entityID,
                                    $entitySubtype, $overwrite = true,
                                    $fileParams = null,
                                    $uploadName = 'uploadFile',
                                    $mimeType    ) {

        require_once 'CRM/Core/DAO/File.php';
        $config = & CRM_Core_Config::singleton();
        
        $path = explode( '/', $data );
        $filename = $path[count($path) - 1];
        
        // rename this file to go into the secure directory
        if ( $entitySubtype ) {
            $directoryName = $config->customFileUploadDir . $entitySubtype .DIRECTORY_SEPARATOR . $entityID;
        } else {
            $directoryName = $config->customFileUploadDir;
        }

        require_once "CRM/Utils/File.php";
        CRM_Utils_File::createDir( $directoryName );

        if ( ! rename( $data, $directoryName . DIRECTORY_SEPARATOR . $filename ) ) {
            CRM_Core_Error::fatal( ts( 'Could not move custom file to custom upload directory' ) );
            break;
        }

        // to get id's 
        if ( $overwrite && $fileID ) {
            list( $sql, $params ) = self::sql( $entityTable, $entityID, $fileID );
        } else {
            list( $sql, $params ) = self::sql( $entityTable, $entityID, 0 );
        }

        $dao =& CRM_Core_DAO::executeQuery( $sql, $params );
        $dao->fetch();

        if ( ! $mimeType ) {
            CRM_Core_Error::fatal( );
        }
        
        require_once "CRM/Core/DAO/File.php";
        $fileDAO = new CRM_Core_DAO_File();
        if ( isset( $dao->cfID ) &&
             $dao->cfID ) {
            $fileDAO->id = $dao->cfID;
            unlink( $directoryName . DIRECTORY_SEPARATOR . $dao->uri );
        }

        if ( ! empty( $fileParams ) ) {
            $fileDAO->copyValues($fileParams);
        }

        $fileDAO->uri               = $filename;
        $fileDAO->mime_type         = $mimeType;
        $fileDAO->file_type_id      = $fileID;
        $fileDAO->upload_date       = date('Ymdhis'); 
        $fileDAO->save();
    
        // need to add/update civicrm_entity_file
        require_once "CRM/Core/DAO/EntityFile.php";
        $entityFileDAO = new CRM_Core_DAO_EntityFile();
        if ( isset( $dao->cefID ) &&
             $dao->cefID ) {
            $entityFileDAO->id =  $dao->cefID;
        }
        $entityFileDAO->entity_table = $entityTable;
        $entityFileDAO->entity_id    = $entityID;
        $entityFileDAO->file_id      = $fileDAO->id;
        $entityFileDAO->save();
        
    }

    public function delete($fileID , $entityID, $fieldID ) {
        // get the table and column name
        require_once 'CRM/Core/BAO/CustomField.php';
        list( $tableName, $columnName, $groupID ) = CRM_Core_BAO_CustomField::getTableColumnGroup( $fieldID );

        require_once "CRM/Core/DAO/EntityFile.php";
        $entityFileDAO = new CRM_Core_DAO_EntityFile();
        $entityFileDAO->file_id      = $fileID;
        $entityFileDAO->entity_id    = $entityID;
        $entityFileDAO->entity_table = $tableName;
        
        if ( $entityFileDAO->find(true) ) {
            $entityFileDAO->delete();
        } else {
            CRM_Core_Error::fatal( );
        }

        require_once "CRM/Core/DAO/File.php";
        $fileDAO = new CRM_Core_DAO_File();
        $fileDAO->id = $fileID;
        if ( $fileDAO->find(true) ) {
            $fileDAO->delete();
        } else {
            CRM_Core_Error::fatal( );
        }

        // also set the value to null of the table and column
        $query = "UPDATE $tableName SET $columnName = null WHERE $columnName = %1";
        $params = array( 1 => array( $fileID, 'Integer' ) );
        CRM_Core_DAO::executeQuery( $query, $params );
    }

    /**
     * delete all the files and associated object associated with this 
     * combination
     */
    public function deleteEntityFile( $entityTable, $entityID ) {
        if ( empty( $entityTable ) ||
             empty( $entityID ) ) {
            return;
        }

        $config = CRM_Core_Config::singleton( );

        list( $sql, $params ) = self::sql( $entityTable, $entityID, null );
        $dao    = CRM_Core_DAO::executeQuery( $sql, $params );
        $cfIDs  = array( );
        $cefIDs = array( );
        while ( $dao->fetch( ) ) {
            unlink( $config->customFileUploadDir . DIRECTORY_SEPARATOR . $dao->uri );
            $cfIDs[]  = $dao->cfID ;
            $cefIDs[] = $dao->cefID;
        }

        if ( ! empty( $cefIDs ) ) {
            $cefIDs = implode( ',', $cefIDs );
            $sql = "DELETE FROM civicrm_entity_file where id IN ( $cefIDs )";
            CRM_Core_DAO::executeQuery( $sql );
        }

        if ( ! empty( $cfIDs ) ) {
            $cfIDs = implode( ',', $cfIDs );
            $sql = "DELETE FROM civicrm_file where id IN ( $cfIDs )";
            CRM_Core_DAO::executeQuery( $sql );
        }
    }

    /**
     * get all the files and associated object associated with this 
     * combination
     */
    public function &getEntityFile( $entityTable, $entityID ) {
        require_once 'CRM/Utils/File.php';
        $config = CRM_Core_Config::singleton( );

        list( $sql, $params ) = self::sql( $entityTable, $entityID, null );
        $dao    = CRM_Core_DAO::executeQuery( $sql, $params );
        $results = array( );
        while ( $dao->fetch( ) ) {
            $result['fileID'   ] = $dao->cfID;
            $result['entityID' ] = $dao->cefID;
            $result['mime_type'] = $dao->mime_type;
            $result['fileName' ] = $dao->uri;
            $result['cleanName'] = CRM_Utils_File::cleanFileName( $dao->uri );
            $result['fullPath' ] = $config->customFileUploadDir . DIRECTORY_SEPARATOR . $dao->uri;
            $result['url'      ] = CRM_Utils_System::url( 'civicrm/file', "reset=1&id={$dao->cfID}&eid={$entityID}" );
            $result['href'     ] = "<a href=\"{$result['url']}\">{$result['cleanName']}</a>";
            $results[$dao->cfID] = $result;
        }
        return $results;
    }

    public function sql( $entityTable, $entityID, $fileID = null ) {
        $sql = "
SELECT    CF.id as cfID,
          CF.uri as uri,
          CF.mime_type as mime_type,
          CEF.id as cefID
FROM      civicrm_file AS CF
LEFT JOIN civicrm_entity_file AS CEF ON ( CEF.file_id = CF.id )
WHERE     CEF.entity_table = %1
AND       CEF.entity_id    = %2";
        $params = array( 1 => array( $entityTable, 'String'  ),
                         2 => array( $entityID   , 'Integer' ) );

        if ( $fileID !== null ) {
            $sql .= " AND CF.file_type_id = %3";
            $params[3] = array( $fileID, 'Integer' );
        }

        return array( $sql, $params );

    }
    
    static function buildAttachment( &$form, $entityTable, $entityID = null, $numAttachments = null ) {

        $config = CRM_Core_Config::singleton( );

        if( ! $numAttachments ) {
            $numAttachments = $config->maxAttachments;
        }

        // set default max file size as 2MB
        $maxFileSize = $config->maxFileSize ? $config->maxFileSize : 2;
        
        $form->assign( 'numAttachments', $numAttachments );
        // add attachments
        for ( $i = 1; $i <= $numAttachments; $i++ ) {
            $form->addElement( 'file', "attachFile_$i", ts('Attach File'), 'size=30 maxlength=60' );
            $form->setMaxFileSize( $maxFileSize * 1024 * 1024 );
            $form->addRule( "attachFile_$i",
                            ts( 'File size should be less than %1 MByte(s)',
                                array( 1 => $maxFileSize ) ),
                            'maxfilesize',
                            $maxFileSize * 1024 * 1024 );
        }

        $attachmentInfo = self::attachmentInfo(  $entityTable, $entityID );
        if ( $attachmentInfo ) {
            $form->add( 'checkbox', 'is_delete_attachment', ts( 'Delete Current Attachment(s)' ) );
            $form->assign( 'currentAttachmentURL',
                           $attachmentInfo );
        } else {
            $form->assign( 'currentAttachmentURL', null );
        }
    }

    static function attachmentInfo( $entityTable, $entityID, $separator = '<br />' ) {
        if ( ! $entityID ) {
            return null;
        }

        $currentAttachments = self::getEntityFile( $entityTable,
                                                   $entityID );
        if ( ! empty( $currentAttachments ) ) {
            $currentAttachmentURL = array( );
            foreach ( $currentAttachments as $fileID => $attach ) {
                $currentAttachmentURL[] = $attach['href'];
            }
            return implode( $separator, $currentAttachmentURL );
        }
        return null;
    }

    static function formatAttachment( &$formValues,
                                      &$params,
                                      $entityTable,
                                      $entityID = null ) {

        // delete current attachments if applicable
        if ( $entityID &&
             CRM_Utils_Array::value( 'is_delete_attachment', $formValues ) ) {
            CRM_Core_BAO_File::deleteEntityFile( $entityTable,
                                                 $entityID );
        }

        $config = CRM_Core_Config::singleton( );
        $numAttachments = $config->maxAttachments;

        // setup all attachments
        for ( $i = 1; $i <= $numAttachments; $i++ ) {
            $attachName = "attachFile_$i";
            if ( isset( $formValues[$attachName] ) &&
                 ! empty( $formValues[$attachName] ) ) {
                // ensure file is not empty
                $contents = file_get_contents( $formValues[$attachName]['name'] );
                if ( $contents ) {
                    $fileParams = array( 'uri'        => $formValues[$attachName]['name'],
                                         'type'       => $formValues[$attachName]['type'],
                                         'upload_date'=> date( 'Ymdhis' ),
                                         'location'   => $formValues[$attachName]['name'] );
                    $params[$attachName] = $fileParams;
                }
            }
        }
    }

    static function processAttachment( &$params,
                                       $entityTable,
                                       $entityID ) {
        $config = CRM_Core_Config::singleton( );
        $numAttachments = $config->maxAttachments;

        for ( $i = 1; $i <= $numAttachments; $i++ ) {
            if ( isset( $params["attachFile_$i"] ) &&
                 is_array( $params["attachFile_$i"] ) ) {
                self::filePostProcess($params["attachFile_$i"]['location'],
                                      null, 
                                      $entityTable,
                                      $entityID,
                                      null,
                                      true,
                                      $params["attachFile_$i"],
                                      "attachFile_$i",
                                      $params["attachFile_$i"]['type'] );
            }
        }
    }

    static function uploadNames( ) {
        $config = CRM_Core_Config::singleton( );
        $numAttachments = $config->maxAttachments;

        $names = array( );
        for ( $i = 1; $i <= $numAttachments; $i++ ) {
            $names[] = "attachFile_{$i}";
        }
        $names[] = 'uploadFile';
        return $names;
    }

    /*
     * Function to copy/attach an existing file to a different entity
     * table and id.
     */
    static function copyEntityFile( $oldEntityTable, $oldEntityId, $newEntityTable, $newEntityId ) {
        require_once "CRM/Core/DAO/EntityFile.php";
        $oldEntityFile = new CRM_Core_DAO_EntityFile();
        $oldEntityFile->entity_id    = $oldEntityId;
        $oldEntityFile->entity_table = $oldEntityTable;
        $oldEntityFile->find( );

        while ( $oldEntityFile->fetch( ) ) {
            $newEntityFile = new CRM_Core_DAO_EntityFile();
            $newEntityFile->entity_id    = $newEntityId;
            $newEntityFile->entity_table = $newEntityTable;
            $newEntityFile->file_id      = $oldEntityFile->file_id;
            $newEntityFile->save( );
        }
    }
}

