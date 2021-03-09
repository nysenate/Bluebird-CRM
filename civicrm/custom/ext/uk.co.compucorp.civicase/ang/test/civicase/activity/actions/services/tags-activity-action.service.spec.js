/* eslint-env jasmine */

(function (_, $) {
  describe('TagsActivityAction', function () {
    var $q, $rootScope, TagsActivityAction, activitiesMockData, TagsMockData,
      civicaseCrmApiMock, dialogServiceMock;

    beforeEach(module('civicase', 'civicase.data', function ($provide) {
      civicaseCrmApiMock = jasmine.createSpy('civicaseCrmApi');
      dialogServiceMock = jasmine.createSpyObj('dialogService', ['open']);

      $provide.value('civicaseCrmApi', civicaseCrmApiMock);
      $provide.value('dialogService', dialogServiceMock);
    }));

    beforeEach(inject(function (_$q_, _$rootScope_, _activitiesMockData_, _TagsMockData_,
      _TagsActivityAction_) {
      $q = _$q_;
      $rootScope = _$rootScope_;
      activitiesMockData = _activitiesMockData_;
      TagsMockData = _TagsMockData_;
      TagsActivityAction = _TagsActivityAction_;

      spyOn($.fn, 'dialog');
      spyOn($rootScope, '$broadcast');
    }));

    describe('Add Tags to Activities bulk action', function () {
      var modalOpenCall;
      var $scope = {};

      beforeEach(function () {
        civicaseCrmApiMock.and.returnValue($q.resolve({ values: TagsMockData.get() }));
        $scope.selectedActivities = activitiesMockData.get();
        TagsActivityAction.doAction($scope, { operation: 'add' });
        $rootScope.$digest();
        modalOpenCall = dialogServiceMock.open.calls.mostRecent().args;
      });

      it('fetches the tags from the api endpoint', function () {
        expect(civicaseCrmApiMock).toHaveBeenCalledWith('Tag', 'get', {
          sequential: 1,
          used_for: { LIKE: '%civicrm_activity%' },
          options: { limit: 0 }
        });
      });

      it('sets the title of the modal as "Tag Activities"', function () {
        expect(modalOpenCall[3].title).toBe('Tag Activities');
      });

      it('sets the text of the save button of the modal as "Tag Activities"', function () {
        expect(modalOpenCall[3].buttons[0].text).toBe('Tag Activities');
      });

      it('does not have any selected tags initially', function () {
        expect(modalOpenCall[2].selectedTags.length).toBe(0);
      });

      it('shows all tags for selection', function () {
        expect(modalOpenCall[2].allTags).toEqual(TagsMockData.get());
      });

      it('opens the modal to add tags', function () {
        expect(dialogServiceMock.open).toHaveBeenCalledWith(
          'TagsActivityAction',
          '~/civicase/activity/actions/services/tags-activity-action.html',
          jasmine.any(Object),
          jasmine.any(Object)
        );
      });

      describe('when the save button is clicked', function () {
        beforeEach(function () {
          modalOpenCall[2].selectedTags = [TagsMockData.get()[0].id];
          modalOpenCall[3].buttons[0].click();
        });

        describe('api calls', function () {
          var apiCalls;

          beforeEach(function () {
            apiCalls = [['EntityTag', 'createByQuery', {
              entity_table: 'civicrm_activity',
              entity_id: activitiesMockData.get().map(function (activity) {
                return activity.id;
              }),
              tag_id: [TagsMockData.get()[0].id]
            }]];
          });

          it('saves the selected tags to the selected activities', function () {
            expect(civicaseCrmApiMock).toHaveBeenCalledWith(apiCalls);
          });
        });

        it('closes the dialog', function () {
          expect($.fn.dialog).toHaveBeenCalledWith('close');
        });
      });
    });

    describe('Remove Tags to Activities bulk action', function () {
      var modalOpenCall;
      var $scope = {};

      beforeEach(function () {
        civicaseCrmApiMock.and.returnValue($q.resolve({ values: TagsMockData.get() }));
        $scope.selectedActivities = activitiesMockData.get();
        TagsActivityAction.doAction($scope, { operation: 'remove' });
        $rootScope.$digest();
        modalOpenCall = dialogServiceMock.open.calls.mostRecent().args;
      });

      it('fetches the tags from the api endpoint', function () {
        expect(civicaseCrmApiMock).toHaveBeenCalledWith('Tag', 'get', {
          sequential: 1,
          used_for: { LIKE: '%civicrm_activity%' },
          options: { limit: 0 }
        });
      });

      it('sets the title of the modal as "Tag Activities (Remove)"', function () {
        expect(modalOpenCall[3].title).toBe('Tag Activities (Remove)');
      });

      it('sets the text of the save button of the modal as "Remove tags from Activities"', function () {
        expect(modalOpenCall[3].buttons[0].text).toBe('Remove tags from Activities');
      });

      it('does not have any selected tags initially', function () {
        expect(modalOpenCall[2].selectedTags.length).toBe(0);
      });

      it('shows all tags for selection', function () {
        expect(modalOpenCall[2].allTags).toEqual(TagsMockData.get());
      });

      it('opens the modal to remove tags', function () {
        expect(dialogServiceMock.open).toHaveBeenCalledWith(
          'TagsActivityAction',
          '~/civicase/activity/actions/services/tags-activity-action.html',
          jasmine.any(Object),
          jasmine.any(Object)
        );
      });

      describe('when the save button is clicked', function () {
        beforeEach(function () {
          modalOpenCall[2].selectedTags = [TagsMockData.get()[0].id];
          modalOpenCall[3].buttons[0].click();
        });

        describe('api calls', function () {
          var apiCalls;

          beforeEach(function () {
            apiCalls = [['EntityTag', 'deleteByQuery', {
              entity_table: 'civicrm_activity',
              entity_id: activitiesMockData.get().map(function (activity) {
                return activity.id;
              }),
              tag_id: [TagsMockData.get()[0].id]
            }]];
          });

          it('deletes the selected tags to the selected activities', function () {
            expect(civicaseCrmApiMock).toHaveBeenCalledWith(apiCalls);
          });
        });

        it('closes the dialog', function () {
          expect($.fn.dialog).toHaveBeenCalledWith('close');
        });
      });
    });
  });
})(CRM._, CRM.$);
