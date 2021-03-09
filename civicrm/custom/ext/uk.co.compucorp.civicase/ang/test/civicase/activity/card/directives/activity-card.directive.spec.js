/* eslint-env jasmine */

(($, _) => {
  describe('ActivityCard', () => {
    let $compile, $filter, $rootScope, $scope, viewInPopup, activityCard,
      activitiesMockData, CaseType, CaseTypeCategory,
      viewInPopupMockReturn, crmFormSuccessCallback;

    beforeEach(module('civicase', 'civicase.templates', 'civicase.data', ($provide) => {
      const viewInPopupMock = jasmine.createSpy('viewInPopupMock');
      viewInPopupMockReturn = jasmine.createSpyObj('viewInPopupMockObj', ['on']);
      viewInPopupMockReturn.on.and.callFake((event, fn) => {
        crmFormSuccessCallback = fn;
      });
      viewInPopupMock.and.returnValue(viewInPopupMockReturn);

      $provide.value('viewInPopup', viewInPopupMock);
    }));

    beforeEach(inject(function (_$compile_, _$filter_, _$rootScope_, _activitiesMockData_,
      _CaseType_, _CaseTypeCategory_, _viewInPopup_) {
      $compile = _$compile_;
      $filter = _$filter_;
      $rootScope = _$rootScope_;
      activitiesMockData = _activitiesMockData_;
      CaseType = _CaseType_;
      CaseTypeCategory = _CaseTypeCategory_;
      viewInPopup = _viewInPopup_;

      $scope = $rootScope.$new();
      $scope.activity = {
        type: 'Meeting'
      };

      $('<div id="bootstrap-theme"></div>').appendTo('body');
      initDirective();
    }));

    afterEach(() => {
      $('#bootstrap-theme').remove();
    });

    describe('on init', () => {
      it('stores a reference to the bootstrap theme element', () => {
        expect(activityCard.isolateScope().bootstrapThemeElement.is('#bootstrap-theme')).toBe(true);
      });

      it('defines the "From" and "To" fields visibility as false', () => {
        expect(activityCard.isolateScope().areFromAndToFieldsVisible).toBe(false);
      });

      describe('when the activity does not belong to a case', () => {
        it('does not store a link to a case details page', () => {
          expect($scope.caseDetailUrl).not.toBeDefined();
        });
      });

      describe('when the activity belongs to a case', () => {
        let expectedCaseDetailsUrl;

        beforeEach(() => {
          const caseTypes = CaseType.getAll();
          const caseTypeId = _.chain(caseTypes).keys().sample().value();
          const caseType = CaseType.getById(caseTypeId);
          const caseTypeCategory = _.find(CaseTypeCategory.getAll(), {
            value: caseType.case_type_category
          });
          $scope.activity = _.sample(activitiesMockData.get());
          $scope.activity.case_id = _.uniqueId();
          $scope.activity.type = 'Meeting';
          $scope.activity.case = {
            case_id: $scope.activity.case_id,
            case_type_id: caseTypeId
          };

          expectedCaseDetailsUrl = getExpectedCaseDetailsUrl(
            $scope.activity.case_id,
            caseTypeCategory.name,
            caseType.is_active
          );

          initDirective();
        });

        it('stores the URL to the case details for the case associated to the activity', () => {
          expect(activityCard.isolateScope().caseDetailUrl).toEqual(expectedCaseDetailsUrl);
        });

        /**
         * @param {number} caseId the case id.
         * @param {number} caseTypeCategoryName the category the case belongs to.
         * @param {boolean} isCaseTypeActive the active status of the case type.
         * @returns {string} the expected URL to the case details for the given case.
         */
        function getExpectedCaseDetailsUrl (caseId, caseTypeCategoryName, isCaseTypeActive) {
          const caseDetailUrl = 'civicrm/case/a/?' +
            $.param({ case_type_category: caseTypeCategoryName }) +
            '#/case/list';
          const angularParams = $.param({
            caseId,
            cf: JSON.stringify({
              'case_type_id.is_active': isCaseTypeActive
            })
          });

          return $filter('civicaseCrmUrl')(caseDetailUrl) + '?' + angularParams;
        }
      });
    });

    describe('"From" and "To" fields visibility', () => {
      beforeEach(() => {
        $scope.activity = activitiesMockData.get()[0];
        $scope.activity.type = 'Meeting';
      });

      describe('when the activity is a communication of the "Print/Merge Document" type', () => {
        beforeEach(() => {
          $scope.activity.category = 'communication';
          $scope.activity.type = 'Print/Merge Document';

          initDirective();
        });

        it('does not show the "From" and "To" fields', () => {
          expect(activityCard.isolateScope().areFromAndToFieldsVisible).toBe(false);
        });
      });

      describe('when the activity is any communication other than the "Print/Merge Document" type', () => {
        beforeEach(() => {
          $scope.activity.category = 'communication';
          $scope.activity.type = 'Email';

          initDirective();
        });

        it('shows the "From" and "To" fields', () => {
          expect(activityCard.isolateScope().areFromAndToFieldsVisible).toBe(true);
        });
      });

      describe('when the activity is not a communication', () => {
        beforeEach(() => {
          $scope.activity.category = 'milestone';
          $scope.activity.type = 'Open Case';

          initDirective();
        });

        it('does not show the "From" and "To" fields', () => {
          expect(activityCard.isolateScope().areFromAndToFieldsVisible).toBe(false);
        });
      });
    });

    describe('when editing an activity in the popup', () => {
      let activity;

      beforeEach(() => {
        activity = _.first(activitiesMockData.get());
        activity.type = 'Meeting';

        activityCard.isolateScope().viewInPopup(null, activity);
      });

      it('opens the modal to edit the activity', () => {
        expect(viewInPopup).toHaveBeenCalledWith(null, activity, {
          isReadOnly: false
        });
      });

      it('listens for the the form to be saved', () => {
        expect(viewInPopupMockReturn.on).toHaveBeenCalledWith('crmFormSuccess', jasmine.any(Function));
      });

      describe('when activity is saved', () => {
        beforeEach(() => {
          crmFormSuccessCallback();
        });

        it('refreshes the data when activity is saved', () => {
          expect(activityCard.isolateScope().refresh).toHaveBeenCalled();
        });
      });
    });

    describe('when viewing an activity in the popup', () => {
      let activity;

      beforeEach(() => {
        activity = _.first(activitiesMockData.get());

        activityCard.isolateScope().isReadOnly = true;
        activityCard.isolateScope().viewInPopup(null, activity);
      });

      it('opens the modal to view the activity', () => {
        expect(viewInPopup).toHaveBeenCalledWith(null, activity, {
          isReadOnly: true
        });
      });
    });

    /**
     * Initializes the ActivityCard directive
     */
    function initDirective () {
      $scope.refreshCallback = jasmine.createSpy('refreshCallback');

      activityCard = $compile(`
        <div
          case-activity-card="activity"
          refresh-callback="refreshCallback"
          is-read-only="false"
        ></div>
      `)($scope);
      $rootScope.$digest();
    }
  });
})(CRM.$, CRM._);
