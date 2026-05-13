<?php
use CRM_NYSS_Mail_ExtensionUtil as E;
return [
    'nyssUnsubLinkTimeout' => [
        'name' => 'nyssUnsubLinkTimeout',
        'type' => 'Integer',
        'html_type' => 'text',
        'default' => 1440,
        'title' => ts('NYSS Unsubscribe Link Timeout (Hours)'),
        'is_domain' => 1,
        'is_contact' => 0,
        'help_text' => ts('Checksum timeout value added to custom NYSS unsubscribe links.'),
        'settings_pages' => ['mail' => ['section' => 'reply', 'weight' => 100]],
    ],
];