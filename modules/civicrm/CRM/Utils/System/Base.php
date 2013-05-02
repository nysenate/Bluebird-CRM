<?php

/**
 * Base class for UF system integrations
 */
class CRM_Utils_System_Base {
  var $is_drupal = FALSE;
  var $is_joomla = FALSE;
  var $is_wordpress = FALSE;

  /*
     * Does the CMS allow CMS forms to be extended by hooks
     */

  var $supports_form_extensions = FALSE;

  /**
   * if we are using a theming system, invoke theme, else just print the
   * content
   *
   * @param string  $type    name of theme object/file
   * @param string  $content the content that will be themed
   * @param array   $args    the args for the themeing function if any
   * @param boolean $print   are we displaying to the screen or bypassing theming?
   * @param boolean $ret     should we echo or return output
   * @param boolean $maintenance  for maintenance mode
   *
   * @return void           prints content on stdout
   * @access public
   */
  function theme($type, &$content, $args = NULL, $print = FALSE, $ret = FALSE, $maintenance = FALSE) {
    // TODO: Split up; this was copied verbatim from CiviCRM 4.0's multi-UF theming function
    // but the parts should be copied into cleaner subclass implementations
    if (function_exists('theme') && !$print) {
      if ($maintenance) {
        drupal_set_breadcrumb('');
        drupal_maintenance_theme();
        print theme('maintenance_page', array('content' => $content));
        exit();
      }
      $out = $content;
      $ret = TRUE;
    }
    else {
      $out = $content;
    }

    $config = &CRM_Core_Config::singleton();
    if (!$print &&
      $config->userFramework == 'WordPress'
    ) {
      if (is_admin()) {
        require_once (ABSPATH . 'wp-admin/admin-header.php');
      }
      else {
        // FIX ME: we need to figure out to replace civicrm content on the frontend pages
      }
    }

    if ($ret) {
      return $out;
    }
    else {
      print $out;
    }
  }

  function getDefaultBlockLocation() {
    return 'left';
  }

  function getVersion() {
    return 'Unknown';
  }

  /**
   * Format the url as per language Negotiation.
   *
   * @param string $url
   *
   * @return string $url, formatted url.
   * @static
   */
  function languageNegotiationURL(
    $url,
    $addLanguagePart = TRUE,
    $removeLanguagePart = FALSE
  ) {
    return $url;
  }

  /*
     * Currently this is just helping out the test class as defaults is calling it - maybe move fix to defaults
     */
  function cmsRootPath() {
  }
}

