'use strict';

module.exports = async (engine, scenario, viewport) => {
  await require('./main-page.js')(engine, scenario, viewport);
  await engine.hover('.crm-mosaico-page > div:last-child .thumbnail');
};
