'use strict';

module.exports = async (engine, scenario, viewport) => {
  await Promise.all([
    engine.click('a[title="Continue Mailing"]'),
    engine.waitForNavigation()
  ]);
  await engine.waitFor('form[name="crmMailing"].ng-scope.ng-dirty');
}
