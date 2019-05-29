'use strict';

module.exports = async (engine, scenario, viewport) => {
  await require('./step-1.js') (engine, scenario, viewport);
  await engine.click('.crm_wizard .nav > li:nth-child(2) > a.ng-binding');
}
