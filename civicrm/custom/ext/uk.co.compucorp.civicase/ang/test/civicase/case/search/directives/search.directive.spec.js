/* eslint-env jasmine */
(($, _, crmCheckPerm) => {
  describe('civicaseSearch', () => {
    let $controller, $rootScope, $scope, $window, $timeout, CaseFilters,
      CaseStatuses, caseTypeCategoriesMockData, CaseTypes, civicaseCrmApi,
      currentCaseCategory, customSearchFields, affixOriginalFunction,
      offsetOriginalFunction, originalParentScope, affixReturnValue,
      originalBindToRoute;

    const SEARCH_EVENT_NAME = 'civicase::case-search::filters-updated';

    beforeEach(module('civicase.templates', 'civicase', 'civicase.data', ($provide) => {
      civicaseCrmApi = jasmine.createSpy('civicaseCrmApi');

      $provide.value('civicaseCrmApi', civicaseCrmApi);
      $provide.value('$window', { location: {} });
    }));

    beforeEach(inject((_$controller_, $q, _$rootScope_, _$timeout_, _$window_,
      _CaseFilters_, _CaseStatuses_, _caseTypeCategoriesMockData_,
      _CaseTypesMockData_, _currentCaseCategory_, _CustomSearchField_) => {
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      $scope = $rootScope.$new();
      $timeout = _$timeout_;
      $window = _$window_;
      CaseFilters = _CaseFilters_;
      CaseStatuses = _CaseStatuses_.values;
      CaseTypes = _CaseTypesMockData_.get();
      caseTypeCategoriesMockData = _caseTypeCategoriesMockData_;
      customSearchFields = _CustomSearchField_.getAll();
      currentCaseCategory = _currentCaseCategory_;

      civicaseCrmApi.and.returnValue($q.resolve({ values: [] }));
    }));

    beforeEach(() => {
      affixOriginalFunction = CRM.$.fn.affix;
      offsetOriginalFunction = CRM.$.fn.offset;

      CRM.$.fn.offset = () => ({ top: 100 });

      CRM.$.fn.affix = jasmine.createSpy('affix');
      affixReturnValue = jasmine.createSpyObj('affix', ['on']);
      affixReturnValue.on.and.returnValue(affixReturnValue);
      CRM.$.fn.affix.and.returnValue(affixReturnValue);
      originalBindToRoute = $scope.$bindToRoute;
      $scope.$bindToRoute = jasmine.createSpy('$bindToRoute');
      spyOn($rootScope, '$broadcast').and.callThrough();

      initController();
    });

    afterEach(() => {
      CRM.$.fn.affix = affixOriginalFunction;
      CRM.$.fn.offset = offsetOriginalFunction;
      $scope.$bindToRoute = originalBindToRoute;
    });

    describe('scope variables', () => {
      describe('case type options', () => {
        let expectedOptions;

        beforeEach(() => {
          const selectedCaseTypeCategory = _.find(caseTypeCategoriesMockData, (caseCategory) => {
            return caseCategory.name.toLowerCase() === $scope.filters.case_type_category
              .toLowerCase();
          });
          expectedOptions = _.chain(CaseTypes)
            .filter((caseType) => {
              return caseType.case_type_category === selectedCaseTypeCategory.value;
            })
            .map(getSelect2Options)
            .value();
        });

        it('contains a select2-friendly list of case type options limited by the case category included in the filters object', () => {
          expect($scope.caseTypeOptions).toEqual(expectedOptions);
        });
      });

      describe('case status options', () => {
        let expectedOptions;

        beforeEach(() => {
          expectedOptions = _.map(CaseStatuses, getSelect2Options);
        });

        it('contains a select2-friendly list of case status options', () => {
          expect($scope.caseStatusOptions).toEqual(expectedOptions);
        });
      });

      describe('custom field groups', () => {
        it('contains a list of custom fields group and the fields that can be used to search for their custom data', () => {
          expect($scope.customGroups).toEqual(customSearchFields);
        });
      });

      describe('case relationship options', () => {
        it('contains a list of case relationship options', () => {
          expect($scope.caseRelationshipOptions).toEqual([
            { text: ts('My Cases'), id: 'is_case_manager' },
            { text: ts('Cases I am involved in'), id: 'is_involved' },
            { text: ts('All Cases'), id: 'all' }
          ]);
        });
      });

      describe('check permision service', () => {
        it('provides a reference to the CRM check permission service', () => {
          expect($scope.checkPerm).toEqual(crmCheckPerm);
        });
      });

      describe('filter description', () => {
        it('contains an empty list of descriptions by default', () => {
          expect($scope.filterDescription).toEqual([]);
        });
      });

      describe('filters', () => {
        it('filters by the current case category', () => {
          expect($scope.filters).toEqual({
            case_type_category: currentCaseCategory
          });
        });
      });
    });

    describe('default case filters', () => {
      describe('when no case filters are passed through the URL', () => {
        it('sets the case type category category equal to the current case type category', () => {
          expect($scope.filters).toEqual({
            case_type_category: currentCaseCategory
          });
        });
      });

      describe('when a case filter is passed through the URL', () => {
        beforeEach(() => {
          $scope.$bindToRoute.and.callFake((options) => {
            if (options.param === 'cf') {
              $scope.filters.case_type_category = 'custom-category';
              $scope.$digest();
            }
          });

          initController();
        });

        it('uses the case type category passed from the URL', () => {
          expect($scope.filters.case_type_category).toEqual('custom-category');
        });
      });
    });

    describe('watchers', () => {
      describe('when updating the relationship types', () => {
        describe('when I am the case manager', () => {
          beforeEach(() => {
            $scope.relationshipType = ['is_case_manager'];
            $scope.$digest();
          });

          it('sets the case manager filter equal to my id', () => {
            expect($scope.filters.case_manager).toEqual([CRM.config.user_contact_id]);
          });
        });

        describe('when I am involved in the case', () => {
          beforeEach(() => {
            $scope.relationshipType = ['is_involved'];
            $scope.$digest();
          });

          it('sets the contact id filter equal to my id', function () {
            expect($scope.filters.contact_involved).toEqual([CRM.config.user_contact_id]);
          });

          it('filters by case activities related to the involved contact', () => {
            expect($scope.filters.has_activities_for_involved_contact).toBe(1);
          });
        });
      });

      describe('on init', () => {
        beforeEach(() => {
          $scope.filters = CaseFilters.filter;
        });

        describe('as soon as the component starts', () => {
          it('does not execute the search', () => {
            expect($rootScope.$broadcast).not
              .toHaveBeenCalledWith(SEARCH_EVENT_NAME, jasmine.any(Object));
          });
        });

        describe('after the component starts', () => {
          beforeEach(() => {
            $timeout.flush();
          });

          it('executes the search', () => {
            expect($rootScope.$broadcast)
              .toHaveBeenCalledWith(SEARCH_EVENT_NAME, jasmine.any(Object));
          });
        });
      });

      describe('when show all cases button is presser', () => {
        beforeEach(() => {
          $rootScope.$broadcast(
            'civicase::case-details::clear-filter-and-focus-specific-case', {
              caseId: 10
            }
          );
        });

        it('shows the cases', () => {
          const expectedURL = 'case_type_category=cases#/case/list?' +
            'caseId=10&all_statuses=1&cf=%7B"case_type_category":"cases"%7D';

          expect($window.location.href)
            .toBe(expectedURL);
        });
      });
    });

    describe('checking when the case manager is the logged in user', () => {
      describe('when case manager filter is the logged in user', () => {
        beforeEach(() => {
          $scope.filters.case_manager = [203];
        });

        it('returns true', () => {
          expect($scope.caseManagerIsMe()).toBe(true);
        });
      });

      describe('when the case manager filter is not the logged in user', () => {
        describe('when the case manager id is different', () => {
          beforeEach(() => {
            $scope.filters.case_manager = [201];
          });

          it('returns false', () => {
            expect($scope.caseManagerIsMe()).toBe(false);
          });
        });

        describe('when the case manager id is undefined', () => {
          beforeEach(() => {
            $scope.filters.case_manager = undefined;
          });

          it('returns false', () => {
            expect($scope.caseManagerIsMe()).toBe(false);
          });
        });
      });
    });

    describe('automatically searching when not expanding', () => {
      describe('when the search is not expanded', () => {
        beforeEach(() => {
          $scope.expanded = false;

          $scope.doSearchIfNotExpanded();
          $timeout.flush();
        });

        it('executes the search', () => {
          expect($rootScope.$broadcast)
            .toHaveBeenCalledWith(SEARCH_EVENT_NAME, jasmine.any(Object));
        });
      });

      describe('when the search is expanded', () => {
        beforeEach(() => {
          $scope.expanded = true;
          $scope.doSearchIfNotExpanded();
        });

        it('does not execute the search', () => {
          expect($rootScope.$broadcast)
            .not.toHaveBeenCalledWith(SEARCH_EVENT_NAME, jasmine.any(Object));
        });
      });
    });

    describe('accepting URL values for the relationship type filter', () => {
      describe('when setting the case manager as the logged in user', () => {
        beforeEach(() => {
          $scope.$bindToRoute.and.callFake(() => {
            $scope.filters = {
              case_manager: 'user_contact_id'
            };
          });
          initController();
          $scope.$digest();
        });

        it('sets the relationship type filter equal to "My Cases"', () => {
          expect($scope.relationshipType).toEqual(['is_case_manager']);
        });

        it('sets the case manager filter equal to the current logged in user id', () => {
          expect($scope.filters.case_manager).toEqual([CRM.config.user_contact_id]);
        });
      });

      describe('when setting the contact involved as the logged in user', () => {
        beforeEach(() => {
          $scope.$bindToRoute.and.callFake(() => {
            $scope.filters = {
              contact_involved: 'user_contact_id'
            };
          });
          initController();
          $scope.$digest();
        });

        it('sets the relationship type filter equal to "Cases I am involved"', () => {
          expect($scope.relationshipType).toEqual(['is_involved']);
        });

        it('sets the contact involved filter equal to the current logged in user id', () => {
          expect($scope.filters.contact_involved).toEqual([CRM.config.user_contact_id]);
        });
      });
    });

    describe('handling search submit event', () => {
      beforeEach(() => {
        originalParentScope = $scope.$parent;
        $scope.$parent = {};
      });

      beforeEach(() => {
        $scope.expanded = true;
        $scope.filters.case_manager = [203];
        $scope.handleSearchSubmit();
      });

      afterEach(() => {
        $scope.$parent = originalParentScope;
      });

      it('builds the filter description', () => {
        expect($scope.filterDescription).toEqual([{ label: 'Case Manager', text: 'Me' }]);
      });

      it('closes the dropdown', () => {
        expect($scope.expanded).toBe(false);
      });
    });

    describe('when the search filters are cleared', () => {
      beforeEach(() => {
        $scope.filters = CaseFilters.filter;
        $scope.clearSearch();
      });

      it('clears filters object', () => {
        expect($scope.filters).toEqual({});
      });

      it('executes the search', () => {
        expect($rootScope.$broadcast).toHaveBeenCalledWith(SEARCH_EVENT_NAME, jasmine.any(Object));
      });
    });

    describe('updating the search title', () => {
      const updateTitleEventName = 'civicase::case-search::page-title-updated';

      describe('when a case has been selected to be displayed', () => {
        const caseName = 'Housing Support';

        beforeEach(() => {
          $scope.$emit(updateTitleEventName, caseName);
        });

        it('sets the title equal to the provided case name', () => {
          expect($scope.pageTitle).toEqual(caseName);
        });
      });

      describe('when no cases have been selected', () => {
        describe('when no filters have been applied', () => {
          beforeEach(() => {
            $scope.filters = {};

            $scope.$emit(updateTitleEventName);
          });

          it('displays an "all open cases" title', () => {
            expect($scope.pageTitle).toEqual('All Open  Cases');
          });
        });

        describe('when cases are filtered by case type category', () => {
          beforeEach(() => {
            $scope.filters = {
              case_type_category: '1'
            };

            $scope.$emit(updateTitleEventName);
          });

          it('displays an "all open cases" title', () => {
            expect($scope.pageTitle).toEqual('All Open  Cases');
          });
        });

        describe('when there are filters not used for describing the title', () => {
          beforeEach(() => {
            $scope.filters = [{ case_manager: [1] }];

            $scope.$emit(updateTitleEventName);
          });

          it('displays a "catch all" title for all extra filters', () => {
            expect($scope.pageTitle).toEqual('Case Search Results');
          });
        });

        describe('when the filters can be used to describe the title', () => {
          let expectedTitle;

          describe('when filtering only by case statuses', () => {
            beforeEach(() => {
              expectedTitle = `${CaseStatuses['1'].label} & ${CaseStatuses['2'].label}  Cases`;
              $scope.filters = {
                status_id: [
                  CaseStatuses['1'].value,
                  CaseStatuses['2'].value
                ]
              };

              $scope.$emit(updateTitleEventName);
            });

            it('displays title for the statuses', () => {
              expect($scope.pageTitle).toEqual(expectedTitle);
            });
          });

          describe('when filtering only by case types', () => {
            beforeEach(() => {
              expectedTitle = `All Open ${CaseTypes['1'].title} & ${CaseTypes['2'].title} Cases`;
              $scope.filters = {
                case_type_id: [
                  CaseTypes['1'].name,
                  CaseTypes['2'].name
                ]
              };

              $scope.$emit(updateTitleEventName);
            });

            it('displays a title for the case types', () => {
              expect($scope.pageTitle).toEqual(expectedTitle);
            });
          });

          describe('when filtering by both case statuses and case types', () => {
            beforeEach(() => {
              expectedTitle = `${CaseStatuses['1'].label} & ${CaseStatuses['2'].label}` +
                ` ${CaseTypes['1'].title} & ${CaseTypes['2'].title} Cases`;
              $scope.filters = {
                status_id: [
                  CaseStatuses['1'].value,
                  CaseStatuses['2'].value
                ],
                case_type_id: [
                  CaseTypes['1'].name,
                  CaseTypes['2'].name
                ]
              };

              $scope.$emit(updateTitleEventName);
            });

            it('displays a title for the case statuses and case types', () => {
              expect($scope.pageTitle).toEqual(expectedTitle);
            });
          });
        });

        describe('when a case count is provided', () => {
          const randomCount = _.random(0, 1000);

          beforeEach(() => {
            $scope.$emit(updateTitleEventName, null, randomCount);
          });

          it('adds the count at the end of the title', () => {
            expect($scope.pageTitle).toEqual(`All Open  Cases (${randomCount})`);
          });
        });
      });
    });

    /**
     * Converts the given option object to one that is understood
     * to Select2.
     *
     * @param {object} option the original option object.
     * @returns {object} a select2 option object.
     */
    function getSelect2Options (option) {
      return {
        id: option.value || option.name,
        text: option.label || option.title,
        color: option.color,
        icon: option.icon
      };
    }

    /**
     * Initiate controller
     */
    function initController () {
      $scope.filters = {};
      $scope.searchIsOpentrue = true;
      $scope.applyAdvSearch = function () { };
      $controller('civicaseSearchController', {
        $scope: $scope
      });
    }
  });
})(CRM.$, CRM._, CRM.checkPerm);
