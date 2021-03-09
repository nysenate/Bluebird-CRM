'use strict';

module.exports = async (page, scenario, vp) => {
  await require('./bulk-actions')(page, scenario, vp);

  await page.click('.civicase__bulkactions-actions-dropdown .dropdown-menu li:nth-child(4) a');

  await page.waitFor(500);
};
