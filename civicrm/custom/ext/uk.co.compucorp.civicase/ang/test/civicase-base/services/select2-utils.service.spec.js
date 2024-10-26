(() => {
  describe('Select2Utils', () => {
    let Select2Utils;

    beforeEach(module('civicase-base', 'civicase.data'));

    beforeEach(inject((_Select2Utils_) => {
      Select2Utils = _Select2Utils_;
    }));

    describe('when getting values from select2', () => {
      let returnedValue;

      beforeEach(() => {
        returnedValue = Select2Utils.getSelect2Value('1,2');
      });

      it('returns the values converted to array format', () => {
        expect(returnedValue).toEqual(['1', '2']);
      });
    });

    describe('when mapping object to show as select2 option ', () => {
      let returnedValue;

      describe('when sending option with value and label field', () => {
        beforeEach(() => {
          returnedValue = Select2Utils.mapSelectOptions({
            value: 'Some value',
            label: 'Some label',
            color: 'Some color',
            icon: 'Some icon'
          });
        });

        it('formats the sent value to be disaplayed as a select2 option', () => {
          expect(returnedValue).toEqual({
            id: 'Some value',
            text: 'Some label',
            color: 'Some color',
            icon: 'Some icon'
          });
        });
      });

      describe('when sending option with name and title field', () => {
        beforeEach(() => {
          returnedValue = Select2Utils.mapSelectOptions({
            name: 'Some value',
            title: 'Some label',
            color: 'Some color',
            icon: 'Some icon'
          });
        });

        it('formats the sent value to be disaplayed as a select2 option', () => {
          expect(returnedValue).toEqual({
            id: 'Some value',
            text: 'Some label',
            color: 'Some color',
            icon: 'Some icon'
          });
        });
      });
    });
  });
})();
