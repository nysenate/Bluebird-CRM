(function (_) {
  describe('AddActivityMenu', function () {
    describe('Add Activity Menu Controller', function () {
      var $controller, $rootScope, $scope, ActivityForms, activityForm, CaseType,
        mockCase;

      beforeEach(module('civicase', 'civicase.data', ($provide) => {
        ActivityForms = jasmine.createSpyObj('ActivityForms', ['getActivityFormService']);
        activityForm = jasmine.createSpyObj('activityForm', ['getActivityFormUrl']);

        ActivityForms.getActivityFormService.and.returnValue(activityForm);
        activityForm.getActivityFormUrl.and.returnValue('/activity-form-url');

        $provide.value('ActivityForms', ActivityForms);
      }));

      beforeEach(inject(function (_$controller_, _$rootScope_, _CaseType_) {
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
          mockCase = {
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

      describe('new activity URL', () => {
        beforeEach(() => {
          mockCase = {
            id: _.uniqueId(),
            case_type_id: '1',
            client: [{ contact_id: _.uniqueId() }]
          };

          initController(mockCase);
        });

        describe('when getting the form URL for a particular activity type', () => {
          let returnedUrl, activityType;

          beforeEach(() => {
            activityType = {
              id: _.uniqueId(),
              name: 'sample-activity-type'
            };
            returnedUrl = $scope.newActivityUrl(activityType);
          });

          it('requests the appropriate activity form service', () => {
            expect(ActivityForms.getActivityFormService).toHaveBeenCalledWith(
              {
                activity_type_id: activityType.id,
                case_id: $scope.case.id,
                type: activityType.name
              },
              {
                action: 'add',
                civicase_reload: jasmine.any(String),
                cid: _.first(mockCase.client).contact_id
              }
            );
          });

          it('requests the activity type form URL', () => {
            expect(activityForm.getActivityFormUrl).toHaveBeenCalledWith(
              {
                activity_type_id: activityType.id,
                case_id: $scope.case.id,
                type: activityType.name
              },
              {
                action: 'add',
                civicase_reload: jasmine.any(String),
                cid: _.first(mockCase.client).contact_id
              }
            );
          });

          it('returns the URL for the acitivity type form', () => {
            expect(returnedUrl).toBe('/activity-form-url');
          });
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
