'use strict';
const Utility = require('../utility.js');

module.exports = async (page, scenario, vp) => {
  const utility = new Utility(page, scenario, vp);
  // We override the height of the viewport so the Edit button is visible
  const viewPortOverride = Object.assign(page.viewport(), { height: 2000 });

  await page.setViewport(viewPortOverride);

  await require('./activity-detail')(page, scenario, vp);
  await utility.waitForAndClick('.crm-submit-buttons.panel-footer > .edit.button');
  await page.waitForSelector('.blockUI.blockOverlay', { hidden: true });
  await page.waitForTimeout(1000);
  await utility.openAllAccordions();
  await page.waitForTimeout(1000);
};
