const _ = require('lodash');
const createUniqueRecordFactory = require('../utils/create-unique-record-factory.js');
const caseTypeService = require('./case-type.service.js');
const awardService = require('./award.service.js');
const contactService = require('./contact.service.js');
const createUniqueCase = createUniqueRecordFactory('Case', ['subject']);

const service = {
  setupData,
  caseSubject: 'Backstop Case',
  awardApplicationSubject: 'Backstop Award Application',
  emptyCaseSubject: 'Backstop Empty Case',
  activeCaseID: null,
  activeAwardApplicationId: null
};

/**
 * Create Cases
 */
function setupData () {
  var caseIds = createCases(
    17,
    service.caseSubject,
    caseTypeService.caseType,
    contactService.activeContact
  );

  caseIds = caseIds.concat(
    createCases(
      1,
      service.emptyCaseSubject,
      caseTypeService.caseType,
      contactService.emptyContact
    )
  );

  service.activeCaseID = caseIds[0];

  const awardApplicationIds = createCases(
    5,
    service.awardApplicationSubject,
    awardService.award,
    contactService.activeContact
  );

  service.activeAwardApplicationId = awardApplicationIds[0];

  console.log('Case data setup successful.');
}

/**
 * Create Cases
 *
 * @param {number} numberOfCases number of cases
 * @param {object} caseSubject case subject
 * @param {object} caseType case type
 * @param {object} contact contact object
 * @returns {string[]} list of case ids
 */
function createCases (numberOfCases, caseSubject, caseType, contact) {
  return _.range(numberOfCases).map((i) => createUniqueCase({
    case_type_id: caseType.id,
    contact_id: contact.id,
    creator_id: contact.id,
    subject: caseSubject + (i === 0 ? '' : (i + 1))
  }).id);
}

module.exports = service;
