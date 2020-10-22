/* eslint-env jasmine */
((_, getCrmUrl) => {
  describe('AddCustomPathActivityForm', () => {
    let AddCustomPathActivityForm, activity, activityFormUrl, expectedActivityFormUrl, canHandle;

    beforeEach(module('civicase', 'civicase-base', 'civicase.data'));

    beforeEach(inject((_activitiesMockData_, _AddCustomPathActivityForm_) => {
      AddCustomPathActivityForm = _AddCustomPathActivityForm_;
      activity = _.chain(_activitiesMockData_.get())
        .first()
        .cloneDeep()
        .value();
    }));

    describe('allowing activity status change', () => {
      it('allows for activity status change', () => {
        expect(AddCustomPathActivityForm.canChangeStatus).toBe(true);
      });
    });

    describe('handling activity forms', () => {
      describe('when handling a new Email activity', () => {
        beforeEach(() => {
          activity.type = 'Email';
          canHandle = AddCustomPathActivityForm.canHandleActivity(activity, {
            action: 'add'
          });
        });

        it('can handle the activity', () => {
          expect(canHandle).toBe(true);
        });
      });

      describe('when handling a new Print PDF Letter activity', () => {
        beforeEach(() => {
          activity.type = 'Print PDF Letter';
          canHandle = AddCustomPathActivityForm.canHandleActivity(activity, {
            action: 'add'
          });
        });

        it('can handle the activity', () => {
          expect(canHandle).toBe(true);
        });
      });

      describe('when handling an existing Email activity', () => {
        beforeEach(() => {
          activity.type = 'Email';
          canHandle = AddCustomPathActivityForm.canHandleActivity(activity, {
            action: 'update'
          });
        });

        it('cannot handle the activity', () => {
          expect(canHandle).toBe(false);
        });
      });

      describe('when handling an existing Print PDF Letter activity', () => {
        beforeEach(() => {
          activity.type = 'Print PDF Letter';
          canHandle = AddCustomPathActivityForm.canHandleActivity(activity, {
            action: 'update'
          });
        });

        it('cannot handle the activity', () => {
          expect(canHandle).toBe(false);
        });
      });

      describe('when handling a new activity', () => {
        beforeEach(() => {
          activity.type = 'Another Type';
          canHandle = AddCustomPathActivityForm.canHandleActivity(activity, {
            action: 'add'
          });
        });

        it('cannot handle the activity', () => {
          expect(canHandle).toBe(false);
        });
      });
    });

    describe('getting the activity form url', () => {
      describe('when getting the form url to create a new Email activity', () => {
        beforeEach(() => {
          activity.type = 'Email';
          activityFormUrl = AddCustomPathActivityForm.getActivityFormUrl(activity);
          expectedActivityFormUrl = getCrmUrl('civicrm/activity/email/add', {
            action: 'add',
            reset: 1,
            caseid: activity.case_id,
            atype: activity.activity_type_id,
            context: 'standalone'
          });
        });

        it('returns the form url to create a new activity', () => {
          expect(activityFormUrl).toEqual(expectedActivityFormUrl);
        });
      });

      describe('when getting the form url to create a new Print PDF Letter activity', () => {
        beforeEach(() => {
          activity.type = 'Print PDF Letter';
          activityFormUrl = AddCustomPathActivityForm.getActivityFormUrl(activity);
          expectedActivityFormUrl = getCrmUrl('civicrm/activity/pdf/add', {
            action: 'add',
            reset: 1,
            caseid: activity.case_id,
            atype: activity.activity_type_id,
            context: 'standalone'
          });
        });

        it('returns the form url to create a new activity', () => {
          expect(activityFormUrl).toEqual(expectedActivityFormUrl);
        });
      });
    });
  });
})(CRM._, CRM.url);
