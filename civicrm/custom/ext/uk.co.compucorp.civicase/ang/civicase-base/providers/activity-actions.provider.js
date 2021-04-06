(function (angular, $, _, ts) {
  var module = angular.module('civicase-base');

  module.provider('ActivityActions', function () {
    var actions = [{
      title: ts('Resume Draft'),
      icon: 'play_circle_filled',
      name: 'ResumeDraft',
      isWriteAction: true
    }, {
      title: ts('View in Feed'),
      icon: 'pageview',
      name: 'ViewInFeed',
      isWriteAction: false
    }, {
      title: ts('Edit'),
      icon: 'edit',
      name: 'Edit',
      isWriteAction: true
    }, {
      title: ts('Print Report'),
      icon: 'print',
      name: 'PrintReport',
      isWriteAction: false
    }, {
      title: ts('Move to Case'),
      icon: 'next_week',
      name: 'MoveCopy',
      operation: 'move',
      isWriteAction: true
    }, {
      title: ts('Copy to Case'),
      icon: 'filter_none',
      name: 'MoveCopy',
      operation: 'copy',
      isWriteAction: false
    }, {
      title: ts('Tag - add to activities'),
      icon: 'add_circle',
      name: 'Tags',
      operation: 'add',
      isWriteAction: true
    }, {
      title: ts('Tag - remove from activities'),
      icon: 'remove_circle',
      name: 'Tags',
      operation: 'remove',
      isWriteAction: true
    }, {
      title: ts('Download All'),
      icon: 'file_download',
      name: 'DownloadAll',
      isWriteAction: false
    }, {
      showDividerBeforeThisAction: true,
      title: ts('Delete'),
      icon: 'delete',
      name: 'Delete',
      isWriteAction: true
    }];

    this.$get = function () {
      return actions;
    };
  });
})(angular, CRM.$, CRM._, window.ts);
