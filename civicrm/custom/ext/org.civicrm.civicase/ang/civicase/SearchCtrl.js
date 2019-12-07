(function(angular, $, _) {

  angular.module('civicase').config(function($routeProvider) {
    $routeProvider.when('/case/search', {
      reloadOnSearch: false,
      template: '<h1 crm-page-title>{{ ts(\'Find Cases\') }}</h1>' +
      '<div id="bootstrap-theme" class="civicase-main">' +
      '<div class="panel" civicase-search="selections" expanded="true" on-search="show(selectedFilters)">' +
      '</div>' +
      '<pre>{{selections|json}}</pre>'+
      '</div>',
      controller: searchPageController
    });
  });

  function searchPageController($scope) {
    var ts = $scope.ts = CRM.ts('civicase');
    $scope.selections = {};
    $scope.show = function(selectedFilters) {
      $scope.selections = selectedFilters;
    };
    $scope.$bindToRoute({
      expr: 'selections',
      param: 's',
      default: {status_id:['Urgent']}
    });
  }

})(angular, CRM.$, CRM._);
