'use strict';

const Utility = require('./../utility.js');

module.exports = async (page, scenario, vp) => {
  const utility = new Utility(page, scenario, vp);

  await require('./action-dropdown')(page, scenario, vp);
  await page.click('.civicase__case-header__action-menu .btn-group:last-child .dropdown-menu li .fa-file-excel-o');
  await utility.waitForUIModalLoad();
};
