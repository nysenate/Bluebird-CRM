(function (angular, $, _) {
  var module = angular.module('civicase');

  module.service('TagsActivityAction', TagsActivityAction);

  /**
   * Tags Activity Action Service
   *
   * @param {object} $rootScope rootscope object
   * @param {object} civicaseCrmApi service to use civicrm api
   * @param {object} dialogService service to open dialog box
   */
  function TagsActivityAction ($rootScope, civicaseCrmApi, dialogService) {
    /**
     * Check if the Action is enabled
     *
     * @param {object} $scope scope object
     * @returns {boolean} if the action is enabled
     */
    this.isActionEnabled = function ($scope) {
      return $scope.mode === 'case-activity-bulk-action';
    };

    /**
     * Perform the action
     *
     * @param {object} $scope scope object
     * @param {object} action action object
     */
    this.doAction = function ($scope, action) {
      manageTags(
        action.operation,
        $scope.selectedActivities,
        $scope.isSelectAll,
        $scope.params,
        $scope.totalCount
      );
    };

    /**
     * Add/Remove tags to activities
     *
     * @param {string} operation add or remove operation
     * @param {Array} activities list of activities
     * @param {boolean} isSelectAll if select all checkbox is true
     * @param {object} params search parameters for activities to be moved/copied
     * @param {number} totalCount total number of activities, used when isSelectAll is true
     */
    function manageTags (operation, activities, isSelectAll, params, totalCount) {
      var title, saveButtonLabel;
      title = saveButtonLabel = 'Tag Activities';

      if (operation === 'remove') {
        title += ' (Remove)';
        saveButtonLabel = 'Remove tags from Activities';
      }

      getTags()
        .then(function (tags) {
          var model = setModelObjectForModal(tags, activities.length || totalCount);

          openTagsModal(model, title, saveButtonLabel, operation, {
            selectedActivities: activities,
            isSelectAll: isSelectAll,
            searchParams: params
          });
        });
    }

    /**
     * Set the model object to be used in the modal
     *
     * @param {Array} tags tags
     * @param {number} numberOfActivities number of activities
     * @returns {object} model object for the dialog box
     */
    function setModelObjectForModal (tags, numberOfActivities) {
      var model = {};

      model.allTags = tags;
      model.selectedTags = [];
      model.selectedActivitiesLength = numberOfActivities;

      return model;
    }

    /**
     * Opens the modal for addition/removal of tags
     *
     * @param {object} model model object for dialog box
     * @param {string} title title of the dialog box
     * @param {string} saveButtonLabel label for the save button
     * @param {string} operation name of the operation, add/ remove
     * @param {object} activitiesObject object containing configuration of activities
     */
    function openTagsModal (model, title, saveButtonLabel, operation, activitiesObject) {
      dialogService.open('TagsActivityAction', '~/civicase/activity/actions/services/tags-activity-action.html', model, {
        autoOpen: false,
        height: 'auto',
        width: '450px',
        title: title,
        buttons: [{
          text: saveButtonLabel,
          icons: operation === 'add' ? { primary: 'fa-check' } : false,
          click: function () {
            addRemoveTagsConfirmationHandler.call(this, operation, activitiesObject, model);
          }
        }]
      });
    }

    /**
     * Add/Remove tags confirmation handler
     *
     * @param {string} operation name of the operation, add/ remove
     * @param {object} activitiesObject object containing configuration of activities
     * @param {object} model model object for dialog box
     */
    function addRemoveTagsConfirmationHandler (operation, activitiesObject, model) {
      var apiCalls = prepareApiCalls(operation, activitiesObject, model.selectedTags);

      civicaseCrmApi(apiCalls)
        .then(function () {
          $rootScope.$broadcast('civicase::activity::updated');
        });

      $(this).dialog('close');
    }

    /**
     * Prepare the API calls for the Add/Remove operation
     *
     * @param {string} operation name of the operation, add/ remove
     * @param {object} activitiesObject object containing configuration of activities
     * @param {Array} tagIds list of tag ids
     * @returns {Array} configuration for the api call
     */
    function prepareApiCalls (operation, activitiesObject, tagIds) {
      var action = operation === 'add' ? 'createByQuery' : 'deleteByQuery';

      if (activitiesObject.isSelectAll) {
        return [['EntityTag', action, {
          entity_table: 'civicrm_activity',
          tag_id: tagIds,
          params: activitiesObject.searchParams
        }]];
      } else {
        return [['EntityTag', action, {
          entity_table: 'civicrm_activity',
          tag_id: tagIds,
          entity_id: activitiesObject.selectedActivities.map(function (activity) {
            return activity.id;
          })
        }]];
      }
    }

    /**
     * Get the tags for Activities from API end point
     *
     * @returns {Promise} api call promise
     */
    function getTags () {
      return civicaseCrmApi('Tag', 'get', {
        sequential: 1,
        used_for: { LIKE: '%civicrm_activity%' },
        options: { limit: 0 }
      }).then(function (data) {
        return data.values;
      });
    }
  }
})(angular, CRM.$, CRM._);
