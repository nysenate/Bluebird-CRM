/* eslint-env jasmine */

((_) => {
  describe('Contact Case Tab', () => {
    var $controller, $rootScope, $scope, CaseTypeCategoryTranslationService,
      crmApi, mockContactId, mockContactService, AddCase;

    beforeEach(module('civicase.data', 'civicase', ($provide) => {
      mockContactService = jasmine.createSpyObj('Contact', ['getCurrentContactID']);

      $provide.value('Contact', mockContactService);
    }));

    beforeEach(inject((_$controller_, _$rootScope_, _CaseTypeCategoryTranslationService_,
      _crmApi_, _AddCase_) => {
      $controller = _$controller_;
      $rootScope = _$rootScope_;
      CaseTypeCategoryTranslationService = _CaseTypeCategoryTranslationService_;
      AddCase = _AddCase_;
      crmApi = _crmApi_;

      spyOn(CaseTypeCategoryTranslationService, 'restoreTranslation');
      spyOn(CaseTypeCategoryTranslationService, 'storeTranslation');
    }));

    beforeEach(() => {
      mockContactId = _.uniqueId();

      mockContactService.getCurrentContactID.and.returnValue(mockContactId);
      initController();
    });

    describe('on init', () => {
      it('stores the contact id extracted from the URL', () => {
        expect($scope.contactId).toBe(mockContactId);
      });

      it('stores the current case type category translation', () => {
        expect(CaseTypeCategoryTranslationService.storeTranslation)
          .toHaveBeenCalledWith($scope.caseTypeCategory);
      });

      it('stores the case type category name', () => {
        expect($scope.caseTypeCategoryName)
          .toBe(CRM['civicase-base'].caseTypeCategories[$scope.caseTypeCategory].name);
      });
    });

    describe('when loading cases', () => {
      it('requests non deleted opened cases for the given contact', () => {
        expect(crmApi.calls.allArgs()).toContain(jasmine.arrayContaining([
          jasmine.objectContaining({
            cases: ['Case', 'getcaselist', jasmine.objectContaining({
              'status_id.grouping': 'Opened',
              'case_type_id.case_type_category': 2,
              contact_id: mockContactId,
              is_deleted: 0
            })]
          })
        ]));
      });

      it('requests non deleted closed cases for the given contact', () => {
        expect(crmApi.calls.allArgs()).toContain(jasmine.arrayContaining([
          jasmine.objectContaining({
            cases: ['Case', 'getcaselist', jasmine.objectContaining({
              'status_id.grouping': 'Closed',
              'case_type_id.case_type_category': 2,
              contact_id: mockContactId,
              is_deleted: 0
            })]
          })
        ]));
      });

      it('requests non deleted cases where the contact has a role other than client', () => {
        expect(crmApi.calls.allArgs()).toContain(jasmine.arrayContaining([
          jasmine.objectContaining({
            cases: ['Case', 'getcaselist', jasmine.objectContaining({
              exclude_for_client_id: $scope.contactId,
              contact_involved: $scope.contactId,
              'case_type_id.case_type_category': 2,
              is_deleted: 0
            })]
          })
        ]));
      });
    });

    describe('when changing contact tabs', () => {
      describe('when changing back to the current case type category tab', () => {
        beforeEach(() => {
          $scope.handleContactTabChange({
            case_type_category: $scope.caseTypeCategory
          });
        });

        it('restores the translations for the current case type category', () => {
          expect(CaseTypeCategoryTranslationService.restoreTranslation)
            .toHaveBeenCalledWith($scope.caseTypeCategory);
        });
      });

      describe('when changing back to a different case type category tab', () => {
        beforeEach(() => {
          $scope.handleContactTabChange({
            case_type_category: 3
          });
        });

        it('does not restore the case type category translation', () => {
          expect(CaseTypeCategoryTranslationService.restoreTranslation)
            .not.toHaveBeenCalled();
        });
      });
    });

    describe('Add Case Button', () => {
      beforeEach(() => {
        spyOn(AddCase, 'clickHandler');
        spyOn(AddCase, 'isVisible');
      });

      describe('visibility of Add Case Button', () => {
        beforeEach(() => {
          initController();
          $scope.isAddCaseVisible();
        });

        it('displays the Add Case button only when adequate permission is available', () => {
          expect(AddCase.isVisible).toHaveBeenCalled();
        });
      });

      describe('when clicking on Add Case Button', () => {
        beforeEach(() => {
          initController();
          $scope.addCase({
            caseTypeCategoryName: 'cases',
            contactId: '5',
            callbackFn: jasmine.any(Function)
          });
        });

        it('creates a new case', () => {
          expect(AddCase.clickHandler).toHaveBeenCalledWith({
            caseTypeCategoryName: 'cases',
            contactId: '5',
            callbackFn: jasmine.any(Function)
          });
        });
      });
    });

    /**
     * Initializes the contact case tab controller.
     */
    function initController () {
      $scope = $rootScope.$new();
      $scope.caseTypeCategory = 2;
      $controller('CivicaseContactCaseTabController', { $scope: $scope });
    }
  });
})(CRM._);
