(function (_, $) {
  describe('EditTagsCaseAction', function () {
    var $q, $rootScope, EditTagsCaseAction, CasesData,
      civicaseCrmApiMock, dialogServiceMock, TagsMockData;

    beforeEach(module('civicase', 'civicase.data', ($provide) => {
      civicaseCrmApiMock = jasmine.createSpy('civicaseCrmApi');
      dialogServiceMock = jasmine.createSpyObj('dialogService', ['open']);

      $provide.value('civicaseCrmApi', civicaseCrmApiMock);
      $provide.value('dialogService', dialogServiceMock);
    }));

    beforeEach(inject(function (_$q_, _$rootScope_, _EditTagsCaseAction_,
      _CasesData_, _TagsMockData_) {
      $q = _$q_;
      $rootScope = _$rootScope_;
      EditTagsCaseAction = _EditTagsCaseAction_;
      CasesData = _CasesData_.get().values;
      TagsMockData = _TagsMockData_.get();

      spyOn($.fn, 'dialog');
    }));

    describe('basic tests', function () {
      let modalOpenCall, callBackFn;

      beforeEach(() => {
        callBackFn = jasmine.createSpy('callBackFn');
        var editTagsCaseAction = {
          title: 'Edit Tags',
          action: 'EditTags'
        };

        civicaseCrmApiMock.and.returnValue($q.resolve({ values: TagsMockData }));

        var caseObj = angular.copy(CasesData[0]);
        caseObj.tag_id = {
          1: { tag_id: 1 },
          2: { tag_id: 2 }
        };

        EditTagsCaseAction.doAction([caseObj], editTagsCaseAction, callBackFn);

        $rootScope.$digest();
        modalOpenCall = dialogServiceMock.open.calls.mostRecent().args;
      });

      it('fetches the tags and shows on the UI', () => {
        expect(civicaseCrmApiMock).toHaveBeenCalledWith('Tag', 'get', {
          sequential: 1,
          used_for: { LIKE: '%civicrm_case%' },
          options: { limit: 0 }
        });
      });

      it('opens the modal to show tags', () => {
        expect(modalOpenCall[0]).toBe('EditTags');
        expect(modalOpenCall[1]).toBe('~/civicase/case/actions/directives/edit-tags.html');
        expect(modalOpenCall[3]).toEqual({
          autoOpen: false,
          height: 'auto',
          width: '450px',
          title: 'Edit Tags',
          buttons: [{
            text: 'Save',
            icons: { primary: 'fa-check' },
            click: jasmine.any(Function)
          }]
        });
      });

      it('displays the existing tags', () => {
        expect(modalOpenCall[2]).toEqual({
          allTags: TagsMockData,
          selectedTags: [1, 2]
        });
      });

      describe('when saving the tags', () => {
        beforeEach(() => {
          modalOpenCall[2].selectedTags = [1, 3];
          modalOpenCall[3].buttons[0].click();
        });

        it('deletes the removed tags', () => {
          expect(callBackFn.calls.mostRecent().args[0][0]).toEqual([
            'EntityTag', 'deleteByQuery', { entity_id: '141', tag_id: [2], entity_table: 'civicrm_case' }
          ]);
        });

        it('adds the newly added tags', () => {
          expect(callBackFn.calls.mostRecent().args[0][1]).toEqual([
            'EntityTag', 'createByQuery', { entity_id: '141', tag_id: [3], entity_table: 'civicrm_case' }
          ]);
        });

        it('creates an activity', () => {
          expect(callBackFn.calls.mostRecent().args[0][2]).toEqual([
            'Activity', 'create', { case_id: '141', status_id: 'Completed', activity_type_id: 'Change Case Tags' }
          ]);
        });

        it('closes the dialog', () => {
          expect($.fn.dialog).toHaveBeenCalled();
        });
      });
    });
  });
})(CRM._, CRM.$);
