(function (angular, $, _) {
  var module = angular.module('civicase');

  module.directive('civicaseFileFilter', function () {
    return {
      restrict: 'A',
      templateUrl: '~/civicase/case/details/file-tab/directives/file-filter.directive.html',
      controller: 'civicaseFileFilterController',
      scope: {
        fileFilter: '=civicaseFileFilter'
      }
    };
  });

  module.controller('civicaseFileFilterController', civicaseFileFilterController);

  /**
   * Controller for civicaseFileFilter directive
   *
   * @param {object} $scope $scope
   * @param {object} ActivityCategory ActivityCategory
   * @param {object} FileCategory FileCategory
   */
  function civicaseFileFilterController ($scope, ActivityCategory, FileCategory) {
    $scope.ts = CRM.ts('civicase');
    $scope.fileCategoriesIT = FileCategory.getAll();
    $scope.activityCategories = ActivityCategory.getAll();
    $scope.customFilters = {
      grouping: ''
    };

    (function init () {
      $scope.$watchCollection('customFilters', customFiltersWatcher);
    })();

    /**
     * Watcher for customFilters property
     */
    function customFiltersWatcher () {
      if (!_.isEmpty($scope.customFilters.grouping)) {
        $scope.fileFilter.params['activity_type_id.grouping'] = { LIKE: '%' + $scope.customFilters.grouping + '%' };
      } else {
        delete $scope.fileFilter.params['activity_type_id.grouping'];
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
        $scope.fileFilter.params.tag_id = tagid.length === 1 ? tagid[0] : { IN: tagid };
      } else {
        delete $scope.fileFilter.params.tag_id;
      }
    }
  }
})(angular, CRM.$, CRM._);
