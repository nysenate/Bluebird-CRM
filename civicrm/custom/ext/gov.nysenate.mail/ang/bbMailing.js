/* NYSS 17587 -- Show warning when message content length exceeds Gmail clip limit */
(function(angular, $, _) {
  angular.module('bbMailing', CRM.angRequires('bbMailing'));

  angular.module('bbMailing').directive('bbMailingSizeWarning', function() {
    return {
      restrict: 'E',
      scope: {crmMailing: '=',},
      template: `
        <div ng-show="showWarning()"
             class="messages status warning bb-mailing-size-warning">
          <i class="crm-i fa-info-circle"></i>
          <span class="msg-title">{{ ts('Message Length Warning') }}</span>
          <div class="msg-text">{{ ts('This message is approximately %1 KB (kilobytes).', {1:formattedMailingSize()}) }} 
          {{ ts('Some email apps clip messages after %1 KB.',{1:warnThreshold})}}
          {{ ts('Shorten the message content to avoid being clipped.') }}
          </div>
        </div>
      `,
      transclude: false,
      controller: function($scope, $route) {
        //console.log(CRM.crmMailing);
        const warnThreshold = 102 * 1024;
        $scope.warnThreshold = (warnThreshold / 1024).toFixed(0);

        if ($scope.crmMailing && $scope.crmMailing.body_html) {
          $scope.mailingSize = new TextEncoder().encode($scope.crmMailing.body_html).length;
        } else {
          $scope.mailingSize = 0; // or null, depending on what you prefer
        }

        $scope.showWarning = () => $scope.mailingSize > warnThreshold;
        $scope.formattedMailingSize = () => ($scope.mailingSize / 1024).toFixed(1);
      },

      link: function (scope, element, attrs, ctrl) {

        scope.ts = CRM.ts(null);

        if (scope.crmMailing && scope.crmMailing.body_html) {
          scope.$watch('crmMailing.body_html', function (newValue) {
            scope.mailingSize = new TextEncoder().encode(newValue).length;
          })
        }

      }
    };
  });

})(angular, CRM.$, CRM._);

