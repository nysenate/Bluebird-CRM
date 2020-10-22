/* eslint-env jasmine */
((_, angular) => {
  describe('CaseDetailsTabs', () => {
    let CaseDetailsTabsProvider;

    describe('when the case tabs are being configured', () => {
      beforeEach(module('civicase-base', spyOnCaseDetailsProvider, 'civicase'));

      beforeEach(inject);

      it('it adds the summary, activities, people, and files tabs', () => {
        expect(CaseDetailsTabsProvider.addTabs).toHaveBeenCalledWith(jasmine.arrayContaining([
          { name: 'Summary', label: ts('Summary'), weight: 1 },
          { name: 'Activities', label: ts('Activities'), weight: 2 },
          { name: 'People', label: ts('People'), weight: 3 },
          { name: 'Files', label: ts('Files'), weight: 4 }
        ]));
      });
    });

    describe('linked cases tab', () => {
      const LINKED_CASES_TAB = {
        name: 'LinkedCases',
        label: ts('Linked Cases'),
        weight: 5
      };

      describe('when the linked cases tab is enabled', () => {
        beforeEach(module('civicase-base', ($provide) => {
          $provide.constant('allowLinkedCasesTab', true);
        }));

        beforeEach(module(spyOnCaseDetailsProvider, 'civicase'));

        beforeEach(inject);

        it('includes the linked cases tab', () => {
          expect(CaseDetailsTabsProvider.addTabs)
            .toHaveBeenCalledWith(jasmine.arrayContaining([
              LINKED_CASES_TAB
            ]));
        });
      });

      describe('when the linked cases tab is not enabled', () => {
        beforeEach(module('civicase-base', ($provide) => {
          $provide.constant('allowLinkedCasesTab', false);
        }));

        beforeEach(module(spyOnCaseDetailsProvider, 'civicase'));

        beforeEach(inject);

        it('dooes not include the linked cases tab', () => {
          expect(CaseDetailsTabsProvider.addTabs)
            .not.toHaveBeenCalledWith(jasmine.arrayContaining([
              LINKED_CASES_TAB
            ]));
        });
      });
    });

    /**
     * Spies and hoists the case details tabs provider.
     *
     * @param {object} _CaseDetailsTabsProvider_ The case details tabs provider.
     */
    function spyOnCaseDetailsProvider (_CaseDetailsTabsProvider_) {
      CaseDetailsTabsProvider = _CaseDetailsTabsProvider_;

      spyOn(CaseDetailsTabsProvider, 'addTabs');
    }
  });
})(CRM._, angular);
