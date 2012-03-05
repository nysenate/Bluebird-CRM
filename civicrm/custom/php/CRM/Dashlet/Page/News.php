<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
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

require_once 'CRM/Core/Page.php';

/**
 * News RSS dashlet
 *
 */
class CRM_Dashlet_Page_News extends CRM_Core_Page 
{
    /**
     * List RSS feed as dashlet
     *
     * @return none
     * @access public
     */
    function run( ) {

        function rss_to_array($tag, $array, $url) {
            $doc = new DOMdocument();
            $doc->load($url);
            $rss_array = array();
            $items = array();
            $i = 0;
            foreach($doc->getElementsByTagName($tag) AS $node) {
                //only show the 5 most recent posts
                if ( $i > 4 ) {
                    break;
                }
                foreach($array AS $key => $value) {
                    $items[$value] = $node->getElementsByTagName($value)->item(0)->nodeValue;
                    if ( $value == 'pubDate' ) {
                        $items[$value] = date("l, M j, Y g:ia", strtotime($items[$value]));
                    }
                }
                array_push($rss_array, $items);
                $i++;
            }
            return $rss_array;
        }
        
        $rss_tags = array( 'title',
                           'pubDate',
                           'description',
                           'link',
                           'category',
                           'creator',
                           'comments',
                           'guid',
                           'encoded',
                         );
        $rss_item_tag = 'item';
        $rss_url = 'http://senateonline.senate.state.ny.us/BluebirdNews.nsf/feed.rss';
        
        $rssfeed = rss_to_array($rss_item_tag,$rss_tags,$rss_url);
        //CRM_Core_Error::debug($rssfeed);
        
        $this->assign('newsfeed', $rssfeed);

        return parent::run( );
    }
}