(function(angular, $, _) {

  angular.module('civicase').config(function($routeProvider) {
    $routeProvider.when('/case/list', {
      reloadOnSearch: false,
      resolve: {
        hiddenFilters: function() {}
      },
      controller: 'CivicaseCaseList',
      templateUrl: '~/civicase/CaseList.html'
    });
  });

  // Common function to get api params for fetching case list in various contexts
  function loadCaseApiParams(filters, sort, page) {
    var returnParams = {
      sequential: 1,
      return: ['subject', 'case_type_id', 'status_id', 'is_deleted', 'start_date', 'modified_date', 'contacts', 'activity_summary', 'category_count', 'tag_id.name', 'tag_id.color', 'tag_id.description'],
      options: {
        sort: sort.field + ' ' + sort.dir,
        limit: page.size,
        offset: page.size * (page.num - 1)
      }
    };
    // Keep things consistent and add a secondary sort on client name and a tertiary sort on case id
    if (sort.field !== 'id' && sort.field !== 'contact_id.sort_name') {
      returnParams.options.sort += ', contact_id.sort_name';
    }
    if (sort.field !== 'id') {
      returnParams.options.sort += ', id';
    }
    var params = {"case_type_id.is_active": 1};
    _.each(filters, function(val, filter) {
      if (val || typeof val === 'boolean') {
        if (typeof val === 'number' || typeof val === 'boolean') {
          params[filter] = val;
        }
        else if (typeof val === 'object' && !$.isArray(val)) {
          params[filter] = val;
        }
        else if (val.length) {
          params[filter] = $.isArray(val) ? {IN: val} : {LIKE: '%' + val + '%'};
        }
      }
    });
    // Filter out deleted contacts
    if (!params.contact_id) {
      params.contact_is_deleted = 0;
    }
    // If no status specified, default to all open cases
    if (!params.status_id && !params.id) {
      params['status_id.grouping'] = 'Opened';
    }
    // Default to not deleted
    if (!params.is_deleted && !params.id) {
      params.is_deleted = 0;
    }
    return [
      ['Case', 'getcaselist', $.extend(true, returnParams, params)],
      ['Case', 'getcount', params]
    ];
  }

  // CaseList controller
  angular.module('civicase').controller('CivicaseCaseList', function($scope, crmApi, crmStatus, crmUiHelp, crmThrottle, $timeout, hiddenFilters, getActivityFeedUrl, formatCase) {
    // The ts() and hs() functions help load strings for this module.
    var ts = $scope.ts = CRM.ts('civicase'),
      firstLoad = true,
      caseTypes = CRM.civicase.caseTypes,
      caseStatuses = $scope.caseStatuses = CRM.civicase.caseStatuses;
    $scope.activityTypes = CRM.civicase.activityTypes;
    $scope.activityCategories = CRM.civicase.activityCategories;
    $scope.cases = [];
    $scope.CRM = CRM;
    $scope.pageTitle = '';
    $scope.viewingCaseDetails = null;
    $scope.selectedCases = [];
    $scope.activityFeedUrl = getActivityFeedUrl;
    $scope.hiddenFilters = hiddenFilters;
    $scope.sort = {sortable: true};
    $scope.page = {total: 0};
    $scope.isLoading = true;

    if (CRM.checkPerm('basic case information') &&
      !CRM.checkPerm('administer CiviCase') &&
      !CRM.checkPerm('access my cases and activities') &&
      !CRM.checkPerm('access all cases and activities')
    ) {
      $scope.bulkAllowed = false;
    } else {
      $scope.bulkAllowed = true;
    }

    $scope.$bindToRoute({expr:'searchIsOpen', param: 'sx', format: 'bool', default: false});
    $scope.$bindToRoute({expr:'sort.field', param:'sf', format: 'raw', default: 'contact_id.sort_name'});
    $scope.$bindToRoute({expr:'sort.dir', param:'sd', format: 'raw', default: 'ASC'});
    $scope.$bindToRoute({expr:'caseIsFocused', param:'focus', format: 'bool', default: false});
    $scope.$bindToRoute({expr:'filters', param:'cf', default: {}});
    $scope.$bindToRoute({expr:'viewingCase', param:'caseId', format: 'raw'});
    $scope.$bindToRoute({expr:'viewingCaseTab', param:'tab', format: 'raw', default:'summary'});
    $scope.$bindToRoute({expr:'page.size', param:'cps', format: 'int', default: 15});
    $scope.$bindToRoute({expr:'page.num', param:'cpn', format: 'int', default: 1});
    $scope.casePlaceholders = $scope.filters.id ? [0] : _.range($scope.page.size);

    $scope.viewCase = function(id, $event) {
      if (!$scope.bulkAllowed) {
        return;
      }

      if (!$event || !$($event.target).is('a, a *, input, button')) {
        unfocusCase();
        if ($scope.viewingCase === id) {
          $scope.viewingCase = null;
          $scope.viewingCaseDetails = null;
        } else {
          $scope.viewingCaseDetails = _.findWhere($scope.cases, {id: id});
          $scope.viewingCase = id;
          $scope.viewingCaseTab = 'summary';
        }
      }
      setPageTitle();
    };

    
    $scope.$watch('caseIsFocused', function() {
      $timeout(function() {
        var $actHeader = $('.act-feed-panel .panel-header'),
        $actControls = $('.act-feed-panel .act-list-controls');

        if($actHeader.hasClass('affix')) {
            $actHeader.css('width',$('.act-feed-panel').css('width'));
        }
        else {
          $actHeader.css('width', 'auto');
        }

        if($actControls.hasClass('affix')) {
            $actControls.css('width',$actHeader.css('width'));
        }
        else {
          $actControls.css('width', 'auto');
        }
      },1500);
    });

    var unfocusCase = $scope.unfocusCase = function() {
      $scope.caseIsFocused = false;
    };

    $scope.selectAll = function(e) {
      var checked = e.target.checked;
      _.each($scope.cases, function(item) {
        item.selected = checked;
      });
    };

    $scope.isSelection = function(condition) {
      if (!$scope.cases) {
        return false;
      }
      var count = $scope.selectedCases.length;
      if (condition === 'all') {
        return count === $scope.cases.length;
      } else if (condition === 'any') {
        return !!count;
      }
      return count === condition;
    };

    function setPageTitle() {
      var viewingCase = $scope.viewingCase,
        cases = $scope.cases,
        filters = $scope.filters;
      // Hide page title when case is selected
      $('h1.crm-page-title').toggle(!viewingCase);
      if (viewingCase) {
        var item = _.findWhere(cases, {id: viewingCase});
        if (item) {
          $scope.pageTitle = item.client[0].display_name + ' - ' + item.case_type;
        }
        return;
      }
      if (_.size(_.omit(filters, ['status_id', 'case_type_id']))) {
        $scope.pageTitle = ts('Case Search Results');
      } else {
        var status = [];
        if (filters.status_id && filters.status_id.length) {
          _.each(filters.status_id, function(s) {
            status.push(_.findWhere(caseStatuses, {name: s}).label);
          });
        } else {
          status = [ts('All Open')];
        }
        var type = [];
        if (filters.case_type_id && filters.case_type_id.length) {
          _.each(filters.case_type_id, function(t) {
            type.push(_.findWhere(caseTypes, {name: t}).title);
          });
        }
        $scope.pageTitle = status.join(' & ') + ' ' + type.join(' & ') + ' ' + ts('Cases');
      }
      if (typeof $scope.totalCount === 'number') {
        $scope.pageTitle += ' (' + $scope.totalCount + ')';
      }
    }

    var getCases = $scope.getCases = function() {
      $scope.isLoading = true;
      setPageTitle();
      crmThrottle(_loadCases).then(function(result) {
        var viewingCaseDetails;
        var cases = _.each(result[0].values, formatCase);
        if ($scope.viewingCase) {
          if ($scope.viewingCaseDetails) {
            var currentCase = _.findWhere(cases, {id: $scope.viewingCase});
            if (currentCase) {
              _.assign(currentCase, $scope.viewingCaseDetails);
            }
          } else {
            $scope.viewingCaseDetails = _.findWhere(cases, {id: $scope.viewingCase});
          }
        }

        if (typeof result[2] !== 'undefined') {
          $scope.headers = result[2].values;
        }

        $scope.cases = cases;
        $scope.page.num = result[0].page || $scope.page.num;
        $scope.totalCount = result[1];
        $scope.page.total = Math.ceil(result[1] / $scope.page.size);
        setPageTitle();
        firstLoad = $scope.isLoading = false;
      });
    };

    $scope.refresh = function(apiCalls) {
      $scope.isLoading = true;
      if (!apiCalls) apiCalls = [];
      apiCalls = apiCalls.concat(loadCaseApiParams(angular.extend({}, $scope.filters, $scope.hiddenFilters), $scope.sort, $scope.page));
      crmApi(apiCalls, true).then(function(result) {
        $scope.cases = _.each(result[apiCalls.length - 2].values, formatCase);
        $scope.totalCount = result[apiCalls.length - 1];
        $scope.isLoading = false;
      });
    };

    function _loadCases() {
      var params = loadCaseApiParams(angular.extend({}, $scope.filters, $scope.hiddenFilters), $scope.sort, $scope.page);

      if (firstLoad && $scope.viewingCase) {
        params[0][2].options.page_of_record = $scope.viewingCase;
      } else if (firstLoad) {
        params.push(['Case', 'getcaselistheaders']);
      }

      return crmApi(params);
    }

    function getCasesFromWatcher(newValue, oldValue) {
      if (newValue !== oldValue) {
        getCases();
      }
    }

    $scope.$watchCollection('sort', getCasesFromWatcher);
    $scope.$watchCollection('page', getCasesFromWatcher);
    $scope.$watch('cases', function(cases) {
      $scope.selectedCases = _.filter(cases, 'selected');
    }, true);

    $scope.applyAdvSearch = function(newFilters) {
      $scope.filters = newFilters;
      getCases();
    };

    $timeout(getCases);

    $timeout(function() {

      var $listTable = $('.case-list-panel .inner'),
        $customScroll = $('.case-list-panel .custom-scroll-wrapper'),
        $tableHeader = $('.case-list-panel .inner table thead');

      $($listTable).scroll(function(){
        $customScroll.scrollLeft($listTable.scrollLeft());
        $('thead.affix').scrollLeft($customScroll.scrollLeft());
      });

      $customScroll.scroll(function(){
        $listTable.scrollLeft($customScroll.scrollLeft());
        $('thead.affix').scrollLeft($customScroll.scrollLeft());
      });

      $([$tableHeader, $customScroll]).affix({
        offset: {
           top: $('.case-list-panel').offset().top - 50
        }
      })
      .on('affixed.bs.affix', function() {
        $('thead.affix').scrollLeft($customScroll.scrollLeft());
      });
    });
  });

  function caseListTableController($scope, $location, crmApi, formatCase, crmThrottle, $timeout, getActivityFeedUrl) {
    var ts = $scope.ts = CRM.ts('civicase');
    var firstLoad = true;

    $scope.cases = [];
    $scope.CRM = CRM;
    $scope.activityCategories = CRM.civicase.activityCategories;
    $scope.activityFeedUrl = getActivityFeedUrl;
    $scope.casePlaceholders = _.range($scope.page.size);
    $scope.isLoading = true;

    if (CRM.checkPerm('basic case information') &&
      !CRM.checkPerm('administer CiviCase') &&
      !CRM.checkPerm('access my cases and activities') &&
      !CRM.checkPerm('access all cases and activities')
    ) {
      $scope.bulkAllowed = false;
    } else {
      $scope.bulkAllowed = true;
    }

    function _loadCases() {
      var params = loadCaseApiParams($scope.filters, $scope.sort, $scope.page);

      if (firstLoad) {
        params.push(['Case', 'getcaselistheaders']);
      }

      return crmApi(params);
    }

    function getCases() {
      $scope.isLoading = true;
      crmThrottle(_loadCases)
        .then(function(result) {
          $scope.cases = _.each(result[0].values, formatCase);

          if (firstLoad) {
            $scope.headers = result[2].values;
            firstLoad = false;
          }

          $scope.totalCount = result[1];
          $scope.isLoading = false;

          $timeout(function() {

            var $listTable = $('.case-list-panel .inner'),
              $customScroll = $('.case-list-panel .custom-scroll-wrapper'),
              $tableHeader = $('.case-list-panel .inner table thead');

            $($listTable).scroll(function(){
              $customScroll.scrollLeft($listTable.scrollLeft());
              $('thead.affix').scrollLeft($customScroll.scrollLeft());
            });

            $customScroll.scroll(function(){
              $listTable.scrollLeft($customScroll.scrollLeft());
              $('thead.affix').scrollLeft($customScroll.scrollLeft());
            });

            $([$tableHeader, $customScroll]).affix({
              offset: {
                 top: $('.case-list-panel').offset().top - 50
              }
            })
            .on('affixed.bs.affix', function() {
              $('thead.affix').scrollLeft($customScroll.scrollLeft());
            });
          });
        });
    }

    $scope.viewCase = function(id, $event) {
      if (!$scope.bulkAllowed) {
        return;
      }

      if (!$event || !$($event.target).is('a, a *, input, button, button *')) {
        var p = {
          caseId: id,
          focus: 1,
          sf: $scope.sort.field,
          sd: $scope.sort.dir
        };
        $location.path('/case/list');
        $location.search(p);
      }
    };

    $scope.$on('caseRefresh', getCases);
    getCases();
  }

  angular.module('civicase').directive('caseListTable', function() {
    return {
      restrict: 'A',
      controller: caseListTableController,
      templateUrl: '~/civicase/CaseListTable.html',
      scope: {
        sort: '=caseListTableSort',
        page: '=caseListTablePage',
        filters: '=caseListTableFilters',
        refresh: '=refreshCallback'
      }
    };
  });

})(angular, CRM.$, CRM._);
