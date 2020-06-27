/* eslint-env jasmine */

((_, $) => {
  describe('EditActivityAction', () => {
    var EditActivityAction;
    var $scope = {};

    beforeEach(module('civicase'));

    beforeEach(inject((_EditActivityAction_) => {
      EditActivityAction = _EditActivityAction_;
    }));

    describe('visibility of action', () => {
      describe('when used outside of case summary', () => {
        beforeEach(() => {
          $scope.mode = 'not case-summary';
        });

        it('disables the action', () => {
          expect(EditActivityAction.isActionEnabled($scope)).toBeFalsy();
        });
      });

      describe('when used inside case summary', () => {
        beforeEach(() => {
          $scope.mode = 'case-summary';
          $scope.getEditActivityUrl = () => {};
        });

        describe('and selected activity type is Email', () => {
          beforeEach(() => {
            $scope.selectedActivities = [{ activity_type_id: '3' }];
          });

          it('disables the action', () => {
            expect(EditActivityAction.isActionEnabled($scope)).toBe(false);
          });
        });

        describe('and selected activity type is PDF', () => {
          beforeEach(() => {
            $scope.selectedActivities = [{ activity_type_id: '22' }];
          });

          it('disables the action', () => {
            expect(EditActivityAction.isActionEnabled($scope)).toBe(false);
          });
        });

        describe('and selected activity type is not PDF or email', () => {
          beforeEach(() => {
            $scope.selectedActivities = [{ activity_type_id: '2' }];
          });

          it('enables the action', () => {
            expect(EditActivityAction.isActionEnabled($scope)).toBe(true);
          });
        });
      });
    });

    describe('when action is clicked', () => {
      beforeEach(() => {
        $scope.selectedActivities = [{ id: '2' }];
        $scope.getEditActivityUrl = jasmine.createSpy();
        $scope.getEditActivityUrl.and.returnValue('getEditActivityUrl return value');

        EditActivityAction.doAction($scope);
      });

      it('open a popup to edit the form', () => {
        expect($scope.getEditActivityUrl).toHaveBeenCalledWith('2');
        expect(CRM.loadForm).toHaveBeenCalledWith('getEditActivityUrl return value');
      });
    });
  });
})(CRM._, CRM.$);
