'use strict';

const Utility = require('./../utility.js');

module.exports = async (page, scenario, vp) => {
  const utility = new Utility(page, scenario, vp);

  await utility.waitForAngular();
  await utility.waitForLoadingComplete('.civicase__case-list-panel');
  await page.click('.civicase__case-list-table__header .civicase__case-sort-dropdown > a');
};
