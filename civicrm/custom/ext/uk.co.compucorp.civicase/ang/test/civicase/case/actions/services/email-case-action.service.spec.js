(function (_, $) {
  describe('Email Case Action', function () {
    var $q, $rootScope, EmailCaseAction, CasesMockData, caseObj,
      actualCRMAlert, dialogServiceMock, civicaseCrmApiMock, RelationshipData;

    beforeEach(module('civicase', 'civicase.data'));

    beforeEach(module('civicase', 'civicase.data', ($provide) => {
      dialogServiceMock = jasmine.createSpyObj('dialogService', ['open', 'close']);
      civicaseCrmApiMock = jasmine.createSpy('civicaseCrmApi');

      $provide.value('civicaseCrmApi', civicaseCrmApiMock);
      $provide.value('dialogService', dialogServiceMock);
    }));

    beforeEach(inject(function (_$rootScope_, _EmailCaseAction_, _CasesData_,
      _$q_, _RelationshipData_) {
      $q = _$q_;
      EmailCaseAction = _EmailCaseAction_;
      CasesMockData = _CasesData_;
      $rootScope = _$rootScope_;
      RelationshipData = _RelationshipData_;

      actualCRMAlert = CRM.alert;
      CRM.alert = jasmine.createSpy('CRMAlert');
    }));

    afterEach(function () {
      CRM.alert = actualCRMAlert;
    });

    describe('on click', () => {
      describe('when clicking on the action with one case selected', () => {
        var modalOpenCall, returnValue, expectedModelObject;

        beforeEach(function () {
          caseObj = CasesMockData.get().values[0];
          EmailCaseAction.doAction([caseObj], 'email', jasmine.any(Function))
            .then(function (result) {
              returnValue = result;
            });
          expectedModelObject = {
            caseRoles: [
              { name: 'Client', id: 'client', text: 'Client' },
              { name: 'Homeless Services Coordinator', id: '11', text: 'Homeless Services Coordinator' },
              { name: 'Health Services Coordinator', id: '12', text: 'Health Services Coordinator' },
              { name: 'Benefits Specialist', id: '14', text: 'Benefits Specialist' },
              { name: 'Senior Services Coordinator', id: '16', text: 'Senior Services Coordinator' }
            ],
            caseClientIDs: ['170'],
            selectedCaseRoles: '',
            caseIds: [caseObj.id],
            deferObject: jasmine.any(Object)
          };

          modalOpenCall = dialogServiceMock.open.calls.mostRecent().args;
        });

        it('opens a popup with list of case roles', () => {
          expect(dialogServiceMock.open).toHaveBeenCalledWith('EmailCaseActionRoleSelector',
            '~/civicase/case/actions/directives/email-role-selector.html',
            expectedModelObject,
            {
              autoOpen: false,
              height: '300px',
              width: '40%',
              title: 'Email Case Role(s)',
              buttons: [{
                text: ts('Draft Email'),
                icons: { primary: 'fa-check' },
                click: jasmine.any(Function)
              }]
            }
          );
        });

        describe('when submitting the role selector popup', () => {
          describe('and there are roles present for the selected relationships', () => {
            beforeEach(() => {
              civicaseCrmApiMock.and.returnValue($q.resolve({ values: RelationshipData.get() }));
              modalOpenCall[2].selectedCaseRoles = 'client,11,12';
              modalOpenCall[3].buttons[0].click();

              $rootScope.$digest();
            });

            it('fetches the active relationships', () => {
              expect(civicaseCrmApiMock).toHaveBeenCalledWith('Relationship', 'get', {
                sequential: 1,
                is_active: 1,
                case_id: { IN: ['141'] },
                relationship_type_id: { IN: ['11', '12'] },
                options: { limit: 0 }
              });
            });

            it('returns the path to open the send email popup and hides the draft button', () => {
              expect(returnValue).toEqual({
                path: 'civicrm/activity/email/add',
                query: {
                  action: 'add',
                  reset: 1,
                  cid: '170,4,6',
                  hideDraftButton: 1,
                  caseid: '141',
                  caseRolesBulkEmail: 1
                }
              });
            });
          });

          describe('and there are no roles present for the selected relationships', () => {
            beforeEach(() => {
              civicaseCrmApiMock.and.returnValue($q.resolve({ values: [] }));
              modalOpenCall[2].selectedCaseRoles = '11,12';
              modalOpenCall[3].buttons[0].click();

              $rootScope.$digest();
            });

            it('shows an error message', () => {
              expect(returnValue).toBeUndefined();
              expect(CRM.alert).toHaveBeenCalledWith(
                'Please add a contact for the selected role(s).',
                'No contacts available for selected role(s)',
                'error'
              );
            });
          });

          describe('and there are no roles selected on the popup', () => {
            beforeEach(() => {
              CRM.alert.calls.reset();
              modalOpenCall[2].selectedCaseRoles = '';
              modalOpenCall[3].buttons[0].click();

              $rootScope.$digest();
            });

            it('shows an error message', () => {
              expect(CRM.alert).toHaveBeenCalledWith(
                'Select case role(s).',
                'No case roles are selected',
                'error'
              );
            });
          });
        });
      });
      describe('when clicking on the action with more than one case selected', () => {
        var modalOpenCall, returnValue;

        beforeEach(function () {
          caseObj = CasesMockData.get().values[0];
          var case2Obj = CasesMockData.get().values[1];
          EmailCaseAction.doAction([caseObj, case2Obj], 'email', jasmine.any(Function))
            .then(function (result) {
              returnValue = result;
            });
          modalOpenCall = dialogServiceMock.open.calls.mostRecent().args;
          civicaseCrmApiMock.and.returnValue($q.resolve({ values: RelationshipData.get() }));
          modalOpenCall[2].selectedCaseRoles = 'client,11,12';
          modalOpenCall[3].buttons[0].click();
          $rootScope.$digest();
        });

        it('adds bulk email activitiy to all the selected cases', () => {
          expect(returnValue).toEqual({
            path: 'civicrm/activity/email/add',
            query: {
              action: 'add',
              reset: 1,
              cid: '170,4,6',
              hideDraftButton: 1,
              caseid: '141',
              allCaseIds: '141,3',
              caseRolesBulkEmail: 1
            }
          });
        });
      });
    });

    describe('visibility of the action', () => {
      var caseObj, isVisible;

      describe('when inside the bulk action', () => {
        beforeEach(function () {
          caseObj = CasesMockData.get().values[0];
          isVisible = EmailCaseAction.isActionAllowed('email', [caseObj], { mode: 'case-bulk-actions' });
        });

        it('shows the action', () => {
          expect(isVisible).toBe(true);
        });
      });

      describe('when outside the bulk block', () => {
        beforeEach(function () {
          caseObj = CasesMockData.get().values[0];
          isVisible = EmailCaseAction.isActionAllowed('email', [caseObj], { mode: 'not-case-bulk-actions' });
        });

        it('hides the action', () => {
          expect(isVisible).toBe(false);
        });
      });
    });
  });
})(CRM._, CRM.$);
