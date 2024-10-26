(function (_, angular) {
  describe('DashboardActionItems', () => {
    let DashboardActionItems, DashboardActionItemsProvider;

    beforeEach(() => {
      initSpyModule();
      module('civicase-base', 'civicase.data', 'civicase.spy');

      // This will initialise the modules:
      inject();
    });

    describe('when no items have been defined', () => {
      beforeEach(() => {
        injectDependencies();
      });

      it('returns an empty array', () => {
        expect(DashboardActionItems).toEqual([]);
      });
    });

    describe('when adding action items to the dashboard', () => {
      const actionItem = {
        templateUrl: 'abc.html',
        weight: 3
      };
      const actionItem2 = {
        templateUrl: 'efg.html',
        weight: 1
      };

      beforeEach(() => {
        DashboardActionItemsProvider.addItems([
          actionItem,
          actionItem2
        ]);
        injectDependencies();
      });

      it('adds the action item sorted by order', () => {
        expect(DashboardActionItems)
          .toEqual([actionItem2, actionItem]);
      });
    });

    /**
     * Injects and hoists all the dependencies needed by the spec file.
     */
    function injectDependencies () {
      inject((_DashboardActionItems_) => {
        DashboardActionItems = _DashboardActionItems_;
      });
    }

    /**
     * Initialises the spy module that hoists the Dashboard Action Items provider.
     */
    function initSpyModule () {
      angular.module('civicase.spy', ['civicase-base'])
        .config((_DashboardActionItemsProvider_) => {
          DashboardActionItemsProvider = _DashboardActionItemsProvider_;
        });
    }
  });
})(CRM._, angular);
