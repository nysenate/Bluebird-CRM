(function (angular, $, _, CRM) {
  var module = angular.module('civicase');

  module.factory('getCaseQueryParams', function (currentCaseCategory) {
    var DEFAULT_FILTERS = {
      caseTypeCategory: currentCaseCategory,
      panelLimit: 5
    };

    return function getCaseQueryParams (extraFilters) {
      var filters = _.defaults(extraFilters, DEFAULT_FILTERS);
      var activityReturnParams = [
        'subject', 'details', 'activity_type_id', 'status_id', 'source_contact_name',
        'target_contact_name', 'assignee_contact_name', 'activity_date_time', 'is_star',
        'original_id', 'tag_id.name', 'tag_id.description', 'tag_id.color', 'file_id',
        'is_overdue', 'case_id'
      ];
      var caseReturnParams = [
        'subject', 'details', 'contact_id', 'case_type_id', 'case_type_id.case_type_category',
        'status_id', 'contacts', 'start_date', 'end_date', 'is_deleted',
        'activity_summary', 'activity_count', 'category_count', 'tag_id.name',
        'tag_id.color', 'tag_id.description', 'tag_id.parent_id', 'related_case_ids'
      ];
      var caseListReturnParams = ['case_type_id', 'case_type_id.is_active',
        'start_date', 'end_date', 'status_id', 'contacts', 'subject'];
      var customValuesReturnParams = [
        'custom_group.id', 'custom_group.name', 'custom_group.title',
        'custom_group.weight', 'custom_group.style',
        'custom_field.name', 'custom_field.label',
        'custom_value.display'
      ];
      var relationshipReturnParams = ['id', 'relationship_type_id', 'contact_id_a',
        'contact_id_b', 'description', 'end_date', 'is_active', 'start_date'];

      return {
        id: filters.caseId,
        return: caseReturnParams,
        'case_type_id.case_type_category': filters.caseTypeCategory,
        'api.Case.getcaselist.relatedCasesByContact': {
          'case_type_id.case_type_category': filters.caseTypeCategory,
          contact_id: { IN: '$value.contact_id' },
          id: { '!=': '$value.id' },
          is_deleted: 0,
          return: caseListReturnParams
        },
        // Linked cases
        'api.Case.getcaselist.linkedCases': {
          'case_type_id.case_type_category': filters.caseTypeCategory,
          id: { IN: '$value.related_case_ids' },
          is_deleted: 0,
          return: caseListReturnParams
        },
        // For the "recent communication" panel
        'api.Activity.getAll.recentCommunication': {
          case_id: filters.caseId,
          is_current_revision: 1,
          is_test: 0,
          'activity_type_id.grouping': { LIKE: '%communication%' },
          'status_id.filter': 1,
          options: { limit: filters.panelLimit, sort: 'activity_date_time DESC' },
          return: activityReturnParams
        },
        // For the "tasks" panel
        'api.Activity.getAll.tasks': {
          case_id: filters.caseId,
          is_current_revision: 1,
          is_test: 0,
          'activity_type_id.grouping': { LIKE: '%task%' },
          'status_id.filter': 0,
          options: { limit: filters.panelLimit, sort: 'activity_date_time ASC' },
          return: activityReturnParams
        },
        // For the "Next Activity" panel
        'api.Activity.getAll.nextActivitiesWhichIsNotMileStone': {
          case_id: filters.caseId,
          status_id: { '!=': 'Completed' },
          'activity_type_id.grouping': { 'NOT LIKE': '%milestone%' },
          options: {
            limit: 1
          },
          return: activityReturnParams
        },
        'api.Activity.getcount.scheduled': {
          case_id: filters.caseId,
          is_current_revision: 1,
          is_deleted: 0,
          status_id: 'Scheduled'
        },
        // For the "scheduled-overdue" count
        'api.Activity.getcount.scheduled_overdue': {
          case_id: filters.caseId,
          is_current_revision: 1,
          is_deleted: 0,
          is_overdue: 1,
          status_id: 'Scheduled'
        },
        // Custom data
        'api.CustomValue.getalltreevalues': {
          entity_id: '$value.id',
          entity_type: 'Case',
          return: customValuesReturnParams
        },
        // Relationship description field
        'api.Relationship.get': {
          case_id: filters.caseId,
          return: relationshipReturnParams,
          'api.Contact.get': {
            contact_id: '$value.contact_id_b'
          },
          options: {
            limit: 0
          }
        },
        sequential: 1
      };
    };
  });
})(angular, CRM.$, CRM._, CRM);
