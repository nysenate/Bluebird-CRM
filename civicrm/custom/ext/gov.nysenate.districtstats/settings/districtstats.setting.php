<?php

return [
  'districtstats_tag_report_route' => [
    'name' => 'districtstats_tag_report_route',
    'type' => 'String',
    'default' => 'civicrm/report/constituent_tag',
    'html_type' => 'text',
    'title' => ts('Constituent Tag Report Route'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => ts('The route / partial URL (typically civicrm/...) of the report that displays constituents tagged with a given tag. This is relevant to Issue Codes and Keywords'),
    'settings_pages' => ['districtstats' => ['weight' => 10]],
  ]
];