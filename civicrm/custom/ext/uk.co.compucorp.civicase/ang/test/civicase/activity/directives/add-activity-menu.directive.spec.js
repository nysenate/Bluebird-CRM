/* eslint-env jasmine */

(function (_) {
  describe('AddActivityMenu', function () {
    describe('Add Activity Menu Controller', function () {
      var $controller, $rootScope, $scope, CaseType;

      beforeEach(module('civicase', 'civicase.data'));

      beforeEach(inject(function (_$controller_, _$rootScope_, _CaseType_, _ActivityType_) {
        $controller = _$controller_;
        $rootScope = _$rootScope_;
        CaseType = _CaseType_;
      }));

      describe('activity menu', function () {
        var activityTypeWithMaxInstance, activityTypeExceedingMaxInstanceIsHidden;

        beforeEach(function () {
          var activityTypes = CaseType.getById(1).definition.activityTypes;
          activityTypeWithMaxInstance = activityTypes.find(function (activity) {
            return activity.max_instances;
          });
          var mockCase = {
            case_type_id: 1
          };

          initController(mockCase);

          activityTypeExceedingMaxInstanceIsHidden = !_.find($scope.availableActivityTypes, function (activityType) {
            return activityType.name === activityTypeWithMaxInstance.name;
          });
        });

        it('hides activity types exceeding max instance', function () {
          expect(activityTypeExceedingMaxInstanceIsHidden).toBe(true);
        });
      });

      /**
       * Initializes the add activity menu controller.
       *
       * @param {object} caseData a sample case to pass to the controller.
       */
      function initController (caseData) {
        $scope = $rootScope.$new();
        $scope.case = caseData;

        $controller('civicaseAddActivityMenuController', {
          $scope: $scope
        });
      }
    });
  });
})(CRM._);
