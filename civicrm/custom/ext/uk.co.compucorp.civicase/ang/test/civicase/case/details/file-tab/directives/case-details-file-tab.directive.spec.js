(function (_) {
  describe('civicaseCaseDetailsFileTab', function () {
    var $q, $controller, $rootScope, $scope, civicaseCrmApiMock, activitiesMockData;

    beforeEach(module('civicase', 'civicase.data', ($provide) => {
      civicaseCrmApiMock = jasmine.createSpy('civicaseCrmApi');

      $provide.value('civicaseCrmApi', civicaseCrmApiMock);
    }));

    beforeEach(inject(function (_$controller_, _$rootScope_, _$q_, _activitiesMockData_) {
      $q = _$q_;
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      activitiesMockData = _activitiesMockData_;
    }));

    describe('on init', function () {
      beforeEach(function () {
        civicaseCrmApiMock.and.returnValue($q.resolve({
          xref: { activity: [activitiesMockData.get()[0]] }
        }));
        initController();
      });

      it('shows the placeholders while data is loading', () => {
        expect($scope.isLoading).toEqual(true);
      });

      it('loads the data for the selected case', () => {
        expect($scope.fileFilterParams).toEqual({
          case_id: $scope.item.id,
          text: '',
          options: { xref: 1, limit: 0 }
        });
      });

      describe('after data is loaded', () => {
        beforeEach(function () {
          $scope.$digest();
        });

        it('hides the placeholders', () => {
          expect($scope.isLoading).toEqual(false);
        });

        it('displays the activities with attachments', () => {
          expect($scope.activities).toEqual([activitiesMockData.get()[0]]);
          expect($scope.totalCount).toEqual(1);
        });
      });
    });

    describe('bulk action', () => {
      beforeEach(() => {
        civicaseCrmApiMock.and.returnValue($q.resolve({
          xref: { activity: [activitiesMockData.get()[0]] }
        }));

        initController();
      });

      describe('when "None" bulk action is clicked', () => {
        beforeEach(() => {
          $scope.$emit('civicase::bulk-actions::bulk-selections', 'none');
        });

        it('deselects all activities', () => {
          expect($scope.isSelectAll).toBe(false);
          expect($scope.selectedActivities).toEqual([]);
        });
      });

      describe('when "All" bulk action is clicked', () => {
        beforeEach(() => {
          $scope.$emit('civicase::bulk-actions::bulk-selections', 'all');
        });

        it('selects all activities but does not show checkbox ticked', () => {
          expect($scope.selectedActivities).toEqual([]);
          expect($scope.isSelectAll).toBe(true);
        });
      });
    });

    /**
     * Initialise controller
     */
    function initController () {
      $scope = $rootScope.$new();
      $scope.item = {};

      $controller('civicaseCaseDetailsFileTabController', {
        $scope: $scope
      });
    }
  });
})(CRM._);
