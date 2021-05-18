(($, _) => {
  describe('viewInPopup', () => {
    let viewInPopup, mockGetActivityFormService, mockGetActivityFormUrl,
      civicaseCrmLoadForm;

    beforeEach(module('civicase', 'civicase.data', ($provide) => {
      mockGetActivityFormService = jasmine.createSpy('getActivityFormService');
      mockGetActivityFormUrl = jasmine.createSpy('getActivityFormUrl');
      mockGetActivityFormUrl.and.returnValue('mock GetActivityFormUrl return value');

      mockGetActivityFormService.and.returnValue({
        getActivityFormUrl: mockGetActivityFormUrl
      });

      $provide.value('ActivityForms', { getActivityFormService: mockGetActivityFormService });
    }));

    beforeEach(inject((_viewInPopup_, _civicaseCrmLoadForm_) => {
      viewInPopup = _viewInPopup_;
      civicaseCrmLoadForm = _civicaseCrmLoadForm_;
    }));

    describe('when clicking a button', () => {
      let activity;

      beforeEach(() => {
        const event = $.Event('click');
        event.target = document.createElement('a');

        activity = { type: 'email' };
        viewInPopup(event, activity);
      });

      it('does not show the activity in a popup', () => {
        expect(mockGetActivityFormUrl).not.toHaveBeenCalled();
      });
    });

    describe('when not clicking a button', () => {
      let activity, returnValue, event;

      beforeEach(() => {
        civicaseCrmLoadForm.and.returnValue('loadForm');

        event = $.Event('click');
        event.target = document.createElement('span');
      });

      describe('and we want to update the activity', () => {
        beforeEach(function () {
          activity = { type: 'Meeting' };
          returnValue = viewInPopup(event, activity);
        });

        it('shows the activity in a popup in update mode', function () {
          expect(mockGetActivityFormService).toHaveBeenCalledWith(activity, { action: 'update' });
          expect(mockGetActivityFormUrl).toHaveBeenCalledWith(activity, { action: 'update' });
          expect(civicaseCrmLoadForm).toHaveBeenCalledWith('mock GetActivityFormUrl return value');
          expect(returnValue).toBe('loadForm');
        });
      });

      describe('and we want to view the activity', () => {
        beforeEach(() => {
          activity = { type: 'Meeting' };
          returnValue = viewInPopup(event, activity, {
            isReadOnly: true
          });
        });

        it('shows the activity in a popup in view mode', () => {
          expect(mockGetActivityFormService).toHaveBeenCalledWith(activity, { action: 'view' });
          expect(mockGetActivityFormUrl).toHaveBeenCalledWith(activity, { action: 'view' });
          expect(civicaseCrmLoadForm).toHaveBeenCalledWith('mock GetActivityFormUrl return value');
          expect(returnValue).toBe('loadForm');
        });
      });
    });
  });
})(CRM.$, CRM._);
