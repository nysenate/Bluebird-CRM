(function (angular, $, _) {
  var module = angular.module('civicase');

  module.directive('civicaseContactCaseTabCaseList', function () {
    return {
      restrict: 'EA',
      replace: true,
      controller: CivicaseContactCaseTabCaseListController,
      templateUrl: '~/civicase/case/contact-case-tab/directives/contact-case-tab-case-list.directive.html',
      scope: {
        casesList: '=',
        viewingCaseId: '='
      }
    };
  });

  /**
   * @param {object} $scope the controller scope
   * @param {Function} ts translation service
   * @param {Function} $rootScope the root scope
   * @param {Function} crmApi the crm api service
   * @param {Function} formatCase the format case service
   * @param {Function} DateHelper the date helper service
   */
  function CivicaseContactCaseTabCaseListController ($scope, ts, $rootScope, crmApi, formatCase, DateHelper) {
    var defaultPageSize = 2;

    $scope.loadingPlaceholders = _.range($scope.casesList.page.size || defaultPageSize);
    $scope.formatDate = DateHelper.formatDate;
    $scope.ts = ts;

    /**
     * Emits loadmore event
     */
    $scope.loadMore = function () {
      $scope.$emit('civicase::contact-record-list::load-more', $scope.casesList.name);
    };

    /**
     * Emits view-case event
     *
     * @param {object} caseObj the case object
     */
    $scope.viewCase = function (caseObj) {
      $scope.$emit('civicase::contact-record-list::view-case', caseObj);
    };
  }
})(angular, CRM.$, CRM._);
