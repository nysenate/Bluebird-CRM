((_, angular) => {
  describe('WorkflowListFilters Provider', () => {
    let WorkflowListFilters;
    const testFilters = [
      {
        filterIdentifier: 'Test1',
        defaultValue: '',
        onlyVisibleForInstance: 'test',
        templateUrl: '~/test.html'
      },
      {
        filterIdentifier: 'Test2',
        defaultValue: '',
        onlyVisibleForInstance: 'test2',
        templateUrl: '~/test2.html'
      }
    ];

    describe('when no filters have been added', () => {
      beforeEach(() => {
        module('civicase-base');
        injectDependencies();
      });

      it('returns an empty array', () => {
        expect(WorkflowListFilters).toEqual([]);
      });
    });

    describe('when adding new filters', () => {
      let expectedFilters;

      beforeEach(() => {
        initSpyModule();
        module('civicase.spy', 'civicase-base');
        injectDependencies();

        expectedFilters = testFilters;
      });

      it('displays the newly added filters', () => {
        expect(WorkflowListFilters).toEqual(expectedFilters);
      });
    });

    /**
     * Initialises a spy module by hoisting the filters provider.
     */
    function initSpyModule () {
      angular.module('civicase.spy', ['civicase-base'])
        .config((WorkflowListFiltersProvider) => {
          WorkflowListFiltersProvider.addItems(testFilters);
        });
    }

    /**
     * Injects and hoists the dependencies needed by this spec.
     */
    function injectDependencies () {
      inject((_WorkflowListFilters_) => {
        WorkflowListFilters = _WorkflowListFilters_;
      });
    }
  });
})(CRM._, angular);
