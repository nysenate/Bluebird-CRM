(function (angular, $, _, CRM) {
  var module = angular.module('civicase');

  module.factory('getActivityFeedUrl', function ($route, $location, $sce, ActivityStatusType, ActivityStatus) {
    return function (urlParams) {
      var activityFilters = {};
      var baseUrl = '#/case/list?';
      var currentPath = $location.path();

      urlParams = urlParams || {};
      urlParams.activityId = urlParams.activityId || 0;

      if (urlParams.category) {
        activityFilters['activity_type_id.grouping'] = urlParams.category;
      }

      if (urlParams.statusType) {
        activityFilters.status_id = ActivityStatusType.getAll()[urlParams.statusType];
      }

      if (urlParams.status) {
        activityFilters.status_id = [_.findKey(ActivityStatus.getAll(), function (statusObj) {
          return statusObj.name === urlParams.status;
        })];
      }

      activityFilters = angular.extend(
        {},
        $route.current.params.af || {},
        activityFilters,
        urlParams.activityFilters || {}
      );

      var finalUrlParams = angular.extend({}, $route.current.params, {
        aid: urlParams.activityId,
        focus: 1,
        sx: 0,
        ai: '{"myActivities":false,"delegated":false}',
        af: JSON.stringify(activityFilters)
      });

      if (currentPath !== '/case/list') {
        baseUrl = '#/case?';
        finalUrlParams.dtab = 1;

        // If we're not already viewing a case, force the case id filter
        finalUrlParams.cf = JSON.stringify({ id: urlParams.caseId });
      } else {
        finalUrlParams.tab = 'Activities';
      }

      if (urlParams.caseId) {
        finalUrlParams.caseId = parseInt(urlParams.caseId, 10);
      }

      // The value to mark as trusted in angular context for security.
      return $sce.trustAsResourceUrl(baseUrl + $.param(finalUrlParams));
    };
  });
})(angular, CRM.$, CRM._, CRM);
