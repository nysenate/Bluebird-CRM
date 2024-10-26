((_, angular) => {
  describe('CaseTypeCategory Provider', () => {
    let CaseTypeCategory, CaseCategoryInstanceTypeData;

    describe('when all case type categories are requested', () => {
      beforeEach(() => {
        module('civicase-base', 'civicase.data');
        injectDependencies();
      });

      it('returns all available case type categories', () => {
        expect(CaseTypeCategory.getAll()).toEqual(CRM['civicase-base'].caseTypeCategories);
      });
    });

    describe('when a case type category is requested by name', () => {
      var expectedResult;

      beforeEach(() => {
        module('civicase-base', 'civicase.data');
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
        module('civicase-base', 'civicase.data');
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

        module('civicase-base', 'civicase.data');
        injectDependencies();

        expectedResults = [_.find(CRM['civicase-base'].caseTypeCategories, function (caseTypeCategory) {
          return caseTypeCategory.name === 'awards';
        })];
      });

      it('returns all available case type categories', () => {
        expect(CaseTypeCategory.getCategoriesWithAccessToActivity()).toEqual(expectedResults);
      });
    });

    describe('when checking if a case type category is part of sent instance type', () => {
      beforeEach(() => {
        module('civicase-base', 'civicase.data');
        injectDependencies();
      });

      it('returns true if the case type category belongs to the sent instance', () => {
        expect(CaseTypeCategory.isInstance('Cases', 'case_management')).toBe(true);
      });
    });

    describe('when searching for a case type category by its id', () => {
      var expectedResult;

      beforeEach(() => {
        module('civicase-base', 'civicase.data');
        injectDependencies();

        expectedResult = {
          value: '1',
          label: 'Cases',
          name: 'Cases',
          is_active: '1'
        };
      });

      it('returns the case type category which matches the sent id', () => {
        expect(CaseTypeCategory.findById('1')).toEqual(expectedResult);
      });
    });

    describe('when searching for all case type categories by instance', () => {
      var expectedResult;

      beforeEach(() => {
        module('civicase-base', 'civicase.data');
        injectDependencies();

        expectedResult = [{
          value: '3',
          label: 'Awards',
          name: 'awards',
          is_active: '1'
        }];
      });

      it('returns all the case type categories belonging to sent instance', () => {
        expect(CaseTypeCategory.findAllByInstance('applicant_management')).toEqual(expectedResult);
      });
    });

    describe('when searching for instance of a case type category', () => {
      var expectedResult;

      beforeEach(() => {
        module('civicase-base', 'civicase.data');
        injectDependencies();

        expectedResult = _.find(CaseCategoryInstanceTypeData.get(), function (instance) {
          return instance.name === 'case_management';
        });
      });

      it('returns the instance of the case type category', () => {
        expect(CaseTypeCategory.getCaseTypeCategoryInstance('1')).toEqual(expectedResult);
      });
    });

    /**
     * Injects and hoists the dependencies needed by this spec.
     */
    function injectDependencies () {
      inject((_CaseTypeCategory_, _CaseCategoryInstanceTypeData_) => {
        CaseTypeCategory = _CaseTypeCategory_;
        CaseCategoryInstanceTypeData = _CaseCategoryInstanceTypeData_;
      });
    }
  });
})(CRM._, angular);
