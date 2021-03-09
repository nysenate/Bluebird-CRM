/* eslint-env jasmine */

(($, _) => {
  describe('civicaseInlineDatepicker', () => {
    let $compile, $q, $rootScope, $scope, civicaseCrmApi, crmStatus, element;

    beforeEach(module('civicase-base', 'civicase.data', ($provide) => {
      civicaseCrmApi = jasmine.createSpy('civicaseCrmApi');
      crmStatus = jasmine.createSpy('crmStatus');

      $provide.value('civicaseCrmApi', civicaseCrmApi);
      $provide.value('crmStatus', crmStatus);
    }));

    beforeEach(inject((_$compile_, _$q_, _$rootScope_,
      _civicaseCrmApi_) => {
      $compile = _$compile_;
      $q = _$q_;
      $rootScope = _$rootScope_;
      $scope = $rootScope.$new();
      civicaseCrmApi = _civicaseCrmApi_;
    }));

    beforeEach(() => {
      $scope.handleDataSent = jasmine.createSpy('handleDataSent');
      $scope.apiData = ['Contact', 'create', { id: 1, display_name: 'abc' }];
      $scope.myModel = 123;

      crmStatus.and.returnValue($q.resolve());
      civicaseCrmApi.and.returnValue($q.resolve());
      initDirective();
    });

    describe('when the input changes', () => {
      beforeEach(() => {
        element.val(456);
        element.change();
      });

      it('does not send the data to the API', () => {
        expect(civicaseCrmApi).not.toHaveBeenCalled();
      });

      describe('when the loses focus', () => {
        beforeEach(() => {
          element.blur();
        });

        it('displays a status message while the request is being completed', () => {
          expect(crmStatus).toHaveBeenCalledWith(
            {
              start: 'Saving',
              success: 'Saved'
            },
            jasmine.any(Object)
          );
        });

        it('sends the data to the API', () => {
          expect(civicaseCrmApi).toHaveBeenCalledWith($scope.apiData);
        });

        it('calls the api data sent handler', () => {
          expect($scope.handleDataSent).toHaveBeenCalledWith();
        });
      });
    });

    /**
     * Initialises the Inline Datepicker directive on an input element using
     * the global $scope variable.
     */
    function initDirective () {
      element = $compile(`
        <input
          civicase-send-to-api-on-change
          data-api-data="apiData"
          data-on-api-data-sent="handleDataSent()"
          ng-model="myModel"
          type="text"
        />
      `)($scope);
      $scope.$digest();
    }
  });
})(CRM.$, CRM._);
