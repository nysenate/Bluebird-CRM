(function (angular, $, _) {
  var module = angular.module('civicase');

  module.directive('caseActivityCard', function () {
    return {
      restrict: 'A',
      templateUrl: function (elem, attrs) {
        switch (attrs.mode) {
          case 'big':
            return '~/civicase/activity/card/directives/activity-card-big.directive.html';
          case 'long':
            return '~/civicase/activity/card/directives/activity-card-long.directive.html';
          default:
            return '~/civicase/activity/card/directives/activity-card-short.directive.html';
        }
      },
      controller: caseActivityCardController,
      link: caseActivityCardLink,
      replace: true,
      scope: {
        activity: '=caseActivityCard',
        case: '=?',
        isReadOnly: '<',
        customDropdownClass: '@',
        refresh: '=refreshCallback',
        refreshOnCheckboxToggle: '=?',
        bulkAllowed: '=',
        type: '=type',
        customClickEvent: '='
      }
    };

    /**
     * Link function for caseActivityCard
     *
     * @param {object} scope scope object of the controller
     */
    function caseActivityCardLink (scope) {
      scope.bootstrapThemeElement = $('#bootstrap-theme');
    }
  });

  module.controller('caseActivityCardController', caseActivityCardController);

  /**
   * @param {Function} $filter a reference to the $filter service
   * @param {object} $scope scope object of the controller
   * @param {object} CaseType a reference to the Case Type service
   * @param {object} CaseTypeCategory a reference to the Case Type Category service
   * @param {object} dialogService service to open the dialog box
   * @param {Function} civicaseCrmApi service to interact with the civicrm api
   * @param {object} crmBlocker crm blocker service
   * @param {object} crmStatus crm status service
   * @param {object} DateHelper date helper service
   * @param {object} ts ts service
   * @param {Function} viewInPopup factory to view an activity in a popup
   * @param {Function} isTruthy service to check if value is truthy
   */
  function caseActivityCardController ($filter, $scope, CaseType,
    CaseTypeCategory, dialogService, civicaseCrmApi, crmBlocker, crmStatus,
    DateHelper, ts, viewInPopup, isTruthy) {
    var caseTypeCategories = CaseTypeCategory.getAll();

    $scope.areFromAndToFieldsVisible = false;
    $scope.ts = ts;
    $scope.formatDate = DateHelper.formatDate;

    (function init () {
      var hasCase = $scope.activity && !_.isEmpty($scope.activity.case);

      $scope.areFromAndToFieldsVisible = getFromAndToFieldsVisibilityStatus();

      if (hasCase) {
        $scope.caseDetailUrl = getCaseDetailUrl();
      }
    })();

    /**
     * Mark an activity as complete
     *
     * @param {object} activity activity object
     * @returns {Promise} api call promise
     */
    $scope.markCompleted = function (activity) {
      return civicaseCrmApi([['Activity', 'create', {
        id: activity.id,
        status_id: activity.is_completed ? 'Scheduled' : 'Completed'
      }]])
        .then(function (data) {
          if (!data[0].is_error) {
            activity.is_completed = !activity.is_completed;
            $scope.refreshOnCheckboxToggle && $scope.refresh();
          }
        });
    };

    /**
     * Toggle an activity as favourite
     *
     * @param {object} $event event object
     * @param {object} activity activity object
     */
    $scope.toggleActivityStar = function ($event, activity) {
      $event.stopPropagation();
      activity.is_star = isTruthy(activity.is_star) ? '0' : '1';
      // Setvalue api avoids messy revisioning issues
      $scope.refresh([['Activity', 'setvalue', {
        id: activity.id,
        field: 'is_star',
        value: activity.is_star
      }]], true);
    };

    /**
     * Click handler for Activity Card
     *
     * @param {object} $event event object
     * @param {object} activity activity object
     */
    $scope.viewActivityDetails = function ($event, activity) {
      if ($scope.customClickEvent) {
        $scope.$emit('civicaseAcitivityClicked', $event, activity);
      } else {
        $scope.viewInPopup($event, activity);
      }
    };

    /**
     * View the sent activity details in the popup
     *
     * @param {object} $event event object
     * @param {object} activity activity object
     */
    $scope.viewInPopup = function ($event, activity) {
      var response = viewInPopup($event, activity, {
        isReadOnly: $scope.isReadOnly
      });

      if (response) {
        response
          .on('crmFormSuccess', function () {
            $scope.refresh();
          });
      }
    };

    /**
     * Gets attachments for an activity
     *
     * @param {object} activity activity object
     */
    $scope.getAttachments = function (activity) {
      if (!activity.attachments) {
        activity.attachments = [];
        CRM.api3('Attachment', 'get', {
          entity_table: 'civicrm_activity',
          entity_id: activity.id,
          sequential: 1
        }).done(function (data) {
          activity.attachments = data.values;
          $scope.$digest();
        });
      }

      /**
       * Deletes file of an activity
       *
       * @param {object} activity activity object
       * @param {object} file file object
       * @returns {Promise} promise
       */
      $scope.deleteFile = function (activity, file) {
        var promise = civicaseCrmApi('Attachment', 'delete', { id: file.id })
          .then(function () {
            $scope.refresh();
          });

        return crmBlocker(crmStatus({
          start: $scope.ts('Deleting...'),
          success: $scope.ts('Deleted')
        }, promise));
      };
    };

    /**
     * @returns {string} The URL to the case details for the case related to
     * this activity. The case type category name is appended in order to
     * validate the right permissions.
     */
    function getCaseDetailUrl () {
      var caseTypeId = $scope.activity.case.case_type_id;
      var caseType = CaseType.getById(caseTypeId);
      var caseTypeCategory = caseTypeCategories[caseType.case_type_category];
      var caseDetailUrl = 'civicrm/case/a/?' +
        $.param({ case_type_category: caseTypeCategory.name }) +
        '#/case/list';
      var angularParams = $.param({
        caseId: $scope.activity.case_id,
        cf: JSON.stringify({
          'case_type_id.is_active': caseType.is_active
        })
      });

      return $filter('civicaseCrmUrl')(caseDetailUrl) + '?' + angularParams;
    }

    /**
     * Determines whether to show or hide the "From" and "To" fields from the view.
     *
     * @returns {boolean} true when the current activity belongs to the communication group,
     *   but it's not a Print/Merge Document activity.
     */
    function getFromAndToFieldsVisibilityStatus () {
      var isNotPrintPdfActivity = $scope.activity && $scope.activity.type !== 'Print/Merge Document';
      var isCommunicationActivity = (($scope.activity && $scope.activity.category) || [])
        .indexOf('communication') >= 0;

      return isCommunicationActivity && isNotPrintPdfActivity;
    }
  }
})(angular, CRM.$, CRM._);
