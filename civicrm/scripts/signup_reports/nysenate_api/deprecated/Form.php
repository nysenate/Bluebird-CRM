<?php

require_once 'get_services/xmlrpc-api-senators.inc';

abstract class Form
{
  abstract function getRawEntries($start_date, $end_date, $start_id, $end_id, $limit = 1000);
  abstract function getFormContacts($start_date, $end_date, $start_id, $end_id, $limit = 1000);
  abstract function formContactFromEntry($entry);

  public $api_key;
  public $domain_name;


  function __construct($api_key, $domain_name)
  {
    $this->api_key = $api_key;
    $this->domain_name = $domain_name;
  }


  /*
   * used to assign default value if value doesn't
   * exist within associative array
   */
  static function get_default($optlist, $option, $default = NULL)
  {
    if ($optlist && isset($optlist[$option])) {
      return $optlist[$option];
    }
    else {
      return $default;
    }
  } // get_default()


  static function get_bb_config($site = 'sd99')
  {
    require_once dirname(__FILE__) . './../../bluebird_config.php';
    return get_bluebird_instance_config($site);
  } // get_bb_config()


  /**
   * initiate session/config for given $site
   * @param $site
   * @param $key
   * @return CRM_CORE_CONFIG initiated on $site
   */
  static function get_config($site = 'sd99', $key = NULL)
  {
    $_SERVER['PHP_SELF'] = "/index.php";
    $_SERVER['HTTP_HOST'] = $site;
    $_SERVER['SCRIPT_FILENAME'] = __FILE__;
    $_REQUEST['key'] = $key;
    require_once "../../../drupal/sites/default/civicrm.settings.php";
    require_once 'CRM/Core/Config.php';
    $config = CRM_Core_Config::singleton(true, true);
    return $config;
  } // get_config()


  static function valid_instance($instance)
  {
    $instances = self::get_instances();
    return in_array($instance, $instances);
  } // valid_instance()


  /**
   *
   * @return array of live instances
   */
  static function get_instances()
  {
    return null;   // need an implementation for this
  } // get_instances()


  /**
   *
   * returns senator map from nysenate.gov as an associative array
   * with keys defined by $map_key (so you can define key as
   * district number, senator short name, etc.)
   * @param $api_key services key
   * @param $domain_name
   * @param $map_key
   * @param $force if true overrides static copy
   */
  static function get_senator_map($api_key, $domain_name, $map_key = 'district', $force = false)
  {
    static $senators;

    if (!$senators || $force) {
      $senators = array();

      if ($api_key && $domain_name) {
        $service = new SenatorData($domain_name, $api_key);
        $values = $service->get();

        foreach ($values as $senator) {
          $senators[$senator[$map_key]] = $senator;
        }
      }
    }

    return $senators;
  } // get_senator_map()
}
