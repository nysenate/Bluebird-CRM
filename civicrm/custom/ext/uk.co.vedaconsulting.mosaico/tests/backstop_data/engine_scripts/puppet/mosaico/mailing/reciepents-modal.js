'use strict';

module.exports = async (engine, scenario, viewport) => {
  await require('./step-1.js') (engine, scenario, viewport);
  await engine.waitFor(() => document.querySelector('a[title="Preview a List of Recipients"]').innerHTML !== 'Estimating...');
  await engine.click('a[title="Preview a List of Recipients"]');
  await engine.waitFor('.ui-dialog');
  // wait for modal to readjust
  await engine.waitFor(200);
}
