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
require_once 'CRM/Core/BAO/Navigation.php';
require_once 'CRM/Core/Permission.php';

class CRM_Report_Form_Instance {

    static function buildForm( &$form ) {
        $attributes = CRM_Core_DAO::getAttribute( 'CRM_Report_DAO_Instance' );

        $form->add( 'text',
                    'title',
                    ts( 'Report Title' ),
                    $attributes['title'] );

        $form->add( 'text',
                    'description',
                    ts( 'Report Description' ),
                    $attributes['description'] );

        $form->add( 'text',
                    'email_subject',
                    ts( 'Subject' ),
                    $attributes['email_subject'] );

        $form->add( 'text',
                    'email_to',
                    ts( 'To' ),
                    $attributes['email_to'] );
        
        $form->add( 'text',
                    'email_cc',
                    ts( 'CC' ),
                    $attributes['email_subject'] );
        
        $form->add( 'textarea',
                    'report_header',
                    ts( 'Report Header' ),
                    $attributes['header'] );
        
        $form->add( 'textarea',
                    'report_footer',
                    ts( 'Report Footer' ),
                    $attributes['footer'] );

        $form->addElement( 'checkbox', 'is_navigation', ts('Include Report in Navigation Menu?'), null, 
                           array('onclick' =>"return showHideByValue('is_navigation','','navigation_menu','table-row','radio',false);") );

        $form->addElement( 'checkbox', 'addToDashboard', ts('Available for Dashboard?') );
        
        $config = CRM_Core_Config::singleton( );
        if ( $config->userFramework != 'Joomla' ) {
            $form->addElement( 'select',
                               'permission',
                               ts( 'Permission' ),
                               array( '0' => '- Any One -') + CRM_Core_Permission::basicPermissions( ) );
        }
        // navigation field
        $parentMenu = CRM_Core_BAO_Navigation::getNavigationList( );
       
        $form->add( 'select', 'parent_id', ts( 'Parent Menu' ), array( '' => ts('-- select --') ) + $parentMenu );
        
        $form->addButtons( array(
                                 array ( 'type'      => 'submit',
                                         'name'      => ts('Save Report'),
                                         'isDefault' => true   ),
                                 array ( 'type'      => 'cancel',
                                         'name'      => ts('Cancel') ),
                                 )
                           );

        $form->addFormRule( array( 'CRM_Report_Form_Instance', 'formRule' ), $form );
    }

    static function formRule( $fields, $errors, $self ) {
        $buttonName = $self->controller->getButtonName( );
        $selfButtonName = $self->getVar( '_instanceButtonName' );
        
        $errors = array( );
        if ( $selfButtonName == $buttonName ) {
            if ( empty( $fields['title'] ) ) {
                $errors['title'] = ts( 'Title is a required field' );
                $self->assign('instanceFormError', true);
            }
        }

        return empty( $errors ) ? true : $errors;
    }

    static function setDefaultValues( &$form, &$defaults ) {
        $instanceID = $form->getVar( '_id' );
        $navigationDefaults = array();
        require_once 'CRM/Core/Config.php';
        $config = CRM_Core_Config::singleton(); 
        $defaults['report_header'] = $report_header = "<html>
  <head>
    <title>CiviCRM Report</title>
    <style type=\"text/css\">@import url({$config->userFrameworkResourceURL}css/print.css);</style>
  </head>
  <body><div id=\"crm-container\">";

        $defaults['report_footer'] = $report_footer = "<p><img src=\"{$config->userFrameworkResourceURL}i/powered_by.png\" /></p></div></body>
</html>
";
       
        if ( $instanceID ) {
            // this is already retrieved via Form.php
            $defaults['description']   = $defaults['description'];
            
            if ( CRM_Utils_Array::value( 'header', $defaults ) ) {
                $defaults['report_header'] = $defaults['header'];
            } 
            
            if ( CRM_Utils_Array::value( 'footer', $defaults ) ) {
                $defaults['report_footer'] = $defaults['footer'];
            }

            if ( CRM_Utils_Array::value( 'navigation_id', $defaults )) {
                //get the default navigation parent id
                $params = array( 'id' => $defaults['navigation_id'] );
                CRM_Core_BAO_Navigation::retrieve( $params, $navigationDefaults );
                $defaults['is_navigation'] = 1;
                $defaults['parent_id']     = CRM_Utils_Array::value( 'parent_id', $navigationDefaults );
                
                if ( CRM_Utils_Array::value( 'is_active', $navigationDefaults ) ) {
                    $form->assign('is_navigation', true);
                }
                
                if ( CRM_Utils_Array::value( 'id', $navigationDefaults ) ) {
                    $form->_navigation['id']        = $navigationDefaults['id'];
                    $form->_navigation['parent_id'] = $navigationDefaults['parent_id'];
                }
            }
        } else {
            $defaults['description'] = $form->_description;
        }
    }

    static function postProcess( &$form ) {
        $params = $form->getVar( '_params' );
        $config = CRM_Core_Config::singleton(); 
        $params['header']    = $params['report_header'];
        $params['footer']    = $params['report_footer'];
        $params['domain_id'] = CRM_Core_Config::domainID( );
        //navigation parameters
        if ( CRM_Utils_Array::value( 'is_navigation', $params ) ) {
            $form->_navigation['permission'] = array( );
            $permission = CRM_Utils_Array::value( 'permission', $params );
            
            $form->_navigation['current_parent_id']  = CRM_Utils_Array::value( 'parent_id', $form->_navigation );
            $form->_navigation['parent_id']          = CRM_Utils_Array::value( 'parent_id', $params );
            $form->_navigation['label']              = $params['title'];
            $form->_navigation['name']               = $params['title'];
            $form->_navigation['is_active']          = 1;
            
            if ( $permission ) {
                $form->_navigation['permission'][] = $permission;
            }
            //unset the navigation related element,
            //not used in report form values
            unset($params['parent_id']);
            unset($params['is_navigation']);
        }
        
        // add to dashboard
        $dashletParams = array( );
        if ( CRM_Utils_Array::value( 'addToDashboard', $params ) ) {
            $dashletParams = array( 'label'     =>  $params['title'],
                                    'is_active' => 1,
                                    'content'   => 'NULL');
                                
            $permission = CRM_Utils_Array::value( 'permission', $params );
            if ( $permission ) {
                $dashletParams['permission'][] = $permission;
            }
            unset( $params['addToDashboard'] );
        }
        
        require_once 'CRM/Report/DAO/Instance.php';
        $dao = new CRM_Report_DAO_Instance( );
        $dao->copyValues( $params );

        if ( $config->userFramework == 'Joomla' ) {
            $dao->permission = NULL;
        }

        // unset all the params that we use
        $fields = array( 'title', 'to_emails', 'cc_emails', 'header', 'footer',
                         'qfKey', '_qf_default', 'report_header', 'report_footer' );
        foreach ( $fields as $field ) {
            unset( $params[$field] );
        }
        $dao->form_values = serialize( $params );

        $instanceID = $form->getVar( '_id' );
        if ( $instanceID ) {
            $dao->id = $instanceID;
        }

        require_once 'CRM/Report/Utils/Report.php';
        $dao->report_id = CRM_Report_Utils_Report::getValueFromUrl( $instanceID );

        $dao->save( );

        $form->set( 'id', $dao->id );

        $reloadTemplate = false;
        if ( $dao->id ) {
            if ( !empty($form->_navigation) ) {
                $form->_navigation['url'] = "civicrm/report/instance/{$dao->id}&reset=1";
                $navigation = CRM_Core_BAO_Navigation::add( $form->_navigation );

                //set the navigation id in report instance table
                CRM_Core_DAO::setFieldValue( 'CRM_Report_DAO_Instance', $dao->id, 'navigation_id', $navigation->id );

                //reset navigation
                CRM_Core_BAO_Navigation::resetNavigation( );

                // in order to reflect change in navigation, template needs to be reloaded 
                $reloadTemplate = true;
            }
            
            // add to dashlet
            if ( !empty( $dashletParams ) ) {
                $section = 2;
                $chart = '';
                if ( CRM_Utils_Array::value( 'charts', $params ) ) {
                    $section = 1;
                    $chart = "&charts=". $params['charts'];
                    $dashletParams['is_fullscreen'] = 0;
                }

                $dashletParams['url'        ] = "civicrm/report/instance/{$dao->id}&reset=1&section={$section}&snippet=4{$chart}";
                $dashletParams['instanceURL'] = "civicrm/report/instance/{$dao->id}";
                require_once 'CRM/Core/BAO/Dashboard.php';
                CRM_Core_BAO_Dashboard::addDashlet(  $dashletParams );
            }
            
            $instanceParams   = array( 'value' => $dao->report_id );
            $instanceDefaults = array();
            $cmpName   = "Contact";
            $statusMsg = "null";
            CRM_Core_DAO::commonRetrieve( 'CRM_Core_DAO_OptionValue',
                                          $instanceParams,
                                          $instanceDefaults );

            if ( $cmpID = CRM_Utils_Array::value('component_id', $instanceDefaults) ) {
                $cmpName = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_Component', $cmpID,
                                                        'name', 'id');
                $cmpName = substr( $cmpName, 4 );
            }
            
            // Url to view this report and others created FROM this template
            $instanceUrl = CRM_Utils_System::url( 'civicrm/report/list',
                                                  "reset=1&ovid={$instanceDefaults['id']}" );
            $statusMsg = ts('Report "%1" has been created and is now available in the <a href="%3">report listings under "%2" Reports</a>.', array( 1 => $dao->title, 2 => $cmpName, 3 => $instanceUrl ));
            if ( $instanceID ) {
                $statusMsg = ts('Report "%1" has been updated.', array( 1 => $dao->title ));
            }
            CRM_Core_Session::setStatus( $statusMsg );
        }

        if ( $reloadTemplate ) {
            // as there's been change in navigation, reload the template 
            return CRM_Utils_System::redirect( CRM_Utils_System::url(CRM_Utils_System::currentPath( ), 'force=1') );
        }
    }
    
}
