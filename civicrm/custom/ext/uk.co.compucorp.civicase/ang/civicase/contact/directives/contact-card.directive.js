(function (angular, $, _, CRM) {
  var module = angular.module('civicase');

  module.directive('civicaseContactCard', function (ContactsCache) {
    return {
      restrict: 'A',
      replace: true,
      controller: civicaseContactCardController,
      templateUrl: '~/civicase/contact/directives/contact-card.directive.html',
      scope: {
        caseId: '<?',
        data: '=contacts',
        totalContacts: '=',
        isAvatar: '=avatar',
        noIcon: '=',
        displayMoreFields: '=',
        showFullNameOnHover: '<'
      }
    };

    /**
     * Contact Card directive's controller
     *
     * @param {object} $scope scope object reference.
     * @param {Function} civicaseCrmUrl crm url service.
     */
    function civicaseContactCardController ($scope, civicaseCrmUrl) {
      $scope.url = civicaseCrmUrl;
      $scope.mainContact = null;

      (function init () {
        $scope.$watch('data', refresh);
        $scope.$on('civicase::contacts-cache::contacts-added', refresh);
      }());

      /**
       * Watch function for data refresh
       */
      function refresh () {
        fetchContactsInfo()
          .then(function () {
            $scope.contacts = [];

            if (_.isPlainObject($scope.data)) {
              _.each($scope.data, function (name, contactID) {
                if ($scope.isAvatar) {
                  prepareAvatarData(ContactsCache.getCachedContact(contactID));
                } else {
                  $scope.contacts.push({ display_name: name, contact_id: contactID });
                }
              });
            } else if (typeof $scope.data === 'string') {
              if ($scope.isAvatar) {
                prepareAvatarData(ContactsCache.getCachedContact($scope.data));
              } else {
                $scope.contacts = [{
                  contact_id: $scope.data,
                  display_name: ContactsCache.getCachedContact($scope.data).display_name
                }];
              }
            } else {
              $scope.contacts = _.cloneDeep($scope.data);
            }
          });
      }

      /**
       * Fetch the contacts information
       *
       * @returns {Promise} promise
       */
      function fetchContactsInfo () {
        var contactIds;

        if (typeof $scope.data === 'string') {
          contactIds = [$scope.data];
        } else if (_.isPlainObject($scope.data)) {
          contactIds = _.keys($scope.data);
        } else {
          contactIds = _.chain($scope.data)
            .compact()
            .map('contact_id')
            .value();
        }

        return ContactsCache.add(contactIds);
      }

      /**
       * Get initials from the sent parameter
       * Example: JD should be returned for John Doe
       *
       * @param {object} contactObj the contact object.
       *
       * @returns {string} the contact's initials.
       */
      function getInitials (contactObj) {
        // for organisation contact types
        if (contactObj.first_name || contactObj.last_name) {
          return contactObj.first_name.substring(0, 1).toUpperCase() +
            contactObj.last_name.substring(0, 1).toUpperCase();
        } else {
          var names = contactObj.display_name.split(' ');
          var initials = names[0].substring(0, 1).toUpperCase();

          if (names.length > 1) {
            initials += names[names.length - 1].substring(0, 1).toUpperCase();
          }

          return initials;
        }
      }

      /**
       * Prepares data when the directive is avatar
       *
       * @param {object} contactObj the contact object.
       */
      function prepareAvatarData (contactObj) {
        var avatarText;

        if (validateEmail(contactObj.display_name)) {
          avatarText = contactObj.display_name.substr(0, 1).toUpperCase();
        } else {
          avatarText = getInitials(contactObj);
        }

        $scope.contacts.push({
          display_name: contactObj.display_name,
          contact_id: contactObj.contact_id,
          avatar: avatarText,
          image_URL: ContactsCache.getImageUrlOf(contactObj.contact_id)
        });
      }

      /**
       * Checks whether the sent parameter is a valid email address
       *
       * @param {string} email the contact's email.
       *
       * @returns {boolean} true when the email is valid.
       */
      function validateEmail (email) {
        var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()\\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

        return re.test(String(email).toLowerCase());
      }
    }
  });
})(angular, CRM.$, CRM._, CRM);
