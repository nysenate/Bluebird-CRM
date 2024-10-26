(function ($, _) {
  describe('ContactCaseTabCaseList', function () {
    var $compile, $scope, $rootScope, element, eventResponse, CasesData;

    beforeEach(module('civicase', 'civicase.templates', 'civicase.data'));

    beforeEach(inject(function (_$q_, _$compile_, _$rootScope_, _CasesData_) {
      $compile = _$compile_;
      $rootScope = _$rootScope_;
      $scope = $rootScope.$new();
      CasesData = {
        name: 'opened',
        title: 'Open Cases',
        filterParams: {
          'status_id.grouping': 'Opened',
          contact_id: jasmine.any(Number)
        },
        isLoaded: false,
        showSpinner: false,
        cases: _CasesData_.get(),
        isLoadMoreAvailable: true,
        page: {
          size: 3,
          num: 1
        }
      };
    }));

    beforeEach(function () {
      spyOn($scope, '$emit').and.callThrough();
    });

    describe('basic tests', function () {
      beforeEach(function () {
        compileDirective();
      });

      it('complies the ContactCaseTabCaseList directive', function () {
        expect(element.html()).toContain('<!-- ngRepeat: case in casesList.cases -->');
      });
    });

    describe('loadMore()', function () {
      beforeEach(function () {
        listenForContactCasesListLoadMoreEvent();
        compileDirective();
        element.isolateScope().loadMore();
      });

      it('emits event', function () {
        expect(eventResponse).toBe('opened');
      });
    });

    /**
     * Compiles the directive
     */
    function compileDirective () {
      $scope.casesList = CasesData;
      element = $compile('<civicase-contact-case-tab-case-list cases-list="casesList" ></civicase-contact-case-tab-case-list>')($scope);
      $scope.$digest();
    }

    /**
     * Listener for `civicase::contact-record-case::loadmore` event
     */
    function listenForContactCasesListLoadMoreEvent () {
      $rootScope.$on('civicase::contact-record-list::load-more', function (event, type) {
        eventResponse = type;
      });
    }
  });
}(CRM.$, CRM._));
