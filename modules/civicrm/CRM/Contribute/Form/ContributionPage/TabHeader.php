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
 * Helper class to build navigation links
 */
class CRM_Contribute_Form_ContributionPage_TabHeader 
{
    static function build( &$form ) 
    {
        $tabs = $form->get( 'tabHeader' );
        if ( !$tabs || !CRM_Utils_Array::value('reset', $_GET) ) {
            $tabs =& self::process( $form );
            $form->set( 'tabHeader', $tabs );
        }
        $form->assign_by_ref( 'tabHeader', $tabs );
        $form->assign_by_ref( 'selectedTab', self::getCurrentTab($tabs) );
        return $tabs;
    }
    
    static function process( &$form ) 
    {
        if ( $form->getVar( '_id' ) <= 0 ) {
            return null;
        }
        
        $tabs = array(
                      'settings'     => array( 'title'   => ts( 'Title' ),
                                               'link'    => null,
                                               'valid'   => false,
                                               'active'  => false,
                                               'current' => false,
                                               ),
                      'amount'       => array( 'title'   => ts( 'Amounts' ),
                                               'link'    => null,
                                               'valid'   => false,
                                               'active'  => false,
                                               'current' => false,
                                               ),
                      'membership'   => array( 'title'   => ts( 'Memberships' ),
                                               'link'    => null,
                                               'valid'   => false,
                                               'active'  => false,
                                               'current' => false,
                                               ),
                      'thankYou'     => array( 'title'   => ts( 'Receipt' ),
                                               'link'    => null,
                                               'valid'   => false,
                                               'active'  => false,
                                               'current' => false,
                                               ),
                      'friend'       => array( 'title'   => ts( 'Tell a Friend' ),
                                               'link'    => null,
                                               'valid'   => false,
                                               'active'  => false,
                                               'current' => false,
                                               ),
                      'custom'       => array( 'title'   => ts( 'Profiles' ),
                                               'link'    => null,
                                               'valid'   => false,
                                               'active'  => false,
                                               'current' => false,
                                               ),  
                      'premium'      => array( 'title'   => ts( 'Premiums' ),
                                               'link'    => null,
                                               'valid'   => false,
                                               'active'  => false,
                                               'current' => false,
                                               ),
                      'widget'       => array( 'title'   => ts( 'Widgets' ),
                                               'link'    => null,
                                               'valid'   => false,
                                               'active'  => false,
                                               'current' => false,
                                               ),
                      'pcp'          => array( 'title'   => ts( 'Personal Campaigns' ),
                                               'link'    => null,
                                               'valid'   => false,
                                               'active'  => false,
                                               'current' => false,
                                               ),
                      );

        $contribPageId = $form->getVar( '_id' );
        $fullName      = $form->getVar( '_name' );
        $className     = CRM_Utils_String::getClassName( $fullName );
        
        if ( $className == 'ThankYou' ) {
            $class = 'thankYou';
        } else if ( $className == 'Contribute' ) {
            $class = 'friend';
        } else if ( $className == 'MembershipBlock' ) {
            $class = 'membership';
        } else {
            $class = strtolower($className) ;
        }

        $qfKey = $form->get( 'qfKey' );
        $form->assign( 'qfKey', $qfKey );

        if ( array_key_exists( $class, $tabs ) ) {
            $tabs[$class]['current'] = true;
        }

        if ( $contribPageId ) {
            $reset = CRM_Utils_Array::value( 'reset', $_GET ) ? 'reset=1&' : '';
            
            foreach ( $tabs as $key => $value ) {
                $tabs[$key]['link']   = CRM_Utils_System::url( "civicrm/admin/contribute/{$key}",
                                                               "{$reset}action=update&snippet=4&id={$contribPageId}&qfKey={$qfKey}" );
                $tabs[$key]['active'] = $tabs[$key]['valid'] = true;
            }
            //get all section info.
            require_once 'CRM/Contribute/BAO/ContributionPage.php';
            $contriPageInfo = CRM_Contribute_BAO_ContributionPage::getSectionInfo( array( $contribPageId ) );
            
            foreach ( $contriPageInfo[$contribPageId] as $section => $info ) {
                if ( !$info ) {
                    $tabs[$section]['valid'] = false;
                }
            }
        }
        return $tabs;
    }

    static function reset( &$form ) 
    {
        $tabs =& self::process( $form );
        $form->set( 'tabHeader', $tabs );
    }

    static function getCurrentTab( $tabs ) 
    {
        static $current = false;

        if ( $current ) {
            return $current;
        }
        
        if ( is_array($tabs) ) {
            foreach ( $tabs as $subPage => $pageVal ) {
                if ( $pageVal['current'] === true ) {
                    $current = $subPage;
                    break;
                }
            }
        }
        
        $current = $current ? $current : 'settings';
        return $current;

    }
}