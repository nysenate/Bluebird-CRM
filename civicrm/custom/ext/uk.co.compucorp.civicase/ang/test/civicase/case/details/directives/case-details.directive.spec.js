/* eslint-env jasmine */
(function ($, _) {
  describe('civicaseCaseDetails', function () {
    var $httpBackend, element, controller, activitiesMockData, $controller, $compile,
      $document, $rootScope, $scope, $provide, civicaseCrmApi, civicaseCrmApiMock, $q,
      formatCase, CasesData, CasesUtils, $route, civicaseCrmUrl;

    beforeEach(module('civicase.templates', 'civicase', 'civicase.data', function (_$provide_) {
      $provide = _$provide_;

      killDirective('civicaseActivitiesCalendar');
      killDirective('civicaseCaseActions');
      killDirective('civicaseCaseDetailsPeopleTab');
      killDirective('civicaseContactCard');
    }));

    beforeEach(inject(function ($q) {
      var formatCaseMock = jasmine.createSpy('formatCase');
      civicaseCrmApiMock = jasmine.createSpy('civicaseCrmApi').and.returnValue($q.resolve());
      $route = { current: { params: {} } };

      formatCaseMock.and.callFake(function (data) {
        return data;
      });

      $provide.value('$route', $route);
      $provide.value('civicaseCrmApi', civicaseCrmApiMock);
      $provide.value('formatCase', formatCaseMock);
    }));

    beforeEach(inject(function (_$compile_, _$controller_, _$httpBackend_,
      _$rootScope_, _$document_, _activitiesMockData_, _CasesData_, _civicaseCrmApi_,
      _$q_, _formatCase_, _CasesUtils_, _civicaseCrmUrl_) {
      $compile = _$compile_;
      civicaseCrmUrl = _civicaseCrmUrl_;
      $document = _$document_;
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      $httpBackend = _$httpBackend_;
      activitiesMockData = _activitiesMockData_;
      CasesData = _CasesData_;
      CasesUtils = _CasesUtils_;
      $scope = $rootScope.$new();
      $q = _$q_;
      civicaseCrmApi = _civicaseCrmApi_;
      formatCase = _formatCase_;

      civicaseCrmApi.and.returnValue($q.resolve(CasesData.get()));
      spyOn(CasesUtils, 'fetchMoreContactsInformation');
    }));

    describe('basic tests', function () {
      beforeEach(function () {
        compileDirective();
      });

      it('complies the directive', function () {
        expect(element.html()).toContain('civicase__case-header');
      });
    });

    describe('activeTab watcher', function () {
      describe('when switching to an existing tab', () => {
        beforeEach(function () {
          compileDirective();
          element.isolateScope().activeTab = 'People';
          element.isolateScope().$digest();
        });

        it('should return active tab content url', function () {
          expect(element.isolateScope().activeTabContentUrl).toEqual('~/civicase/case/details/directives/tab-content/people.html');
        });
      });

      describe('when switching to a custom active tab', () => {
        beforeEach(function () {
          $httpBackend.when('GET', '~/civicase/custom-tab.html')
            .respond(200, '');
          compileDirective();
          element.isolateScope().tabs.push({
            name: 'CustomTab',
            label: ts('Custom Tab'),
            service: {
              activeTabContentUrl: () => '~/civicase/custom-tab.html'
            }
          });
          element.isolateScope().activeTab = 'CustomTab';
          element.isolateScope().$digest();
        });

        it('should return active tab content url', function () {
          expect(element.isolateScope().activeTabContentUrl).toEqual('~/civicase/custom-tab.html');
        });
      });
    });

    describe('focusToggle()', function () {
      describe('basic test', function () {
        beforeEach(function () {
          compileDirective();
          element.isolateScope().isFocused = true;
          element.isolateScope().focusToggle();
        });

        it('toggles the focus state', function () {
          expect(element.isolateScope().isFocused).toBe(false);
        });
      });

      describe('when case is unfocused and screen width is less than 1690px', function () {
        beforeEach(function () {
          spyOn($rootScope, '$broadcast').and.callThrough();
          spyOn($document, 'width').and.returnValue(1600);
          compileDirective();
          element.isolateScope().isFocused = true;
          element.isolateScope().focusToggle();
        });

        it('fires the case details unfocused event', function () {
          expect($rootScope.$broadcast)
            .toHaveBeenCalledWith('civicase::case-details::unfocused');
        });
      });

      describe('when case is unfocused and screen width is more than 1690px', function () {
        beforeEach(function () {
          spyOn($rootScope, '$broadcast');
          spyOn($document, 'width').and.returnValue(1700);
          compileDirective();
          element.isolateScope().isFocused = true;
          element.isolateScope().focusToggle();
        });

        it('does not fire the case details unfocused event', function () {
          expect($rootScope.$broadcast).not.toHaveBeenCalled();
        });
      });
    });

    describe('visibility of content and placeholders', () => {
      beforeEach(function () {
        compileDirective();
      });

      describe('when case is being loaded', () => {
        beforeEach(() => {
          element.isolateScope().item = null;
          element.isolateScope().areDetailsLoaded = false;
        });

        it('hides the main content', () => {
          expect(element.isolateScope().isMainContentVisible()).toBeFalsy();
        });

        it('shows the main content', () => {
          expect(element.isolateScope().isPlaceHolderVisible()).toBeTruthy();
        });
      });

      describe('when case is loaded', () => {
        beforeEach(() => {
          element.isolateScope().item = CasesData.get().values[0];
          element.isolateScope().areDetailsLoaded = true;
        });

        it('hides the main content', () => {
          expect(element.isolateScope().isMainContentVisible()).toBeTruthy();
        });

        it('shows the main content', () => {
          expect(element.isolateScope().isPlaceHolderVisible()).toBeFalsy();
        });
      });
    });

    describe('formatDate()', function () {
      var returnValue;

      beforeEach(function () {
        compileDirective();
        returnValue = element.isolateScope().formatDate('2018-09-14 18:29:45', 'DD MMMM YYYY');
      });

      it('returns the date in the sent format', function () {
        expect(returnValue).toBe('14 September 2018');
      });
    });

    describe('pushCaseData()', function () {
      beforeEach(function () {
        compileDirective();
        element.isolateScope().item = CasesData.get().values[0];
        element.isolateScope().pushCaseData(CasesData.get().values[0]);
      });

      it('calculates the scheduled activities', function () {
        expect(element.isolateScope().item.status_count)
          .toEqual({ scheduled: { count: 2, overdue: 3 } });
      });

      it('calculates the incomplete tasks activities', function () {
        expect(element.isolateScope().item.category_count.incomplete.task).toBe(2);
      });

      it('checks whether the user has permission to fetch case details', function () {
        expect(civicaseCrmApi).toHaveBeenCalledWith('Case', 'getdetails', jasmine.objectContaining({
          'case_type_id.case_type_category': 'cases'
        }));
      });

      describe('Related Cases', function () {
        describe('related cases', function () {
          var relatedCasesByContact, linkedCases;

          beforeEach(function () {
            relatedCasesByContact = CasesData.get().values[0]['api.Case.getcaselist.relatedCasesByContact'].values;
            linkedCases = CasesData.get().values[0]['api.Case.getcaselist.linkedCases'].values;
          });

          it('related cases are displayed', function () {
            expect(element.isolateScope().item.relatedCases.length).toBe(relatedCasesByContact.concat(linkedCases).length);
          });
        });

        describe('linked cases cases', function () {
          var relatedCasesCopy, sortedList;

          beforeEach(function () {
            relatedCasesCopy = angular.copy(element.isolateScope().item.relatedCases);
            sortedList = relatedCasesCopy.sort(function (x, y) {
              return !!y.is_linked - !!x.is_linked;
            });
          });

          it('linked cases are displayed first', function () {
            expect(sortedList).toEqual(element.isolateScope().item.relatedCases);
          });
        });

        it('shows the first page of the pager', function () {
          expect(element.isolateScope().relatedCasesPager.num).toBe(1);
        });
      });
      /* TODO - Rest of function needs to be unit tested */
    });

    describe('isCurrentRelatedCaseVisible()', function () {
      var returnValue;

      beforeEach(function () {
        compileDirective();
        element.isolateScope().item = {};
        element.isolateScope().item.relatedCases = CasesData.get().values[0];
        element.isolateScope().relatedCasesPager.num = 2;
        element.isolateScope().relatedCasesPager.size = 5;
      });

      describe('when the index is between current range', function () {
        beforeEach(function () {
          returnValue = element.isolateScope().isCurrentRelatedCaseVisible(7);
        });

        it('shows the related case', function () {
          expect(returnValue).toBe(true);
        });
      });

      describe('when the index is more that the current range', function () {
        beforeEach(function () {
          returnValue = element.isolateScope().isCurrentRelatedCaseVisible(11);
        });

        it('hides the related case', function () {
          expect(returnValue).toBe(false);
        });
      });

      describe('when the index is less that the current range', function () {
        beforeEach(function () {
          returnValue = element.isolateScope().isCurrentRelatedCaseVisible(4);
        });

        it('hides the related case', function () {
          expect(returnValue).toBe(false);
        });
      });
    });

    describe('when printing selected activities', function () {
      var selectedActivities;

      beforeEach(function () {
        initController();

        controller.getPrintActivityUrl(activitiesMockData.get());
        selectedActivities = activitiesMockData.get().map(function (item) {
          return item.id;
        }).join(',');
      });

      it('retuns the url to print the activities', function () {
        expect(civicaseCrmUrl).toHaveBeenCalledWith('civicrm/case/customreport/print', {
          all: 1,
          redact: 0,
          cid: $scope.item.client[0].contact_id,
          asn: 'standard_timeline',
          caseID: $scope.item.id,
          sact: selectedActivities
        });
      });
    });

    describe('when activity details panel is opened', function () {
      describe('when width of the screen is more than 1690 px', function () {
        beforeEach(function () {
          spyOn($document, 'width').and.returnValue(1700);
          compileDirective();
          $rootScope.$broadcast('civicase::activity-feed::show-activity-panel');
        });

        it('does not hide the case list', function () {
          expect(element.isolateScope().isFocused).not.toBe(true);
        });
      });

      describe('when width of the screen is less than 1690 px', function () {
        beforeEach(function () {
          spyOn($document, 'width').and.returnValue(1650);
          compileDirective();
          $rootScope.$broadcast('civicase::activity-feed::show-activity-panel');
        });

        it('hides the case list', function () {
          expect(element.isolateScope().isFocused).toBe(true);
        });
      });
    });

    describe('when clear all filters button is pressed', function () {
      beforeEach(function () {
        spyOn($rootScope, '$broadcast');
        compileDirective();

        element.isolateScope().clearAllFiltersToLoadSpecificCase();
      });

      it('clears all filters and focuses on current case', function () {
        expect($rootScope.$broadcast).toHaveBeenCalledWith('civicase::case-details::clear-filter-and-focus-specific-case', {
          caseId: '141'
        });
      });
    });

    /**
     * Compiles the civicase-case-details directive.
     */
    function compileDirective () {
      var caseObj = CasesData.get().values[0];

      $scope.viewingCaseId = caseObj.id;
      $scope.viewingCaseDetails = formatCase(caseObj);
      $scope.caseTypeCategory = 'cases';
      element = $compile(`
        <div
          civicase-case-details="viewingCaseDetails"
          viewing-case-id="viewingCaseId"
          case-type-category="caseTypeCategory">
        </div>`
      )($scope);
      $scope.$digest();
    }

    /**
     * Initializes the case details controller.
     *
     * @param {object} caseItem a case item to pass to the controller. Defaults to
     * a case from the mock data.
     */
    function initController (caseItem) {
      $scope = $rootScope.$new();

      controller = $controller('civicaseCaseDetailsController', {
        $scope: $scope
      });
      $scope.item = caseItem || _.cloneDeep(CasesData.get().values[0]);
      $scope.$digest();
    }

    /**
     * Mocks a directive
     * TODO: Have a more generic usage - Maybe create a service/factory
     *
     * @param {string} directiveName name of the directive
     */
    function killDirective (directiveName) {
      angular.mock.module(function ($compileProvider) {
        $compileProvider.directive(directiveName, function () {
          return {
            priority: 9999999,
            terminal: true
          };
        });
      });
    }
  });

  describe('civicaseCaseDetailsController', function () {
    let $controller, $provide, $rootScope, $route, $scope, apiResponses,
      CasesData, civicaseCrmApiMock, controller, DetailsCaseTab,
      loadFormBefore, civicaseCrmUrl;

    beforeEach(module('civicase', 'civicase.data', function (_$provide_) {
      $provide = _$provide_;
      civicaseCrmApiMock = jasmine.createSpy('civicaseCrmApi');

      $provide.value('civicaseCrmApi', civicaseCrmApiMock);
      $provide.value('crmApi', civicaseCrmApiMock);
    }));

    beforeEach(inject(function (_$controller_, $q, _$rootScope_, _$route_,
      _CasesData_, _DetailsCaseTab_, _civicaseCrmUrl_) {
      civicaseCrmUrl = _civicaseCrmUrl_;
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      $route = _$route_;
      CasesData = _CasesData_;
      DetailsCaseTab = _DetailsCaseTab_;
      apiResponses = {
        'Contact.get': { values: [] }
      };
      civicaseCrmApiMock.and
        .callFake((entity, action, params) => {
          const entityActionName = `${entity}.${action}`;

          if (apiResponses[entityActionName]) {
            return $q.resolve(apiResponses[entityActionName]);
          } else {
            return $q.resolve({ values: CasesData.get() });
          }
        });
    }));

    describe('linked cases', () => {
      let caseItem;

      beforeEach(() => {
        caseItem = _.cloneDeep(CasesData.get().values[0]);
      });

      describe('when the linked cases are displayed on a tab', () => {
        beforeEach(() => {
          initController(caseItem, {
            allowLinkedCasesTab: true
          });

          $scope.pushCaseData(caseItem);
        });

        it('hides the linked cases panel from the case summary tab', () => {
          expect($scope.areRelatedCasesVisibleOnSummaryTab).toBe(false);
        });
      });

      describe('when the linked cases are not displayed on a tab', () => {
        beforeEach(() => {
          initController(caseItem, {
            allowLinkedCasesTab: false
          });

          $scope.pushCaseData(caseItem);
        });

        it('displays the linked cases panel on the case summary tab', () => {
          expect($scope.areRelatedCasesVisibleOnSummaryTab).toBe(true);
        });
      });
    });

    describe('viewing the case', function () {
      describe('on init', () => {
        beforeEach(() => {
          initController();
        });

        it('binds the scope to the controller function', () => {
          expect(controller.$scope).toBe($scope);
        });
      });

      describe('when requesting to view a case that is missing its details', function () {
        beforeEach(function () {
          initController();
        });

        it('requests the missing case details', function () {
          expect(civicaseCrmApiMock).toHaveBeenCalledWith(
            'Case', 'getdetails', jasmine.any(Object)
          );
        });
      });

      describe('when the case is locked for the current user', function () {
        beforeEach(function () {
          var caseItem = _.cloneDeep(CasesData.get().values[0]);
          caseItem.lock = 1;

          spyOn($route, 'updateParams');
          initController(caseItem);
        });

        it('redirects the user to the case list', function () {
          expect($route.updateParams).toHaveBeenCalledWith({ caseId: null });
        });
      });

      describe('Custom Data', () => {
        let caseItem, customDataBlocks;

        beforeEach(() => {
          customDataBlocks = [];
          caseItem = _.cloneDeep(CasesData.get().values[0]);
          apiResponses['Case.getdetails'] = {
            values: [caseItem]
          };
        });

        describe('when there are inline custom data blocks', () => {
          beforeEach(() => {
            customDataBlocks = [
              generateCustomDataBlock({ style: 'Inline' }),
              generateCustomDataBlock({ style: 'Inline' }),
              generateCustomDataBlock({ style: 'Inline' })
            ];
            caseItem['api.CustomValue.getalltreevalues'] = {
              values: customDataBlocks
            };
            initController(caseItem);
          });

          it('stores the custom data blocks in a container for inline blocks', () => {
            expect($scope.item.customData.Inline).toEqual(customDataBlocks);
          });
        });

        describe('when there are tab custom data blocks', () => {
          beforeEach(() => {
            customDataBlocks = [
              generateCustomDataBlock({ style: 'Inline' }),
              generateCustomDataBlock({ style: 'Tab' }),
              generateCustomDataBlock({ style: 'Inline' })
            ];
            caseItem['api.CustomValue.getalltreevalues'] = {
              values: customDataBlocks
            };
            initController(caseItem);
          });

          it('stores the custom data blocks in a container for tab blocks', () => {
            expect($scope.item.customData.Tab).toEqual([customDataBlocks[1]]);
          });

          it('adds the Details tab to display custom tab blocks', () => {
            expect($scope.tabs).toContain({
              name: 'Details',
              label: ts('Details'),
              service: DetailsCaseTab
            });
          });
        });

        describe('when there are no tab custom data blocks', () => {
          beforeEach(() => {
            customDataBlocks = [
              generateCustomDataBlock({ style: 'Inline' }),
              generateCustomDataBlock({ style: 'Inline' }),
              generateCustomDataBlock({ style: 'Inline' })
            ];
            caseItem['api.CustomValue.getalltreevalues'] = {
              values: customDataBlocks
            };
            initController(caseItem);
          });

          it('does not add the details tab', () => {
            expect($scope.tabs).not.toContain({
              name: 'Details',
              label: ts('Details'),
              service: DetailsCaseTab
            });
          });
        });

        describe('when there are data custom blocks and the case details are updated', () => {
          let detailsTabsCount;

          beforeEach(() => {
            customDataBlocks = [
              generateCustomDataBlock({ style: 'Inline' }),
              generateCustomDataBlock({ style: 'Tab' }),
              generateCustomDataBlock({ style: 'Inline' })
            ];
            caseItem['api.CustomValue.getalltreevalues'] = {
              values: customDataBlocks
            };
            initController(caseItem);

            // refresh case details:
            caseItem['api.Case.getcaselist.relatedCasesByContact'] = { values: [] };
            caseItem['api.Case.getcaselist.linkedCases'] = { values: [] };
            caseItem['api.Activity.getAll.recentCommunication'] = { values: [] };
            caseItem['api.Activity.getAll.tasks'] = { values: [] };
            caseItem['api.Activity.getAll.nextActivitiesWhichIsNotMileStone'] = { values: [] };
            caseItem['api.CustomValue.getalltreevalues'] = {
              values: customDataBlocks
            };
            $scope.pushCaseData(caseItem);

            detailsTabsCount = _.where($scope.tabs, { name: 'Details' }).length;
          });

          it('does not add the case details tab multiple times', () => {
            expect(detailsTabsCount).toBe(1);
          });
        });

        /**
         * @param {object} defaultValues default values to use when generating the
         *   custom data block.
         * @returns {object} a mock custom data block.
         */
        function generateCustomDataBlock (defaultValues) {
          const uniqueId = _.uniqueId();

          return _.extend({}, {
            id: uniqueId,
            name: `Custom_Data_Block_${uniqueId}`,
            title: `Custom Data Block ${uniqueId}`,
            style: 'Inline'
          }, defaultValues);
        }
      });
    });

    describe('when creating an email', function () {
      var loadFormArguments;

      beforeEach(function () {
        initController();
        spyOn($rootScope, '$broadcast');
        loadFormBefore = CRM.loadForm;
        CRM.loadForm = jasmine.createSpy();
        CRM.loadForm.and.returnValue({
          on: function () {
            loadFormArguments = arguments;
          }
        });

        $scope.createEmail();
      });

      afterEach(function () {
        CRM.loadForm = loadFormBefore;
      });

      it('open a popup to create emails', function () {
        expect(civicaseCrmUrl).toHaveBeenCalledWith('civicrm/activity/email/add', {
          action: 'add',
          caseid: $scope.item.id,
          atype: '3',
          reset: 1,
          cid: '170'
        });
      });

      it("creates a listener for the popup's close event", function () {
        expect(loadFormArguments[0]).toBe('crmFormSuccess');
      });

      describe('when popup closes', function () {
        beforeEach(function () {
          loadFormArguments[1]();
        });

        it('refreshes the activity feed', function () {
          expect($rootScope.$broadcast).toHaveBeenCalledWith('civicase::activity::updated');
        });
      });
    });

    describe('going to other cases', () => {
      let caseItem, clickEvent;

      beforeEach(() => {
        spyOn($route, 'updateParams');
        initController();

        clickEvent = $.Event('click');
        clickEvent.target = document.createElement('span');
        caseItem = {
          id: _.uniqueId(),
          case_type_id: '1',
          status_id: '1',
          'case_type_id.is_active': '0'
        };
      });

      describe('when clicking on a non button element', () => {
        beforeEach(() => {
          $route.current = {
            params: {
              customParam: 'custom-value'
            }
          };

          $scope.gotoCase(caseItem, clickEvent);
        });

        it('goes to the other case', () => {
          expect($route.updateParams).toHaveBeenCalledWith(jasmine.objectContaining({
            caseId: caseItem.id
          }));
        });

        it('retains existing route filters', () => {
          expect($route.updateParams).toHaveBeenCalledWith(jasmine.objectContaining({
            customParam: 'custom-value'
          }));
        });

        it('adds filters for the case\'s case type, status, and active status', () => {
          expect($route.updateParams).toHaveBeenCalledWith(jasmine.objectContaining({
            cf: JSON.stringify({
              case_type_id: ['housing_support'],
              status_id: ['Open'],
              'case_type_id.is_active': '0'
            })
          }));
        });
      });

      describe('when a button element is clicked', () => {
        beforeEach(() => {
          clickEvent.target = document.createElement('a');

          $scope.gotoCase(caseItem, clickEvent);
        });

        it('does not go to the other case', () => {
          expect($route.updateParams).not.toHaveBeenCalled();
        });
      });
    });

    /**
     * Initializes the case details controller.
     *
     * @param {object} caseItem a case item to pass to the controller. Defaults to
     * a case from the mock data.
     * @param {object} dependencies a list of mock dependencies to pass to the controller.
     */
    function initController (caseItem, dependencies) {
      $scope = $rootScope.$new();

      controller = $controller('civicaseCaseDetailsController', _.extend({}, {
        $scope: $scope
      }, dependencies));
      $scope.item = caseItem || _.cloneDeep(CasesData.get().values[0]);
      $scope.$digest();
    }
  });
})(CRM.$, CRM._);
