const createUniqueRecordFactory = require('../utils/create-unique-record-factory.js');
const createUniqueTag = createUniqueRecordFactory('Tag', ['name', 'used_for']);

const service = {
  setupData,
  caseTagID: null
};

/**
 * Create Tags
 */
function setupData () {
  const caseTag = 'Backstop Case Tag';

  service.caseTagID = createUniqueTag({
    is_selectable: 1,
    name: caseTag,
    used_for: 'Cases'
  }).id;

  console.log('Tags data setup successful.');
}

module.exports = service;
