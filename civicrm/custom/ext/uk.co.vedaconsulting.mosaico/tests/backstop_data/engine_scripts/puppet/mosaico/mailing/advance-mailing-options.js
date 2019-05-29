'use strict';

module.exports = async (engine, scenario, viewport) => {
  await require('./step-3.js') (engine, scenario, viewport);
  await engine.click('button[ng-click="openAdvancedOptions(mailing)"]');
  await engine.waitFor('.ui-dialog');
  //wait for screen to adjust
  await engine.waitFor(200);
}
