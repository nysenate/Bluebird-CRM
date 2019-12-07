(function (angular, $, _) {

  angular.module('civicase').directive('civicaseActivityDot', function () {
    return {
      restrict: 'A',
      template:
        '<div class="cb-dot" title="{{ activity.type }} ({{ activity.status }})" style="background-color: {{ activity.color }};">' +
          '<i ng-if="direction" class="fa act-direction-icon fa-long-arrow-{{ direction }}" style="color: {{ activity.color }};"></i>' +
          '<i ng-if="activity.icon" class="fa {{ activity.icon }}"></i>' +
          '<strong ng-if="!activity.icon">{{ activity.type[0] }}</strong>' +
        '</div>',
      scope: {
        activity: '=civicaseActivityDot'
      },
      link: function(scope) {
        var actTypes = CRM.civicase.activityTypes,
          actType = actTypes[scope.activity.activity_type_id];
        // Set direction icon for inbound/outbound email
        scope.direction = null;
        if (actType.name == 'Email') {
          scope.direction = 'left';
        }
        if (actType.name == 'Inbound Email') {
          scope.direction = 'right';
        }
      }
    };
  });

})(angular, CRM.$, CRM._);
