/* eslint-env jasmine */

(() => {
  describe('Case Status', () => {
    let CaseStatus, CaseStatusData;

    beforeEach(module('civicase', 'civicase.data'));

    beforeEach(inject((_CaseStatus_, _CaseStatuses_) => {
      CaseStatus = _CaseStatus_;
      CaseStatusData = _CaseStatuses_.values;
    }));

    describe('when getting all case statuses', () => {
      let returnedCaseStatuses;

      beforeEach(() => {
        returnedCaseStatuses = CaseStatus.getAll();
      });

      it('returns all the case statuses', () => {
        expect(returnedCaseStatuses).toEqual(CaseStatusData);
      });
    });

    describe('when getting the labels for case statuses using status values', () => {
      let returnedLabels;

      beforeEach(() => {
        returnedLabels = CaseStatus.getLabelsForValues(['2', '3']);
      });

      it('returns the labels for the given status values', () => {
        expect(returnedLabels).toEqual(['Resolved', 'Urgent']);
      });
    });
  });
})();
