'use strict';

const Utility = require('./../utility.js');

module.exports = async (page, scenario, vp) => {
  const utility = new Utility(page, scenario, vp);

  await page.waitFor('.blockUI.blockOverlay', { hidden: true });
  await page.waitForSelector('#civicaseActivitiesTab #bootstrap-theme .civicase__activity-feed');
  await utility.waitForLoadingComplete();
};
