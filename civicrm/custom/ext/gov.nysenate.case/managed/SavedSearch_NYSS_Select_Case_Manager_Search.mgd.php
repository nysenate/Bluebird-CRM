<?php
use CRM_NYSS_Case_ExtensionUtil as E;

// NYSS #17214
return [
  [
    'name' => 'SavedSearch_NYSS_Select_Case_Manager_Search',
    'entity' => 'SavedSearch',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'NYSS_Select_Case_Manager_Search',
        'label' => E::ts('NYSS Select Case Manager Search'),
        'api_entity' => 'Contact',
        'api_params' => [
          'version' => 4,
          'select' => ['id', 'sort_name'],
          'orderBy' => [],
          'where' => [],
          'groupBy' => [],
          'join' => [
            [
              'Group AS Contact_GroupContact_Group_01',
              'INNER',
              'GroupContact',
              [
                'id',
                '=',
                'Contact_GroupContact_Group_01.contact_id',
              ],
              [
                'Contact_GroupContact_Group_01.title',
                '=',
                '"Office Staff"',
              ],
            ],
          ],
          'having' => [],
        ],
      ],
      'match' => ['name'],
    ],
  ],
];
