/* eslint-env jasmine */

((_) => {
  describe('Contact Case Tab Case Details', () => {
    let $controller, $rootScope, $scope, CaseTypeCategory, mockCase;

    beforeEach(module('civicase', 'civicase.data'));

    beforeEach(inject((_$controller_, _$rootScope_, _CasesData_, _CaseTypeCategory_) => {
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      CaseTypeCategory = _CaseTypeCategory_;
      mockCase = _CasesData_.get().values[0];

      initController();
    }));

    describe('when requesting the case details page URL', () => {
      let expectedUrl, returnedUrl;

      beforeEach(() => {
        var caseTypeCategory = _.chain(CaseTypeCategory.getAll())
          .values()
          .sample()
          .value();
        mockCase['case_type_id.case_type_category'] = caseTypeCategory.value;
        expectedUrl = '/civicrm/case/a/' +
          `?case_type_category=${caseTypeCategory.name}` +
          `#/case/list?caseId=${mockCase.id}&focus=1&all_statuses=1`;
        returnedUrl = $scope.getCaseDetailsUrl(mockCase);
      });

      it('returns the case details page url for the given case', () => {
        expect(returnedUrl).toEqual(expectedUrl);
      });
    });

    /**
     * Initializes the contact case tab case details controller.
     */
    function initController () {
      $scope = $rootScope.$new();

      $controller('CivicaseContactCaseTabCaseDetailsController', { $scope: $scope });
    }
  });
})(CRM._);
