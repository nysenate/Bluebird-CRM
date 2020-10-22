/* eslint-env jasmine */
(() => {
  describe('case details linked cases tab service', () => {
    let LinkedCasesCaseTab;

    beforeEach(module('civicase'));
    beforeEach(inject((_LinkedCasesCaseTab_) => {
      LinkedCasesCaseTab = _LinkedCasesCaseTab_;
    }));

    describe('getting the tab contents', () => {
      let tabContentPath;

      beforeEach(() => {
        tabContentPath = LinkedCasesCaseTab.activeTabContentUrl();
      });

      it('returns the path to the linked cases tab contents', () => {
        expect(tabContentPath)
          .toBe('~/civicase/case/details/directives/tab-content/linked-cases.html');
      });
    });
  });
})();
