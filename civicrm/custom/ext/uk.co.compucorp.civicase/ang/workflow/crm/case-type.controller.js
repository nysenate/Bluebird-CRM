(function ($, _, angular) {
  var module = angular.module('workflow');

  module.controller('CivicaseCaseTypeController', CivicaseCaseTypeController);

  /**
   * @param {object} $q angular queue service
   * @param {Function} $controller controller service
   * @param {object} $scope controller's scope object
   * @param {Function} crmApi service to access civicrm api
   * @param {Array} apiCalls api calls
   * @param {object} $window browsers window object
   * @param {object} CaseTypeCategory case type category service
   */
  function CivicaseCaseTypeController ($q, $controller, $scope, crmApi, apiCalls, $window, CaseTypeCategory) {
    (function init () {
      initParentController();
    })();

    $scope.goto = goto;

    /**
     * Creates a fake crmApi service, which is then feeded to `CaseTypeCtrl` in
     * CiviCRM core's "modules/contrib/civicrm/ang/crmCaseType.js" file
     *
     * This is done, because we need to override the $scope.save function of
     * the `CaseTypeCtrl` and redirect to a different url.
     *
     * This function applies a custom save logic when `CaseType.create` api is
     * called, otherwise it calls the original `crmApi` service itself.
     *
     * @param {string} entity entity name
     * @param {string} action action name
     * @param {object} params paramters
     * @param {string} message message
     * @returns {Promise} promise
     */
    function fakeCrmApi (entity, action, params, message) {
      if (entity === 'CaseType' && action === 'create') {
        saveCaseType();

        return $q.defer().promise;
      } else {
        return crmApi(entity, action, params, message);
      }
    }

    /**
     * Overrides the $rootScope.goto function from civicrm core
     */
    function goto () {
      if ($scope.caseType.id) {
        $window.location.href = getUrlToManageWorkflowPage($scope.caseType.case_type_category);
      } else {
        $window.location.href = '/civicrm/a/#/caseType';
      }
    }

    /**
     * Initialise the CaseTypeCtrl controller
     */
    function initParentController () {
      $controller('CaseTypeCtrl', {
        $scope: $scope,
        crmApi: fakeCrmApi,
        apiCalls: apiCalls
      });
    }

    /**
     * Saves the case type
     * The logic is similar to the save function of `CaseTypeCtrl` in
     * CiviCRM core's "modules/contrib/civicrm/ang/crmCaseType.js" file.
     * Only the redirection url is different.
     */
    function saveCaseType () {
      var result = crmApi('CaseType', 'create', $scope.caseType, true);

      result.then(function (data) {
        if (data.is_error === 0 || data.is_error === '0') {
          $scope.caseType.id = data.id;
          $window.location.href = getUrlToManageWorkflowPage(Object.values(data.values)[0].case_type_category);
        }
      });
    }

    /**
     * @param {string} caseTypeCategoryId case type category id
     * @returns {string} url
     */
    function getUrlToManageWorkflowPage (caseTypeCategoryId) {
      var caseTypeCategoryName = CaseTypeCategory.findById(caseTypeCategoryId).name;

      return '/civicrm/workflow/a?case_type_category=' + caseTypeCategoryName + '#/list';
    }
  }
})(CRM.$, CRM._, angular);
