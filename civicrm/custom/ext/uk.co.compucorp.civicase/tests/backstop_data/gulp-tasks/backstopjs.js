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
var moment = require('moment');
var path = require('path');
var PluginError = require('plugin-error');
var puppeteer = require('puppeteer');

var LOGGED_IN_USER_NAME = 'admin';
var BACKSTOP_DIR = '.';
var CACHE = {
  caseId: null,
  emptyCaseId: null,
  contactIdsMap: {}
};
var FILES = {
  siteConfig: 'site-config.json',
  siteConfigSample: 'site-config.json.sample',
  temp: 'backstop.temp.json',
  tpl: 'backstop.tpl.json'
};
var RECORD_IDENTIFIERS = {
  activeContactDisplayName: 'Arnold Backstop',
  activeContactEmail: 'arnold@backstop.com',
  caseSubject: 'Backstop Case',
  caseTag: 'Backstop Case Tag',
  emptyCaseSubject: 'Backstop Empty Case',
  caseTypeName: 'backstop_case_type',
  emptyContactDisplayName: 'Emil Backstop',
  emptyContactEmail: 'emil@backstop.com',
  fileUploadActivitySubject: 'Backstop File Upload',
  customGroups: {
    inline: {
      fieldLabel: 'Backstop Case Inline Custom Field',
      groupTitle: 'Backstop Case Inline Custom Group'
    },
    tab: {
      fieldLabel: 'Backstop Case Tab Custom Field',
      groupTitle: 'Backstop Case Tab Custom Group'
    }
  },
  relationshipTypes: {
    homelessCoordinator: 'Homeless Services Coordinator is',
    healthServiceCoordinator: 'Health Services Coordinator',
    benefitsSpecialist: 'Benefits Specialist is'
  }
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
var createUniqueEmail = createUniqueRecordFactory('Email', ['email']);
var createUniqueCaseType = createUniqueRecordFactory('CaseType', ['name']);
var createUniqueRelationship = createUniqueRecordFactory('Relationship', ['contact_id_a', 'contact_id_b', 'relationship_type_id']);
var createUniqueRelationshipType = createUniqueRecordFactory('RelationshipType', ['name_a_b']);
var createUniqueContact = createUniqueRecordFactory('Contact', ['display_name']);
var createUniqueCustomField = createUniqueRecordFactory('CustomField', ['label']);
var createUniqueCustomGroup = createUniqueRecordFactory('CustomGroup', ['title']);
var createUniqueTag = createUniqueRecordFactory('Tag', ['name', 'used_for']);
var createUniqueEntityTag = createUniqueRecordFactory('EntityTag', ['entity_id', 'entity_table', 'tag_id']);

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
 */
function cleanUp () {
  gulp
    .src(FILES.temp, { read: false })
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
  var list = buildScenariosList(group);
  var content = JSON.parse(fs.readFileSync(FILES.tpl));

  content.scenarios = list.map((item) => {
    item.delay = item.delay || 2000;

    return item;
  });

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
    var filter = { options: { limit: 1 }, sequential: 1 };

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
  var cmd = `echo '${JSON.stringify(queriesData)}' | cv api:batch -U ${LOGGED_IN_USER_NAME}`;
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
 * Setups the data needed for some of the backstop tests.
 *
 * @returns {Promise} An empty promise that is resolved when the task is done.
 */
async function setupData () {
  var homelessCoordinatorRelType = createUniqueRelationshipType({
    name_a_b: RECORD_IDENTIFIERS.relationshipTypes.homelessCoordinator
  });
  var benefitsSpecialistRelType = createUniqueRelationshipType({
    name_a_b: RECORD_IDENTIFIERS.relationshipTypes.benefitsSpecialist
  });

  var caseType = createUniqueCaseType({
    name: RECORD_IDENTIFIERS.caseTypeName,
    case_type_category: 'Cases',
    title: 'Backstop Case Type',
    definition: {
      activityTypes: [{
        name: 'Open Case',
        max_instances: '1'
      }, {
        name: 'Follow up'
      }, {
        name: 'File Upload'
      }],
      activitySets: [],
      caseRoles: [
        {
          name: RECORD_IDENTIFIERS.relationshipTypes.homelessCoordinator,
          creator: '1',
          manager: '0'
        }, {
          name: RECORD_IDENTIFIERS.relationshipTypes.healthServiceCoordinator,
          manager: '0'
        }, {
          name: RECORD_IDENTIFIERS.relationshipTypes.benefitsSpecialist,
          manager: '1'
        }
      ],
      timelineActivityTypes: []
    }
  });

  var activeContact = createUniqueContact({
    contact_type: 'Individual',
    display_name: RECORD_IDENTIFIERS.activeContactDisplayName
  });
  createUniqueEmail({
    contact_id: activeContact.id,
    email: RECORD_IDENTIFIERS.activeContactEmail
  });
  var emptyContact = createUniqueContact({
    contact_type: 'Individual',
    display_name: RECORD_IDENTIFIERS.emptyContactDisplayName,
    email: RECORD_IDENTIFIERS.emptyContactEmail
  });
  createUniqueEmail({
    contact_id: emptyContact.id,
    email: RECORD_IDENTIFIERS.emptyContactEmail
  });

  var caseIds = createCases(caseType, activeContact, emptyContact);
  createActivities(caseIds[0], activeContact);

  createUniqueRelationship({
    contact_id_a: activeContact.id,
    relationship_type_id: benefitsSpecialistRelType.id,
    start_date: 'now',
    end_date: null,
    contact_id_b: emptyContact.id,
    case_id: caseIds[0],
    description: 'Manager Role Assigned'
  });

  await sleep(500);

  createUniqueRelationship({
    contact_id_a: activeContact.id,
    relationship_type_id: homelessCoordinatorRelType.id,
    start_date: 'now',
    end_date: null,
    contact_id_b: emptyContact.id,
    case_id: caseIds[0],
    description: 'Homeless Coordinator Assigned'
  });

  await sleep(500);

  var fileUploadActivity = createUniqueActivity({
    activity_type_id: 'File Upload',
    case_id: caseIds[0],
    source_contact_id: activeContact.id,
    subject: RECORD_IDENTIFIERS.fileUploadActivitySubject
  });

  var inlineCustomGroup = createUniqueCustomGroup({
    extends: 'Case',
    style: 'Inline',
    title: RECORD_IDENTIFIERS.customGroups.inline.groupTitle
  });
  var tabCustomGroup = createUniqueCustomGroup({
    extends: 'Case',
    style: 'Tab',
    title: RECORD_IDENTIFIERS.customGroups.tab.groupTitle
  });

  createUniqueCustomField({
    custom_group_id: inlineCustomGroup.id,
    label: RECORD_IDENTIFIERS.customGroups.inline.fieldLabel,
    data_type: 'String',
    html_type: 'Text'
  });

  createUniqueCustomField({
    custom_group_id: tabCustomGroup.id,
    label: RECORD_IDENTIFIERS.customGroups.tab.fieldLabel,
    data_type: 'String',
    html_type: 'Text'
  });

  createUniqueAttachment({
    content: '',
    entity_id: fileUploadActivity.id,
    entity_table: 'civicrm_activity',
    name: 'backstop-file-upload.png',
    mime_type: 'image/png'
  });

  createUniqueTag({
    is_selectable: 1,
    name: RECORD_IDENTIFIERS.caseTag,
    used_for: 'Cases'
  });
  createUniqueEntityTag({
    entity_id: caseIds[0],
    entity_table: 'civicrm_case',
    tag_id: RECORD_IDENTIFIERS.caseTag
  });

  createSampleUploadFile();

  return Promise.resolve();
}

/**
 * Create Sample Upload File
 */
function createSampleUploadFile () {
  fs.writeFileSync('sample.txt', 'Sample Text');
}

/**
 * Create Activities
 *
 * @param {number} caseId case id
 * @param {object} activeContact active contact
 * @returns {Array} list of activity ids
 */
function createActivities (caseId, activeContact) {
  var activityIds = [];

  for (var i = 1; i <= 30; i++) {
    activityIds.push(createUniqueActivity({
      activity_type_id: 'Follow up',
      case_id: caseId,
      source_contact_id: activeContact.id,
      subject: 'Follow Up ' + i,
      activity_date_time: moment().startOf('month').format('YYYY-MM-DD')
    }).id);
  }

  return activityIds;
}

/**
 * Create Cases
 *
 * @param {object} caseType case type
 * @param {object} activeContact active contact
 * @param {object} emptyContact empty contact
 * @returns {Array} list of case ids
 */
function createCases (caseType, activeContact, emptyContact) {
  var caseIds = [];

  for (var i = 1; i <= 17; i++) {
    caseIds.push(createUniqueCase({
      case_type_id: caseType.id,
      contact_id: activeContact.id,
      creator_id: activeContact.id,
      subject: RECORD_IDENTIFIERS.caseSubject + i
    }).id);
  }

  caseIds.push(createUniqueCase({
    case_type_id: caseType.id,
    contact_id: emptyContact.id,
    creator_id: emptyContact.id,
    subject: RECORD_IDENTIFIERS.emptyCaseSubject
  }).id);

  return caseIds;
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
    // fs.createReadStream(FILES.siteConfigSample).pipe(fs.createWriteStream(FILES.siteConfig));
    fs.copyFileSync(FILES.siteConfigSample, FILES.siteConfig);
    // fs.writeFileSync(FILES.siteConfig, JSON.stringify(CONFIG_TPL, null, 2));

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
 * @param {number} ms milliseconds
 */
async function sleep (ms) {
  await new Promise((resolve) => setTimeout(resolve, ms));
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
