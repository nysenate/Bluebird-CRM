((_, $) => {
  describe('DownloadAllActivityAction', () => {
    var DownloadAllActivityAction, $window, civicaseCrmUrl;
    var $scope = {};

    beforeEach(module('civicase', ($provide) => {
      $provide.value('$window', jasmine.createSpyObj('$window', ['open']));
    }));

    beforeEach(inject((_civicaseCrmUrl_, _DownloadAllActivityAction_, _$window_) => {
      DownloadAllActivityAction = _DownloadAllActivityAction_;
      $window = _$window_;
      civicaseCrmUrl = _civicaseCrmUrl_;
    }));

    describe('visibility of action', () => {
      describe('when used inside activity feed menu and activity is of type File Upload', () => {
        beforeEach(() => {
          $scope.mode = 'case-activity-feed-menu';
          $scope.selectedActivities = [{ type: 'File Upload' }];
          $scope.isSelectAll = false;
        });

        it('enables the action', () => {
          expect(DownloadAllActivityAction.isActionEnabled($scope)).toBe(true);
        });
      });

      describe('when used inside activity feed bulk action and all activities are of type File Upload', () => {
        beforeEach(() => {
          $scope.mode = 'case-activity-bulk-action';
          $scope.selectedActivities = [{ type: 'File Upload' }];
          $scope.isSelectAll = false;
        });

        it('enables the action', () => {
          expect(DownloadAllActivityAction.isActionEnabled($scope)).toBe(true);
        });
      });

      describe('when used inside activity feed bulk action and select all is true', () => {
        beforeEach(() => {
          $scope.mode = 'case-activity-bulk-action';
          $scope.selectedActivities = [{ type: 'File Upload' }];
          $scope.isSelectAll = true;
        });

        it('disables the action', () => {
          expect(DownloadAllActivityAction.isActionEnabled($scope)).toBe(false);
        });
      });

      describe('when used inside activity feed bulk action and one of the selected activiy is not File Upload type', () => {
        beforeEach(() => {
          $scope.mode = 'case-activity-bulk-action';
          $scope.selectedActivities = [{ type: 'Something Else' }];
          $scope.isSelectAll = false;
        });

        it('disables the action', () => {
          expect(DownloadAllActivityAction.isActionEnabled($scope)).toBe(false);
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
          $scope.mode = 'case-activity-feed-menu';
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

      describe('when used outside files tab', () => {
        beforeEach(() => {
          $scope.mode = 'case-files-activity-bulk-action';
        });

        it('enables the action', () => {
          expect(DownloadAllActivityAction.isActionEnabled($scope)).toBe(true);
        });
      });
    });

    describe('when action is clicked', () => {
      beforeEach(() => {
        civicaseCrmUrl.and.returnValue('CRM Mock URL');
      });

      describe('when used inside the activity feed', () => {
        beforeEach(() => {
          $scope.mode = 'case-activity-feed-menu';
          $scope.selectedActivities = [{ id: '1' }];
          DownloadAllActivityAction.doAction($scope);
        });

        it('downloads all the files for the sent activity', () => {
          expect(civicaseCrmUrl).toHaveBeenCalledWith('civicrm/case/activity/download-all-files', {
            activity_ids: ['1']
          });
          expect($window.open).toHaveBeenCalledWith('CRM Mock URL', '_blank');
        });
      });

      describe('when used inside the case files tab', () => {
        describe('when select all is turned off', () => {
          beforeEach(() => {
            $scope.mode = 'case-files-activity-bulk-action';
            $scope.isSelectAll = false;
            $scope.selectedActivities = [{ id: '1' }, { id: '2' }];
            DownloadAllActivityAction.doAction($scope);
          });

          it('downloads all the files for all the selected activities', () => {
            expect(civicaseCrmUrl).toHaveBeenCalledWith('civicrm/case/activity/download-all-files', {
              activity_ids: ['1', '2']
            });
            expect($window.open).toHaveBeenCalledWith('CRM Mock URL', '_blank');
          });
        });

        describe('when select all is turned on', () => {
          beforeEach(() => {
            $scope.mode = 'case-files-activity-bulk-action';
            $scope.isSelectAll = true;
            $scope.params = { key: 'value' };
            DownloadAllActivityAction.doAction($scope);
          });

          it('downloads all the files for all the activities matching the search parameters', () => {
            expect(civicaseCrmUrl).toHaveBeenCalledWith('civicrm/case/activity/download-all-files', {
              searchParams: { key: 'value' }
            });
            expect($window.open).toHaveBeenCalledWith('CRM Mock URL', '_blank');
          });
        });
      });
    });
  });
})(CRM._, CRM.$);
