'use strict';

module.exports = async (page, scenario, vp) => {
  await require('./bulk-actions')(page, scenario, vp);

  await page.click('.civicase__bulkactions-actions-dropdown .dropdown-menu li:nth-child(3) a');

  await page.waitForTimeout(1000);
};
