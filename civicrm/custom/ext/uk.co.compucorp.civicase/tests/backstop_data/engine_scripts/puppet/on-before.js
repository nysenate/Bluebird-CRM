'use strict';

const cv = require('civicrm-cv')({ mode: 'sync' });
const loadCookies = require('./load-cookies');
var fs = require('fs');

module.exports = async (page, scenario, vp) => {
  console.log('--------------------------------------------');
  console.log('Running Scenario "' + scenario.label + '" ' + scenario.count);

  var cwd = JSON.parse(fs.readFileSync('site-config.json')).root;

  // Execute api calls defined in the scenario
  if (scenario.apiCalls) {
    scenario.apiCalls.forEach((apiCall) => {
      cv(`--cwd=${cwd} api ${apiCall}`);
    });
  }

  await loadCookies(page, scenario);
};
