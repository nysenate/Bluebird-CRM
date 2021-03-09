/* eslint-env jasmine */
((_, getCrmUrl) => {
  describe('AddActivityForm', () => {
    let AddActivityForm, activity, activityFormUrl, expectedActivityFormUrl, canHandle;

    beforeEach(module('civicase', 'civicase-base', 'civicase.data'));

    beforeEach(inject((_activitiesMockData_, _AddActivityForm_) => {
      AddActivityForm = _AddActivityForm_;
      activity = _.chain(_activitiesMockData_.get())
        .first()
        .cloneDeep()
        .value();
    }));

    describe('allowing activity status change', () => {
      it('allows for activity status change', () => {
        expect(AddActivityForm.canChangeStatus).toBe(true);
      });
    });

    describe('handling activity forms', () => {
      describe('when handling a new activity', () => {
        beforeEach(() => {
          canHandle = AddActivityForm.canHandleActivity(activity, {
            action: 'add'
          });
        });

        it('can handle the activity', () => {
          expect(canHandle).toBe(true);
        });
      });

      describe('when handling an existing activity', () => {
        beforeEach(() => {
          canHandle = AddActivityForm.canHandleActivity(activity, {
            action: 'update'
          });
        });

        it('cannot handle the activity', () => {
          expect(canHandle).toBe(false);
        });
      });
    });

    describe('getting the activity form url', () => {
      describe('when getting the form url to create a new activity', () => {
        beforeEach(() => {
          activityFormUrl = AddActivityForm.getActivityFormUrl(activity);
          expectedActivityFormUrl = getCrmUrl('civicrm/case/activity', {
            action: 'add',
            reset: 1,
            caseid: activity.case_id,
            atype: activity.activity_type_id
          });
        });

        it('returns the form url to create a new activity', () => {
          expect(activityFormUrl).toEqual(expectedActivityFormUrl);
        });
      });
    });
  });
})(CRM._, CRM.url);
