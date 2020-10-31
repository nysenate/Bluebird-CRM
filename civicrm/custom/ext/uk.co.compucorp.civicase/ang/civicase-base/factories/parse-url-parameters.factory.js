(function (_, angular) {
  var module = angular.module('civicase-base');

  module.factory('parseUrlParameters', function () {
    return parseUrlParameters;

    /**
     * @param {string} url the URL to use for parsing its parameter
     * @returns {object} returns the given URL's parameters as an object.
     */
    function parseUrlParameters (url) {
      var urlParamPairs = url.split('?')
        .slice(1)
        .join('')
        .split('&')
        .map(function (paramNameAndValue) {
          return paramNameAndValue.split('=');
        });

      return _.zipObject(urlParamPairs);
    }
  });
})(CRM._, angular);
