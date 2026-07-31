<?php

class CRM_NYSS_Dashboard_Utils
{
    /**
     * Used to remove a dashlet from the dashboard when the dashlet is a legacy report and the current user doesn't
     * have access to that report based on their group roles.
     * The intent is for this function to be used in conjunction with array_filter().
     * It returns FALSE when the dashlet should be removed.
     * But the default behavior is to return TRUE, allowing the dashlet to stay unless certain conditions are met.
     *
     * NYSS 3439
     * @param $record
     * @return bool - TRUE
     */
    #[CRM_NYSS_Attribute_IssueRefs('3439')]
    public static function filterDashletsBasedOnReport($record):bool {
        //NYSS 3439 also check for group based permission if created from report
        $url = $record['url'] ?? '';
        if (!preg_match('#civicrm/report/instance/(\d+)#', $url, $matches)) {
            return TRUE; // allow, not our job to prevent access if not within the context of a legacy report instance.
        }
        return CRM_Report_Utils_Report::isInstanceGroupRoleAllowed((int)$matches[1]);
    }
}