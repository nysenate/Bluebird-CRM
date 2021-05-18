((_) => {
  describe('UpdateActivityForm', () => {
    let civicaseCrmUrl, activity, UpdateActivityForm, canHandle;

    beforeEach(module('civicase', 'civicase-base', 'civicase.data'));

    beforeEach(inject((_civicaseCrmUrl_, _activitiesMockData_, _UpdateActivityForm_) => {
      civicaseCrmUrl = _civicaseCrmUrl_;
      UpdateActivityForm = _UpdateActivityForm_;
      activity = _.chain(_activitiesMockData_.get())
        .first()
        .cloneDeep()
        .value();
    }));

    describe('allowing activity status change', () => {
      it('allows for activity status change', () => {
        expect(UpdateActivityForm.canChangeStatus).toBe(true);
      });
    });

    describe('handling activity forms', () => {
      describe('when handling an update form', () => {
        beforeEach(() => {
          canHandle = UpdateActivityForm.canHandleActivity(activity, {
            action: 'update'
          });
        });

        it('can handle the update form', () => {
          expect(canHandle).toBe(true);
        });
      });

      describe('when handling any other form type', () => {
        beforeEach(() => {
          canHandle = UpdateActivityForm.canHandleActivity(activity);
        });

        it('cannot handle the form', () => {
          expect(canHandle).toBe(false);
        });
      });
    });

    describe('getting the activity form url', () => {
      describe('when getting the form url for a stand alone activity', () => {
        beforeEach(() => {
          delete activity.case_id;

          UpdateActivityForm.getActivityFormUrl(activity);
        });

        it('returns the update form url for the stand alone activity', () => {
          expect(civicaseCrmUrl).toHaveBeenCalledWith('civicrm/activity', {
            action: 'update',
            id: activity.id,
            reset: 1
          });
        });
      });

      describe('when getting the form url for a case activity', () => {
        beforeEach(() => {
          activity.case_id = _.uniqueId();
          UpdateActivityForm.getActivityFormUrl(activity);
        });

        it('returns the update form url for the case activity', () => {
          expect(civicaseCrmUrl).toHaveBeenCalledWith('civicrm/case/activity', {
            action: 'update',
            id: activity.id,
            reset: 1,
            caseid: activity.case_id
          });
        });
      });
    });
  });
})(CRM._);
