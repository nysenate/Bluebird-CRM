module.exports = async (page, scenario, vp) => {
  console.log('--------------------------------------------');
  console.log('Running Scenario "' + scenario.label + '" ' + scenario.count);
    await require('./loadCookies')(page, scenario);
    await require('./clickAndHoverHelper')(page, scenario);
};
