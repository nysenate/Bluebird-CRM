/* eslint-env jasmine */

((_, $) => {
  describe('DownloadAllActivityAction', () => {
    var DownloadAllActivityAction, $window;
    var $scope = {};

    beforeEach(module('civicase', ($provide) => {
      $provide.value('$window', jasmine.createSpyObj('$window', ['open']));
    }));

    beforeEach(inject((_DownloadAllActivityAction_, _$window_) => {
      DownloadAllActivityAction = _DownloadAllActivityAction_;
      $window = _$window_;
    }));

    describe('visibility of action', () => {
      describe('when used inside activity feed and activity is of type File Upload', () => {
        beforeEach(() => {
          $scope.mode = 'case-activity-feed';
          $scope.selectedActivities = [{ type: 'File Upload' }];
        });

        it('enables the action', () => {
          expect(DownloadAllActivityAction.isActionEnabled($scope)).toBe(true);
        });
      });

      describe('when used outside activity feed and activity is of type File Upload', () => {
        beforeEach(() => {
          $scope.mode = 'something else';
          $scope.selectedActivities = [{ type: 'File Upload' }];
        });

        it('disables the action', () => {
          expect(DownloadAllActivityAction.isActionEnabled($scope)).toBe(false);
        });
      });

      describe('when used inside activity feed and activity is not of type File Upload', () => {
        beforeEach(() => {
          $scope.mode = 'case-activity-feed';
          $scope.selectedActivities = [{ type: 'Not File Upload' }];
        });

        it('disables the action', () => {
          expect(DownloadAllActivityAction.isActionEnabled($scope)).toBe(false);
        });
      });

      describe('when used outside activity feed and activity is not of type File Upload', () => {
        beforeEach(() => {
          $scope.mode = 'something else';
          $scope.selectedActivities = [{ type: 'Not File Upload' }];
        });

        it('disables the action', () => {
          expect(DownloadAllActivityAction.isActionEnabled($scope)).toBe(false);
        });
      });
    });

    describe('when action is clicked', () => {
      beforeEach(() => {
        $scope.selectedActivities = [{ id: '1' }];
        CRM.url.and.returnValue('CRM Mock URL');

        DownloadAllActivityAction.doAction($scope);
      });

      it('downloads the file', () => {
        expect(CRM.url).toHaveBeenCalledWith('civicrm/case/activity/download-all-files', {
          activity_id: '1'
        });
        expect($window.open).toHaveBeenCalledWith('CRM Mock URL', '_blank');
      });
    });
  });
})(CRM._, CRM.$);
