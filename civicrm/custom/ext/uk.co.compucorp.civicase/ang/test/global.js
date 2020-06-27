/* eslint-env jasmine */

(function (CRM) {
  CRM['civicase-base'] = {};
  CRM.civicase = {};
  CRM['civicase-base'].currentCaseCategory = 'cases';
  CRM.angular = { requires: {} };
  /**
   * Dependency Injection for civicase module, defined in ang/civicase.ang.php
   * For unit testing they needs to be mentioned here
   */
  CRM.angular.requires.civicase = ['civicase-base', 'crmAttachment', 'crmUi', 'ngRoute', 'angularFileUpload', 'bw.paging', 'crmRouteBinder', 'crmResource', 'ui.bootstrap', 'uibTabsetClass', 'dialogService'];
  CRM.angular.requires['civicase-base'] = ['crmUtil'];

  CRM.checkPerm = jasmine.createSpy('checkPerm');
  CRM.loadForm = jasmine.createSpy('loadForm');
  CRM.loadForm.and.returnValue({
    one: jasmine.createSpy(),
    on: jasmine.createSpy()
  });
  CRM.confirm = jasmine.createSpy('confirm');
  CRM.status = jasmine.createSpy('status');
  CRM.url = jasmine.createSpy('url').and.callFake((url, searchParamsObject) => {
    const searchParamsString = CRM._.chain(searchParamsObject)
      .map((value, key) => ({ key, value }))
      .sortBy('key')
      .map((parameter) => {
        return `${parameter.key}=${parameter.value}`;
      })
      .join('&')
      .value();

    return `${url}?${searchParamsString}`;
  });

  // Common utility functions for tests
  CRM.testUtils = {

    /**
     * Given a full url, it extracts the querystring parameters, making sure
     * to decode and parse any value that is an encoded JSON object
     *
     * @param {string} url url
     * @returns {object} parameters
     */
    extractQueryStringParams: function (url) {
      var queryString, paramsCouples;

      queryString = url.split('?')[1];

      if (!queryString) {
        return {};
      }

      paramsCouples = queryString.split('&');

      return paramsCouples.reduce(function (acc, couple) {
        var coupleKeyVal = couple.split('=');

        acc[coupleKeyVal[0]] = coupleKeyVal[1].match(/^%7B.+%7D/)
          ? JSON.parse(decodeURIComponent(coupleKeyVal[1]))
          : coupleKeyVal[1];

        return acc;
      }, {});
    }
  };
}(CRM));
