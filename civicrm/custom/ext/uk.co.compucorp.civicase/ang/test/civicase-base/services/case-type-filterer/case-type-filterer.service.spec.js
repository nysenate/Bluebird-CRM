/* eslint-env jasmine */

((_) => {
  describe('CaseTypeFilterer service', () => {
    let allCaseTypeCategories, allCaseTypes, CaseTypeFilterer, expectedCaseTypes,
      returnedCaseTypes;

    beforeEach(module('civicase-base'));

    beforeEach(inject((_CaseType_, _CaseTypeCategory_, _CaseTypeFilterer_) => {
      allCaseTypeCategories = _CaseTypeCategory_.getAll();
      allCaseTypes = _CaseType_.getAll();
      CaseTypeFilterer = _CaseTypeFilterer_;
    }));

    describe('when filtering by case type id', () => {
      beforeEach(() => {
        const sampleCaseType = _.sample(allCaseTypes);

        expectedCaseTypes = [
          sampleCaseType
        ];

        returnedCaseTypes = CaseTypeFilterer.filter({
          id: sampleCaseType.id
        });
      });

      it('returns a list of case types that only includes the requested case type', () => {
        expect(returnedCaseTypes)
          .toEqual(jasmine.arrayWithExactContents(expectedCaseTypes));
      });
    });

    describe('when filtering by a list of case type ids', () => {
      beforeEach(() => {
        expectedCaseTypes = _.sample(allCaseTypes, 2);
        const expectedCaseTypeIds = _.map(expectedCaseTypes, 'id');

        returnedCaseTypes = CaseTypeFilterer.filter({
          id: {
            IN: expectedCaseTypeIds
          }
        });
      });

      it('returns a list of case types including all requested case types', () => {
        expect(returnedCaseTypes)
          .toEqual(jasmine.arrayWithExactContents(expectedCaseTypes));
      });
    });

    describe('when filtering by a case type category', () => {
      beforeEach(() => {
        const prospectCategory = _.find(allCaseTypeCategories, {
          name: 'Prospecting'
        });

        expectedCaseTypes = _.filter(allCaseTypes, {
          case_type_category: prospectCategory.value
        });

        returnedCaseTypes = CaseTypeFilterer.filter({
          case_type_category: prospectCategory.name
        });
      });

      it('returns a list of case types belonging to the case type category', () => {
        expect(returnedCaseTypes)
          .toEqual(jasmine.arrayWithExactContents(expectedCaseTypes));
      });
    });

    describe('multiple filters', () => {
      describe('when filtering by multiple case type ids that belong to a particular category', () => {
        beforeEach(() => {
          const prospectCategory = _.find(allCaseTypeCategories, {
            name: 'Prospecting'
          });
          const prospectCaseTypes = _.filter(allCaseTypes, {
            case_type_category: prospectCategory.value
          });

          expectedCaseTypes = _.sample(prospectCaseTypes, 1);

          returnedCaseTypes = CaseTypeFilterer.filter({
            case_type_category: prospectCategory.name,
            id: {
              IN: _.map(expectedCaseTypes, 'id')
            }
          });
        });

        it('returns a list of case types filtered by multiple parameters', () => {
          expect(returnedCaseTypes)
            .toEqual(jasmine.arrayWithExactContents(expectedCaseTypes));
        });
      });
    });
  });
})(CRM._);
