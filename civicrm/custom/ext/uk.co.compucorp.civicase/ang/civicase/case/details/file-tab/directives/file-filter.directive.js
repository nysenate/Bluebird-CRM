(function (angular, $, _) {
  var module = angular.module('civicase');

  module.directive('civicaseFileFilter', function () {
    return {
      restrict: 'A',
      templateUrl: '~/civicase/case/details/file-tab/directives/file-filter.directive.html',
      controller: 'civicaseFileFilterController',
      scope: {
        activities: '=',
        fileFilterParams: '=civicaseFileFilter',
        isLoading: '=',
        refresh: '<'
      }
    };
  });

  module.controller('civicaseFileFilterController', civicaseFileFilterController);

  /**
   * Controller for civicaseFileFilter directive
   *
   * @param {object} $scope $scope
   * @param {object} $timeout $timeout
   * @param {object} ActivityCategory ActivityCategory
   * @param {object} FileCategory FileCategory
   */
  function civicaseFileFilterController ($scope, $timeout, ActivityCategory, FileCategory) {
    $scope.ts = CRM.ts('civicase');
    $scope.fileCategoriesIT = FileCategory.getAll();
    $scope.activityCategories = ActivityCategory.getAll();
    $scope.customFilters = {
      grouping: ''
    };

    $scope.refreshWithTimeout = refreshWithTimeout;

    (function init () {
      $scope.$watchCollection('customFilters', customFiltersWatcher);
    })();

    /**
     * Watcher for customFilters property
     */
    function customFiltersWatcher () {
      if (!_.isEmpty($scope.customFilters.grouping)) {
        $scope.fileFilterParams['activity_type_id.grouping'] = { LIKE: '%' + $scope.customFilters.grouping + '%' };
      } else {
        delete $scope.fileFilterParams['activity_type_id.grouping'];
      }

      if ($scope.customFilters.tag_id) {
        applyTagsFilter();
      }
    }

    /**
     * Apply Tags filter
     */
    function applyTagsFilter () {
      if ($scope.customFilters.tag_id.length > 0) {
        var tagid = $scope.customFilters.tag_id;
        $scope.fileFilterParams.tag_id = tagid.length === 1 ? tagid[0] : { IN: tagid };
      } else {
        delete $scope.fileFilterParams.tag_id;
      }
    }

    /**
     * Call refresh function inside a timeout
     *
     * When `ng-change` is mentioned in `crm-entityref` directive, the change
     * listener gets fired before the ng-model is changed.
     * This function is created to avoid this problem
     */
    function refreshWithTimeout () {
      $timeout($scope.refresh);
    }
  }
})(angular, CRM.$, CRM._);
