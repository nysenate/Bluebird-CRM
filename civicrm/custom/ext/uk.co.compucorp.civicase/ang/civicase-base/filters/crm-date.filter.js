(function ($, angular) {
  var module = angular.module('civicase-base');

  module.filter('civicaseCrmDate', crmDate);

  /**
   * CRM Date filter.
   *
   * @param {string} dateInputFormatValue A jQuery UI date format as provided
   *   by the value stored in CiviCRM.
   * @returns {Function} the date filter.
   */
  function crmDate (dateInputFormatValue) {
    return crmDateFilter;

    /**
     * Formats the given date string using the format stored in CiviCRM.
     *
     * @param {string} dateString A date string in the "yyyy-mm-dd" format.
     * @returns {string} The formatted date.
     */
    function crmDateFilter (dateString) {
      return $.datepicker.formatDate(
        dateInputFormatValue,
        moment(dateString).toDate()
      );
    }
  }
})(CRM.$, angular);
