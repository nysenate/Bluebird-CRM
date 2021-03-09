(function ($, _, angular, getCrmUrl) {
  var module = angular.module('workflow');

  module.controller('WorkflowDuplicateController', WorkflowDuplicateController);

  /**
   * @param {object} $scope scope object
   * @param {object} $rootScope root scope object
   * @param {object} $q angular's queue service
   * @param {object} $injector angular's injector service
   * @param {object} dialogService service to open dialog box
   * @param {object} crmStatus civicrm status service
   * @param {object} CaseTypeCategory case type category service
   * @param {object} pascalCase service to convert a string to pascal case
   */
  function WorkflowDuplicateController ($scope, $rootScope, $q, $injector,
    dialogService, crmStatus, CaseTypeCategory, pascalCase) {
    $scope.submitInProgress = false;
    $scope.clickHandler = clickHandler;

    /**
     * Opens a popup to prompt user for the new workflow name
     *
     * @param {object} workflow workflow object
     */
    function clickHandler (workflow) {
      var model = {
        workflow: _.clone(workflow)
      };

      model.workflow.title = '';
      model.workflow.name = '';

      if (dialogService.dialogs.WorkflowDuplicate) {
        return;
      }

      dialogService.open(
        'WorkflowDuplicate',
        '~/workflow/action-links/directives/workflow-list-duplicate-popup.html',
        model,
        {
          autoOpen: false,
          height: 'auto',
          width: '40%',
          title: 'Duplicate Workflow',
          buttons: [{
            text: ts('Save'),
            icons: { primary: 'fa-check' },
            click: function () {
              if (ifSaveButtonDisabled(model.workflow_duplicate_form)) {
                return;
              }

              createDuplicateWorkflow(model.workflow)
                .then(function () {
                  dialogService.close('WorkflowDuplicate');
                });
            }
          }]
        }
      );
    }

    /**
     * Check if Save button should be disabled
     *
     * @param {object} workflowDuplicateForm duplicate workflow form
     * @returns {boolean} if Save button should be disabled
     */
    function ifSaveButtonDisabled (workflowDuplicateForm) {
      return !workflowDuplicateForm.$valid || $scope.submitInProgress;
    }

    /**
     * Create Duplicate Worklfow
     *
     * @param {object} workflow workflow object
     * @returns {Promise} promise
     */
    function createDuplicateWorkflow (workflow) {
      $scope.submitInProgress = true;

      var promise = saveDuplicateWorkflow(workflow)
        .then(function () {
          $rootScope.$broadcast('workflow::list::refresh');
        })
        .catch(function (error) {
          var errorMessage;

          _.each(error, function (errorObj) {
            if (errorObj.error_code === 'already exists') {
              errorMessage = ts('This title is already in use. Please choose another');

              return false;
            } else {
              errorMessage = errorObj.error_message;
            }
          });

          return $q.reject({
            error_message: errorMessage
          });
        })
        .finally(function () {
          $scope.submitInProgress = false;
        });

      return crmStatus({
        start: $scope.ts('Duplicating Workflow...'),
        success: $scope.ts('Duplicate created successfully')
      }, promise);
    }

    /**
     * Save duplicate workflow by calling the API
     *
     * @param {object} workflow workflow object
     * @returns {Promise} Promise
     */
    function saveDuplicateWorkflow (workflow) {
      workflow.name = generateWorkflowName(workflow.title);
      workflow.sequential = true;

      var instanceName = CaseTypeCategory.getCaseTypeCategoryInstance(workflow.case_type_category).name;

      var service = getServiceToDuplicate(instanceName);

      return service.createDuplicate(_.clone(workflow));
    }

    /**
     * Searches for a angularJS service for the current case type category
     * instance, if not found, returns the service for case management service
     * as default.
     *
     * @param {string} instanceName name of the instance
     * @returns {object/null} service
     */
    function getServiceToDuplicate (instanceName) {
      var CASE_MANAGEMENT_INSTACE_NAME = 'case_management';

      try {
        return $injector.get(
          pascalCase(instanceName) + 'Workflow'
        );
      } catch (e) {
        return $injector.get(
          pascalCase(CASE_MANAGEMENT_INSTACE_NAME) + 'Workflow'
        );
      }
    }

    /**
     * Generate workflow `name` from the sent text
     *
     * @param {string} text string from which name would be generated
     * @returns {string} name of the workflow
     */
    function generateWorkflowName (text) {
      return text
        .replace(/ /g, '_')
        .replace(/[^a-zA-Z0-9_]/g, '')
        .toLowerCase();
    }
  }
})(CRM.$, CRM._, angular, CRM.url);
