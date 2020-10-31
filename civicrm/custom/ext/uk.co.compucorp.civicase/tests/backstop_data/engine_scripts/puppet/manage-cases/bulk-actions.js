'use strict';

const Utility = require('./../utility.js');

module.exports = async (page, scenario, vp) => {
  const utility = new Utility(page, scenario, vp);

  await utility.waitForAngular();
  await utility.waitForLoadingComplete('.civicase__case-list-panel');
  await page.click('.civicase__case-list-table__header .civicase__bulkactions-checkbox-toggle');
  // // this waits for the animation to finish before continue:
  await page.waitFor(300);
  await utility.clickAll('.civicase__checkbox--bulk-action');
  await page.click('.civicase__case-list-table__header .civicase__bulkactions-actions-dropdown .btn:first-child');
};
