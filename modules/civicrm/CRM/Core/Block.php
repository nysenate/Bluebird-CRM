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

require_once 'CRM/Core/Menu.php';
require_once 'CRM/Core/Permission.php';

/**
 * defines a simple implemenation of a drupal block.
 * blocks definitions and html are in a smarty template file
 *
 */
class CRM_Core_Block {

    /**
     * the following blocks are supported
     *
     * @var int
     */
    const
        CREATE_NEW      = 1,
        RECENTLY_VIEWED = 2,
        DASHBOARD       = 3,
        ADD             = 4,
        LANGSWITCH      = 5,
        EVENT           = 6,
        FULLTEXT_SEARCH = 7;
    
    /**
     * template file names for the above blocks
     */
    static $_properties = null;

    /**
     * class constructor
     *
     */
    function __construct( ) {
    }

    /**
     * initialises the $_properties array
     * @return void
     */
    static function initProperties()
    {
        if ( ! defined( 'BLOCK_CACHE_GLOBAL' ) ) {
            define('BLOCK_CACHE_GLOBAL', 0x0008);
        }

        if ( ! defined( 'BLOCK_CACHE_PER_PAGE' ) ) {
            define('BLOCK_CACHE_PER_PAGE', 0x0004);
        }

        if ( ! defined( 'BLOCK_NO_CACHE' ) ) {
            define('BLOCK_NO_CACHE', -1);
        }
        
        if (!(self::$_properties)) {
            self::$_properties = array(
            // set status item to 0 to disable block by default (at install)
                                       self::CREATE_NEW   => array( 'template'   => 'CreateNew.tpl',
                                                                   'info'       => ts('CiviCRM Create New Record'),
                                                                   'subject'    => ts(''),
                                                                   'active'     => true,
                                                                   'cache'      => BLOCK_CACHE_GLOBAL,
                                                                   'visibility' => 1,
                                                                   'weight'     => -100,
                                                                   'status'     => 1,
                                                                   'pages'      => 'civicrm*',
                                                                   'region'     => 'left' ),
                                       self::RECENTLY_VIEWED => array(  'template'   => 'RecentlyViewed.tpl',
                                                                         'info'       => ts('CiviCRM Recent Items'),
                                                                         'subject'    => ts('Recent Items'),
                                                                         'active'     => true,
                                                                         'cache'      => BLOCK_NO_CACHE,
                                                                         'visibility' => 1,
                                                                         'weight'     => -99,
                                                                         'status'     => 1,
                                                                         'pages'      => 'civicrm*',
                                                                         'region'     => 'left' ),                
                                       self::DASHBOARD   => array( 'template'   => 'Dashboard.tpl',
                                                                   'info'       => ts('CiviCRM Contact Dashboard'),
                                                                   'subject'    => '',
                                                                   'active'     => true,
                                                                   'cache'      => BLOCK_NO_CACHE,
                                                                   'visibility' => 1,
                                                                   'weight'     => -98,
                                                                   'status'     => 1,
                                                                   'pages'      => 'civicrm*',
                                                                   'region'     => 'left' ),
                                       self::ADD         => array( 'template'   => 'Add.tpl',
                                                                   'info'       => ts('CiviCRM Quick Add'),
                                                                   'subject'    => ts('New Individual'),
                                                                   'active'     => true,
                                                                   'cache'      => BLOCK_CACHE_GLOBAL,
                                                                   'visibility' => 1,
                                                                   'weight'     => -97,
                                                                   'status'     => 1,
                                                                   'pages'      => 'civicrm*',
                                                                   'region'     => 'left' ),
                                       self::LANGSWITCH  => array( 'template'   => 'LangSwitch.tpl',
                                                                   'info'       => ts('CiviCRM Language Switcher'),
                                                                   'subject'    => '',
                                                                   'templateValues' => array(),
                                                                   'active'     => true,
                                                                   'cache'      => BLOCK_NO_CACHE,
                                                                   'visibility' => 1,
                                                                   'weight'     => -96,
                                                                   'status'     => 1,
                                                                   'pages'      => 'civicrm*',
                                                                   'region'     => 'left' ),
                                       self::EVENT      => array( 'template'   => 'Event.tpl',
                                                                   'info'       => ts('CiviCRM Upcoming Events'),
                                                                   'subject'    => ts('Upcoming Events'),
                                                                   'templateValues' => array(),
                                                                   'active'     => true,
                                                                   'cache'      => BLOCK_NO_CACHE,
                                                                   'visibility' => 1,
                                                                   'weight'     => -95,
                                                                   'status'     => 0,
                                                                   'pages'      => 'civicrm*',
                                                                   'region'     => 'left' ),
                                       self::FULLTEXT_SEARCH => array(  'template'   => 'FullTextSearch.tpl',
                                                                        'info'       => ts('CiviCRM Full-text Search'),
                                                                        'subject'    => ts('Full-text Search'),
                                                                        'active'     => true,
                                                                        'cache'      => BLOCK_CACHE_GLOBAL,
                                                                        'visibility' => 1,
                                                                        'weight'     => -94,
                                                                        'status'     => 0,
                                                                        'pages'      => 'civicrm*',
                                                                        'region'     => 'left' )
                                       );
        
            ksort(self::$_properties);
        }
    }

    /**
     * returns the desired property from the $_properties array
     *
     * @params int    $id        one of the class constants (ADD, SEARCH, etc.)
     * @params string $property  the desired property
     *
     * @return string  the value of the desired property
     */
    static function getProperty($id, $property)
    {
        if (!(self::$_properties)) {
            self::initProperties();
        }
        return self::$_properties[$id][$property];
    }

    /**
     * sets the desired property in the $_properties array
     *
     * @params int    $id        one of the class constants (ADD, SEARCH, etc.)
     * @params string $property  the desired property
     * @params string $value     the value of the desired property
     *
     * @return void
     */
    static function setProperty($id, $property, $value)
    {
        if (!(self::$_properties)) {
            self::initProperties();
        }
        self::$_properties[$id][$property] = $value;
    }

    /**
     * returns the whole $_properties array
     * @return array  the $_properties array
     */
    static function properties()
    {
        if (!(self::$_properties)) {
            self::initProperties();
        }
        return self::$_properties;
    }

    /**
     * Creates the info block for drupal
     *
     * @return array 
     * @access public
     */
    static function getInfo( ) {
                
        $block = array( );
        foreach ( self::properties() as $id => $value ) {
             if ( $value['active'] ) {
                 if ( in_array( $id, array( self::ADD, self::CREATE_NEW ) ) ) {
                     $hasAccess = true;
                     if ( !CRM_Core_Permission::check('add contacts') && 
                          !CRM_Core_Permission::check('edit groups') ) {
                         $hasAccess = false;
                     }
                     //validate across edit/view - CRM-5666
                     if ( $hasAccess && ($id == self::ADD) ) {
                         $hasAccess = CRM_Core_Permission::giveMeAllACLs( );
                     }
                     if ( !$hasAccess ) continue; 
                 }
                 
                 if ( $id == self::EVENT &&
                      ( ! CRM_Core_Permission::access( 'CiviEvent', false ) ||
                        ! CRM_Core_Permission::check( 'view event info' ) ) ) {
                     continue;
                 }

                 $block[$id] = array(
                                     'info'       => $value['info']      ,
                                     'cache'      => $value['cache']     ,
                                     'status'     => $value['active']    ,
                                     'region'     => $value['region']    ,
                                     'visibility' => $value['visibility'],
                                     'pages'      => $value['pages']     ,
                                     'status'     => $value['status']    ,
                                     'weight'     => $value['weight']    
                                     );
            }
        }
        
        return $block;
    }

    /**
     * set the post action values for the block.
     *
     * php is lame and u cannot call functions from static initializers
     * hence this hack
     *
     * @return void
     * @access private
     */
    private function setTemplateValues( $id ) {
        switch ( $id ) {

        case self::CREATE_NEW:
            self::setTemplateShortcutValues( );
            break;

        case self::DASHBOARD:
            self::setTemplateDashboardValues( );
            break;    

        case self::ADD:
            require_once "CRM/Core/BAO/LocationType.php";
            $defaultLocation =& CRM_Core_BAO_LocationType::getDefault();
            $defaultPrimaryLocationId = $defaultLocation->id;
            
            $values = array( 'postURL' => CRM_Utils_System::url( 'civicrm/contact/add', 'reset=1&ct=Individual' ), 
                             'primaryLocationType' => $defaultPrimaryLocationId );
            self::setProperty( self::ADD,
                               'templateValues',
                               $values );
            break;


        case self::FULLTEXT_SEARCH:
            $urlArray = array( 'fullTextSearchID'  => CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionValue',
                                                    'CRM_Contact_Form_Search_Custom_FullText', 'value', 'name' ) );
            self::setProperty( self::FULLTEXT_SEARCH, 'templateValues', $urlArray );
            break;    

        case self::RECENTLY_VIEWED:
            require_once 'CRM/Utils/Recent.php';
            $recent  =& CRM_Utils_Recent::get( );
            self::setProperty( self::RECENTLY_VIEWED, 'templateValues', array( 'recentlyViewed' => $recent ) );
            break;

        case self::EVENT:
            self::setTemplateEventValues( );
            break;

        }
    }

    /**
     * create the list of options to create New objects for the application and format is as a block
     *
     * @return void
     * @access private
     */
    private function setTemplateShortcutValues( ) {
        $config = CRM_Core_Config::singleton( );

        static $shortCuts = array( );
        
        if (!($shortCuts)) {
            if (CRM_Core_Permission::check('add contacts')) {
                if ( CRM_Core_Permission::giveMeAllACLs() ) {
                    require_once 'CRM/Contact/BAO/ContactType.php';
                    $shortCuts = CRM_Contact_BAO_ContactType::getCreateNewList( );
                }
                if ( CRM_Core_Permission::access( 'Quest' ) ) {
                    $shortCuts = array_merge($shortCuts, array( array( 'path'  => 'civicrm/quest/search',
                                                                       'query' => 'reset=1',
                                                                       'ref'   => 'quest-search',
                                                                       'title' => ts('Quest Search') ))); 
                }
            }

            // new activity (select target contact)
            $shortCuts = array_merge($shortCuts, array( array( 'path'  => 'civicrm/activity',
                                                               'query' => 'action=add&reset=1&context=standalone',
                                                               'ref'   => 'new-activity',
                                                               'title' => ts('Activity') ) ));
                    
            require_once 'CRM/Core/Component.php';
            $components = CRM_Core_Component::getEnabledComponents();
            
            if ( !empty( $config->enableComponents ) ) {
                foreach( $components as $componentName => $obj ) {
                    if ( in_array(  $componentName, $config->enableComponents ) ) {
                        eval( '$obj->creatNewShortcut( $shortCuts );');
                    }
                }
            }
            
            // new email (select recipients)
            $shortCuts = array_merge($shortCuts, array( array( 'path'  => 'civicrm/activity/email/add',
                                                               'query' => 'atype=3&action=add&reset=1&context=standalone',
                                                               'ref'   => 'new-email',
                                                               'title' => ts('Email') ) ));
                                    
            if ( CRM_Core_Permission::check('edit groups') ) {
                $shortCuts = array_merge($shortCuts, array( array( 'path'  => 'civicrm/group/add',
                                                                   'query' => 'reset=1',
                                                                   'ref'   => 'new-group',
                                                                   'title' => ts('Group') ) ));
            }

            if ( CRM_Core_Permission::check('administer CiviCRM') ) {
                $shortCuts = array_merge($shortCuts, array( array( 'path'  => 'civicrm/admin/tag',
                                                                   'query' => 'reset=1&action=add',
                                                                   'ref'   => 'new-tag',
                                                                   'title' => ts('Tag') ) ));
            }

            if ( empty( $shortCuts ) ) {
                return null;
            }
        }
            
        $values = array( );
        foreach ( $shortCuts as $short ) {
            $value = array( );
            if ( isset( $short['url'] ) ) {
                $value['url'] = $short['url'];
            } else {
                $value['url'] = CRM_Utils_System::url( $short['path'], $short['query'], false );
            }
            $value['title'] = $short['title'];
            $value['ref'] = $short['ref'];
            $values[] = $value;
        }
        
        // call links hook to add user defined links
        require_once 'CRM/Utils/Hook.php';
        CRM_Utils_Hook::links( 'create.new.shorcuts',
                               null,
                               CRM_Core_DAO::$_nullObject,
                               $values,
                               CRM_Core_DAO::$_nullObject );
                    
        self::setProperty( self::CREATE_NEW, 'templateValues', array( 'shortCuts' => $values ) );
    }

    /**
     * create the list of dashboard links
     *
     * @return void
     * @access private
     */
    private function setTemplateDashboardValues( ) {
        static $dashboardLinks = array( );
        if ( CRM_Core_Permission::check('access Contact Dashboard')) {
            $dashboardLinks = array( array( 'path'  => 'civicrm/user',
                                            'query' => 'reset=1',
                                            'title' => ts('My Contact Dashboard') ) );
        }

        if ( empty( $dashboardLinks ) ) {
            return null;
        }

        $values = array( );
        foreach ( $dashboardLinks as $dash ) {
            $value = array( );
            if ( isset( $dash['url'] ) ) {
                $value['url'] = $dash['url'];
            } else {
                $value['url'] = CRM_Utils_System::url( $dash['path'], $dash['query'], false );
            }
            $value['title'] = $dash['title'];
            $value['key'] = CRM_Utils_Array::value( 'key', $dash );
            $values[] = $value;
        }
        self::setProperty( self::DASHBOARD, 'templateValues', array( 'dashboardLinks' => $values ) );
    }

    /**
     * create the list of mail urls for the application and format is as a block
     *
     * @return void
     * @access private
     */
    private function setTemplateMailValues( ) {
        static $shortCuts = null;
        
        if (!($shortCuts)) {
             $shortCuts = array( array( 'path'  => 'civicrm/mailing/send',
                                        'query' => 'reset=1',
                                        'title' => ts('Send Mailing') ),
                                 array( 'path'  => 'civicrm/mailing/browse',
                                        'query' => 'reset=1',
                                        'title' => ts('Browse Sent Mailings') ),
                                 );
        }

        $values = array( );
        foreach ( $shortCuts as $short ) {
            $value = array( );
            $value['url'  ] = CRM_Utils_System::url( $short['path'], $short['query'] );
            $value['title'] = $short['title'];
            $values[] = $value;
        }
        self::setProperty( self::MAIL, 'templateValues', array( 'shortCuts' => $values ) );
    }

    /**
     * create the list of shortcuts for the application and format is as a block
     *
     * @return void
     * @access private
     */
    private function setTemplateMenuValues( ) {
        $config = CRM_Core_Config::singleton( );

        $path = 'navigation';
        $values =& CRM_Core_Menu::getNavigation( );
        if ( $values ) {
            self::setProperty( self::MENU, 'templateValues', array( 'menu' => $values ) );
        }
    }

    /**
     * create the event blocks for upcoming events
     *
     * @return void
     * @access private
     */
    private function setTemplateEventValues( ) {
        $config = CRM_Core_Config::singleton( );
        
        require_once 'CRM/Event/BAO/Event.php';
        $info = CRM_Event_BAO_Event::getCompleteInfo( date("Ymd") );

        if ( $info ) {
            $session = CRM_Core_Session::singleton( );
            // check if registration link should be displayed
            foreach ( $info as $id => $event ) {
                $info[$id]['onlineRegistration'] = CRM_Event_BAO_Event::validRegistrationDate( $event,
                                                                                               $session->get( 'userID' ) );
            }

            self::setProperty( self::EVENT, 'templateValues', array( 'eventBlock' => $info ) );
        }

    }

    /**
     * Given an id creates a subject/content array
     *
     * @param int $id id of the block
     *
     * @return array
     * @access public
     */
    static function getContent( $id ) {
        // return if upgrade mode
        $config = CRM_Core_Config::singleton( );
        if ( CRM_Utils_Array::value( $config->userFrameworkURLVar, $_GET ) == 'civicrm/upgrade' ) {
            return;
        }

        if ( ! self::getProperty( $id, 'active' ) ) {
            return null;
        }
        
        if ( $id == self::EVENT &&
             CRM_Core_Permission::check( 'view event info' ) ) {
            // is CiviEvent enabled?
            if ( ! CRM_Core_Permission::access( 'CiviEvent', false ) ) {
                return null;
            }
            // do nothing
        } else if ( ! CRM_Core_Permission::check( 'access CiviCRM' ) ) {
            return null;
        } else if ( $id == self::ADD ) {
            $hasAccess = true;
            if ( !CRM_Core_Permission::check( 'add contacts' ) &&
                 !CRM_Core_Permission::check('edit groups') ) {
                $hasAccess = false;
            }
            //validate across edit/view - CRM-5666
            if ( $hasAccess ) {
                $hasAccess = CRM_Core_Permission::giveMeAllACLs( );
            }
            if ( !$hasAccess ) return null;
        }
        
        self::setTemplateValues( $id );

        // Suppress Recent Items block if it's empty - CRM-5188
        if ($id == self::RECENTLY_VIEWED){
            $recent = self::getProperty( $id, 'templateValues' );
            if ( CRM_Utils_Array::crmIsEmptyArray( $recent ) ){
                return null;
            }
        }

        $block = array( );
        $block['name'   ] = 'block-civicrm';
        $block['id'     ] = $block['name'] . '_' . $id;
        $block['subject'] = self::fetch( $id, 'Subject.tpl',
                                         array( 'subject' => self::getProperty( $id, 'subject' ) ) );
        $block['content'] = self::fetch( $id, self::getProperty( $id, 'template' ),
                                         self::getProperty( $id, 'templateValues' ) );
        
        
        return $block;
    }

    /**
     * Given an id and a template, fetch the contents
     *
     * @param int    $id         id of the block
     * @param string $fileName   name of the template file
     * @param array  $properties template variables
     *
     * @return array
     * @access public
     */
    static function fetch( $id, $fileName, $properties ) {
        $template = CRM_Core_Smarty::singleton( );

        if ( $properties ) {
            $template->assign( $properties );
        }

        return $template->fetch( 'CRM/Block/' . $fileName );
    }

}


