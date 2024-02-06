<?php

  use CRM_NYSS_Search_ExtensionUtil as E;

  return [
    [
      'name' => 'SavedSearch_Case_Activity_Tags',
      'entity' => 'SavedSearch',
      'cleanup' => 'always',
      'update' => 'unmodified',
      'params' => [
        'version' => 4,
        'values' => [
          'name' => 'Case_Activity_Tags',
          'label' => 'Case/Activity Tags',
          'form_values' => NULL,
          'mapping_id' => NULL,
          'search_custom_id' => NULL,
          'api_entity' => 'Contact',
          'api_params' => [
            'version' => 4,
            'select' => [
              'id',
              'sort_name',
              'Contact_CaseContact_Case_01.case_type_id:label',
              'Contact_CaseContact_Case_01.subject',
              'GROUP_CONCAT(DISTINCT Contact_CaseContact_Case_01_Case_EntityTag_Tag_01.name) AS GROUP_CONCAT_Contact_CaseContact_Case_01_Case_EntityTag_Tag_01_name',
              'GROUP_CONCAT(DISTINCT Contact_CaseContact_Case_01_Case_CaseActivity_Activity_01_Activity_EntityTag_Tag_01.name) AS GROUP_CONCAT_Contact_CaseContact_Case_01_Case_CaseActivity_Activity_01_Activity_EntityTag_Tag_01_name',
            ],
            'orderBy' => [],
            'where' => [
              [
                'is_deleted',
                '=',
                FALSE,
              ],
            ],
            'groupBy' => [
              'id',
              'Contact_CaseContact_Case_01.id',
            ],
            'join' => [
              [
                'Case AS Contact_CaseContact_Case_01',
                'INNER',
                'CaseContact',
                [
                  'id',
                  '=',
                  'Contact_CaseContact_Case_01.contact_id',
                ],
              ],
              [
                'Activity AS Contact_CaseContact_Case_01_Case_CaseActivity_Activity_01',
                'LEFT',
                'CaseActivity',
                [
                  'Contact_CaseContact_Case_01.id',
                  '=',
                  'Contact_CaseContact_Case_01_Case_CaseActivity_Activity_01.case_id',
                ],
              ],
              [
                'Tag AS Contact_CaseContact_Case_01_Case_EntityTag_Tag_01',
                'LEFT',
                'EntityTag',
                [
                  'Contact_CaseContact_Case_01.id',
                  '=',
                  'Contact_CaseContact_Case_01_Case_EntityTag_Tag_01.entity_id',
                ],
                [
                  'Contact_CaseContact_Case_01_Case_EntityTag_Tag_01.entity_table',
                  '=',
                  "'civicrm_case'",
                ],
              ],
              [
                'Tag AS Contact_CaseContact_Case_01_Case_CaseActivity_Activity_01_Activity_EntityTag_Tag_01',
                'LEFT',
                'EntityTag',
                [
                  'Contact_CaseContact_Case_01_Case_CaseActivity_Activity_01.id',
                  '=',
                  'Contact_CaseContact_Case_01_Case_CaseActivity_Activity_01_Activity_EntityTag_Tag_01.entity_id',
                ],
                [
                  'Contact_CaseContact_Case_01_Case_CaseActivity_Activity_01_Activity_EntityTag_Tag_01.entity_table',
                  '=',
                  "'civicrm_activity'",
                ],
              ],
            ],
            'having' => [],
          ],
          'expires_date' => NULL,
          'description' => NULL,
        ],
      ],
    ],
    [
      'name' => 'SavedSearch_Case_Activity_Tags_SearchDisplay_Case_Activity_Tags_Table_1',
      'entity' => 'SearchDisplay',
      'cleanup' => 'always',
      'update' => 'unmodified',
      'params' => [
        'version' => 4,
        'values' => [
          'name' => 'Case_Activity_Tags_Table_1',
          'label' => 'Case/Activity Tags Table 1',
          'saved_search_id.name' => 'Case_Activity_Tags',
          'type' => 'table',
          'settings' => [
            'description' => NULL,
            'sort' => [
              [
                'sort_name',
                'ASC',
              ],
            ],
            'limit' => 50,
            'pager' => [],
            'placeholder' => 5,
            'columns' => [
              [
                'type' => 'field',
                'key' => 'sort_name',
                'dataType' => 'String',
                'label' => 'Contact Name',
                'sortable' => TRUE,
                'link' => [
                  'path' => '',
                  'entity' => 'Contact',
                  'action' => 'view',
                  'join' => '',
                  'target' => '_blank',
                ],
                'title' => 'View Contact',
              ],
              [
                'type' => 'field',
                'key' => 'Contact_CaseContact_Case_01.case_type_id:label',
                'dataType' => 'Integer',
                'label' => 'Case Type',
                'sortable' => TRUE,
                'link' => [
                  'path' => 'civicrm/contact/view/case?reset=1&id=[Contact_CaseContact_Case_01.id]&cid=[id]&action=view&context=case',
                  'entity' => '',
                  'action' => '',
                  'join' => '',
                  'target' => '_blank',
                ],
              ],
              [
                'type' => 'field',
                'key' => 'Contact_CaseContact_Case_01.subject',
                'dataType' => 'String',
                'label' => 'Case Subject',
                'sortable' => TRUE,
              ],
              [
                'type' => 'field',
                'key' => 'GROUP_CONCAT_Contact_CaseContact_Case_01_Case_EntityTag_Tag_01_name',
                'dataType' => 'String',
                'label' => 'Case Tags',
                'sortable' => FALSE,
              ],
              [
                'type' => 'field',
                'key' => 'GROUP_CONCAT_Contact_CaseContact_Case_01_Case_CaseActivity_Activity_01_Activity_EntityTag_Tag_01_name',
                'dataType' => 'String',
                'label' => 'Case Activity Tags',
                'sortable' => FALSE,
              ],
            ],
            'actions' => FALSE,
            'classes' => [
              'table',
              'table-striped',
            ],
          ],
          'acl_bypass' => FALSE,
        ],
      ],
    ],
  ];