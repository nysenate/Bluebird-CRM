'use strict';

const Utility = require('./../utility.js');

module.exports = async (page, scenario, vp) => {
  const utility = new Utility(page, scenario, vp);

  console.log(scenario.url);

  await require('./status-dropdown')(page, scenario, vp);
  await page.click('.civicase__case-header__action-menu .btn-group:first-child .dropdown-menu li:nth-child(2) a');
  await utility.waitForUIModalLoad();
};
