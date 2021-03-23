/* eslint-env jasmine */
((_) => {
  describe('EmailActivityForm', () => {
    let civicaseCrmUrl, activity, EmailActivityForm, canHandle;

    beforeEach(module('civicase', 'civicase-base', 'civicase.data'));

    beforeEach(inject((_civicaseCrmUrl_, _activitiesMockData_, _EmailActivityForm_) => {
      civicaseCrmUrl = _civicaseCrmUrl_;
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

          EmailActivityForm.getActivityFormUrl(activity);
        });

        it('returns the email form url for the stand alone activity', () => {
          expect(civicaseCrmUrl).toHaveBeenCalledWith('civicrm/activity', {
            action: 'view',
            id: activity.id,
            reset: 1,
            context: 'activity'
          });
        });
      });

      describe('when getting the form url for a case activity', () => {
        beforeEach(() => {
          activity.case_id = _.uniqueId();
          EmailActivityForm.getActivityFormUrl(activity);
        });

        it('returns the email form url for the case activity', () => {
          expect(civicaseCrmUrl).toHaveBeenCalledWith('civicrm/activity', {
            action: 'view',
            id: activity.id,
            reset: 1,
            context: 'case'
          });
        });
      });
    });
  });
})(CRM._);
