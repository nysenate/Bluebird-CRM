(() => {
  describe('workflow list', () => {
    let $q, $controller, $rootScope, $scope, $window, CaseTypesMockData,
      mockGotoFn, mockApiCalls, crmApiMock, crmApiReceievedInCaseTypeCtrl,
      apiCallsReceievedInCaseTypeCtrl;

    beforeEach(module('workflow.mock', 'workflow', 'civicase.data', ($provide, $controllerProvider) => {
      crmApiMock = jasmine.createSpy('crmApi');
      $provide.value('crmApi', crmApiMock);

      mockGotoFn = jasmine.createSpy('goto');
      $controllerProvider.register('CaseTypeCtrl', function ($scope, crmApi, apiCalls) {
        $scope.goto = mockGotoFn;
        crmApiReceievedInCaseTypeCtrl = crmApi;
        apiCallsReceievedInCaseTypeCtrl = apiCalls;
      });

      $provide.value('$window', { location: {} });

      mockApiCalls = ['value1', 'value2'];
    }));

    beforeEach(inject((_$q_, _$controller_, _$rootScope_, _$window_,
      _CaseTypesMockData_) => {
      $q = _$q_;
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      $window = _$window_;
      CaseTypesMockData = _CaseTypesMockData_;

      var apiReturnValue = {
        id: CaseTypesMockData.getSequential()[1].id,
        values: [CaseTypesMockData.getSequential()[1]],
        is_error: '0'
      };
      crmApiMock.and.returnValue($q.resolve(apiReturnValue));
    }));

    describe('basic tests', () => {
      beforeEach(() => {
        initController();

        crmApiMock('CaseType', 'create', {});
        $rootScope.$digest();
      });

      it('extends case type controller from core', () => {
        expect(apiCallsReceievedInCaseTypeCtrl).toEqual(mockApiCalls);
      });

      describe('when clicked on Save button', () => {
        describe('and case type is being saved', () => {
          beforeEach(() => {
            crmApiReceievedInCaseTypeCtrl('CaseType', 'create', {});

            $rootScope.$digest();
          });

          it('saves the case type succesfully', () => {
            expect($scope.caseType.id).toBe(CaseTypesMockData.getSequential()[1].id);
          });

          it('redirects the user the workflow list page', () => {
            expect($window.location.href).toBe('/civicrm/workflow/a?case_type_category=Prospecting#/list');
          });
        });

        describe('and other api calls are made', () => {
          beforeEach(() => {
            crmApiReceievedInCaseTypeCtrl('Contact', 'create', {}, 'message');

            $rootScope.$digest();
          });

          it('calls the backend api in usual manner', () => {
            expect(crmApiMock).toHaveBeenCalledWith('Contact', 'create', {}, 'message');
          });
        });
      });

      describe('when clicking on cancel button', () => {
        describe('when creating a new workflow', () => {
          beforeEach(() => {
            $scope.caseType = {};
            $scope.goto();
          });

          it('redirects to the core case type list screen', () => {
            expect($window.location.href).toBe('/civicrm/a/#/caseType');
          });
        });

        describe('when editing an existing workflow', () => {
          beforeEach(() => {
            $scope.caseType = CaseTypesMockData.getSequential()[0];
            $scope.goto();
          });

          it('redirects to the core case type list screen', () => {
            expect($window.location.href).toBe('/civicrm/workflow/a?case_type_category=Cases#/list');
          });
        });
      });
    });

    /**
     * Initializes the CivicaseCaseTypeController.
     */
    function initController () {
      $scope = $rootScope.$new();
      $scope.caseType = CaseTypesMockData.getSequential()[0];

      $controller('CivicaseCaseTypeController', {
        $scope,
        apiCalls: mockApiCalls
      });
    }
  });
})();
