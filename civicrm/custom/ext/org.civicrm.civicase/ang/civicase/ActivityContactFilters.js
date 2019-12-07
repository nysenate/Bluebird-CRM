(function(angular, $, _) {
  // "civicaseActivityContactFilters" is a basic skeletal directive.
  // Example usage: <div civicase-activity-contact-filters="apiv3ActivityParams"></div>
  angular.module('civicase').directive('civicaseActivityContactFilters', function() {
    return {
      restrict: 'AE',
      replace: true,
      templateUrl: '~/civicase/ActivityContactFilters.html',
      scope: {
        filters: '=civicaseActivityContactFilters'
      },
      link: function($scope, $el, $attr) {
        var ts = $scope.ts = CRM.ts('civicase');

        $scope.$watch('filters', function(){
          // Ensure "All" checkbox renders.
          if ($scope.filters['@involvingContact'] === undefined) {
            $scope.filters['@involvingContact'] = '';
          }
        });

        $scope.$on('civicaseActivityFeed.query', function(event, filters, params) {
          switch (filters['@involvingContact']) {
            case 'myActivities':
              params.contact_id = 'user_contact_id';
              break;

            case 'delegated':
              if (_.isEmpty(params.assignee_contact_id)) {
                params.assignee_contact_id = {'!=': 'user_contact_id'};
              }
              if (_.isEmpty(params.source_contact_id)) {
                params.source_contact_id = 'user_contact_id';
              }
              break;

            default:
              break;
          }
        });
      }
    };
  });
})(angular, CRM.$, CRM._);
