<?php
declare(strict_types=1);

use CRM_SAGE_ExtensionUtil as E;

class CRM_SAGE_Page_SageTest extends CRM_Core_Page
{

    public function run()
    {
        // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
        CRM_Utils_System::setTitle(E::ts('SageTest'));

        $this->assign('sage_api_base', SAGE_API_BASE);

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
                'name' => 'Geocode State',
                'out' => array(
                    'city' => 'Albany',
                    'supplemental_address_1' => '131 State Street',
                    'state_province' => 'NY',
                    'postal_code' => '12207',
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

        $check_address_ret = CRM_Utils_SAGE::checkAddress($tests[0]['out']);
        $tests[0]['ret'] = $check_address_ret;
        $format_ret = CRM_Utils_SAGE::format($tests[1]['out'], true);
        $tests[1]['ret'] = $format_ret;
        $dist_assign_ret = CRM_Utils_SAGE::distassign($tests[2]['out']);
        $tests[2]['ret'] = $dist_assign_ret;
        $lookup_ret = CRM_Utils_SAGE::lookup($tests[3]['out'], true);
        $tests[3]['ret'] = $lookup_ret;



        $this->assign('tests', $tests);
        parent::run();
    }

}
