<?php
use CRM_NYSS_CaseloadDash_ExtensionUtil as E;

return [
  [
    'name' => 'SearchSegment_NYSS_Caseload_Unassigned',
    'entity' => 'SearchSegment',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'NYSS_Caseload_Unassigned',
        'label' => E::ts('Case Is Unassigned'),
        'description' => E::ts('If a case exists in this entity (NYSS Caseload - Unassigned Cases DB View), then it is considered to be an unassigned case.'),
        'entity_name' => 'SK_NyssCaseloadUnassignedCases',
        'items' => [
          [
            'label' => E::ts('1'),
            'when' => [
              ['id', 'IS NOT EMPTY'],
            ],
          ],
        ],
      ],
      'match' => ['name'],
    ],
  ],
];
