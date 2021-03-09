/* eslint-env jasmine */
((_, getCrmUrl) => {
  describe('EmailActivityForm', () => {
    let activity, activityFormUrl, EmailActivityForm, expectedActivityFormUrl, canHandle;

    beforeEach(module('civicase', 'civicase-base', 'civicase.data'));

    beforeEach(inject((_activitiesMockData_, _EmailActivityForm_) => {
      EmailActivityForm = _EmailActivityForm_;
      activity = _.chain(_activitiesMockData_.get())
        .first()
        .cloneDeep()
        .value();

      activity.type = 'Email';
      activity.status_type = 'completed';
    }));

    describe('allowing activity status change', () => {
      it('allows for activity status change', () => {
        expect(EmailActivityForm.canChangeStatus).toBe(true);
      });
    });

    describe('handling email activity forms', () => {
      describe('when handling an email activity', () => {
        beforeEach(() => {
          canHandle = EmailActivityForm.canHandleActivity(activity);
        });

        it('can handle the email activity', () => {
          expect(canHandle).toBe(true);
        });
      });

      describe('when handling any other activity type', () => {
        beforeEach(() => {
          activity.type = 'Meeting';
          canHandle = EmailActivityForm.canHandleActivity(activity);
        });

        it('cannot handle the activity', () => {
          expect(canHandle).toBe(false);
        });
      });
    });

    describe('getting the activity form url', () => {
      describe('when getting the form url for a stand alone activity', () => {
        beforeEach(() => {
          delete activity.case_id;

          activityFormUrl = EmailActivityForm.getActivityFormUrl(activity);
          expectedActivityFormUrl = getCrmUrl('civicrm/activity', {
            action: 'view',
            id: activity.id,
            reset: 1
          });
        });

        it('returns the email form url for the stand alone activity', () => {
          expect(activityFormUrl).toEqual(expectedActivityFormUrl);
        });
      });

      describe('when getting the form url for a case activity', () => {
        beforeEach(() => {
          activity.case_id = _.uniqueId();
          activityFormUrl = EmailActivityForm.getActivityFormUrl(activity);
          expectedActivityFormUrl = getCrmUrl('civicrm/case/activity', {
            action: 'view',
            id: activity.id,
            reset: 1,
            caseid: activity.case_id
          });
        });

        it('returns the email form url for the case activity', () => {
          expect(activityFormUrl).toEqual(expectedActivityFormUrl);
        });
      });
    });
  });
})(CRM._, CRM.url);
