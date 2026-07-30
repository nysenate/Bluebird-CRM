<?php

use Civi\NYSS\Mail\Listener\NyssFlexmailListener;

class CRM_NYSS_Mail_HookAlterMailParamsListener
{
    public static function alterMailParamsLate($event) {

        $has_job = $event->params[NyssFlexmailListener::$PARAM_JOB_INFO]['job_id'] ?? FALSE;
        $has_list_unsubscribe = $event->params['headers']['List-Unsubscribe'] ?? FALSE;

        // NYSS #18424 - Override Public One-Click Unsubscribe Link
        if ($has_job && $has_list_unsubscribe) {
            Civi::log()->debug('Orig List-Unsubscribe', [
                'List-Unsubscribe' => $event->params['headers']['List-Unsubscribe'],
            ]);
            $oneclick_unsubscribe_vals = self::get_oneclick_unsubscribe_values($event->params);
            if (sizeof($oneclick_unsubscribe_vals)) {
                $event->params['headers']['List-Unsubscribe'] = join(', ', $oneclick_unsubscribe_vals);
            } else {
                // We've made an honest attempt to generate a one-click unsubscribe link. But, in the off chance
                // that it isn't successfully generated, then bail on the List-Unsubscribe-Post to avoid broken
                // unsubscribe function on the mail recipient's end of things.
                unset($event->params['headers']['List-Unsubscribe-Post']);
            }
        }

        // NYSS #18424 - avoid for big mailing jobs. This is only intended to execute for "one-shot" emails
        // like unsubscribe confirmations. Rewrite the resubscribe link in the unsubscribe confirmation
        // messages in html and text.
        if (!$has_job) {
            $bbcfg ??= get_bluebird_instance_config();
            foreach (['html', 'text'] as $key) {
                if (!empty($event->params[$key]) && str_contains($event->params[$key], 'civicrm/mailing/resubscribe')) {
                    $event->params[$key] = self::replace_resubscribe_urls($event->params[$key], $bbcfg, $key === 'html');
                }
            }
        }
    }

    /**
     * return a list of urls (there's really only one) for "one-click" unsubscribes / List-Unsubscribe header
     * @param $params
     * @return string[]|null really only one value right now
     */
    #[CRM_NYSS_Attribute_IssueRef(18424)]
    protected static function get_oneclick_unsubscribe_values($params) : array {

        $bbcfg = get_bluebird_instance_config();
        $queue_id = $params[NyssFlexmailListener::$PARAM_EVENT_Q_ID] ?? NULL;
        $task_hash = $params[NyssFlexmailListener::$PARAM_TASK_HASH] ?? NULL;
        $return_values = [];

        // In the event that the above params are not set, here are some possible ways to avoid a database lookup.
        // Parse the default/existing List-Unsubscribe header
        //} else if (strlen($params['headers']['List-Unsubscribe'] ?? '')) {
        // Parse the X-CiviMail-Bounce header which is based on the same information.
        //} else if (strlen($params['X-CiviMail-Bounce'])) {

        if ($queue_id && $task_hash) {
            // Expected Squid Proxy / Public URL pattern = ^https?://pubfiles\.nysenate\.gov/(crm[^/]*)/([^/]*)/subscription/(manage|view|(un|re)subscribe)/([0-9]+)/([0-9a-f_]+)$
            $url = "{$bbcfg['public.url.base']}/{$bbcfg['envname']}/{$bbcfg['shortname']}/subscription/unsubscribe/" . $queue_id . '/' . $task_hash;
            $return_values[] = '<' . $url . '>';
            Civi::log()->debug('get_oneclick_unsubscribe_values', [
                '$return_values' => $return_values,
            ]);
        } else {
            // I don't expect to land here, but if we do then issue a log warning so that we know that other
            // accommodations need to be made -- like a database query or parsing another header for the needed data points.
            Civi::log()->warning('get_oneclick_unsubscribe_values: missing required params, skipping List-Unsubscribe header', [
                'queue_id' => $queue_id, 'task_hash' => $task_hash,
            ]);
        }
        return $return_values;
    }

    /**
     * Rewrite the resubscribe URL in an email body to the public proxy URL.
     * Handles both HTML (&amp;-encoded) and plain-text (& literal) variants.
     */
    #[CRM_NYSS_Attribute_IssueRef(18424)]
    protected static function replace_resubscribe_urls(string $body, array $bbcfg, bool $isHtml): string {
        return preg_replace_callback(
            '/https?:\/\/[^\s"\'<>]+civicrm\/mailing\/resubscribe[^\s"\'<>]*/i',
            function (array $m) use ($bbcfg, $isHtml): string {
                $decoded = html_entity_decode($m[0], ENT_HTML5 | ENT_QUOTES);
                parse_str(parse_url($decoded, PHP_URL_QUERY) ?? '', $q);
                if (empty($q['qid']) || empty($q['h'])) {
                    return $m[0];
                }
                return "{$bbcfg['public.url.base']}/{$bbcfg['envname']}/{$bbcfg['shortname']}/subscription/resubscribe/"
                       . $q['qid'] . '/' . $q['h'];
            },
            $body
        );
    }
}