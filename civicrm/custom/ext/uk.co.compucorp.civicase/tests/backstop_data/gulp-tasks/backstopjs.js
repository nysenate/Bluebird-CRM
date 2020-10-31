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
var notify = require('gulp-notify');
var moment = require('moment');
var path = require('path');
var PluginError = require('plugin-error');
var puppeteer = require('puppeteer');

var BACKSTOP_DIR = '.';
var CACHE = {
  caseId: null,
  emptyCaseId: null,
  contactIdsMap: {}
};
var CONFIG_TPL = {
  url: 'http://%{site-host}',
  drush_alias: '',
  root: '%{path-to-site-root}'
};
var FILES = {
  siteConfig: 'site-config.json',
  temp: 'backstop.temp.json',
  tpl: 'backstop.tpl.json'
};
var RECORD_IDENTIFIERS = {
  activeContactDisplayName: 'Arnold Backstop',
  customGroupTitle: 'Backstop Case Custom Group',
  customFieldLabel: 'Backstop Case Custom Field',
  emptyCaseSubject: 'Backstop Empty Case',
  emptyCaseTypeName: 'backstop_empty_case_type',
  emptyContactDisplayName: 'Emil Backstop',
  fileUploadActivitySubject: 'Backstop File Upload'
};
var URL_VAR_REPLACERS = [
  replaceCaseIdVar,
  replaceEmptyCaseIdVar,
  replaceRootUrlVar,
  replaceContactIdVar
];

var createUniqueActivity = createUniqueRecordFactory('Activity', ['subject']);
var createUniqueAttachment = createUniqueRecordFactory('Attachment', ['entity_id', 'entity_table']);
var createUniqueCase = createUniqueRecordFactory('Case', ['subject']);
var createUniqueCaseType = createUniqueRecordFactory('CaseType', ['name']);
var createUniqueContact = createUniqueRecordFactory('Contact', ['display_name']);
var createUniqueCustomField = createUniqueRecordFactory('CustomField', ['label']);
var createUniqueCustomGroup = createUniqueRecordFactory('CustomGroup', ['title']);

/**
 * Returns the list of the scenarios from
 *   a. All the different groups if `group` is == '_all_',
 *   b. Only the given group
 *
 * @param {string} group of scenarios
 * @returns {Array} of the list of the scenarios
 */
function buildScenariosList (group) {
  const dirPath = 'scenarios';

  return _(fs.readdirSync(dirPath))
    .filter(scenario => {
      return (group === '_all_' ? true : scenario === `${group}.json`) && scenario.endsWith('.json');
    })
    .map(scenario => {
      return JSON.parse(fs.readFileSync(path.join(dirPath, scenario))).scenarios;
    })
    .flatten()
    .map((scenario, index, scenarios) => {
      const url = replaceUrlVars(scenario.url);

      return _.assign({}, scenario, {
        cookiePath: path.join('cookies', 'admin.json'),
        count: '(' + (index + 1) + ' of ' + scenarios.length + ')',
        url: url
      });
    })
    .value();
}

/**
 * Throws an error if it finds any inside one of the `cv api` responses.
 *
 * @param {Array} responses the list of responses as returned by `cv api:batch`.
 */
function checkAndThrowApiResponseErrors (responses) {
  responses.forEach((response) => {
    if (response.is_error) {
      throw response.error_message;
    }
  });
}

/**
 * Removes the temp config file and sends a notification
 * based on the given outcome from BackstopJS
 *
 * @param {boolean} success if backstop ran with a success
 */
function cleanUpAndNotify (success) {
  gulp
    .src(FILES.temp, { read: false })
    .pipe(clean())
    .pipe(notify({
      message: success ? 'Success' : 'Error',
      title: 'BackstopJS',
      sound: 'Beep'
    }));
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
  var list = buildScenariosList(group);
  var content = JSON.parse(fs.readFileSync(FILES.tpl));

  content.scenarios = list;

  return JSON.stringify(content);
}

/**
 * Returns a function that creates unique records for the given entity.
 *
 * @param {string} entityName the name of the entity that the records belongs to.
 * @param {string[]} matchingFields the list of fields that will be used to check
 * if the record has already been created. Ex.: `name`, `subject`, `title`, etc.
 * @returns {Function} of unique records
 */
function createUniqueRecordFactory (entityName, matchingFields) {
  /**
   * Checks if the record exists on the given entity before creating a new one.
   *
   * @param {object} recordData the data used to create a new record on the Entity.
   * @returns {object} the returned value from the API.
   */
  return function createUniqueRecord (recordData) {
    var filter = { options: { limit: 1 } };

    matchingFields.forEach((matchingField) => {
      filter[matchingField] = recordData[matchingField];
    });

    var record = cvApi(entityName, 'get', filter);

    if (record.count) {
      return record;
    }

    return cvApi(entityName, 'create', recordData);
  };
}

/**
 * Executes a single call to the `cv api` service and returns the response
 * in JSON format.
 *
 * @param {string} entityName the name of the entity to run the query on.
 * @param {string} action the entity action.
 * @param {object} queryData the data to pass to the entity action.
 * @returns {object} the result from the entity action call.
 */
function cvApi (entityName, action, queryData) {
  var queryResponse = cvApiBatch([[entityName, action, queryData]]);

  return queryResponse[0];
}

/**
 * Executes multi calls to the `cv api` service and returns the response from
 * those calls in JSON format.
 *
 * @param {Array} queriesData a list of queries to pass to the `cv api:batch` service.
 * @returns {object} response from the cv api.
 */
function cvApiBatch (queriesData) {
  var config = siteConfig();
  var cmd = `echo '${JSON.stringify(queriesData)}' | cv api:batch`;
  var responses = JSON.parse(execSync(cmd, { cwd: config.root }));

  checkAndThrowApiResponseErrors(responses);

  return responses;
}

/**
 * Defines a BackstopJS gulp task for the given action.
 *
 * @param {string} action the name of the Backstop action.
 * @returns {object} gulp task.
 */
function defineBackstopJsAction (action) {
  return gulp.task('backstopjs:' + action, () => runBackstopJS(action));
}

/**
 * Returns the ID of a case that is active and has an activity for the current
 * calendar month.
 *
 * @returns {number} case id of an active case
 */
function getActiveCaseId () {
  var startDate = moment().startOf('month').format('YYYY-MM-DD');
  var endDate = moment().endOf('month').format('YYYY-MM-DD');
  var activity = cvApi('Activity', 'get', {
    sequential: 1,
    activity_date_time: { BETWEEN: [startDate, endDate] },
    'case_id.is_deleted': 0,
    'case_id.status_id': 'Scheduled',
    case_filter: { 'case_type_id.case_type_category': 'cases' },
    return: ['case_id'],
    options: { limit: 1 }
  });

  if (!activity.count) {
    throw new Error('Please add an activity for the current month and for a case with a "Scheduled" status');
  }

  return activity.count && activity.values[0].case_id[0];
}

/**
 * Tries to get the record id from the cache first and if not found will retrieve
 * it using `cv api`, store the record id, and return it.
 *
 * @param {string} cacheKey the cache key where the record id is stored.
 * @param {Function} callback a callback function that should return the record id
 *   if none is stored.
 * @returns {string} if the record from cache
 */
function getRecordIdFromCacheOrCallback (cacheKey, callback) {
  if (!CACHE[cacheKey]) {
    CACHE[cacheKey] = callback();
  }

  return CACHE[cacheKey];
}

/**
 * Replaces the `{caseId}` var with the id of the first non deleted, open case.
 *
 * @param {string} url the scenario url.
 * @param {object} config the site config options.
 * @returns {string} replaced record id
 */
function replaceCaseIdVar (url, config) {
  return url.replace('{caseId}', function () {
    return getRecordIdFromCacheOrCallback('caseId', getActiveCaseId);
  });
}

/**
 * Replaces the `{emptyCaseId}` var with the id for the empty case created by the setup script.
 *
 * @param {string} url the scenario url.
 * @returns {string} case record id
 */
function replaceEmptyCaseIdVar (url) {
  return url.replace('{emptyCaseId}', function () {
    return getRecordIdFromCacheOrCallback('emptyCaseId', () => {
      var caseRecord = cvApi('Case', 'get', {
        subject: RECORD_IDENTIFIERS.emptyCaseSubject
      });

      return caseRecord.id;
    });
  });
}

/**
 * Replaces the `{contactName: CONTACT NAME}` var with the contact id for the contact.
 *
 * @param {string} url the scenario url.
 * @param {object} config the site config options.
 * @returns {string} final processed url string
 */
function replaceContactIdVar (url, config) {
  return url.replace(/{contactName:(.+)}/, function (stringMatch, contactName) {
    var contactId = CACHE.contactIdsMap[contactName];

    if (!contactId) {
      var cmd = `cv api contact.getsingle display_name=${contactName} option.limit=1`;
      var contactInfo = JSON.parse(execSync(cmd, { cwd: config.root }));
      contactId = contactInfo.id;
      CACHE.contactIdsMap[contactName] = contactId;
    }

    return contactId;
  });
}

/**
 * Replaces the `{url}` var with the site url as defined in the config file.
 *
 * @param {string} url the scenario url.
 * @param {object} config the site config options.
 * @returns {string} final processed url string
 */
function replaceRootUrlVar (url, config) {
  return url.replace('{url}', config.url);
}

/**
 * Runs a series of URL var replaces for the scenario URL. A URL var would look
 * like `{url}/contact` and can be replaced into a string similar to
 * `http://example.com/contact`.
 *
 * @param {string} url the original scenario url with all vars intact.
 * @returns {string} the scenario url with vars replaced.
 */
function replaceUrlVars (url) {
  const config = siteConfig();

  URL_VAR_REPLACERS.forEach(function (urlVarReplacer) {
    url = urlVarReplacer(url, config);
  });

  return url;
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

    gulp.src(FILES.tpl)
      .pipe(file(path.basename(FILES.temp), createTempConfig()))
      .pipe(gulp.dest('.'))
      .on('end', async () => {
        try {
          (typeof argv.skipCookies === 'undefined') && await writeCookies();
          await backstopjs(command, { configPath: FILES.temp, filter: argv.filter });

          success = true;
        } finally {
          cleanUpAndNotify(success);

          success ? resolve() : reject(new Error('BackstopJS error'));
        }
      });
  })
    .catch(function (err) {
      throwError(err.message);
    });
}

/**
 * Setups the data needed for some of the backstop tests.
 *
 * @returns {Promise} An empty promise that is resolved when the task is done.
 */
function setupData () {
  var activeCaseId = getActiveCaseId();
  var caseType = createUniqueCaseType({
    name: RECORD_IDENTIFIERS.emptyCaseTypeName,
    case_type_category: 'Cases',
    title: 'Backstop Empty Case Type',
    definition: {
      activityTypes: [],
      activitySets: [],
      caseRoles: [{ name: 'Case Coordinator is', manager: '1', creator: '1' }],
      timelineActivityTypes: []
    }
  });
  var activeContact = createUniqueContact({
    contact_type: 'Individual',
    display_name: RECORD_IDENTIFIERS.activeContactDisplayName
  });
  var emptyContact = createUniqueContact({
    contact_type: 'Individual',
    display_name: RECORD_IDENTIFIERS.emptyContactDisplayName
  });
  var fileUploadActivity = createUniqueActivity({
    activity_type_id: 'File Upload',
    case_id: activeCaseId,
    source_contact_id: activeContact.id,
    subject: RECORD_IDENTIFIERS.fileUploadActivitySubject
  });
  var customGroup = createUniqueCustomGroup({
    title: RECORD_IDENTIFIERS.customGroupTitle,
    extends: 'Case'
  });

  createUniqueAttachment({
    content: '',
    entity_id: fileUploadActivity.id,
    entity_table: 'civicrm_activity',
    name: 'backstop-file-upload.png',
    mime_type: 'image/png'
  });
  createUniqueCase({
    case_type_id: caseType.id,
    contact_id: emptyContact.id,
    creator_id: emptyContact.id,
    subject: RECORD_IDENTIFIERS.emptyCaseSubject
  });
  createUniqueCustomField({
    custom_group_id: customGroup.id,
    label: RECORD_IDENTIFIERS.customFieldLabel,
    data_type: 'String',
    html_type: 'Text'
  });

  return Promise.resolve();
}

/**
 * Returns the content of site config file
 *
 * @returns {object} content of site config file
 */
function siteConfig () {
  return JSON.parse(fs.readFileSync(FILES.siteConfig));
}

/**
 * Creates the site config file is in the backstopjs folder, if it doesn't exists yet
 *
 * @returns {boolean} Whether the file had to be created or not
 */
function touchSiteConfigFile () {
  let created = false;

  try {
    fs.readFileSync(FILES.siteConfig);
  } catch (err) {
    fs.writeFileSync(FILES.siteConfig, JSON.stringify(CONFIG_TPL, null, 2));

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
  var cookiesDir = path.join(BACKSTOP_DIR, 'cookies');
  var cookieFilePath = path.join(cookiesDir, 'admin.json');
  var config = siteConfig();
  var command = `drush ${config.drush_alias} uli --name=admin --uri=${config.url} --browser=0`;
  var loginUrl = execSync(command, { encoding: 'utf8', cwd: config.root });
  var browser = await puppeteer.launch();
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
module.exports = {
  setupData: setupData,
  defineAction: defineBackstopJsAction
};
