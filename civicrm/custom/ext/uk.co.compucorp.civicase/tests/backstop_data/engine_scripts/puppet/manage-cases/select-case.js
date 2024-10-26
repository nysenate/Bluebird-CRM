'use strict';

const Utility = require('./../utility.js');

module.exports = async (page, scenario, vp) => {
  const utility = new Utility(page, scenario, vp);

  await utility.waitForAngular();
  await utility.waitForLoadingComplete('.civicase__case-list-panel');
  // Evaluating the click using browser native click function
  // pupetter is faling to click on the correct div (Opening a random screen)
  await page.evaluate(() => {
    document.querySelector('.civicase__case-list-table tbody tr:first-child .civicase__case-card').click();
  });
  await utility.waitForLoadingComplete();
};
