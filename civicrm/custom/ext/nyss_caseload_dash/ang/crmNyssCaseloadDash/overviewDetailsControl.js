(function(angular, $, _) {
  // Forces the drill-down (details) table display to open when an expected filter is in the query string.
  // Example usage: <div overview-details-control="{foo: 1, bar: 2}"></div>
  angular.module('crmNyssCaseloadDash').directive('overviewDetailsControl', function() {
    return {
      restrict: 'AE',
      scope: {
        overviewDetailsControl: '='
      },
        controller: function($scope, $location) {
            var ts = CRM.ts('nyss_caseload_dash');
            function ensureOpen(params) {
                detailFilterParams = ['segment_NYSS_Caseload_Open',
                    'segment_NYSS_Caseload_Urgent',
                    'segment_NYSS_Caseload_Unassigned',
                    'segment_NYSS_Caseload_Newer_Than_7_Days',
                    'segment_NYSS_Caseload_7_To_30_Days',
                    'segment_NYSS_Caseload_Older_Than_30_Days',
                    'segment_NYSS_Caseload_Resolved_Last_7_Days'];
                if (detailFilterParams.some(function(key) { return key in params; })) {
                    CRM.$('#afsearchNYSSCaseloadOverviewDetails-accordion').attr('open', '1');
                }
                //
            }

            $scope.$watch(function() { return $location.search(); }, ensureOpen, true);
        },
      link: function($scope, $el, $attr) {
        //var ts = $scope.ts = CRM.ts('nyss_caseload_dash');
        //$scope.$watch('overviewDetailsControl', function(newValue){
        //  $scope.myOptions = newValue;
        //});
      }
    };
  });
})(angular, CRM.$, CRM._);
