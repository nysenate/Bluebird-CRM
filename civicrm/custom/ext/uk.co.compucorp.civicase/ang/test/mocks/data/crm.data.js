(function (CRM) {
  var module = angular.module('civicase.data');
  CRM.config = {
    enableComponents: [
      'CiviMail',
      'CiviReport',
      'CiviCase'
    ],
    user_contact_id: 203,
    isFrontend: false,
    wysisygScriptLocation: '/sites/all/modules/civicrm/js/wysiwyg/crm.ckeditor.js',
    CKEditorCustomConfig: {
      default: 'http://civicase.local/sites/default/files/civicrm/persist/crm-ckeditor-default.js'
    },
    userFramework: 'Drupal',
    resourceBase: 'http://civicase.local/sites/all/modules/civicrm/',
    lcMessages: 'en_US',
    dateInputFormat: 'dd/mm/yy',
    timeIs24Hr: false,
    ajaxPopupsEnabled: true,
    allowAlertAutodismissal: true,
    entityRef: {
      contactCreate: [
        {
          label: 'New Individual',
          url: '/civicrm/profile/create?reset=1&context=dialog&gid=4',
          type: 'Individual'
        },
        {
          label: 'New Organization',
          url: '/civicrm/profile/create?reset=1&context=dialog&gid=5',
          type: 'Organization'
        },
        {
          label: 'New Household',
          url: '/civicrm/profile/create?reset=1&context=dialog&gid=6',
          type: 'Household'
        }
      ],
      filters: {
        activity: [
          {
            key: 'activity_type_id',
            value: 'Activity Type'
          },
          {
            key: 'status_id',
            value: 'Activity Status'
          }
        ],
        contact: [
          {
            key: 'contact_type',
            value: 'Contact Type'
          },
          {
            key: 'group',
            value: 'Group',
            entity: 'group_contact'
          },
          {
            key: 'tag',
            value: 'Tag',
            entity: 'entity_tag'
          },
          {
            key: 'state_province',
            value: 'State/Province',
            entity: 'address'
          },
          {
            key: 'country',
            value: 'Country',
            entity: 'address'
          },
          {
            key: 'gender_id',
            value: 'Gender'
          },
          {
            key: 'is_deceased',
            value: 'Deceased'
          },
          {
            key: 'contact_id',
            value: 'Contact ID',
            type: 'text'
          },
          {
            key: 'external_identifier',
            value: 'External ID',
            type: 'text'
          },
          {
            key: 'source',
            value: 'Contact Source',
            type: 'text'
          }
        ],
        case: [
          {
            key: 'case_id.case_type_id',
            value: 'Case Type',
            entity: 'Case'
          },
          {
            key: 'case_id.status_id',
            value: 'Case Status',
            entity: 'Case'
          },
          {
            key: 'contact_id.contact_type',
            value: 'Contact Type',
            entity: 'contact'
          },
          {
            key: 'contact_id.group',
            value: 'Group',
            entity: 'group_contact'
          },
          {
            key: 'contact_id.tag',
            value: 'Tag',
            entity: 'entity_tag'
          },
          {
            key: 'contact_id.state_province',
            value: 'State/Province',
            entity: 'address'
          },
          {
            key: 'contact_id.country',
            value: 'Country',
            entity: 'address'
          },
          {
            key: 'contact_id.gender_id',
            value: 'Gender',
            entity: 'contact'
          },
          {
            key: 'contact_id.is_deceased',
            value: 'Deceased',
            entity: 'contact'
          },
          {
            key: 'contact_id.contact_id',
            value: 'Contact ID',
            type: 'text',
            entity: 'contact'
          },
          {
            key: 'contact_id.external_identifier',
            value: 'External ID',
            type: 'text',
            entity: 'contact'
          },
          {
            key: 'contact_id.source',
            value: 'Contact Source',
            type: 'text',
            entity: 'contact'
          }
        ]
      }
    }
  };

  module.constant('CRM', {
    checkPerm: CRM.checkPerm,
    config: CRM.config
  });
}(CRM));
