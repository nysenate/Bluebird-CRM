'use strict';

const Utility = require('./../utility.js');

module.exports = async (page, scenario, vp) => {
  const utility = new Utility(page, scenario, vp);

  await utility.waitForAngular();
  await utility.waitForLoadingComplete();
  await page.click('.civicase__activity-feed__list .civicase__activity-card');
  await page.waitFor('.blockUI.blockOverlay', { hidden: true });
};
