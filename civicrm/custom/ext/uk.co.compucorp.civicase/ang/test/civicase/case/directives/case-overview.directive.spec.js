/* eslint-env jasmine */
(($, _) => {
  describe('CaseOverview', () => {
    let $compile, $provide, $q, $rootScope, $scope, BrowserCache,
      CasesOverviewStats, crmApi, element, targetElementScope,
      CaseStatus, CaseType, CaseTypeFilterer;

    beforeEach(module('civicase.data', 'civicase', 'civicase.templates', (_$provide_) => {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_$compile_, _$q_, _$rootScope_, BrowserCacheMock,
      _crmApi_, _CasesOverviewStatsData_, _CaseStatus_, _CaseType_, _CaseTypeFilterer_) {
      $compile = _$compile_;
      $q = _$q_;
      $rootScope = _$rootScope_;
      $scope = $rootScope.$new();
      crmApi = _crmApi_;
      CasesOverviewStats = _CasesOverviewStatsData_.get();
      BrowserCache = BrowserCacheMock;
      CaseStatus = _CaseStatus_;
      CaseType = _CaseType_;
      CaseTypeFilterer = _CaseTypeFilterer_;

      BrowserCache.get.and.returnValue([1, 3]);
      $provide.value('BrowserCache', BrowserCache);
      crmApi.and.returnValue($q.resolve([CasesOverviewStats]));
      spyOn(CaseTypeFilterer, 'filter').and.callThrough();
    }));

    beforeEach(() => {
      $scope.caseStatuses = CaseStatus.getAll();
      $scope.summaryData = [];
    });

    beforeEach(() => {
      listenForCaseOverviewRecalculate();
      compileDirective({});
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
          case_type_category: 'Cases',
          id: { IN: ['1', '2'] }
        };

        crmApi.and.returnValue($q.resolve([CasesOverviewStats]));
        expectedCaseTypes = CaseTypeFilterer.filter(expectedFilters);
        CaseTypeFilterer.filter.calls.reset();
        compileDirective({
          caseTypeCategory: 'Cases',
          caseTypeID: { IN: ['1', '2'] }
        });
      });

      it('passes the filter parameters to the case type filterer', () => {
        expect(CaseTypeFilterer.filter).toHaveBeenCalledWith(expectedFilters);
      });

      it('stores the filtered case types', () => {
        expect(element.isolateScope().caseTypes).toEqual(expectedCaseTypes);
      });
    });

    describe('Case Status Data', () => {
      beforeEach(() => {
        crmApi.and.returnValue($q.resolve([CasesOverviewStats]));
        compileDirective({
          caseTypeCategory: 'Cases',
          caseTypeID: { IN: ['1', '2'] },
          status_id: '1'
        });
      });

      it('fetches the case statistics, but shows all case statuses', () => {
        expect(crmApi).toHaveBeenCalledWith([['Case', 'getstats', {
          'case_type_id.case_type_category': 'Cases',
          case_type_id: { IN: ['1', '2'] }
        }]]);
      });
    });

    describe('Case Statuses', () => {
      let expectedCaseStatuses;

      describe('when loading a subset of case types', () => {
        beforeEach(() => {
          const sampleCaseStatuses = _.sample(CaseStatus.getAll(), 2);
          const sampleCaseTypes = _.sample(CaseType.getAll(), 2);

          sampleCaseTypes[0].definition.statuses = [sampleCaseStatuses[0].name];
          sampleCaseTypes[1].definition.statuses = [sampleCaseStatuses[1].name];

          expectedCaseStatuses = _.chain(sampleCaseStatuses)
            .sortBy('weight')
            .indexBy('value')
            .value();

          crmApi.and.callFake((entity) => {
            const response = entity === 'CaseType'
              ? { values: sampleCaseTypes }
              : [CasesOverviewStats];

            return $q.resolve(response);
          });

          compileDirective({});
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

          crmApi.and.callFake((entity) => {
            const response = entity === 'CaseType'
              ? { values: [caseType] }
              : [CasesOverviewStats];

            return $q.resolve(response);
          });

          compileDirective({});
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

    /**
     * Initialise directive.
     *
     * @param {string} params the case type category name.
     */
    function compileDirective (params) {
      $scope.caseFilter = {
        'case_type_id.case_type_category': params.caseTypeCategory,
        case_type_id: params.caseTypeID
      };
      element = $compile('<civicase-case-overview case-filter="caseFilter"></civicase-case-overview>')($scope);
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
