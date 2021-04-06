(function (_, angular) {
  describe('DashboardCaseTypeItems', () => {
    let DashboardCaseTypeItems, DashboardCaseTypeItemsProvider;

    beforeEach(() => {
      initSpyModule('civicase.spy', ['civicase']);
      module('civicase', 'civicase.data', 'civicase.spy');
    });

    beforeEach(inject((_DashboardCaseTypeItems_) => {
      DashboardCaseTypeItems = _DashboardCaseTypeItems_;
    }));

    describe('when no buttons have been defined', () => {
      it('returns an empty object', () => {
        expect(DashboardCaseTypeItems).toEqual({});
      });
    });

    describe('when adding buttons to case types', () => {
      beforeEach(() => {
        DashboardCaseTypeItemsProvider.addItems('housing_support', [{
          templateUrl: '~/civicase/mock-button-template.html'
        }]);
        DashboardCaseTypeItemsProvider.addItems('adult_day_care_referral', [{
          templateUrl: '~/civicase/mock-button-template.html'
        }]);
      });

      it('adds the corresponding button to housing support', () => {
        expect(DashboardCaseTypeItems.housing_support).toEqual([{
          templateUrl: '~/civicase/mock-button-template.html'
        }]);
      });

      it('adds the corresponding button to adult day care referral', () => {
        expect(DashboardCaseTypeItems.adult_day_care_referral).toEqual([{
          templateUrl: '~/civicase/mock-button-template.html'
        }]);
      });
    });

    /**
     * Initialises the spy module that hoists the Case Types provider.
     *
     * @param {string} spyModuleName the name for the spy module
     * @param {string[]} spyModuleRequirements a list of required modules for the spy module
     */
    function initSpyModule (spyModuleName, spyModuleRequirements) {
      angular.module(spyModuleName, spyModuleRequirements)
        .config((_DashboardCaseTypeItemsProvider_) => {
          DashboardCaseTypeItemsProvider = _DashboardCaseTypeItemsProvider_;
        });
    }
  });
})(CRM._, angular);
