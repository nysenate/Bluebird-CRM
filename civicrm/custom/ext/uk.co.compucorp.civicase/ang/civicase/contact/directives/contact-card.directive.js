(function (angular, $, _, CRM) {
  var module = angular.module('civicase');

  module.directive('civicaseContactCard', function ($document, ContactsCache) {
    return {
      restrict: 'A',
      replace: true,
      controller: civicaseContactCardController,
      templateUrl: '~/civicase/contact/directives/contact-card.directive.html',
      scope: {
        caseId: '<?',
        data: '=contacts',
        isAvatar: '=avatar',
        noIcon: '='
      }
    };

    /**
     * Contact Card directive's controller
     *
     * @param {object} $scope scope object reference.
     */
    function civicaseContactCardController ($scope) {
      $scope.url = CRM.url;
      $scope.mainContact = null;

      (function init () {
        $scope.$watch('data', refresh);
        $scope.$on('civicase::contacts-cache::contacts-added', refresh);
      }());

      /**
       * Watch function for data refresh
       */
      function refresh () {
        $scope.contacts = [];

        if (_.isPlainObject($scope.data)) {
          _.each($scope.data, function (name, contactID) {
            if ($scope.isAvatar) {
              prepareAvatarData(name, contactID);
            } else {
              $scope.contacts.push({ display_name: name, contact_id: contactID });
            }
          });
        } else if (typeof $scope.data === 'string') {
          if ($scope.isAvatar) {
            prepareAvatarData(
              ContactsCache.getCachedContact($scope.data).display_name,
              $scope.data
            );
          } else {
            $scope.contacts.push({
              contact_id: $scope.data,
              display_name: ContactsCache.getCachedContact($scope.data).display_name
            });
          }
        } else {
          $scope.contacts = _.cloneDeep($scope.data);
        }
      }

      /**
       * Get initials from the sent parameter
       * Example: JD should be returned for John Doe
       *
       * @param {string} contactFullName the contact's full name.
       *
       * @returns {string} the contact's initials.
       */
      function getInitials (contactFullName) {
        var names = contactFullName.split(' ');
        var initials = names[0].substring(0, 1).toUpperCase();

        if (names.length > 1) {
          initials += names[names.length - 1].substring(0, 1).toUpperCase();
        }

        return initials;
      }

      /**
       * Prepares data when the directive is avatar
       *
       * @param {string} name the contact's full name.
       * @param {string} contactID the contact's id.
       */
      function prepareAvatarData (name, contactID) {
        var avatarText;

        if (validateEmail(name)) {
          avatarText = name.substr(0, 1).toUpperCase();
        } else {
          avatarText = getInitials(name);
        }

        $scope.contacts.push({
          display_name: name,
          contact_id: contactID,
          avatar: avatarText,
          image_URL: ContactsCache.getImageUrlOf(contactID)
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
