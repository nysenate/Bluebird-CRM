((_) => {
  describe('ViewActivityForm', () => {
    let activity, civicaseCrmUrl, canHandle, ViewActivityForm;

    beforeEach(module('civicase', 'civicase-base', 'civicase.data'));

    beforeEach(inject((_civicaseCrmUrl_, _activitiesMockData_, _ViewActivityForm_) => {
      civicaseCrmUrl = _civicaseCrmUrl_;
      ViewActivityForm = _ViewActivityForm_;
      activity = _.chain(_activitiesMockData_.get())
        .first()
        .cloneDeep()
        .value();
    }));

    describe('allowing activity status change', () => {
      it('allows for activity status change', () => {
        expect(ViewActivityForm.canChangeStatus).toBe(true);
      });
    });

    describe('handling activity forms', () => {
      describe('when handling an existing activity', () => {
        beforeEach(() => {
          canHandle = ViewActivityForm.canHandleActivity(activity);
        });

        it('can handle the activity', () => {
          expect(canHandle).toBe(true);
        });
      });

      describe('when handling an activity that has not been saved', () => {
        beforeEach(() => {
          delete activity.id;
          canHandle = ViewActivityForm.canHandleActivity(activity);
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

          ViewActivityForm.getActivityFormUrl(activity);
        });

        it('returns the form url for the stand alone activity', () => {
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
          ViewActivityForm.getActivityFormUrl(activity);
        });

        it('returns the form url for the case activity', () => {
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
