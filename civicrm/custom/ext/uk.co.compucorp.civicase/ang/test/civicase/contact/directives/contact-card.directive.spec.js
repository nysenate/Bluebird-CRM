/* eslint-env jasmine */
(function ($) {
  describe('contactCard', function () {
    var element, civicaseCrmApi, $q, $compile, $rootScope, $scope, ContactsData, ContactsCache;

    beforeEach(module('civicase.templates', 'civicase', 'civicase.data', function ($provide) {
      $provide.value('civicaseCrmApi', jasmine.createSpy('civicaseCrmApi'));
    }));

    beforeEach(inject(function (_$compile_, _$rootScope_, _$q_, _civicaseCrmApi_,
      _ContactsData_, _ContactsCache_) {
      $q = _$q_;
      $compile = _$compile_;
      $rootScope = _$rootScope_;
      civicaseCrmApi = _civicaseCrmApi_;
      ContactsData = _ContactsData_;
      ContactsCache = _ContactsCache_;
      $scope = $rootScope.$new();
    }));

    describe('basic tests', function () {
      beforeEach(function () {
        spyOn(ContactsCache, 'add').and.returnValue($q.resolve(ContactsData.values[0]));
        compileDirective(false);
      });

      it('complies the Action directive', function () {
        expect(element.html()).toContain('<!-- Contact Icons -->');
      });
    });

    describe('when contacts data are set', function () {
      beforeEach(function () {
        spyOn(ContactsCache, 'add').and.returnValue($q.resolve(ContactsData.values[0]));
        compileDirective(false, [ContactsData.values[0].contact_id]);
      });

      it('displays the contact', function () {
        expect(element.isolateScope().contacts).toEqual([ContactsData.values[0].contact_id]);
      });
    });

    describe('when the contact card is of avatar type', function () {
      describe('when the display name is a name', function () {
        beforeEach(function () {
          spyOn(ContactsCache, 'add').and.returnValue($q.resolve(ContactsData.values[0]));
          spyOn(ContactsCache, 'getCachedContact').and.returnValue({
            display_name: 'John Doe',
            first_name: 'John',
            last_name: 'Doe'
          });
          compileDirective(true, ContactsData.values[0].id);
        });

        it('sets the initial of the name as the avatar', function () {
          expect(element.isolateScope().contacts[0].avatar).toBe('JD');
        });
      });

      describe('when the display name is an email', function () {
        beforeEach(function () {
          spyOn(ContactsCache, 'add').and.returnValue($q.resolve(ContactsData.values[0]));
          spyOn(ContactsCache, 'getCachedContact').and.returnValue({ display_name: 'example@example.com' });
          compileDirective(true, ContactsData.values[0].id);
        });

        it('sets the first letter of the email address as the avatar', function () {
          expect(element.isolateScope().contacts[0].avatar).toBe('E');
        });
      });

      describe('when the display name contains both prefix and suffix honorific', function () {
        beforeEach(function () {
          spyOn(ContactsCache, 'add').and.returnValue($q.resolve(ContactsData.values[0]));
          spyOn(ContactsCache, 'getCachedContact').and.returnValue({
            display_name: 'Mr. John Doe Jr.',
            first_name: 'John',
            last_name: 'Doe'
          });

          compileDirective(true, ContactsData.values[0].id);
        });

        it('ignores the honorific while creating the avatar', function () {
          expect(element.isolateScope().contacts[0].avatar).toBe('JD');
        });
      });

      describe('image url', function () {
        beforeEach(function () {
          civicaseCrmApi.and.returnValue($q.resolve(ContactsData));
          ContactsCache.add(ContactsData.values);
          compileDirective(true, ContactsData.values[0].contact_id, ContactsData.values[0].display_name);
        });

        it('sets the image url for the sent contact', function () {
          expect(element.isolateScope().contacts[0].image_URL).toBe(ContactsData.values[0].image_URL);
        });
      });
    });

    /**
     * Compiles the contact card directive.
     *
     * @param {boolean} isAvatar is avatar
     * @param {number} contactID contact id
     */
    function compileDirective (isAvatar, contactID) {
      element = $compile('<div civicase-contact-card contacts="contacts" avatar="isAvatar">')($scope);
      $scope.isAvatar = isAvatar;
      $scope.contacts = contactID || [ContactsData.values[0]];
      $scope.$digest();
    }
  });
}(CRM.$));
