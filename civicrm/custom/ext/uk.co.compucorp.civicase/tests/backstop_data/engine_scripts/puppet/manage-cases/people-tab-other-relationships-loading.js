'use strict';

const Utility = require('./../utility.js');

module.exports = async (page, scenario, vp) => {
  const utility = new Utility(page, scenario, vp);

  await utility.waitForAngular();
  await utility.waitForLoadingComplete();

  // Intercepts the server response before switching tabs so we can capture the loading state
  page.setRequestInterception(true);
  page.on('response', (response) => {
    response.abort();
  });

  await page.click(`.civicase__people-tab .nav-tabs .civicase__people-tab-link[ng-click="setTab('relations')"]`);
};
