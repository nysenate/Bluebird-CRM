(function (angular, $, _, CRM) {
  var module = angular.module('civicase');

  module.service('Contact', function () {
    /**
     * Returns contact id which is currently being viewed
     *
     * @returns {string} id of the current user
     */
    this.getCurrentContactID = function () {
      var url = new URL(window.location.href);

      return url.searchParams.get('cid') !== null ? url.searchParams.get('cid') : CRM.config.user_contact_id;
    };
  });
})(angular, CRM.$, CRM._, CRM);
