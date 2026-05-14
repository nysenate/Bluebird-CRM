<?php
use CRM_NYSS_CaseloadDash_ExtensionUtil as E;

return [
  [
    'name' => 'SavedSearch_NYSS_Caseload_Overview_Totals',
    'entity' => 'SavedSearch',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'NYSS_Caseload_Overview_Totals',
        'label' => E::ts('NYSS Caseload - Overview Totals'),
        'api_entity' => 'SK_NyssCaseloadDetailsStats',
        'api_params' => [
          'version' => 4,
          'select' => [
            'COUNT(segment_NYSS_Caseload_Open) AS COUNT_segment_NYSS_Caseload_Open',
            'COUNT(segment_NYSS_Caseload_Resolved) AS COUNT_segment_NYSS_Caseload_Resolved',
            'COUNT(segment_NYSS_Caseload_Urgent) AS COUNT_segment_NYSS_Caseload_Urgent',
            'COUNT(segment_NYSS_Caseload_Newer_Than_7_Days) AS COUNT_segment_NYSS_Caseload_Newer_Than_7_Days',
            'COUNT(segment_NYSS_Caseload_Older_Than_7_Days) AS COUNT_segment_NYSS_Caseload_Older_Than_7_Days',
            'COUNT(segment_NYSS_Caseload_7_To_30_Days) AS COUNT_segment_NYSS_Caseload_7_To_30_Days',
            'COUNT(segment_NYSS_Caseload_Older_Than_30_Days) AS COUNT_segment_NYSS_Caseload_Older_Than_30_Days',
            'COUNT(segment_NYSS_Caseload_Resolved_Last_7_Days) AS COUNT_segment_NYSS_Caseload_Resolved_Last_7_Days',
            'AVG(DATEDIFF_end_date_start_date) AS AVG_DATEDIFF_end_date_start_date',
            'COUNT(Case_SK_NyssCaseloadUnassignedCases_id_01_c7b0c6b4793bbbee:label) AS COUNT_Case_SK_NyssCaseloadUnassignedCases_id_01_c7b0c6b4793bbbee_label',
          ],
          'orderBy' => [],
          'where' => [],
          'groupBy' => [],
          'join' => [],
          'having' => [],
        ],
      ],
      'match' => ['name'],
    ],
  ],
  [
    'name' => 'SavedSearch_NYSS_Caseload_Overview_Totals_SearchDisplay_NYSS_Caseload_Average_Days_to_Resolution',
    'entity' => 'SearchDisplay',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'NYSS_Caseload_Average_Days_to_Resolution',
        'label' => E::ts('NYSS Caseload - Average Days to Resolution'),
        'saved_search_id.name' => 'NYSS_Caseload_Overview_Totals',
        'type' => 'table',
        'settings' => [
          'description' => E::ts(''),
          'sort' => [],
          'limit' => 0,
          'pager' => FALSE,
          'placeholder' => 5,
          'columns' => [
            [
              'type' => 'field',
              'key' => 'AVG_DATEDIFF_end_date_start_date',
              'label' => E::ts('Avg. Days to Resolution'),
              'sortable' => FALSE,
              'alignment' => 'text-center',
              'cssRules' => [
                ['font-bold'],
              ],
              'rewrite' => '{$AVG_DATEDIFF_end_date_start_date|round}',
            ],
          ],
          'actions' => FALSE,
          'classes' => [
            'nyss-caseload-dash',
          ],
        ],
      ],
      'match' => [
        'saved_search_id',
        'name',
      ],
    ],
  ],
  [
    'name' => 'SavedSearch_NYSS_Caseload_Overview_Totals_SearchDisplay_NYSS_Caseload_Total_7_To_30_Days',
    'entity' => 'SearchDisplay',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'NYSS_Caseload_Total_7_To_30_Days',
        'label' => E::ts('NYSS Caseload - Total Between 7 and 30 Days'),
        'saved_search_id.name' => 'NYSS_Caseload_Overview_Totals',
        'type' => 'table',
        'settings' => [
          'description' => E::ts(''),
          'sort' => [],
          'limit' => 0,
          'pager' => FALSE,
          'placeholder' => 5,
          'columns' => [
            [
              'type' => 'html',
              'key' => 'COUNT_segment_NYSS_Caseload_7_To_30_Days',
              'label' => E::ts('Open 7 - 30 Days'),
              'sortable' => FALSE,
              'alignment' => 'text-center',
              'cssRules' => [
                ['font-bold'],
              ],
              'rewrite' => '<a href="/civicrm/nyss/caseload/dashboard#/?segment_NYSS_Caseload_7_To_30_Days=yes">[COUNT_segment_NYSS_Caseload_7_To_30_Days]</a>',
            ],
          ],
          'actions' => FALSE,
          'classes' => [
            'nyss-caseload-dash',
          ],
        ],
      ],
      'match' => [
        'saved_search_id',
        'name',
      ],
    ],
  ],
  [
    'name' => 'SavedSearch_NYSS_Caseload_Overview_Totals_SearchDisplay_NYSS_Caseload_Total_Newer_Than_7_Days',
    'entity' => 'SearchDisplay',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'NYSS_Caseload_Total_Newer_Than_7_Days',
        'label' => E::ts('NYSS Caseload - Total Newer Than 7 Days'),
        'saved_search_id.name' => 'NYSS_Caseload_Overview_Totals',
        'type' => 'table',
        'settings' => [
          'description' => E::ts(''),
          'sort' => [],
          'limit' => 0,
          'pager' => FALSE,
          'placeholder' => 5,
          'columns' => [
            [
              'type' => 'html',
              'key' => 'COUNT_segment_NYSS_Caseload_Newer_Than_7_Days',
              'label' => E::ts('Open < 7 Days'),
              'sortable' => FALSE,
              'alignment' => 'text-center',
              'cssRules' => [
                ['font-bold'],
              ],
              'rewrite' => '<a href="/civicrm/nyss/caseload/dashboard#/?segment_NYSS_Caseload_Newer_Than_7_Days=yes">[COUNT_segment_NYSS_Caseload_Newer_Than_7_Days]</a>',
            ],
          ],
          'actions' => FALSE,
          'classes' => [
            'nyss-caseload-dash',
          ],
        ],
      ],
      'match' => [
        'saved_search_id',
        'name',
      ],
    ],
  ],
  [
    'name' => 'SavedSearch_NYSS_Caseload_Overview_Totals_SearchDisplay_NYSS_Caseload_Total_Older_Than_30_Days',
    'entity' => 'SearchDisplay',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'NYSS_Caseload_Total_Older_Than_30_Days',
        'label' => E::ts('NYSS Caseload - Total Older Than 30 Days'),
        'saved_search_id.name' => 'NYSS_Caseload_Overview_Totals',
        'type' => 'table',
        'settings' => [
          'description' => E::ts(''),
          'sort' => [],
          'limit' => 0,
          'pager' => FALSE,
          'placeholder' => 5,
          'columns' => [
            [
              'type' => 'html',
              'key' => 'COUNT_segment_NYSS_Caseload_Older_Than_30_Days',
              'label' => E::ts('Open > 30 Days'),
              'sortable' => FALSE,
              'cssRules' => [
                ['font-bold'],
              ],
              'alignment' => 'text-center',
              'rewrite' => '<a href="/civicrm/nyss/caseload/dashboard#/?segment_NYSS_Caseload_Older_Than_30_Days=yes">[COUNT_segment_NYSS_Caseload_Older_Than_30_Days]</a>',
            ],
          ],
          'actions' => FALSE,
          'classes' => [
            'nyss-caseload-dash',
          ],
        ],
      ],
      'match' => [
        'saved_search_id',
        'name',
      ],
    ],
  ],
  [
    'name' => 'SavedSearch_NYSS_Caseload_Overview_Totals_SearchDisplay_NYSS_Caseload_Total_Older_Than_7_Days',
    'entity' => 'SearchDisplay',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'NYSS_Caseload_Total_Older_Than_7_Days',
        'label' => E::ts('NYSS Caseload - Total Older Than 7 Days'),
        'saved_search_id.name' => 'NYSS_Caseload_Overview_Totals',
        'type' => 'table',
        'settings' => [
          'description' => E::ts(''),
          'sort' => [],
          'limit' => 0,
          'pager' => FALSE,
          'placeholder' => 5,
          'columns' => [
            [
              'type' => 'html',
              'key' => 'COUNT_segment_NYSS_Caseload_Older_Than_7_Days',
              'label' => E::ts('Open > 7 Days'),
              'sortable' => FALSE,
              'alignment' => 'text-center',
              'cssRules' => [
                ['font-bold'],
              ],
              'rewrite' => '<a href="/civicrm/nyss/caseload/dashboard#/?segment_NYSS_Caseload_Older_Than_7_Days=yes">[COUNT_segment_NYSS_Caseload_Older_Than_7_Days]</a>',
            ],
          ],
          'actions' => FALSE,
          'classes' => [
            'nyss-caseload-dash',
          ],
        ],
      ],
      'match' => [
        'saved_search_id',
        'name',
      ],
    ],
  ],
  [
    'name' => 'SavedSearch_NYSS_Caseload_Overview_Totals_SearchDisplay_NYSS_Caseload_Total_Open',
    'entity' => 'SearchDisplay',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'NYSS_Caseload_Total_Open',
        'label' => E::ts('NYSS Caseload - Total Open'),
        'saved_search_id.name' => 'NYSS_Caseload_Overview_Totals',
        'type' => 'table',
        'settings' => [
          'description' => E::ts(''),
          'sort' => [],
          'limit' => 0,
          'pager' => FALSE,
          'placeholder' => 5,
          'columns' => [
            [
              'type' => 'html',
              'key' => 'COUNT_segment_NYSS_Caseload_Open',
              'label' => E::ts('Total Open'),
              'sortable' => FALSE,
              'alignment' => 'text-center',
              'cssRules' => [
                ['font-bold'],
              ],
              'rewrite' => '<a href="/civicrm/nyss/caseload/dashboard#/?segment_NYSS_Caseload_Open=yes">[COUNT_segment_NYSS_Caseload_Open]</a>',
            ],
          ],
          'actions' => FALSE,
          'classes' => [
            'nyss-caseload-dash',
          ],
        ],
      ],
      'match' => [
        'saved_search_id',
        'name',
      ],
    ],
  ],
  [
    'name' => 'SavedSearch_NYSS_Caseload_Overview_Totals_SearchDisplay_NYSS_Caseload_Total_Resolved_Last_7_Days',
    'entity' => 'SearchDisplay',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'NYSS_Caseload_Total_Resolved_Last_7_Days',
        'label' => E::ts('NYSS Caseload - Total Resolved Last 7 Days'),
        'saved_search_id.name' => 'NYSS_Caseload_Overview_Totals',
        'type' => 'table',
        'settings' => [
          'description' => E::ts(''),
          'sort' => [],
          'limit' => 0,
          'pager' => FALSE,
          'placeholder' => 5,
          'columns' => [
            [
              'type' => 'html',
              'key' => 'COUNT_segment_NYSS_Caseload_Resolved_Last_7_Days',
              'label' => E::ts('Resolved Last 7 Days'),
              'sortable' => FALSE,
              'alignment' => 'text-center',
              'cssRules' => [
                ['font-bold'],
              ],
              'rewrite' => '<a href="/civicrm/nyss/caseload/dashboard#/?segment_NYSS_Caseload_Resolved_Last_7_Days=yes">[COUNT_segment_NYSS_Caseload_Resolved_Last_7_Days]</a>',
            ],
          ],
          'actions' => FALSE,
          'classes' => [
            'nyss-caseload-dash',
          ],
        ],
      ],
      'match' => [
        'saved_search_id',
        'name',
      ],
    ],
  ],
  [
    'name' => 'SavedSearch_NYSS_Caseload_Overview_Totals_SearchDisplay_NYSS_Caseload_Total_Unassigned',
    'entity' => 'SearchDisplay',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'NYSS_Caseload_Total_Unassigned',
        'label' => E::ts('NYSS Caseload - Total Unassigned'),
        'saved_search_id.name' => 'NYSS_Caseload_Overview_Totals',
        'type' => 'table',
        'settings' => [
          'description' => E::ts(''),
          'sort' => [],
          'limit' => 0,
          'pager' => FALSE,
          'placeholder' => 5,
          'columns' => [
            [
              'type' => 'html',
              'key' => 'COUNT_Case_SK_NyssCaseloadUnassignedCases_id_01_c7b0c6b4793bbbee_label',
              'label' => E::ts('Total Unassigned'),
              'sortable' => FALSE,
              'alignment' => 'text-center',
              'cssRules' => [
                ['font-bold'],
              ],
              'rewrite' => '<a href="/civicrm/nyss/caseload/dashboard#/?segment_NYSS_Caseload_Unassigned=yes">[COUNT_Case_SK_NyssCaseloadUnassignedCases_id_01_c7b0c6b4793bbbee_label]</a>',
            ],
          ],
          'actions' => FALSE,
          'classes' => [
            'nyss-caseload-dash',
          ],
        ],
      ],
      'match' => [
        'saved_search_id',
        'name',
      ],
    ],
  ],
  [
    'name' => 'SavedSearch_NYSS_Caseload_Overview_Totals_SearchDisplay_NYSS_Caseload_Total_Urgent',
    'entity' => 'SearchDisplay',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'NYSS_Caseload_Total_Urgent',
        'label' => E::ts('NYSS Caseload - Total Urgent'),
        'saved_search_id.name' => 'NYSS_Caseload_Overview_Totals',
        'type' => 'table',
        'settings' => [
          'description' => E::ts(''),
          'sort' => [],
          'limit' => 0,
          'pager' => FALSE,
          'placeholder' => 5,
          'columns' => [
            [
              'type' => 'html',
              'key' => 'COUNT_segment_NYSS_Caseload_Urgent',
              'label' => E::ts('Total Urgent'),
              'sortable' => FALSE,
              'alignment' => 'text-center',
              'cssRules' => [
                ['font-bold'],
              ],
              'rewrite' => '<a href="/civicrm/nyss/caseload/dashboard#/?segment_NYSS_Caseload_Urgent=yes">[COUNT_segment_NYSS_Caseload_Urgent]</a>',
            ],
          ],
          'actions' => FALSE,
          'classes' => [
            'nyss-caseload-dash',
          ],
        ],
      ],
      'match' => [
        'saved_search_id',
        'name',
      ],
    ],
  ],
];
