'use strict';

module.exports = async (engine, scenario, viewport) => {
  await require('./step-2.js') (engine, scenario, viewport);
  await engine.waitFor('a[title="Edit"].ng-binding', { visible: true });
  //  wait for MosaicoCtrl to get mailing template. 
  // Since Mosaico shows the application and asynchronously loads the tempaltes (crmMosaico/MixinCtrl:113). 
  // Backstop needs to wait for a random long time (assuming that variable is resolved meanwhile)
  await engine.waitFor(500);
  await engine.click('a[title="Edit"].ng-binding');
  await engine.waitFor('.status-start', { hidden: true });
  await engine.waitFor('body> iframe.ui-front', { visible: true });
  
  const frames = await engine.frames();
  const frame = frames.find(f => f._navigationURL .match(/civicrm\/mosaico\/iframe\?snippet\=1/g) !== null);
  const testButton = await frame.$('a[title="Show preview and send test"]');
  
  await testButton.click();
  // wait for modal to adjust
  await engine.waitFor(500);
}
