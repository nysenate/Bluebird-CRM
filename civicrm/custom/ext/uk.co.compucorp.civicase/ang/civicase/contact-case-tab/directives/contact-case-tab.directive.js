(function (angular, $, _) {
  var module = angular.module('civicase');

  module.directive('civicaseContactCaseTab', function () {
    return {
      restrict: 'EA',
      controller: 'CivicaseContactCaseTabController',
      templateUrl: '~/civicase/contact-case-tab/directives/contact-case-tab.directive.html',
      scope: {
        caseTypeCategory: '='
      }
    };
  });

  module.controller('CivicaseContactCaseTabController', CivicaseContactCaseTabController);

  /**
   * @param {object} $scope the controller scope
   * @param {Function} ts translation service
   * @param {Function} CaseTypeCategory the case type category service
   * @param {Function} CaseTypeCategoryTranslationService the case type category translation service
   * @param {Function} crmApi the crm api service
   * @param {Function} formatCase the format case service
   * @param {object} Contact the contact service
   * @param {object} ContactsCache the contacts cache service
   * @param {string} AddCase service to open add case popup
   */
  function CivicaseContactCaseTabController ($scope, ts, CaseTypeCategory,
    CaseTypeCategoryTranslationService, crmApi, formatCase, Contact,
    ContactsCache, AddCase) {
    var commonConfigs = {
      isLoaded: false,
      showSpinner: false,
      isLoadMoreAvailable: false,
      page: {
        size: 3,
        num: 1
      }
    };

    $scope.caseDetailsLoaded = false;
    $scope.caseTypeCategoryName = CaseTypeCategory.getAll()[$scope.caseTypeCategory].name;
    $scope.contactId = Contact.getCurrentContactID();
    $scope.casesListConfig = [
      {
        name: 'opened',
        title: ts('Open Cases'),
        filterParams: {
          'status_id.grouping': 'Opened',
          'case_type_id.case_type_category': $scope.caseTypeCategory,
          contact_id: $scope.contactId,
          is_deleted: 0
        },
        showContactRole: false
      }, {
        name: 'closed',
        title: ts('Resolved cases'),
        filterParams: {
          'status_id.grouping': 'Closed',
          'case_type_id.case_type_category': $scope.caseTypeCategory,
          contact_id: $scope.contactId,
          is_deleted: 0
        },
        showContactRole: false
      }, {
        name: 'related',
        title: ts('Other cases for this contact'),
        filterParams: {
          'case_type_id.case_type_category': $scope.caseTypeCategory,
          exclude_for_client_id: $scope.contactId,
          contact_involved: $scope.contactId,
          is_deleted: 0
        },
        showContactRole: true
      }
    ];

    $scope.addCase = AddCase.clickHandler;
    $scope.isAddCaseVisible = AddCase.isVisible;
    $scope.checkPerm = CRM.checkPerm;
    $scope.handleContactTabChange = handleContactTabChange;
    $scope.ts = ts;

    (function init () {
      initCasesConfig();
      initSubscribers();
      getCases();
      CaseTypeCategoryTranslationService
        .storeTranslation($scope.caseTypeCategory);
    }());

    /**
     * refresh function to set refresh cases
     */
    $scope.refresh = function () {
      initCasesConfig();
      getCases();
    };

    /**
     * Watcher for civicase::contact-record-list::loadmore event
     *
     * @param {object} event scope watch event reference
     * @param {string} name of the list
     */
    function contactRecordListLoadmoreWatcher (event, name) {
      var caseListIndex = _.findIndex($scope.casesListConfig, function (caseObj) {
        return caseObj.name === name;
      });
      var params = getCaseApiParams($scope.casesListConfig[caseListIndex].filterParams, $scope.casesListConfig[caseListIndex].page);

      $scope.casesListConfig[caseListIndex].showSpinner = true;
      updateCase(caseListIndex, params);
    }

    /**
     * Watcher for civicase::contact-record-list::view-case event
     *
     * @param {object} event scope watch event reference
     * @param {object} caseObj the data belonging to a case
     */
    function contactRecordListViewCaseWatcher (event, caseObj) {
      setCaseAsSelected(caseObj);
    }

    /**
     * Fetch additional information about the contacts
     *
     * @param {object[]} cases a list of cases
     */
    function fetchContactsData (cases) {
      var contacts = [];

      _.each(cases, function (caseObj) {
        contacts = contacts.concat(getAllContactIdsForCase(caseObj));
      });

      ContactsCache.add(contacts);
    }

    /**
     * Returns all the contact ids for the given case
     *
     * @param {object} caseObj the data belonging to a case
     * @returns {number[]} a list of contact ids
     */
    function getAllContactIdsForCase (caseObj) {
      var contacts = [];

      _.each(caseObj.contacts, function (currentCase) {
        contacts.push(currentCase.contact_id);
      });

      _.each(caseObj.activity_summary.next, function (activity) {
        contacts = contacts.concat(activity.assignee_contact_id);
        contacts = contacts.concat(activity.target_contact_id);
        contacts.push(activity.source_contact_id);
      });

      return contacts;
    }

    /**
     * Fetch cases for each type of list and count total number of cases
     */
    function getCases () {
      var totalCountApi = [];

      _.each($scope.casesListConfig, function (item, ind) {
        var params = getCaseApiParams(item.filterParams, item.page);

        updateCase(ind, params);
        totalCountApi.push(params.count);
      });

      getTotalCasesCount(totalCountApi);
    }

    /**
     * Get parameters to load cases
     *
     * @param {object} filter the filters to use when loading the cases
     * @param {object} page the current page and the page size
     * @returns {object} the parameters needed to load cases
     */
    function getCaseApiParams (filter, page) {
      var caseReturnParams = [
        'subject', 'details', 'contact_id', 'case_type_id', 'status_id',
        'contacts', 'start_date', 'end_date', 'is_deleted', 'activity_summary',
        'activity_count', 'category_count', 'tag_id.name', 'tag_id.color',
        'tag_id.description', 'tag_id.parent_id', 'related_case_ids',
        'case_type_id.case_type_category'
      ];
      var returnCaseParams = {
        sequential: 1,
        return: caseReturnParams,
        options: {
          sort: 'modified_date DESC',
          limit: page.size,
          offset: page.size * (page.num - 1)
        }
      };
      var params = { 'case_type_id.is_active': 1 };

      return {
        cases: ['Case', 'getcaselist', $.extend(true, returnCaseParams, filter, params)],
        count: ['Case', 'getdetailscount', $.extend(true, returnCaseParams, filter, params)]
      };
    }

    /**
     * Returns contact role for the currently viewing contact
     *
     * @param {object} caseObj the data belonging to a case
     * @returns {string} role
     */
    function getContactRole (caseObj) {
      var contact = _.find(caseObj.contacts, {
        contact_id: $scope.contactId
      });

      return contact ? contact.role : 'No Role Associated';
    }

    /**
     * Fetches count of all the cases a contact have
     *
     * @param {Array|object} apiCall the api call parameters to use for counting cases
     */
    function getTotalCasesCount (apiCall) {
      var count = 0;

      crmApi(apiCall).then(function (response) {
        _.each(response, function (ind) {
          count += ind;
        });

        $scope.totalCount = count;
      });
    }

    /**
     * Triggers after the contact tab changes. When changing back to the tab belonging to this case
     * type category, it restores the translations associated with it. This is done due to CiviCRM
     * replacing the translation files when a new angular module is loaded. The solution is to manually
     * restore the translations.
     *
     * @param {object} urlParams a map of the URL parameters belonging to the currently active tab.
     */
    function handleContactTabChange (urlParams) {
      var caseTypeCategoryId = parseInt(urlParams.case_type_category, 10);
      var isChangingToCurrentCaseCategoryTab = caseTypeCategoryId === $scope.caseTypeCategory;

      if (!isChangingToCurrentCaseCategoryTab) {
        return;
      }

      CaseTypeCategoryTranslationService.restoreTranslation(caseTypeCategoryId);
    }

    /**
     * Extends casesListConfig
     */
    function initCasesConfig () {
      _.each($scope.casesListConfig, function (item, ind) {
        $scope.casesListConfig[ind].cases = [];
        $scope.casesListConfig[ind] = $.extend(true, $scope.casesListConfig[ind], commonConfigs);
      });
    }

    /**
     * Subscribers for events
     */
    function initSubscribers () {
      $scope.$on('civicase::contact-record-list::load-more', contactRecordListLoadmoreWatcher);
      $scope.$on('civicase::contact-record-list::view-case', contactRecordListViewCaseWatcher);
    }

    /**
     * Loads additional data for contacts and set the first case as selected
     */
    function loadAdditionalDataWhenAllCasesLoaded () {
      if (isAllCasesLoaded()) {
        var allCases = _.reduce($scope.casesListConfig, function (memoriser, caseObj) {
          return memoriser.concat(caseObj.cases);
        }, []);

        fetchContactsData(allCases);

        if (!$scope.selectedCase) {
          setCaseAsSelected(allCases[0]);
          $scope.caseDetailsLoaded = true;
        }
      }
    }

    /**
     * Sets passed case object as selected case
     *
     * @param {object} caseObj the data belonging to a case
     */
    function setCaseAsSelected (caseObj) {
      $scope.selectedCase = caseObj;
    }

    /**
     * Watcher function for cases collections
     *
     * @returns {boolean} true when all cases list have been loaded
     */
    function isAllCasesLoaded () {
      return _.reduce($scope.casesListConfig, function (memoriser, data) {
        return memoriser && data.isLoaded;
      }, true);
    }

    /**
     * Updates the list with new entries
     *
     * @param {string} caseListIndex the case list config index to update
     * @param {Array} params the parameters to use when updating the case list
     */
    function updateCase (caseListIndex, params) {
      crmApi(params).then(function (response) {
        _.each(response.cases.values, function (item) {
          item.contact_role = getContactRole(item);
          $scope.casesListConfig[caseListIndex].cases.push(formatCase(item));
        });

        $scope.casesListConfig[caseListIndex].isLoaded = true;
        $scope.casesListConfig[caseListIndex].showSpinner = false;
        $scope.casesListConfig[caseListIndex].isLoadMoreAvailable = $scope.casesListConfig[caseListIndex].cases.length < response.count;

        if ($scope.casesListConfig[caseListIndex].page.num === 1) {
          loadAdditionalDataWhenAllCasesLoaded();
        }

        $scope.casesListConfig[caseListIndex].page.num += 1;
      });
    }
  }
})(angular, CRM.$, CRM._);
