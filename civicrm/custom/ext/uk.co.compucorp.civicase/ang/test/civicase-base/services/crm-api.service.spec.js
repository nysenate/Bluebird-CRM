/* eslint-env jasmine */

((_) => {
  describe('crm service', () => {
    let $q, $rootScope, crmApiMock, civicaseCrmApi;

    beforeEach(module('civicase-base', ($provide) => {
      crmApiMock = jasmine.createSpy('crmApi');

      $provide.value('crmApi', crmApiMock);
    }));

    beforeEach(inject((_$q_, _$rootScope_, _civicaseCrmApi_) => {
      $q = _$q_;
      $rootScope = _$rootScope_;
      civicaseCrmApi = _civicaseCrmApi_;
    }));

    describe('when calling the api with an array of requests', () => {
      describe('and when one of the requests returns an error', () => {
        var errorHandler;

        beforeEach(() => {
          crmApiMock.and.returnValue($q.resolve([
            { is_error: false },
            { is_error: true }
          ]));

          errorHandler = jasmine.createSpy('errorHandler');

          civicaseCrmApi([
            ['SomeEntity', 'someendpoint', {}],
            ['SomeEntity2', 'someendpoint2', {}]
          ]).catch(errorHandler);

          $rootScope.$digest();
        });

        it('calls the backend with for all the requested information', () => {
          expect(crmApiMock.calls.mostRecent().args[0]).toEqual([
            ['SomeEntity', 'someendpoint', {}],
            ['SomeEntity2', 'someendpoint2', {}]
          ]);
        });

        it('throws an exception', () => {
          expect(errorHandler).toHaveBeenCalled();
        });
      });

      describe('and none one of the requests returns an error', () => {
        var errorHandler;

        beforeEach(() => {
          crmApiMock.and.returnValue($q.resolve([
            { is_error: false },
            { is_error: false }
          ]));

          errorHandler = jasmine.createSpy('errorHandler');

          civicaseCrmApi([
            ['SomeEntity', 'someendpoint', {}],
            ['SomeEntity2', 'someendpoint2', {}]
          ]).catch(errorHandler);

          $rootScope.$digest();
        });

        it('calls the backend with for all the requested information', () => {
          expect(crmApiMock.calls.mostRecent().args[0]).toEqual([
            ['SomeEntity', 'someendpoint', {}],
            ['SomeEntity2', 'someendpoint2', {}]
          ]);
        });

        it('does not throw an exception', () => {
          expect(errorHandler).not.toHaveBeenCalled();
        });
      });
    });
  });
})(CRM._);
