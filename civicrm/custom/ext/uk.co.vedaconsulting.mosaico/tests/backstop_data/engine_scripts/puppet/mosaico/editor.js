'use strict';

module.exports = async (engine, scenario, viewport) => {
  await require('./main-page-hover-state.js')(engine, scenario, viewport);
  await engine.click('.crm-mosaico-page > div:last-child .thumbnail a[title="Edit"]');
  await engine.waitFor('.status-start', { hidden: true });
};
