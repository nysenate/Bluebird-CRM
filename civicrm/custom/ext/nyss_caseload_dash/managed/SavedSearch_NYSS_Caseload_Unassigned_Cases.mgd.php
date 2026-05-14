<?php
use CRM_NYSS_CaseloadDash_ExtensionUtil as E;

return [
  [
    'name' => 'SavedSearch_NYSS_Caseload_Unassigned_Cases',
    'entity' => 'SavedSearch',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'NYSS_Caseload_Unassigned_Cases',
        'label' => E::ts('NYSS Caseload - Unassigned Cases'),
        'api_entity' => 'Case',
        'api_params' => [
          'version' => 4,
            'checkPermissions' => FALSE,
          'select' => [
            'id',
            'subject',
            'status_id:label',
            'COUNT(Case_CaseContact_Contact_01_Contact_RelationshipCache_Case_01.near_contact_id) AS COUNT_Case_CaseContact_Contact_01_Contact_RelationshipCache_Case_01_near_contact_id',
            'start_date',
            'case_type_id:label',
            'GROUP_CONCAT(DISTINCT Case_CaseContact_Contact_01.display_name) AS GROUP_CONCAT_Case_CaseContact_Contact_01_display_name',
          ],
          'orderBy' => [],
          'where' => [
            [
              'OR',
              [
                ['status_id:name', '=', 'Unassigned'],
                [
                  'Case_CaseContact_Contact_01_Contact_RelationshipCache_Case_01.near_contact_id',
                  'IS EMPTY',
                ],
              ],
            ],
            ['is_deleted', '=', FALSE],
          ],
          'groupBy' => [
            'id',
            'status_id',
            'start_date',
            'case_type_id',
            'subject',
          ],
          'join' => [
            [
              'Contact AS Case_CaseContact_Contact_01',
              'LEFT',
              'CaseContact',
              [
                'id',
                '=',
                'Case_CaseContact_Contact_01.case_id',
              ],
            ],
            [
              'Case AS Case_CaseContact_Contact_01_Contact_RelationshipCache_Case_01',
              'LEFT',
              'RelationshipCache',
              [
                'Case_CaseContact_Contact_01.id',
                '=',
                'Case_CaseContact_Contact_01_Contact_RelationshipCache_Case_01.far_contact_id',
              ],
              [
                'Case_CaseContact_Contact_01_Contact_RelationshipCache_Case_01.near_relation:name',
                '=',
                '"Case Manager"',
              ],
              [
                'Case_CaseContact_Contact_01_Contact_RelationshipCache_Case_01.is_current',
                '=',
                TRUE,
              ],
              [
                'Case_CaseContact_Contact_01_Contact_RelationshipCache_Case_01.id',
                '=',
                'id',
              ],
            ],
          ],
          'having' => [],
        ],
      ],
      'match' => ['name'],
    ],
  ],
  [
    'name' => 'SavedSearch_NYSS_Caseload_Unassigned_Cases_SearchDisplay_Unassigned_Cases_Table_Details',
    'entity' => 'SearchDisplay',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'NYSS_Caseload_Unassigned_Cases_Table_Details',
        'label' => E::ts('NYSS Caseload - Unassigned Cases Table Details'),
        'saved_search_id.name' => 'NYSS_Caseload_Unassigned_Cases',
        'type' => 'table',
        'settings' => [
          'description' => E::ts(NULL),
          'sort' => [
            ['start_date', 'ASC'],
          ],
          'limit' => 50,
          'pager' => [],
          'placeholder' => 5,
          'columns' => [
            [
              'type' => 'html',
              'key' => 'id',
              'label' => E::ts('Case'),
              'sortable' => TRUE,
              'rewrite' => '<a href="/civicrm/contact/view/case?id=[id]&action=view&reset=1" target="_blank">#[id] &mdash; [subject]</a>',
              'title' => E::ts('Manage Case'),
              'cssRules' => [
                ['bg-warning', 'segment_Urgent', '=', 'Urgent'],
              ],
            ],
            [
              'type' => 'field',
              'key' => 'start_date',
              'label' => E::ts('Case Start Date'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'GROUP_CONCAT_Case_CaseContact_Contact_01_display_name',
              'label' => E::ts('Constituents'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'case_type_id:label',
              'label' => E::ts('Case Type'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'status_id:label',
              'label' => E::ts('Case Status'),
              'sortable' => TRUE,
              'cssRules' => [
                ['bg-warning', 'segment_Urgent', '=', 'Urgent'],
              ],
            ],
            [
              'text' => E::ts(''),
              'style' => 'default',
              'size' => 'btn-xs',
              'icon' => 'fa-bars',
              'links' => [
                [
                  'entity' => 'Case',
                  'action' => 'view',
                  'join' => '',
                  'target' => '',
                  'icon' => 'fa-external-link',
                  'text' => E::ts('View Case'),
                  'style' => 'default',
                  'path' => '',
                  'task' => '',
                  'conditions' => [],
                ],
                [
                  'task' => 'case.addRole',
                  'entity' => 'Case',
                  'join' => '',
                  'target' => 'crm-popup',
                  'icon' => 'fa-user-plus',
                  'text' => E::ts('Case - Assign Role'),
                  'style' => 'default',
                  'path' => '',
                  'action' => '',
                  'conditions' => [],
                ],
                [
                  'task' => 'delete',
                  'entity' => 'Case',
                  'join' => '',
                  'target' => 'crm-popup',
                  'icon' => 'fa-trash',
                  'text' => E::ts('Delete Cases'),
                  'style' => 'danger',
                  'path' => '',
                  'action' => '',
                  'conditions' => [],
                ],
              ],
              'type' => 'menu',
              'alignment' => 'text-right',
            ],
          ],
          'actions' => TRUE,
          'classes' => ['table', 'table-striped'],
          'actions_display_mode' => 'menu',
          'cssRules' => [
            ['bg-warning', 'segment_Urgent', '=', 'Urgent'],
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
    'name' => 'SavedSearch_NYSS_Caseload_Unassigned_Cases_SearchDisplay_NyssUnassignedCases',
    'entity' => 'SearchDisplay',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'NyssCaseloadUnassignedCases',
        'label' => E::ts('NYSS Caseload - Unassigned Cases DB View'),
        'saved_search_id.name' => 'NYSS_Caseload_Unassigned_Cases',
        'type' => 'entity',
        'settings' => [
          'sort' => [
            [
              'status_id:label',
              'ASC',
            ],
          ],
          'columns' => [
            [
              'type' => 'field',
              'key' => 'id',
              'label' => E::ts('Case ID'),
              'spec' => [
                'name' => 'id',
                'data_type' => 'Integer',
                'suffixes' => NULL,
                'options' => FALSE,
                'serialize' => NULL,
                'original_field_name' => 'id',
                'original_field_entity' => 'Case',
                'entity_reference' => [
                  'entity' => 'Case',
                ],
                'input_type' => 'EntityRef',
              ],
            ],
            [
              'type' => 'field',
              'key' => 'status_id:label',
              'label' => E::ts('Case Status'),
              'spec' => [
                'name' => 'status_id',
                'data_type' => 'Integer',
                'suffixes' => ['id', 'label'],
                'options' => TRUE,
                'serialize' => NULL,
                'original_field_name' => 'status_id',
                'original_field_entity' => 'Case',
                'input_type' => 'Select',
                'entity_reference' => NULL,
              ],
            ],
            [
              'type' => 'field',
              'key' => 'COUNT_Case_CaseContact_Contact_01_Contact_RelationshipCache_Case_01_near_contact_id',
              'label' => E::ts('Case Manager Count'),
              'spec' => [
                'name' => 'COUNT_Case_CaseContact_Contact_01_Contact_a6c385c12bd86d07',
                'data_type' => 'Integer',
                'suffixes' => NULL,
                'options' => FALSE,
                'serialize' => NULL,
                'original_field_name' => 'near_contact_id',
                'original_field_entity' => 'RelationshipCache',
                'input_type' => 'Number',
              ],
            ],
          ],
          'description' => E::ts('Unassigned cases are cases that either have no case manager role or have the status set to "Unassigned"'),
          'data_mode' => 'view',
        ],
      ],
      'match' => [
        'saved_search_id',
        'name',
      ],
    ],
  ],
];
