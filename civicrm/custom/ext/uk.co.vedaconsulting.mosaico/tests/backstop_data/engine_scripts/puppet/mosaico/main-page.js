'use strict';

module.exports = async (engine, scenario, viewport) => {
  await engine.waitFor('.crm-mosaico-page');
  // wait for images to load
  await engine.evaluate(async () => {
    const selectors = Array.from(document.querySelectorAll('img'));
    await Promise.all(selectors.map(img => {
      if (img.complete) {
        return;
      }

      return new Promise((resolve, reject) => {
        img.addEventListener('load', resolve);
        img.addEventListener('error', reject);
      });
    }));
  });
};
