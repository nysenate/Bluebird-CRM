(function (angular, $, _) {
  var module = angular.module('civicase');

  module.service('MoveCopyActivityAction', MoveCopyActivityAction);

  /**
   * Move Copy Activity Action Service
   *
   * @param {object} $rootScope rootscope object
   * @param {object} civicaseCrmApi service to call civicrm api
   * @param {object} dialogService service for opening dialog box
   * @param {Function} ts the translation service
   * @param {object} CaseTypeCategory the case type category service
   */
  function MoveCopyActivityAction ($rootScope, civicaseCrmApi, dialogService,
    ts, CaseTypeCategory) {
    /**
     * Check if the Action is enabled
     *
     * @param {object} $scope scope object
     * @returns {boolean} if the action is enabled
     */
    this.isActionEnabled = function ($scope) {
      return $scope.mode !== 'case-files-activity-bulk-action';
    };

    /**
     * Perform the action
     *
     * @param {object} $scope scope object
     * @param {object} action action object
     */
    this.doAction = function ($scope, action) {
      moveCopyActivities($scope.selectedActivities, action.operation, $scope.isSelectAll, $scope.params, $scope.totalCount);
    };

    /**
     * Move/Copy activities
     *
     * @param {Array} activities list of activities
     * @param {string} operation move or copy operation
     * @param {boolean} isSelectAll if select all checkbox is true
     * @param {object} params search parameters for activities to be moved/copied
     * @param {number} totalCount total number of activities, used when isSelectAll is true
     */
    function moveCopyActivities (activities, operation, isSelectAll, params, totalCount) {
      var activitiesCopy = _.cloneDeep(activities);
      var title = operation[0].toUpperCase() + operation.slice(1) +
        ((activities.length === 1)
          ? ts(' %1Activity', { 1: activities[0].type ? activities[0].type + ' ' : '' })
          : ts(' %1 Activities', { 1: isSelectAll ? totalCount : activities.length }));
      var model = {
        ts: ts,
        case_id: (activities.length > 1 || isSelectAll) ? '' : activitiesCopy[0].case_id,
        isSubjectVisible: activities.length === 1,
        subject: (activities.length > 1 || isSelectAll) ? '' : activitiesCopy[0].subject,
        getCaseListApiParams: getCaseListApiParams
      };

      dialogService.open(
        'MoveCopyActCard',
        '~/civicase/activity/actions/services/move-copy-activity-action.html',
        model,
        {
          autoOpen: false,
          height: 'auto',
          width: '40%',
          title: title,
          buttons: [{
            text: ts('Save'),
            icons: { primary: 'fa-check' },
            click: function () {
              moveCopyConfirmationHandler.call(this, operation, model, {
                selectedActivities: activities,
                isSelectAll: isSelectAll,
                searchParams: params
              });
            }
          }]
        }
      );
    }

    /**
     * @returns {object} api parameters for Case.getlist
     */
    function getCaseListApiParams () {
      return {
        params: { search_by_case_id: true }
      };
    }

    /**
     * Handles the click event when the move/copy operation is confirmed
     *
     * @param {string} operation move or copy operation
     * @param {object} model model object for dialog box
     * @param {object} activitiesObject object containing configuration of activities
     */
    function moveCopyConfirmationHandler (operation, model, activitiesObject) {
      var isCaseIdNew = !_.find(activitiesObject.selectedActivities, function (activity) {
        return activity.case_id === model.case_id;
      });

      if (model.case_id && isCaseIdNew) {
        var apiCalls = prepareApiCalls(activitiesObject, operation, model);

        civicaseCrmApi(apiCalls)
          .then(function () {
            $rootScope.$broadcast('civicase::activity::updated');
          });
      }

      $(this).dialog('close');
    }

    /**
     * Prepare the API calls for the move/copy operation
     *
     * @param {object} activitiesObject object containing configuration of activities
     * @param {string} operation move or copy operation
     * @param {object} model model object for dialog box
     * @returns {Array} api call configuration
     */
    function prepareApiCalls (activitiesObject, operation, model) {
      var action = operation === 'copy' ? 'copybyquery' : 'movebyquery';
      var selectedActivitiesIds = _.map(activitiesObject.selectedActivities, 'id');
      var isSingleActivity = selectedActivitiesIds.length === 1;
      var apiCallParams = { case_id: model.case_id };

      if (activitiesObject.isSelectAll) {
        apiCallParams.params = activitiesObject.searchParams;
      } else {
        if (isSingleActivity) {
          apiCallParams.subject = model.subject;
        }

        apiCallParams.id = selectedActivitiesIds;
      }

      return [['Activity', action, apiCallParams]];
    }
  }
})(angular, CRM.$, CRM._);
