<?php

return array(
  array(
    'name' => 'Website Inbox Messages',
    'entity' => 'Dashboard',
    'params' => array(
      'version' => 3,
      'domain_id' => 1,
      'name' => 'unmatchedMessages',
      'label' => 'Website Inbox Messages',
      'url' => 'civicrm/nyss/dashlet/webintegration/unmatched?reset=1',
      'permission' => 'access CiviCRM',
      'is_active' => 1,
      'is_reserved' => 1,
      'fullscreen_url' => 'civicrm/nyss/dashlet/webintegration/unmatched?reset=1&context=dashletFullscreen',
      'cache_minutes' => 60,
    ),
  ),
);
