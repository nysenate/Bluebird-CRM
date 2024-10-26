(function (_, $) {
  describe('case actions', function () {
    let $q, element, $provide, $compile, $rootScope, CaseActionsData,
      PrintCaseAction, loadFormOnListener, crmFormSuccessFunction,
      dialogCloseFunction, originalTriggerFunction, civicaseCrmLoadForm,
      civicaseCrmUrl;

    beforeEach(module('civicase', 'civicase.data', 'civicase.templates', (_$provide_) => {
      $provide = _$provide_;

      $provide.service('PrintCaseAction', function () {
        this.doAction = jasmine.createSpy('doAction');
        this.refreshData = jasmine.createSpy('refreshData');
        this.isActionAllowed = jasmine.createSpy('isActionAllowed');
        this.isActionAllowed.and.returnValue(true);
      });

      var civicaseCrmLoadFormSpy = jasmine.createSpy('loadForm');
      loadFormOnListener = jasmine.createSpyObj('', ['on']);
      loadFormOnListener.on.and.callFake(function () {
        if (arguments[0] === 'crmFormSuccess crmPopupFormSuccess') {
          crmFormSuccessFunction = arguments[1];
        } else if (arguments[0] === 'dialogclose.crmPopup') {
          dialogCloseFunction = arguments[1];
        }

        return loadFormOnListener;
      });
      civicaseCrmLoadFormSpy.and.returnValue(loadFormOnListener);
      $provide.service('civicaseCrmLoadForm', function () {
        return civicaseCrmLoadFormSpy;
      });
    }));

    beforeEach(inject(function (_$q_, _$compile_, _$rootScope_, _CaseActionsData_,
      _civicaseCrmUrl_, _PrintCaseAction_, _civicaseCrmLoadForm_) {
      $q = _$q_;
      civicaseCrmUrl = _civicaseCrmUrl_;
      $compile = _$compile_;
      $rootScope = _$rootScope_;
      CaseActionsData = _CaseActionsData_;
      PrintCaseAction = _PrintCaseAction_;
      civicaseCrmLoadForm = _civicaseCrmLoadForm_;

      spyOn($rootScope, '$broadcast').and.callThrough();

      originalTriggerFunction = $.fn.trigger;
      spyOn($.fn, 'trigger');
    }));

    afterEach(function () {
      $.fn.trigger = originalTriggerFunction;
    });

    describe('basic tests', () => {
      beforeEach(() => {
        compileDirective();
      });

      it('compiles the case action directive', () => {
        expect(element.html()).toContain('ng-repeat="action in caseActions');
      });
    });

    describe('sub menus', () => {
      var action;

      describe('when the menu has sub items', function () {
        beforeEach(() => {
          compileDirective();

          action = _.find(CaseActionsData.get(), (action) => {
            return action.items;
          });
        });

        it('shows the submenu', () => {
          expect(element.isolateScope().hasSubMenu(action)).toBe(true);
        });
      });

      describe('when the menu does not have sub items', function () {
        beforeEach(() => {
          compileDirective();

          action = _.find(CaseActionsData.get(), (action) => {
            return !action.items;
          });
        });

        it('hides the submenu', () => {
          expect(element.isolateScope().hasSubMenu(action)).toBe(false);
        });
      });
    });

    describe('disabling of the action', () => {
      var action;

      describe('when action can only be enabled for any number of cases', function () {
        beforeEach(() => {
          compileDirective();

          action = _.find(CaseActionsData.get(), (action) => {
            return !action.number;
          });
        });

        it('enables the action', () => {
          expect(element.isolateScope().isActionEnabled(action)).toBe(true);
        });
      });

      describe('when action can only be enabled for a defined number of cases and number of cases match', function () {
        beforeEach(() => {
          compileDirective();

          action = _.find(CaseActionsData.get(), (action) => {
            return action.number;
          });
          element.isolateScope().cases = [{ id: 1 }];
        });

        it('enables the action', () => {
          expect(element.isolateScope().isActionEnabled(action)).toBe(true);
        });
      });

      describe('when action can only be enabled for a defined number of cases and number of cases does not match', function () {
        beforeEach(() => {
          compileDirective();

          action = _.find(CaseActionsData.get(), (action) => {
            return action.number;
          });
          element.isolateScope().cases = [{ id: 1 }, { id: 2 }];
        });

        it('disables the action', () => {
          expect(element.isolateScope().isActionEnabled(action)).toBe(false);
        });
      });

      describe('when using actions that makes changes to the case', () => {
        describe('when the case is disabled', () => {
          beforeEach(() => {
            compileDirective();

            action = _.find(CaseActionsData.get(), { is_write_action: true });
            element.isolateScope().cases = [{ 'case_type_id.is_active': '0' }];
          });

          it('disables the action', () => {
            expect(element.isolateScope().isActionEnabled(action)).toBe(false);
          });
        });

        describe('when the case is enabled', () => {
          beforeEach(() => {
            compileDirective();

            action = _.find(CaseActionsData.get(), { is_write_action: true });
            element.isolateScope().cases = [{ 'case_type_id.is_active': '1' }];
          });

          it('enables the action', () => {
            expect(element.isolateScope().isActionEnabled(action)).toBe(true);
          });
        });
      });
    });

    describe('visibility of the action', () => {
      var action;

      describe('basic test', () => {
        beforeEach(() => {
          compileDirective();

          action = _.find(CaseActionsData.get(), (action) => {
            return action.action === 'Print';
          });
        });

        it('calls the individual service of the action', () => {
          expect(PrintCaseAction.isActionAllowed).toHaveBeenCalled();
        });
      });

      describe('when lock cases action', () => {
        describe('and cases can be locked', () => {
          beforeEach(() => {
            $provide.constant('allowCaseLocks', true);
            compileDirective();

            action = _.find(CaseActionsData.get(), (action) => {
              return action.action === 'LockCases';
            });
          });

          it('shows the action', () => {
            expect(element.isolateScope().isActionAllowed(action)).toBe(true);
          });
        });

        describe('and cases can not be locked', () => {
          beforeEach(() => {
            $provide.constant('allowCaseLocks', false);
            compileDirective();

            action = _.find(CaseActionsData.get(), (action) => {
              return action.action === 'LockCases';
            });
          });

          it('hides the action', () => {
            expect(element.isolateScope().isActionAllowed(action)).toBe(false);
          });
        });
      });

      describe('when not lock cases action', () => {
        describe('and bulk action is on and action can only be shown for single case selection', () => {
          beforeEach(() => {
            $provide.constant('allowCaseLocks', false);
            compileDirective({
              isBulkMode: true
            });

            action = _.find(CaseActionsData.get(), (action) => {
              return action.action !== 'LockCases';
            });

            action.number = 1;
          });

          it('hides the action', () => {
            expect(element.isolateScope().isActionAllowed(action)).toBe(false);
          });
        });

        describe('and bulk action is on and action can be shown for multiple case selection', () => {
          beforeEach(() => {
            $provide.constant('allowCaseLocks', false);
            compileDirective({
              isBulkMode: true
            });

            action = _.find(CaseActionsData.get(), (action) => {
              return action.action !== 'LockCases';
            });

            action.number = 2;
          });

          it('shows the action', () => {
            expect(element.isolateScope().isActionAllowed(action)).toBe(true);
          });
        });

        describe('and bulk action is off and action can only be shown for single case selection', () => {
          beforeEach(() => {
            $provide.constant('allowCaseLocks', false);
            compileDirective({
              isBulkMode: false
            });

            action = _.find(CaseActionsData.get(), (action) => {
              return action.action !== 'LockCases';
            });

            action.number = 1;
          });

          it('shows the action', () => {
            expect(element.isolateScope().isActionAllowed(action)).toBe(true);
          });
        });

        describe('and bulk action is off and action can be shown for multiple case selection', () => {
          beforeEach(() => {
            $provide.constant('allowCaseLocks', false);
            compileDirective({
              isBulkMode: false
            });

            action = _.find(CaseActionsData.get(), (action) => {
              return action.action !== 'LockCases';
            });

            action.number = 2;
          });

          it('hides the action', () => {
            expect(element.isolateScope().isActionAllowed(action)).toBe(false);
          });
        });
      });
    });

    describe('when clicking on an action', () => {
      var action;

      describe('if the action is not enabled', () => {
        beforeEach(() => {
          compileDirective();

          action = _.find(CaseActionsData.get(), (action) => {
            return action.action === 'Print';
          });

          spyOn(element.isolateScope(), 'isActionEnabled');
          element.isolateScope().isActionEnabled.and.returnValue(false);

          element.isolateScope().doAction(action);
        });

        it('does not allow clicking on the action', () => {
          expect(PrintCaseAction.doAction).not.toHaveBeenCalled();
        });
      });

      describe('if the action is enabled', () => {
        beforeEach(() => {
          compileDirective();

          action = _.find(CaseActionsData.get(), (action) => {
            return action.action === 'Print';
          });

          spyOn(element.isolateScope(), 'isActionEnabled');
          element.isolateScope().isActionEnabled.and.returnValue(true);
        });

        describe('basic test', () => {
          beforeEach(() => {
            element.isolateScope().doAction(action);
          });

          it('allows clicking on the action', () => {
            expect(PrintCaseAction.doAction).toHaveBeenCalled();
          });
        });

        describe('when the action returns an object', () => {
          beforeEach(() => {
            PrintCaseAction.doAction.and.returnValue($q.resolve({
              query: {
                civicase_reload: ''
              },
              path: 'some_path'
            }));
            civicaseCrmUrl.and.returnValue('CRM Mock URL');
            element.isolateScope().popupParams = jasmine.createSpy('popupParams');
            element.isolateScope().popupParams.and.returnValue('some_params');

            element.isolateScope().doAction(action);
            $rootScope.$digest();
          });

          it('opens a new form based on returned parameters', () => {
            expect(civicaseCrmLoadForm)
              .toHaveBeenCalledWith('CRM Mock URL');
          });

          it('listenes for the form success event', () => {
            expect(loadFormOnListener.on)
              .toHaveBeenCalledWith('crmFormSuccess crmPopupFormSuccess', jasmine.any(Function));
          });

          it('listenes for the popup close event', () => {
            expect(loadFormOnListener.on)
              .toHaveBeenCalledWith('dialogclose.crmPopup', jasmine.any(Function));
          });

          describe('when form is submitted succesfully', () => {
            beforeEach(() => {
              crmFormSuccessFunction(jasmine.any(Object), 'somedata');
            });

            it('refreshes the case information', () => {
              expect($rootScope.$broadcast)
                .toHaveBeenCalledWith('updateCaseData');
              expect(PrintCaseAction.refreshData).toHaveBeenCalled();
            });
          });

          describe('when form is closed', () => {
            describe('and form data is available', () => {
              beforeEach(() => {
                crmFormSuccessFunction(jasmine.any(Object), 'someformdata');
                dialogCloseFunction(jasmine.any(Object), 'somedata');
              });

              it('closes the popup', () => {
                expect(element.trigger)
                  .toHaveBeenCalledWith('crmPopupClose', [
                    loadFormOnListener, 'somedata'
                  ]);
              });

              it('refreshes the case', () => {
                expect(element.trigger)
                  .toHaveBeenCalledWith('crmPopupFormSuccess', [
                    loadFormOnListener, 'someformdata'
                  ]);
              });
            });

            describe('and form data is not available', () => {
              beforeEach(() => {
                dialogCloseFunction(jasmine.any(Object), 'somedata');
              });

              it('closes the popup', () => {
                expect(element.trigger)
                  .toHaveBeenCalledWith('crmPopupClose', [
                    loadFormOnListener, 'somedata'
                  ]);
              });

              it('does not refresh the case', () => {
                expect(element.trigger)
                  .not.toHaveBeenCalledWith('crmPopupFormSuccess', [
                    loadFormOnListener, 'someformdata'
                  ]);
              });
            });
          });
        });
      });
    });

    // TODO: FINISH REST of the unit test

    /**
     * Compiles the directive
     *
     * @param {object} options options
     */
    function compileDirective (options) {
      options = options || {};

      var isBulkMode = options.isBulkMode ? 'is-bulk-mode="true"' : '';
      var markup = `
        <div
          civicase-case-actions=[]
          ${isBulkMode}
          popup-params=""
        ></div>
      `;

      element = $compile(markup)($rootScope);
      $rootScope.$digest();
    }
  });
})(CRM._, CRM.$);
