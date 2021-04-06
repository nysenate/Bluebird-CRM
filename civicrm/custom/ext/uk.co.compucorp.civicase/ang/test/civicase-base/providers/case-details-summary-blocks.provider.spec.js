((_, angular) => {
  describe('CaseDetailsSummaryBlocks Provider', () => {
    let CaseDetailsSummaryBlocks;

    const testCaseDetailsSummaryBlocks = [
      {
        templateUrl: '~/civicase/case/details/summary-tab/case-details-summary-next-milestone.html',
        weight: 2
      }, {
        templateUrl: '~/civicase/case/details/summary-tab/case-details-summary-next-non-milestone-activity.html',
        weight: 1
      }, {
        templateUrl: '~/civicase/case/details/summary-tab/case-details-summary-calendar.html',
        weight: 0
      }
    ];

    describe('when no blocks have been added', () => {
      beforeEach(() => {
        module('civicase-base');
        injectDependencies();
      });

      it('returns an empty array', () => {
        expect(CaseDetailsSummaryBlocks).toEqual([]);
      });
    });

    describe('when adding new blocks', () => {
      let expectedBlocks;

      beforeEach(() => {
        initSpyModule();
        module('civicase.spy', 'civicase-base');
        injectDependencies();

        expectedBlocks = getExpectedBlocks();
      });

      it('displays the newly added blocks sorted by weight', () => {
        expect(CaseDetailsSummaryBlocks).toEqual(expectedBlocks);
      });
    });

    /**
     * Returns the blocks as expected by the spec:
     *  - sorted by weight
     *
     * The function also supports adding extra blocks to the list.
     *
     * @returns {object[]} a list of expected blocks.
     */
    function getExpectedBlocks () {
      return _.sortBy(testCaseDetailsSummaryBlocks, 'weight');
    }

    /**
     * Initialises a spy module by hoisting the case details summary blocks
     * provider
     */
    function initSpyModule () {
      angular.module('civicase.spy', ['civicase-base'])
        .config((CaseDetailsSummaryBlocksProvider) => {
          CaseDetailsSummaryBlocksProvider.addItems(testCaseDetailsSummaryBlocks);
        });
    }

    /**
     * Injects and hoists the dependencies needed by this spec.
     */
    function injectDependencies () {
      inject((_CaseDetailsSummaryBlocks_) => {
        CaseDetailsSummaryBlocks = _CaseDetailsSummaryBlocks_;
      });
    }
  });
})(CRM._, angular);
