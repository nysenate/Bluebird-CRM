/* eslint-env jasmine */

(() => {
  describe('Activity Status', () => {
    let ActivityStatus, ActivityStatusesData;

    beforeEach(module('civicase', 'civicase.data'));

    beforeEach(inject((_ActivityStatus_, _ActivityStatusesData_) => {
      ActivityStatus = _ActivityStatus_;
      ActivityStatusesData = _ActivityStatusesData_.values;
    }));

    describe('when getting all activity statuses', () => {
      let returnedActivityStatuses;

      beforeEach(() => {
        returnedActivityStatuses = ActivityStatus.getAll();
      });

      it('returns all the case statuses', () => {
        expect(returnedActivityStatuses).toEqual(ActivityStatusesData);
      });
    });

    describe('when getting the status by name', () => {
      let returnedStatus;

      beforeEach(() => {
        returnedStatus = ActivityStatus.findByName('Unread');
      });

      it('returns the related status object', () => {
        expect(returnedStatus).toEqual({
          value: '9',
          label: 'Unread',
          color: '#d9534f',
          name: 'Unread',
          grouping: 'communication',
          is_active: '1'
        });
      });
    });
  });
})();
