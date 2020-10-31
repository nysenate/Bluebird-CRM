/* eslint-env jasmine */

((_, $) => {
  describe('ViewActivityAction', () => {
    var ViewActivityAction;
    var $scope = {};

    beforeEach(module('civicase'));

    beforeEach(inject((_ViewActivityAction_) => {
      ViewActivityAction = _ViewActivityAction_;
    }));

    describe('visibility of action', () => {
      describe('when inside bulk action', () => {
        beforeEach(() => {
          $scope.mode = 'case-activity-bulk-action';
          $scope.selectedActivities = [{
            activity_type_id: 22,
            status_name: 'Draft'
          }];
        });

        it('disables the action', () => {
          expect(ViewActivityAction.isActionEnabled($scope)).toBeFalsy();
        });
      });

      describe('when used outside bulk action', () => {
        beforeEach(() => {
          $scope.mode = 'not case-activity-bulk-action';
        });

        describe('activity is not from communication category and status is not draft', () => {
          beforeEach(() => {
            $scope.selectedActivities = [{
              activity_type_id: 23,
              status_name: 'not Draft'
            }];
          });

          it('enables the action', () => {
            expect(ViewActivityAction.isActionEnabled($scope)).toBe(true);
          });
        });

        describe('activity is not from communication category and status is draft', () => {
          beforeEach(() => {
            $scope.selectedActivities = [{
              activity_type_id: 23,
              status_name: 'Draft'
            }];
          });

          it('enables the action', () => {
            expect(ViewActivityAction.isActionEnabled($scope)).toBe(true);
          });
        });

        describe('activity is from communication category and status is draft', () => {
          beforeEach(() => {
            $scope.selectedActivities = [{
              activity_type_id: 22,
              status_name: 'Draft'
            }];
          });

          it('disables the action', () => {
            expect(ViewActivityAction.isActionEnabled($scope)).toBe(false);
          });
        });
      });
    });
  });
})(CRM._, CRM.$);
