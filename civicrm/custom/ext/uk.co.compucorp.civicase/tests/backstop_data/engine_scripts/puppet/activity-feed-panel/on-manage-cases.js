'use strict';

const Utility = require('./../utility.js');

module.exports = async (page, scenario, vp) => {
  const utility = new Utility(page, scenario, vp);

  await require('./../manage-cases/select-case.js')(page, scenario, vp);

  await page.click('.civicase__case-body_tab > li:nth-child(2) a');
  await utility.waitForLoadingComplete();
  await page.waitFor(500);
};
