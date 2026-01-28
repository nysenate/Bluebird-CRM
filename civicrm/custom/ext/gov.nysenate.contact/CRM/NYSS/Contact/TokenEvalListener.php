<?php

class CRM_NYSS_Contact_TokenEvalListener {

    public static function evalToken($event) {

        $ctx_class = $event?->getTokenProcessor()?->getContextValues('class');

        // limit to events with 'CRM_Utils_Address' context class
        if (in_array('CRM_Utils_Address',$ctx_class ?? [])) {
            // NYSS 14192 - hide job title and employer, if option is not checked on mailing label export
            $include_title_org_check = CRM_Utils_Request::retrieve('include_title_org', 'Positive') === 1;
            $label_submit_check = CRM_Utils_Request::retrieve('_qf_Label_submit', 'Positive') === 1;
            $entry_url_check = str_contains(CRM_Utils_Request::retrieve('entryURL', 'String'), 'contact/search/advanced');
            if ((!$include_title_org_check) && $label_submit_check && $entry_url_check) {
                self::remove_title_and_org_from_label($event);
            }
        }
    }

    /** NYSS 14192
     * hide job title and employer from mailing label, if option is not checked on mailing label export */
    #[CRM_NYSS_Attribute_IssueRef('14192')]
    private static function remove_title_and_org_from_label(Civi\Token\Event\TokenValueEvent $event) {
        $row = $event?->getTokenProcessor()->getRow(0);
        $row->tokens('contact', 'job_title', "");
        $row->tokens('contact', 'current_employer', "");
    }

}