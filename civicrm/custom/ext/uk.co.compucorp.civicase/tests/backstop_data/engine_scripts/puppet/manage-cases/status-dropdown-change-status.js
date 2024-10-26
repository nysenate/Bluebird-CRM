'use strict';

module.exports = async (page, scenario, vp) => {
  await require('./status-dropdown')(page, scenario, vp);
  await page.click('.civicase__case-header__action-menu .btn-group:first-child .dropdown-menu li:nth-child(2) a');
  await page.waitForTimeout(1000);
};
