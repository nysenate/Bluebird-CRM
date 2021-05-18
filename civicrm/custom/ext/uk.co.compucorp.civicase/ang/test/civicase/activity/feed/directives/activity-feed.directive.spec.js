((_) => {
  describe('civicaseActivityFeed', () => {
    describe('Activity Feed Controller', () => {
      let $provide, $controller, $rootScope, $scope, $q, civicaseCrmApi,
        CaseTypesMockData, activitiesMockData, ActivityType,
        activitiesInCurrentPage, showFullContactNameOnActivityFeed,
        totalNumberOfActivities;

      beforeEach(module('civicase', 'civicase.data', (_$provide_) => {
        $provide = _$provide_;

        $provide.value('civicaseCrmApi', jasmine.createSpy('civicaseCrmApi'));
      }));

      beforeEach(inject((_$controller_, _$rootScope_, _$q_, _CaseTypesMockData_,
        _civicaseCrmApi_, _activitiesMockData_, _ActivityType_,
        _showFullContactNameOnActivityFeed_) => {
        $controller = _$controller_;
        $rootScope = _$rootScope_;
        $q = _$q_;
        CaseTypesMockData = _CaseTypesMockData_;
        ActivityType = _ActivityType_;
        activitiesMockData = _activitiesMockData_;
        showFullContactNameOnActivityFeed = _showFullContactNameOnActivityFeed_;

        $scope = $rootScope.$new();
        $scope.$bindToRoute = jasmine.createSpy('$bindToRoute');
        civicaseCrmApi = _civicaseCrmApi_;
      }));

      describe('on init', () => {
        beforeEach(() => {
          initController();
        });

        it('provides the value for the "Show Full Contact Name On ActivityFeed" setting', () => {
          expect($scope.showFullContactNameOnActivityFeed).toBe(showFullContactNameOnActivityFeed);
        });
      });

      describe('loadActivities', () => {
        beforeEach(() => {
          activitiesInCurrentPage = [];
          totalNumberOfActivities = 0;

          mockActivitiesAPICall();
          initController();

          $scope.filters.activitySet = CaseTypesMockData.get()['1'].definition.activitySets[0].name;
          $scope.filters.activity_type_id = '5';
        });

        describe('when filtered by activity set and activity id', () => {
          let expectedActivityTypeIDs;

          beforeEach(() => {
            expectedActivityTypeIDs = [];
            $scope.$digest();

            _.each(CaseTypesMockData.get()['1'].definition.activitySets[0].activityTypes, (activityTypeFromSet) => {
              expectedActivityTypeIDs.push(_.findKey(ActivityType.getAll(true), (activitySet) => {
                return activitySet.name === activityTypeFromSet.name;
              }));
            });
            expectedActivityTypeIDs.push($scope.filters.activity_type_id);
          });

          it('requests the activities using the "getAll" api action', () => {
            expect(civicaseCrmApi).toHaveBeenCalledWith({
              acts: ['Activity', 'getAll', jasmine.any(Object)],
              all: ['Activity', 'getcount', jasmine.any(Object)]
            });
          });

          it('filters by the activities of the selected activity set and the activity id', () => {
            const args = civicaseCrmApi.calls.mostRecent().args[0].acts[2].activity_type_id;

            expect(args).toEqual({ IN: expectedActivityTypeIDs });
          });
        });

        describe('when filtered by "My Activities"', () => {
          beforeEach(() => {
            $scope.filters['@involvingContact'] = 'myActivities';

            $scope.$digest();
          });

          it('requests the activities using the "get contact activities" api action', () => {
            expect(civicaseCrmApi).toHaveBeenCalledWith({
              acts: ['Activity', 'getcontactactivities', jasmine.any(Object)],
              all: ['Activity', 'getcontactactivitiescount', jasmine.any(Object)]
            });
          });
        });

        describe('when the filters are updated', () => {
          let expectedFilters;

          beforeEach(() => {
            $scope.params.filters = {
              case_type_id: _.uniqueId()
            };
            expectedFilters = angular.extend({}, $scope.filters, $scope.params.filters);
            $scope.$digest();
          });

          it('update the existing filters', () => {
            expect($scope.filters).toEqual(expectedFilters);
          });

          it('updates the activity feed', () => {
            expect(civicaseCrmApi).toHaveBeenCalledWith({
              acts: ['Activity', 'getAll', jasmine.any(Object)],
              all: ['Activity', 'getcount', jasmine.any(Object)]
            });
          });
        });
      });

      describe('checkIfRecordsAvailableOnDirection()', () => {
        describe('when total count of activities is 0', () => {
          beforeEach(() => {
            activitiesInCurrentPage = [];
            totalNumberOfActivities = 0;

            mockActivitiesAPICall();
            initController();
            $scope.$digest();
          });

          it('does not show load more button', () => {
            expect($scope.checkIfRecordsAvailableOnDirection('down')).toBe(false);
          });
        });

        describe('when total count of activities is more than 0', () => {
          describe('starting offset is 0', () => {
            describe('and less than 25', () => {
              beforeEach(() => {
                activitiesInCurrentPage = activitiesMockData.getSentNoOfActivities(20);
                totalNumberOfActivities = 20;

                mockActivitiesAPICall();
                initController();
                $scope.$digest();

                $scope.$emit('civicase::month-nav::set-starting-offset', {
                  startingOffset: 0
                });
                $scope.$digest();
              });

              it('does not show load more button on down direction', () => {
                expect($scope.checkIfRecordsAvailableOnDirection('down')).toBe(false);
              });

              it('does not show load more button on top direction', () => {
                expect($scope.checkIfRecordsAvailableOnDirection('up')).toBe(false);
              });
            });

            describe('and total count is 26', () => {
              beforeEach(() => {
                activitiesInCurrentPage = activitiesMockData.getSentNoOfActivities(26);
                totalNumberOfActivities = 26;

                mockActivitiesAPICall();
                initController();
                $scope.$digest();

                $scope.$emit('civicase::month-nav::set-starting-offset', {
                  startingOffset: 0
                });
                $scope.$digest();
              });

              it('shows load more button on down direction', () => {
                expect($scope.checkIfRecordsAvailableOnDirection('down')).toBe(true);
              });

              it('does not show load more button on top direction', () => {
                expect($scope.checkIfRecordsAvailableOnDirection('up')).toBe(false);
              });
            });

            describe('and more than 25', () => {
              beforeEach(() => {
                activitiesInCurrentPage = activitiesMockData.getSentNoOfActivities(30);
                totalNumberOfActivities = 30;

                mockActivitiesAPICall();
                initController();
                $scope.$digest();

                $scope.$emit('civicase::month-nav::set-starting-offset', {
                  startingOffset: 0
                });
                $scope.$digest();
              });

              it('shows load more button on down direction', () => {
                expect($scope.checkIfRecordsAvailableOnDirection('down')).toBe(true);
              });

              it('does not show load more button on top direction', () => {
                expect($scope.checkIfRecordsAvailableOnDirection('up')).toBe(false);
              });
            });
          });

          describe('starting offset is between 0 and total number of records', () => {
            beforeEach(() => {
              activitiesInCurrentPage = activitiesMockData.getSentNoOfActivities(60);
              totalNumberOfActivities = 60;

              mockActivitiesAPICall();
              initController();
              $scope.$digest();

              $scope.$emit('civicase::month-nav::set-starting-offset', {
                startingOffset: 30
              });
              $scope.$digest();
            });

            it('shows load more button on down direction', () => {
              expect($scope.checkIfRecordsAvailableOnDirection('down')).toBe(true);
            });

            it('shows load more button on top direction', () => {
              expect($scope.checkIfRecordsAvailableOnDirection('up')).toBe(true);
            });
          });
        });
      });

      describe('when clicking load more button', () => {
        beforeEach(() => {
          activitiesInCurrentPage = activitiesMockData.getSentNoOfActivities(25);
          totalNumberOfActivities = 26;

          mockActivitiesAPICall();
          initController();
          $scope.$digest();

          activitiesInCurrentPage = activitiesMockData.getSentNoOfActivities(1);
          activitiesInCurrentPage[0].subject = 'custom text';
        });

        describe('nextPage()', () => {
          beforeEach(() => {
            $scope.nextPage();
            $scope.$digest();
          });

          it('appends new activties to the end of previous activities', () => {
            expect($scope.activities[25].subject).toBe('custom text');
          });
        });

        describe('previousPage()', () => {
          beforeEach(() => {
            $scope.previousPage();
            $scope.$digest();
          });

          it('appends new activties to the beginning of previous activities', () => {
            expect($scope.activities[0].subject).toBe('custom text');
          });
        });
      });

      describe('when clicked on a month nav and data for that month' +
       'is not rendered on the screen', () => {
        beforeEach(() => {
          activitiesInCurrentPage = activitiesMockData.getSentNoOfActivities(60);
          totalNumberOfActivities = 60;

          mockActivitiesAPICall();

          initController();
          $scope.$digest();

          $scope.$emit('civicase::month-nav::set-starting-offset', {
            startingOffset: 10
          });
          $scope.$digest();
        });

        it('shows records starting from the clicked month', () => {
          expect(civicaseCrmApi).toHaveBeenCalledWith(jasmine.objectContaining({
            acts: ['Activity', 'getAll', jasmine.objectContaining({
              options: jasmine.objectContaining({
                offset: 10
              })
            })]
          }));
        });
      });

      describe('when activity panel show/hide', () => {
        beforeEach(() => {
          activitiesInCurrentPage = activitiesMockData.getSentNoOfActivities(25);
          totalNumberOfActivities = 25;
          mockActivitiesAPICall();

          initController();
          $scope.$digest();
        });

        describe('when activity panel is shown', () => {
          describe('and hide-quick-nav-when-details-is-visible property is true', () => {
            beforeEach(() => {
              $scope.hideQuickNavWhenDetailsIsVisible = true;
              $rootScope.$broadcast('civicase::activity-feed::show-activity-panel');
            });

            it('hides the activity nav', () => {
              expect($scope.isMonthNavVisible).toBe(false);
            });
          });

          describe('and hide-quick-nav-when-details-is-visible property is false', () => {
            beforeEach(() => {
              $scope.hideQuickNavWhenDetailsIsVisible = false;
              $rootScope.$broadcast('civicase::activity-feed::show-activity-panel');
            });

            it('does not hide the activity nav', () => {
              expect($scope.isMonthNavVisible).toBe(true);
            });
          });
        });

        describe('when activity panel is hidden', () => {
          describe('and hide-quick-nav-when-details-is-visible property is true', () => {
            beforeEach(() => {
              $scope.isMonthNavVisible = false;
              $scope.hideQuickNavWhenDetailsIsVisible = true;
              $rootScope.$broadcast('civicase::activity-feed::hide-activity-panel');
            });

            it('shows the activity nav', () => {
              expect($scope.isMonthNavVisible).toBe(true);
            });
          });

          describe('and hide-quick-nav-when-details-is-visible property is false', () => {
            beforeEach(() => {
              $scope.isMonthNavVisible = false;
              $scope.hideQuickNavWhenDetailsIsVisible = false;
              $rootScope.$broadcast('civicase::activity-feed::hide-activity-panel');
            });

            it('does not show the activity nav', () => {
              expect($scope.isMonthNavVisible).toBe(false);
            });
          });
        });
      });

      /**
       * Mocks Activities API calls
       */
      function mockActivitiesAPICall () {
        civicaseCrmApi.and.returnValue($q.resolve({
          acts: { values: activitiesInCurrentPage },
          all: totalNumberOfActivities
        }));

        $provide.factory('crmThrottle', () => {
          const crmThrottle = jasmine.createSpy('crmThrottle');

          crmThrottle.and.callFake((callable) => {
            callable();

            return $q.resolve([{
              acts: { values: activitiesInCurrentPage },
              all: totalNumberOfActivities
            }]);
          });

          return crmThrottle;
        });
      }

      /**
       * Initializes the activity feed controller.
       */
      function initController () {
        $scope.caseTypeId = '1';
        $scope.filters = {};
        $scope.displayOptions = {};
        $scope.params = {
          displayOptions: 1
        };

        $controller('civicaseActivityFeedController', {
          $scope: $scope
        });
      }
    });
  });
})(CRM._);
