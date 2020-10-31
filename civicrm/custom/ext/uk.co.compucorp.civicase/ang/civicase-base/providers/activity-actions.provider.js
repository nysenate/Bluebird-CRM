(function (angular, $, _, ts) {
  var module = angular.module('civicase-base');

  module.provider('ActivityActions', function () {
    var actions = [{
      title: ts('Resume Draft'),
      icon: 'play_circle_filled',
      name: 'ResumeDraft'
    }, {
      title: ts('View in Feed'),
      icon: 'pageview',
      name: 'ViewInFeed'
    }, {
      title: ts('Edit'),
      icon: 'edit',
      name: 'Edit'
    }, {
      title: ts('Print Report'),
      icon: 'print',
      name: 'PrintReport'
    }, {
      title: ts('Move to Case'),
      icon: 'next_week',
      name: 'MoveCopy',
      operation: 'move'
    }, {
      title: ts('Copy to Case'),
      icon: 'filter_none',
      name: 'MoveCopy',
      operation: 'copy'
    }, {
      title: ts('Tag - add to activities'),
      icon: 'add_circle',
      name: 'Tags',
      operation: 'add'
    }, {
      title: ts('Tag - remove from activities'),
      icon: 'remove_circle',
      name: 'Tags',
      operation: 'remove'
    }, {
      title: ts('Download All'),
      icon: 'file_download',
      name: 'DownloadAll'
    }, {
      showDividerBeforeThisAction: true,
      title: ts('Delete'),
      icon: 'delete',
      name: 'Delete'
    }];

    this.$get = function () {
      return actions;
    };
  });
})(angular, CRM.$, CRM._, window.ts);
