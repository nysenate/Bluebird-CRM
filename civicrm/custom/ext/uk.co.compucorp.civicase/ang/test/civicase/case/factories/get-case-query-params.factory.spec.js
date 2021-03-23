/* eslint-env jasmine */
((_) => {
  describe('getCaseQueryParams', () => {
    let getCaseQueryParams;

    beforeEach(module('civicase'));

    beforeEach(inject((_getCaseQueryParams_) => {
      getCaseQueryParams = _getCaseQueryParams_;
    }));

    describe('when requesting case query parameters', () => {
      let returnValue;

      beforeEach(() => {
        returnValue = getCaseQueryParams({
          caseId: '11',
          caseTypeCategory: 'cases',
          panelLimit: 5
        });
      });

      it('returns case query parameters', () => {
        expect(returnValue).toEqual({
          id: '11',
          return: [
            'subject', 'details', 'contact_id', 'case_type_id', 'case_type_id.case_type_category',
            'status_id', 'contacts', 'start_date', 'end_date', 'is_deleted',
            'activity_summary', 'activity_count', 'category_count', 'tag_id.name',
            'tag_id.color', 'tag_id.description', 'tag_id.parent_id', 'related_case_ids'
          ],
          'case_type_id.case_type_category': 'cases',
          'api.Case.getcaselist.relatedCasesByContact': {
            'case_type_id.case_type_category': 'cases',
            contact_id: { IN: '$value.contact_id' },
            id: { '!=': '$value.id' },
            is_deleted: 0,
            return: ['case_type_id', 'case_type_id.is_active', 'start_date',
              'end_date', 'status_id', 'contacts', 'subject']
          },
          // Linked cases
          'api.Case.getcaselist.linkedCases': {
            'case_type_id.case_type_category': 'cases',
            id: { IN: '$value.related_case_ids' },
            is_deleted: 0,
            return: ['case_type_id', 'case_type_id.is_active', 'start_date',
              'end_date', 'status_id', 'contacts', 'subject']
          },
          // For the "recent communication" panel
          'api.Activity.getAll.recentCommunication': {
            case_id: '11',
            is_current_revision: 1,
            is_test: 0,
            'activity_type_id.grouping': { LIKE: '%communication%' },
            'status_id.filter': 1,
            options: { limit: 5, sort: 'activity_date_time DESC' },
            return: [
              'subject', 'details', 'activity_type_id', 'status_id', 'source_contact_name',
              'target_contact_name', 'assignee_contact_name', 'activity_date_time', 'is_star',
              'original_id', 'tag_id.name', 'tag_id.description', 'tag_id.color', 'file_id',
              'is_overdue', 'case_id'
            ]
          },
          // For the "tasks" panel
          'api.Activity.getAll.tasks': {
            case_id: '11',
            is_current_revision: 1,
            is_test: 0,
            'activity_type_id.grouping': { LIKE: '%task%' },
            'status_id.filter': 0,
            options: { limit: 5, sort: 'activity_date_time ASC' },
            return: [
              'subject', 'details', 'activity_type_id', 'status_id', 'source_contact_name',
              'target_contact_name', 'assignee_contact_name', 'activity_date_time', 'is_star',
              'original_id', 'tag_id.name', 'tag_id.description', 'tag_id.color', 'file_id',
              'is_overdue', 'case_id'
            ]
          },
          // For the "Next Activity" panel
          'api.Activity.getAll.nextActivitiesWhichIsNotMileStone': {
            case_id: '11',
            status_id: { '!=': 'Completed' },
            'activity_type_id.grouping': { 'NOT LIKE': '%milestone%' },
            options: {
              limit: 1
            },
            return: [
              'subject', 'details', 'activity_type_id', 'status_id', 'source_contact_name',
              'target_contact_name', 'assignee_contact_name', 'activity_date_time', 'is_star',
              'original_id', 'tag_id.name', 'tag_id.description', 'tag_id.color', 'file_id',
              'is_overdue', 'case_id'
            ]
          },
          'api.Activity.getcount.scheduled': {
            case_id: '11',
            is_current_revision: 1,
            is_deleted: 0,
            status_id: 'Scheduled'
          },
          // For the "scheduled-overdue" count
          'api.Activity.getcount.scheduled_overdue': {
            case_id: '11',
            is_current_revision: 1,
            is_deleted: 0,
            is_overdue: 1,
            status_id: 'Scheduled'
          },
          // Custom data
          'api.CustomValue.getalltreevalues': {
            entity_id: '$value.id',
            entity_type: 'Case',
            return: [
              'custom_group.id', 'custom_group.name', 'custom_group.title',
              'custom_group.weight', 'custom_group.style',
              'custom_field.name', 'custom_field.label',
              'custom_value.display'
            ]
          },
          // Relationship description field
          'api.Relationship.get': {
            case_id: '11',
            'api.Contact.get': {
              contact_id: '$value.contact_id_b'
            },
            options: {
              limit: 0
            },
            return: [
              'id', 'relationship_type_id', 'contact_id_a', 'contact_id_b',
              'description', 'end_date', 'is_active', 'start_date'
            ]
          },
          sequential: 1
        });
      });
    });
  });
})(CRM._);
