<?php
	require_once 'get_services/xmlrpc-api-senators.inc';
	
	class FormHelper {
		/*
		 * used to assign default value if value doesn't
		 * exist within associative array
		 */
		static function get_default($optlist, $option, $default = NULL) {
			if($optlist && array_key_exists($option, $optlist)) {
				return $optlist[$option]
							? $optlist[$option]
							: $default;
			}
			return $default;
		}
		
		static function get_bb_config($site = 'sd99') {
			require_once dirname(__FILE__) . './../../bluebird_config.php';
			return get_bluebird_instance_config($site);
		}
		
		/**
		 * initiate session/config for given $site
		 * @param $site
		 * @param $key
		 * @return CRM_CORE_CONFIG initiated on $site
		 */
		static function get_config($site = 'sd99', $key = NULL) {
			$_SERVER['PHP_SELF'] = "/index.php";
			$_SERVER['HTTP_HOST'] = $site;
			$_SERVER['SCRIPT_FILENAME'] = __FILE__;
			$_REQUEST['key'] = $key;
			require_once "../../../drupal/sites/default/civicrm.settings.php";
			require_once 'CRM/Core/Config.php';
			$config = CRM_Core_Config::singleton(true, true);
			
			return $config;
		}
		
		static function valid_instance($instance) {
			$instances = self::get_instances();
			
			return in_array($instance, $instances);
		}
		
		/**
		 *
		 * @return array of live instances
		 */
		static function get_instances() {
			/*exec('../../scripts/iterateInstances.sh --live --quiet', $output);
		
			$instances = split(" ", $output[0]);
		
			return $instances;*/
			//TODO
			return split(" ", "template sd99 3rdparty adams addabbo alesi avella ball ".
				"bonacic breslin carlucci defrancisco diaz dilan duane espaillat ".
				"farley flanagan fuschillo gallivan gianaris golden griffo grisanti ".
				"hannon hassellthompson huntley ojohnson kennedy klein krueger kruger ".
				"lanza larkin lavalle libous little marcellino martins maziarz mcdonald ".
				"montgomery nozzolio omara oppenheimer parker peralta perkins ranzenhofer ".
				"ritchie rivera robach ruralresources saland sampson savino serrano seward ".
				"skelos smith squadron stavisky stewartcousins valesky young zeldin training1 ".
				"training2 training3 training4 example sd83 sd95 sd98 mincomms demo ".
				"123click aubertine espada foley cjohnson leibell onorato padavan ".
				"schneiderman stachowski thompson volker winner");
		}
		
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
		static function get_senator_map($api_key, $domain_name, $map_key = 'district', $force = false) {
			static $senators;
			
			if(!$senators || $force) {
				$senators = array();
				
				if($api_key && $domain_name) {
					$service = new SenatorData($domain_name, $api_key);
					$values = $service->get();
					
					foreach($values as $senator) {
						$senators[$senator[$map_key]] = $senator;
					}
				}
			}
			
			return $senators;
		}
	}
