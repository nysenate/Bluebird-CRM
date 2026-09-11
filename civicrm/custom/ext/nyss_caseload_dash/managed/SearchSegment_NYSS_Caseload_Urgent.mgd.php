<?php
use CRM_NYSS_CaseloadDash_ExtensionUtil as E;

return [
  [
    'name' => 'SearchSegment_NYSS_Caseload_Urgent',
    'entity' => 'SearchSegment',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'NYSS_Caseload_Urgent',
        'label' => E::ts('Case Is Urgent'),
        'description' => E::ts('Indicates if the case is urgent. Cases are considered urgent when the status is "Urgent".'),
        'entity_name' => 'Case',
        'items' => [
          [
            'label' => E::ts('1'),
            'when' => [
              ['status_id:name', '=', 'Urgent'],
            ],
          ],
        ],
      ],
      'match' => ['name'],
    ],
  ],
];
