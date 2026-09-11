<?php
use CRM_NYSS_CaseloadDash_ExtensionUtil as E;

return [
  [
    'name' => 'SearchSegment_NYSS_Caseload_Resolved_Last_7_Days',
    'entity' => 'SearchSegment',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'NYSS_Caseload_Resolved_Last_7_Days',
        'label' => E::ts('Cases Resolved Last 7 Days'),
        'description' => E::ts('Indicates when a case has been resolved in the last 7 days based on its end date'),
        'entity_name' => 'Case',
        'items' => [
          [
            'label' => E::ts('1'),
            'when' => [
              ['status_id:name', '=', 'Closed'],
              ['end_date', '>=', 'now - 7 day'],
            ],
          ],
        ],
      ],
      'match' => ['name'],
    ],
  ],
];
