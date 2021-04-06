(function (_, angular) {
  var module = angular.module('civicase-base');

  module.service('CaseManagementWorkflow', CaseManagementWorkflow);

  /**
   * Service for case management workflows
   *
   * @param {Function} civicaseCrmApi civicrm api service
   * @param {object} $window window object of the browser
   */
  function CaseManagementWorkflow (civicaseCrmApi, $window) {
    this.getActivityFilters = getActivityFilters;
    this.createDuplicate = createDuplicate;
    this.getEditWorkflowURL = getEditWorkflowURL;
    this.getWorkflowsListForCaseOverview = getWorkflowsListForCaseOverview;
    this.getWorkflowsListForManageWorkflow = getWorkflowsListForCaseOverview;
    this.redirectToWorkflowCreationScreen = redirectToWorkflowCreationScreen;

    /**
     * Get Initial Activity Filters to load dashboard
     *
     * @returns {object} filter
     */
    function getActivityFilters () {
      return {
        case_filter: { 'case_type_id.is_active': 1, contact_is_deleted: 0 }
      };
    }

    /**
     * @param {object} workflow workflow object
     * @returns {Array} api call parameters
     */
    function createDuplicate (workflow) {
      return civicaseCrmApi([
        ['CaseType', 'create', _.extend({}, workflow, { id: null })]
      ]);
    }

    /**
     * @param {string/number} workflow workflow object
     * @returns {string} url to edit workflow page
     */
    function getEditWorkflowURL (workflow) {
      return 'civicrm/a/#/caseType/' + workflow.id;
    }

    /**
     * Returns workflows list for case management
     *
     * @param {object} selectedFilters filters
     * @param {object} page page object needed for pagination
     * @returns {Array} api call parameters
     */
    function getWorkflowsListForCaseOverview (selectedFilters, page) {
      var apiCalls = [
        [
          'CaseType',
          'get', _.extend({}, selectedFilters, {
            sequential: 1,
            options: {
              limit: page.size,
              offset: page.size * (page.num - 1)
            }
          })
        ],
        [
          'CaseType',
          'getcount', selectedFilters
        ]
      ];

      return civicaseCrmApi(apiCalls)
        .then(function (data) {
          return {
            values: data[0].values,
            count: data[1]
          };
        });
    }

    /**
     * Redirect to the workflow creation screen
     */
    function redirectToWorkflowCreationScreen () {
      $window.location.href = '/civicrm/a/#/caseType/new';
    }
  }
})(CRM._, angular);
