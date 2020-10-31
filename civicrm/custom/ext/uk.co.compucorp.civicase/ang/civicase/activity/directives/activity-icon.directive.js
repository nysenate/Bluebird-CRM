(function (angular, $, _, CRM) {
  var module = angular.module('civicase');

  module.directive('activityIcon', function (ActivityType) {
    return {
      restrict: 'A',
      replace: true,
      templateUrl: '~/civicase/activity/directives/activity-icon.directive.html',
      scope: {
        activity: '=activityIcon'
      },
      link: activityIconLink
    };

    /**
     * Link function for activityIcon directive
     *
     * @param {object} scope scope
     * @param {object} elem element
     * @param {object} attrs attributes
     */
    function activityIconLink (scope, elem, attrs) {
      var activityTypes = ActivityType.getAll(true);
      var activityType = activityTypes[scope.activity.activity_type_id];

      // Set direction icon for inbound/outbound email
      scope.direction = null;
      if (activityType.name === 'Email') {
        scope.direction = 'up';
      } else if (activityType.name === 'Inbound Email') {
        scope.direction = 'down';
      }
    }
  });
})(angular, CRM.$, CRM._, CRM);
