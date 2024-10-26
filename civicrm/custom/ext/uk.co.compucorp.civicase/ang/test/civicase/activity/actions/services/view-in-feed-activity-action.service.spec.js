((_, $) => {
  describe('ViewInFeedActivityAction', () => {
    var ViewInFeedActivityAction, $window, getActivityFeedUrl;
    var $scope = {};

    beforeEach(module('civicase', ($provide) => {
      var getActivityFeedUrlMock = jasmine.createSpy();
      getActivityFeedUrlMock.and.returnValue('mock url');

      $provide.value('getActivityFeedUrl', getActivityFeedUrlMock);
      $provide.value('$window', { location: {} });
    }));

    beforeEach(inject((_ViewInFeedActivityAction_, _getActivityFeedUrl_, _$window_) => {
      ViewInFeedActivityAction = _ViewInFeedActivityAction_;
      getActivityFeedUrl = _getActivityFeedUrl_;
      $window = _$window_;
    }));

    describe('visibility of action', () => {
      describe('when inside case summary page', () => {
        beforeEach(() => {
          $scope.mode = 'case-summary';
        });

        it('enables the action', () => {
          expect(ViewInFeedActivityAction.isActionEnabled($scope)).toBe(true);
        });
      });

      describe('when outside case summary page', () => {
        beforeEach(() => {
          $scope.mode = 'not case-summary';
        });

        it('disables the action', () => {
          expect(ViewInFeedActivityAction.isActionEnabled($scope)).toBe(false);
        });
      });
    });

    describe('when action is clicked', () => {
      beforeEach(() => {
        $scope.selectedActivities = [{
          id: 1,
          case_id: 2
        }];
        ViewInFeedActivityAction.doAction($scope);
      });

      it('shows the activity details in feed', () => {
        expect(getActivityFeedUrl).toHaveBeenCalledWith({
          caseId: 2,
          activityId: 1
        });
        expect($window.location.href).toBe('mock url');
      });
    });
  });
})(CRM._, CRM.$);
