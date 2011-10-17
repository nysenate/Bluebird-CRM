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

require_once 'CRM/Core/SelectValues.php';
require_once 'CRM/Core/Form.php';

/**
 * This class generates form components for relationship
 * 
 */
class CRM_Contact_Form_Task extends CRM_Core_Form
{
    /**
     * the task being performed
     *
     * @var int
     */
    protected $_task;

    /**
     * The array that holds all the contact ids
     *
     * @var array
     */
    public $_contactIds;

    /**
     * The array that holds all the contact types
     *
     * @var array
     */
    public $_contactTypes;

    /**
     * The additional clause that we restrict the search with
     *
     * @var string
     */
    protected $_componentClause = null;

    /**
     * The name of the temp table where we store the contact IDs
     *
     * @var string
     */
    protected $_componentTable = null;

    /**
     * The array that holds all the component ids
     *
     * @var array
     */
    protected $_componentIds;

    /**
     * build all the data structures needed to build the form
     *
     * @param
     * @return void
     * @access public
     */
    function preProcess( ) 
    {
        self::preProcessCommon( $this );
    }

    static function preProcessCommon( &$form, $useTable = false )
    {
        $form->_contactIds   = array( );
        $form->_contactTypes = array( );

        // get the submitted values of the search form
        // we'll need to get fv from either search or adv search in the future
        $fragment = 'search';
        if ( $form->_action == CRM_Core_Action::ADVANCED ) {
            $values = $form->controller->exportValues( 'Advanced' );
            $fragment .= '/advanced';
        } else if ( $form->_action == CRM_Core_Action::PROFILE ) {
            $values = $form->controller->exportValues( 'Builder' );
            $fragment .= '/builder';
        } else if ( $form->_action == CRM_Core_Action::COPY ) {
            $values = $form->controller->exportValues( 'Custom' );
            $fragment .= '/custom';
        } else {
            $values = $form->controller->exportValues( 'Basic' );
        }
        
        //set the user context for redirection of task actions
        $qfKey = CRM_Utils_Request::retrieve( 'qfKey', 'String', $form );
        require_once 'CRM/Utils/Rule.php';
        $urlParams = 'force=1';
        if ( CRM_Utils_Rule::qfKey( $qfKey ) ) {
            $urlParams .= "&qfKey=$qfKey";
        }
        
        $url = CRM_Utils_System::url( 'civicrm/contact/' . $fragment, $urlParams );
        $session = CRM_Core_Session::singleton( );
        $session->replaceUserContext( $url );
        
        require_once 'CRM/Contact/Task.php';
        $form->_task         = CRM_Utils_Array::value( 'task', $values ) ;
        $crmContactTaskTasks = CRM_Contact_Task::taskTitles();
        $form->assign( 'taskName', CRM_Utils_Array::value( $form->_task, $crmContactTaskTasks ) );
       
        if ( $useTable ) {
            $form->_componentTable = CRM_Core_DAO::createTempTableName( 'civicrm_task_action', true, $qfKey );
            $sql = " DROP TABLE IF EXISTS {$form->_componentTable}";
            CRM_Core_DAO::executeQuery( $sql );

            $sql = "CREATE TABLE {$form->_componentTable} ( contact_id int primary key) ENGINE=MyISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci";
            CRM_Core_DAO::executeQuery( $sql );
        }            

        // all contacts or action = save a search
        if ( ( CRM_Utils_Array::value('radio_ts', $values ) == 'ts_all' ) ||
             ( $form->_task == CRM_Contact_Task::SAVE_SEARCH ) ) {
            // need to perform action on all contacts
            // fire the query again and get the contact id's + display name
            $sortID = null;
            if ( $form->get( CRM_Utils_Sort::SORT_ID  ) ) {
                $sortID = CRM_Utils_Sort::sortIDValue( $form->get( CRM_Utils_Sort::SORT_ID  ),
                                                       $form->get( CRM_Utils_Sort::SORT_DIRECTION ) );
            }

            $selectorName = $form->controller->selectorName( );
            require_once( str_replace('_', DIRECTORY_SEPARATOR, $selectorName ) . '.php' );

            $fv          = $form->get( 'formValues' );
            $customClass = $form->get( 'customSearchClass' );
            require_once 'CRM/Core/BAO/Mapping.php';
            $returnProperties = CRM_Core_BAO_Mapping::returnProperties( $values);

            eval( '$selector   = new ' .
                  $selectorName . 
                  '( $customClass, $fv, null, $returnProperties ); '
                  );

            $params    =  $form->get( 'queryParams' );

            // fix for CRM-5165
            $sortByCharacter = $form->get( 'sortByCharacter' );
            if ( $sortByCharacter &&
                 $sortByCharacter != 1 ) {
                $params[] = array( 'sortByCharacter', '=', $sortByCharacter, 0, 0 );
            }
            $queryOperator = $form->get( 'queryOperator' );
            if ( ! $queryOperator ) {
                $queryOperator = 'AND';
            }
            $dao =& $selector->contactIDQuery( $params, $form->_action, $sortID,
                                               CRM_Utils_Array::value( 'display_relationship_type',
                                                                       $fv ),
                                               $queryOperator );


            $form->_contactIds = array( );
            if ( $useTable ) {
                $count = 0;
                $insertString = array( );
                while ( $dao->fetch( ) ) {
                    $count++;
                    $insertString[] = " ( {$dao->contact_id} ) ";
                    if ( $count % 200 == 0 ) {
                        $string = implode( ',', $insertString );
                        $sql = "REPLACE INTO {$form->_componentTable} ( contact_id ) VALUES $string";
                        CRM_Core_DAO::executeQuery( $sql );
                        $insertString = array( );
                    }
                }
                if ( ! empty( $insertString ) ) {
                    $string = implode( ',', $insertString );
                    $sql = "REPLACE INTO {$form->_componentTable} ( contact_id ) VALUES $string";
                    CRM_Core_DAO::executeQuery( $sql );
                }
                $dao->free( );
            } else {
                // filter duplicates here
                // CRM-7058
                // might be better to do this in the query, but that logic is a bit complex
                // and it decides when to use distinct based on input criteria, which needs
                // to be fixed and optimized.
                $alreadySeen = array( );
                while ( $dao->fetch( ) ) {
                    if ( ! array_key_exists( $dao->contact_id, $alreadySeen ) ) {
                        $form->_contactIds[] = $dao->contact_id;
                        $alreadySeen[$dao->contact_id] = 1;
                    }
                }
                unset( $alreadySeen );
                $dao->free( );
            }
        } else if ( CRM_Utils_Array::value( 'radio_ts' , $values ) == 'ts_sel') {
            // selected contacts only
            // need to perform action on only selected contacts
            $insertString = array( );
            foreach ( $values as $name => $value ) {
                if ( substr( $name, 0, CRM_Core_Form::CB_PREFIX_LEN ) == CRM_Core_Form::CB_PREFIX ) {
                    $contactID = substr( $name, CRM_Core_Form::CB_PREFIX_LEN );
                    if ( $useTable ) {
                        $insertString[] = " ( {$contactID} ) ";
                    } else {
                        $form->_contactIds[] = substr( $name, CRM_Core_Form::CB_PREFIX_LEN );
                    }
                }
            }
            if ( ! empty( $insertString ) ) {
                $string = implode( ',', $insertString );
                $sql = "REPLACE INTO {$form->_componentTable} ( contact_id ) VALUES $string";
                CRM_Core_DAO::executeQuery( $sql );
            }
        }
        
        //contact type for pick up profiles as per selected contact types with subtypes
        //CRM-5521
        if ( $selectedTypes = CRM_Utils_Array::value( 'contact_type' , $values ) ) {
            if( !is_array( $selectedTypes ) ) {
                $selectedTypes  = explode( ' ', $selectedTypes );
            }
            foreach( $selectedTypes as $ct => $dontcare ) {
                if ( strpos($ct, CRM_Core_DAO::VALUE_SEPARATOR) === false ) {
                    $form->_contactTypes[] = $ct;  
                } else {
                    $separator = strpos($ct, CRM_Core_DAO::VALUE_SEPARATOR);
                    $form->_contactTypes[] = substr($ct, $separator+1);
                }
            }  
        }
        
        if ( ! empty( $form->_contactIds ) ) {
            $form->_componentClause =
                ' contact_a.id IN ( ' .
                implode( ',', $form->_contactIds ) . ' ) ';
            $form->assign( 'totalSelectedContacts', count( $form->_contactIds ) );             
            
            $form->_componentIds = $form->_contactIds;
        }
    }

    /**
     * This function sets the default values for the form. Relationship that in edit/view action
     * the default values are retrieved from the database
     * 
     * @access public
     * @return void
     */
    function setDefaultValues( ) 
    {
        $defaults = array( );
        return $defaults;
    }
    
    /**
     * This function is used to add the rules for form.
     *
     * @return void
     * @access public
     */
    function addRules( )
    {
    }

    /**
     * Function to actually build the form
     *
     * @return void
     * @access public
     */
    public function buildQuickForm( ) 
    {
        $this->addDefaultButtons(ts('Confirm Action'));        
    }

       
    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return void
     */
    public function postProcess() 
    {
    }//end of function

    /**
     * simple shell that derived classes can call to add buttons to
     * the form with a customized title for the main Submit
     *
     * @param string $title title of the main button
     * @param string $type  button type for the form after processing
     * @return void
     * @access public
     */
    function addDefaultButtons( $title, $nextType = 'next', $backType = 'back' ) {
        $this->addButtons( array(
                                 array ( 'type'      => $nextType,
                                         'name'      => $title,
                                         'isDefault' => true   ),
                                 array ( 'type'      => $backType,
                                         'name'      => ts('Cancel') ),
                                 )
                           );
    }

}


