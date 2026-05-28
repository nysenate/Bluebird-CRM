(function(angular, $, _) {
  // "overviewDetailsHeading" is a basic skeletal directive.
  // Example usage: <div overview-details-heading="{foo: 1, bar: 2}"></div>
  angular.module('crmNyssCaseloadDash').directive('overviewDetailsHeading', function() {
    return {
      restrict: 'AE',
      templateUrl: '~/crmNyssCaseloadDash/overviewDetailsHeading.html',
      scope: {
        overviewDetailsHeading: '='
      },
      controller: function($scope, $location) {
        var ts = CRM.ts('nyss_caseload_dash');

        function updateHeading(params) {
            $scope.heading = ts('All Cases');
            Object.keys(params).forEach(function (key) {
                switch (key) {
                    case 'segment_NYSS_Caseload_Open':
                        $scope.heading = ts('Open Cases');
                        break;
                    case 'segment_NYSS_Caseload_Urgent':
                        $scope.heading = ts('Urgent Cases');
                        break;
                    case 'segment_NYSS_Caseload_Unassigned':
                        $scope.heading = ts('Unassigned Cases');
                        break;
                    case 'segment_NYSS_Caseload_Newer_Than_7_Days':
                        $scope.heading = ts('Cases Open < 7 Days');
                        break;
                    case 'segment_NYSS_Caseload_7_To_30_Days':
                        $scope.heading = ts('Cases Open 7 - 30 Days');
                        break;
                    case 'segment_NYSS_Caseload_Older_Than_30_Days':
                        $scope.heading = ts('Cases Open > 30 Days');
                        break;
                    case 'segment_NYSS_Caseload_Resolved_Last_7_Days':
                        $scope.heading = ts('Cases Resolved (Last 7 Days)');
                        break;
                }
            });
        }

        $scope.$watch(function() { return $location.search(); }, updateHeading, true);
      },
      link: function($scope, $el, $attr) {
        var ts = $scope.ts = CRM.ts('nyss_caseload_dash');
        $scope.$watch('overviewDetailsHeading', function(newValue){
          //$scope.myOptions = newValue;

        });
      }
    };
  });
})(angular, CRM.$, CRM._);
