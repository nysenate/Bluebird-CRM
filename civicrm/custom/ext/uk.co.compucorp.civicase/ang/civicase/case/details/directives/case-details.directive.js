(function (angular, $, _) {
  var module = angular.module('civicase');

  module.directive('civicaseCaseDetails', function () {
    return {
      restrict: 'A',
      templateUrl: '~/civicase/case/details/directives/case-details.directive.html',
      controller: 'civicaseCaseDetailsController',
      scope: {
        activeTab: '=civicaseTab',
        isFocused: '=civicaseFocused',
        viewingCaseId: '=',
        item: '=civicaseCaseDetails',
        showClearfiltersUi: '=',
        caseTypeCategory: '='
      }
    };
  });

  module.controller('civicaseCaseDetailsController', civicaseCaseDetailsController);

  /**
   * Case Details Controller
   *
   * @param {Function} civicaseCrmUrl crm url service.
   * @param {object} $sce angular Strict Contextual Escaping service
   * @param {object} $rootScope $rootScope
   * @param {object} $scope $scope
   * @param {object} $document $document
   * @param {boolean} allowLinkedCasesTab allow linked case page setting value
   * @param {object} BulkActions bulk actions service
   * @param {object[]} CaseDetailsTabs list of case tabs
   * @param {object} civicaseCrmApi civicase crm api service
   * @param {object} formatActivity format activity service
   * @param {object} formatCase format case service
   * @param {object} getActivityFeedUrl get activity feed url service
   * @param {object} getCaseQueryParams get case query params service
   * @param {object} $route $route service
   * @param {object} $timeout $timeout service
   * @param {object} crmStatus service to show status notifications
   * @param {object} CasesUtils cases utils service
   * @param {object} PrintMergeCaseAction print merge case action service
   * @param {object} ts ts service
   * @param {object} ActivityType activity type service
   * @param {object} CaseStatus case status service
   * @param {object} CaseType case type service
   * @param {object} CaseDetailsSummaryBlocks case details summary blocks
   * @param {object} DetailsCaseTab case details case tab service reference
   */
  function civicaseCaseDetailsController (civicaseCrmUrl, $sce, $rootScope, $scope,
    $document, allowLinkedCasesTab, BulkActions, CaseDetailsTabs, civicaseCrmApi,
    formatActivity, formatCase, getActivityFeedUrl, getCaseQueryParams, $route,
    $timeout, crmStatus, CasesUtils, PrintMergeCaseAction, ts, ActivityType,
    CaseStatus, CaseType, CaseDetailsSummaryBlocks, DetailsCaseTab) {
    // Makes the scope available to child directives when they require this parent directive:
    this.$scope = $scope;

    // The ts() and hs() functions help load strings for this module.
    // TODO: Move the common logic into a common controller (based on the usage of ContactCaseTabCaseDetails)
    $scope.ts = ts;
    var caseStatuses = $scope.caseStatuses = CaseStatus.getAll();
    var activityTypes = $scope.activityTypes = ActivityType.getAll(true);
    var panelLimit = 5;

    $scope.areDetailsLoaded = false;
    $scope.areRelatedCasesVisibleOnSummaryTab = false;
    $scope.relatedCasesPager = { total: 0, size: 5, num: 0, range: {} };
    $scope.getActivityFeedUrl = getActivityFeedUrl;
    $scope.bulkAllowed = BulkActions.isAllowed();
    $scope.caseDetailsSummaryBlocks = CaseDetailsSummaryBlocks;
    $scope.caseTypesLength = _.size(CaseType.getAll());
    $scope.CRM = CRM;
    $scope.tabs = _.cloneDeep(CaseDetailsTabs);
    $scope.trustAsHtml = $sce.trustAsHtml;
    $scope.isMainContentVisible = isMainContentVisible;
    $scope.isPlaceHolderVisible = isPlaceHolderVisible;
    $scope.markCompleted = markCompleted;
    $scope.onChangeSubject = onChangeSubject;
    $scope.clearAllFiltersToLoadSpecificCase = clearAllFiltersToLoadSpecificCase;
    $scope.addTimeline = addTimeline;
    $scope.caseGetParamsAsString = caseGetParamsAsString;
    $scope.createEmail = createEmail;
    $scope.createPDFLetter = createPDFLetter;
    $scope.focusToggle = focusToggle;
    $scope.formatDate = formatDate;
    $scope.getActivityType = getActivityType;
    $scope.gotoCase = gotoCase;
    $scope.isCurrentRelatedCaseVisible = isCurrentRelatedCaseVisible;
    $scope.isSameDate = isSameDate;
    $scope.refresh = refresh;
    $scope.selectTab = selectTab;
    $scope.viewActivityUrl = viewActivityUrl;
    $scope.pushCaseData = pushCaseData;

    this.getEditActivityUrl = getEditActivityUrl;
    this.getPrintActivityUrl = getPrintActivityUrl;

    (function init () {
      $scope.$watch('activeTab', activeTabWatcher);
      $scope.$watch('isFocused', isFocusedWatcher);
      $scope.$watch('item', itemWatcher);
      $scope.$on('civicase::activity-feed::show-activity-panel',
        showActivityPanelListener);
    }());

    /**
     * Broadcast an event to clear all filters and focus on a specific case.
     */
    function clearAllFiltersToLoadSpecificCase () {
      $rootScope.$broadcast('civicase::case-details::clear-filter-and-focus-specific-case', {
        caseId: $scope.viewingCaseId
      });
    }

    /**
     * Adds the sent timeline to the current case
     *
     * @param {string} name name of the timeline
     */
    function addTimeline (name) {
      $scope.refresh([['Case', 'addtimeline', {
        case_id: $scope.item.id,
        timeline: name
      }]]);
    }

    /**
     * @returns {string} case params as string
     */
    function caseGetParamsAsString () {
      return JSON.stringify(caseGetParams());
    }

    /**
     * Opens the popup for Creating Email
     */
    function createEmail () {
      var createEmailURLParams = {
        action: 'add',
        caseid: $scope.item.id,
        atype: '3',
        reset: 1,
        cid: $scope.item.client.map(function (client) {
          return client.contact_id;
        }).join(',')
      };

      CRM
        .loadForm(civicaseCrmUrl('civicrm/activity/email/add', createEmailURLParams))
        .on('crmFormSuccess', function () {
          $rootScope.$broadcast('civicase::activity::updated');
        });
    }

    /**
     * Opens the popup for Creating PDF letter
     */
    function createPDFLetter () {
      PrintMergeCaseAction.doAction([$scope.item])
        .then(function (pdfLetter) {
          CRM.loadForm(civicaseCrmUrl(pdfLetter.path, pdfLetter.query));
        });
    }

    /**
     * Toggle focus of the Summary View
     */
    function focusToggle () {
      $scope.isFocused = !$scope.isFocused;

      if (!$scope.isFocused && checkIfWindowWidthBreakpointIsReached()) {
        $rootScope.$broadcast('civicase::case-details::unfocused');
      }
    }

    /**
     * Formats Date in given format
     *
     * @param {string} date ISO string
     * @param {string} format Date format
     * @returns {string} the formatted date
     */
    function formatDate (date, format) {
      return moment(date).format(format);
    }

    /**
     * @param {string} name name of the activity type
     * @returns {object} Activity Type for the sent name
     */
    function getActivityType (name) {
      return _.findKey(activityTypes, { name: name });
    }

    /**
     * Go to the sent case
     *
     * @param {object} item case object
     * @param {object} $event event object
     */
    function gotoCase (item, $event) {
      if ($event && $($event.target).is('a, a *, input, button, button *')) {
        return;
      }
      var cf = {
        case_type_id: [CaseType.getById(item.case_type_id).name],
        status_id: [caseStatuses[item.status_id].name],
        'case_type_id.is_active': item['case_type_id.is_active']
      };
      var p = angular.extend({}, $route.current.params, { caseId: item.id, cf: JSON.stringify(cf) });
      $route.updateParams(p);
    }

    /**
     * Decide if the sent related case is visible with respect to the pager
     *
     * @param {number} index index
     * @returns {boolean} if current related case visible
     */
    function isCurrentRelatedCaseVisible (index) {
      $scope.relatedCasesPager.range.from = (($scope.relatedCasesPager.num - 1) * $scope.relatedCasesPager.size) + 1;
      $scope.relatedCasesPager.range.to = ($scope.relatedCasesPager.num * $scope.relatedCasesPager.size);

      if ($scope.relatedCasesPager.range.to > $scope.item.relatedCases.length) {
        $scope.relatedCasesPager.range.to = $scope.item.relatedCases.length;
      }

      return index >= ($scope.relatedCasesPager.range.from - 1) && index < $scope.relatedCasesPager.range.to;
    }

    /**
     * Checks if the sent dates are same.
     *
     * @param {Date} d1 date 1
     * @param {Date} d2 date 2
     * @returns {boolean} if the sent dates are same
     */
    function isSameDate (d1, d2) {
      return d1 && d2 && (d1.slice(0, 10) === d2.slice(0, 10));
    }

    /**
     * Marks sent activity as completed.
     *
     * @param {object} act activity object
     */
    function markCompleted (act) {
      $scope.refresh([['Activity', 'create', {
        id: act.id,
        status_id: act.is_completed ? 'Scheduled' : 'Completed'
      }]]);
    }

    /**
     * Create activity when changing case subject
     *
     * @param {string} newSubject new subject for the case
     */
    function onChangeSubject (newSubject) {
      CRM.api3('Activity', 'create', {
        case_id: $scope.item.id,
        activity_type_id: 'Change Case Subject',
        subject: newSubject,
        status_id: 'Completed'
      });
    }

    /**
     * Updates case data
     *
     * @param {object} data data to be pushed
     */
    function pushCaseData (data) {
      var isDataResponseForCurrentCase = $scope.item && data &&
        data.id === $scope.item.id;

      // If the user has already clicked through to another case by the time we get this data back, stop.
      if (!isDataResponseForCurrentCase) {
        return;
      }

      // Maintain the reference to the variable in the parent scope.
      delete ($scope.item.tag_id);
      _.assign($scope.item, formatCaseDetails(data));
      $scope.allowedCaseStatuses = getAllowedCaseStatuses($scope.item.definition);
      $scope.areRelatedCasesVisibleOnSummaryTab = !allowLinkedCasesTab &&
        $scope.item.relatedCases.length > 0;

      includeDetailsTab();
      $scope.$broadcast('updateCaseData');
      $scope.$emit('civicase::ActivitiesCalendar::reload');
    }

    /**
     * Refreshes the Case Details data
     *
     * @param {Array} apiCalls extra api calls to load on refresh.
     * @returns {Promise} promise
     */
    function refresh (apiCalls) {
      if (!_.isArray(apiCalls)) {
        apiCalls = [];
      }

      apiCalls.push(['Case', 'getdetails', caseGetParams()]);

      var promise = civicaseCrmApi(apiCalls)
        .then(function (result) {
          $scope.pushCaseData(result[apiCalls.length - 1].values[0]);
        });

      return crmStatus({
        start: 'Saving',
        success: 'Saved'
      }, promise);
    }

    /**
     * Selects the sent tab as active
     *
     * @param {object} tab tab name
     */
    function selectTab (tab) {
      $scope.activeTab = tab;
      if (typeof $scope.isFocused === 'boolean') {
        $scope.isFocused = true;
      }
    }

    /**
     * @param {string} id activity id
     * @returns {object} url to view the activity
     */
    function viewActivityUrl (id) {
      return civicaseCrmUrl('civicrm/case/activity', {
        action: 'update',
        reset: 1,
        cid: $scope.item.client[0].contact_id,
        caseid: $scope.item.id,
        id: id,
        civicase_reload: caseGetParamsAsString()
      });
    }

    /**
     * @param {string} id activity id
     * @returns {object} url to edit the activity
     */
    function getEditActivityUrl (id) {
      return civicaseCrmUrl('civicrm/case/activity', {
        action: 'update',
        reset: 1,
        caseid: $scope.item.id,
        id: id,
        civicase_reload: caseGetParamsAsString()
      });
    }

    /**
     * Get the url to print activities
     *
     * @param {Array} selectedActivities selected activities
     * @returns {string} url
     */
    function getPrintActivityUrl (selectedActivities) {
      selectedActivities = selectedActivities.map(function (item) {
        return item.id;
      }).join(',');

      return civicaseCrmUrl('civicrm/case/customreport/print', {
        all: 1,
        redact: 0,
        cid: $scope.item.client[0].contact_id,
        asn: 'standard_timeline',
        caseID: $scope.item.id,
        sact: selectedActivities
      });
    }

    /**
     * Listener for civicase::activity-feed::show-activity-panel
     */
    function showActivityPanelListener () {
      if (checkIfWindowWidthBreakpointIsReached()) {
        $scope.isFocused = true;
      }
    }

    /**
     * Get Case parameters
     *
     * @returns {string} url
     */
    function caseGetParams () {
      return getCaseQueryParams({
        caseId: $scope.item.id,
        panelLimit: panelLimit,
        caseTypeCategory: $scope.caseTypeCategory
      });
    }

    /**
     * Check if window width has reached set breakpoint
     *
     * @returns {boolean} if window width has reached set breakpoint
     */
    function checkIfWindowWidthBreakpointIsReached () {
      var WINDOW_WIDTH_BREAKPOINT = 1690;

      return $document.width() < WINDOW_WIDTH_BREAKPOINT;
    }

    /**
     *
     * @param {object} act activity
     * @returns {object} formatted activity
     */
    function formatAct (act) {
      return formatActivity(act, $scope.item.id);
    }

    /**
     * Formats the case detail object in required format
     *
     * @param {object} item case
     * @returns {object} case
     */
    function formatCaseDetails (item) {
      formatCase(item);
      item.definition = CaseType.getById(item.case_type_id).definition;

      prepareRelatedCases(item);

      // Scheduled Count
      item.status_count = { scheduled: {} };
      item.status_count.scheduled.count = item['api.Activity.getcount.scheduled'];
      item.status_count.scheduled.overdue = item['api.Activity.getcount.scheduled_overdue'];
      delete (item['api.Activity.getcount.scheduled']);
      delete (item['api.Activity.getcount.scheduled_overdue']);
      // Recent communications
      item.recentCommunication = _.each(_.cloneDeep(item['api.Activity.getAll.recentCommunication'].values), formatAct);
      delete (item['api.Activity.getAll.recentCommunication']);
      // Tasks
      item.tasks = _.each(_.cloneDeep(item['api.Activity.getAll.tasks'].values), formatAct);
      delete (item['api.Activity.getAll.tasks']);
      // nextActivitiesWhichIsNotMileStoneList
      item.nextActivityNotMilestone = _.each(_.cloneDeep(item['api.Activity.getAll.nextActivitiesWhichIsNotMileStone'].values), formatAct)[0];
      delete (item['api.Activity.getAll.nextActivitiesWhichIsNotMileStone']);

      // Custom fields
      var customData = item['api.CustomValue.getalltreevalues'].values || [];
      item.customData = _.groupBy(customData, 'style');
      delete (item['api.CustomValue.getalltreevalues']);

      return item;
    }

    /**
     * It includes the details tab when there are custom data that are assigned
     * to be displayed in a tab.
     */
    function includeDetailsTab () {
      var shouldAddDetailsTab = !_.isEmpty($scope.item.customData.Tab);
      var isDetailsTabIncluded = !!_.find($scope.tabs, { name: 'Details' });

      if (!shouldAddDetailsTab || isDetailsTabIncluded) {
        return;
      }

      $scope.tabs.splice(1, 0, {
        name: 'Details',
        label: ts('Details'),
        service: DetailsCaseTab
      });

      activeTabWatcher();
    }

    /**
     * Prepare Related Cases
     *
     * @param {object} caseObj case object
     */
    function prepareRelatedCases (caseObj) {
      caseObj.relatedCases = _.each(_.cloneDeep(caseObj['api.Case.getcaselist.relatedCasesByContact'].values), formatCase);
      // Add linked cases
      _.each(_.cloneDeep(caseObj['api.Case.getcaselist.linkedCases'].values), function (linkedCase) {
        var existing = _.find(caseObj.relatedCases, { id: linkedCase.id });
        if (existing) {
          existing.is_linked = true;
        } else {
          linkedCase.is_linked = true;
          caseObj.relatedCases.push(formatCase(linkedCase));
        }
      });

      caseObj.relatedCases.sort(function (x, y) {
        return !!y.is_linked - !!x.is_linked;
      });

      CasesUtils.fetchMoreContactsInformation(caseObj.relatedCases);
      $scope.relatedCasesPager.num = 1;

      delete (caseObj['api.Case.getcaselist.relatedCasesByContact']);
      delete (caseObj['api.Case.getcaselist.linkedCases']);
    }

    /**
     *
     * @param {object} definition definition
     * @returns {object} case statuses
     */
    function getAllowedCaseStatuses (definition) {
      var ret = _.cloneDeep(caseStatuses);
      ret = _.chain(ret)
        .sortBy(function (status) { return status.weight; })
        .indexBy('weight')
        .value();
      if (definition.statuses && definition.statuses.length) {
        _.each(_.cloneDeep(ret), function (status, id) {
          if (definition.statuses.indexOf(status.name) < 0) {
            delete (ret[id]);
          }
        });
      }
      return ret;
    }

    /**
     * Watches for activeTab variable and update the active tab
     * placeholder and content template.
     */
    function activeTabWatcher () {
      var activeCaseTab = _.find($scope.tabs, {
        name: $scope.activeTab
      });

      if (activeCaseTab && activeCaseTab.service) {
        $scope.activeTabContentUrl = activeCaseTab.service.activeTabContentUrl();
      }
    }

    /**
     * Watches for case changes. When the case is locked it redirects the user
     * to the case list. Also, If the case is loaded without its definition, it
     * will make a request to get the missing information.
     *
     * @returns {object|boolean} params
     */
    function itemWatcher () {
      var isCaseLocked = $scope.item && $scope.item.lock;

      if (isCaseLocked) {
        return redirectToCaseList();
      }

      // Fetch extra info about the case
      if ($scope.item && $scope.item.id && !$scope.item.definition) {
        $scope.areDetailsLoaded = false;
        civicaseCrmApi('Case', 'getdetails', caseGetParams()).then(function (info) {
          $scope.pushCaseData(info.values[0]);
          $scope.areDetailsLoaded = true;
        });
      }
    }

    /**
     * Watcher for isFocused variable
     */
    function isFocusedWatcher () {
      $timeout(function () {
        var $actHeader = $('.act-feed-panel .panel-header');
        var $actControls = $('.act-feed-panel .act-list-controls');

        if ($actHeader.hasClass('affix')) {
          $actHeader.css('width', $('.act-feed-panel').css('width'));
        } else {
          $actHeader.css('width', 'auto');
        }

        if ($actControls.hasClass('affix')) {
          $actControls.css('width', $actHeader.css('width'));
        } else {
          $actControls.css('width', 'auto');
        }
      }, 1500);
    }

    /**
     * Changes the current route and goes to the list of cases.
     *
     * @returns {object} params
     */
    function redirectToCaseList () {
      return $route.updateParams({ caseId: null });
    }

    /**
     * @returns {boolean} if place holder should be visible
     */
    function isPlaceHolderVisible () {
      return !$scope.areDetailsLoaded;
    }

    /**
     * @returns {boolean} if main content should be visible
     */
    function isMainContentVisible () {
      return $scope.item && $scope.areDetailsLoaded;
    }
  }
})(angular, CRM.$, CRM._);
