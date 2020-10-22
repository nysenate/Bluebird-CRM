<div id="civicaseActivitiesTab">
  {if $action EQ 16 and $permission EQ 'edit' and !$addAssigneeContact and !$addTargetContact}
    <div class="civicase__contact-activity-tab__add">
      {include file="CRM/Activity/Form/ActivityLinks.tpl" as_select=true}
    </div>
  {/if}
  <div class="container" ng-view></div>
</div>
{literal}
<script type="text/javascript">
  (function(angular, $, _) {
    angular.module('civicaseActivitiesTab', ['civicase']);
    angular.module('civicaseActivitiesTab').config(function($routeProvider) {
      $routeProvider.when('/', {
        reloadOnSearch: false,
        template: '<civicase-contact-activity-tab></civicase-contact-activity-tab>'
      });
    });
  })(angular, CRM.$, CRM._);

  CRM.$(document).one('crmLoad', function(){
    angular.bootstrap(document.getElementById('civicaseActivitiesTab'), ['civicaseActivitiesTab']);
  });
</script>
{/literal}
