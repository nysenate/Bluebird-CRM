/* eslint-env jasmine */

(function (_, $) {
  describe('CivicaseCaseListTable Directive', function () {
    var $compile, $rootScope, $scope, originaljQueryHeightFn;

    beforeEach(module('civicase', 'civicase.templates', function ($controllerProvider) {
      $controllerProvider.register('CivicaseCaseListTableController', function () {});
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
    var $controller, $q, $scope, CasesData, crmApi;

    beforeEach(module('civicase', 'civicase.data', 'crmUtil'));

    beforeEach(inject(function (_$controller_, _$q_, $rootScope, _CasesData_, _crmApi_,
      _formatCase_) {
      $controller = _$controller_;
      $q = _$q_;
      $scope = $rootScope.$new();
      CasesData = _CasesData_.get();
      crmApi = _crmApi_;
      // custom function added by civicrm:
      $scope.$bindToRoute = jasmine.createSpy('$bindToRoute');
      $scope.filters = {
        id: _.uniqueId()
      };
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
              'tag_id.name', 'tag_id.color', 'tag_id.description'
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

        crmApi.and.returnValue($q.resolve([_.cloneDeep(CasesData)]));
        initController();
        $scope.applyAdvSearch($scope.filters);
      });

      it('requests the cases data', function () {
        expect(crmApi).toHaveBeenCalledWith(expectedApiCallParams);
      });
    });

    describe('on page number has changed', function () {
      beforeEach(function () {
        addAdditionalMarkup();
        crmApi.and.returnValue($q.resolve([_.cloneDeep(CasesData)]));
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
