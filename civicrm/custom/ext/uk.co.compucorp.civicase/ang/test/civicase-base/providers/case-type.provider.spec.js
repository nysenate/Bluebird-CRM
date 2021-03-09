/* eslint-env jasmine */

((_) => {
  describe('Case Type', () => {
    let CaseType, CaseTypesData, CaseTypesMockData, CaseTypesMockDataProvider;

    afterEach(() => {
      CaseTypesMockDataProvider.reset();
    });

    describe('when getting all active case types', () => {
      let activeCaseTypes, returnedCaseTypes;

      beforeEach(() => initModulesAndServices());

      beforeEach(() => {
        activeCaseTypes = _.pick(CaseTypesData, (caseType) => caseType.is_active === '1');
        returnedCaseTypes = CaseType.getAll();
      });

      it('returns all the active case types', () => {
        expect(returnedCaseTypes).toEqual(activeCaseTypes);
      });
    });

    describe('when getting all case including inactive ones', () => {
      let returnedCaseTypes;

      beforeEach(() => initModulesAndServices());

      beforeEach(() => {
        returnedCaseTypes = CaseType.getAll({ includeInactive: true });
      });

      it('returns all the case types including inactive ones', () => {
        expect(returnedCaseTypes).toEqual(CaseTypesData);
      });
    });

    describe('when getting the titles for case types using their name', () => {
      let returnedTitles;

      beforeEach(() => initModulesAndServices());

      beforeEach(() => {
        returnedTitles = CaseType.getTitlesForNames([
          'housing_support',
          'adult_day_care_referral'
        ]);
      });

      it('returns the title for the given case types', () => {
        expect(returnedTitles).toEqual([
          'Housing Support',
          'Adult Day Care Referral'
        ]);
      });
    });

    describe('when getting a case type by id', () => {
      let expectedCaseType, returnedCaseType;

      beforeEach(() => initModulesAndServices());

      beforeEach(() => {
        const caseTypeId = _.chain(CaseTypesData).keys().sample().value();
        expectedCaseType = CaseTypesData[caseTypeId];
        returnedCaseType = CaseType.getById(caseTypeId);
      });

      it('returns the matching case type', () => {
        expect(returnedCaseType).toEqual(expectedCaseType);
      });
    });

    describe('case roles', () => {
      let expectedResult, returnedResult;

      beforeEach(() => {
        expectedResult = [
          { name: 'Homeless Services Coordinator', id: '11' },
          { name: 'Health Services Coordinator', id: '12' },
          { name: 'Benefits Specialist', id: '14' },
          { name: 'Senior Services Coordinator', id: '16' }
        ];
      });

      describe('when getting all roles for the given case type category id', () => {
        beforeEach(() => initModulesAndServices());

        beforeEach(() => {
          const casesCategoryId = '1';
          returnedResult = CaseType.getAllRolesByCategoryID(casesCategoryId);
        });

        it('returns all the unique case roles', () => {
          expect(returnedResult).toEqual(expectedResult);
        });
      });

      describe('when there is a case type with no definition', () => {
        beforeEach(() => {
          CRM['civicase-base'].caseTypes = {
            4: {
              id: '4',
              name: 'case_with_no_definition',
              title: 'Case With No Definition',
              description: '',
              definition: [],
              case_type_category: '1'
            }
          };
          initModulesAndServices();
        });

        afterEach(() => {
          CaseTypesMockData.reset();
        });

        it('does not throw an error when trying to get the case roles', () => {
          expect(() => {
            CaseType.getAllRolesByCategoryID('1');
          }).not.toThrow();
        });
      });
    });

    /**
     * Initialises the civicase and mock data modules. Will also hoist
     * the services required by the spec file.
     */
    function initModulesAndServices () {
      module('civicase', 'civicase.data', (_CaseTypesMockDataProvider_) => {
        CaseTypesMockDataProvider = _CaseTypesMockDataProvider_;

        CaseTypesMockDataProvider.add({
          title: 'inactive case type',
          is_active: '0'
        });
      });

      inject((_CaseType_, _CaseTypesMockData_) => {
        CaseType = _CaseType_;
        CaseTypesMockData = _CaseTypesMockData_;
        CaseTypesData = CaseTypesMockData.get();
      });
    }
  });
})(CRM._);
