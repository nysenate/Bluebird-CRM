((_, angular) => {
  describe('ActivityForms', () => {
    let activity, activityForm, ActivityForms, CatchAllActivityFormSpy, SpyActivityForm2;

    beforeEach(initSpyModule);

    beforeEach(module('civicase-base', 'civicase.spy'));

    beforeEach(inject((_ActivityForms_, _CatchAllActivityFormSpy_, _SpyActivityForm2_) => {
      ActivityForms = _ActivityForms_;
      CatchAllActivityFormSpy = _CatchAllActivityFormSpy_;
      SpyActivityForm2 = _SpyActivityForm2_;
    }));

    describe('when requesting the form service for the activity spy 2', () => {
      beforeEach(() => {
        activity = { type: 'spy2' };
        activityForm = ActivityForms.getActivityFormService(activity);
      });

      it('returns the spy 2 activity form service', () => {
        expect(activityForm).toBe(SpyActivityForm2);
      });
    });

    describe('when requesting the form service for any activity', () => {
      beforeEach(() => {
        activity = { type: 'spy99' };
        activityForm = ActivityForms.getActivityFormService(activity);
      });

      it('returns the catch all activity form service', () => {
        expect(activityForm).toBe(CatchAllActivityFormSpy);
      });
    });

    /**
     * Initializes the spy module that adds spy activity forms and a catch all activity
     * form.
     */
    function initSpyModule () {
      const module = angular.module('civicase.spy', ['civicase-base']);

      (() => {
        defineSpyActivityForms();
        defineCatchAllActivityForm();
        configureActivityForms();
      })();

      /**
       * Configures all the spy activity forms.
       */
      function configureActivityForms () {
        module
          .config((ActivityFormsProvider) => {
            ActivityFormsProvider.addActivityForms([
              {
                name: 'SpyActivityForm2',
                weight: 2
              },
              {
                name: 'SpyActivityForm3',
                weight: 3
              },
              {
                name: 'SpyActivityForm1',
                weight: 1
              },
              {
                name: 'CatchAllActivityFormSpy',
                weight: 4
              }
            ]);
          });
      }

      /**
       * Defines a catch all spy form.
       */
      function defineCatchAllActivityForm () {
        module.service('CatchAllActivityFormSpy', function () {
          this.canHandleActivity = () => true;
          this.getActivityFormUrl = _.noop;
        });
      }

      /**
       * Defines several spy forms.
       */
      function defineSpyActivityForms () {
        _.range(1, 3)
          .forEach((spyNumber) => {
            module.service(`SpyActivityForm${spyNumber}`, function SpyActivityForm () {
              this.canHandleActivity = (activity) => activity.type === `spy${spyNumber}`;
              this.getActivityFormUrl = _.noop;
            });
          });
      }
    }
  });
})(CRM._, angular);
