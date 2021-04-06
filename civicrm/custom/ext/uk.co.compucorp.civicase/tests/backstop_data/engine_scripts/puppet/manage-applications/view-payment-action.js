'use strict';

const Utility = require('../utility.js');

module.exports = async (page, scenario, vp) => {
  const utility = new Utility(page, scenario, vp);
  await page.click('[ng-repeat="payment in payments"]:nth-child(1) .dropdown-toggle');
  await utility.waitForAndClick('civicase-case-list-table +.dropdown-menu [ng-click="handleViewActivity(payment.id)"]');
  await page.waitForTimeout(2000);
};
