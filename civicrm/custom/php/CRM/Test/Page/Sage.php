<?php

require_once 'CRM/Core/Page.php';
require_once 'CRM/Utils/SAGE.php';

class CRM_Test_Page_Sage extends CRM_Core_Page {

	function run( )
	{
		$tests = array(
			0 => array(
				'name' => 'Standardize Lark',
				'out' => array(
					'city' => 'Albany',
					'street_address' => '80 Lark Street',
					'state_province' => 'NY',
				)
			),
			1 => array(
				'name' => 'Geocode Tolland',
				'out' => array(
					'city' => 'Tolland',
					'supplemental_address_1' => '30 Stone Pond Drive',
					'state_province' => 'CT',
					'postal_code' => '12180',
				)
			),
			2 => array(
				'name' => 'Distassign Troy',
				'out' => array(
					'city' => 'Troy',
					'state_province' => 'NY',
					'postal_code' => '12180',
					'street_address' => '90 14th Street',
				)
			),
			3 => array(
				'name' => 'Lookup Rensselaer Amtrak',
				'out' => array(
					'city' => 'Rensselaer',
					'state_province' => 'NY',
					'street_address' => 'East Street'
				)
			)
		);

		CRM_Utils_SAGE::checkAddress($tests[0]['out']);
		CRM_Utils_SAGE::format($tests[1]['out'],true);
		CRM_Utils_SAGE::distassign($tests[0]['out']);
		CRM_Utils_SAGE::distassign($tests[2]['out']);
		CRM_Utils_SAGE::lookup($tests[3]['out'],true);
		$this->assign( 'tests', $tests );
		parent::run();
	}
}

?>
