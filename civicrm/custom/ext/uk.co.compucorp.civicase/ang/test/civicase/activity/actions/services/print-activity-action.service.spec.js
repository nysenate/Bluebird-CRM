((_, $) => {
  describe('PrintReportActivityAction', () => {
    var PrintReportActivityAction, $window;
    var $scope = {};

    beforeEach(module('civicase', ($provide) => {
      var $windowMock = jasmine.createSpyObj('$window', ['open']);
      $windowMock.open.and.returnValue(jasmine.createSpyObj('open', ['focus']));

      $provide.value('$window', $windowMock);
    }));

    beforeEach(inject((_PrintReportActivityAction_, _$window_) => {
      PrintReportActivityAction = _PrintReportActivityAction_;
      $window = _$window_;
    }));

    describe('visibility of action', () => {
      describe('when used inside bulk action and is inside case summary page', () => {
        beforeEach(() => {
          $scope.mode = 'case-activity-bulk-action';
          $scope.isCaseSummaryPage = true;
        });

        it('enables the action', () => {
          expect(PrintReportActivityAction.isActionEnabled($scope)).toBe(true);
        });
      });

      describe('when used not inside bulk action and is inside case summary page', () => {
        beforeEach(() => {
          $scope.mode = 'not case-activity-bulk-action';
          $scope.isCaseSummaryPage = true;
        });

        it('disables the action', () => {
          expect(PrintReportActivityAction.isActionEnabled($scope)).toBe(false);
        });
      });

      describe('when used inside bulk action and is not inside case summary page', () => {
        beforeEach(() => {
          $scope.mode = 'case-activity-bulk-action';
          $scope.isCaseSummaryPage = false;
        });

        it('disables the action', () => {
          expect(PrintReportActivityAction.isActionEnabled($scope)).toBe(false);
        });
      });
    });

    describe('when action is clicked', () => {
      beforeEach(() => {
        $scope.getPrintActivityUrl = jasmine.createSpy();
        $scope.getPrintActivityUrl.and.returnValue('mock url');

        PrintReportActivityAction.doAction($scope);
      });

      it('opens a new tab with printable content', () => {
        expect($scope.getPrintActivityUrl).toHaveBeenCalledWith($scope.selectedActivities);
        expect($window.open).toHaveBeenCalledWith('mock url', '_blank');
      });
    });
  });
})(CRM._, CRM.$);
