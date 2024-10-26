(function (_, angular) {
  var module = angular.module('civicase-base');

  module.provider('UrlParameters', UrlParametersProvider);

  /**
   * Url Parameters provider
   */
  function UrlParametersProvider () {
    this.$get = $get;
    this.parse = parse;

    /**
     * Returns an instance of the url parameters service.
     *
     * @returns {Function} the case type service.
     */
    function $get () {
      return {
        parse: parse
      };
    }

    /**
     * @param {string} url the URL to use for parsing its parameter
     * @returns {object} returns the given URL's parameters as an object.
     */
    function parse (url) {
      var urlParamPairs = url.split('?')
        .slice(1)
        .join('')
        .split('&')
        .map(function (paramNameAndValue) {
          return paramNameAndValue.split('=');
        });

      return _.zipObject(urlParamPairs);
    }
  }
})(CRM._, angular);
