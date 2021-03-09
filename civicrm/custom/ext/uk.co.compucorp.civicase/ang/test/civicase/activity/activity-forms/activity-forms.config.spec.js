/* eslint-env jasmine */
(() => {
  describe('Activity forms configuration', () => {
    let ActivityFormsProvider;

    beforeEach(module('civicase-base', (_ActivityFormsProvider_) => {
      ActivityFormsProvider = _ActivityFormsProvider_;

      spyOn(ActivityFormsProvider, 'addActivityForms');
    }));

    beforeEach(module('civicase'));

    beforeEach(inject);

    describe('when the civicase module is configured', () => {
      it('adds the draft pdf activity form service', () => {
        expect(ActivityFormsProvider.addActivityForms)
          .toHaveBeenCalledWith(jasmine.arrayContaining([{
            name: 'AddCustomPathActivityForm',
            weight: 0
          }]));
      });

      it('adds the draft pdf activity form service', () => {
        expect(ActivityFormsProvider.addActivityForms)
          .toHaveBeenCalledWith(jasmine.arrayContaining([{
            name: 'AddActivityForm',
            weight: 1
          }]));
      });

      it('adds the email activity form service', () => {
        expect(ActivityFormsProvider.addActivityForms)
          .toHaveBeenCalledWith(jasmine.arrayContaining([{
            name: 'EmailActivityForm',
            weight: 2
          }]));
      });

      it('adds the draft pdf activity form service', () => {
        expect(ActivityFormsProvider.addActivityForms)
          .toHaveBeenCalledWith(jasmine.arrayContaining([{
            name: 'DraftPdfActivityForm',
            weight: 3
          }]));
      });

      it('adds the draft email activity form service', () => {
        expect(ActivityFormsProvider.addActivityForms)
          .toHaveBeenCalledWith(jasmine.arrayContaining([{
            name: 'DraftEmailActivityForm',
            weight: 4
          }]));
      });

      it('adds the activity popup form service', () => {
        expect(ActivityFormsProvider.addActivityForms)
          .toHaveBeenCalledWith(jasmine.arrayContaining([{
            name: 'UpdateActivityForm',
            weight: 5
          }]));
      });

      it('adds the view activity form service', () => {
        expect(ActivityFormsProvider.addActivityForms)
          .toHaveBeenCalledWith(jasmine.arrayContaining([{
            name: 'ViewActivityForm',
            weight: 6
          }]));
      });
    });
  });
})();
