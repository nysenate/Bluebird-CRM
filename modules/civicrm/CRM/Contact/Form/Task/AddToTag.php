<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
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

require_once 'CRM/Contact/Form/Task.php';
require_once 'CRM/Core/BAO/EntityTag.php';

/**
 * This class provides the functionality to delete a group of
 * contacts. This class provides functionality for the actual
 * addition of contacts to groups.
 */
class CRM_Contact_Form_Task_AddToTag extends CRM_Contact_Form_Task {

    /**
     * name of the tag
     *
     * @var string
     */
    protected $_name;

    /**
     * all the tags in the system
     *
     * @var array
     */
    protected $_tags;

    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    function buildQuickForm( ) {
        // add select for tag
        $this->_tags = CRM_Core_BAO_Tag::getTags( );
        
        foreach ($this->_tags as $tagID => $tagName) {
            $this->_tagElement =& $this->addElement('checkbox', "tag[$tagID]", null, $tagName);
        }
        
        require_once 'CRM/Core/Form/Tag.php';
        require_once 'CRM/Core/BAO/Tag.php';
        $parentNames = CRM_Core_BAO_Tag::getTagSet( 'civicrm_contact' );
        CRM_Core_Form_Tag::buildQuickForm( $this, $parentNames, 'civicrm_contact' );
        
        $this->addDefaultButtons( ts('Tag Contacts') );
    }

    function addRules( )
    {
        $this->addFormRule( array( 'CRM_Contact_Form_Task_AddToTag', 'formRule' ) );
    }
    
    static function formRule( $form, $rule) {
        $errors =array();
        if ( empty( $form['tag'] ) && empty( $form['taglist'] ) ) {
            $errors['_qf_default'] = "Please select atleast one tag.";
        }
        return $errors;
    }
    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return None
     */
    public function postProcess() {
        //get the submitted values in an array
        $params = $this->controller->exportValues( $this->_name );

        $contactTags = $tagList = array( );

        // check if contact tags exists
        if ( CRM_Utils_Array::value( 'tag', $params ) ) {
            $contactTags = $params['tag'];
        }
        
        // check if tags are selected from taglists
        if ( CRM_Utils_Array::value( 'taglist', $params ) ) {
            foreach( $params['taglist'] as $val ) {
                if ( $val ) {
                    if ( is_numeric( $val ) ) {
                        $tagList[ $val ] = 1;
                    } else {
                        list( $label, $tagID ) = explode( ',', $val );
                        $tagList[ $tagID ] = 1;
                    }
                }
            }
        }
        $tagSets = CRM_Core_BAO_Tag::getTagsUsedFor( 'civicrm_contact', false, true);
                
        foreach ( $tagSets as $key => $value ) {
            $this->_tags[$key] = $value['name'];
        }
        // merge contact and taglist tags
        $allTags = CRM_Utils_Array::crmArrayMerge( $contactTags, $tagList );        
        
        $this->_name = array();
        foreach( $allTags as $key => $dnc ) {
            $this->_name[]   = $this->_tags[$key];
            
            list( $total, $added, $notAdded ) = CRM_Core_BAO_EntityTag::addEntitiesToTag( $this->_contactIds, $key );
            
            $status = array(
                            'Contact(s) tagged as: '       . implode(',', $this->_name),
                            'Total Selected Contact(s): '  . $total
                            );
        }
        
        if ( $added ) {
            $status[] = 'Total Contact(s) tagged: ' . $added;
        }
        if ( $notAdded ) {
            $status[] = 'Total Contact(s) already tagged: ' . $notAdded;
        }
        
        CRM_Core_Session::setStatus( $status );
    }//end of function


}


