const relationshipTypeService = require('./data-setup-steps/relationship-type.service');
const caseTypeService = require('./data-setup-steps/case-type.service.js');
const contactService = require('./data-setup-steps/contact.service.js');
const tagService = require('./data-setup-steps/tag.service.js');
const entityTagService = require('./data-setup-steps/entity-tag.service.js');
const activityService = require('./data-setup-steps/activity.service.js');
const relationshipService = require('./data-setup-steps/relationship.service.js');
const awardService = require('./data-setup-steps/award.service.js');
const awardFinanceManagement = require('./data-setup-steps/award-finance-management.service.js');
const caseService = require('./data-setup-steps/case.service.js');
const customGroupService = require('./data-setup-steps/custom-group.service.js');
const customFieldService = require('./data-setup-steps/custom-field.service.js');
const createSampleUploadFile = require('./data-setup-steps/create-sample-upload-file.service.js');

module.exports = setupData;

/**
 * Setups the data needed for some of the backstop tests.
 *
 * @returns {Promise} An empty promise that is resolved when the task is done.
 */
async function setupData () {
  createSampleUploadFile();

  relationshipTypeService.setupData();

  customGroupService.setupData();
  customFieldService.setupData();

  caseTypeService.setupData();
  tagService.setupData();
  contactService.setupData();
  awardService.setupData();
  awardFinanceManagement.setupData();
  caseService.setupData();
  activityService.setupData();
  await relationshipService.setupData();

  entityTagService.setupData();

  return Promise.resolve();
}
