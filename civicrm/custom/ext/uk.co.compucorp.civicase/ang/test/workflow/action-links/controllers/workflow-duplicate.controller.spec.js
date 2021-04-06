((_) => {
  describe('workflow duplicate controller', () => {
    let $controller, $rootScope, $q, $scope, dialogService, CaseTypesMockData,
      crmStatus, CaseManagementWorkflow;

    beforeEach(module('workflow.mock', 'workflow', 'civicase.data', ($provide, $qProvider) => {
      $qProvider.errorOnUnhandledRejections(false);
      $provide.value('dialogService',
        jasmine.createSpyObj('dialogService', ['open', 'close'])
      );
    }));

    beforeEach(inject((_$q_, _$controller_, _$rootScope_, _CaseTypesMockData_,
      _dialogService_, _crmStatus_, _CaseManagementWorkflow_) => {
      $q = _$q_;
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      dialogService = _dialogService_;
      CaseTypesMockData = _CaseTypesMockData_;
      crmStatus = _crmStatus_;
      CaseManagementWorkflow = _CaseManagementWorkflow_;

      dialogService.dialogs = {};

      spyOn(CaseManagementWorkflow, 'createDuplicate');
      CaseManagementWorkflow.createDuplicate.and.returnValue($q.resolve({
        values: CaseTypesMockData.getSequential()
      }));

      spyOn($rootScope, '$broadcast').and.callThrough();
    }));

    describe('initially', () => {
      beforeEach(() => {
        initController();
      });

      it('submit button is not disabled', () => {
        expect($scope.submitInProgress).toBe(false);
      });
    });

    describe('when submit button is clicked', () => {
      describe('if a popup is not already open', () => {
        var workflow, modelObject, expectedWorkflowObject, saveButtonClickFunction;

        beforeEach(() => {
          initController();

          workflow = CaseTypesMockData.getSequential()[0];
          $scope.clickHandler(workflow);

          expectedWorkflowObject = _.clone(workflow);
          expectedWorkflowObject.title = '';
          expectedWorkflowObject.name = '';

          modelObject = dialogService.open.calls.mostRecent().args[2];
          saveButtonClickFunction =
            dialogService.open.calls.mostRecent().args[3].buttons[0].click;

          modelObject.workflow_duplicate_form = { $valid: true };
        });

        it('opens a popup for user to enter details of duplicate workflow', () => {
          expect(dialogService.open).toHaveBeenCalledWith(
            'WorkflowDuplicate',
            '~/workflow/action-links/directives/workflow-list-duplicate-popup.html',
            modelObject,
            {
              autoOpen: false,
              height: 'auto',
              width: '40%',
              title: 'Duplicate Workflow',
              buttons: [{
                text: ts('Save'),
                icons: { primary: 'fa-check' },
                click: jasmine.any(Function)
              }]
            }
          );
        });

        describe('when saving the save button in duplicate popup', () => {
          var expectedApiParam;

          describe('when there is no error while saving', () => {
            beforeEach(() => {
              modelObject.workflow.title = 'New Workflow';
              saveButtonClickFunction();

              expectedApiParam = _.extend(_.clone(workflow), {
                title: 'New Workflow',
                name: 'new_workflow',
                sequential: true
              });
            });

            it('initially disables the submit button', () => {
              expect($scope.submitInProgress).toBe(true);
            });

            it('duplicates the workflow', () => {
              expect(CaseManagementWorkflow.createDuplicate)
                .toHaveBeenCalledWith(expectedApiParam);
            });

            it('shows saving notification while save is in progress', () => {
              expect(crmStatus).toHaveBeenCalledWith({
                start: 'Duplicating Workflow...',
                success: 'Duplicate created successfully'
              }, jasmine.any(Object));
            });

            describe('after the data is saved', function () {
              beforeEach(() => {
                $scope.$digest();
              });

              it('refreshes the worklfow list', () => {
                expect($rootScope.$broadcast)
                  .toHaveBeenCalledWith('workflow::list::refresh');
              });

              it('closes the popup', () => {
                expect(dialogService.close)
                  .toHaveBeenCalledWith('WorkflowDuplicate');
              });

              it('enables the submit button', () => {
                expect($scope.submitInProgress).toBe(false);
              });
            });
          });

          describe('when there is error while saving', () => {
            beforeEach(() => {
              CaseManagementWorkflow.createDuplicate.and.returnValue($q.reject({
                error_code: 'already exists'
              }));
              saveButtonClickFunction();
              $scope.$digest();
            });

            it('does not close the popup', () => {
              expect(dialogService.close)
                .not.toHaveBeenCalled();
            });

            it('enables the submit button', () => {
              expect($scope.submitInProgress).toBe(false);
            });
          });
        });

        describe('when the form has validation errors', () => {
          beforeEach(() => {
            modelObject.workflow_duplicate_form = { $valid: false };
            saveButtonClickFunction();
          });

          it('does not call the api to save the workflow', () => {
            expect(CaseManagementWorkflow.createDuplicate).not.toHaveBeenCalled();
          });
        });
      });

      describe('if a popup is already open', () => {
        beforeEach(() => {
          initController();

          dialogService.dialogs.WorkflowDuplicate = jasmine.any(Object);

          var workflow = CaseTypesMockData.getSequential()[0];
          $scope.clickHandler(workflow);
        });

        it('does not open a duplicate popup', () => {
          expect(dialogService.open).not.toHaveBeenCalled();
        });
      });
    });

    /**
     * Initializes the workflow duplicate controller.
     */
    function initController () {
      $scope = $rootScope.$new();

      $controller('WorkflowDuplicateController', { $scope: $scope });
    }
  });
})(CRM._);
