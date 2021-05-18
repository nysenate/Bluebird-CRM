((_, $) => {
  describe('ResumeDraftActivityAction', () => {
    var ResumeDraftActivityAction, viewInPopup;
    var $scope = {};

    beforeEach(module('civicase', ($provide) => {
      var viewInPopupMock = jasmine.createSpy();
      viewInPopupMock.and.returnValue(jasmine.createSpyObj('viewInPopup', ['on']));

      $provide.value('viewInPopup', viewInPopupMock);
    }));

    beforeEach(inject((_ResumeDraftActivityAction_, _viewInPopup_) => {
      ResumeDraftActivityAction = _ResumeDraftActivityAction_;
      viewInPopup = _viewInPopup_;
    }));

    describe('visibility of action', () => {
      describe('when inside bulk action', () => {
        beforeEach(() => {
          $scope.mode = 'case-activity-bulk-action';
        });

        it('disables the action', () => {
          expect(ResumeDraftActivityAction.isActionEnabled($scope)).toBeFalsy();
        });
      });

      describe('when inside files tab bulk action', () => {
        beforeEach(() => {
          $scope.mode = 'case-files-activity-bulk-action';
        });

        it('disables the action', () => {
          expect(ResumeDraftActivityAction.isActionEnabled($scope)).toBeFalsy();
        });
      });

      describe('when used else where', () => {
        beforeEach(() => {
          $scope.mode = 'not case-activity-bulk-action';
        });

        describe('activity is from communication category and status is draft', () => {
          beforeEach(() => {
            $scope.selectedActivities = [{
              activity_type_id: 22,
              status_name: 'Draft'
            }];
          });

          it('enables the action', () => {
            expect(ResumeDraftActivityAction.isActionEnabled($scope)).toBe(true);
          });
        });

        describe('activity is not from communication category and status is draft', () => {
          beforeEach(() => {
            $scope.selectedActivities = [{
              activity_type_id: 23,
              status_name: 'Draft'
            }];
          });

          it('disables the action', () => {
            expect(ResumeDraftActivityAction.isActionEnabled($scope)).toBe(false);
          });
        });

        describe('activity is from communication category and status is not draft', () => {
          beforeEach(() => {
            $scope.selectedActivities = [{
              activity_type_id: 22,
              status_name: 'not Draft'
            }];
          });

          it('disables the action', () => {
            expect(ResumeDraftActivityAction.isActionEnabled($scope)).toBe(false);
          });
        });

        describe('activity is not from communication category and status is not draft', () => {
          beforeEach(() => {
            $scope.selectedActivities = [{
              activity_type_id: 23,
              status_name: 'not Draft'
            }];
          });

          it('disables the action', () => {
            expect(ResumeDraftActivityAction.isActionEnabled($scope)).toBe(false);
          });
        });
      });
    });

    describe('when action is clicked', () => {
      beforeEach(() => {
        $scope.selectedActivities = [];
        ResumeDraftActivityAction.doAction($scope);
      });

      it('opens a new tab with printable content', () => {
        expect(viewInPopup).toHaveBeenCalledWith(null, $scope.selectedActivities[0]);
      });
    });
  });
})(CRM._, CRM.$);
