(function (angular) {
  var module = angular.module('civicase');

  module.config(function (CaseDetailsSummaryBlocksProvider) {
    var showComingSoonCaseSummaryBlock = CRM['civicase-base'].showComingSoonCaseSummaryBlock;

    if (!showComingSoonCaseSummaryBlock) {
      return;
    }

    var caseSummaryBlocks = [
      {
        templateUrl: '~/civicase/case/details/summary-tab/case-details-summary-next-milestone.html',
        weight: 0
      }, {
        templateUrl: '~/civicase/case/details/summary-tab/case-details-summary-next-non-milestone-activity.html',
        weight: 5
      }, {
        templateUrl: '~/civicase/case/details/summary-tab/case-details-summary-calendar.html',
        weight: 10
      }
    ];

    CaseDetailsSummaryBlocksProvider.addItems(caseSummaryBlocks);
  });
})(angular);
