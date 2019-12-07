(function(angular, $, _) {

  angular.module('civicase').config(function($routeProvider) {
      $routeProvider.when('/case', {
        reloadOnSearch: false,
        controller: 'CivicaseDashboardCtrl',
        templateUrl: '~/civicase/DashboardCtrl.html'
      });
    }
  );

  angular.module('civicase').controller('CivicaseDashboardCtrl', function($scope, crmApi, formatActivity, $timeout) {
    var ts = $scope.ts = CRM.ts('civicase'),
      activitiesToShow = 10;
    $scope.caseStatuses = CRM.civicase.caseStatuses;
    $scope.caseTypes = CRM.civicase.caseTypes;
    $scope.caseTypesLength = _.size(CRM.civicase.caseTypes);
    $scope.checkPerm = CRM.checkPerm;
    $scope.url = CRM.url;
    $scope.activityPlaceholders = _.range(activitiesToShow);

    $scope.$bindToRoute({
      param: 'dtab',
      expr: 'activeTab',
      format: 'int',
      default: 0
    });

    if (CRM.checkPerm('access all cases and activities')) {
      $scope.$bindToRoute({
        param: 'dme',
        expr: 'myCasesOnly',
        format: 'bool',
        default: false
      });
    } else {
      $scope.myCasesOnly = true;
    }

    $scope.$bindToRoute({
      param: 'dbd',
      expr: 'showBreakdown',
      format: 'bool',
      default: false
    });

    // We hide the breakdown when there's only one case type
    if ($scope.caseTypesLength < 2) {
      $scope.showBreakdown = false;
    }

    $scope.summaryData = [];

    $scope.dashboardActivities = {};

    $scope.showHideBreakdown = function() {
      $scope.showBreakdown = !$scope.showBreakdown;
    };

    $scope.seeAllLink = function(category, statusFilter) {
      var params = {
        dtab: 1,
        dme: $scope.myCasesOnly ? 1 : 0,
        dbd: 0,
        af: JSON.stringify({
          'activity_type_id.grouping': category,
          status_id: CRM.civicase.activityStatusTypes[statusFilter]
        })
      };
      return '#/case?' + $.param(params);
    };

    $scope.caseListLink = function(type, status) {
      var cf = {};
      if (type) {
        cf.case_type_id = [type];
      }
      if (status) {
        cf.status_id = [status];
      }
      if ($scope.myCasesOnly) {
        cf.case_manager = [CRM.config.user_contact_id];
      }
      return '#/case/list?' + $.param({cf: JSON.stringify(cf)});
    };

    $scope.refresh = function(apiCalls) {
      apiCalls = apiCalls || [];
      apiCalls.push(['Case', 'getstats', {my_cases: $scope.myCasesOnly}]);
      var params = _.extend({
        sequential: 1,
        is_current_revision: 1,
        is_test: 0,
        return: ['case_id', 'activity_type_id', 'subject', 'activity_date_time', 'status_id', 'target_contact_name', 'assignee_contact_name', 'is_overdue', 'is_star', 'file_id', 'case_id.case_type_id', 'case_id.status_id', 'case_id.contacts']
      }, $scope.activityFilters);
      // recent communication
      apiCalls.push(['Activity', 'get', _.extend({
        "activity_type_id.grouping": {LIKE: "%communication%"},
        'status_id.filter': 1,
        options: {limit: activitiesToShow, sort: 'activity_date_time DESC'}
      }, params)]);
      apiCalls.push(['Activity', 'getcount', _.extend({
        "activity_type_id.grouping": {LIKE: "%communication%"},
        'status_id.filter': 1,
        is_current_revision: 1,
        is_test: 0
      }, $scope.activityFilters)]);
      // next milestones
      apiCalls.push(['Activity', 'get', _.extend({
        "activity_type_id.grouping": {LIKE: "%milestone%"},
        'status_id.filter': 0,
        options: {limit: activitiesToShow, sort: 'activity_date_time ASC'}
      }, params)]);
      apiCalls.push(['Activity', 'getcount', _.extend({
        "activity_type_id.grouping": {LIKE: "%milestone%"},
        'status_id.filter': 0,
        is_current_revision: 1,
        is_test: 0
      }, $scope.activityFilters)]);
      crmApi(apiCalls).then(function(data) {
        $scope.$broadcast('caseRefresh');
        $scope.summaryData = data[apiCalls.length - 5].values;
        $scope.dashboardActivities.recentCommunication = _.each(data[apiCalls.length - 4].values, formatActivity);
        $scope.dashboardActivities.recentCommunicationCount = data[apiCalls.length - 3];
        $scope.dashboardActivities.nextMilestones = _.each(data[apiCalls.length - 2].values, formatActivity);
        $scope.dashboardActivities.nextMilestonesCount = data[apiCalls.length - 1];
      });
    };

    // Translate between the dashboard's global filter-options and
    // the narrower, per-section filter-options.
    $scope.$watch('myCasesOnly', function (myCasesOnly) {
      $scope.activityFilters = {
        case_filter: {"case_type_id.is_active": 1, contact_is_deleted: 0}
      };
      var recentCaseFilter = {
        'status_id.grouping': 'Opened'
      };
      if (myCasesOnly) {
        $scope.activityFilters.case_filter.case_manager = CRM.config.user_contact_id;
        recentCaseFilter.case_manager = [CRM.config.user_contact_id];
      }
      $scope.recentCaseFilter = recentCaseFilter;
      $scope.recentCaseLink = '#/case/list?sf=modified_date&sd=DESC' + (myCasesOnly ? ('&cf=' + JSON.stringify({case_manager: [CRM.config.user_contact_id]})) : '');
      $scope.refresh();
    });
  });

  // TODO: Move this to common crm-affix directive
  angular.module('civicase').directive('uibTabsetAffix', function ($timeout) {
    return {
      link: function(scope, $el, attrs) {
        $timeout(function() {

          // $el it self is not ul.nav (consider this when creating common directive)
          var $tabNavigation = $('ul.nav'),
          $civicrmMenu = $('#civicrm-menu'),
          $tabContainer = $('.dashboard-tab-container');

          $tabNavigation.affix({
            offset: {
              top: $tabContainer.offset().top - 128
            }
          })
          .on('affixed.bs.affix', function() {
            $tabNavigation.css('top', $civicrmMenu.height());
          })
          .on('affixed-top.bs.affix', function() {
            $tabNavigation.css('top','auto');
          });
        });
    }
  };
});

})(angular, CRM.$, CRM._);
