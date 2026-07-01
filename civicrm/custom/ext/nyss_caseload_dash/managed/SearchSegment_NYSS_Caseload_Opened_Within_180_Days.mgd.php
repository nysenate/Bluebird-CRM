<?php
use CRM_NYSS_CaseloadDash_ExtensionUtil as E;

return [
  [
    'name' => 'SearchSegment_NYSS_Caseload_Opened_Within_180_Days',
    'entity' => 'SearchSegment',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'NYSS_Caseload_Opened_Within_180_Days',
        'label' => E::ts('Cases Opened Within 180 Days'),
        'description' => E::ts('Indicates when a case has been open within 180 days based on its start date'),
        'entity_name' => 'Case',
        'items' => [
          [
            'label' => E::ts('1'),
            'when' => [
              ['start_date', '>=', 'now - 180 day'],
            ],
          ],
        ],
      ],
      'match' => ['name'],
    ],
  ],
];
