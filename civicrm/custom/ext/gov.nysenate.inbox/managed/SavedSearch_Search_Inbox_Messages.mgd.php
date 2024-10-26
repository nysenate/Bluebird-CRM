<?php

  use CRM_NYSS_Inbox_ExtensionUtil as E;

  return [
    [
      'name' => 'SavedSearch_Search_Inbox_Messages',
      'entity' => 'SavedSearch',
      'cleanup' => 'always',
      'update' => 'unmodified',
      'params' => [
        'version' => 4,
        'values' => [
          'name' => 'Search_Inbox_Messages',
          'label' => 'Search Inbox Messages',
          'form_values' => NULL,
          'mapping_id' => NULL,
          'search_custom_id' => NULL,
          'api_entity' => 'InboxMessages',
          'api_params' => [
            'version' => 4,
            'select' => [
              'id',
              'sender_name',
              'sender_email',
              'subject',
              'forwarder',
              'status',
              'InboxMessages_InboxMessagesMatched_row_id_01_InboxMessagesMatched_Contact_matched_id_01.sort_name',
              'updated_date',
              'email_date',
            ],
            'orderBy' => [],
            'where' => [],
            'groupBy' => [],
            'join' => [
              [
                'InboxMessagesMatched AS InboxMessages_InboxMessagesMatched_row_id_01',
                'LEFT',
                [
                  'id',
                  '=',
                  'InboxMessages_InboxMessagesMatched_row_id_01.row_id',
                ],
              ],
              [
                'Contact AS InboxMessages_InboxMessagesMatched_row_id_01_InboxMessagesMatched_Contact_matched_id_01',
                'LEFT',
                [
                  'InboxMessages_InboxMessagesMatched_row_id_01.matched_id',
                  '=',
                  'InboxMessages_InboxMessagesMatched_row_id_01_InboxMessagesMatched_Contact_matched_id_01.id',
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
      'name' => 'SavedSearch_Search_Inbox_Messages_SearchDisplay_Search_Inbox_Messages_Table_1',
      'entity' => 'SearchDisplay',
      'cleanup' => 'always',
      'update' => 'unmodified',
      'params' => [
        'version' => 4,
        'values' => [
          'name' => 'Search_Inbox_Messages_Table_1',
          'label' => 'Search Inbox Messages Table',
          'saved_search_id.name' => 'Search_Inbox_Messages',
          'type' => 'table',
          'settings' => [
            'description' => NULL,
            'sort' => [
              [
                'email_date',
                'DESC',
              ],
            ],
            'limit' => 50,
            'pager' => [
              'show_count' => TRUE,
              'expose_limit' => TRUE,
            ],
            'placeholder' => 5,
            'columns' => [
              [
                'type' => 'field',
                'key' => 'sender_name',
                'dataType' => 'String',
                'label' => 'Sender Name',
                'sortable' => TRUE,
              ],
              [
                'type' => 'field',
                'key' => 'sender_email',
                'dataType' => 'String',
                'label' => 'Sender Email',
                'sortable' => TRUE,
              ],
              [
                'type' => 'field',
                'key' => 'InboxMessages_InboxMessagesMatched_row_id_01_InboxMessagesMatched_Contact_matched_id_01.sort_name',
                'dataType' => 'String',
                'label' => 'Matched To',
                'sortable' => TRUE,
                'link' => [
                  'path' => '',
                  'entity' => 'Contact',
                  'action' => 'view',
                  'join' => 'InboxMessages_InboxMessagesMatched_row_id_01_InboxMessagesMatched_Contact_matched_id_01',
                  'target' => '_blank',
                ],
                'title' => 'View Matched Contact',
              ],
              [
                'type' => 'field',
                'key' => 'subject',
                'dataType' => 'String',
                'label' => 'Subject',
                'sortable' => TRUE,
              ],
              [
                'type' => 'field',
                'key' => 'updated_date',
                'dataType' => 'Timestamp',
                'label' => 'Last Edited',
                'sortable' => TRUE,
              ],
              [
                'type' => 'field',
                'key' => 'email_date',
                'dataType' => 'Timestamp',
                'label' => 'Date Sent',
                'sortable' => TRUE,
              ],
              [
                'type' => 'field',
                'key' => 'status',
                'dataType' => 'Integer',
                'label' => 'Status',
                'sortable' => TRUE,
                'rewrite' => '{if "[status]" eq 0}Unmatched
{elseif "[status]" eq 1}Matched
{elseif "[status]" eq 7}Cleared
{elseif "[status]" eq 9}Deleted
{elseif "[status]" eq 99}Unprocessed
{/if}',
              ],
              [
                'type' => 'field',
                'key' => 'forwarder',
                'dataType' => 'String',
                'label' => 'Forwarded By',
                'sortable' => TRUE,
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