'use strict';

const Utility = require('./../utility.js');

module.exports = async (page, scenario, vp) => {
  const utility = new Utility(page, scenario, vp);

  await utility.waitForAngular();
  await utility.waitForLoadingComplete();

  await page.click('.civicase__activity-feed-pager__more .btn');
  await page.waitForSelector('.civicase__activity-feed-pager__more .civicase__spinner', { hidden: true });
};
