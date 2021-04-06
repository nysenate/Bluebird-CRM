const createUniqueRecordFactory = require('../utils/create-unique-record-factory.js');
const cvApi = require('../utils/cv-api.js');
const customGroupService = require('./custom-group.service.js');
const createUniqueCustomField = createUniqueRecordFactory('CustomField', ['label']);

const service = {
  setupData,
  getCustomFieldsFor,
  awardReviewFieldID: null,
  awardCustomFieldID: null
};

/**
 * Create Custom Fields
 */
function setupData () {
  createCustomField(customGroupService.inline.id, customGroupService.inline.fieldLabel);
  createCustomField(customGroupService.tab.id, customGroupService.tab.fieldLabel);
  service.awardReviewFieldID = createCustomField(
    customGroupService.awardReviewField.id,
    customGroupService.awardReviewField.fieldLabel
  ).id;
  service.awardCustomFieldID = createCustomField(
    customGroupService.awardCustomField.id,
    customGroupService.awardCustomField.fieldLabel
  ).id;

  console.log('Custom Fields data setup successful.');
}

/**
 * Create Custom Field
 *
 * @param {string} customGroupId custom group id
 * @param {string} label label
 * @returns {object} custom field
 */
function createCustomField (customGroupId, label) {
  return createUniqueCustomField({
    custom_group_id: customGroupId,
    label: label,
    data_type: 'String',
    html_type: 'Text'
  });
}

/**
 * Fetch custom fields.
 *
 * @param {string} customGroupId custom group id
 * @param {string} customFieldName custom field name
 * @returns {object} custom field
 */
function getCustomFieldsFor (customGroupId, customFieldName) {
  return cvApi('CustomField', 'get', {
    sequential: true,
    custom_group_id: customGroupId,
    name: customFieldName
  });
}

module.exports = service;
