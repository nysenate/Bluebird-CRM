/* eslint-env jasmine */
((_, angular) => {
  describe('WorkflowListColumns Provider', () => {
    let WorkflowListColumns;
    const testColumns = [
      {
        label: 'Test1',
        onlyVisibleForInstance: 'test',
        templateUrl: '~/test1.html'
      },
      {
        filterIdentifier: 'Test2',
        onlyVisibleForInstance: 'test2',
        templateUrl: '~/test2.html'
      }
    ];

    describe('when no columns have been added', () => {
      beforeEach(() => {
        module('civicase-base');
        injectDependencies();
      });

      it('returns an empty array', () => {
        expect(WorkflowListColumns).toEqual([]);
      });
    });

    describe('when adding new columns', () => {
      let expectedColumns;

      beforeEach(() => {
        initSpyModule();
        module('civicase.spy', 'civicase-base');
        injectDependencies();

        expectedColumns = testColumns;
      });

      it('displays the newly added columns', () => {
        expect(WorkflowListColumns).toEqual(expectedColumns);
      });
    });

    /**
     * Initialises a spy module by hoisting the columns provider.
     */
    function initSpyModule () {
      angular.module('civicase.spy', ['civicase-base'])
        .config((WorkflowListColumnsProvider) => {
          WorkflowListColumnsProvider.addItems(testColumns);
        });
    }

    /**
     * Injects and hoists the dependencies needed by this spec.
     */
    function injectDependencies () {
      inject((_WorkflowListColumns_) => {
        WorkflowListColumns = _WorkflowListColumns_;
      });
    }
  });
})(CRM._, angular);
