<?php
// NYSS #18424
return [
    [
        'name' => 'MailingComponent_nyss_unsubscribe',
        'entity' => 'MailingComponent',
        'cleanup' => 'unused',
        'update' => 'always',
        'params' => [
            'version' => 4,
            'values' => [
                'name' => 'NYSS Unsubscribe Message',
                'component_type' => 'Unsubscribe',
                'subject' => 'NY Senate Unsubscribe Confirmation',
                'body_html' => '<p>This is confirmation that you have unsubscribed from: {unsubscribe.group}. You can re-subscribe <a href="{action.resubscribeUrl}">here</a>.</p>',
                'body_text' => 'This is confirmation that you have unsubscribed from: {unsubscribe.group}. You can re-subscribe here: {action.resubscribeUrl}',
                'is_default' => TRUE,
                'is_active' => TRUE,
            ],
            'match' => ['component_type','name'],
        ],
    ],
];