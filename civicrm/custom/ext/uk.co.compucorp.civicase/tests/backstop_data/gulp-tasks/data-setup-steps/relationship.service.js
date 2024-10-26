const createUniqueRecordFactory = require('../utils/create-unique-record-factory.js');
const caseService = require('./case.service.js');
const contactService = require('./contact.service.js');
const relationshipTypesService = require('./relationship-type.service.js');
const createUniqueRelationship = createUniqueRecordFactory(
  'Relationship',
  ['contact_id_a', 'contact_id_b', 'relationship_type_id']
);

const service = {
  setupData
};

/**
 * Create Relationships
 */
async function setupData () {
  await createRelationship(
    caseService.activeCaseID,
    contactService.activeContact.id,
    contactService.emptyContact.id,
    relationshipTypesService.benefitsSpecialistRelType.id,
    'Manager Role Assigned'
  );

  await createRelationship(
    caseService.activeCaseID,
    contactService.activeContact.id,
    contactService.emptyContact.id,
    relationshipTypesService.homelessCoordinatorRelType.id,
    'Homeless Coordinator Assigned'
  );

  console.log('Relationship data setup successful.');
}

/**
 * Create Relationship
 *
 * @param {number} caseID case id
 * @param {number} contactIdA contact id A
 * @param {number} contactIdB contact id B
 * @param {number} relationshipTypeId relationship type id
 * @param {string} description relationship description
 * @returns {object} relationship
 */
async function createRelationship (caseID, contactIdA, contactIdB, relationshipTypeId, description) {
  createUniqueRelationship({
    contact_id_a: contactIdA,
    relationship_type_id: relationshipTypeId,
    start_date: 'now',
    end_date: null,
    contact_id_b: contactIdB,
    case_id: caseID,
    description: description
  });

  // This is needed, because otherwise activities for relationships
  // creation of the case gets created in random manner. Which results in
  // failure of backstop tests
  await sleep(500);
}

/**
 * @param {number} ms milliseconds
 */
async function sleep (ms) {
  await new Promise((resolve) => setTimeout(resolve, ms));
}

module.exports = service;
