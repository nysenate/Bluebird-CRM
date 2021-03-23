/* eslint-env jasmine */
((_) => {
  describe('AddCustomPathActivityForm', () => {
    let AddCustomPathActivityForm, activity, civicaseCrmUrl, canHandle;

    beforeEach(module('civicase', 'civicase-base', 'civicase.data'));

    beforeEach(inject((_civicaseCrmUrl_, _activitiesMockData_, _AddCustomPathActivityForm_) => {
      civicaseCrmUrl = _civicaseCrmUrl_;
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
          AddCustomPathActivityForm.getActivityFormUrl(activity);
        });

        it('returns the form url to create a new activity', () => {
          expect(civicaseCrmUrl).toHaveBeenCalledWith('civicrm/activity/email/add', {
            action: 'add',
            reset: 1,
            caseid: activity.case_id,
            atype: activity.activity_type_id,
            context: 'standalone'
          });
        });
      });

      describe('when getting the form url to create a new Print PDF Letter activity', () => {
        beforeEach(() => {
          activity.type = 'Print PDF Letter';
          AddCustomPathActivityForm.getActivityFormUrl(activity);
        });

        it('returns the form url to create a new activity', () => {
          expect(civicaseCrmUrl).toHaveBeenCalledWith('civicrm/activity/pdf/add', {
            action: 'add',
            reset: 1,
            caseid: activity.case_id,
            atype: activity.activity_type_id,
            context: 'standalone'
          });
        });
      });
    });
  });
})(CRM._);
