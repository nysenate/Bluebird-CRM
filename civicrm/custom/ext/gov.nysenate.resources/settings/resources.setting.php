<?php

/*
 * Settings metadata file
 */
return [
  'resources_slack_url' => [
    'group_name' => 'domain',
    'group' => 'resources',
    'name' => 'resources_slack_url',
    'type' => 'String',
    'add' => '1.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'Full URL to Slack',
    'help_text' => '',
  ],
  'resources_slack_channel' => [
    'group_name' => 'domain',
    'group' => 'resources',
    'name' => 'resources_slack_channel',
    'type' => 'String',
    'add' => '1.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'Default channel name',
    'help_text' => '',
  ],
  'resources_slack_title' => [
    'group_name' => 'domain',
    'group' => 'resources',
    'name' => 'resources_slack_title',
    'type' => 'String',
    'add' => '1.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'Default title',
    'help_text' => '',
  ],
];
