<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2017                                |
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
namespace Civi\FlexMailer\ClickTracker;

class TextClickTracker implements ClickTrackerInterface {

  public function filterContent($msg, $mailing_id, $queue_id) {
    return self::replaceTextUrls($msg,
      function ($url) use ($mailing_id, $queue_id) {
        if (strpos($url, '{') !== FALSE) {
          return $url;
        }
        return \CRM_Mailing_BAO_TrackableURL::getTrackerURL($url, $mailing_id,
          $queue_id);
      }
    );
  }

  /**
   * Find any URLs and replace them.
   *
   * @param string $text
   * @param callable $replace
   *   Function(string $oldUrl) => string $newUrl.
   * @return mixed
   *   String, text.
   */
  public static function replaceTextUrls($text, $replace) {
    $callback = function ($matches) use ($replace) {
      // ex: $matches[0] == 'http://foo.com'
      return $replace($matches[0]);
    };
    // Find any HTTP(S) URLs in the text.
    // return preg_replace_callback('/\b(?:(?:https?):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $callback, $tex
    return preg_replace_callback('/\b(?:(?:https?):\/\/)[-A-Z0-9+&@#\/%=~_|$?!:,.{}]*[A-Z0-9+&@#\/%=~_|${}]/i',
      $callback, $text);
  }

}
