(function (angular, $, _) {
  var module = angular.module('civicase');

  module.directive('civicaseActivityPanel', function ($timeout,
    ActivityFeedMeasurements, ActivityForms, BulkActions) {
    return {
      restrict: 'A',
      templateUrl: '~/civicase/activity/panel/directives/activity-panel.directive.html',
      controller: civicaseActivityPanelController,
      link: civicaseActivityPanelLink,
      scope: {
        activity: '=civicaseActivityPanel',
        refresh: '=refreshCallback'
      }
    };

    /**
     * Link function for civicaseActivityPanelLink
     *
     * @param {object} scope scope object
     * @param {object} element directive element
     * @param {object} attrs attributes
     */
    function civicaseActivityPanelLink (scope, element, attrs) {
      scope.canChangeStatus = true;

      (function init () {
        $timeout(setPanelHeight);
        scope.$on('civicase::activity-feed::show-activity-panel', loadActivityForm);
        element.on('crmFormSuccess', scope.refresh);
        element.on('crmLoad', crmLoadListener);
      }());

      /**
       * Listener for crmLoad event
       */
      function crmLoadListener () {
        // Workaround bug where href="#" changes the angular route
        $('a.crm-clear-link', this).removeAttr('href');
        $('a.delete.button', this).click(onDeleteClickEvent);

        if (!BulkActions.isAllowed()) {
          $('div.crm-submit-buttons').remove();
        }

        // Scrolls the details panel to top once new data loads
        element.scrollTop(0);
      }

      /**
       * Listener for loadActivityForm event
       *
       * @param {object} event event
       * @param {object} activity activity
       */
      function loadActivityForm (event, activity) {
        var activityFormOptions = { action: 'view' };
        var activityForm = ActivityForms.getActivityFormService(activity, activityFormOptions);

        if (!activityForm) {
          return;
        }

        scope.canChangeStatus = activityForm.canChangeStatus;

        CRM
          .loadForm(activityForm.getActivityFormUrl(activity, activityFormOptions), {
            target: $(element).find('.civicase__activity-panel__core_container')
          })
          .one('crmAjaxError', function () {
            scope.$apply(function () {
              scope.closeDetailsPanel();
            });
          });

        element.find('.crm-submit-buttons a.edit').addClass('btn btn-primary');
      }

      /**
       * Listener for click event of delete button
       *
       * @returns {boolean} false
       */
      function onDeleteClickEvent () {
        var activityTypeName = scope.civicaseTs(scope.activity.type);

        CRM.confirm({
          title: scope.ts('Delete Activity'),
          message: scope.ts('Permanently delete this %1 activity?', { 1: activityTypeName })
        }).on('crmConfirm:yes', function () {
          $(element).children('.civicase__activity-panel__core_container').block();
          CRM.api3('Activity', 'delete', { id: scope.activity.id })
            .done(scope.close)
            .done(scope.refresh);
        });

        return false;
      }

      /**
       * Set height for activity panel
       */
      function setPanelHeight () {
        var $feedPanel = $('.civicase__activity-feed__body__details');

        ActivityFeedMeasurements.setScrollHeightOf($feedPanel);
      }
    }
  });

  /**
   * Activity Panel Controller
   *
   * @param {object} $rootScope root scope object
   * @param {object} $scope scope object
   * @param {object} ActivityStatus activity status service
   * @param {Function} checkIfDraftActivity check if activity function
   * @param {object} Priority priority service
   */
  function civicaseActivityPanelController ($rootScope, $scope, ActivityStatus,
    checkIfDraftActivity, Priority) {
    $scope.activityPriorties = Priority.getAll();
    $scope.allowedActivityStatuses = {};
    $scope.closeDetailsPanel = closeDetailsPanel;
    $scope.setStatusTo = setStatusTo;
    $scope.setPriorityTo = setPriorityTo;
    $scope.checkIfDraftEmailOrPDFActivity = checkIfDraftEmailOrPDFActivity;

    (function init () {
      $scope.$watch('activity.id', showActivityDetails);
      $scope.$on('civicase::case-details::unfocused', closeDetailsPanel);
    }());

    /**
     * @param {object} activity an activity object
     * @returns {boolean} true when the given activity is either an email
     *  or PDF letter activity and their status is set to draft.
     */
    function checkIfDraftEmailOrPDFActivity (activity) {
      return checkIfDraftActivity(activity, ['Email', 'Print PDF Letter']);
    }

    /**
     * Close the activity details panel
     */
    function closeDetailsPanel () {
      delete $scope.activity.id;

      $rootScope.$broadcast('civicase::activity-feed::hide-activity-panel');
    }

    /**
     * Set status of sent activity
     *
     * @param {object} activity activity
     * @param {object} activityStatusId activity status id
     */
    function setStatusTo (activity, activityStatusId) {
      activity.status_id = activityStatusId;
      // Setvalue api avoids messy revisioning issues
      $scope.refresh([['Activity', 'setvalue', { id: activity.id, field: 'status_id', value: activity.status_id }]], true);
    }

    /**
     * Set priority of sent activity
     *
     * @param {object} activity activity
     * @param {object} priorityId priority id
     */
    function setPriorityTo (activity, priorityId) {
      activity.priority_id = priorityId;
      // Setvalue api avoids messy revisioning issues
      $scope.refresh([['Activity', 'setvalue', { id: activity.id, field: 'priority_id', value: activity.priority_id }]], true);
    }

    /**
     * Set Allowed Activity status's
     */
    function setAllowedActivityStatuses () {
      $scope.allowedActivityStatuses = {};

      _.each(ActivityStatus.getAll(), function (activityStatus, activityStatusID) {
        var statusGrouping = activityStatus.grouping ? activityStatus.grouping.split(',') : [];
        var ifStatusIsInSameCategory = _.intersection($scope.activity.category, statusGrouping).length > 0;
        var ifStatusIsInNoneCategory = $scope.activity.category.length === 0 && statusGrouping.indexOf('none') !== -1;
        var ifStatusIsForAllCategories = statusGrouping.length === 0;

        if (ifStatusIsInSameCategory || ifStatusIsInNoneCategory || ifStatusIsForAllCategories) {
          $scope.allowedActivityStatuses[activityStatusID] = activityStatus;
        }
      });
    }

    /**
     * Show activity details
     */
    function showActivityDetails () {
      if ($scope.activity.id) {
        setAllowedActivityStatuses();

        $rootScope.$broadcast('civicase::activity-feed::show-activity-panel', $scope.activity);
      }
    }
  }
})(angular, CRM.$, CRM._);
