(() => {
  describe('Dashboard Action Buttons', () => {
    let DashboardActionItemsProvider;

    beforeEach(() => {
      module('civicase-base', (_DashboardActionItemsProvider_) => {
        DashboardActionItemsProvider = _DashboardActionItemsProvider_;

        spyOn(DashboardActionItemsProvider, 'addItems');
      });

      module('civicase');
      inject();
    });

    describe('when the dashboard configurations runs', () => {
      it('adds the Add Case action button', () => {
        expect(DashboardActionItemsProvider.addItems)
          .toHaveBeenCalledWith(jasmine.arrayContaining([{
            templateUrl: '~/civicase/dashboard/directives/add-case-dashboard-action-button.html',
            weight: 0
          }]));
      });
    });
  });
})();
