/* eslint-env jasmine */
((_, angular) => {
  describe('CaseTypeCategory Provider', () => {
    let CaseTypeCategory;

    describe('when all case type categories are requested', () => {
      beforeEach(() => {
        module('civicase-base');
        injectDependencies();
      });

      it('returns all available case type categories', () => {
        expect(CaseTypeCategory.getAll()).toEqual(CRM['civicase-base'].caseTypeCategories);
      });
    });

    describe('when a case type category is requested by name', () => {
      var expectedResult;

      beforeEach(() => {
        module('civicase-base');
        injectDependencies();

        expectedResult = _.find(CRM['civicase-base'].caseTypeCategories, function (caseTypeCategory) {
          return caseTypeCategory.name === 'awards';
        });
      });

      it('returns the case type category which matches the name', () => {
        expect(CaseTypeCategory.findByName('awards')).toEqual(expectedResult);
      });
    });

    describe('when a case type category is requested by lower case name, but it is stored in capital case', () => {
      var expectedResult;

      beforeEach(() => {
        module('civicase-base');
        injectDependencies();

        expectedResult = _.find(CRM['civicase-base'].caseTypeCategories, function (caseTypeCategory) {
          return caseTypeCategory.name === 'Cases';
        });
      });

      it('returns the case type category which matches the name even if the category was stored in capital case', () => {
        expect(CaseTypeCategory.findByName('cases')).toEqual(expectedResult);
      });
    });

    describe('when case type categories with access to activities are requested', () => {
      var expectedResults;

      beforeEach(() => {
        CRM['civicase-base'].caseTypeCategoriesWhereUserCanAccessActivities = ['awards'];

        module('civicase-base');
        injectDependencies();

        expectedResults = [_.find(CRM['civicase-base'].caseTypeCategories, function (caseTypeCategory) {
          return caseTypeCategory.name === 'awards';
        })];
      });

      it('returns all available case type categories', () => {
        expect(CaseTypeCategory.getCategoriesWithAccessToActivity()).toEqual(expectedResults);
      });
    });
    /**
     * Injects and hoists the dependencies needed by this spec.
     */
    function injectDependencies () {
      inject((_CaseTypeCategory_) => {
        CaseTypeCategory = _CaseTypeCategory_;
      });
    }
  });
})(CRM._, angular);
