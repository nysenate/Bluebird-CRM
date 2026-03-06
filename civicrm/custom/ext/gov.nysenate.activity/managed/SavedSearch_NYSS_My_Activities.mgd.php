<?php
use CRM_NYSS_Activity_ExtensionUtil as E;

return [
  [
    'name' => 'SavedSearch_NYSS_My_Activities',
    'entity' => 'SavedSearch',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'NYSS_My_Activities',
        'label' => E::ts('My Activities'),
        'api_entity' => 'Contact',
        'api_params' => [
          'version' => 4,
          'select' => [
            'id',
            'Contact_ActivityContact_Activity_01.subject',
            'Contact_ActivityContact_Activity_01_Activity_ActivityContact_Contact_01.display_name',
            'Contact_ActivityContact_Activity_01.status_id:label',
            'Contact_ActivityContact_Activity_01.activity_date_time',
            'Contact_ActivityContact_Activity_01.activity_type_id:label',
            'Contact_ActivityContact_Activity_01_Activity_CaseActivity_Case_01.id',
            'Contact_ActivityContact_Activity_01_Activity_CaseActivity_Case_01.subject',
          ],
          'orderBy' => [],
          'where' => [],
          'groupBy' => [],
          'join' => [
            [
              'Activity AS Contact_ActivityContact_Activity_01',
              'INNER',
              'ActivityContact',
              [
                'id',
                '=',
                'Contact_ActivityContact_Activity_01.contact_id',
              ],
              [
                'Contact_ActivityContact_Activity_01.record_type_id:name',
                'IN',
                [
                  'Activity Assignees',
                  'Activity Source',
                ],
              ],
              [
                'Contact_ActivityContact_Activity_01.contact_id',
                '=',
                'id',
              ],
              [
                'id',
                '=',
                '"user_contact_id"',
              ],
            ],
            [
              'Contact AS Contact_ActivityContact_Activity_01_Activity_ActivityContact_Contact_01',
              'LEFT',
              'ActivityContact',
              [
                'Contact_ActivityContact_Activity_01.id',
                '=',
                'Contact_ActivityContact_Activity_01_Activity_ActivityContact_Contact_01.activity_id',
              ],
              [
                'Contact_ActivityContact_Activity_01_Activity_ActivityContact_Contact_01.record_type_id:name',
                '=',
                '"Activity Targets"',
              ],
              [
                'Contact_ActivityContact_Activity_01_Activity_ActivityContact_Contact_01.activity_id',
                '=',
                'Contact_ActivityContact_Activity_01.id',
              ],
            ],
            [
              'Case AS Contact_ActivityContact_Activity_01_Activity_CaseActivity_Case_01',
              'LEFT',
              'CaseActivity',
              [
                'Contact_ActivityContact_Activity_01.id',
                '=',
                'Contact_ActivityContact_Activity_01_Activity_CaseActivity_Case_01.activity_id',
              ],
              [
                'Contact_ActivityContact_Activity_01.id',
                '=',
                'Contact_ActivityContact_Activity_01_Activity_CaseActivity_Case_01.activity_id',
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
    'name' => 'SavedSearch_NYSS_My_Activities_SearchDisplay_NYSS_My_Activities_Table_1',
    'entity' => 'SearchDisplay',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'NYSS_My_Activities_Table_1',
        'label' => E::ts('My Activities Table'),
        'saved_search_id.name' => 'NYSS_My_Activities',
        'type' => 'table',
        'settings' => [
          'description' => E::ts(NULL),
          'sort' => [
              [
                  'Contact_ActivityContact_Activity_01.activity_date_time',
                  'DESC',
              ],
          ],
          'limit' => 25,
          'pager' => [],
          'placeholder' => 5,
          'columns' => [
            [
              'type' => 'field',
              'key' => 'Contact_ActivityContact_Activity_01.activity_type_id:label',
              'dataType' => 'Integer',
              'label' => E::ts('Activity Type'),
              'sortable' => TRUE,
              'icons' => [
                [
                  'field' => 'contact_type:icon',
                  'side' => 'left',
                ],
              ],
            ],
            [
              'type' => 'field',
              'key' => 'Contact_ActivityContact_Activity_01.subject',
              'dataType' => 'String',
              'label' => E::ts('Subject'),
              'sortable' => TRUE,
              'link' => [
                'path' => '',
                'entity' => 'Activity',
                'action' => 'view',
                'join' => 'Contact_ActivityContact_Activity_01',
                'target' => 'crm-popup',
                'task' => '',
              ],
              'title' => E::ts('View Contact Activities'),
            ],
            [
              'type' => 'field',
              'key' => 'Contact_ActivityContact_Activity_01_Activity_ActivityContact_Contact_01.display_name',
              'dataType' => 'String',
              'label' => E::ts('With Constituents'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'Contact_ActivityContact_Activity_01.status_id:label',
              'dataType' => 'Integer',
              'label' => E::ts('Status'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'Contact_ActivityContact_Activity_01.activity_date_time',
              'dataType' => 'Timestamp',
              'label' => E::ts('Activity Date'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'html',
              'key' => 'Contact_ActivityContact_Activity_01_Activity_CaseActivity_Case_01.id',
              'dataType' => 'Integer',
              'label' => E::ts('Case'),
              'sortable' => TRUE,
              'rewrite' => '{if \'[Contact_ActivityContact_Activity_01_Activity_CaseActivity_Case_01.id]\'}<a href="/civicrm/contact/view/case?id=[Contact_ActivityContact_Activity_01_Activity_CaseActivity_Case_01.id]&action=view&reset=1" target="_blank">Case #[Contact_ActivityContact_Activity_01_Activity_CaseActivity_Case_01.id] - [Contact_ActivityContact_Activity_01_Activity_CaseActivity_Case_01.subject]</a>{/if}',
            ],
            [
              'links' => [
                [
                  'entity' => 'Activity',
                  'action' => 'view',
                  'join' => 'Contact_ActivityContact_Activity_01',
                  'target' => 'crm-popup',
                  'icon' => 'fa-external-link',
                  'text' => E::ts('View Activity'),
                  'style' => 'default',
                  'path' => '',
                  'task' => '',
                  'conditions' => [],
                ],
              ],
              'type' => 'links',
              'alignment' => 'text-right',
            ],
          ],
          'actions' => ['download'],
          'classes' => ['table', 'table-striped'],
          'actions_display_mode' => 'menu',
          'button' => NULL,
        ],
      ],
      'match' => [
        'saved_search_id',
        'name',
      ],
    ],
  ],
];
