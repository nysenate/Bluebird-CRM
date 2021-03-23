/* eslint-env jasmine */
((_) => {
  describe('DraftEmailActivityForm', () => {
    let activity, checkIfDraftActivity,
      DraftEmailActivityForm, civicaseCrmUrl;

    beforeEach(module('civicase', 'civicase-base', 'civicase.data', ($provide) => {
      checkIfDraftActivity = jasmine.createSpy('checkIfDraftActivity');

      $provide.value('checkIfDraftActivity', checkIfDraftActivity);
    }));

    beforeEach(inject((_civicaseCrmUrl_, _activitiesMockData_, _DraftEmailActivityForm_) => {
      civicaseCrmUrl = _civicaseCrmUrl_;
      DraftEmailActivityForm = _DraftEmailActivityForm_;
      activity = _.chain(_activitiesMockData_.get())
        .first()
        .cloneDeep()
        .value();
    }));

    describe('allowing activity status change', () => {
      it('does not allow for activity status change', () => {
        expect(DraftEmailActivityForm.canChangeStatus).toBe(false);
      });
    });

    describe('handling activity forms', () => {
      let canHandleResult, mockCheckIfDraftActivityResult;

      beforeEach(() => {
        mockCheckIfDraftActivityResult = _.uniqueId();
        checkIfDraftActivity.and.returnValue(mockCheckIfDraftActivityResult);

        canHandleResult = DraftEmailActivityForm.canHandleActivity(activity);
      });

      it('uses the check draft activity to determine if it can handle the given activity', () => {
        expect(checkIfDraftActivity).toHaveBeenCalledWith(activity, ['Email']);
      });

      it('returns the result from check draft activity directly', () => {
        expect(canHandleResult).toBe(mockCheckIfDraftActivityResult);
      });
    });

    describe('getting the activity form URL', () => {
      let expectedActivityFormUrlParams;

      beforeEach(() => {
        activity.target_contact_id = [_.uniqueId()];

        expectedActivityFormUrlParams = {
          action: 'add',
          atype: activity.activity_type_id,
          caseid: activity.case_id,
          cid: activity.target_contact_id[0],
          draft_id: activity.id,
          id: activity.id,
          reset: '1'
        };
      });

      describe('when getting the form URL for a case activity', () => {
        beforeEach(() => {
          expectedActivityFormUrlParams.action = 'add';
          DraftEmailActivityForm.getActivityFormUrl(activity);
        });

        it('returns the popup form URL for the draft activity in create mode by default', () => {
          expect(civicaseCrmUrl).toHaveBeenCalledWith('civicrm/activity/email/add',
            expectedActivityFormUrlParams);
        });
      });

      describe('when getting the form URL for a standalone activity', () => {
        beforeEach(() => {
          expectedActivityFormUrlParams.action = 'add';

          delete expectedActivityFormUrlParams.caseid;
          delete activity.case_id;

          DraftEmailActivityForm.getActivityFormUrl(activity);
        });

        it('returns the popup form URL for the draft activity in create mode by default', () => {
          expect(civicaseCrmUrl).toHaveBeenCalledWith('civicrm/activity/email/add',
            expectedActivityFormUrlParams);
        });
      });

      describe('when getting the form URL in view mode', () => {
        beforeEach(() => {
          expectedActivityFormUrlParams.action = 'view';
          DraftEmailActivityForm.getActivityFormUrl(activity, {
            action: 'view'
          });
        });

        it('returns the popup form URL for the draft activity in view mode', () => {
          expect(civicaseCrmUrl).toHaveBeenCalledWith('civicrm/activity/email/view',
            expectedActivityFormUrlParams);
        });
      });
    });
  });
})(CRM._);
