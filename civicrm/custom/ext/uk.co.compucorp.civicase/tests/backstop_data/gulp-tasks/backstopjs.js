/**
 * @file
 * Contains functional configurations for setting up backstopJS test suite
 */

'use strict';

var _ = require('lodash');
var argv = require('yargs').argv;
var backstopjs = require('backstopjs');
var clean = require('gulp-clean');
var colors = require('ansi-colors');
var execSync = require('child_process').execSync;
var file = require('gulp-file');
var fs = require('fs');
var gulp = require('gulp');
var path = require('path');
var PluginError = require('plugin-error');
var puppeteer = require('puppeteer');

const CONFIGS = require('././utils/configs.js');
const replaceUrlVars = require('././utils/replace-url-vars.js');

var LOGGED_IN_USER_NAME = 'admin';

/**
 * Returns the list of the scenarios from
 *   a. All the different groups if `group` is == '_all_',
 *   b. Only the given group
 *
 * @param {string} group of scenarios
 * @param {Array} subfolderPaths sub folder paths
 * @returns {Array} of the list of the scenarios
 */
function buildScenariosList (group, subfolderPaths) {
  var scenarioIndex = 1;

  return _.chain(subfolderPaths)
    .map(function (subfolderPath) {
      const dirPath = 'scenarios/' + subfolderPath;

      return _(fs.readdirSync(dirPath))
        .filter(scenario => {
          return (group === '_all_' ? true : scenario === `${group}.json`) && scenario.endsWith('.json');
        })
        .map(scenario => {
          return JSON.parse(fs.readFileSync(path.join(dirPath, scenario))).scenarios;
        })
        .flatten()
        .value();
    })
    .flatten()
    .map((scenario, index, scenarios) => {
      const url = replaceUrlVars(scenario.url);

      return _.assign({}, scenario, {
        cookiePath: path.join('cookies', 'admin.json'),
        count: '(' + (scenarioIndex++) + ' of ' + scenarios.length + ')',
        url: url
      });
    })
    .value();
}

/**
 * Removes the temp config file and sends a notification
 * based on the given outcome from BackstopJS
 */
function cleanUp () {
  gulp
    .src(CONFIGS.FILES.temp, { read: false })
    .pipe(clean());
}

/**
 * Creates the content of the config temporary file that will be fed to BackstopJS
 * The content is the mix of the config template and the list of scenarios
 * under the scenarios/ folder
 *
 * @returns {string} of content for the config
 */
function createTempConfig () {
  var group = argv.group ? argv.group : '_all_';
  var list = buildScenariosList(group, ['civicase', 'civiawards']);
  var content = JSON.parse(fs.readFileSync(CONFIGS.FILES.tpl));

  content.scenarios = list.map((item) => {
    item.delay = item.delay || 2000;

    return item;
  });

  return JSON.stringify(content);
}

/**
 * Runs backstopJS with the given command.
 *
 * It fills the template file with the list of scenarios, creates a temp
 * file passed to backstopJS, then removes the temp file once the command is completed
 *
 * @param  {string} command for the backstop task
 * @returns {Promise} for the backstop task
 */
function runBackstopJS (command) {
  if (touchSiteConfigFile()) {
    throwError(
      'No site-config.json file detected!\n' +
      '\tOne has been created for you \n' +
      '\tPlease insert the real value for each placeholder and try again'
    );
  }

  return new Promise((resolve, reject) => {
    let success = false;

    gulp.src(CONFIGS.FILES.tpl)
      .pipe(file(path.basename(CONFIGS.FILES.temp), createTempConfig()))
      .pipe(gulp.dest('.'))
      .on('end', async () => {
        try {
          (typeof argv.skipCookies === 'undefined') && await writeCookies();

          await backstopjs(command, { configPath: CONFIGS.FILES.temp, filter: argv.filter });

          success = true;
        } finally {
          cleanUp();

          success ? resolve() : reject(new Error('BackstopJS error'));
        }
      });
  })
    .catch(function (err) {
      throwError(err.message);
    });
}

/**
 * Creates the site config file is in the backstopjs folder, if it doesn't exists yet
 *
 * @returns {boolean} Whether the file had to be created or not
 */
function touchSiteConfigFile () {
  let created = false;

  try {
    fs.readFileSync(CONFIGS.FILES.siteConfig);
  } catch (err) {
    fs.copyFileSync(CONFIGS.FILES.siteConfigSample, CONFIGS.FILES.siteConfig);

    created = true;
  }

  return created;
}

/**
 * A simple wrapper for displaying errors
 * It converts the tab character to the amount of spaces required to correctly
 * align a multi-line block of text horizontally
 *
 * @param {string} msg to be displayed
 * @throws {Error} of the plugin
 */
function throwError (msg) {
  throw new PluginError('Error', {
    message: colors.red(msg.replace(/\t/g, '    '))
  });
}

/**
 * Writes the session cookie files that will be used to log in as different users
 *
 * It uses the [`drush uli`](https://drushcommands.com/drush-7x/user/user-login/)
 * command to generate a one-time login url, the browser then go to that url
 * which then creates the session cookie
 *
 * The cookie is then stored in a json file which is used by the BackstopJS scenarios
 * to log in
 *
 * @returns {Promise} for writing cookies
 */
async function writeCookies () {
  var cookiesDir = path.join(CONFIGS.BACKSTOP_DIR, 'cookies');
  var cookieFilePath = path.join(cookiesDir, 'admin.json');
  var config = CONFIGS.getSiteConfig();
  var command = `drush ${config.drush_alias} uli --name=${LOGGED_IN_USER_NAME} --uri=${config.url} --browser=0`;
  var loginUrl = execSync(command, { encoding: 'utf8', cwd: config.root });
  var browser = await puppeteer.launch({
    headless: true,
    args: ['--no-sandbox']
  });

  var page = await browser.newPage();

  await page.goto(loginUrl);

  var cookies = await page.cookies();
  await browser.close();

  !fs.existsSync(cookiesDir) && fs.mkdirSync(cookiesDir);
  fs.existsSync(cookieFilePath) && fs.unlinkSync(cookieFilePath);

  fs.writeFileSync(cookieFilePath, JSON.stringify(cookies));
}

/**
 * Exports backstopJS related tasks task
 *
 * @param {string} action
 */
module.exports = runBackstopJS;
