/* eslint-env jasmine */
(() => {
  describe('isTruthy', () => {
    let isTruthy;

    beforeEach(module('civicase-base'));

    beforeEach(inject((_isTruthy_) => {
      isTruthy = _isTruthy_;
    }));

    describe('when tested with "1"', () => {
      it('returns true', () => {
        expect(isTruthy('1')).toBe(true);
      });
    });

    describe('when tested with "0"', () => {
      it('returns false', () => {
        expect(isTruthy('0')).toBe(false);
      });
    });

    describe('when tested with 1', () => {
      it('returns true', () => {
        expect(isTruthy(1)).toBe(true);
      });
    });

    describe('when tested with 0', () => {
      it('returns false', () => {
        expect(isTruthy(0)).toBe(false);
      });
    });

    describe('when tested with "true"', () => {
      it('returns true', () => {
        expect(isTruthy(true)).toBe(true);
      });
    });

    describe('when tested with "false"', () => {
      it('returns false', () => {
        expect(isTruthy(false)).toBe(false);
      });
    });
  });
})();
