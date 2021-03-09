/* eslint-env jasmine */
(($, _) => {
  describe('CaseOverview', () => {
    let $compile, $provide, $q, $rootScope, $scope, BrowserCache,
      CasesOverviewStats, civicaseCrmApi, element, targetElementScope,
      CaseStatus, CaseType, CaseTypesMockData, CaseManagementWorkflow;

    beforeEach(module('civicase.data', 'civicase', 'civicase.templates', (_$provide_) => {
      $provide = _$provide_;

      $provide.value('civicaseCrmApi', jasmine.createSpy('civicaseCrmApi'));
    }));

    beforeEach(inject(function (_$compile_, _$q_, _$rootScope_, BrowserCacheMock,
      _civicaseCrmApi_, _CasesOverviewStatsData_, _CaseStatus_, _CaseType_,
      _CaseTypesMockData_, _CaseManagementWorkflow_) {
      $compile = _$compile_;
      $q = _$q_;
      $rootScope = _$rootScope_;
      $scope = $rootScope.$new();
      civicaseCrmApi = _civicaseCrmApi_;
      CasesOverviewStats = _CasesOverviewStatsData_.get();
      BrowserCache = BrowserCacheMock;
      CaseStatus = _CaseStatus_;
      CaseType = _CaseType_;
      CaseTypesMockData = _CaseTypesMockData_;
      CaseManagementWorkflow = _CaseManagementWorkflow_;

      BrowserCache.get.and.returnValue([1, 3]);
      $provide.value('BrowserCache', BrowserCache);
      civicaseCrmApi.and.returnValue($q.resolve([CasesOverviewStats]));

      spyOn(CaseManagementWorkflow, 'getWorkflowsListForCaseOverview');
      CaseManagementWorkflow.getWorkflowsListForCaseOverview.and.returnValue($q.resolve({
        values: CaseTypesMockData.getSequential(),
        count: CaseTypesMockData.getSequential().length
      }));
    }));

    beforeEach(() => {
      $scope.caseStatuses = CaseStatus.getAll();
      $scope.summaryData = [];
    });

    beforeEach(() => {
      listenForCaseOverviewRecalculate();
      compileDirective({ caseTypeCategory: 'Cases' });
    });

    describe('compile directive', () => {
      it('should have class civicase__case-overview-container', () => {
        expect(element.html()).toContain('civicase__case-overview-container');
      });
    });

    describe('Case Types', () => {
      let expectedCaseTypes, expectedFilters;

      beforeEach(() => {
        expectedFilters = {
          'case_type_id.case_type_category': 'Cases'
        };
        expectedCaseTypes = CaseTypesMockData.getSequential();

        civicaseCrmApi.and.returnValue($q.resolve([CasesOverviewStats]));
        compileDirective({
          caseTypeCategory: 'Cases'
        });

        $rootScope.$digest();
      });

      it('passes the filter parameters to the case type filterer', () => {
        expect(civicaseCrmApi).toHaveBeenCalledWith([['Case', 'getstats', expectedFilters]]);
      });

      it('stores the filtered case types', () => {
        expect(angular.copy(element.isolateScope().caseTypes)).toEqual(angular.copy(expectedCaseTypes));
      });

      it('shows pagination', () => {
        expect(element.isolateScope().totalCount).toBe(CaseTypesMockData.getSequential().length);
        expect(element.isolateScope().pageObj.total).toBe(1);
      });
    });

    describe('Case Status Data', () => {
      beforeEach(() => {
        civicaseCrmApi.and.returnValue($q.resolve([CasesOverviewStats]));
        compileDirective({
          caseTypeCategory: 'Cases',
          status_id: '1'
        });
      });

      it('fetches the case statistics, but shows all case statuses', () => {
        expect(civicaseCrmApi).toHaveBeenCalledWith([['Case', 'getstats', {
          'case_type_id.case_type_category': 'Cases'
        }]]);
      });
    });

    describe('Case Statuses', () => {
      let expectedCaseStatuses;

      describe('when loading a subset of case types', () => {
        beforeEach(() => {
          const sampleCaseStatuses = _.sample(CaseStatus.getAll(), 2);
          const sampleCaseTypes = _.sample(CaseType.getAll(), 3);

          sampleCaseTypes[0].definition.statuses = [sampleCaseStatuses[0].name];
          sampleCaseTypes[1].definition.statuses = [sampleCaseStatuses[1].name];
          sampleCaseTypes[2].definition.statuses = [sampleCaseStatuses[1].name];

          expectedCaseStatuses = _.chain(sampleCaseStatuses)
            .sortBy('weight')
            .indexBy('value')
            .value();

          CaseManagementWorkflow.getWorkflowsListForCaseOverview.and.returnValue($q.resolve({
            values: sampleCaseTypes,
            count: sampleCaseTypes.length
          }));

          compileDirective({ caseTypeCategory: 'Cases' });

          $rootScope.$digest();
        });

        it('only displays the case statuses belonging to the case types subset', () => {
          expect(element.isolateScope().caseStatuses).toEqual(expectedCaseStatuses);
        });
      });

      describe('when loading a case type that supports all statuses', () => {
        beforeEach(() => {
          const allCaseStatuses = CaseStatus.getAll();
          const caseType = _.sample(CaseType.getAll());

          delete caseType.definition.statuses;

          expectedCaseStatuses = _.chain(allCaseStatuses)
            .sortBy('weight')
            .indexBy('value')
            .value();

          CaseManagementWorkflow.getWorkflowsListForCaseOverview.and.returnValue($q.resolve({
            values: [caseType],
            count: 1
          }));

          compileDirective({ caseTypeCategory: 'Cases' });

          $rootScope.$digest();
        });

        it('only displays all case statuses', () => {
          expect(element.isolateScope().caseStatuses).toEqual(expectedCaseStatuses);
        });
      });
    });

    describe('Case Status visibility', () => {
      describe('when the component loads', () => {
        it('requests the case status that are hidden stored in the browser cache', () => {
          expect(BrowserCache.get).toHaveBeenCalledWith('civicase.CaseOverview.hiddenCaseStatuses', []);
        });

        it('hides the case statuses marked as hidden by the browser cache', () => {
          expect(element.isolateScope().hiddenCaseStatuses).toEqual({
            1: true,
            3: true
          });
        });
      });

      describe('when marking a status as hidden', () => {
        beforeEach(() => {
          element.isolateScope().hiddenCaseStatuses = {
            1: true,
            2: false,
            3: true
          };

          element.isolateScope().toggleStatusVisibility($.Event(), 2);
        });

        it('stores the hidden case statuses including the new one', () => {
          expect(BrowserCache.set).toHaveBeenCalledWith('civicase.CaseOverview.hiddenCaseStatuses', ['1', '2', '3']);
        });
      });

      describe('when marking a status as enabled', () => {
        beforeEach(() => {
          element.isolateScope().hiddenCaseStatuses = {
            1: true,
            2: false,
            3: true
          };

          element.isolateScope().toggleStatusVisibility($.Event(), 1);
        });

        it('stores the hidden case statuses including the new one', () => {
          expect(BrowserCache.set).toHaveBeenCalledWith('civicase.CaseOverview.hiddenCaseStatuses', ['3']);
        });
      });
    });

    describe('when showBreakdown is false', () => {
      beforeEach(() => {
        element.isolateScope().showBreakdown = false;
      });

      describe('when toggleBreakdownVisibility is called', () => {
        beforeEach(() => {
          element.isolateScope().toggleBreakdownVisibility();
        });

        it('resets showBreakdown to true', () => {
          expect(element.isolateScope().showBreakdown).toBe(true);
        });
      });
    });

    describe('when showBreakdown is true', () => {
      beforeEach(() => {
        element.isolateScope().showBreakdown = true;
      });

      describe('when toggleBreakdownVisibility is called', () => {
        beforeEach(() => {
          element.isolateScope().toggleBreakdownVisibility();
        });

        it('resets showBreakdown to false', () => {
          expect(element.isolateScope().showBreakdown).toBe(false);
        });
      });
    });

    describe('showBreakdown watcher', () => {
      it('emit called and targetElementScope to be defined', () => {
        expect(targetElementScope).toEqual(element.isolateScope());
      });
    });

    describe('when using pagination', () => {
      beforeEach(() => {
        element.isolateScope().setPageTo(5);
      });

      it('displays the content for the clicked page', () => {
        expect(element.isolateScope().pageObj.num).toBe(5);
        expect(CaseManagementWorkflow.getWorkflowsListForCaseOverview).toHaveBeenCalled();
      });
    });

    /**
     * Initialise directive.
     *
     * @param {string} params the case type category name.
     */
    function compileDirective (params) {
      $scope.caseFilter = {
        'case_type_id.case_type_category': params.caseTypeCategory
      };

      element = $compile(`
        <civicase-case-overview
          case-filter="caseFilter"
          current-case-category="currentCaseCategory"
        ></civicase-case-overview>`)($scope);
      $scope.$digest();
    }

    /**
     * Listen for `civicase::custom-scrollbar::recalculate` event
     */
    function listenForCaseOverviewRecalculate () {
      $rootScope.$on('civicase::custom-scrollbar::recalculate', (event) => {
        targetElementScope = event.targetScope;
      });
    }
  });
})(CRM.$, CRM._);
