'use strict';

module.exports = async (engine, scenario, viewport) => {
  await require('./step-1.js')(engine, scenario, viewport);
  await engine.click('button[on-yes="delete()"]');
  await engine.waitFor('.ui-dialog');
  // wait for modal to readjust
  await engine.waitFor(200);
}
