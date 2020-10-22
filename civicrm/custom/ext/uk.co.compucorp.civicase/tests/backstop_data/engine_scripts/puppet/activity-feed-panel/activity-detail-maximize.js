'use strict';

const Utility = require('./../utility.js');

module.exports = async (page, scenario, vp) => {
  const utility = new Utility(page, scenario, vp);

  await require('./activity-detail')(page, scenario, vp);

  await page.click('.civicase__activity-panel__maximise');
  await utility.waitForUIModalLoad();
};
