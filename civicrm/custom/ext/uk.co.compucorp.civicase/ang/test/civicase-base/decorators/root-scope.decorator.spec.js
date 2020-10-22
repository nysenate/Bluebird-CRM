/* eslint-env jasmine */

(() => {
  describe('$rootScope decorator', () => {
    let $rootScope, ts;

    beforeEach(module('civicase-base'));

    beforeEach(inject((_$rootScope_, _ts_) => {
      $rootScope = _$rootScope_;
      ts = _ts_;
    }));

    describe('when the app starts', () => {
      it('appends the `ts` service to the root scope', () => {
        expect($rootScope.civicaseTs).toBe(ts);
      });

      it('appends the `ts` service as `civicaseTs` to the root scope', () => {
        expect($rootScope.civicaseTs).toBe(ts);
      });
    });
  });
})();
