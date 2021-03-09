/* eslint-env jasmine */

((_) => {
  describe('workflow list', () => {
    let $q, $controller, $rootScope, $scope, CaseTypesMockData,
      civicaseCrmApiMock, WorkflowListActionItems, CaseManagementWorkflow,
      WorkflowListColumns;

    const testFilters = [
      {
        filterIdentifier: 'test1',
        defaultValue: true,
        onlyVisibleForInstance: 'case_management',
        templateUrl: '~/test.html'
      },
      {
        filterIdentifier: 'test2',
        defaultValue: '',
        onlyVisibleForInstance: 'applicant_management',
        templateUrl: '~/test2.html'
      }
    ];

    const applicantManagementActionItem = {
      templateUrl: '~/test-action.html',
      onlyVisibleForInstance: 'applicant_management',
      weight: 1
    };

    describe('basic tests', () => {
      beforeEach(() => {
        injectModulesAndDependencies();
        CaseManagementWorkflow.getWorkflowsListForManageWorkflow.and.returnValue($q.resolve({
          values: CaseTypesMockData.getSequential(),
          count: CaseTypesMockData.getSequential().length
        }));
        initController();
      });

      it('hides the empty message before case types are loaded', () => {
        expect($scope.isLoading).toBe(true);
      });

      describe('after case types are loaded', () => {
        beforeEach(() => {
          $scope.$digest();
        });

        it('displays the first 25 records', () => {
          expect($scope.pageObj).toEqual({ total: 1, size: 25, num: 1 });
          expect($scope.totalCount).toBe(CaseTypesMockData.getSequential().length);
        });

        it('shows the results after case types are loaded', () => {
          expect($scope.isLoading).toBe(false);
        });

        it('displays the list of fetched workflows', () => {
          expect($scope.workflows).toEqual(CaseTypesMockData.getSequential());
        });

        describe('action items', () => {
          var expectedActionItems;

          beforeEach(() => {
            expectedActionItems = _.filter(WorkflowListActionItems, function (actionItem) {
              return actionItem.onlyVisibleForInstance !== 'applicant_management';
            });
          });

          it('displays the action items only meant for current instance', () => {
            expect($scope.actionItems).toEqual(expectedActionItems);
          });
        });

        it('displays the columns', () => {
          expect($scope.tableColumns).toEqual(WorkflowListColumns);
        });

        it('displays the filters only meant for current instance', () => {
          expect($scope.filters).toEqual([testFilters[0]]);
        });

        it('displays the filters default values', () => {
          expect($scope.selectedFilters).toEqual({ test1: true });
        });
      });
    });

    describe('when list refresh event is fired', () => {
      beforeEach(() => {
        injectModulesAndDependencies();
        CaseManagementWorkflow.getWorkflowsListForManageWorkflow.and.returnValue($q.resolve({
          values: CaseTypesMockData.getSequential(),
          count: CaseTypesMockData.getSequential().length
        }));
        initController();
        $scope.pageObj = { total: 2, size: 25, num: 2 };
        $scope.$digest();
        $scope.workflows = [];
        $rootScope.$broadcast('workflow::list::refresh');
        $scope.$digest();
      });

      it('fetches the case types for the current case type category', () => {
        expect(CaseManagementWorkflow.getWorkflowsListForManageWorkflow).toHaveBeenCalled();
      });

      it('refreshes the workflows list', () => {
        expect($scope.workflows).toEqual(CaseTypesMockData.getSequential());
      });

      it('resets the pagination', () => {
        expect($scope.pageObj).toEqual({ total: 1, size: 25, num: 1 });
      });
    });

    describe('when clicking on New Workflow button', () => {
      beforeEach(() => {
        injectModulesAndDependencies();
        CaseManagementWorkflow.getWorkflowsListForManageWorkflow.and.returnValue($q.resolve(
          CaseTypesMockData.getSequential()
        ));
        spyOn(CaseManagementWorkflow, 'redirectToWorkflowCreationScreen');
        initController();

        $scope.redirectToWorkflowCreationScreen();
      });

      it('redirects to the create new workflow screen for the current instance', () => {
        expect(CaseManagementWorkflow.redirectToWorkflowCreationScreen).toHaveBeenCalled();
      });
    });

    /**
     * Initialises a spy module by hoisting the filters provider.
     */
    function initSpyModule () {
      angular.module('civicase.spy', ['civicase-base'])
        .config((WorkflowListFiltersProvider, WorkflowListActionItemsProvider) => {
          WorkflowListFiltersProvider.addItems(testFilters);
          WorkflowListActionItemsProvider.addItems([applicantManagementActionItem]);
        });
    }

    /**
     * Injects modules and dependencies.
     */
    function injectModulesAndDependencies () {
      initSpyModule();

      module('workflow', 'civicase.data', 'civicase.spy', ($provide) => {
        civicaseCrmApiMock = jasmine.createSpy('civicaseCrmApi');

        $provide.value('civicaseCrmApi', civicaseCrmApiMock);
      });

      inject((_$q_, _$controller_, _$rootScope_, _CaseTypesMockData_,
        _WorkflowListActionItems_, _CaseManagementWorkflow_,
        _WorkflowListColumns_) => {
        $q = _$q_;
        $controller = _$controller_;
        $rootScope = _$rootScope_;
        WorkflowListActionItems = _WorkflowListActionItems_;
        WorkflowListColumns = _WorkflowListColumns_;
        CaseTypesMockData = _CaseTypesMockData_;
        CaseManagementWorkflow = _CaseManagementWorkflow_;

        spyOn(CaseManagementWorkflow, 'getWorkflowsListForManageWorkflow');
      });
    }

    /**
     * Initializes the contact case tab case details controller.
     */
    function initController () {
      $scope = $rootScope.$new();
      $scope.caseTypeCategory = 'Cases';

      $controller('workflowListController', { $scope: $scope });
    }
  });
})(CRM._);
