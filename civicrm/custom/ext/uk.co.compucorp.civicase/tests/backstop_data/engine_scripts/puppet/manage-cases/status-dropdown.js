'use strict';

const Utility = require('./../utility.js');

module.exports = async (page, scenario, vp) => {
  const utility = new Utility(page, scenario, vp);

  await utility.waitForAngular();
  await utility.waitForLoadingComplete();
  await page.click('.civicase__case-header__action-menu .btn-group:first-child .btn');
  // There is no direct way to wait for the dropdown options to be loaded as they
  // are loaded from another REST API call. So, we check if there are any `ng-repeat`
  // li element.
  await page.waitForSelector('.civicase__case-header__action-menu .btn-group:first-child .dropdown-menu li[ng-repeat]');
};
