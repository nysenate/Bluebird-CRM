((_, angular) => {
  describe('CaseDetailsTabs Provider', () => {
    let $injector, CaseDetailsTabs;
    const testCaseTabs = [
      {
        name: 'Test1',
        label: 'Test 1',
        weight: 2
      },
      {
        name: 'Test2',
        label: 'Test 2',
        weight: 1
      },
      {
        name: 'Test3',
        label: 'Test 3',
        weight: 3
      }
    ];

    describe('when no case tabs have been added', () => {
      beforeEach(() => {
        module('civicase-base');
        injectDependencies();
      });

      it('returns an empty array', () => {
        expect(CaseDetailsTabs).toEqual([]);
      });
    });

    describe('when adding new case tabs', () => {
      let expectedCaseTabs;

      beforeEach(() => {
        initSpyModule();
        module('civicase.spy', 'civicase-base');
        injectDependencies();

        expectedCaseTabs = getExpectedCaseTabs();
      });

      it('displays the newly added tab sorted by weight', () => {
        expect(CaseDetailsTabs).toEqual(expectedCaseTabs);
      });
    });

    /**
     * Returns the case tabs as expected by the spec:
     *  - sorted by weight
     *  - including their service
     *
     * The function also supports adding extra case tabs to the list.
     *
     * @property {object[]} extraCaseTabs a list of case tabs to add.
     * @returns {object[]} a list of expected case tabs.
     */
    function getExpectedCaseTabs () {
      return _.chain(testCaseTabs)
        .sortBy('weight')
        .map((caseTab) => {
          const caseTabService = $injector.get(`${caseTab.name}CaseTab`);

          return _.extend({}, caseTab, {
            service: caseTabService
          });
        })
        .value();
    }

    /**
     * Initialises a spy module by hoisting the case details tabs provider
     * and adding a mock TestCaseTab service.
     */
    function initSpyModule () {
      angular.module('civicase.spy', ['civicase-base'])
        .config((CaseDetailsTabsProvider) => {
          CaseDetailsTabsProvider.addTabs(testCaseTabs);
        })
        .service('Test1CaseTab', TestCaseTab)
        .service('Test2CaseTab', TestCaseTab)
        .service('Test3CaseTab', TestCaseTab);
    }

    /**
     * Injects and hoists the dependencies needed by this spec.
     */
    function injectDependencies () {
      inject((_$injector_, _CaseDetailsTabs_) => {
        $injector = _$injector_;
        CaseDetailsTabs = _CaseDetailsTabs_;
      });
    }

    /**
     * Mock case tab service.
     */
    function TestCaseTab () {
      this.activeTabContentUrl = _.noop;
    }
  });
})(CRM._, angular);
