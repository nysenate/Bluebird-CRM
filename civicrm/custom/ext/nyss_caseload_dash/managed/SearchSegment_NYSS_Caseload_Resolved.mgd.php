<?php
use CRM_NYSS_CaseloadDash_ExtensionUtil as E;

return [
  [
    'name' => 'SearchSegment_NYSS_Caseload_Resolved',
    'entity' => 'SearchSegment',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'NYSS_Caseload_Resolved',
        'label' => E::ts('Case Is Resolved'),
        'description' => E::ts('Indicates if the case is resolved. Cases are considered resolved when status is "Resolved".'),
        'entity_name' => 'Case',
        'items' => [
          [
            'label' => E::ts('1'),
            'when' => [
              ['status_id:name', '=', 'Closed'],
            ],
          ],
        ],
      ],
      'match' => ['name'],
    ],
  ],
];
