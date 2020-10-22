/* eslint-env jasmine */
((_) => {
  describe('checkIfDraftActivity', () => {
    let activity, checkIfDraftActivity, emailActivityTypeId,
      isDraftActivity, pdfActivityTypeId;

    beforeEach(module('civicase.data', 'civicase'));

    beforeEach(inject((_activitiesMockData_, _ActivityTypesData_,
      _checkIfDraftActivity_) => {
      checkIfDraftActivity = _checkIfDraftActivity_;
      emailActivityTypeId = _.chain(_ActivityTypesData_.values)
        .findKey({ name: 'Email' })
        .cloneDeep()
        .value();
      pdfActivityTypeId = _.chain(_ActivityTypesData_.values)
        .findKey({ name: 'Print PDF Letter' })
        .cloneDeep()
        .value();
      activity = _.chain(_activitiesMockData_.get())
        .first()
        .cloneDeep()
        .value();
    }));

    describe('when checking a draft activity of any kind', () => {
      beforeEach(() => {
        activity.status_name = 'Draft';
        isDraftActivity = checkIfDraftActivity(activity);
      });

      it('returns true', () => {
        expect(isDraftActivity).toBe(true);
      });
    });

    describe('when checking a non draft activity of any kind', () => {
      beforeEach(() => {
        activity.status_name = 'Completed';
        isDraftActivity = checkIfDraftActivity(activity);
      });

      it('returns false', () => {
        expect(isDraftActivity).toBe(false);
      });
    });

    describe('when checking an email draft', () => {
      beforeEach(() => {
        activity.activity_type_id = emailActivityTypeId;
        activity.status_name = 'Draft';
        isDraftActivity = checkIfDraftActivity(activity, ['Email']);
      });

      it('returns true', () => {
        expect(isDraftActivity).toBe(true);
      });
    });

    describe('when checking a non draft email', () => {
      beforeEach(() => {
        activity.activity_type_id = emailActivityTypeId;
        activity.status_name = 'Completed';
        isDraftActivity = checkIfDraftActivity(activity, ['Email']);
      });

      it('returns false', () => {
        expect(isDraftActivity).toBe(false);
      });
    });

    describe('when checking a PDF draft', () => {
      beforeEach(() => {
        activity.activity_type_id = pdfActivityTypeId;
        activity.status_name = 'Draft';
        isDraftActivity = checkIfDraftActivity(activity, ['Print PDF Letter']);
      });

      it('returns true', () => {
        expect(isDraftActivity).toBe(true);
      });
    });

    describe('when checking a non draft PDF', () => {
      beforeEach(() => {
        activity.activity_type_id = pdfActivityTypeId;
        activity.status_name = 'Completed';
        isDraftActivity = checkIfDraftActivity(activity, ['Print PDF Letter']);
      });

      it('returns false', () => {
        expect(isDraftActivity).toBe(false);
      });
    });
  });
})(CRM._);
