(function (angular) {
  var module = angular.module('civicase');

  module.filter('civicaseCrmUrl', function (civicaseCrmUrl) {
    /**
     * Returns the URL's complete path when provided a relative URL.
     *
     * @param {string} relativeUrl the relative URL path
     * @param {object} query additional query parameters to append to the URL
     * @returns {string} url
     */
    function crmUrlFilter (relativeUrl, query) {
      if (relativeUrl.startsWith('/')) {
        relativeUrl = relativeUrl.substr(1);
      }

      return civicaseCrmUrl(relativeUrl, query);
    }

    return crmUrlFilter;
  });
})(angular);
