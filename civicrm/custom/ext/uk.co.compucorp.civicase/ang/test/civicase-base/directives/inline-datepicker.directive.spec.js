/* eslint-env jasmine */

(($, _) => {
  describe('civicaseInlineDatepicker', () => {
    const NG_INVALID_CLASS = 'ng-invalid';
    let $compile, $rootScope, $scope, dateInputFormatValue, element,
      originalDatepickerFunction, removeDatePickerHrefs;

    beforeEach(module('civicase-base', 'civicase.data', ($provide) => {
      removeDatePickerHrefs = jasmine.createSpy('removeDatePickerHrefs');

      $provide.value('removeDatePickerHrefs', removeDatePickerHrefs);
    }));

    beforeEach(inject((_$compile_, _$rootScope_, _dateInputFormatValue_) => {
      $compile = _$compile_;
      $rootScope = _$rootScope_;
      dateInputFormatValue = _dateInputFormatValue_;
      $scope = $rootScope.$new();
      originalDatepickerFunction = $.fn.datepicker;
      $.fn.datepicker = jasmine.createSpy('datepicker');
      moment.suppressDeprecationWarnings = true;
    }));

    afterEach(() => {
      $.fn.datepicker = originalDatepickerFunction;
      moment.suppressDeprecationWarnings = false;
    });

    describe('when the directive is initialised', () => {
      beforeEach(() => {
        initDirective();
      });

      it('sets the element as a datepicker input element', () => {
        expect($.fn.datepicker).toHaveBeenCalled();
      });

      it('sets the date format as the one specified by CiviCRM setting', () => {
        expect($.fn.datepicker).toHaveBeenCalledWith(jasmine.objectContaining({
          dateFormat: dateInputFormatValue
        }));
      });
    });

    describe('handling the datepicker events', () => {
      beforeEach(() => {
        initDirective();
      });

      describe('when the datepicker is opened', () => {
        beforeEach(() => {
          const beforeShow = $.fn.datepicker.calls.first()
            .args[0].beforeShow || _.noop;

          beforeShow(1, 2, 3, 4);
        });

        it('does not change the site url wrongly when selecting a date', () => {
          expect(removeDatePickerHrefs).toHaveBeenCalledWith(1, 2, 3, 4);
        });

        it('keeps displaying the input even after moving out to select a date', () => {
          expect(element.hasClass('civicase__inline-datepicker--open')).toBe(true);
        });
      });

      describe('when the datepicker month or year changes', () => {
        beforeEach(() => {
          const onChangeMonthYear = $.fn.datepicker.calls.first()
            .args[0].onChangeMonthYear || _.noop;

          onChangeMonthYear(1, 2, 3, 4);
        });

        it('does not change the site url wrongly when selecting a date', () => {
          expect(removeDatePickerHrefs).toHaveBeenCalledWith(1, 2, 3, 4);
        });
      });

      describe('when the datepicker is closed', () => {
        beforeEach(() => {
          const onClose = $.fn.datepicker.calls.first()
            .args[0].onClose || _.noop;

          onClose();
        });

        it('hides the input element if not directly hovering it', () => {
          expect(element.hasClass('civicase__inline-datepicker--open')).toBe(false);
        });
      });
    });

    describe('input format', () => {
      describe('when the value is initially given', () => {
        beforeEach(() => {
          $scope.date = '1999-01-31';

          initDirective();
        });

        it('sets the input format in day/month/year', () => {
          expect(element.val()).toBe('31/01/1999');
        });

        it('keeps the model value in the year-month-day format', () => {
          expect($scope.date).toBe('1999-01-31');
        });
      });

      describe('when the value is updated', () => {
        beforeEach(() => {
          $scope.date = '1999-01-31';

          initDirective();
          element.val('28/02/1999');
          element.change();
          $scope.$digest();
        });

        it('sets the input format in day/month/year', () => {
          expect(element.val()).toBe('28/02/1999');
        });

        it('keeps the model value in the year-month-day format', () => {
          expect($scope.date).toBe('1999-02-28');
        });
      });
    });

    describe('validation', () => {
      describe('when changing to a invalid date format', () => {
        beforeEach(() => {
          $scope.date = '1999-01-31';

          initDirective();
          element.val('28/02');
          element.change();
          $scope.$digest();
        });

        it('marks the input as invalid', () => {
          expect(element.hasClass(NG_INVALID_CLASS)).toBe(true);
        });
      });

      describe('when changing to a valid date format', () => {
        beforeEach(() => {
          $scope.date = '1999-01-31';

          initDirective();
          element.val('28/02');
          element.change();
          $scope.$digest();
          element.val('28/02/1999');
          element.change();
          $scope.$digest();
        });

        it('marks the input as valid', () => {
          expect(element.hasClass(NG_INVALID_CLASS)).toBe(false);
        });
      });
    });

    describe('min and max dates', () => {
      describe('minimum date limit', () => {
        beforeEach(() => {
          $scope.date = '1999-06-01';
          $scope.minDate = '1999-01-01';

          initDirective(`
            data-min-date="{{minDate}}"
          `);
          $scope.$digest();
        });

        it('sets the minimum date as the provided value', () => {
          expect($.fn.datepicker).toHaveBeenCalledWith(
            'option',
            'minDate',
            new Date('1999-01-01')
          );
        });

        describe('when the minimum date is updated', () => {
          beforeEach(() => {
            $scope.minDate = '1999-01-31';

            $scope.$digest();
          });

          it('updates the minimum date limit', () => {
            expect($.fn.datepicker).toHaveBeenCalledWith(
              'option',
              'minDate',
              new Date('1999-01-31')
            );
          });
        });
      });

      describe('maximum date limit', () => {
        beforeEach(() => {
          $scope.date = '1999-06-01';
          $scope.maxDate = '1999-12-31';

          initDirective(`
            data-max-date="{{maxDate}}"
          `);
          $scope.$digest();
        });

        it('sets the maximum date as the provided value', () => {
          expect($.fn.datepicker).toHaveBeenCalledWith(
            'option',
            'maxDate',
            new Date('1999-12-31')
          );
        });

        describe('when the maximum date is updated', () => {
          beforeEach(() => {
            $scope.maxDate = '1999-01-01';

            $scope.$digest();
          });

          it('updates the minimum date limit', () => {
            expect($.fn.datepicker).toHaveBeenCalledWith(
              'option',
              'maxDate',
              new Date('1999-01-01')
            );
          });
        });
      });
    });

    /**
     * Initialises the Inline Datepicker directive on an input element using
     * the global $scope variable.
     *
     * @param {string} extraAttributes custom attributes to add to the input
     *   element alongside the inline datepicker directive.
     */
    function initDirective (extraAttributes = '') {
      element = $compile(`
        <input
          civicase-inline-datepicker
          ${extraAttributes}
          ng-model="date"
          type="text"
        />
      `)($scope);
      $scope.$digest();
    }
  });
})(CRM.$, CRM._);
