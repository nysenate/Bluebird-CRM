/* eslint-env jasmine */

((_) => {
  describe('civicaseActivityActions', () => {
    describe('Activity Actions Controller', () => {
      var $controller, $rootScope, $scope, ResumeDraftActivityActionMock;

      beforeEach(module('civicase', ($provide) => {
        ResumeDraftActivityActionMock = jasmine.createSpyObj('action', ['isActionEnabled', 'doAction']);
        $provide.value('ResumeDraftActivityAction', ResumeDraftActivityActionMock);
      }));

      beforeEach(inject((_$controller_, _$rootScope_) => {
        $controller = _$controller_;
        $rootScope = _$rootScope_;

        $scope = $rootScope.$new();
      }));

      describe('basic actions', () => {
        beforeEach(() => {
          initController();
        });

        it('fetches all activity actions', () => {
          expect($scope.activityActions.length).toEqual(10);
        });
      });

      describe('when selecting activity custom actions', () => {
        beforeEach(() => {
          $scope.selectedActivities = [{
            'api.Activity.getactionlinks': [
              { name: 1, icon: 'icon' },
              { name: 2, icon: 'icon2' }
            ]
          }];
          initController();
        });

        it('fetches all custom actions for the activity', () => {
          expect($scope.customActionsForActivity).toEqual([
            { name: 1, icon: 'filter_none' },
            { name: 2, icon: 'filter_none' }
          ]);
        });
      });

      describe('isActionEnabled', () => {
        beforeEach(() => {
          initController();
          $scope.isActionEnabled({ name: 'ResumeDraft' });
        });

        it('calls the respective service to fetch if the action is enabled', () => {
          expect(ResumeDraftActivityActionMock.isActionEnabled).toHaveBeenCalledWith($scope);
        });
      });

      describe('doAction', () => {
        beforeEach(() => {
          initController();
          $scope.doAction({ name: 'ResumeDraft' });
        });

        it('executes the actions defined in respective service', () => {
          expect(ResumeDraftActivityActionMock.doAction).toHaveBeenCalledWith($scope, { name: 'ResumeDraft' });
        });
      });

      /**
       * Initializes the controller.
       */
      function initController () {
        $controller('civicaseActivityActionsController', {
          $scope: $scope
        });
      }
    });
  });
})(CRM._);
