const moment = require('moment');
const _ = require('lodash');
const createUniqueRecordFactory = require('../utils/create-unique-record-factory.js');
const caseService = require('./case.service.js');
const contactService = require('./contact.service.js');
const customFieldService = require('./custom-field.service.js');
const createUniqueActivity = createUniqueRecordFactory('Activity', ['subject']);
const createUniqueAttachment = createUniqueRecordFactory('Attachment', ['entity_id', 'entity_table']);

const service = {
  setupData
};

/**
 * Create Activities
 */
function setupData () {
  createCaseActivities();
  createAwardsActivities();

  console.log('Activities data setup successful.');
}

/**
 * @param {number} numberOfActivities number of activities
 * @param {object} params params
 * @returns {Array} list of activity ids
 */
function createActivities (numberOfActivities, params) {
  const defaultParams = {
    source_contact_id: contactService.activeContact.id,
    activity_date_time: moment().startOf('month').format('YYYY-MM-DD')
  };

  return _.range(numberOfActivities).map((i) => createUniqueActivity({
    ...defaultParams,
    subject: params.activity_type_id + ' ' + (i === 0 ? '' : (i + 1)),
    ...params
  }).id);
}

/**
 * @param {string} activityID activity ID
 */
function createAttachment (activityID) {
  createUniqueAttachment({
    content: '',
    entity_id: activityID,
    entity_table: 'civicrm_activity',
    name: 'backstop-file-upload.png',
    mime_type: 'image/png'
  });
}

/**
 * Create Case Activities
 */
function createCaseActivities () {
  const fileUploadActivityID = createActivities(1, {
    case_id: caseService.activeCaseID,
    activity_type_id: 'File Upload'
  })[0];
  createAttachment(fileUploadActivityID);

  createActivities(30, {
    case_id: caseService.activeCaseID,
    activity_type_id: 'Follow up'
  });
}

/**
 * Create Award Application Activities
 */
function createAwardsActivities () {
  const paymentTypeFieldID = customFieldService.getCustomFieldsFor(
    'Awards_Payment_Information',
    'Type'
  ).id;
  const paymentCurrencyTypeFieldID = customFieldService.getCustomFieldsFor(
    'Awards_Payment_Information',
    'Payment_Amount_Currency_Type'
  ).id;
  const paymentAmountValueFieldID = customFieldService.getCustomFieldsFor(
    'Awards_Payment_Information',
    'Payment_Amount_Value'
  ).id;

  createActivities(1, {
    target_contact_id: contactService.adminUserID,
    case_id: caseService.activeAwardApplicationId,
    activity_type_id: 'Awards Payment',
    status_id: 'approved_complete',
    ['custom_' + paymentTypeFieldID]: 1,
    ['custom_' + paymentCurrencyTypeFieldID]: 'USD',
    ['custom_' + paymentAmountValueFieldID]: '5000'
  });
}

module.exports = service;
