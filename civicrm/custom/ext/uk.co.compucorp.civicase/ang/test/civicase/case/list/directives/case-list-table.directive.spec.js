(function (_, $) {
  describe('CivicaseCaseListTable Directive', function () {
    var $compile, $rootScope, $scope, originaljQueryHeightFn;

    beforeEach(module('civicase', 'civicase.templates', function ($controllerProvider, $provide) {
      $controllerProvider.register('CivicaseCaseListTableController', function () {});
      $provide.value('civicaseCrmApi', jasmine.createSpy('civicaseCrmApi'));
    }));

    beforeEach(inject(function (_$compile_, _$rootScope_) {
      $compile = _$compile_;
      $rootScope = _$rootScope_;

      $scope = $rootScope.$new();
      $scope.$bindToRoute = jasmine.createSpy('$bindToRoute');

      originaljQueryHeightFn = $.fn.height;
      spyOn($.fn, 'height');
      addAdditionalMarkup();
    }));

    afterEach(function () {
      removeAdditionalMarkup();
      $.fn.height = originaljQueryHeightFn;
    });

    describe('on init', function () {
      describe('when case is focused', function () {
        beforeEach(function () {
          initDirective();
          $scope.caseIsFocused = true;
          $scope.$digest();
        });

        it('resets the height of the case list', function () {
          expect($.fn.height).toHaveBeenCalledWith('auto');
        });
      });

      describe('when not viewing the case', function () {
        beforeEach(function () {
          initDirective();
          $scope.viewingCase = false;
          $scope.$digest();
        });

        it('resets the height of the case list', function () {
          expect($.fn.height).toHaveBeenCalledWith('auto');
        });
      });

      describe('when viewing the case and case is not focused', function () {
        var calculatedHeight;

        beforeEach(function () {
          initDirective();
          $scope.viewingCase = true;
          $scope.caseIsFocused = false;
          $scope.$digest();

          var caseList = $('.civicase__case-list');
          var crmPageTitle = $('[crm-page-title]');
          var crmPageTitleHeight = crmPageTitle.outerHeight(true);
          var caseListFilterPanel = $('.civicase__case-filter-panel__form');
          var offsetTop = caseList.offset().top -
            caseListFilterPanel.outerHeight() - crmPageTitleHeight;
          calculatedHeight = 'calc(100vh - ' + offsetTop + 'px)';
        });

        it('sets the height of the case list', function () {
          expect($.fn.height).toHaveBeenCalledWith(calculatedHeight);
        });
      });
    });

    /**
     * Initializes the civicaseActivityMonthNav directive
     */
    function initDirective () {
      var html = '<div civicase-case-list-table></div>';

      $compile(html)($scope);
      $scope.$digest();
    }

    /**
     * Add aditional markup
     */
    function addAdditionalMarkup () {
      var markup = `<div class="civicase-case-list-directive-unit-test" style="height: 50px">
        <div class='civicase__case-list'></div>
        <div crm-page-title></div>
        <div class="civicase__case-filter-panel__form"></div>
      </div>`;

      $(markup).appendTo('body');
    }

    /**
     * Remove aditional markup
     */
    function removeAdditionalMarkup () {
      $('.civicase-case-list-directive-unit-test').remove();
    }
  });

  describe('CivicaseCaseListTableController', function () {
    var $controller, $q, $scope, $route, CasesData, civicaseCrmApi,
      crmThrottleMock, CasesUtils;

    beforeEach(module('civicase', 'civicase.data', 'crmUtil', function ($provide) {
      crmThrottleMock = jasmine.createSpy('crmThrottle');

      $provide.value('$route', {
        current: {
          params: {
            cf: 1,
            caseId: 2,
            otherParam: 3,
            otherParam2: 4
          }
        }
      });

      $provide.value('crmThrottle', crmThrottleMock);
      $provide.value('civicaseCrmApi', jasmine.createSpy('civicaseCrmApi'));
    }));

    beforeEach(inject(function (_$controller_, _$q_, _$route_, $rootScope,
      _CasesData_, _civicaseCrmApi_, _formatCase_, _CasesUtils_) {
      $controller = _$controller_;
      $q = _$q_;
      $route = _$route_;
      $scope = $rootScope.$new();
      CasesData = _CasesData_.get();
      CasesUtils = _CasesUtils_;
      civicaseCrmApi = _civicaseCrmApi_;
      // custom function added by civicrm:
      $scope.$bindToRoute = jasmine.createSpy('$bindToRoute');
      $scope.filters = {
        id: _.uniqueId()
      };

      crmThrottleMock.and.callFake(function (callbackFn) {
        callbackFn();

        return $q.resolve([CasesData]);
      });

      spyOn(CasesUtils, 'fetchMoreContactsInformation');
    }));

    describe('on calling applyAdvSearch()', function () {
      var expectedApiCallParams;

      beforeEach(function () {
        expectedApiCallParams = [
          ['Case', 'getcaselist', jasmine.objectContaining({
            sequential: 1,
            return: [
              'subject', 'case_type_id', 'status_id', 'is_deleted', 'start_date',
              'modified_date', 'contacts', 'activity_summary', 'category_count',
              'tag_id.name', 'tag_id.color', 'tag_id.description',
              'case_type_id.case_type_category', 'case_type_id.is_active'
            ],
            options: jasmine.any(Object),
            'case_type_id.is_active': 1,
            'case_type_id.case_type_category': CRM['civicase-base'].currentCaseCategory,
            id: { LIKE: '%' + $scope.filters.id + '%' },
            contact_is_deleted: 0
          })],
          ['Case', 'getdetailscount', jasmine.objectContaining({
            'case_type_id.is_active': 1,
            id: { LIKE: '%' + $scope.filters.id + '%' },
            'case_type_id.case_type_category': CRM['civicase-base'].currentCaseCategory,
            contact_is_deleted: 0
          })],
          ['Case', 'getcaselistheaders']
        ];

        civicaseCrmApi.and.returnValue($q.resolve([_.cloneDeep(CasesData)]));
        initController();
        $scope.applyAdvSearch($scope.filters);

        $scope.$digest();
      });

      it('requests the cases data', function () {
        expect(civicaseCrmApi).toHaveBeenCalledWith(expectedApiCallParams);
      });
    });

    describe('on page number has changed', function () {
      beforeEach(function () {
        addAdditionalMarkup();
        civicaseCrmApi.and.returnValue($q.resolve([_.cloneDeep(CasesData)]));
        initController();
        $scope.$digest();
        $('.civicase__case-list-panel').scrollTop(20);
        $scope.page.num = 2;
        $scope.$digest();
      });

      afterEach(function () {
        removeAdditionalMarkup();
      });

      it('scrolls to the top of case list panel', function () {
        expect($('.civicase__case-list-panel').scrollTop()).toBe(0);
      });

      /**
       * Add aditional markup
       */
      function addAdditionalMarkup () {
        var markup = `<div class='civicase__case-list-panel' style='height: 10px; overflow: scroll'>
          <br><br><br>
          <br><br><br>
          <br><br><br>
        </div>`;

        $(markup).appendTo('body');
      }

      /**
       * Remove aditional markup
       */
      function removeAdditionalMarkup () {
        $('.civicase__case-list-panel').remove();
      }
    });

    describe('when switching to displaying a new case details', () => {
      var expectedParams;

      beforeEach(function () {
        initController();
        $scope.cases = _.cloneDeep(CasesData.values);

        $scope.viewCase(CasesData.values[0].id);

        expectedParams = { cf: 1, caseId: 2 };
      });

      it('removes all url parameters added by individual tabs', function () {
        expect($route.current.params).toEqual(expectedParams);
      });
    });

    describe('when case being viewed is not present in the list of cases with current filters', () => {
      beforeEach(function () {
        initController();
        $scope.cases = _.cloneDeep(CasesData.values);
        $scope.viewingCase = 111111;

        $scope.applyAdvSearch();
        $scope.$digest();
      });

      it('displays a button to clear all filters', function () {
        expect($scope.caseNotFound).toBe(true);
      });
    });

    /**
     * Initializes the case list table controller.
     */
    function initController () {
      $controller('CivicaseCaseListTableController', {
        $scope: $scope
      });
    }
  });
})(CRM._, CRM.$);
