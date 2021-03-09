/* eslint-env jasmine */

(() => {
  describe('DeleteActivityAction', () => {
    var DeleteActivityAction, $provide;
    var $scope = {};

    beforeEach(module('civicase', (_$provide_) => {
      $provide = _$provide_;
    }));

    beforeEach(inject((_DeleteActivityAction_) => {
      DeleteActivityAction = _DeleteActivityAction_;
    }));

    describe('visibility of action', () => {
      describe('when selected activities does not specific visibility logic', () => {
        beforeEach(() => {
          $scope.selectedActivities = [{ type: 'File Upload' }, { type: 'Follow Up' }];
        });

        it('enables the action', () => {
          expect(DeleteActivityAction.isActionEnabled($scope)).toBe(true);
        });
      });

      describe('when all the selected activities supports delete action', () => {
        beforeEach(() => {
          setupMockModules({ fileUploadVisible: true, followUpVisible: true });
          $scope.selectedActivities = [{ type: 'File Upload' }, { type: 'Follow Up' }];
        });

        it('enables the action', () => {
          expect(DeleteActivityAction.isActionEnabled($scope)).toBe(true);
        });
      });

      describe('when only one of the selected activities supports delete action', () => {
        beforeEach(() => {
          setupMockModules({ fileUploadVisible: true, followUpVisible: false });
          $scope.selectedActivities = [{ type: 'File Upload' }, { type: 'Follow Up' }];
        });

        it('disables the action', () => {
          expect(DeleteActivityAction.isActionEnabled($scope)).toBe(false);
        });
      });

      describe('when none of the selected activities supports delete action', () => {
        beforeEach(() => {
          setupMockModules({ fileUploadVisible: false, followUpVisible: false });
          $scope.selectedActivities = [{ type: 'File Upload' }, { type: 'Follow Up' }];
        });

        it('disables the action', () => {
          expect(DeleteActivityAction.isActionEnabled($scope)).toBe(false);
        });
      });
    });

    /**
     * @param {object} params parameters
     */
    function setupMockModules (params) {
      $provide.service('FileUploadActivityStatus', function () {
        this.isDeleteVisible = function () {
          return params.fileUploadVisible;
        };
      });

      $provide.service('FollowUpActivityStatus', function () {
        this.isDeleteVisible = function () {
          return params.followUpVisible;
        };
      });
    }
  });
})();
