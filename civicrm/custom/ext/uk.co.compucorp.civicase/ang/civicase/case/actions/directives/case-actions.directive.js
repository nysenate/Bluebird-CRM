(function (angular, $, _) {
  var module = angular.module('civicase');

  module.directive('civicaseCaseActions', function ($window, $rootScope, $injector, allowCaseLocks,
    CaseActions, dialogService, PrintMergeCaseAction) {
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

      $scope.hasSubMenu = function (action) {
        return (action.items && action.items.length);
      };

      $scope.isActionEnabled = function (action) {
        return (!action.number || $scope.cases.length === +action.number);
      };

      $scope.isActionAllowed = function (action) {
        var isActionAllowed = true;
        var isLockCaseAction = _.startsWith(action.action, 'lockCases');
        var isCaseLockAllowed = allowCaseLocks;
        var caseActionService = getCaseActionService(action.action);

        if (caseActionService && caseActionService.isActionAllowed) {
          isActionAllowed = caseActionService.isActionAllowed(action, $scope.cases, attributes);
        }

        return isActionAllowed && ((isLockCaseAction && isCaseLockAllowed) ||
          (!isLockCaseAction && (!action.number || ((isBulkMode && action.number > 1) || (!isBulkMode && action.number === 1)))));
      };

      // Perform bulk actions
      $scope.doAction = function (action) {
        var caseActionService = getCaseActionService(action.action);

        if (!$scope.isActionEnabled(action) || !caseActionService) {
          return;
        }

        var result = caseActionService.doAction($scope.cases, action, $scope.refresh);
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
          var dialog = CRM.loadForm(url)
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
      };

      $scope.$watchCollection('cases', function (cases) {
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
      });

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
