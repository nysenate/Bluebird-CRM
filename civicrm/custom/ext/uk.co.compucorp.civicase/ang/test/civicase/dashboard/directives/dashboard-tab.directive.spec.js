(function ($, _, moment) {
  describe('dashboardTabController', function () {
    var $controller, $rootScope, $scope, civicaseCrmApi, formatActivity, formatCase,
      mockedCases, ActivityStatusType, CaseTypesData;

    /**
     * Generate Mocked Cases
     */
    function generateMockedCases () {
      mockedCases = _.times(5, function () {
        return { id: _.uniqueId() };
      });
    }

    beforeEach(module('civicase.templates', 'civicase.data', 'civicase', 'crmUtil', function ($provide) {
      const formatCase = jasmine.createSpy('formatCase')
        .and.callFake(function (caseObj) {
          return caseObj;
        });
      $provide.value('civicaseCrmApi', jasmine.createSpy('civicaseCrmApi'));
      $provide.value('formatCase', formatCase);
    }));

    beforeEach(inject(function (_$controller_, _$rootScope_, _civicaseCrmApi_,
      _formatActivity_, _formatCase_, _ActivityStatusType_, _CaseTypesMockData_) {
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      civicaseCrmApi = _civicaseCrmApi_;
      formatActivity = _formatActivity_;
      formatCase = _formatCase_;
      ActivityStatusType = _ActivityStatusType_;
      $scope = $rootScope.$new();
      CaseTypesData = _CaseTypesMockData_.get();

      $scope.filters = { caseRelationshipType: 'all' };
      $scope.activityFilters = {
        case_filter: { foo: 'foo' }
      };
    }));

    beforeEach(inject(function ($q) {
      generateMockedCases();
      civicaseCrmApi.and.returnValue($q.resolve({
        values: mockedCases
      }));
    }));

    describe('case parameters', function () {
      beforeEach(function () {
        initController();
      });

      it('fetches the activities from the cases that match the relationship filter', function () {
        expect($scope.calendarCaseParams).toEqual(_.assign({
          'status_id.grouping': 'Opened'
        }, $scope.activityFilters.case_filter));
      });

      describe('when the relationship type changes', function () {
        var newFilterValue;

        beforeEach(function () {
          newFilterValue = 'bar';

          $scope.filters.caseRelationshipType = 'is_involved';
          $scope.activityFilters.case_filter.foo = newFilterValue;

          civicaseCrmApi.calls.reset();
          $scope.$digest();
        });

        it('adds the properties of the `case_filter` object to the query params', function () {
          expect($scope.calendarCaseParams).toEqual(_.assign({
            'status_id.grouping': 'Opened'
          }, {
            foo: newFilterValue
          }));
        });
      });
    });

    describe('refresh callback for activity cards in the calendar', function () {
      beforeEach(function () {
        spyOn($rootScope, '$emit');
        initController();

        $scope.activityCardRefreshCalendar();
        $scope.$digest();
      });

      it('emits the calendar reload event', function () {
        expect($rootScope.$emit).toHaveBeenCalledWith('civicase::ActivitiesCalendar::reload');
      });

      it('reload both the activities and milestone panels', function () {
        expect($rootScope.$emit).toHaveBeenCalledWith('civicase::PanelQuery::reload', [
          'activities',
          'milestones'
        ]);
      });
    });

    describe('panel-query panel: new cases', function () {
      beforeEach(function () {
        initController();
      });

      it('is defined', function () {
        expect($scope.newCasesPanel).toBeDefined();
      });

      describe('query', function () {
        it('is defined', function () {
          expect($scope.newCasesPanel.query).toBeDefined();
        });

        it('is for the Case entity', function () {
          expect($scope.newCasesPanel.query.entity).toBe('Case');
        });

        it('is calls the getcaselist endpoint', function () {
          expect($scope.newCasesPanel.query.action).toBe('getcaselist');
        });

        it('adds the params defined in the relationship filter', function () {
          expect($scope.newCasesPanel.query.params)
            .toEqual(jasmine.objectContaining($scope.activityFilters.case_filter));
        });

        it('fetches only the cases with the "Opened" class', function () {
          expect($scope.newCasesPanel.query.params['status_id.grouping']).toBe('Opened');
        });

        it('fetches cases that have not been deleted', function () {
          expect($scope.newCasesPanel.query.params.is_deleted).toBe(0);
        });

        it('sorts by start_date and case id, descending order', function () {
          expect($scope.newCasesPanel.query.params.options.sort).toBe('start_date DESC, id DESC');
        });
      });

      describe('handlers', function () {
        describe('results handler', function () {
          var mockedResults = mockResults('contacts');

          it('is defined', function () {
            expect($scope.newCasesPanel.handlers.results).toBeDefined();
          });

          describe('when invoked', function () {
            var contactsCount, ContactsCache;

            beforeEach(inject(function (_ContactsCache_) {
              ContactsCache = _ContactsCache_;
            }));

            beforeEach(function () {
              spyOn(ContactsCache, 'add');

              contactsCount = countTotalAndUniqueContactIds(mockedResults, 'contacts');
              $scope.newCasesPanel.handlers.results(mockedResults);
            });

            it('calls the formatCase service on each result item', function () {
              expect(formatCase.calls.count()).toBe(mockedResults.length);
            });

            it('calls ContactsCache.add() with a duplicate-free list of the results\'s contacts', function () {
              var contactIds = ContactsCache.add.calls.argsFor(0)[0];

              expect(ContactsCache.add).toHaveBeenCalled();
              expect(contactIds).not.toEqual(contactsCount.total);
              expect(contactIds).toEqual(contactsCount.uniq);
            });
          });
        });

        describe('range handler', function () {
          it('is defined', function () {
            expect($scope.newCasesPanel.handlers.range).toBeDefined();
          });

          describe('when invoked', function () {
            describe('when the week range is selected', function () {
              beforeEach(function () {
                $scope.newCasesPanel.handlers.range('week', $scope.newCasesPanel.query.params);
              });

              it('filters by `start_date` between start and end of the current week', function () {
                expect($scope.newCasesPanel.query.params.start_date).toBeDefined();
                expect($scope.newCasesPanel.query.params.start_date).toEqual({
                  BETWEEN: getStartEndOfRange('week', 'YYYY-MM-DD')
                });
              });
            });

            describe('when the month range is selected', function () {
              beforeEach(function () {
                $scope.newCasesPanel.handlers.range('month', $scope.newCasesPanel.query.params);
              });

              it('filters by `start_date` between start and end of the current month', function () {
                expect($scope.newCasesPanel.query.params.start_date).toBeDefined();
                expect($scope.newCasesPanel.query.params.start_date).toEqual({
                  BETWEEN: getStartEndOfRange('month', 'YYYY-MM-DD')
                });
              });
            });
          });
        });
      });

      describe('custom data', function () {
        it('is defined', function () {
          expect($scope.newCasesPanel.custom).toBeDefined();
        });

        it('sets the custom name of the items to "cases"', function () {
          expect($scope.newCasesPanel.custom.itemName).toBe('cases');
        });

        describe('custom click handler', function () {
          it('is defined', function () {
            expect($scope.newCasesPanel.custom.caseClick).toBeDefined();
          });

          describe('when invoked', function () {
            let $location, mockCase, caseType;

            beforeEach(inject(function (_$location_) {
              const caseTypeId = _.chain(CaseTypesData).keys().sample().value();
              caseType = CaseTypesData[caseTypeId];
              $location = _$location_;
              mockCase = {
                id: _.random(1, 10),
                case_type_id: caseTypeId
              };

              spyOn($location, 'path').and.callThrough();
              spyOn($location, 'search').and.callThrough();

              $scope.newCasesPanel.custom.caseClick(mockCase);
            }));

            it('redirects to the individual case details page', function () {
              expect($location.path).toHaveBeenCalledWith('case/list');
              expect($location.search).toHaveBeenCalledWith('caseId', mockCase.id);
            });

            it('passes the case type active status to the manage case page', () => {
              expect($location.search).toHaveBeenCalledWith('cf', JSON.stringify({
                'case_type_id.is_active': caseType.is_active
              }));
            });
          });
        });

        describe('view cases link', function () {
          var linkProps, queryParams, userId;

          beforeEach(function () {
            userId = 20;
          });

          it('is defined', function () {
            expect($scope.newCasesPanel.custom.viewCasesLink).toBeDefined();
          });

          it('contains a label for the link', function () {
            expect($scope.newCasesPanel.custom.viewCasesLink.label).toBeDefined();
          });

          it('contains a trusted url for the link', function () {
            var url = $scope.newCasesPanel.custom.viewCasesLink.url;

            expect(url).toBeDefined();
            expect(url.$$unwrapTrustedValue).toBeDefined();
          });

          describe('when the relationship type filter is: My cases', function () {
            beforeEach(function () {
              $scope.filters.caseRelationshipType = 'is_case_manager';
              $scope.activityFilters.case_filter.case_manager = userId;
              $scope.$digest();

              linkProps = $scope.newCasesPanel.custom.viewCasesLink;
              queryParams = CRM.testUtils.extractQueryStringParams(linkProps.url.$$unwrapTrustedValue());
            });

            it('sets "View all my cases" as label', function () {
              expect(linkProps.label).toBe('View all my cases');
            });

            it('passes the correct filter to the manage cases page', function () {
              expect(queryParams.cf.case_manager).toEqual([userId]);
            });
          });

          describe('when the relationship type filter is: Cases I\'m involved with', function () {
            beforeEach(function () {
              $scope.filters.caseRelationshipType = 'is_involved';
              $scope.activityFilters.case_filter.contact_id = [userId];
              $scope.$digest();

              linkProps = $scope.newCasesPanel.custom.viewCasesLink;
              queryParams = CRM.testUtils.extractQueryStringParams(linkProps.url.$$unwrapTrustedValue());
            });

            it('sets "View all my cases" as label', function () {
              expect(linkProps.label).toBe('View all my cases');
            });

            it('passes the correct filter to the manage cases page', function () {
              expect(queryParams.cf.contact_id).toEqual([userId]);
            });
          });

          describe('when the relationship type filter is: All Cases', function () {
            beforeEach(function () {
              $scope.filters.caseRelationshipType = 'all';
              $scope.$digest();

              linkProps = $scope.newCasesPanel.custom.viewCasesLink;
              queryParams = CRM.testUtils.extractQueryStringParams(linkProps.url.$$unwrapTrustedValue());
            });

            it('sets "View all cases" as label', function () {
              expect(linkProps.label).toBe('View all cases');
            });

            it('passes the correct filter to the manage cases page', function () {
              expect(queryParams.cf).not.toBeDefined();
            });
          });
        });
      });

      describe('when the relationship type changes', function () {
        var newFilterValue, newType;

        beforeEach(function () {
          newType = 'is_involed';
          newFilterValue = 'bar';

          $scope.filters.caseRelationshipType = newType;
          $scope.activityFilters.case_filter.foo = newFilterValue;

          $scope.$digest();
        });

        it('adds the properties of the `case_filter` object to the query params', function () {
          expect($scope.newCasesPanel.query.params).toEqual(jasmine.objectContaining({
            foo: newFilterValue
          }));
        });
      });
    });

    describe('panel-query panel: new milestones', function () {
      beforeEach(function () {
        initController();
      });

      it('is defined', function () {
        expect($scope.newMilestonesPanel).toBeDefined();
      });

      it('has a name defined', function () {
        expect($scope.newMilestonesPanel.name).toBe('milestones');
      });

      describe('query', function () {
        it('is defined', function () {
          expect($scope.newMilestonesPanel.query).toBeDefined();
        });

        it('is for the Activity entity', function () {
          expect($scope.newMilestonesPanel.query.entity).toBe('Activity');
        });

        it('queries the `get contact activities` action by default', function () {
          expect($scope.newMilestonesPanel.query.action).toBe('getcontactactivities');
        });

        it('counts using the `get contact activities count` action by default', function () {
          expect($scope.newMilestonesPanel.query.countAction).toBe('getcontactactivitiescount');
        });

        it('adds the params defined in the relationship filter', function () {
          expect($scope.newMilestonesPanel.query.params)
            .toEqual(jasmine.objectContaining($scope.activityFilters));
        });

        it('fetches only the milestones', function () {
          expect($scope.newMilestonesPanel.query.params['activity_type_id.grouping']).toEqual({
            LIKE: '%milestone%'
          });
        });

        it('fetches only the user\'s milestones', function () {
          expect($scope.newMilestonesPanel.query.params.contact_id).toBe('user_contact_id');
        });

        it('fetches only the milestones on the current revision', function () {
          expect($scope.newMilestonesPanel.query.params.is_current_revision).toBe(1);
        });

        it('fetches only the non-test, non-deleted milestones', function () {
          expect($scope.newMilestonesPanel.query.params.is_test).toBe(0);
          expect($scope.newMilestonesPanel.query.params.is_deleted).toBe(0);
        });

        it('fetches only the incomplete milestones', function () {
          expect($scope.newMilestonesPanel.query.params.status_id).toEqual({
            IN: ActivityStatusType.getAll().incomplete
          });
        });

        it('sorts by is_overdue (descending order) and activity_date_time (ascending order)', function () {
          expect($scope.newMilestonesPanel.query.params.options.sort).toBe('is_overdue DESC, activity_date_time ASC');
        });

        it('asks the api to return only a specific set of fields', function () {
          expect($scope.newMilestonesPanel.query.params.return).toEqual([
            'subject', 'details', 'activity_type_id', 'status_id', 'source_contact_name',
            'target_contact_name', 'assignee_contact_name', 'activity_date_time', 'is_star',
            'original_id', 'tag_id.name', 'tag_id.description', 'tag_id.color', 'file_id',
            'is_overdue', 'case_id', 'priority_id', 'case_id.case_type_id', 'case_id.status_id',
            'case_id.contacts'
          ]);
        });
      });

      describe('handlers', function () {
        describe('results handler', function () {
          var mockedResults = mockResults('case_id.contacts');

          it('is defined', function () {
            expect($scope.newMilestonesPanel.handlers.results).toBeDefined();
          });

          describe('when invoked', function () {
            var contactsCount, ContactsCache;

            beforeEach(inject(function (_ContactsCache_) {
              ContactsCache = _ContactsCache_;
            }));

            beforeEach(function () {
              spyOn(ContactsCache, 'add');

              contactsCount = countTotalAndUniqueContactIds(mockedResults, 'case_id.contacts');
              $scope.newMilestonesPanel.handlers.results(mockedResults);
            });

            it('calls the formatCase service on each result item', function () {
              expect(formatActivity.calls.count()).toBe(mockedResults.length);
            });

            it('calls ContactsCache.add() with a duplicate-free list of the results\'s contacts', function () {
              var contactIds = ContactsCache.add.calls.argsFor(0)[0];

              expect(ContactsCache.add).toHaveBeenCalled();
              expect(contactIds).not.toEqual(contactsCount.total);
              expect(contactIds).toEqual(contactsCount.uniq);
            });
          });
        });

        describe('range handler', function () {
          it('is defined', function () {
            expect($scope.newMilestonesPanel.handlers.range).toBeDefined();
          });

          describe('when invoked', function () {
            describe('when the week range is selected', function () {
              beforeEach(function () {
                $scope.newMilestonesPanel.handlers.range('week', $scope.newMilestonesPanel.query.params);
              });

              it('filters by `activity_date_time` between today and end of the current week', function () {
                expect($scope.newMilestonesPanel.query.params.activity_date_time).toBeDefined();
                expect($scope.newMilestonesPanel.query.params.activity_date_time).toEqual({
                  BETWEEN: getStartEndOfRange('week', 'YYYY-MM-DD HH:mm:ss')
                });
              });
            });

            describe('when the month range is selected', function () {
              beforeEach(function () {
                $scope.newMilestonesPanel.handlers.range('month', $scope.newMilestonesPanel.query.params);
              });

              it('filters by `activity_date_time` between today and end of the current month', function () {
                expect($scope.newMilestonesPanel.query.params.activity_date_time).toBeDefined();
                expect($scope.newMilestonesPanel.query.params.activity_date_time).toEqual({
                  BETWEEN: getStartEndOfRange('month', 'YYYY-MM-DD HH:mm:ss')
                });
              });
            });
          });
        });
      });

      describe('custom data', function () {
        it('is defined', function () {
          expect($scope.newMilestonesPanel.custom).toBeDefined();
        });

        it('sets the custom name of the items to "milestones"', function () {
          expect($scope.newMilestonesPanel.custom.itemName).toBe('milestones');
        });

        describe('activity involvement filter', function () {
          it('is defined', function () {
            expect($scope.newMilestonesPanel.custom.involvementFilter).toBeDefined();
          });

          it('is set to "myActivities" by default', function () {
            expect($scope.newMilestonesPanel.custom.involvementFilter).toEqual({
              '@involvingContact': 'myActivities'
            });
          });

          describe('when it changes', function () {
            beforeEach(function () {
              spyOn($rootScope, '$broadcast').and.callThrough();

              $scope.newMilestonesPanel.custom.involvementFilter = { '@involvingContact': '' };
              $scope.$digest();
            });

            it('sets the query action to "get"', function () {
              expect($scope.newMilestonesPanel.query.action).toBe('get');
            });

            it('sets the count query action to "get count"', function () {
              expect($scope.newMilestonesPanel.query.countAction).toBe('getcount');
            });

            it('broadcasts a "civicaseActivityFeed.query" event', function () {
              expect($rootScope.$broadcast).toHaveBeenCalledWith(
                'civicaseActivityFeed.query', {
                  filters: $scope.newMilestonesPanel.custom.involvementFilter,
                  apiParams: $scope.newMilestonesPanel.query.params,
                  reset: true
                }
              );
            });
          });

          describe('when it changes to "my activities"', function () {
            beforeEach(function () {
              $scope.newMilestonesPanel.custom.involvementFilter = { '@involvingContact': 'myActivities' };
              $scope.$digest();
            });

            it('sets the query action to "get contact activities"', function () {
              expect($scope.newMilestonesPanel.query.action).toBe('getcontactactivities');
            });

            it('sets the count query action to "get contact activities count"', function () {
              expect($scope.newMilestonesPanel.query.countAction).toBe('getcontactactivitiescount');
            });
          });
        });

        describe('refresh callback for activity cards', function () {
          it('is defined', function () {
            expect($scope.newMilestonesPanel.custom.cardRefresh).toBeDefined();
          });

          describe('when called', function () {
            beforeEach(function () {
              spyOn($rootScope, '$emit');

              $scope.newMilestonesPanel.custom.cardRefresh();
              $scope.$digest();
            });

            it('emits the calendar reload event', function () {
              expect($rootScope.$emit).toHaveBeenCalledWith('civicase::ActivitiesCalendar::reload');
            });

            it('reloads its own panel', function () {
              expect($rootScope.$emit).toHaveBeenCalledWith(
                'civicase::PanelQuery::reload',
                $scope.newMilestonesPanel.name
              );
            });
          });
        });
      });

      describe('when the relationship type changes', function () {
        var newFilterValue, newType;

        beforeEach(function () {
          newType = 'is_involed';
          newFilterValue = 'bar';

          $scope.filters.caseRelationshipType = newType;
          $scope.activityFilters.case_filter.foo = newFilterValue;

          $scope.$digest();
        });

        it('adds the properties of the `case_filter` object to the query params', function () {
          expect($scope.newMilestonesPanel.query.params.case_filter.foo).toEqual(newFilterValue);
        });
      });
    });

    describe('panel-query panel: activities', function () {
      beforeEach(function () {
        initController();
      });

      it('is defined', function () {
        expect($scope.activitiesPanel).toBeDefined();
      });

      it('has a name defined', function () {
        expect($scope.activitiesPanel.name).toBe('activities');
      });

      describe('query', function () {
        it('is defined', function () {
          expect($scope.activitiesPanel.query).toBeDefined();
        });

        it('is for the Activity entity', function () {
          expect($scope.activitiesPanel.query.entity).toBe('Activity');
        });

        it('queries the `get contact activities` action by default', function () {
          expect($scope.activitiesPanel.query.action).toBe('getcontactactivities');
        });

        it('counts using the `get contact activities count` action by default', function () {
          expect($scope.activitiesPanel.query.countAction).toBe('getcontactactivitiescount');
        });

        it('adds the params defined in the relationship filter', function () {
          expect($scope.activitiesPanel.query.params)
            .toEqual(jasmine.objectContaining($scope.activityFilters));
        });

        it('fetches everything expect milestones', function () {
          expect($scope.activitiesPanel.query.params['activity_type_id.grouping']).toEqual({
            'NOT LIKE': '%milestone%'
          });
        });

        it('fetches only the user\'s activities', function () {
          expect($scope.activitiesPanel.query.params.contact_id).toBe('user_contact_id');
        });

        it('fetches only the activities on the current revision', function () {
          expect($scope.activitiesPanel.query.params.is_current_revision).toBe(1);
        });

        it('fetches only the non-test, non-deleted activities', function () {
          expect($scope.activitiesPanel.query.params.is_test).toBe(0);
          expect($scope.activitiesPanel.query.params.is_deleted).toBe(0);
        });

        it('fetches only the incomplete activities', function () {
          expect($scope.activitiesPanel.query.params.status_id).toEqual({
            IN: ActivityStatusType.getAll().incomplete
          });
        });

        it('sorts by is_overdue (descending order) and activity_date_time (ascending order)', function () {
          expect($scope.activitiesPanel.query.params.options.sort).toBe('is_overdue DESC, activity_date_time ASC');
        });

        it('asks the api to return only a specific set of fields', function () {
          expect($scope.activitiesPanel.query.params.return).toEqual([
            'subject', 'details', 'activity_type_id', 'status_id', 'source_contact_name',
            'target_contact_name', 'assignee_contact_name', 'activity_date_time', 'is_star',
            'original_id', 'tag_id.name', 'tag_id.description', 'tag_id.color', 'file_id',
            'is_overdue', 'case_id', 'priority_id', 'case_id.case_type_id', 'case_id.status_id',
            'case_id.contacts'
          ]);
        });
      });

      describe('handlers', function () {
        describe('results handler', function () {
          var mockedResults = mockResults('case_id.contacts');

          it('is defined', function () {
            expect($scope.activitiesPanel.handlers.results).toBeDefined();
          });

          describe('when invoked', function () {
            var contactsCount, ContactsCache;

            beforeEach(inject(function (_ContactsCache_) {
              ContactsCache = _ContactsCache_;
            }));

            beforeEach(function () {
              spyOn(ContactsCache, 'add');

              contactsCount = countTotalAndUniqueContactIds(mockedResults, 'case_id.contacts');
              $scope.activitiesPanel.handlers.results(mockedResults);
            });

            it('calls the formatCase service on each result item', function () {
              expect(formatActivity.calls.count()).toBe(mockedResults.length);
            });

            it('calls ContactsCache.add() with a duplicate-free list of the results\'s contacts', function () {
              var contactIds = ContactsCache.add.calls.argsFor(0)[0];

              expect(ContactsCache.add).toHaveBeenCalled();
              expect(contactIds).not.toEqual(contactsCount.total);
              expect(contactIds).toEqual(contactsCount.uniq);
            });
          });
        });

        describe('range handler', function () {
          it('is defined', function () {
            expect($scope.activitiesPanel.handlers.range).toBeDefined();
          });

          describe('when invoked', function () {
            describe('when the week range is selected', function () {
              beforeEach(function () {
                $scope.activitiesPanel.handlers.range('week', $scope.activitiesPanel.query.params);
              });

              it('filters by `activity_date_time` between start and end of the current week', function () {
                expect($scope.activitiesPanel.query.params.activity_date_time).toBeDefined();
                expect($scope.activitiesPanel.query.params.activity_date_time).toEqual({
                  BETWEEN: getStartEndOfRange('week', 'YYYY-MM-DD HH:mm:ss')
                });
              });
            });

            describe('when the month range is selected', function () {
              beforeEach(function () {
                $scope.activitiesPanel.handlers.range('month', $scope.activitiesPanel.query.params);
              });

              it('filters by `activity_date_time` between start and end of the current month', function () {
                expect($scope.activitiesPanel.query.params.activity_date_time).toBeDefined();
                expect($scope.activitiesPanel.query.params.activity_date_time).toEqual({
                  BETWEEN: getStartEndOfRange('month', 'YYYY-MM-DD HH:mm:ss')
                });
              });
            });
          });
        });
      });

      describe('custom data', function () {
        it('is defined', function () {
          expect($scope.activitiesPanel.custom).toBeDefined();
        });

        it('sets the custom name of the items to "milestones"', function () {
          expect($scope.activitiesPanel.custom.itemName).toBe('activities');
        });

        describe('activity involvement filter', function () {
          it('is defined', function () {
            expect($scope.activitiesPanel.custom.involvementFilter).toBeDefined();
          });

          it('is set to "myActivities" by default', function () {
            expect($scope.activitiesPanel.custom.involvementFilter).toEqual({
              '@involvingContact': 'myActivities'
            });
          });

          describe('when it changes', function () {
            beforeEach(function () {
              spyOn($rootScope, '$broadcast').and.callThrough();

              $scope.activitiesPanel.custom.involvementFilter = { '@involvingContact': '' };
              $scope.$digest();
            });

            it('sets the query action to "get"', function () {
              expect($scope.activitiesPanel.query.action).toBe('get');
            });

            it('sets the count query action to "get count"', function () {
              expect($scope.activitiesPanel.query.countAction).toBe('getcount');
            });

            it('broadcasts a "civicaseActivityFeed.query" event', function () {
              expect($rootScope.$broadcast).toHaveBeenCalledWith(
                'civicaseActivityFeed.query', {
                  filters: $scope.activitiesPanel.custom.involvementFilter,
                  apiParams: $scope.activitiesPanel.query.params,
                  reset: true
                }
              );
            });
          });

          describe('when it changes to "my activities"', function () {
            beforeEach(function () {
              $scope.activitiesPanel.custom.involvementFilter = { '@involvingContact': 'myActivities' };
              $scope.$digest();
            });

            it('sets the query action to "get contact activities"', function () {
              expect($scope.activitiesPanel.query.action).toBe('getcontactactivities');
            });

            it('sets the count query action to "get contact activities count"', function () {
              expect($scope.activitiesPanel.query.countAction).toBe('getcontactactivitiescount');
            });
          });
        });

        describe('refresh callback for activity cards', function () {
          it('is defined', function () {
            expect($scope.activitiesPanel.custom.cardRefresh).toBeDefined();
          });

          describe('when called', function () {
            beforeEach(function () {
              spyOn($rootScope, '$emit');

              $scope.activitiesPanel.custom.cardRefresh();
              $scope.$digest();
            });

            it('emits the calendar reload event', function () {
              expect($rootScope.$emit).toHaveBeenCalledWith('civicase::ActivitiesCalendar::reload');
            });

            it('reloads its own panel', function () {
              expect($rootScope.$emit).toHaveBeenCalledWith(
                'civicase::PanelQuery::reload',
                $scope.activitiesPanel.name
              );
            });
          });
        });
      });

      describe('when the relationship type changes', function () {
        var newFilterValue, newType;

        beforeEach(function () {
          newType = 'is_involed';
          newFilterValue = 'bar';

          $scope.filters.caseRelationshipType = newType;
          $scope.activityFilters.case_filter.foo = newFilterValue;

          $scope.$digest();
        });

        it('adds the properties of the `case_filter` object to the query params', function () {
          expect($scope.activitiesPanel.query.params.case_filter.foo).toEqual(newFilterValue);
        });
      });

      describe('when the dashboard filters changed event is fired', function () {
        var newFilterValue;

        beforeEach(function () {
          newFilterValue = 'new value';
          $scope.activityFilters.case_filter.foo = newFilterValue;

          $rootScope.$broadcast('civicase::dashboard-filters::updated');
        });

        it('force reloads all panels', function () {
          expect($scope.activitiesPanel.config.forceReload).toBe(true);
          expect($scope.newMilestonesPanel.config.forceReload).toBe(true);
          expect($scope.newCasesPanel.config.forceReload).toBe(true);
          expect($scope.activitiesPanel.query.params.case_filter.foo).toEqual(newFilterValue);
          expect($scope.newMilestonesPanel.query.params.case_filter.foo).toEqual(newFilterValue);
          expect($scope.newCasesPanel.query.params.foo).toEqual(newFilterValue);
        });
      });
    });

    /**
     * Initializes the controller and digests the scope
     */
    function initController () {
      $controller('dashboardTabController', {
        $scope: $scope
      });
      $scope.$digest();
    }

    /**
     * Given a list of mocked results, it will find all the contact ids in the
     * given "contacts list" property and returns the total of all the ids and of
     * all the unique ids
     *
     * @param {Array} mockedResults mock results
     * @param {Array} contactsListKey contacts list key
     * @returns {object} total and unique
     */
    function countTotalAndUniqueContactIds (mockedResults, contactsListKey) {
      var total, uniq;

      uniq = _(mockedResults)
        .pluck(contactsListKey).flatten()
        .pluck('contact_id')
        .tap(function (nonUniq) {
          total = nonUniq;
          return nonUniq;
        })
        .uniq().value();

      return { total: total, uniq: uniq };
    }

    /**
     * Returns the start and end of the given range (week/month) formatted
     * in the given format
     *
     * @param {string} range range
     * @param {string} format format
     * @returns {Array} stand and end date
     */
    function getStartEndOfRange (range, format) {
      var now = moment();
      var start = now.startOf(range).format(format);
      var end = now.endOf(range).format(format);

      return [start, end];
    }

    /**
     * Mocks a list of results (either cases or activities), and for each of them
     * places a list of mocked contacts in the property with the given name
     *
     * @param {string} contactsListKey contacts list key
     * @returns {Array} Mock results
     */
    function mockResults (contactsListKey) {
      return _.times(5, function () {
        var obj = {};
        obj[contactsListKey] = _.times(2, function () {
          return { contact_id: _.random(1, 5) };
        });

        return obj;
      });
    }
  });
}(CRM.$, CRM._, moment));
