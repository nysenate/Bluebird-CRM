/* eslint-env jasmine */

(function ($, _) {
  describe('civicaseActivityFilters', function () {
    var $compile, $rootScope, $scope, activityFilters, CaseTypeCategory,
      categoryWhereUserCanAccessActivities;

    beforeEach(module('civicase', 'civicase.templates', function () {
      killDirective('civicaseActivityFiltersContact');
    }));

    beforeEach(inject(function (_$compile_, _$rootScope_, _CaseTypeCategory_) {
      $compile = _$compile_;
      $rootScope = _$rootScope_;
      CaseTypeCategory = _CaseTypeCategory_;

      categoryWhereUserCanAccessActivities = _.sample(CaseTypeCategory.getAll(), 1);
      spyOn($rootScope, '$broadcast');
      spyOn(CaseTypeCategory, 'getCategoriesWithAccessToActivity')
        .and.returnValue([categoryWhereUserCanAccessActivities]);

      $scope = $rootScope.$new();
      $scope.filters = {};

      initDirective();
    }));

    describe('on init', () => {
      it('displays a list of case type categories for which the user has permission to see the activities', () => {
        expect(activityFilters.isolateScope().caseTypeCategories)
          .toEqual([categoryWhereUserCanAccessActivities]);
      });

      it('does not filter the activity list with case type category', () => {
        expect(activityFilters.isolateScope().filters.case_type_category).toBeUndefined();
      });

      describe('when user can select case type category filter', () => {
        beforeEach(() => {
          $scope.canSelectCaseTypeCategory = true;
          initDirective();
        });

        it('filters the activity list with the first available case type category', () => {
          expect(activityFilters.isolateScope().filters.case_type_category)
            .toEqual(categoryWhereUserCanAccessActivities.name);
        });
      });
    });

    describe('when clicking more filters button', function () {
      beforeEach(function () {
        activityFilters.isolateScope().filters['@moreFilters'] = true;
        activityFilters.isolateScope().toggleMoreFilters();
      });

      it('toggles more filters visibility', function () {
        expect(activityFilters.isolateScope().filters['@moreFilters']).toEqual(false);
      });

      it('fires an event', function () {
        expect($rootScope.$broadcast)
          .toHaveBeenCalledWith('civicase::activity-filters::more-filters-toggled');
      });
    });

    /**
     * Initializes the ActivityPanel directive
     */
    function initDirective () {
      activityFilters = $compile(`<div
          civicase-activity-filters="filters"
          can-select-case-type-category="canSelectCaseTypeCategory"
        ></div>`)($scope);
      $rootScope.$digest();
    }

    /**
     * Mocks a directive
     *
     * @param {string} directiveName name of the directive
     */
    function killDirective (directiveName) {
      angular.mock.module(function ($compileProvider) {
        $compileProvider.directive(directiveName, function () {
          return {
            priority: 9999999,
            terminal: true
          };
        });
      });
    }
  });
})(CRM.$, CRM._);
