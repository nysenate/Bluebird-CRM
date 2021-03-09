/* eslint-env jasmine */

(function (_, $) {
  describe('civicaseDashboardController', function () {
    var $controller, $rootScope, $scope, DashboardActionItems;

    beforeEach(module('civicase', 'civicase.data'));

    beforeEach(inject(function (_$controller_, _$rootScope_, _DashboardActionItems_) {
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      DashboardActionItems = _DashboardActionItems_;

      $scope = $rootScope.$new();
      $scope.$bindToRoute = jasmine.createSpy('$bindToRoute');
    }));

    describe('on init', () => {
      beforeEach(() => {
        initController();
      });

      it('stores the dashboard action items', () => {
        expect($scope.actionBarItems).toBe(DashboardActionItems);
      });
    });

    describe('filter', function () {
      describe('when user has permission to view all cases', function () {
        beforeEach(function () {
          CRM.checkPerm.and.returnValue(true);
          initController();
        });

        it('shows the `All Cases` filter option', function () {
          expect($scope.caseRelationshipOptions).toEqual([
            { text: 'My Cases', id: 'is_case_manager' },
            { text: 'Cases I am involved in', id: 'is_involved' },
            { text: 'All Cases', id: 'all' }
          ]);
        });
      });

      describe('when user does not have permission to view all cases', function () {
        beforeEach(function () {
          CRM.checkPerm.and.returnValue(false);
          initController();
        });

        it('does not show the `All Cases` filter option', function () {
          expect($scope.caseRelationshipOptions).toEqual([
            { text: 'My Cases', id: 'is_case_manager' },
            { text: 'Cases I am involved in', id: 'is_involved' }
          ]);
        });
      });
    });

    describe('caseRelationshipType watcher', function () {
      describe('when `My Cases` filter is applied', function () {
        beforeEach(function () {
          initController();
          $scope.filters.caseRelationshipType = 'is_case_manager';
          $scope.$digest();
        });

        it('filters the cases and activties where the user is the manager', function () {
          expect($scope.activityFilters.case_filter).toEqual(jasmine.objectContaining({
            case_manager: CRM.config.user_contact_id
          }));
        });
      });

      describe('when `Cases I am Involved` filter is applied', function () {
        beforeEach(function () {
          initController();
          $scope.filters.caseRelationshipType = 'is_involved';
          $scope.$digest();
        });

        it('filters the cases and activties where the user is involved', function () {
          expect($scope.activityFilters.case_filter).toEqual(jasmine.objectContaining({
            contact_involved: { IN: [CRM.config.user_contact_id] }
          }));
        });

        it('filters by case activities related to the involved contact', () => {
          expect($scope.activityFilters.case_filter.has_activities_for_involved_contact)
            .toBe(1);
        });
      });
    });

    describe('link to manage screen page', () => {
      beforeEach(() => {
        initController();
        $scope.caseTypeCategoryName = 'cases';
      });

      describe('when case type and case status is sent ', () => {
        let returnedLink;

        beforeEach(() => {
          returnedLink = $scope.linkToManageCase('type', 'status');
        });

        it('returns the url to manage cases page with case type and case status preselected', () => {
          expect(returnedLink)
            .toBe('#/case/list?' + $.param({
              cf: JSON.stringify({
                case_type_category: 'cases',
                case_type_id: ['type'],
                status_id: ['status']
              })
            }));
        });
      });

      describe('when "my cases" filter is selected ', () => {
        let returnedLink;

        beforeEach(() => {
          $scope.filters.caseRelationshipType = 'is_case_manager';
          returnedLink = $scope.linkToManageCase('type', 'status');
        });

        it('returns the url to manage cases page with "my cases" filter selected', () => {
          expect(returnedLink)
            .toBe('#/case/list?' + $.param({
              cf: JSON.stringify({
                case_type_category: 'cases',
                case_type_id: ['type'],
                status_id: ['status'],
                case_manager: [CRM.config.user_contact_id]
              })
            }));
        });
      });

      describe('when "cases I am involved in" filter is selected ', () => {
        let returnedLink;

        beforeEach(() => {
          $scope.filters.caseRelationshipType = 'is_involved';
          returnedLink = $scope.linkToManageCase('type', 'status');
        });

        it('returns the url to manage cases page with "cases I am involved in" filter selected', () => {
          expect(returnedLink)
            .toBe('#/case/list?' + $.param({
              cf: JSON.stringify({
                case_type_category: 'cases',
                case_type_id: ['type'],
                status_id: ['status'],
                contact_involved: [CRM.config.user_contact_id]
              })
            }));
        });
      });
    });

    /**
     * Initializes the dashboard controller.
     */
    function initController () {
      $controller('civicaseDashboardController', {
        $scope: $scope
      });
    }
  });
})(CRM._, CRM.$);
