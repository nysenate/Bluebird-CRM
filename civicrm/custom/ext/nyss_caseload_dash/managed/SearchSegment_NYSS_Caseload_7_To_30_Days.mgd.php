<?php
use CRM_NYSS_CaseloadDash_ExtensionUtil as E;

return [
  [
    'name' => 'SearchSegment_NYSS_Caseload_7_To_30_Days',
    'entity' => 'SearchSegment',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'NYSS_Caseload_7_To_30_Days',
        'label' => E::ts('Cases Open 7 - 30 Days'),
        'description' => E::ts('Indicates when a case has been open between 7 and 30 days based on its start date'),
        'entity_name' => 'Case',
        'items' => [
          [
            'label' => E::ts('1'),
            'when' => [
              ['status_id:name', '!=', 'Closed'],
              ['start_date', '<', 'now - 7 day'],
              ['start_date', '>=', 'now - 30 day'],
            ],
          ],
        ],
      ],
      'match' => ['name'],
    ],
  ],
];
