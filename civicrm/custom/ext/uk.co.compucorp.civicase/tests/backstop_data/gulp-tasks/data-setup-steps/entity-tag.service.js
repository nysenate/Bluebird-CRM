const createUniqueRecordFactory = require('../utils/create-unique-record-factory.js');
const caseService = require('./case.service.js');
const createUniqueEntityTag = createUniqueRecordFactory('EntityTag', ['entity_id', 'entity_table', 'tag_id']);

const service = {
  setupData
};

/**
 * Create Entity Tags
 */
function setupData () {
  const caseTag = 'Backstop Case Tag';

  createUniqueEntityTag({
    entity_id: caseService.activeCaseID,
    entity_table: 'civicrm_case',
    tag_id: caseTag
  });

  console.log('Entity Tags data setup successful.');
}

module.exports = service;
