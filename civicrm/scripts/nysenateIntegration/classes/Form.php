<?
	abstract class Form {
		abstract function getRawEntries($start_date, $end_date, $start_id, $end_id, $limit = 1000);
		abstract function getFormContacts($start_date, $end_date, $start_id, $end_id, $limit = 1000);
		abstract function formContactFromEntry($entry);
		
		public $api_key;
		public $domain_name;
		
		function __construct($api_key, $domain_name) {
			
			$this->api_key = $api_key;
			$this->domain_name = $domain_name;
		}
	}