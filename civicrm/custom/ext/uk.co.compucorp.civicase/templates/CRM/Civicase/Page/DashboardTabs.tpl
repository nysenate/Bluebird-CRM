<div class="civicase__crm-dashboard">
  <ul
    civicase-crm-dashboard-tabset-affix
    class="civicase__affix__activity-filters nav nav-pills nav-pills-horizontal nav-pills-horizontal-default civicase__tabs">
    <li><a href="#dashboard" data-toggle="tab">Dashboard</a></li>
    <li class=""><a href="#myactivities" data-toggle="tab">My Activities</a></li>
  </ul>
  <div class="tab-content">
    <div class="tab-pane civicase__tabs__panel" id="dashboard">
      {include file="CRM/Contact/Page/DashBoardDashlet.tpl"}
    </div>
    <div class="tab-pane civicase__crm-dashboard__myactivities-tab" id="myactivities">
      <div id="bootstrap-theme" ng-view></div>
    </div>
  </div>
</div>
{literal}
<script type="text/javascript">
  CRM.$(function() {
    var defaultTab = 'dashboard';
    var hashWithoutQueryParams = window.location.hash.split('?')[0].substr(2);
    var currentTab = hashWithoutQueryParams || defaultTab;

    CRM.$('.civicase__crm-dashboard').tabs({
      activate: function (event, ui) {
        currentTab = ui.newPanel.attr('id');
        window.location.hash = currentTab;
        CRM.$(window).scrollTop(0);
      }
    });

    // automatically select tab on load:
    CRM.$('[href="#' + currentTab + '"]').click();
  });

  (function(angular, $, _) {
    angular.module('civicaseCrmDashboard', ['civicase']);
    angular.module('civicaseCrmDashboard').config(function($routeProvider) {
      $routeProvider.when('/myactivities', {
        reloadOnSearch: false,
        template: '<civicase-my-activities-tab></civicase-my-activities-tab>'
      });
    });
    CRM.$('#configure-dashlet').on("click", "a[href='/civicrm/dashboard?reset=1']", function (event) {
      location.reload();
    });
  })(angular, CRM.$, CRM._);

  CRM.$(document).one('crmLoad', function(){
    angular.bootstrap(document.getElementsByClassName('civicase__crm-dashboard')[0], ['civicaseCrmDashboard']);
  });
</script>
{/literal}
