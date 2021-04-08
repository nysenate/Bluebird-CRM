'use strict';

const Utility = require('./../utility.js');

module.exports = async (page, scenario, vp) => {
  const utility = new Utility(page, scenario, vp);

  await utility.waitForAngular();
  await utility.waitForLoadingComplete();

  const input = await page.$('#civicase__file-upload-button');

  await input.uploadFile('sample.txt');
  await page.waitForTimeout(1000);
};
