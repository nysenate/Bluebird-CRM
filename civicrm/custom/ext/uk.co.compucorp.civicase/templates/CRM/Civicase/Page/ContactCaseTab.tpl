<div id="civicaseContactTab-{$case_type_category}">
  <div class="container" ng-view></div>
</div>
{literal}
  <script type="text/javascript">
    (function(angular, $, _) {
      angular.module('civicaseContactTab', ['civicase']);
      angular.module('civicaseContactTab').config(function($routeProvider) {
        $routeProvider.when('/', {
          reloadOnSearch: false,
          template: '<civicase-contact-case-tab case-type-category="{/literal}{$case_type_category}{literal}"></civicase-contact-case-tab>'
        });
      });
    })(angular, CRM.$, CRM._);

    CRM.$(document).one('crmLoad', function(){
      angular.bootstrap(document.getElementById('civicaseContactTab-{/literal}{$case_type_category}{literal}'), ['civicaseContactTab']);
    });
  </script>
{/literal}
