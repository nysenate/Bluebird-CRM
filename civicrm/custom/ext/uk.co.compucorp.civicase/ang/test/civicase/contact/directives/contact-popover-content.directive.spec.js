(function (_) {
  describe('ContactPopoverContent', function () {
    var $controller, $rootScope, $scope, contactsDataServiceMock, civicaseCrmUrl;
    var mockContact = { id: _.uniqueId() };

    beforeEach(module('civicase', function ($provide) {
      contactsDataServiceMock = jasmine.createSpyObj('contactsDataService', ['getCachedContact']);
      contactsDataServiceMock.getCachedContact.and.returnValue(mockContact);

      $provide.value('ContactsCache', contactsDataServiceMock);
    }));

    beforeEach(inject(function (_civicaseCrmUrl_, _$controller_, _$rootScope_) {
      civicaseCrmUrl = _civicaseCrmUrl_;
      $controller = _$controller_;
      $rootScope = _$rootScope_;
    }));

    describe('when the directive initializes', function () {
      beforeEach(function () {
        initController({
          contactId: mockContact.id
        });
      });

      it('requests the contact information', function () {
        expect(contactsDataServiceMock.getCachedContact).toHaveBeenCalledWith($scope.contactId);
      });

      it('stores the contact information', function () {
        expect($scope.contact).toEqual(mockContact);
      });
    });

    describe('getting the email URL', () => {
      describe('when a case id is not provided', () => {
        beforeEach(() => {
          initController({
            caseId: null,
            contactId: mockContact.id
          });

          $scope.getEmailUrl();
        });

        it('returns the URL for sending a standalone email activity', () => {
          expect(civicaseCrmUrl).toHaveBeenCalledWith('civicrm/activity/email/add', {
            action: 'add',
            cid: $scope.contactId,
            reset: 1
          });
        });
      });

      describe('when a case id is provided', () => {
        beforeEach(() => {
          initController({
            caseId: _.uniqueId(),
            contactId: mockContact.id
          });

          $scope.getEmailUrl();
        });

        it('returns the URL for sending a case email activity', () => {
          expect(civicaseCrmUrl).toHaveBeenCalledWith('civicrm/activity/email/add', {
            action: 'add',
            caseid: $scope.caseId,
            cid: $scope.contactId,
            reset: 1
          });
        });
      });
    });

    /**
     * Initializes the contact popover content controller for testing purposes.
     *
     * @param {object} scopeDefaultValues default values provided to the scope.
     */
    function initController (scopeDefaultValues) {
      $scope = $rootScope.$new();

      _.extend($scope, scopeDefaultValues);
      $controller('civicaseContactPopoverContentController', { $scope: $scope });
    }
  });
})(CRM._);
