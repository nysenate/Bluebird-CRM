/* eslint-env jasmine */
((_) => {
  describe('DraftPdfActivityForm', () => {
    let civicaseCrmUrl, activity, checkIfDraftActivity,
      DraftPdfActivityForm;

    beforeEach(module('civicase', 'civicase-base', 'civicase.data', ($provide) => {
      checkIfDraftActivity = jasmine.createSpy('checkIfDraftActivity');

      $provide.value('checkIfDraftActivity', checkIfDraftActivity);
    }));

    beforeEach(inject((_civicaseCrmUrl_, _activitiesMockData_, _DraftPdfActivityForm_) => {
      DraftPdfActivityForm = _DraftPdfActivityForm_;
      civicaseCrmUrl = _civicaseCrmUrl_;
      activity = _.chain(_activitiesMockData_.get())
        .first()
        .cloneDeep()
        .value();
    }));

    describe('allowing activity status change', () => {
      it('does not allow for activity status change', () => {
        expect(DraftPdfActivityForm.canChangeStatus).toBe(false);
      });
    });

    describe('handling activity forms', () => {
      let canHandleResult, mockCheckIfDraftActivityResult;

      beforeEach(() => {
        mockCheckIfDraftActivityResult = _.uniqueId();
        checkIfDraftActivity.and.returnValue(mockCheckIfDraftActivityResult);

        canHandleResult = DraftPdfActivityForm.canHandleActivity(activity);
      });

      it('uses the check draft activity to determine if it can handle the given activity', () => {
        expect(checkIfDraftActivity).toHaveBeenCalledWith(activity, ['Print PDF Letter']);
      });

      it('returns the result from check draft activity directly', () => {
        expect(canHandleResult).toBe(mockCheckIfDraftActivityResult);
      });
    });

    describe('when getting the form url', () => {
      let activityFormUrlParams;

      beforeEach(() => {
        activity.target_contact_id = [_.uniqueId()];

        activityFormUrlParams = {
          action: 'update',
          caseid: activity.case_id,
          cid: activity.target_contact_id[0],
          context: 'standalone',
          draft_id: activity.id,
          id: activity.id,
          reset: '1'
        };
      });

      describe('when the activity is part of a case', () => {
        beforeEach(() => {
          activityFormUrlParams.action = 'add';
          DraftPdfActivityForm.getActivityFormUrl(activity);
        });

        it('returns the popup form url for the PDF draft activity', () => {
          expect(civicaseCrmUrl).toHaveBeenCalledWith(
            'civicrm/activity/pdf/add',
            activityFormUrlParams
          );
        });
      });

      describe('when getting the form URL in view mode', () => {
        beforeEach(() => {
          activityFormUrlParams.action = 'view';
          DraftPdfActivityForm.getActivityFormUrl(activity, {
            action: 'view'
          });
        });

        it('returns the popup form URL for the draft activity in view mode', () => {
          expect(civicaseCrmUrl).toHaveBeenCalledWith(
            'civicrm/activity/pdf/view',
            activityFormUrlParams
          );
        });
      });
    });
  });
})(CRM._);
