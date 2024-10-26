'use strict';

const Utility = require('./utility.js');

module.exports = async (options) => {
  const actions = [
    { name: 'hoverSelector', execute: hoverSelectorAction },
    { name: 'hoverSelectors', execute: hoverSelectorAction },
    { name: 'clickSelector', execute: clickSelectorAction },
    { name: 'clickSelectors', execute: clickSelectorAction }
  ];

  for (const action of actions) {
    const scenarioHasAction = !!options.scenario[action.name];

    scenarioHasAction && await action.execute(options);
  }
};

/**
 * Action handler for hover event
 *
 * @param {object} options options
 * @param {object} options.page pupettter engine object
 * @param {object} options.scenario object of each scenario
 * @param {object} options.viewport viewport configurations
 */
async function hoverSelectorAction ({ page, scenario, viewport }) {
  const hoverSelectors = scenario.hoverSelectors || [scenario.hoverSelector];
  const utility = new Utility(page, scenario, viewport);

  for (const hoverSelector of hoverSelectors) {
    await utility.waitForAndHover(hoverSelector);

    if (scenario.waitForAjaxComplete) {
      await utility.waitForLoadingComplete();
    }
  }
}

/**
 * Action handler for click event
 *
 * @param {object} options options
 * @param {object} options.page pupettter engine object
 * @param {object} options.scenario object of each scenario
 * @param {object} options.viewport viewport configurations
 * @param {boolean} options.wait whether to apply additional wait
 */
async function clickSelectorAction ({ page, scenario, viewport, wait = 1000 }) {
  const clickSelectors = scenario.clickSelectors || [scenario.clickSelector];
  const utility = new Utility(page, scenario, viewport);

  for (const clickSelector of clickSelectors) {
    await utility.waitForAndClick(clickSelector);

    if (scenario.waitForAjaxComplete) {
      await utility.waitForLoadingComplete();
    }

    if (scenario.waitForUIModalLoad) {
      await utility.waitForUIModalLoad();
    }

    await page.waitForTimeout(wait);
  }
}
