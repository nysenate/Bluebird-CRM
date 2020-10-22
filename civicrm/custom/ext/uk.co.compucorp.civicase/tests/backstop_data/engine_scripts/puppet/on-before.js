'use strict';

const cv = require('civicrm-cv')({ mode: 'sync' });
const loadCookies = require('./load-cookies');

module.exports = async (page, scenario, vp) => {
  console.log('--------------------------------------------');
  console.log('Running Scenario "' + scenario.label + '" ' + scenario.count);

  // Execute api calls defined in the scenario
  if (scenario.apiCalls) {
    scenario.apiCalls.forEach((apiCall) => {
      cv('api ' + apiCall);
    });
  }

  await loadCookies(page, scenario);
};
