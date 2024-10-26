'use strict';

const Utility = require('../utility.js');

module.exports = async (page, scenario, vp) => {
  const utility = new Utility(page, scenario, vp);
  // We override the height of the viewport so the Edit button is visible
  const viewPortOverride = Object.assign(page.viewport(), { height: 2000 });

  await page.setViewport(viewPortOverride);

  await page.click('.civicase__case-tab__actions button');
  await page.waitForTimeout(2000);
  await utility.openAllAccordions();
};
