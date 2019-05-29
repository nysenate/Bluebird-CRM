'use strict';

module.exports = async (engine, scenario, viewport) => {
  await require('./editor-test.js') (engine, scenario, viewport);
  await engine.click('button[ng-click="doPreview(\'text\')"]');
  await engine.waitFor('.status-start', { hidden: true });
  // wait for Modal to re adjust
  await engine.waitFor(300);
}
