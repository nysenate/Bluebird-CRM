'use strict';

module.exports = async (page, scenario, vp) => {
  await require('./activity-detail')(page, scenario, vp);

  await page.click('.civicase__activity-panel__priority-dropdown');
};
