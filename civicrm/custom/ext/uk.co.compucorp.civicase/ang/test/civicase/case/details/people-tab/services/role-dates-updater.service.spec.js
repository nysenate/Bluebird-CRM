/* eslint-env jasmine */

((_) => {
  describe('RoleDatesUpdater', () => {
    let roleDatesUpdater, roleData, caseId, loggedInContactId, returnedApiCalls;

    beforeEach(module('civicase', 'civicase.data'));

    beforeEach(inject((_civicaseRoleDatesUpdater_, _loggedInContactId_) => {
      loggedInContactId = _loggedInContactId_;
      roleDatesUpdater = _civicaseRoleDatesUpdater_;
    }));

    beforeEach(() => {
      caseId = _.uniqueId();
      roleData = {
        display_name: 'Jon Snow',
        role: 'Ranger',
        relationship: {
          end_date: '1999-12-31',
          relationship_ids: [_.uniqueId(), _.uniqueId(), _.uniqueId()],
          start_date: '1999-01-31'
        },
        previousValues: {
          end_date: '1999-12-31',
          start_date: '1999-01-31'
        }
      };
    });

    describe('when getting the api calls for updating the end date of a role', () => {
      beforeEach(() => {
        roleData.relationship.end_date = '2000-12-31';
        returnedApiCalls = roleDatesUpdater.getApiCallsForEndDate(
          roleData,
          caseId
        );
      });

      it('updates the end date for each relationship in the role', () => {
        expect(returnedApiCalls).toEqual(jasmine.arrayContaining([
          ['Relationship', 'create', {
            id: roleData.relationship.relationship_ids[0],
            end_date: '2000-12-31'
          }],
          ['Relationship', 'create', {
            id: roleData.relationship.relationship_ids[1],
            end_date: '2000-12-31'
          }],
          ['Relationship', 'create', {
            id: roleData.relationship.relationship_ids[2],
            end_date: '2000-12-31'
          }]
        ]));
      });

      it('creates an activity for recording the end date change', () => {
        expect(returnedApiCalls).toEqual(jasmine.arrayContaining([
          ['Activity', 'create', {
            activity_type_id: 'Change Case Role End Date',
            activity_date_time: 'now',
            case_id: caseId,
            source_contact_id: loggedInContactId,
            status_id: 'Completed',
            subject: 'Jon Snow, with Ranger case role, had end date changed from 31/12/1999 to 31/12/2000'
          }]
        ]));
      });
    });

    describe('when getting the api calls for updating the start date of a role', () => {
      beforeEach(() => {
        roleData.relationship.start_date = '2000-01-31';
        returnedApiCalls = roleDatesUpdater.getApiCallsForStartDate(
          roleData,
          caseId
        );
      });

      it('updates the start date for each relationship in the role', () => {
        expect(returnedApiCalls).toEqual(jasmine.arrayContaining([
          ['Relationship', 'create', {
            id: roleData.relationship.relationship_ids[0],
            start_date: '2000-01-31'
          }],
          ['Relationship', 'create', {
            id: roleData.relationship.relationship_ids[1],
            start_date: '2000-01-31'
          }],
          ['Relationship', 'create', {
            id: roleData.relationship.relationship_ids[2],
            start_date: '2000-01-31'
          }]
        ]));
      });

      it('creates an activity for recording the start date change', () => {
        expect(returnedApiCalls).toEqual(jasmine.arrayContaining([
          ['Activity', 'create', {
            activity_type_id: 'Change Case Role Start Date',
            activity_date_time: 'now',
            case_id: caseId,
            source_contact_id: loggedInContactId,
            status_id: 'Completed',
            subject: 'Jon Snow, with Ranger case role, had start date changed from 31/01/1999 to 31/01/2000'
          }]
        ]));
      });
    });

    describe('when the previous role date is not defined', () => {
      beforeEach(() => {
        delete roleData.previousValues.start_date;

        roleData.relationship.start_date = '2000-01-31';
        returnedApiCalls = roleDatesUpdater.getApiCallsForStartDate(
          roleData,
          caseId
        );
      });

      it('does not add the previous role date to the activity subject', () => {
        expect(returnedApiCalls).toEqual(jasmine.arrayContaining([
          ['Activity', 'create', jasmine.objectContaining({
            subject: 'Jon Snow, with Ranger case role, had start date changed to 31/01/2000'
          })]
        ]));
      });
    });

    describe('when the current role date is not defined', () => {
      beforeEach(() => {
        delete roleData.relationship.start_date;

        roleData.previousValues.start_date = '2000-01-31';
        returnedApiCalls = roleDatesUpdater.getApiCallsForStartDate(
          roleData,
          caseId
        );
      });

      it('does not add the current role date to the activity subject', () => {
        expect(returnedApiCalls).toEqual(jasmine.arrayContaining([
          ['Activity', 'create', jasmine.objectContaining({
            subject: 'Jon Snow, with Ranger case role, had start date changed from 31/01/2000'
          })]
        ]));
      });
    });

    describe('when updating the previous end date value', () => {
      beforeEach(() => {
        roleData.relationship.end_date = '2000-12-31';

        roleDatesUpdater.updatePreviousValue(roleData, 'end_date');
      });

      it('updates the previous end date value', () => {
        expect(roleData.previousValues.end_date).toBe('2000-12-31');
      });
    });

    describe('when updating the previous start date value', () => {
      beforeEach(() => {
        roleData.relationship.start_date = '2000-01-31';

        roleDatesUpdater.updatePreviousValue(roleData, 'start_date');
      });

      it('updates the previous start date value', () => {
        expect(roleData.previousValues.start_date).toBe('2000-01-31');
      });
    });
  });
})(CRM._);
