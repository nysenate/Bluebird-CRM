const fs = require('fs');

var BACKSTOP_DIR = '.';
var FILES = {
  siteConfig: 'site-config.json',
  siteConfigSample: 'site-config.json.sample',
  temp: 'backstop.temp.json',
  tpl: 'backstop.tpl.json'
};

module.exports = {
  BACKSTOP_DIR: BACKSTOP_DIR,
  FILES: FILES,
  getSiteConfig: getSiteConfig
};

/**
 * Returns the content of site config file
 *
 * @returns {object} site config object
 */
function getSiteConfig () {
  return JSON.parse(fs.readFileSync(FILES.siteConfig));
}
