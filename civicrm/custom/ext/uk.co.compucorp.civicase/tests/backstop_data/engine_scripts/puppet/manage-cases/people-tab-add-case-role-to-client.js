'use strict';

const Utility = require('./../utility.js');

module.exports = async (page, scenario, vp) => {
  const utility = new Utility(page, scenario, vp);

  await utility.waitForAngular();
  await utility.waitForLoadingComplete(".civicase__people-tab__sub-tab:not([ng-show=\"tab == 'relations'\"])");

  // For some reason page.click is not working for this dom element. So, we
  // have used evalute and triggered click using DOM events separately.
  await page.evaluate(() => {
    document.querySelector('.civicase__people-tab__search .btn').click();
  });

  await page.click('.civicase__people-tab__search .dropdown-menu li:nth-child(1) a');
};
