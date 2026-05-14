<?php
use CRM_NYSS_CaseloadDash_ExtensionUtil as E;

return [
  [
    'name' => 'SearchSegment_NYSS_Caseload_Open',
    'entity' => 'SearchSegment',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'NYSS_Caseload_Open',
        'label' => E::ts('Case Is Open'),
        'description' => E::ts('Indicates if the case is open. Cases are considered open when status is not "Resolved".'),
        'entity_name' => 'Case',
        'items' => [
          [
            'label' => E::ts('1'),
            'when' => [
              [
                'status_id:name',
                'IN',
                [
                  'Open',
                  'Urgent',
                  'Not Started',
                  'Assigned',
                  'Unassigned',
                ],
              ],
            ],
          ],
        ],
      ],
      'match' => ['name'],
    ],
  ],
];
