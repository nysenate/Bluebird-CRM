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

require_once 'CRM/Contribute/Form/ContributionPage.php';

class CRM_Contribute_Form_ContributionPage_Widget extends CRM_Contribute_Form_ContributionPage 
{
    protected $_colors;

    protected $_widget;

    function preProcess( ) 
    {
        parent::preProcess( );

        require_once 'CRM/Contribute/DAO/Widget.php';
        $this->_widget = new CRM_Contribute_DAO_Widget( );
        $this->_widget->contribution_page_id = $this->_id;
        if ( ! $this->_widget->find( true ) ) {
            $this->_widget = null;
        } else {
            $this->assign( 'widget_id', $this->_widget->id );
        }
        $this->assign( 'id', $this->_id );

        $config = CRM_Core_Config::singleton( );
        $title = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_ContributionPage',
                                              $this->_id,
                                              'title' );
        
        $this->_fields = array( 'title'               => array( ts( 'Title' ),
                                                                'text',
                                                                false,
                                                                $title ),
                                'url_logo'            => array( ts( 'URL to Logo Image' ),
                                                                'text',
                                                                false ,
                                                                null ),
                                'button_title'        => array( ts( 'Button Title' ),
                                                                'text',
                                                                false,
                                                                ts( 'Contribute!' ) ),
                               
                                'url_homepage'        => array( ts( 'URL to Home Page' ),
                                                                'text',
                                                                false,
                                                                $config->userFrameworkBaseURL ),
                                );
        
        $this->_colorFields = array( 'color_title'   => array( ts( 'Title Text Color' ),
                                                              'text',
                                                              false,
                                                              '0x000000' ),
                                     'color_button'  => array( ts( 'Button Color' ),
                                                              'text',
                                                              false,
                                                              '0xCC9900' ),
                                     'color_bar'     => array( ts( 'Progress Bar Color' ),
                                                              'text',
                                                              false,
                                                              '0xCC9900' ),
                                     'color_main_text' => array( ts( 'Additional Text Color' ),
                                                              'text',
                                                              false,
                                                              '0x000000' ),
                                     'color_main'     => array( ts( 'Inner Background Gradient from Bottom' ),
                                                              'text',
                                                              false,
                                                              '0x96E0E0' ),
                                     'color_main_bg'  => array( ts( 'Inner Background Top Area' ),
                                                              'text',
                                                              false,
                                                              '0xFFFFFF' ),
                                     'color_bg'       => array( ts( 'Border Color' ),
                                                              'text',
                                                              false,
                                                              '0x66CCCC' ),
                                     'color_about_link' => array( ts( 'About Link Color' ),
                                                              'text',
                                                              false,
                                                              '0x336699' ),
                                     'color_homepage_link' => array( ts( 'Homepage Link Color' ),
                                                              'text',
                                                              false,
                                                              '0x336699' ),
                               );
    }
    
    function setDefaultValues( ) 
    {
        $defaults = array( );
        // check if there is a widget already created
        if ( $this->_widget ) {
            CRM_Core_DAO::storeValues( $this->_widget, $defaults );
        } else {
            foreach ( $this->_fields as $name => $val ) {
                $defaults[$name] = $val[3];
            }
            foreach ( $this->_colorFields as $name => $val ) {
                $defaults[$name] = $val[3];
            }
            $defaults['about'] = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_ContributionPage', 
                                                              $this->_id, 
                                                              'intro_text' );
        } 
    
        require_once 'CRM/Core/ShowHideBlocks.php';
        $showHide = new CRM_Core_ShowHideBlocks( );
        $showHide->addHide( "id-colors" );
        $showHide->addToTemplate( );
        return $defaults;
    }

    function buildQuickForm( ) 
    {
        $attributes = CRM_Core_DAO::getAttribute( 'CRM_Contribute_DAO_Widget' );
        
        $this->addElement( 'checkbox',
                           'is_active',
                           ts( 'Enable Widget?' ),
                           null,
                           array( 'onclick' => "widgetBlock(this)" ) );
        
        $this->addWysiwyg( 'about', ts('About'), $attributes['about'] );
        
        foreach ( $this->_fields as $name => $val ) {
            $this->add( $val[1],
                        $name,
                        $val[0],
                        $attributes[$name],
                        $val[2] );
        }
        foreach ( $this->_colorFields as $name => $val ) {
            $this->add( $val[1],
                       $name,
                       $val[0],
                       $attributes[$name],
                       $val[2] );
        }
        
        $this->assign_by_ref( 'fields', $this->_fields );
        $this->assign_by_ref( 'colorFields', $this->_colorFields );

        $this->_refreshButtonName = $this->getButtonName( 'refresh' );
        $this->addElement('submit',
                          $this->_refreshButtonName,
                          ts('Save and Preview') );
        parent::buildQuickForm( );
        $this->addFormRule(array('CRM_Contribute_Form_ContributionPage_Widget', 'formRule') , $this );
    }

    /**
     * Function for validation
     *
     * @param array $params (ref.) an assoc array of name/value pairs
     *
     * @return mixed true or array of errors
     * @access public
     * @static
     */
    public static function formRule( $params, $files, $self ) 
    { 
        $errors = array( );
        if ( CRM_Utils_Array::value( 'is_active', $params ) ) {
            if ( !CRM_Utils_Array::value( 'title', $params ) ) {
                $errors['title'] = ts( 'Title is a required field.' );
            }
            if ( !CRM_Utils_Array::value( 'about', $params ) ) {
                $errors['about'] = ts( 'About is a required field.' );
            }
            if ( !CRM_Utils_Array::value( 'url_homepage', $params ) ) {
                $errors['url_homepage'] = ts( 'URL to Home Page is a required field.' );
            }
            foreach( $params as $key => $val ) {
                if ( substr( $key, 0, 6 ) == 'color_' && !CRM_Utils_Array::value( $key, $params ) ) {
                    $errors[$key] = ts( '%1 is a required field.',  array( 1 => $self->_colorFields[$key][0] ) );
                }
            }
        }
        return empty($errors) ? true : $errors;
    }

    function postProcess( ) 
    {
        //to reset quickform elements of next (pcp) page.
        if ( $this->controller->getNextName('Widget') == 'PCP' ) {
            $this->controller->resetPage( 'PCP' );
        }
     
        // get the submitted form values.
        $params = $this->controller->exportValues( $this->_name );
        
        if ( $this->_widget ) {
            $params['id'] = $this->_widget->id;
        }
        $params['contribution_page_id'] = $this->_id;
        $params['is_active']            = CRM_Utils_Array::value('is_active', $params, false);

        require_once 'CRM/Contribute/DAO/Widget.php';
        $widget = new CRM_Contribute_DAO_Widget( );
        $widget->copyValues( $params );
        $widget->save( );

        $buttonName = $this->controller->getButtonName( );
        if ( $buttonName = $this->_refreshButtonName ) {
            return;
        }
    }

    /** 
     * Return a descriptive name for the page, used in wizard header 
     * 
     * @return string 
     * @access public 
     */ 
    public function getTitle( ) 
    {
        return ts( 'Widget Settings' );
    }

}


