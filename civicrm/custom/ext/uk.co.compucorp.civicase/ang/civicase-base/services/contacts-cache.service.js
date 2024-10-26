(function (angular, $, _) {
  var module = angular.module('civicase-base');

  module.service('ContactsCache', ContactsCache);

  /**
   * Contacts Cache Service
   *
   * @param {Function} civicaseCrmApi service to access civicrm api
   * @param {object} $q angular queue service
   * @param {object} $rootScope root scope object
   */
  function ContactsCache (civicaseCrmApi, $q, $rootScope) {
    var defer;
    var savedContacts = [];
    var savedContactDetails = {};
    var requiredContactFields = [
      'first_name',
      'last_name',
      'birth_date',
      'city',
      'contact_type',
      'display_name',
      'email',
      'gender_id',
      'image_URL',
      'postal_code',
      'state_province',
      'street_address',
      'tag'
    ];

    /**
     * Add data to the ContactsData service and fetches Profile Pic and Contact Type
     *
     * @param {Array} contacts list of contacts to be added
     * @returns {Promise} resolves to undefined when the information for the given contacts
     * has been fetched and stored.
     */
    this.add = function (contacts) {
      contacts = _.uniq(contacts);
      var newContacts = _.difference(contacts, savedContacts);
      savedContacts = savedContacts.concat(newContacts);

      if (newContacts.length === 0) {
        // if a previous API call is in progress wait for it to finish;
        return defer ? defer.promise : $q.resolve();
      }

      defer = $q.defer();

      return civicaseCrmApi('Contact', 'get', {
        sequential: 1,
        id: { IN: newContacts },
        return: requiredContactFields,
        options: { limit: 0 },
        'api.Phone.get': {
          contact_id: '$value.id',
          'phone_type_id.name': { IN: ['Mobile', 'Phone'] },
          return: ['phone', 'phone_type_id.name', 'location_type_id'],
          'api.LocationType.get': { id: '$value.location_type_id' }
        },
        'api.GroupContact.get': {
          contact_id: '$value.id',
          return: ['title']
        },
        'api.EntityTag.get': {
          entity_table: 'civicrm_contact',
          entity_id: '$value.id',
          return: ['tag_id.name', 'tag_id.description', 'tag_id.color']
        }
      }).then(function (data) {
        savedContactDetails = _.extend(savedContactDetails, _.indexBy(data.values, 'contact_id'));
        defer.resolve();
        $rootScope.$broadcast('civicase::contacts-cache::contacts-added');
      });
    };

    /**
     * Returns the cached information for the given contact.
     *
     * @param {string} contactID contact id
     * @returns {object} contact object of the passed contact ID.
     */
    this.getCachedContact = function (contactID) {
      var contact = _.clone(savedContactDetails[contactID]);

      if (!contact) {
        return null;
      }

      formatContactPhoneNumbers(contact);

      contact.groups = _.map(contact['api.GroupContact.get'].values, 'title').join(', ');
      contact.tags = (contact.tags + '').split(',').join(', '); // Adds spacing to the tags

      delete contact['api.Phone.get'];
      delete contact['api.GroupContact.get'];

      return contact;
    };

    /**
     * Returns the Profile Pic for the given contact id
     *
     * @param {string} contactID contact id
     * @returns {string} image url of the sent contact
     */
    this.getImageUrlOf = function (contactID) {
      return savedContactDetails[contactID] ? savedContactDetails[contactID].image_URL : '';
    };

    /**
     * Returns the Contact Type for the given contact id
     *
     * @param {string} contactID contact id
     * @returns {string} icon of the sent contact
     */
    this.getContactIconOf = function (contactID) {
      return savedContactDetails[contactID] ? savedContactDetails[contactID].contact_type : '';
    };

    /**
     * Formats the phone numbers in an array which can be used in the html
     *
     * @param {object} contact contact object
     */
    function formatContactPhoneNumbers (contact) {
      var phoneNumbers = [];

      _.each(contact['api.Phone.get'].values, function (numberObject) {
        phoneNumbers.push({
          type: numberObject['api.LocationType.get'].values[0].display_name + ' ' + numberObject['phone_type_id.name'],
          number: numberObject.phone || numberObject.mobile
        });
      });

      contact.phoneNumbers = phoneNumbers;
    }
  }
})(angular, CRM.$, CRM._);
