'use strict';

module.exports = async (engine, scenario, viewport) => {
  await require('./advance-mailing-options.js') (engine, scenario, viewport);
  await engine.click('a[href="#publication"]');
  //wait for screen to adjust
  await engine.waitFor(200);
}
