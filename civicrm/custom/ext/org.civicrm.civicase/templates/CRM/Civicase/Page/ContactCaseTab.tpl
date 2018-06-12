<div id="civicaseContactTab" >
  <div class="container" ng-view></div>
</div>
{literal}
  <script type="text/javascript">
    (function(angular, $, _) {
      angular.module('civicaseContactTab', ['civicase']);
      angular.module('civicaseContactTab').config(function($routeProvider) {
        $routeProvider.when('/', {
          reloadOnSearch: false,
          resolve: {
            hiddenFilters: function() {
              return {
                "contact_id": [{/literal}{$cid|json}{literal}]
              };
            }
          },
          controller: 'CivicaseCaseList',
          templateUrl: '~/civicase/CaseList.html'
        });
      });
    })(angular, CRM.$, CRM._);

    CRM.$(document).one('crmLoad', function(){
      angular.bootstrap(document.getElementById('civicaseContactTab'), ['civicaseContactTab']);
    });
  </script>
{/literal}