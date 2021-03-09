(function (angular, $, _) {
  var module = angular.module('civicase');

  module.directive('civicaseCaseActions', function ($q, $rootScope,
    $injector, allowCaseLocks, CaseActions, civicaseCrmLoadForm) {
    return {
      restrict: 'A',
      templateUrl: '~/civicase/case/actions/directives/case-actions.directive.html',
      scope: {
        cases: '=civicaseCaseActions',
        refresh: '=refreshCallback',
        popupParams: '='
      },
      link: civicaseCaseActionsLink
    };

    /**
     * Angular JS's link function for civicaseCaseActions Directive
     *
     * @param {object} $scope the directive's scope
     * @param {object} element element reference
     * @param {object} attributes the element attributes
     */
    function civicaseCaseActionsLink ($scope, element, attributes) {
      var CASE_ACTIONS = CaseActions.getAll();
      var ts = CRM.ts('civicase');
      var isBulkMode = attributes.isBulkMode;

      $scope.doAction = doAction;
      $scope.hasSubMenu = hasSubMenu;
      $scope.isActionEnabled = isActionEnabled;
      $scope.isActionAllowed = isActionAllowed;

      (function init () {
        $scope.$watchCollection('cases', casesWatcher);
      }());

      /**
       * Check if the sent action has any sub menu.
       *
       * @param {object} action action object
       * @returns {boolean} if the sent action has any sub menu.
       */
      function hasSubMenu (action) {
        return !!(action.items && action.items.length);
      }

      /**
       * Check if the sent action is enabled.
       *
       * An action is disabled when:
       * - They can modify the target cases and the cases have been disabled.
       * - The right number of cases have not been selected.
       *
       * @param {object} action action object
       * @returns {boolean} if the sent action is enabled.
       */
      function isActionEnabled (action) {
        var hasADisabledCaseType = _.some(
          $scope.cases,
          _.matches({ 'case_type_id.is_active': '0' })
        );

        if (action.is_write_action !== false && hasADisabledCaseType) {
          return false;
        }

        return (!action.number || $scope.cases.length === +action.number);
      }

      /**
       * Check if the sent action is allowed.
       *
       * @param {object} action action object
       * @returns {boolean} if the sent action is allowed.
       */
      function isActionAllowed (action) {
        var isActionAllowed = true;
        var isLockCaseAction = _.startsWith(action.action, 'LockCases');
        var isCaseLockAllowed = allowCaseLocks;
        var caseActionService = getCaseActionService(action.action);

        if (caseActionService && caseActionService.isActionAllowed) {
          isActionAllowed = caseActionService.isActionAllowed(action, $scope.cases, attributes);
        }

        return isActionAllowed && ((isLockCaseAction && isCaseLockAllowed) ||
          (!isLockCaseAction && (
            !action.number ||
            ((isBulkMode && action.number > 1) || (!isBulkMode && action.number === 1))
          )));
      }

      /**
       * Perform the action for the sent action object
       *
       * @param {object} action action object
       */
      function doAction (action) {
        var caseActionService = getCaseActionService(action.action);

        if (!$scope.isActionEnabled(action) || !caseActionService) {
          return;
        }

        $q.when(caseActionService.doAction($scope.cases, action, $scope.refresh))
          .then(function (result) {
            // Open popup if callback returns a path & query
            // TODO Move the following code into a service, and the Serivces which
            // returns an URL, should call this newly created service directly.
            if (result) {
              var url = '';
              if (angular.isObject(result)) {
                // Add refresh data
                if ($scope.popupParams) {
                  result.query.civicase_reload = $scope.popupParams();
                }

                url = CRM.url(result.path, result.query);
              } else {
                url = result;
              }

              // Mimic the behavior of CRM.popup()
              var formData = false;
              var dialog = civicaseCrmLoadForm(url)
                // Listen for success events and buffer them so we only trigger once
                .on('crmFormSuccess crmPopupFormSuccess', function (e, data) {
                  formData = data;
                  $rootScope.$broadcast('updateCaseData');
                  refreshDataForActions();
                })
                .on('dialogclose.crmPopup', function (e, data) {
                  if (formData) {
                    element.trigger('crmPopupFormSuccess', [dialog, formData]);
                  }

                  element.trigger('crmPopupClose', [dialog, data]);
                });
            }
          });
      }

      /**
       * Watcher function for cases object of scope
       *
       * @param {object[]} cases list of cases
       */
      function casesWatcher (cases) {
        // Special actions when viewing deleted cases
        if (cases.length && cases[0].is_deleted) {
          $scope.caseActions = [
            { action: 'DeleteCases', type: 'delete', title: ts('Delete Permanently') },
            { action: 'DeleteCases', type: 'restore', title: ts('Restore from Trash') }
          ];
        } else {
          $scope.caseActions = _.cloneDeep(CASE_ACTIONS);

          if (!isBulkMode) {
            _.remove($scope.caseActions, { action: 'changeStatus(cases)' });
          }
        }
        refreshDataForActions();
      }

      /**
       * Get Case Action Service using the action's name.
       *
       * @param {string} action the action name.
       * @returns {object} a service reference or null.
       */
      function getCaseActionService (action) {
        try {
          return $injector.get(action + 'CaseAction');
        } catch (e) {
          return null;
        }
      }

      /**
       * Refreshes the case data for each one of the defined actions.
       */
      function refreshDataForActions () {
        _.each($scope.caseActions, function (action) {
          var caseActionService = getCaseActionService(action.action);

          if (caseActionService && caseActionService.refreshData) {
            caseActionService.refreshData($scope.cases);
          }
        });
      }
    }
  });
})(angular, CRM.$, CRM._);
