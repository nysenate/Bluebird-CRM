/* eslint-env jasmine */

((_) => {
  describe('CasesUtils', () => {
    var CasesData, ContactsCache, CasesUtils;

    beforeEach(module('civicase', 'civicase.data'));

    beforeEach(inject((_ContactsCache_, _CasesData_, _CasesUtils_) => {
      ContactsCache = _ContactsCache_;
      CasesData = _CasesData_;
      CasesUtils = _CasesUtils_;

      spyOn(ContactsCache, 'add');
    }));

    describe('fetchMoreContactsInformation()', () => {
      let contactsFetched, expectedContacts;

      beforeEach(() => {
        const cases = CasesData.get().values[0];

        cases.contacts = [{ contact_id: '1' }];

        CasesUtils.fetchMoreContactsInformation([cases]);

        expectedContacts = ['1', '170', '202'];
        contactsFetched = _.uniq(ContactsCache.add.calls.mostRecent().args[0]);
      });

      it('fetches all contacts of the case', () => {
        expect(contactsFetched).toEqual(expectedContacts);
      });
    });

    describe('getAllCaseClientContactIds()', () => {
      let cases;

      beforeEach(() => {
        cases = CasesData.get().values[0];
      });

      it('fetches all client contact ids of the case', () => {
        expect(CasesUtils.getAllCaseClientContactIds(cases.contacts)).toEqual(['170']);
      });
    });
  });
})(CRM._);
