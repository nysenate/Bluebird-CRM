/* eslint-env jasmine */

((_, $) => {
  describe('MoveCopyActivityAction', () => {
    let $q, $rootScope, MoveCopyActivityAction, activitiesMockData,
      crmApiMock, dialogServiceMock, originalDialogFunction;

    beforeEach(module('civicase', 'civicase.data', ($provide) => {
      crmApiMock = jasmine.createSpy('crmApi');
      dialogServiceMock = jasmine.createSpyObj('dialogService', ['open']);

      $provide.value('crmApi', crmApiMock);
      $provide.value('dialogService', dialogServiceMock);
    }));

    beforeEach(inject(function (_$q_, _$rootScope_, _activitiesMockData_,
      _MoveCopyActivityAction_) {
      $q = _$q_;
      $rootScope = _$rootScope_;
      activitiesMockData = _activitiesMockData_;
      MoveCopyActivityAction = _MoveCopyActivityAction_;
      originalDialogFunction = $.fn.dialog;

      spyOn($.fn, 'dialog');
    }));

    afterEach(() => {
      $.fn.dialog = originalDialogFunction;
    });

    describe('Copy Activities bulk action', () => {
      let activities, modalOpenCall, model;
      const $scope = {};

      beforeEach(() => {
        const caseId = _.uniqueId();

        activities = activitiesMockData.get();

        activities.forEach((activity) => {
          activity.case_id = caseId;
        });

        $scope.selectedActivities = _.sample(activities, 2);
      });

      describe('when selecting some activities and then copy them to a new case', () => {
        beforeEach(() => {
          MoveCopyActivityAction.doAction($scope, { operation: 'copy' });

          modalOpenCall = dialogServiceMock.open.calls.mostRecent().args;
          model = modalOpenCall[2];
        });

        it('opens a case selection modal', () => {
          expect(dialogServiceMock.open).toHaveBeenCalledWith(
            'MoveCopyActCard',
            '~/civicase/activity/actions/services/move-copy-activity-action.html',
            jasmine.any(Object),
            jasmine.any(Object)
          );
        });

        it('displays the title as "Copy 2 Activities"', () => {
          expect(modalOpenCall[3].title).toBe('Copy 2 Activities');
        });

        describe('the model', () => {
          it('defines an empty case id', () => {
            expect(model.case_id).toBe('');
          });

          it('does not display the subject', () => {
            expect(model.isSubjectVisible).toBe(false);
          });

          it('defines an empty subject', () => {
            expect(model.subject).toBe('');
          });
        });

        describe('when fetching list of cases', () => {
          let getCaseListApiParams;

          beforeEach(() => {
            getCaseListApiParams = modalOpenCall[2].getCaseListApiParams;
          });

          it('displays cases from those case type categories for which user has "basic case information" permission', () => {
            expect(getCaseListApiParams()).toEqual({});
          });
        });

        describe('when saving the copy action modal', () => {
          let expectedActivitySavingCalls;

          beforeEach(() => {
            const saveMethod = modalOpenCall[3].buttons[0].click;
            model.case_id = _.uniqueId();
            expectedActivitySavingCalls = [['Activity', 'copybyquery', {
              case_id: model.case_id,
              id: $scope.selectedActivities.map((activity) => {
                return activity.id;
              })
            }]];

            spyOn($rootScope, '$broadcast');
            crmApiMock.and.returnValue($q.resolve([{ values: $scope.selectedActivities }]));
            saveMethod();
            $rootScope.$digest();
          });

          it('saves a new copy of each of the activities and assign them to the selected case', () => {
            expect(crmApiMock.calls.mostRecent().args[0]).toEqual(expectedActivitySavingCalls);
          });

          it('emits a civicase activity updated event', () => {
            expect($rootScope.$broadcast).toHaveBeenCalledWith('civicase::activity::updated');
          });

          it('closes the dialog', () => {
            expect($.fn.dialog).toHaveBeenCalled();
          });
        });

        describe('when the selected case is the same as the current case', () => {
          beforeEach(() => {
            const saveMethod = modalOpenCall[3].buttons[0].click;
            model.case_id = $scope.selectedActivities[0].case_id;

            spyOn($rootScope, '$broadcast');
            crmApiMock.and.returnValue($q.resolve([{ values: $scope.selectedActivities }]));
            saveMethod();
            $rootScope.$digest();
          });

          it('does not request the activities data', () => {
            expect(crmApiMock).not.toHaveBeenCalled();
          });

          it('does not emit the civicase activity updated event', () => {
            expect($rootScope.$broadcast).not.toHaveBeenCalledWith('civicase::activity::updated');
          });

          it('closes the dialog', () => {
            expect($.fn.dialog).toHaveBeenCalled();
          });
        });
      });

      describe('when selecting a single activity and copying it to a new case', () => {
        beforeEach(() => {
          $scope.selectedActivities = _.chain(activities)
            .sample(1)
            .cloneDeep()
            .value();
          $scope.selectedActivities[0].type = 'Meeting';

          MoveCopyActivityAction.doAction($scope, { operation: 'copy' });

          modalOpenCall = dialogServiceMock.open.calls.mostRecent().args;
          model = modalOpenCall[2];
        });

        it('displays the modal title as "Copy Meeting Activity"', () => {
          expect(modalOpenCall[3].title).toBe('Copy Meeting Activity');
        });

        describe('the model', () => {
          it('defines the case id the same as the selected activity', () => {
            expect(model.case_id).toBe($scope.selectedActivities[0].case_id);
          });

          it('displays the subject', () => {
            expect(model.isSubjectVisible).toBe(true);
          });

          it('defines an empty subject', () => {
            expect(model.subject).toBe($scope.selectedActivities[0].subject);
          });
        });

        describe('when saving the copy action modal', () => {
          let expectedActivitySavingCalls;

          beforeEach(() => {
            const saveMethod = modalOpenCall[3].buttons[0].click;
            model.case_id = _.uniqueId();
            model.subject = 'a sample subject';
            expectedActivitySavingCalls = [['Activity', 'copybyquery', {
              case_id: model.case_id,
              subject: model.subject,
              id: $scope.selectedActivities.map((activity) => {
                return activity.id;
              })
            }]];

            crmApiMock.and.returnValue($q.resolve([{ values: $scope.selectedActivities }]));
            saveMethod();
            $rootScope.$digest();
          });

          it('saves a new copy of each of the activity and assigns it to the selected case using the new activity subject', () => {
            expect(crmApiMock.calls.mostRecent().args[0]).toEqual(expectedActivitySavingCalls);
          });
        });
      });
    });

    describe('Move Activities bulk action', () => {
      let activities, modalOpenCall, model;
      const $scope = {};

      beforeEach(() => {
        const caseId = _.uniqueId();

        activities = activitiesMockData.get();

        activities.forEach((activity) => {
          activity.case_id = caseId;
        });

        $scope.selectedActivities = _.sample(activities, 2);
      });

      describe('when selecting some activities and then move them to a new case', () => {
        beforeEach(() => {
          MoveCopyActivityAction.doAction($scope, { operation: 'move' });

          modalOpenCall = dialogServiceMock.open.calls.mostRecent().args;
          model = modalOpenCall[2];
        });

        it('opens a case selection modal', () => {
          expect(dialogServiceMock.open).toHaveBeenCalledWith(
            'MoveCopyActCard',
            '~/civicase/activity/actions/services/move-copy-activity-action.html',
            jasmine.any(Object),
            jasmine.any(Object)
          );
        });

        it('displays the title as "Move 2 Activities"', () => {
          expect(modalOpenCall[3].title).toBe('Move 2 Activities');
        });

        describe('the model', () => {
          it('defines an empty case id', () => {
            expect(model.case_id).toBe('');
          });

          it('does not display the subject', () => {
            expect(model.isSubjectVisible).toBe(false);
          });

          it('defines an empty subject', () => {
            expect(model.subject).toBe('');
          });
        });

        describe('when saving the move action modal', () => {
          let expectedActivitySavingCalls;

          beforeEach(() => {
            const saveMethod = modalOpenCall[3].buttons[0].click;
            model.case_id = _.uniqueId();
            expectedActivitySavingCalls = [['Activity', 'movebyquery', {
              case_id: model.case_id,
              id: $scope.selectedActivities.map((activity) => {
                return activity.id;
              })
            }]];

            spyOn($rootScope, '$broadcast');
            crmApiMock.and.returnValue($q.resolve([{ values: $scope.selectedActivities }]));
            saveMethod();
            $rootScope.$digest();
          });

          it('moves each of the activities and assign them to the selected case', () => {
            expect(crmApiMock.calls.mostRecent().args[0]).toEqual(expectedActivitySavingCalls);
          });

          it('emits a civicase activity updated event', () => {
            expect($rootScope.$broadcast).toHaveBeenCalledWith('civicase::activity::updated');
          });

          it('closes the dialog', () => {
            expect($.fn.dialog).toHaveBeenCalled();
          });
        });

        describe('when the selected case is the same as the current case', () => {
          beforeEach(() => {
            const saveMethod = modalOpenCall[3].buttons[0].click;
            model.case_id = $scope.selectedActivities[0].case_id;

            spyOn($rootScope, '$broadcast');
            crmApiMock.and.returnValue($q.resolve([{ values: $scope.selectedActivities }]));
            saveMethod();
            $rootScope.$digest();
          });

          it('does not request the activities data', () => {
            expect(crmApiMock).not.toHaveBeenCalled();
          });

          it('does not emit the civicase activity updated event', () => {
            expect($rootScope.$broadcast).not.toHaveBeenCalledWith('civicase::activity::updated');
          });

          it('closes the dialog', () => {
            expect($.fn.dialog).toHaveBeenCalled();
          });
        });
      });

      describe('when selecting a single activity and moving it to a new case', () => {
        beforeEach(() => {
          $scope.selectedActivities = _.chain(activities)
            .sample(1)
            .cloneDeep()
            .value();
          $scope.selectedActivities[0].type = 'Meeting';

          MoveCopyActivityAction.doAction($scope, { operation: 'move' });

          modalOpenCall = dialogServiceMock.open.calls.mostRecent().args;
          model = modalOpenCall[2];
        });

        it('displays the modal title as "Move Meeting Activity"', () => {
          expect(modalOpenCall[3].title).toBe('Move Meeting Activity');
        });

        describe('the model', () => {
          it('defines the case id the same as the selected activity', () => {
            expect(model.case_id).toBe($scope.selectedActivities[0].case_id);
          });

          it('displays the subject', () => {
            expect(model.isSubjectVisible).toBe(true);
          });

          it('defines an empty subject', () => {
            expect(model.subject).toBe($scope.selectedActivities[0].subject);
          });
        });

        describe('when fetching list of cases', () => {
          let getCaseListApiParams;

          beforeEach(() => {
            getCaseListApiParams = modalOpenCall[2].getCaseListApiParams;
          });

          it('displays cases from those case type categories for which user has "basic case information" permission', () => {
            expect(getCaseListApiParams()).toEqual({});
          });
        });

        describe('when saving the move action modal', () => {
          let expectedActivitySavingCalls;

          beforeEach(() => {
            const saveMethod = modalOpenCall[3].buttons[0].click;
            model.case_id = _.uniqueId();
            model.subject = 'a sample subject';
            expectedActivitySavingCalls = [['Activity', 'movebyquery', {
              case_id: model.case_id,
              subject: model.subject,
              id: $scope.selectedActivities.map((activity) => {
                return activity.id;
              })
            }]];

            crmApiMock.and.returnValue($q.resolve([{ values: $scope.selectedActivities }]));
            saveMethod();
            $rootScope.$digest();
          });

          it('moves the activity and assigns it to the selected case using the new activity subject', () => {
            expect(crmApiMock.calls.mostRecent().args[0]).toEqual(expectedActivitySavingCalls);
          });
        });
      });
    });
  });
})(CRM._, CRM.$);
